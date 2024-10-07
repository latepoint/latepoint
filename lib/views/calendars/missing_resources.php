<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="no-results-w">
  <div class="icon-w"><i class="latepoint-icon latepoint-icon-calendar"></i></div>
  <h2><?php esc_html_e('Looks like you have not set work hours for these resources, or agents you selected do not offer these services.', 'latepoint'); ?></h2>
  <a href="<?php echo esc_url(OsRouterHelper::build_link(OsRouterHelper::build_route_name('settings', 'general'))); ?>" class="latepoint-btn"><i class="latepoint-icon latepoint-icon-settings"></i><span><?php esc_html_e('Edit Work Hours', 'latepoint'); ?></span></a>
</div>