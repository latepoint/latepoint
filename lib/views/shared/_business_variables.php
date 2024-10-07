<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="available-vars-block">
  <h4><?php esc_html_e('Business Info', 'latepoint'); ?></h4>
  <ul>
	  <li><span class="var-label"><?php esc_html_e('Customer Cabinet URL:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{customer_dashboard_url}}</span></li>
	  <li><span class="var-label"><?php esc_html_e('Business logo HTML:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{business_logo_image}}</span></li>
	  <li><span class="var-label"><?php esc_html_e('Business logo URL:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{business_logo_url}}</span></li>
	  <li><span class="var-label"><?php esc_html_e('Business Address:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{business_address}}</span></li>
	  <li><span class="var-label"><?php esc_html_e('Business Phone:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{business_phone}}</span></li>
	  <li><span class="var-label"><?php esc_html_e('Business Name:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{business_name}}</span></li>
  </ul>
</div>