<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div>
	<label for=""><?php esc_html_e('Customer Login', 'latepoint'); ?></label>
	<select name="" id="">
		<option value=""><?php esc_html_e('Select Page', 'latepoint'); ?></option>
		<?php 
		foreach($pages as $page){ 
			echo '<option value="'.esc_attr($page->ID).'">'.esc_html($page->post_title).'</option>';
		}
		?>
	</select>
</div>
<div>
	<label for=""><?php esc_html_e('Customer Profile', 'latepoint'); ?></label>
	<select name="" id="">
		<option value=""><?php esc_html_e('Select Page', 'latepoint'); ?></option>
		<?php 
		foreach($pages as $page){ 
			echo '<option value="'.esc_attr($page->ID).'">'.esc_html($page->post_title).'</option>';
		}
		?>
	</select>
</div>