<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

/**
 *
 * @var $target_date OsWpDateTime
 * @var $today_date OsWpDateTime
 * @var $calendar_settings array
 * @var $locations OsLocationModel[]
 * @var $services OsServiceModel[]
 * @var $agents OsAgentModel[]
 * @var $categorized_services_list array
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if(!empty($services) && !empty($agents)){ ?>
	<div class="calendar-wrapper" data-view="<?php echo esc_attr($calendar_settings['view']); ?>">
		<form class="os-calendar-settings-form" action="">
			<div class="os-calendar-settings-main">
				<div class="os-current-month-label-w calendar-mobile-controls">
					<div class="os-current-month-label">
						<div class="current-month">
							<?php echo esc_html($top_date_label); ?>
						</div>
						<div class="current-year"><?php echo esc_html($target_date->format('Y')); ?></div>
					</div>
					<div class="cc-actions">

						<a href="#" class="cc-action-today os-calendar-today-btn calendar-load-target-date" data-target-date="<?php echo esc_attr($today_date->format('Y-m-d')); ?>">
							<span><?php esc_html_e('Today', 'latepoint'); ?></span>
						</a>
						<div class="cc-navigate-arrows">
							<a href="#" class="cc-action-prev os-calendar-prev-btn"><i class="latepoint-icon latepoint-icon-arrow-left"></i></a>
							<a href="#" class="cc-action-next os-calendar-next-btn"><i class="latepoint-icon latepoint-icon-arrow-right"></i></a>
						</div>
					</div>
					<div class="cc-view">
						<?php
						$views = ['day' => __('Day', 'latepoint'), 'week' => __('Week', 'latepoint'), 'month' => __('Month', 'latepoint'), 'list' => __('List', 'latepoint')];
						echo OsFormHelper::select_field('calendar_settings[view]', false, $views, $calendar_settings['view']); ?>
					</div>
					<?php if(count($services) > 1 || count($agents) > 1 || count($locations) > 1){ ?>
					<div class="os-calendar-settings-toggler-wrapper">
						<a href="#" class="calendar-settings-toggler"><i class="latepoint-icon latepoint-icon-more-horizontal"></i></a>
					</div>
					<?php } ?>
				</div>
		  </div>
			<div class="os-calendar-settings-extra">
				<?php if(count($services) > 1){ ?>
				<div class="cc-availability-toggler-w">
					<div class="cc-availability-toggler"><?php echo OsFormHelper::toggler_field('calendar_settings[overlay_service_availability]', __('Overlay service hours', 'latepoint'), ($calendar_settings['availability_service_id'] ? true : false), 'cc-service-availability-selector'); ?></div>
					<div class="cc-service-availability-selector" id="cc-service-availability-selector">
						<?php echo OsFormHelper::select_field('calendar_settings[availability_service_id]', __('For:', 'latepoint'), OsUtilHelper::models_to_select_options($services, 'id', 'name'), $calendar_settings['availability_service_id']); ?>
					</div>
				</div>
				<?php } ?>
				<div class="resource-filters-wrapper">
					<?php if(count($services) > 1){ ?>
					<div class="latecheckbox-w">
						<a href="#" class="latecheckbox">
							<div class="filter-label"><?php esc_html_e('Services:', 'latepoint'); ?></div>
							<div class="filter-value"><?php esc_html_e('All', 'latepoint'); ?></div>
						</a>
						<div class="latecheckbox-options-w">
							<?php if(count($services) >= 5){ ?>
								<div class="latecheckbox-filter-input-w">
									<input class="latecheckbox-all-check" type="checkbox" checked/>
									<input class="latecheckbox-filter-input" type="text" placeholder="<?php esc_attr_e('Type to filter...', 'latepoint'); ?>"/>
								</div>
							<?php } ?>
							<div class="latecheckbox-options">
								<?php if(!empty($categorized_services_list)){
									foreach($categorized_services_list as $category){
										if(empty($category['items'])) continue;
										echo '<div class="latecheckbox-group">';
											echo '<div class="latecheckbox-group-heading"><label><input type="checkbox" class="latecheckbox-group-check" checked/><span>'.esc_html($category['name']).'</span></label></div>';
											echo '<div class="latecheckbox-group-options">';
												foreach($category['items'] as $service){
													echo '<div class="latecheckbox-option">';
														echo '<label>';
															echo '<input name="calendar_settings[show_service_ids][]" type="checkbox" checked value="'.esc_attr($service->id).'"/>';
															echo '<span>'.esc_html($service->name).'</span>';
														echo '</label>';
													echo '</div>';
												}
											echo '</div>';
										echo '</div>';
									}
								}else{
									foreach($services as $service) {
										echo '<div class="latecheckbox-option">';
											echo '<label>';
											echo '<input name="calendar_settings[show_service_ids][]" type="checkbox" checked value="' . esc_attr($service->id) . '"/>';
											echo '<span class="late-label">' . esc_html($service->name) . '</span>';
											echo '</label>';
										echo '</div>';
									}
							}
							?>
							</div>
						</div>
					</div>
					<?php } ?>
					<?php if(count($locations) > 1){ ?>
					<div class="latecheckbox-w">
						<a href="#" class="latecheckbox">
							<div class="filter-label"><?php esc_html_e('Locations:', 'latepoint'); ?></div>
							<div class="filter-value"><?php esc_html_e('All', 'latepoint'); ?></div>
						</a>
						<div class="latecheckbox-options-w">
							<?php if(count($locations) >= 5){ ?>
								<div class="latecheckbox-filter-input-w">
									<input class="latecheckbox-all-check" type="checkbox" checked/>
									<input class="latecheckbox-filter-input" type="text" placeholder="<?php esc_attr_e('Type to filter...', 'latepoint'); ?>"/>
								</div>
							<?php } ?>
							<div class="latecheckbox-options">
							<?php foreach($locations as $location){
								echo '<div class="latecheckbox-option">';
									echo '<label>';
										echo '<input name="calendar_settings[show_location_ids][]" type="checkbox" checked value="'.esc_attr($location->id).'"/>';
										echo '<span class="late-label">'.esc_html($location->name).'</span>';
									echo '</label>';
								echo '</div>';
							}
							?>
							</div>
						</div>
					</div>

					<?php } ?>
					<?php if(count($agents) > 1){ ?>
					<div class="latecheckbox-w">
						<a href="#" class="latecheckbox">
							<div class="filter-label"><?php esc_html_e('Agents:', 'latepoint'); ?></div>
							<div class="filter-value"><?php esc_html_e('All', 'latepoint'); ?></div>
						</a>
						<div class="latecheckbox-options-w">
							<?php if(count($agents) >= 5){ ?>
								<div class="latecheckbox-filter-input-w">
									<input class="latecheckbox-all-check" type="checkbox" checked/>
									<input class="latecheckbox-filter-input" type="text" placeholder="<?php esc_attr_e('Type to filter...', 'latepoint'); ?>"/>
								</div>
							<?php } ?>
							<div class="latecheckbox-options">
							<?php foreach($agents as $agent){
								echo '<div class="latecheckbox-option">';
									echo '<label>';
										echo '<input name="calendar_settings[show_agent_ids][]" type="checkbox" checked value="'.esc_attr($agent->id).'"/>';
										echo '<span class="late-avatar" style="background-image: url('.esc_url($agent->get_avatar_url()).')"></span>';
										echo '<span class="late-label">'.esc_html($agent->full_name).'</span>';
									echo '</label>';
								echo '</div>';
							}
							?>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>
				<?php
				echo OsFormHelper::hidden_field('calendar_settings[target_date_string]', $target_date->format('Y-m-d'));
				echo OsFormHelper::hidden_field('calendar_settings[selected_agent_id]', $calendar_settings['selected_agent_id']);
				?>
			</div>
		</form>
		<div class="calendar-view-wrapper" data-route="<?php echo esc_attr(OsRouterHelper::build_route_name('calendars', 'view')); ?>">
			<?php include_once('scopes/_'.$calendar_settings['view'].'.php'); ?>
		</div>
	</div>
<?php
}else{ ?>
  <div class="no-results-w">
    <div class="icon-w"><i class="latepoint-icon latepoint-icon-grid"></i></div>
    <h2><?php esc_html_e('No Agents or Services Created', 'latepoint'); ?></h2>
	  <?php if(OsAuthHelper::is_admin_logged_in()){ ?>
	    <a href="<?php echo esc_url(OsRouterHelper::build_link(['agents', 'new_form'] )); ?>" class="latepoint-btn"><i class="latepoint-icon latepoint-icon-plus-square"></i><span><?php esc_html_e('Create Agent', 'latepoint'); ?></span></a>
	    <a href="<?php echo esc_url(OsRouterHelper::build_link(['services', 'new_form'] )); ?>" class="latepoint-btn"><i class="latepoint-icon latepoint-icon-plus-square"></i><span><?php esc_html_e('Create Service', 'latepoint'); ?></span></a>
		<?php } ?>
  </div>
<?php
}