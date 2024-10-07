<?php

class OsPagesHelper {

	/**
	 * Create Latepoint predefined pages
	 * @return void
	 */
	public static function create_predefined_pages(): void {
		$pages = [
			'customer-cabinet' => array(
				'slug'    => _x( 'customer-cabinet', 'Customer cabinet', 'latepoint' ),
				'title'   => _x( 'Customer Cabinet', 'Customer cabinet', 'latepoint' ),
				'content' => '<!-- wp:latepoint/customer-dashboard --><div class="wp-block-latepoint-customer-dashboard">Customer Dashboard</div><!-- /wp:latepoint/customer-dashboard -->',
				'settings' => ['page_url_customer_dashboard', 'page_url_customer_login']
			)
		];
		foreach ( $pages as $key => $page_settings ) {
			$option = 'latepoint_page_' . $key;
			$page_id = self::create_page( $page_settings, $option);
			if ($page_id) {
				update_option( $option, $page_id );
			}
		}
	}


	/**
	 * @param array $page_settings
	 * @param string $option
	 *
	 * @return int|WP_Error
	 */
	public static function create_page( array $page_settings, string $option = '' ) {
		$option_page_id = get_option( $option );

		if ( $option_page_id > 0 ) {
			$page_object = get_post( $option_page_id );
			if ( $page_object && 'page' === $page_object->post_type && ! in_array( $page_object->post_status, array( 'pending', 'trash', 'future', 'auto-draft' ), true ) ) {
				return $page_object->ID;
			}
		}
		if ( $page = self::get_page_by_slug($page_settings['slug']) ) {
			return $page->ID;
		}

		$page_data = array(
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_author'    => 1,
			'post_name'      => esc_sql( $page_settings['slug'] ),
			'post_title'     => $page_settings['title'],
			'post_content'   => $page_settings['content'],
			'comment_status' => 'closed',
		);
		$new_page_id = wp_insert_post( $page_data );

		# if page is created - Set Page URLs in settings
		if ($new_page_id && count($page_settings['settings'])) {
			self::save_default_pages_settings( $page_settings['settings'], "/{$page_settings['slug']}" );
		}

		return $new_page_id;
	}

	/**
	 * Add states for Latepoint Pages
	 * @param $post_states
	 * @param $page
	 *
	 * @return array
	 */
	public static function add_display_post_states($post_states, $page): array {
		if ( get_option( 'latepoint_page_customer-cabinet') == $page->ID ) {
			$post_states['latepoint_customer_cabinet'] = 'Latepoint Customer Cabinet';
		}
		return $post_states;
	}

	/**
	 * Get page ID by slug with status published
	 * @param $slug
	 * @return string|null
	 */
	public static function get_page_by_slug($slug) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' )  AND post_name = %s LIMIT 1;", $slug ) );
	}

	/**
	 * save default pages settings
	 * @param array $settings
	 * @param $value
	 *
	 * @return void
	 */
	private static function save_default_pages_settings( array $settings, $value ): void {
		foreach ( $settings as $name ) {
			if ( ! OsSettingsHelper::get_settings_value( $name ) ) {
				OsSettingsHelper::save_setting_by_name( $name, $value );
			}
		}
	}


}