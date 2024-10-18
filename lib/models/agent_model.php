<?php

/**
 * @property string $full_name
 */
class OsAgentModel extends OsModel {
	public $id,
		$first_name = '',
		$last_name = '',
		$display_name,
		$email,
		$phone,
		$password,
		$avatar_image_id,
		$bio_image_id,
		$is_custom_price = false,
		$is_custom_hours = false,
		$is_custom_duration = false,
		$custom_hours,
		$wp_user_id,
		$title,
		$bio,
		$features,
		$extra_emails,
		$extra_phones,
		$status,
		$meta_class = 'OsAgentMetaModel',
		$updated_at,
		$created_at,
		$services_agents_table_name;

	function __construct($id = false) {
		parent::__construct();
		$this->table_name = LATEPOINT_TABLE_AGENTS;
		$this->services_agents_table_name = LATEPOINT_TABLE_AGENTS_SERVICES;
		$this->nice_names = array(
			'first_name' => __('First Name', 'latepoint'),
			'password' => __('Password', 'latepoint'),
			'email' => __('Email Address', 'latepoint'),
			'wp_user_id' => __('Connected WordPress User', 'latepoint'),
			'last_name' => __('Last Name', 'latepoint'));

		if ($id) {
			$this->load_by_id($id);
		}
	}


	public function get_initials(){
		return substr($this->first_name,0,1).substr($this->last_name,0,1);
	}

	public function get_edit_link(){
		return OsRouterHelper::build_link(['agents', 'edit_form'], ['id' => $this->id] );
	}

	public function generate_data_vars(): array {
		return [
			'id' => $this->id,
			'full_name' => $this->full_name,
			'email' => $this->email,
			'phone' => $this->phone
		];
	}

	protected function params_to_save($role = 'admin') {
		$params_to_save = array('id',
			'first_name',
			'last_name',
			'display_name',
			'email',
			'phone',
			'password',
			'wp_user_id',
			'bio_image_id',
			'title',
			'bio',
			'features',
			'status',
			'extra_emails',
			'extra_phones',
			'avatar_image_id',
			'custom_hours');
		return $params_to_save;
	}

	protected function allowed_params($role = 'admin') {
		$allowed_params = array('id',
			'first_name',
			'last_name',
			'display_name',
			'email',
			'phone',
			'password',
			'wp_user_id',
			'bio_image_id',
			'title',
			'bio',
			'features',
			'extra_emails',
			'extra_phones',
			'status',
			'avatar_image_id',
			'custom_hours');
		return $allowed_params;
	}


	protected function properties_to_validate() {
		$validations = array(
			'email' => array('presence'),
			'wp_user_id' => array('uniqueness'),
		);
		return $validations;
	}


	public function count_number_of_connected_locations($service_id = false) {
		if ($this->is_new_record()) return 0;
		$args = ['agent_id' => $this->id];
		if ($service_id) $args['service_id'] = $service_id;
		return OsConnectorHelper::count_connections($args, 'location_id');
	}

	public function delete_meta_by_key($meta_key) {
		if ($this->is_new_record()) return false;

		$meta = new OsAgentMetaModel();
		return $meta->delete_by_key($meta_key, $this->id);
	}

	public function get_meta_by_key($meta_key, $default = false) {
		if ($this->is_new_record()) return $default;

		$meta = new OsAgentMetaModel();
		return $meta->get_by_key($meta_key, $this->id, $default);
	}

	public function save_meta_by_key($meta_key, $meta_value) {
		if ($this->is_new_record()) return false;

		$meta = new OsAgentMetaModel();
		return $meta->save_by_key($meta_key, $meta_value, $this->id);
	}

	protected function set_defaults() {
		if (empty($this->status)) $this->status = LATEPOINT_AGENT_STATUS_ACTIVE;
	}

	public function has_location($location_id) {
		return OsConnectorHelper::has_connection(['agent_id' => $this->id, 'location_id' => $location_id]);
	}


	protected function get_total_future_bookings() {
		$bookings = new OsBookingModel();
		$total = $bookings->where(['agent_id' => $this->id])->should_be_in_future()->count();
		return $total;
	}

