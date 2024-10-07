<?php
class OsPluginUpgrader extends Plugin_Upgrader {
	public function upgrade_from_url( $plugin, $url_to_zip, $args = array() ) {

		$defaults    = array(
			'clear_update_cache' => true,
		);
		$parsed_args = wp_parse_args( $args, $defaults );

		$this->init();
		$this->upgrade_strings();


		add_filter( 'upgrader_pre_install', array( $this, 'deactivate_plugin_before_upgrade' ), 10, 2 );
		add_filter( 'upgrader_clear_destination', array( $this, 'delete_old_plugin' ), 10, 4 );
		//'source_selection' => array($this, 'source_selection'), //there's a trac ticket to move up the directory for zip's which are made a bit differently, useful for non-.org plugins.
		if ( $parsed_args['clear_update_cache'] ) {
			// Clear cache so wp_update_plugins() knows about the new plugin.
			add_action( 'upgrader_process_complete', 'wp_clean_plugins_cache', 9, 0 );
		}


		$result = $this->run(
			array(
				'package'           => $url_to_zip,
				'destination'       => WP_PLUGIN_DIR,
				'clear_destination' => true,
				'clear_working'     => true,
				'hook_extra'        => array(
					'plugin' => $plugin,
					'type'   => 'plugin',
					'action' => 'update',
				),
			)
		);

		// Cleanup our hooks, in case something else does a upgrade on this connection.
		remove_action( 'upgrader_process_complete', 'wp_clean_plugins_cache', 9 );
		remove_filter( 'upgrader_pre_install', array( $this, 'deactivate_plugin_before_upgrade' ) );
		remove_filter( 'upgrader_clear_destination', array( $this, 'delete_old_plugin' ) );

		if ( ! $result || is_wp_error( $result ) ) {
			return $result;
		}

		// Force refresh of plugin update information
		wp_clean_plugins_cache( $parsed_args['clear_update_cache'] );

		$activate_result = activate_plugin($plugin);

		return is_wp_error($activate_result) ? $activate_result : true;
	}
}