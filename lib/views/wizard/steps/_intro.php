<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="os-wizard-logo-w"><i class="latepoint-icon latepoint-icon-lp-logo"></i></div>
<h2 class="os-wizard-header"><?php esc_html_e('Setup Wizard', 'latepoint'); ?></h2>
<div class="os-wizard-desc"><?php esc_html_e('Thank you for installing LatePoint, we will walk you through a quick setup process to add services and set working hours for your business.', 'latepoint'); ?></div>
<a href="#" data-route-name="<?php echo esc_attr(OsRouterHelper::build_route_name('wizard', 'next_step')); ?>" class="os-wizard-next-btn os-wizard-trigger-next-btn latepoint-btn latepoint-btn-lg latepoint-btn-outline latepoint-btn-lg"><span><?php esc_html_e('Get Started', 'latepoint'); ?></span><i class="latepoint-icon latepoint-icon-arrow-right"></i></a>