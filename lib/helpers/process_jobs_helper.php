<?php
/*
 * Copyright (c) 2022 LatePoint LLC. All rights reserved.
 */

class OsProcessJobsHelper {

	public static function create_jobs_for_process(OsProcessModel $process, array $objects){
		if(!$process->check_if_objects_satisfy_trigger_conditions($objects)) return;
		$job = new OsProcessJobModel();
		$job->process_id = $process->id;
		$job->object_id = $objects[0]['model_ready']->id;

		// check if job exists already
		$existing_job = new OsProcessJobModel();
		$exists = $existing_job->select('id')->where(['process_id' => $job->process_id, 'object_id' => $job->object_id, 'status' => LATEPOINT_JOB_STATUS_SCHEDULED])->get_results();
		if($exists) return;

		$job_settings = [];
		foreach($process->actions as $action){
			if($action->status != LATEPOINT_STATUS_ACTIVE) continue;
			$action->selected_data_objects = $objects;
			$action->prepare_data_for_run();
			$job_settings['action_info'][$action->id] = ['type' => $action->type];
			$job_settings['action_data'][$action->id] = $action->prepared_data_for_run;
		}

		$event_time_utc = false;
		switch($process->event_type){
			case 'booking_updated';
			case 'booking_created';
			case 'booking_start';
			case 'booking_end';
				$object_model_type = 'booking';
			break;
			case 'customer_created':
			case 'customer_updated':
				$object_model_type = 'customer';
				break;
			case 'order_created':
			case 'order_updated':
				$object_model_type = 'order';
				break;
			case 'service_created':
			case 'service_updated':
				$object_model_type = 'service';
				break;
			case 'agent_created':
			case 'agent_updated':
				$object_model_type = 'agent';
				break;
			case 'transaction_created':
			case 'transaction_updated':
				$object_model_type = 'transaction';
				break;
		}
		try{
			switch($process->event_type){
				case 'booking_updated':
				case 'order_updated':
					$updated_at = new OsWpDateTime($objects[0]['model_ready']->updated_at);
					$updated_at->setTimezone(new DateTimeZone('UTC'));
					$event_time_utc = $updated_at;
					break;
				case 'order_created':
				case 'booking_created':
				case 'transaction_created':
				case 'customer_created':
					$created_at = new OsWpDateTime($objects[0]['model_ready']->created_at);
					$created_at->setTimezone(new DateTimeZone('UTC'));
					$event_time_utc = $created_at;
					break;
				case 'booking_start':
					$event_time_utc = new OsWpDateTime($objects[0]['model_ready']->start_datetime_utc, new DateTimeZone('UTC'));
					break;
				case 'booking_end':
					$event_time_utc = new OsWpDateTime($objects[0]['model_ready']->end_datetime_utc, new DateTimeZone('UTC'));
					break;
			}
		}catch(Exception $e){
			OsDebugHelper::log('Error creating jobs for workflow', 'process_jobs_error', print_r($process->id, true).' '.print_r($objects,true).' '.$e->getMessage());
			return;
		}
		if(!$event_time_utc) return;

		$job->settings = wp_json_encode($job_settings);
		$job->process_info = wp_json_encode($process->get_info());

		// apply time offset if exists in process
		$modify_by = self::should_modify_event_time($process);
		if(!empty($modify_by)) $event_time_utc->modify($modify_by);
		$now_utc = new OsWpDateTime();
		$now_utc->setTimezone(new DateTimeZone('UTC'));

		// we need to make sure we are not creating jobs that are already past their relevance (e.g. booking updated but
		// 2 day before booking_start notification was already sent before, so scheduling a new job for that doesn't
		// make sense). Problem is that is we have booking_udpated notification - then the time of "booking_updated" event
		// is technically in the past, since the DB update happened couple of milliseconds ago. So solution is to create a
		// buffer time to allow for these discrepancies in which we can resend the notification if it's within that buffer
		$buffer = '+5 minutes';
		$event_time_utc_buffered = clone $event_time_utc;
		$event_time_utc_buffered->modify($buffer);
		// event time with buffer is already long passed (cron already ran it probably, or after changing booking times, this job is not relevant anymore)
		if($event_time_utc_buffered < $now_utc) return;


		$is_in_the_future = $event_time_utc > $now_utc;
		$job->object_model_type = $object_model_type ?? 'n/a';
		$job->to_run_after_utc =$event_time_utc->format(LATEPOINT_DATETIME_DB_FORMAT);

		$job->status = LATEPOINT_JOB_STATUS_SCHEDULED;
		$job->save();
		// execute immediately, if there is no delay(time offset) specified
		// todo add ability toggling setting to allow delayed execution even on instant events (to speed up frontend experience for customers)
		if(!$is_in_the_future){
			$job->run();
		}
	}

