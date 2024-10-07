<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="os-wizard-complete-icon-w"><i class="latepoint-icon latepoint-icon-checkmark"></i></div>
<h2 class="os-wizard-header"><?php esc_html_e('Setup Complete', 'latepoint'); ?></h2>
<div class="os-wizard-desc">You can now insert a booking button on any page using <span class="shortcode-example">[latepoint_book_button]</span> shortcode or by adding a WordPress block called Booking Button.</div>
<a href="<?php echo esc_url(OsRouterHelper::build_link(OsRouterHelper::build_route_name('dashboard', 'index'))); ?>" class="os-wizard-complete-btn latepoint-btn latepoint-btn-outline latepoint-btn-lg">
	<span><?php esc_html_e('Open Dashboard', 'latepoint'); ?></span>
	<i class="latepoint-icon latepoint-icon-arrow-right"></i>
</a>