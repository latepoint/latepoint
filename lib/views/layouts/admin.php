<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="latepoint-all-wrapper <?php echo esc_attr(implode(' ', $extra_css_classes)); ?>">
	<?php do_action('latepoint_all_wrapper_open'); ?>
	<div class="latepoint-content-and-menu-w">
		<?php include(LATEPOINT_VIEWS_PARTIALS_ABSPATH . '_side_menu.php'); ?>
		<div class="latepoint-content-w">
			<?php include(LATEPOINT_VIEWS_PARTIALS_ABSPATH . '_top_bar.php'); ?>
			<?php if(isset($pre_page_back_link) && !empty($pre_page_back_link)){ ?>
				<div class="pre-page-back-link-w">
					<a href="<?php echo esc_url($pre_page_back_link); ?>" class="pre-page-back-link">
						<i class="latepoint-icon latepoint-icon-arrow-left"></i>
						<span><?php esc_html_e('Back to agent profile', 'latepoint');?></span>
					</a>
				</div>
			<?php } ?>
			<?php if(isset($pre_page_header) && !empty($pre_page_header)){ ?>
				<h1 class="pre-page-header"><?php echo esc_html($pre_page_header); ?></h1>
			<?php } ?>
			<?php if(isset($page_header) && !empty($page_header)){ ?>
				<div class="page-header-w">
					<?php if(isset($page_header) && !empty($page_header)){
						if(is_array($page_header)){
							if(count($page_header) > 1){
								$condensed = count($page_header) > 5 ? 'condensed' : '';
								echo '<div class="os-page-tabs-w '.esc_attr($condensed).'">';
								echo '<ul class="os-page-tabs">';
								foreach($page_header as $tab){
									$is_active_class = OsRouterHelper::link_has_route($route_name, $tab['link']) ? 'os-page-tab-active' : '';
									$highlight_class = (isset($tab['show_notice']) && $tab['show_notice']) ? 'latepoint-show-notice' : '';
									echo '<li class="'.esc_attr($is_active_class).' '.esc_attr($highlight_class).'"><a href="'.esc_url($tab['link']).'">'.esc_html($tab['label']).'</a></li>';
								}
								echo '</div>';
							}else{
								echo '<h1 class="page-header-main">'.esc_html($page_header[0]['label']).'</h1>';
							}
						}else{
							echo '<h1 class="page-header-main">'.esc_html($page_header).'</h1>';
						}
					} ?>
					<?php 
					if(isset($breadcrumbs) && (count($breadcrumbs) > 1)){
						echo '<div class="breadcrumbs-w"><ul class="breadcrumbs">';
						foreach($breadcrumbs as $crumb){
							if($crumb['link']){
								echo '<li><a href="'.esc_url($crumb['link']).'">'.esc_html($crumb['label']).'</a></li>';
							}else{
								echo '<li><span>'.esc_html($crumb['label']).'</span></li>';
							}
						}
						echo '</ul></div>';
					}?>
				</div><?php	
			} ?>
			<div class="latepoint-content <?php echo empty($content_no_padding) ? '' : 'no-padding'; ?>">
				<?php include($view); ?>
			</div>
			<div class="latepoint-template-variables">
				<div class="close-template-variables-panel"><i class="latepoint-icon latepoint-icon-x"></i></div>
				<h3><?php esc_html_e('Available Template Variables', 'latepoint'); ?></h3>
				<div class="latepoint-template-variables-i">
			    <?php include(LATEPOINT_ABSPATH.'lib/views/shared/_template_variables.php'); ?>
				</div>
			</div>
			<div class="latepoint-layout-template-variables">
				<div class="close-layout-template-variables-panel"><i class="latepoint-icon latepoint-icon-x"></i></div>
				<h3><?php esc_html_e('Available Layout Variables', 'latepoint'); ?></h3>
				<div class="latepoint-template-variables-i">
			    <?php include(LATEPOINT_ABSPATH.'lib/views/shared/_business_variables.php'); ?>
				</div>
			</div>
		</div>
	</div>
</div>