<?php
/**
 * WordPress Plugin RSS Synchronization
 *
 * A plugin for reading and synchronizing external RSS feeds right into your Wordpress site.
 *
 * @package   RSS-Sync
 * @author    João Horta Alves <joao.alves@log.pt>
 * @license   GPL-2.0+
 * @copyright 2014 João Horta Alves
 *
 * @wordpress-plugin
 * Plugin Name:       RSS Sync
 * Description:       Synchronize posts with external RSS feed.
 * Version:           0.5.3
 * Author:            lightsystem, log_oscon
 * Text Domain:       rss-sync
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/LightSystem/WordPress-Plugin-RSS-Sync
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
* Debugging stuff
*/
if (!function_exists('write_log')) {
    function write_log ( $log )  {
        if ( true === WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        }
    }
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'public/class-rss-sync.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'RSS_Sync', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'RSS_Sync', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'RSS_Sync', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-rss-sync-admin.php' );
	add_action( 'plugins_loaded', array( 'RSS_Sync_Admin', 'get_instance' ) );

}