	protected function get_total_synced_future_bookings() {
		$bookings = new OsBookingModel();
		$total = $bookings->where(['agent_id' => $this->id, LATEPOINT_TABLE_BOOKING_META.'.meta_key' => 'google_calendar_event_id'])
			->join(LATEPOINT_TABLE_BOOKING_META, ['object_id' => LATEPOINT_TABLE_BOOKINGS . '.id'])
			->should_be_in_future()
			->count();
		return $total;
	}

	protected function get_future_bookings($limit = false) {
		$bookings = new OsBookingModel();
		if ($limit) {
			$bookings = $bookings->set_limit($limit);
		}
		return $bookings->order_by('start_date, start_time asc')->where(['agent_id' => $this->id])->should_be_in_future()->get_results_as_models();
	}

	public function should_be_active() {
		return $this->where(['status !=' => LATEPOINT_AGENT_STATUS_DISABLED]);
	}

	public function has_service_and_location($service_id, $location_id) {
		if ($this->is_new_record()) return false;
		return OsConnectorHelper::has_connection(['location_id' => $location_id, 'agent_id' => $this->id, 'service_id' => $service_id]);
	}

	public function get_features_arr() {
		$features_arr = [];
		if (!empty($this->features)) {
			$features = json_decode($this->features, true);
			if(!empty($features)){
				foreach ($features as $feature) {
					if ($feature['value'] && $feature['label']) $features_arr[] = $feature;
				}
			}
		}
		return $features_arr;
	}

	public function save_custom_schedule($work_periods) {
		foreach ($work_periods as &$work_period) {
			$work_period['agent_id'] = $this->id;
		}
		unset($work_period);
		OsWorkPeriodsHelper::save_work_periods($work_periods);
	}

	public function delete_custom_schedule() {
		$work_periods_model = new OsWorkPeriodModel();
		$work_periods = $work_periods_model->where(array('agent_id' => $this->id, 'service_id' => 0, 'location_id' => 0, 'custom_date' => 'IS NULL'))->get_results_as_models();
		if (is_array($work_periods)) {
			foreach ($work_periods as $work_period) {
				$work_period->delete();
			}
		}
	}

	public function has_service($service_id) {
		foreach ($this->services as $service) {
			if ($service->id == $service_id) return true;
		}
		return false;
	}


	public function save_services() {
		foreach ($this->services as $service) {
			$service_connection_row = $this->db->get_row($this->db->prepare('SELECT id FROM ' . $this->services_agents_table_name . ' WHERE agent_id = %d AND service_id = %d', array($this->id, $service->id)));
			if ($service_connection_row) {
				$update_data = array('is_custom_hours' => $service->is_custom_hours, 'is_custom_price' => $service->is_custom_price, 'is_custom_duration' => $service->is_custom_duration);
				$this->db->update($this->services_agents_table_name, $update_data, array('id' => $service_connection_row->id));
			} else {
				$insert_data = array('service_id' => $service->id, 'agent_id' => $this->id, 'is_custom_hours' => $service->is_custom_hours, 'is_custom_price' => $service->is_custom_price, 'is_custom_duration' => $service->is_custom_duration);
				if ($this->db->insert($this->services_agents_table_name, $insert_data)) {
					$service_connection_row_id = $this->db->insert_id;
				}
			}
		}
		return true;
	}


	public function remove_services_by_ids($ids_to_remove = array()) {
		if ($ids_to_remove) {
			$query = $this->db->prepare('DELETE FROM %i WHERE agent_id = %d AND service_id IN ' . OsModel::where_in_array_to_string($ids_to_remove), [$this->services_agents_table_name, $this->id]);
			$this->db->query($query);
		}
	}


	public function get_service_ids_to_remove($new_services = array()) {
		$current_service_ids = $this->get_current_service_ids_from_db();
		$new_service_ids = array();
		foreach ($new_services as $service) {
			if ($service['connected'] == "yes") $new_service_ids[] = $service['id'];
		}
		$service_ids_to_remove = array_diff($current_service_ids, $new_service_ids);
		return $service_ids_to_remove;
	}


