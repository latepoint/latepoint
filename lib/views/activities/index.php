<?php
/* @var $activities OsActivityModel[] */
/* @var $showing_from int */
/* @var $showing_to int */
/* @var $total_activities int */
/* @var $per_page int */
/* @var $total_pages int */
/* @var $current_page_number int */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<?php if($activities){ ?>

  <div class="table-with-pagination-w has-scrollable-table">
    <div class="os-pagination-w with-actions">

	    <div class="table-heading-w">
	      <div class="pagination-info"><?php echo esc_html__('Showing', 'latepoint'). ' <span class="os-pagination-from">'. esc_html($showing_from) . '</span>-<span class="os-pagination-to">'. esc_html($showing_to) .'</span> '.esc_html__('of', 'latepoint').' <span class="os-pagination-total">'. esc_html($total_activities). '</span>'; ?></div>
	    </div>
	    <div class="mobile-table-actions-trigger"><i class="latepoint-icon latepoint-icon-more-horizontal"></i></div>
      <div class="table-actions">
        <a data-os-success-action="reload" data-os-params="<?php echo esc_attr(OsUtilHelper::build_os_params([], 'clear_activities')); ?>" data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('activities', 'clear_all')); ?>" data-os-prompt="<?php esc_attr_e('Are you sure you want to clear the activities log?', 'latepoint'); ?>" href="#" class="latepoint-btn latepoint-btn-outline latepoint-btn-danger latepoint-btn-sm"><i class="latepoint-icon latepoint-icon-trash"></i><span><?php esc_html_e('Clear All', 'latepoint'); ?></span></a>
        <a href="<?php echo esc_url(OsRouterHelper::build_admin_post_link(['activities', 'export'])); ?>" target="_blank" class="latepoint-btn latepoint-btn-outline latepoint-btn-sm"><i class="latepoint-icon latepoint-icon-download"></i><span><?php esc_html_e('Export', 'latepoint'); ?></span></a>
      </div>
    </div>
	<div class="activities-index">
	  <div class="os-scrollable-table-w">
		<div class="os-table-w os-table-compact">
			<table class="os-table os-reload-on-booking-update os-scrollable-table" data-route="<?php echo esc_attr(OsRouterHelper::build_route_name('activities', 'index')); ?>">
				<thead>
					<tr>
						<th><?php esc_html_e('Type', 'latepoint'); ?></th>
						<th><?php esc_html_e('Action By', 'latepoint'); ?></th>
						<th><?php esc_html_e('Date/Time', 'latepoint'); ?></th>
						<th><?php esc_html_e('Action', 'latepoint'); ?></th>
					</tr>
          <tr>
	          <th><?php echo OsFormHelper::select_field('filter[code]', false, OsActivitiesHelper::get_codes(), '', ['placeholder' => __('All Types', 'latepoint'),'class' => 'os-table-filter']); ?></th>
	          <th><?php echo OsFormHelper::text_field('filter[initiated_by_id]', false, '', ['placeholder' => __('User ID', 'latepoint'), 'class' => 'os-table-filter']); ?></th>
	          <th>
		          <div class="os-form-group">
			          <div class="os-date-range-picker os-table-filter-datepicker" data-can-be-cleared="yes" data-no-value-label="<?php esc_attr_e('Filter By Date', 'latepoint'); ?>" data-clear-btn-label="<?php esc_attr_e('Reset Date Filtering', 'latepoint'); ?>">
				          <span class="range-picker-value"><?php esc_html_e('Filter By Date', 'latepoint'); ?></span>
				          <i class="latepoint-icon latepoint-icon-chevron-down"></i>
				          <input type="hidden" class="os-table-filter os-datepicker-date-from" name="filter[created_at_from]" value=""/>
				          <input type="hidden" class="os-table-filter os-datepicker-date-to" name="filter[created_at_to]" value=""/>
			          </div>
		          </div>
	          </th>
	          <th></th>
          </tr>
				</thead>
				<tbody>
				<?php include '_table_body.php'; ?>
				</tbody>
				<tfoot>
					<tr>
						<th><?php esc_html_e('Type', 'latepoint'); ?></th>
						<th><?php esc_html_e('Action By', 'latepoint'); ?></th>
						<th><?php esc_html_e('Date/Time', 'latepoint'); ?></th>
						<th><?php esc_html_e('Action', 'latepoint'); ?></th>
					</tr>
				</tfoot>
				</table>
			</div>
	</div>
  <div class="os-pagination-w">
    <div class="pagination-info"><?php echo esc_html__('Showing activities', 'latepoint'). ' <span class="os-pagination-from">'. esc_html($showing_from) . '</span> '.esc_html__('to', 'latepoint').' <span class="os-pagination-to">'. esc_html($showing_to) .'</span> '.esc_html__('of', 'latepoint').' <span class="os-pagination-total">'. esc_html($total_activities). '</span>'; ?></div>
    <div class="pagination-page-select-w">
      <label for="tablePaginationPageSelector"><?php esc_html_e('Page:', 'latepoint'); ?></label>
      <select id="tablePaginationPageSelector" name="page" class="pagination-page-select">
        <?php
        for($i = 1; $i <= $total_pages; $i++){
          $selected = ($current_page_number == $i) ? 'selected' : '';
          echo '<option '.esc_attr($selected).'>'.esc_html($i).'</option>';
        } ?>
      </select>
    </div>
  </div>
  </div>
	</div>
<?php }else{ ?>
  <div class="no-results-w">
    <div class="icon-w"><i class="latepoint-icon latepoint-icon-bell"></i></div>
    <h2><?php esc_html_e('No Activity', 'latepoint'); ?></h2>
  </div>
<?php } ?>