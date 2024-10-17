<?php
/** @var $customers OsCustomerModel[] */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if($customers){ ?>
  <div class="table-with-pagination-w has-scrollable-table">
    <div class="os-pagination-w with-actions">
	    <div class="table-heading-w">
			  <h2 class="table-heading"><?php esc_html_e('Customers', 'latepoint'); ?></h2>
	      <div class="pagination-info"><?php echo esc_html__('Showing', 'latepoint'). ' <span class="os-pagination-from">' . esc_html( $showing_from ) . '</span>-<span class="os-pagination-to">' . esc_html($showing_to) . '</span> ' . esc_html__('of', 'latepoint') . ' <span class="os-pagination-total">' . esc_html($total_customers) . '</span>'; ?></div>
	    </div>
	    <div class="mobile-table-actions-trigger"><i class="latepoint-icon latepoint-icon-more-horizontal"></i></div>
      <div class="table-actions">
	    <?php if (OsSettingsHelper::can_download_records_as_csv()) { ?>
            <a href="<?php echo esc_url(OsRouterHelper::build_admin_post_link(OsRouterHelper::build_route_name('customers', 'index') )); ?>" target="_blank" class="latepoint-btn latepoint-btn-outline latepoint-btn-grey download-csv-with-filters"><i class="latepoint-icon latepoint-icon-download"></i><span><?php esc_html_e('Download .csv', 'latepoint'); ?></span></a>
        <?php } ?>
        <?php if(OsAuthHelper::wp_users_as_customers()){ ?>
          <?php $not_connected_count = OsCustomerHelper::count_customers_not_connected_to_wp_users(); ?>
          <?php if($not_connected_count){ ?>
            <a href="#" data-os-success-action="reload" data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('customers', 'connect_all_to_wp_users')); ?>" class="latepoint-btn latepoint-btn-outline latepoint-btn-grey"><i class="latepoint-icon latepoint-icon-wordpress"></i><span><?php esc_html_e('Connect to WP Users', 'latepoint'); ?><?php echo ' ['.esc_html($not_connected_count).']'; ?></span></a>
          <?php } ?>
        <?php } ?>
        <a href="#" <?php echo OsCustomerHelper::quick_customer_btn_html(); ?> class="latepoint-btn latepoint-btn-outline latepoint-btn-grey"><i class="latepoint-icon latepoint-icon-plus"></i><span><?php esc_html_e('New Customer', 'latepoint'); ?></span></a>
      </div>
    </div>
    <div class="os-customers-list">
	    <div class="os-scrollable-table-w">
      <div class="os-table-w os-table-compact">
        <table class="os-table os-reload-on-booking-update os-scrollable-table" data-route="<?php echo OsRouterHelper::build_route_name('customers', 'index'); ?>">
          <thead>
            <tr>
              <th><?php esc_html_e('ID', 'latepoint'); ?></th>
              <th class="text-left"><?php esc_html_e('Full Name', 'latepoint'); ?></th>
              <th><?php esc_html_e('Phone', 'latepoint'); ?></th>
              <th><?php esc_html_e('Email', 'latepoint'); ?></th>
              <?php if(OsSettingsHelper::is_using_social_login()) echo '<th>'.esc_html__('Social', 'latepoint').'</th>'; ?>
              <th><?php esc_html_e('Total Apps', 'latepoint'); ?></th>
              <th><?php esc_html_e('Next App', 'latepoint'); ?></th>
              <th><?php esc_html_e('Time to Next', 'latepoint'); ?></th>
              <?php if(OsAuthHelper::wp_users_as_customers()) echo '<th>'.esc_html__('WP User ID', 'latepoint').'</th>'; ?>
              <th><?php esc_html_e('Registered On', 'latepoint'); ?></th>
            </tr>
            <tr>
              <th><?php echo OsFormHelper::text_field('filter[id]', false, '', ['style' => 'width: 40px;', 'class' => 'os-table-filter', 'placeholder' => __('ID', 'latepoint')]); ?></th>
              <th><?php echo OsFormHelper::text_field('filter[customer]', false, '', ['class' => 'os-table-filter', 'placeholder' => __('Search by Name', 'latepoint')]); ?></th>
              <th><?php echo OsFormHelper::text_field('filter[phone]', false, '', ['class' => 'os-table-filter', 'placeholder' => __('Phone...', 'latepoint')]); ?></th>
              <th><?php echo OsFormHelper::text_field('filter[email]', false, '', ['class' => 'os-table-filter', 'placeholder' => __('Search by Email', 'latepoint')]); ?></th>
              <th></th>
              <th></th>
              <th></th>
              <?php if(OsSettingsHelper::is_using_social_login()) echo '<th></th>'; ?>
              <?php if(OsAuthHelper::wp_users_as_customers()) echo '<th></th>'; ?>
              <th>
                <div class="os-form-group">
                  <div class="os-date-range-picker os-table-filter-datepicker" data-can-be-cleared="yes" data-no-value-label="<?php esc_attr_e('Filter By Date', 'latepoint'); ?>" data-clear-btn-label="<?php esc_attr_e('Reset Date Filtering', 'latepoint'); ?>">
                    <span class="range-picker-value"><?php esc_html_e('Filter By Date', 'latepoint'); ?></span>
                    <i class="latepoint-icon latepoint-icon-chevron-down"></i>
                    <input type="hidden" class="os-table-filter os-datepicker-date-from" name="filter[registration_date_from]" value=""/>
                    <input type="hidden" class="os-table-filter os-datepicker-date-to" name="filter[registration_date_to]" value=""/>
                  </div>
                </div>
              </th>
            </tr>
          </thead>
          <tbody>
            <?php include('_table_body.php'); ?>
          </tbody>
          <tfoot>
            <tr>
              <th><?php esc_html_e('ID', 'latepoint'); ?></th>
              <th class="text-left"><?php esc_html_e('Full Name', 'latepoint'); ?></th>
              <th><?php esc_html_e('Phone', 'latepoint'); ?></th>
              <th><?php esc_html_e('Email', 'latepoint'); ?></th>
              <?php if(OsSettingsHelper::is_using_social_login()) echo '<th>'.esc_html__('Social', 'latepoint').'</th>'; ?>
              <th><?php esc_html_e('Total Apps', 'latepoint'); ?></th>
              <th><?php esc_html_e('Next App', 'latepoint'); ?></th>
              <th><?php esc_html_e('Time to Next', 'latepoint'); ?></th>
              <?php if(OsAuthHelper::wp_users_as_customers()) echo '<th>'.esc_html__('WP User ID', 'latepoint').'</th>'; ?>
              <th><?php esc_html_e('Registered On', 'latepoint'); ?></th>
            </tr>
          </tfoot>
        </table>
      </div>
	    </div>
    </div>
    <div class="os-pagination-w">
      <div class="pagination-info"><?php echo esc_html__('Showing', 'latepoint'). ' <span class="os-pagination-from">'. esc_html($showing_from) . '</span>-<span class="os-pagination-to">'. esc_html($showing_to) .'</span> '.esc_html__('of', 'latepoint').' <span class="os-pagination-total">'. esc_html($total_customers). '</span>'; ?></div>
      <div class="pagination-page-select-w">
        <label for="tablePaginationPageSelector"><?php esc_html_e('Page:', 'latepoint'); ?></label>
        <select id="tablePaginationPageSelector" name="page" class="pagination-page-select">
          <?php
          for($i = 1; $i <= $total_pages; $i++){
            $selected = ($current_page_number == $i) ? 'selected' : '';
            echo '<option '.esc_html($selected).'>'.esc_html($i).'</option>';
          } ?>
        </select>
      </div>
    </div>
    </div>
<?php }else{ ?>
  <div class="no-results-w">
    <div class="icon-w"><i class="latepoint-icon latepoint-icon-users"></i></div>
    <h2><?php esc_html_e('No Customers Found', 'latepoint'); ?></h2>
    <a href="#" <?php echo OsCustomerHelper::quick_customer_btn_html(); ?> class="latepoint-btn"><i class="latepoint-icon latepoint-icon-plus"></i><span><?php esc_html_e('Add Customer', 'latepoint'); ?></span></a>
  </div>
<?php } ?>