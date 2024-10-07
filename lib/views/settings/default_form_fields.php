<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="os-section-header"><h3><?php esc_html_e('Default Fields', 'latepoint'); ?></h3></div>
<?php OsSettingsHelper::generate_default_form_fields($default_fields); ?>
<div class="os-section-header"><h3><?php esc_html_e('Custom Fields', 'latepoint'); ?></h3></div>
<?php echo OsUtilHelper::pro_feature_block(esc_html__('To create more fields upgrade to a paid version', 'latepoint')); ?>