	/**
	 * @param string $event_type
	 * @param array $objects example format: ['model' => 'booking', 'id' => $booking->id, 'model_ready' => OsModel $booking]
	 * @return void
	 */
	public static function create_jobs_for_event(string $event_type, array $objects){
		$processes = new OsProcessModel();
		// find all processes that match this event type
		$processes = $processes->where(['event_type' => $event_type])->should_be_active()->get_results_as_models();
		if($processes){
			foreach($processes as $process){
				$process->build_from_json();
				self::create_jobs_for_process($process, $objects);
			}
		}
	}

	/**
	 *
	 * Searches existing records that match this process conditions and schedules a job, for example if you created a new
	 * process that sends a notification 15 minute before the booking start - this method will find those bookings and
	 * schedule jobs to send notification
	 *
	 * @param OsProcessModel $process
	 * @return bool|void
	 */
	public static function recreate_jobs_for_existing_records(OsProcessModel $process){
		// don't create jobs for booking_updated event, since we don't capture the exact information of what was updated in
		// the booking and can't check if event conditions are satisfied
		if(!in_array($process->event_type, ['booking_start', 'booking_end', 'booking_created', 'customer_created', 'transaction_created'])) return false;

		// calculate the cutoff date to search records that could be affected by this event
		$cutoff_datetime_utc = OsTimeHelper::now_datetime_utc();
		$modify_by = self::should_modify_event_time($process, true);
		if($modify_by){
			$cutoff_datetime_utc->modify($modify_by);
		}else{
			// if there is no time offset for this process and event type is not "start" or "end" of the booking - we don't need to
			// create any jobs, since other events have already past
			if(!in_array($process->event_type, ['booking_start', 'booking_end'])) return true;
		}

		// convert to server datetime to query create_at/updated_at columns that are stored in server timezone format
		$cutoff_datetime_server = clone $cutoff_datetime_utc;
		$cutoff_datetime_server->setTimezone(OsTimeHelper::get_wp_timezone());

		$formatted_cutoff_utc = $cutoff_datetime_utc->format(LATEPOINT_DATETIME_DB_FORMAT);
		$formatted_cutoff_server = $cutoff_datetime_server->format(LATEPOINT_DATETIME_DB_FORMAT);

		$args = [];
		switch($process->event_type){
			case 'booking_start':
				$args['start_datetime_utc >='] = $formatted_cutoff_utc;
				break;
			case 'booking_end':
				$args['end_datetime_utc >='] = $formatted_cutoff_utc;
				break;
			case 'booking_created':
				$args['created_at >='] = $formatted_cutoff_server;
				break;
			case 'booking_updated':
				$args['updated_at >='] = $formatted_cutoff_server;
				break;
			case 'transaction_created':
				$args['created_at >='] = $formatted_cutoff_server;
				break;
			case 'customer_created':
				$args['created_at >='] = $formatted_cutoff_server;
				break;
		}
		if($process->event_type == 'customer_created'){
			$models = new OsCustomerModel();
			$model_name = 'customer';
		}else{
			$models = new OsBookingModel();
			$model_name = 'booking';
		}
		if($args) $models->where($args);
		$models = $models->get_results_as_models();

		foreach($models as $model){
			$objects = [];
			$objects[] = ['model' => $model_name, 'id' => $model->id, 'model_ready' => $model];
			self::create_jobs_for_process($process, $objects);
		}
	}

	public static function process_scheduled_jobs(){
		$jobs = new OsProcessJobModel();
		// find jobs that are scheduled to run in a period from [24 hour ago to NOW] - so that we don't run old irrelevant jobs
		$jobs = $jobs->where(['status' => LATEPOINT_JOB_STATUS_SCHEDULED, 'to_run_after_utc <=' => OsTimeHelper::now_datetime_utc_in_db_format(), 'to_run_after_utc >=' => OsTimeHelper::custom_datetime_utc_in_db_format('-24 hours')])->get_results_as_models();
		foreach($jobs as $job){
			$job->run();
			$result = json_decode($job->run_result, true);
			echo '<div>'.esc_html($job->id).':'.esc_html($result['status']).', '.esc_html($result['message']).'</div>';
		}
	}


