<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="latepoint-w">
	<div class="os-form-w latepoint-login-form-w">
		<h4><?php esc_html_e('Login to your account', 'latepoint'); ?></h4>
		<form action="" data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('customer_cabinet', 'do_login')); ?>" data-os-success-action="redirect">
			<?php echo OsFormHelper::text_field('customer_login[email]', __('Email Address', 'latepoint')); ?>
			<?php echo OsFormHelper::password_field('customer_login[password]', __('Password', 'latepoint')); ?>
			<div class="os-form-buttons os-flex">
				<?php echo OsFormHelper::button('submit', __('Log me in', 'latepoint'), 'submit', ['class' => 'latepoint-btn']); ?>
				<a href="#" class="latepoint-btn latepoint-btn-primary latepoint-btn-link" data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('customer_cabinet', 'request_password_reset_token')); ?>" data-os-output-target=".latepoint-login-form-w"><?php esc_html_e('Forgot Password?', 'latepoint'); ?></a>
			</div>
			<?php do_action('latepoint_after_customer_login_form'); ?>
		</form>
	</div>
</div>