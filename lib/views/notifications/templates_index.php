<?php
/* @var $templates array */
/* @var $grouped_templates array */
/* @var $heading string */
/* @var $action_type string */
/* @var $selected_template_id string */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


?>
<div class="os-form-header">
	<h2><?php echo esc_html($heading); ?></h2>
    <a href="#" class="latepoint-side-panel-close latepoint-side-panel-close-trigger"><i class="latepoint-icon latepoint-icon-x"></i></a>
</div>
<div class="os-form-content no-padding no-overflow">
	<div class="os-templates-wrapper">
		<div class="os-templates-list">
			<div class="template-type-selector-wrapper">
				<div class="template-type-selector" data-user-type="agent"><?php esc_html_e('For Agents', 'latepoint'); ?></div>
                <div class="os-template-items hidden" data-user-type="agent">
                <?php
                foreach($grouped_templates['agent'] as $template){
                    echo '<div data-user-type="'.esc_attr($template['to_user_type']).'" class="os-template-item" data-id="'.esc_attr($template['id']).'">';
                        echo '<div class="os-template-name">'.esc_html($template['name']).'</div>';
                    echo '</div>';
                } ?>
                </div>
				<div class="template-type-selector" data-user-type="customer"><?php esc_html_e('For Customers', 'latepoint'); ?></div>
                <div class="os-template-items hidden" data-user-type="customer">
                <?php
                foreach($grouped_templates['customer'] as $template){
                    echo '<div data-user-type="'.esc_attr($template['to_user_type']).'" class="os-template-item" data-id="'.esc_attr($template['id']).'">';
                        echo '<div class="os-template-name">'.esc_html($template['name']).'</div>';
                    echo '</div>';
                } ?>
                </div>
			</div>
		</div>
		<div class="os-template-previews">
            <div class="os-no-template-selected-message">
                <i class="latepoint-icon latepoint-icon-browser"></i>
                <div><?php esc_html_e('Please select a template from the list on the left to generate a preview.', 'latepoint'); ?></div>
            </div>
            <?php
            foreach($templates as $template) {
                echo '<div class="os-template-preview type-'.esc_attr($action_type).'" data-id="'.esc_attr($template['id']).'" style="'.($template['id'] == $selected_template_id ? '' : 'display: none;').'">';
                switch($action_type){
                    case 'send_email':
                        echo '<div class="os-template-preview-headings">';
                            echo '<div class="os-template-preview-subject"><span class="os-label">'.esc_html__('Subject: ', 'latepoint').'</span><span class="os-value">'.$template['subject'].'</span></div>';
                            echo '<div class="os-template-preview-to"><span class="os-label">'.esc_html__('To:', 'latepoint').'</span><span class="os-value">'.OsReplacerHelper::stylize_vars(esc_html($template['to_email'])).'</span></div>';
                        echo '</div>';
                        echo '<div class="os-template-preview-content">'.OsReplacerHelper::stylize_vars($template['content']).'</div>';
                        break;
                    case 'send_sms':
                        echo '<div class="os-template-preview-content-wrapper">';
                            echo '<div class="os-template-preview-to"><span class="os-label">'.esc_html__('To:', 'latepoint').'</span><span class="os-value">'.esc_html($template['to_phone']).'</span></div>';
                            echo '<div class="os-template-preview-content">'.esc_html($template['content']).'</div>';
                        echo '</div>';
                        break;
                }

                /**
                 * Executed after each notification template preview
                 *
                 * @since 4.7.0
                 * @hook latepoint_after_notification_template_preview
                 *
                 * @param {string} $action_type Type of action being previewed
                 * @param {array} $template Array of template information being previewed
                 * @param {string} $selected_template_id ID of selected template for which preview is to be shown
                 */
                do_action('latepoint_after_notification_template_preview', $action_type, $template, $selected_template_id);
                echo '</div>';
            }
            ?>
            <div class="os-template-use-button-wrapper hidden">
                <button type="button" class="latepoint-btn latepoint-btn-primary latepoint-btn-lg latepoint-btn-block latepoint-select-template-btn" data-action-type="<?php echo esc_attr($action_type); ?>" data-action-id="<?php echo esc_attr($action_id); ?>" data-route="<?php echo esc_attr(OsRouterHelper::build_route_name('processes', 'load_action_settings'));?>">
                    <span><?php esc_html_e('Use this template', 'latepoint'); ?></span>
                    <i class="latepoint-icon latepoint-icon-arrow-right"></i>
                </button>
            </div>
		</div>
	</div>
</div>