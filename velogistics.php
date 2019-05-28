<?php

/**
 *
 * @link              github.com/cvoigt
 * @since             1.0.0
 * @package           Velogistics
 *
 * @wordpress-plugin
 * Plugin Name:       Velogistics
 * Plugin URI:        github.com/cvoigt/velogistics
 * Description:       Cargobikes for Commons Booking
 * Version:           1.0.0
 * Author:            Christian Voigt
 * Author URI:        github.com/cvoigt
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       velogistics
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'VELOGISTICS_VERSION', '1.0.0' );
define ( 'VELOGISTICS_CB2_DIR', 'commons-booking-2/commons-booking-2.php' );      
define ( 'VELOGISTICS_REQUIRED_CB2_VERSION', '2.0.0' ) ;   
// define ( 'VELOGISTICS_NOTIFICATION_URL', 'http://ptsv2.com/t/velogistics/post' ) ;      
// define ( 'VELOGISTICS_NOTIFICATION_URL', 'http://localhost:8028/notify' ) ;          
define ( 'VELOGISTICS_NOTIFICATION_URL', 'http://host.docker.internal:8082/notify' ) ;          
// define ( 'VELOGISTICS_NOTIFICATION_URL', 'https://velogistics.net/notify' ) ;         
define ('VELOGISTICS_COMMONS_API_ENDPOINT', 'commons-booking-2/v1/items'); 
function check_velogistics_requirements(){
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' ) ;  // to get is_plugin_active() early
    
    if ( ! is_plugin_active ( VELOGISTICS_CB2_DIR ) ) {
        return false ;
    }

    $cb2_data = get_plugin_data(WP_PLUGIN_DIR .'/'.VELOGISTICS_CB2_DIR, false, false);

    if (version_compare ($cb2_data['Version'] , VELOGISTICS_REQUIRED_CB2_VERSION, '<')){
        return false;
    }

    return true ;            
}

if(!check_velogistics_requirements()){
    // add_action( 'admin_notices', 'velogistics_requirements_error' );
    exit( 'The Velogistics plugin requires the Commons Booking 2 plugin to be installed. Please install and activate it first.');
}
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-velogistics-activator.php
 */
function activate_velogistics() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-velogistics-activator.php';
	Velogistics_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-velogistics-deactivator.php
 */
function deactivate_velogistics() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-velogistics-deactivator.php';
	Velogistics_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_velogistics' );
register_deactivation_hook( __FILE__, 'deactivate_velogistics' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-velogistics.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_velogistics() {

	$plugin = new Velogistics();
	$plugin->run();

}
run_velogistics();
