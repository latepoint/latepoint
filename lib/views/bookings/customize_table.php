<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<form class="latepoint-lightbox-wrapper-form" action="" data-os-success-action="reload" data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('settings', 'save_columns_for_bookings_table')); ?>">
<div class="latepoint-lightbox-heading">
	<h2><?php esc_html_e('Show Additional Columns', 'latepoint'); ?></h2>
</div>
<div class="latepoint-lightbox-content">
		<div class="table-fields-selector-w">
			<h3><span><?php esc_html_e('Customer Fields', 'latepoint'); ?></span><span></span></h3>
			<div class="table-fields-selector-column">
				<?php foreach($available_columns['customer'] as $column_key => $column_label){
					$selected = isset($selected_columns['customer']) ? in_array($column_key, $selected_columns['customer']) : false; 
					echo OsFormHelper::toggler_field('selected_columns[customer]['.$column_key.']', $column_label, $selected);
				} ?>
			</div>
			<h3><span><?php esc_html_e('Booking Fields', 'latepoint'); ?></span><span></span></h3>
			<div class="table-fields-selector-column">
				<?php foreach($available_columns['booking'] as $column_key => $column_label){
					$selected = isset($selected_columns['booking']) ? in_array($column_key, $selected_columns['booking']) : false;
					echo OsFormHelper::toggler_field('selected_columns[booking]['.$column_key.']', $column_label, $selected);
				} ?>
			</div>
		</div>
</div>
<div class="latepoint-lightbox-footer">
	<button type="submit" class="latepoint-btn latepoint-btn-block latepoint-btn-lg latepoint-btn-outline"><?php esc_html_e('Save Table Columns', 'latepoint'); ?></button>
</div>
</form>
