<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<?php if($customers){
	foreach($customers as $customer){ ?>
    <div class="customer-option" data-os-params="<?php echo esc_attr(OsUtilHelper::build_os_params(['customer_id' => $customer->id])); ?>"
        data-os-after-call="latepoint_quick_order_customer_selected"
        data-os-output-target=".customer-quick-edit-form-w" 
        data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('customers', 'inline_edit_form')); ?>">
      <div class="customer-option-avatar" style="background-image: url(<?php echo esc_url(OsCustomerHelper::get_avatar_url($customer)); ?>)"></div>
      <div class="customer-option-info">
        <h4 class="customer-option-info-name"><span><?php echo preg_replace("/($query)/i", "<span class='os-query-match'>$1</span>", esc_html($customer->full_name)); ?></span></h4>
        <ul>
          <li>
            <?php esc_html_e('Email: ','latepoint'); ?>
            <strong><?php echo preg_replace("/($query)/i", "<span class='os-query-match'>$1</span>", esc_html($customer->email)); ?></strong>
          </li>
          <li>
            <?php esc_html_e('Phone: ','latepoint'); ?>
            <strong><?php echo preg_replace("/($query)/i", "<span class='os-query-match'>$1</span>", esc_html($customer->phone)); ?></strong>
          </li>
        </ul>
      </div>
    </div> 
    <?php
	}
}else{
	echo '<div class="os-no-matched-customers">'.esc_html__('No matches found.', 'latepoint').'</div>';
} ?>