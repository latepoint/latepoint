<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="pro-feature-banner">
	<h4>&#128274; <?php esc_html_e('Pro Feature', 'latepoint');?></h4>
	<div class="pro-desc">
		<div><?php esc_html_e('This feature is available with a paid version, along with 20+ other premium features.', 'latepoint');?></div>
		<div class="pro-premium-features-list-wrapper">
			<div><?php esc_html_e('Upgrading to a paid version unlocks access to the following features:', 'latepoint');?></div>
			<ul>
				<li><?php esc_html_e('Coupons', 'latepoint'); ?></li>
				<li><?php esc_html_e('Bundles', 'latepoint'); ?></li>
				<li><?php esc_html_e('Timezone Selector', 'latepoint'); ?></li>
				<li><?php esc_html_e('Service Categories', 'latepoint'); ?></li>
				<li><?php esc_html_e('Agents', 'latepoint'); ?></li>
				<li><?php esc_html_e('Locations', 'latepoint'); ?></li>
				<li><?php esc_html_e('Messages', 'latepoint'); ?></li>
				<li><?php esc_html_e('Reminders', 'latepoint'); ?></li>
				<li><?php esc_html_e('Webhooks', 'latepoint'); ?></li>
				<li><?php esc_html_e('Service Extras', 'latepoint'); ?></li>
				<li><?php esc_html_e('Custom User Roles', 'latepoint'); ?></li>
				<li><?php esc_html_e('Multiple Service Durations', 'latepoint'); ?></li>
				<li><?php esc_html_e('Taxes', 'latepoint'); ?></li>
				<li><?php esc_html_e('Custom Fields', 'latepoint'); ?></li>
				<li><?php esc_html_e('Group Bookings and more...', 'latepoint'); ?></li>
			</ul>
		</div>
	</div>
	<a href="<?php echo esc_url(LATEPOINT_UPGRADE_URL); ?>" class="latepoint-pro-link"><?php esc_html_e('Upgrade to paid version', 'latepoint'); ?></a>
	<a href="#" class="latepoint-pro-link-subtle"><?php esc_html_e('Show All premium features', 'latepoint'); ?></a>
</div>