	public function save_locations_and_services($services) {
		if (!$services) return true;
		$connections_to_save = [];
		$connections_to_remove = [];
		foreach ($services as $service_key => $locations) {
			$service_id = str_replace('service_', '', $service_key);
			foreach ($locations as $location_key => $location) {
				$location_id = str_replace('location_', '', $location_key);
				$connection = ['service_id' => $service_id, 'agent_id' => $this->id, 'location_id' => $location_id];
				if ($location['connected'] == 'yes') {
					$connections_to_save[] = $connection;
				} else {
					$connections_to_remove[] = $connection;
				}
			}
		}
		if (!empty($connections_to_save)) {
			foreach ($connections_to_save as $connection_to_save) {
				OsConnectorHelper::save_connection($connection_to_save);
			}
		}
		if (!empty($connections_to_remove)) {
			foreach ($connections_to_remove as $connection_to_remove) {
				OsConnectorHelper::remove_connection($connection_to_remove);
			}
		}
		return true;
	}

	public function set_features($features) {
		$this->features = wp_json_encode($features);
	}

	public function get_current_service_ids_from_db() {
		$query = $this->db->prepare('SELECT service_id FROM ' . $this->services_agents_table_name . ' WHERE agent_id = %d', $this->id);
		$service_rows = $this->db->get_results($query);

		$service_ids = array();

		if ($service_rows) {
			foreach ($service_rows as $service_row) {
				$service_ids[] = $service_row->service_id;
			}
		}
		return $service_ids;
	}


	public function get_current_service_ids() {
		$service_ids = array();
		foreach ($this->services as $service) {
			$service_ids[] = $service->id;
		}
		return $service_ids;
	}

	public function set_services($service_datas) {
		$this->services = array();

		foreach ($service_datas as $service_data) {
			if ($service_data['connected'] == "yes") {
				$service = new OsserviceModel();
				$service->id = $service_data['id'];
				$service->is_custom_hours = $service_data['is_custom_hours'];
				$service->is_custom_price = $service_data['is_custom_price'];
				$service->is_custom_duration = $service_data['is_custom_duration'];
				$this->services[] = $service;
			}
		}
		return $this;
	}

	public function filter_allowed_records(): OsModel{
		if(!OsRolesHelper::are_all_records_allowed('agent')){
			$this->filter_where_conditions(['id' => OsRolesHelper::get_allowed_records('agent')]);
		}
		return $this;
	}

	public function get_services() {
		if (!isset($this->services)) {
			$query = 'SELECT * FROM ' . $this->services_agents_table_name . ' WHERE agent_id = %d GROUP BY service_id';
			$query_args = array($this->id);
			$services_rows = $this->get_query_results($query, $query_args);

			$this->services = array();

			if ($services_rows) {
				foreach ($services_rows as $service_row) {
					$service = new OsServiceModel($service_row->service_id);
					$service->is_custom_hours = $service_row->is_custom_hours;
					$service->is_custom_price = $service_row->is_custom_price;
					$service->is_custom_duration = $service_row->is_custom_duration;
					$this->services[] = $service;
				}
			}
		}
		return $this->services;
	}


	protected function before_create() {
		if (empty($this->password)) $this->password = wp_hash_password(bin2hex(openssl_random_pseudo_bytes(8)));
	}

	protected function before_save() {
	}

	public function get_full_name() {
		$full_name = trim(join(' ', array($this->first_name, $this->last_name)));
		return  empty($full_name) ? __('Agent', 'latepoint') : $full_name;
	}

	protected function get_name_for_front() {
		if (isset($this->display_name) && !empty($this->display_name)) {
			return $this->display_name;
		} else {
			return $this->get_full_name();
		}
	}

	public function get_avatar_url() {
		return OsAgentHelper::get_avatar_url($this);
	}

	public function get_avatar_image() {
		return '<img src="' . $this->get_avatar_url() . '"/>';
	}

	public function get_bio_image_url() {
		return OsAgentHelper::get_bio_image_url($this);
	}

	public function get_bio_image() {
		return '<img src="' . $this->get_bio_image_url() . '"/>';
	}


	public function delete($id = false) {
		if (!$id && isset($this->id)) {
			$id = $this->id;
		}
		if ($id && $this->db->delete($this->table_name, array('id' => $id), array('%d'))) {
			$this->db->delete(LATEPOINT_TABLE_AGENTS_SERVICES, array('agent_id' => $id), array('%d'));
			$this->db->delete(LATEPOINT_TABLE_WORK_PERIODS, array('agent_id' => $id), array('%d'));
			$this->db->delete(LATEPOINT_TABLE_AGENT_META, array('object_id' => $id), array('%d'));
			return true;
		} else {
			return false;
		}
	}


}