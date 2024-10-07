<?php
/** @var $customers OsCustomerModel[] */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if($customers){
  foreach ($customers as $customer):
	  $next_booking = $customer->get_future_bookings(1, true);
	  ?>
    <tr class="os-clickable-row" <?php echo OsCustomerHelper::quick_customer_btn_html($customer->id); ?>>
      <td class="text-center os-column-faded text-right has-floating-button">
	      <?php echo esc_html($customer->id); ?>
	      <div class="os-floating-button"><i class="latepoint-icon latepoint-icon-edit-3"></i></div>
      </td>
      <td>
        <a class="os-with-avatar" href="#">
          <span class="os-avatar" style="background-image: url(<?php echo esc_url($customer->get_avatar_url()); ?>)"></span>
          <span class="os-name"><?php echo esc_html($customer->full_name); ?></span>
        </a>
      </td>
      <td><?php echo esc_html($customer->phone); ?></td>
      <td style="max-width: 220px; overflow: scroll;"><?php echo esc_html($customer->email); ?></td>
      <?php if(OsSettingsHelper::is_using_social_login()){
        $social_google = $customer->google_user_id ? '<i class="latepoint-customer-google latepoint-icon latepoint-icon-google"></i>' : '';
        $social_facebook = $customer->facebook_user_id ? '<i class="latepoint-customer-facebook latepoint-icon latepoint-icon-facebook"></i>' : '';
          echo '<td>'.$social_facebook.$social_google.'</td>'; 
        }
      ?>
      <td><?php echo esc_html($customer->get_total_bookings_count(true)); ?></td>
      <td><?php echo ($next_booking) ? esc_html($next_booking->nice_start_datetime) : esc_html__('n/a', 'latepoint'); ?></td>
      <td><?php echo ($next_booking) ? $next_booking->time_left : '<span class="time-left is-past">'.esc_html__('Past', 'latepoint').'</span>'; ?></td>
      <?php if(OsAuthHelper::wp_users_as_customers()) echo ($customer->wordpress_user_id) ? '<td><a target="_blank" href="'.esc_url(get_edit_user_link($customer->wordpress_user_id)).'">'.esc_html($customer->wordpress_user_id).'</a></td>' : '<td><div class="not-connected-pill"></div></td>'; ?>
      <td><?php echo esc_html($customer->formatted_created_date()); ?></td>
    </tr>
    <?php 
  endforeach;
}?>