	public static function init_hooks(){
		add_action('latepoint_customer_created', 'OsProcessJobsHelper::handle_customer_created', 12);
		add_action('latepoint_transaction_created', 'OsProcessJobsHelper::handle_transaction_created', 12);
		add_action('latepoint_booking_created', 'OsProcessJobsHelper::handle_booking_created', 12);
		add_action('latepoint_booking_updated', 'OsProcessJobsHelper::handle_booking_updated', 12, 2);
		add_action('latepoint_order_created', 'OsProcessJobsHelper::handle_order_created', 12);
		add_action('latepoint_order_updated', 'OsProcessJobsHelper::handle_order_updated', 12, 2);
	}

	public static function handle_customer_created(OsCustomerModel $customer){
		$objects = [];
		$objects[] = ['model' => 'customer', 'id' => $customer->id, 'model_ready' => $customer];
		self::create_jobs_for_event('customer_created', $objects);
	}

	public static function handle_transaction_created(OsTransactionModel $transaction){
		$objects = [];
		$objects[] = ['model' => 'transaction', 'id' => $transaction->id, 'model_ready' => $transaction];
		self::create_jobs_for_event('transaction_created', $objects);
	}

	public static function get_nice_job_status_name($status){
		$names = [
			LATEPOINT_JOB_STATUS_COMPLETED => __('Completed', 'latepoint'),
			LATEPOINT_JOB_STATUS_SCHEDULED => __('Scheduled', 'latepoint'),
			LATEPOINT_JOB_STATUS_CANCELLED => __('Cancelled', 'latepoint'),
			LATEPOINT_JOB_STATUS_ERROR => __('Error', 'latepoint'),
		];
		return $names[$status] ?? __('n/a', 'latepoint');
	}

	public static function handle_booking_created(OsBookingModel $booking){
		$objects = [];
		$objects[] = ['model' => 'booking', 'id' => $booking->id, 'model_ready' => $booking];
		self::create_jobs_for_event('booking_created', $objects);
		self::create_jobs_for_event('booking_start', $objects);
		self::create_jobs_for_event('booking_end', $objects);
	}


	public static function handle_booking_updated(OsBookingModel $new_booking, OsBookingModel $old_booking){
		// remove previously scheduled jobs for this booking because it's changed and might not need them anymore
		// remove only those that are in "scheduled" status, those that were already sent or errored should stay
		$jobs = new OsProcessJobModel();
		$jobs->delete_where(['status' => LATEPOINT_JOB_STATUS_SCHEDULED, 'object_id' => $new_booking->id, 'object_model_type' => 'booking']);

		$objects = [];
		$objects[] = ['model' => 'booking', 'id' => $new_booking->id, 'model_ready' => $new_booking];
		$objects[] = ['model' => 'old_booking', 'id' => $old_booking->id, 'model_ready' => $old_booking];
		self::create_jobs_for_event('booking_updated', $objects);
		// some changes might have triggered other webhooks (e.g. service changed, so now it could be required to be reminded of booking start/end)

		self::create_jobs_for_event('booking_start', $objects);
		self::create_jobs_for_event('booking_end', $objects);
	}



	public static function handle_order_created(OsOrderModel $order){
		$objects = [];
		$objects[] = ['model' => 'order', 'id' => $order->id, 'model_ready' => $order];
		self::create_jobs_for_event('order_created', $objects);
	}


	public static function handle_order_updated(OsOrderModel $new_order, OsOrderModel $old_order){
		// remove previously scheduled jobs for this order because it's changed and might not need them anymore
		// remove only those that are in "scheduled" status, those that were already sent or errored should stay
		$jobs = new OsProcessJobModel();
		$jobs->delete_where(['status' => LATEPOINT_JOB_STATUS_SCHEDULED, 'object_id' => $new_order->id, 'object_model_type' => 'order']);

		$objects = [];
		$objects[] = ['model' => 'order', 'id' => $new_order->id, 'model_ready' => $new_order];
		$objects[] = ['model' => 'old_order', 'id' => $old_order->id, 'model_ready' => $old_order];
		self::create_jobs_for_event('order_updated', $objects);
	}

	/**
	 * @param OsProcessModel $process
	 * @param $opposite determines if apply time offset in opposite direction (to search events eligible for cutoff)
	 * @return string
	 */
	public static function should_modify_event_time(OsProcessModel $process, $opposite = false): string{
		if(empty($process->time_offset)) {
			// no time offset
			return '';
		}else{
			$time_offset_settings = $process->time_offset;
			// offset, calculate how much to modify by
			$sign = ($opposite) ? (($time_offset_settings['before_after'] == 'after') ? '-' : '+') : (($time_offset_settings['before_after'] == 'after') ? '+' : '-');
			$modify_by = $sign.$time_offset_settings['value'].' '.$time_offset_settings['unit'];
			return $modify_by;
		}
	}

}