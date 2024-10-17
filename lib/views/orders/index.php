<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

/* @var $orders OsOrderModel[] */
/* @var $showing_from int */
/* @var $showing_to int */
/* @var $total_orders int */
/* @var $per_page int */
/* @var $total_pages int */
/* @var $current_page_number int */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


?>
<?php if ($orders) { ?>
	<div class="table-with-pagination-w has-scrollable-table">
		<div class="os-pagination-w with-actions">
			<div class="table-heading-w">
				<h2 class="table-heading"><?php esc_html_e('Orders', 'latepoint'); ?></h2>
				<div
					class="pagination-info"><?php echo esc_html__('Showing', 'latepoint') . ' <span class="os-pagination-from">' . esc_html($showing_from) . '</span>-<span class="os-pagination-to">' . esc_html($showing_to) . '</span> ' . esc_html__('of', 'latepoint') . ' <span class="os-pagination-total">' . esc_html($total_orders) . '</span>'; ?></div>
			</div>
			<div class="mobile-table-actions-trigger"><i class="latepoint-icon latepoint-icon-more-horizontal"></i></div>
			<div class="table-actions">
                <?php if (OsSettingsHelper::can_download_records_as_csv()) { ?>
                    <a href="<?php echo esc_url(OsRouterHelper::build_admin_post_link(['orders', 'index'])); ?>" target="_blank"
                       class="latepoint-btn latepoint-btn-outline latepoint-btn-grey download-csv-with-filters"><i
                                class="latepoint-icon latepoint-icon-download"></i><span><?php esc_html_e('Download .csv', 'latepoint'); ?></span></a>
                <?php } ?>

			</div>
		</div>
		<div class="os-orders-list">
			<div class="os-scrollable-table-w">
				<div class="os-table-w os-table-compact">
					<table class="os-table os-reload-on-booking-update os-scrollable-table"
					       data-route="<?php echo esc_attr(OsRouterHelper::build_route_name('orders', 'index')); ?>">
						<thead>
						<tr>
							<th><?php esc_html_e('ID', 'latepoint'); ?></th>
							<th><?php esc_html_e('Customer', 'latepoint'); ?></th>
							<th><?php esc_html_e('Total', 'latepoint'); ?></th>
							<th><?php esc_html_e('Status', 'latepoint'); ?></th>
							<th><?php esc_html_e('Payment Status', 'latepoint'); ?></th>
							<th><?php esc_html_e('Fulfillment Status', 'latepoint'); ?></th>
							<th><?php esc_html_e('Confirmation', 'latepoint'); ?></th>
							<th><?php esc_html_e('Date', 'latepoint'); ?></th>
						</tr>
						<tr>
							<th><?php echo OsFormHelper::text_field('filter[id]', false, '', ['placeholder' => __('ID', 'latepoint'), 'class' => 'os-table-filter', 'style' => 'width: 60px;']); ?></th>
							<th><?php echo OsFormHelper::text_field('filter[customer][full_name]', false, '', ['placeholder' => __('Customer Name', 'latepoint'), 'class' => 'os-table-filter']); ?></th>
							<th><?php echo OsFormHelper::text_field('filter[total]', false, '', ['placeholder' => __('Total', 'latepoint'), 'class' => 'os-table-filter']); ?></th>
							<th><?php echo OsFormHelper::select_field('filter[status]', false, OsOrdersHelper::get_order_statuses_list(), '', ['placeholder' => __('Show All', 'latepoint'), 'class' => 'os-table-filter']); ?></th>
							<th><?php echo OsFormHelper::select_field('filter[payment_status]', false, OsOrdersHelper::get_order_payment_statuses_list(), '', ['placeholder' => __('Show All', 'latepoint'), 'class' => 'os-table-filter']); ?></th>
							<th><?php echo OsFormHelper::select_field('filter[fulfillment_status]', false, OsOrdersHelper::get_fulfillment_statuses_list(), '', ['placeholder' => __('Show All', 'latepoint'), 'class' => 'os-table-filter']); ?></th>
							<th><?php echo OsFormHelper::text_field('filter[confirmation_code]', false, '', ['placeholder' => __('Confirmation', 'latepoint'), 'class' => 'os-table-filter']); ?></th>
							<th>
								<div class="os-form-group">
									<div class="os-date-range-picker os-table-filter-datepicker" data-can-be-cleared="yes"
									     data-no-value-label="<?php esc_attr_e('Filter By Date', 'latepoint'); ?>"
									     data-clear-btn-label="<?php esc_attr_e('Reset Date Filtering', 'latepoint'); ?>">
										<span class="range-picker-value"><?php esc_html_e('Filter By Date', 'latepoint'); ?></span>
										<i class="latepoint-icon latepoint-icon-chevron-down"></i>
										<input type="hidden" class="os-table-filter os-datepicker-date-from" name="filter[created_at_from]"
										       value=""/>
										<input type="hidden" class="os-table-filter os-datepicker-date-to" name="filter[created_at_to]"
										       value=""/>
									</div>
								</div>
							</th>
						</tr>
						</thead>
						<tbody>
						<?php include '_table_body.php'; ?>
						</tbody>
						<tfoot>
						<tr>
							<th><?php esc_html_e('ID', 'latepoint'); ?></th>
							<th><?php esc_html_e('Customer', 'latepoint'); ?></th>
							<th><?php esc_html_e('Total', 'latepoint'); ?></th>
							<th><?php esc_html_e('Status', 'latepoint'); ?></th>
							<th><?php esc_html_e('Payment Status', 'latepoint'); ?></th>
							<th><?php esc_html_e('Fulfillment Status', 'latepoint'); ?></th>
							<th><?php esc_html_e('Confirmation', 'latepoint'); ?></th>
							<th><?php esc_html_e('Date', 'latepoint'); ?></th>
						</tr>
						</tfoot>
					</table>
				</div>
			</div>
			<div class="os-pagination-w">
				<div
					class="pagination-info"><?php echo esc_html__('Showing', 'latepoint') . ' <span class="os-pagination-from">' . esc_html($showing_from) . '</span>-<span class="os-pagination-to">' . esc_html($showing_to) . '</span> ' . esc_html__('of', 'latepoint') . ' <span class="os-pagination-total">' . esc_html($total_orders) . '</span>'; ?></div>
				<div class="pagination-page-select-w">
					<label for="tablePaginationPageSelector"><?php esc_html_e('Page:', 'latepoint'); ?></label>
					<select id="tablePaginationPageSelector" name="page" class="pagination-page-select">
						<?php
						for ($i = 1; $i <= $total_pages; $i++) {
							$selected = ($current_page_number == $i) ? 'selected' : '';
							echo '<option ' . esc_attr($selected) . '>' . esc_html($i) . '</option>';
						} ?>
					</select>
				</div>
			</div>
		</div>

	</div>
<?php } else { ?>
	<div class="no-results-w">
		<div class="icon-w"><i class="latepoint-icon latepoint-icon-credit-card"></i></div>
		<h2><?php esc_html_e('No Orders Found', 'latepoint'); ?></h2>
		<a href="#" <?php echo OsOrdersHelper::quick_order_btn_html(); ?> class="latepoint-btn"><i class="latepoint-icon latepoint-icon-plus"></i><span><?php esc_html_e('Create an Order', 'latepoint'); ?></span></a>
	</div>
<?php } ?>