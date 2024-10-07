<?php 

class OsServiceHelper {

	/**
	 * @return OsServiceModel[]
	 */
	public static function get_allowed_active_services(): array{
		$agents = new OsServiceModel();
    return $agents->should_be_active()->filter_allowed_records()->get_results_as_models();
	}
	public static function get_max_capacity_by_service_id($service_id){
		$service = new OsServiceModel($service_id);
		return self::get_max_capacity($service);
	}

	public static function get_min_capacity_by_service_id($service_id){
		$service = new OsServiceModel($service_id);
		return self::get_min_capacity($service);
	}

    public static function get_minimum_duration_across_services(){
        $service = new OsServiceModel();
        $services = $service->should_be_active()->get_results_as_models();
        $min_duration = false;
        foreach($services as $service){
            $min_duration = $min_duration ? min($min_duration, $service->duration) : $service->duration;
        }
        return $min_duration;
    }

	public static function get_summary_duration_label($duration){
		if(!$duration) return '';
    if(($duration >= 60) && !OsSettingsHelper::is_on('steps_show_duration_in_minutes')){
      $hours = floor($duration / 60);
      $minutes = $duration % 60;
      $summary_duration_label = $hours.' ';
      $summary_duration_label.= ($hours > 1) ? __('Hours', 'latepoint') : __('Hour', 'latepoint');
      if($minutes) $summary_duration_label.= ', '.$minutes.' '.__('Minutes', 'latepoint');
    }else{
      $summary_duration_label = $duration.' '.__('Minutes', 'latepoint');
    }
		return $summary_duration_label;
	}

	public static function get_max_capacity($service){
		if($service && !empty($service->capacity_max)){
			return $service->capacity_max;
		}
		return 1;
	}

	public static function get_min_capacity($service){
		if($service && !empty($service->capacity_min)){
			return $service->capacity_min;
		}
		return 1;
	}

	public static function get_percent_of_capacity_booked($service, $total_attendees){
		$total_attendees = empty($total_attendees) ? 1 : $total_attendees;
		$max_capacity = self::get_max_capacity($service);
		return min(100, round($total_attendees / $max_capacity * 100));
	}

	public static function get_default_colors(){
		return array('#2752E4', '#C066F1', '#26B7DD', '#E8C634', '#19CED6', '#2FEAA3', '#252a3e', '#8d87a5', '#b9b784');
	}

	public static function get_default_duration_for_service($service_id){
		$service = new OsServiceModel($service_id);
		return $service->duration;
	}

  public static function service_option_html_for_select($service, $selected = false){
  	?>
  	<div class="service-option <?php if($selected) { echo 'selected'; } ?>" 
      data-extra-durations="<?php echo esc_attr(wp_json_encode($service->get_extra_durations())); ?>"
      data-id="<?php echo esc_attr($service->id); ?>"
      data-buffer-before="<?php echo esc_attr($service->buffer_before); ?>"
      data-buffer-after="<?php echo esc_attr($service->buffer_after); ?>"
      data-capacity-min="<?php echo esc_attr($service->capacity_min); ?>"
      data-capacity-max="<?php echo esc_attr($service->capacity_max); ?>"
      data-duration-name="<?php echo esc_attr($service->duration_name); ?>"
      data-duration="<?php echo esc_attr($service->duration); ?>">
        <div class="service-color" style="background-color: <?php echo esc_attr($service->bg_color); ?>"></div>
        <span><?php echo esc_html($service->name); ?></span>
    </div>
    <?php
  }

	/**
	 * @param $agent_id
	 * @return OsServiceModel[]
	 */
  public static function get_services(bool $filter_allowed_records = false): array{
    $services = new OsServiceModel();
    if($filter_allowed_records) $services->filter_allowed_records();
    $services = $services->get_results_as_models();
    return $services;
  }

	/**
	 * @param bool $filter_allowed_records
	 * @param array $service_ids
	 *
	 * @return array
	 */
  public static function get_services_list(bool $filter_allowed_records = false, array $service_ids = []): array{
    $services = new OsServiceModel();
    if($filter_allowed_records) $services->filter_allowed_records();

    if (!empty($service_ids)) {
    	$services->where_in('id', $service_ids);
    }

    $services = $services->get_results_as_models();
    $services_list = [];
    if($services){
	    foreach($services as $service){
	      $services_list[] = ['value' => $service->id, 'label' => $service->name];
	    }
	  }
    return $services_list;
  }

	public static function has_multiple_durations(int $service_id): bool {
		$service = new OsServiceModel($service_id);
		$extra_durations = $service->get_extra_durations();
		return !empty($extra_durations);
	}

}