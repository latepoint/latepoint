<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="os-intro-full-screen-w">
	<div class="os-intro-full-screen-i">
		<a href="<?php echo esc_url(OsRouterHelper::build_link(OsRouterHelper::build_route_name('dashboard', 'index'))); ?>" class="os-intro-full-screen-close-trigger"><span><?php esc_html_e('Dismiss', 'latepoint'); ?></span><i class="latepoint-icon latepoint-icon-x"></i></a>
		<div class="os-intro-heading">
			Version 5
		</div>
		<div class="os-intro-sub-heading">
			What's New
		</div>
		<ul class="list-of-version-improvements">
			<li>
				<div class="improvement-heading">New "Pro Features" addon</div>
				<div class="improvement-description">To simplify plugin management we've merged 12 addons into a single "Pro Features" addon. Please deactivate deprecated addons before installing "Pro Features" addon. <br><br><span style="background-color: #ffd05f; color: #000;">Important!</span> Please make sure to read our <a href="https://wpdocs.latepoint.com/how-to-upgrade-to-version-5/" target="_blank">upgrade guide</a> which lists addons that have to be deactivated.</div>
			</li>
			<li>
				<div class="improvement-heading">Refreshed User Interface</div>
				<div class="improvement-description">The new design is sleek, intuitive, and user-friendly, making it easier than ever to navigate and manage appointments.</div>
			</li>
			<li>
				<div class="improvement-heading">Sell Bundles</div>
				<div class="improvement-description">This new feature allows customers to purchase a bundle of bookings and conveniently schedule their appointments later from their customer cabinet. Boost your sales and enhance customer satisfaction with our new bundle and save option!</div>
			</li>
			<li>
				<div class="improvement-heading">Shopping Cart</div>
				<div class="improvement-description">Now, your customers can book multiple different services in a single order with ease. This streamlined process saves time and makes it more convenient for customers to select and schedule the services they need in one go.</div>
			</li>
			<li>
				<div class="improvement-heading">Visual Customizer</div>
				<div class="improvement-description">Improved booking form customizer lets you edit text directly on the preview, it also helps organize each step settings and show them whenever the step is selected, which lets your preview changes in real time.</div>
			</li>
		</ul>
	</div>
  <div class="os-intro-full-screen-footer">
    <a href="<?php echo esc_attr(OsRouterHelper::build_link(['dashboard', 'index'])); ?>" class="latepoint-btn latepoint-btn-lg"><span><?php esc_html_e('Start Using Version 5', 'latepoint'); ?></span> <i class="latepoint-icon latepoint-icon-arrow-right"></i></a>
  </div>
</div>