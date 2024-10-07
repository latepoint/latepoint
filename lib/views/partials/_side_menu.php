<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="latepoint-side-menu-w side-menu-full">
	<div class="side-menu-top-part-w">
		<a href="<?php echo esc_url(OsRouterHelper::build_link(['dashboard', 'index'])); ?>" class="logo-w">
			<img src="<?php echo esc_attr(LATEPOINT_IMAGES_URL . 'logo.svg'); ?>" width="25" height="25" alt="LatePoint Dashboard">
		</a>
        <a href="#" title="<?php esc_attr_e('Menu', 'latepoint'); ?>" class="latepoint-mobile-top-menu-trigger">
            <i class="latepoint-icon latepoint-icon-menu"></i>
        </a>
		<div class="latepoint-top-user-info-w">
			<div class="avatar-w top-user-info-toggler" style="background-image: url('<?php echo esc_url(OsAuthHelper::get_current_user()->get_avatar_url()); ?>');"></div>
            <div class="mobile-user-info-w">
                <div class="m-avatar-w" style="background-image: url('<?php echo esc_url(OsAuthHelper::get_current_user()->get_avatar_url()); ?>');"></div>
                <div class="muid-info">
                    <div class="muid-info-username"><?php echo esc_html(OsAuthHelper::get_current_user()->get_display_name()); ?></div>
                    <div class="muid-info-usertype"><?php echo esc_html(OsAuthHelper::get_current_user()->get_user_type_label()); ?></div>
                </div>
                <div class="m-logout">
                    <a href="<?php echo esc_url(wp_logout_url()); ?>">
                        <i class="latepoint-icon latepoint-icon-cancel"></i>
                        <span><?php esc_html_e('Logout', 'latepoint'); ?></span>
                    </a>
                </div>
            </div>
			<div class="latepoint-user-info-dropdown">
				<a href="#" class="latepoint-user-info-close top-user-info-toggler"><i class="latepoint-icon latepoint-icon-minus"></i></a>
				<div class="latepoint-uid-head">
					<div class="uid-avatar-w">
						<div class="uid-avatar"
						     style="background-image: url('<?php echo esc_url(OsAuthHelper::get_current_user()->get_avatar_url()); ?>');"></div>
					</div>
					<div class="uid-info">
						<div class="uid-info-username"><?php echo esc_html(OsAuthHelper::get_current_user()->get_display_name()); ?></div>
						<div class="uid-info-usertype"><?php echo esc_html(OsAuthHelper::get_current_user()->get_user_type_label()); ?></div>
					</div>
				</div>
				<?php do_action('latepoint_top_bar_mobile_after_user'); ?>
				<ul>
					<?php if (OsAuthHelper::get_current_user()->get_link_to_settings()) { ?>
						<li>
							<a href="<?php echo esc_url(OsAuthHelper::get_current_user()->get_link_to_settings()); ?>">
								<i class="latepoint-icon latepoint-icon-cog1"></i>
								<span><?php esc_html_e('Settings', 'latepoint'); ?></span>
							</a>
						</li>
					<?php } ?>
					<li>
						<a href="<?php echo esc_url(wp_logout_url()); ?>">
							<i class="latepoint-icon latepoint-icon-cancel"></i>
							<span><?php esc_html_e('Logout', 'latepoint'); ?></span>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<ul class="side-menu">
		<?php
		$side_menu_items = OsMenuHelper::get_side_menu_items();
		foreach($side_menu_items as $menu_item){
			if(empty($menu_item['label'])){
				if(isset($menu_item['small_label'])){
					echo '<li class="menu-spacer with-label"><span>'.esc_html($menu_item['small_label']).'<span></li>';
				}else{
					echo '<li class="menu-spacer"></li>';
				}
				continue;
			} 
			$sub_menu_html = '';
			$is_active = OsRouterHelper::link_has_route($route_name, $menu_item['link']);


			if(isset($menu_item['children'])){
				if(count($menu_item['children']) > 1){
					$sub_menu_html.= '<ul class="side-sub-menu">';
					$sub_menu_html.= '<li class="side-sub-menu-header">'.esc_html($menu_item['label']).'</li>';
					foreach($menu_item['children'] as $child_menu_item){
						if(OsRouterHelper::link_has_route($route_name, $child_menu_item['link'])){
							$is_active = true;
							$sub_item_active_class = 'sub-item-is-active';
						}else{
							$sub_item_active_class = '';
						}
						$highlight_class = (isset($child_menu_item['show_notice']) && $child_menu_item['show_notice']) ? ' latepoint-show-notice ' : '';
						$sub_menu_html.= '<li class="'.esc_attr($highlight_class.$sub_item_active_class).'"><a href="'.esc_url($child_menu_item['link']).'"><span>'.esc_html($child_menu_item['label']).'</span></a></li>';
					}
					$sub_menu_html.= '</ul>';
				}else{
					$sub_menu_html.= '<ul class="side-sub-menu only-menu-header">';
					$sub_menu_html.= '<li class="side-sub-menu-header">'.esc_html($menu_item['children'][0]['label']).'</li>';
					$sub_menu_html.= '</ul>';
				}
			}else{
				$sub_menu_html.= '<ul class="side-sub-menu only-menu-header">';
				$sub_menu_html.= '<li class="side-sub-menu-header">'.esc_html($menu_item['label']).'</li>';
				$sub_menu_html.= '</ul>';
			}
			?>
			<li class="<?php if(isset($menu_item['show_notice']) && $menu_item['show_notice']) echo ' latepoint-show-notice ';?><?php if(isset($menu_item['children']) && (count($menu_item['children']) > 1)) echo ' has-children'; ?><?php if($is_active) echo ' menu-item-is-active'; ?>">
				<a href="<?php echo esc_url($menu_item['link']); ?>">
					<i class="<?php echo esc_attr($menu_item['icon']); ?>"></i>
					<span><?php echo esc_html($menu_item['label']); ?></span>
				</a>
				<?php echo $sub_menu_html; ?>
			</li>
		<?php } ?>
		<?php if(OsAuthHelper::is_admin_logged_in()){ ?>
			<li class="back-to-wp-item">
				<a href="<?php echo esc_url(get_admin_url()); ?>"><i class="latepoint-icon latepoint-icon-wordpress"></i><span><?php esc_html_e('Back to WordPress', 'latepoint'); ?></span></a>
				<ul class="side-sub-menu only-menu-header"><li class="side-sub-menu-header"><?php esc_html_e('Back to WordPress', 'latepoint'); ?></li></ul>
			</li>
		<?php } ?>

	</ul>
	<?php if(OsAuthHelper::is_admin_logged_in()){ ?>
		<a class="back-to-wp-link" href="<?php echo esc_url(get_admin_url()); ?>">
			<i class="latepoint-icon latepoint-icon-wordpress"></i>
			<span><?php esc_html_e('back to WordPress', 'latepoint'); ?></span>
		</a>
	<?php } ?>
</div>