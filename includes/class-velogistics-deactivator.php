<?php

/**
 * Fired during plugin deactivation
 *
 * @link       github.com/cvoigt
 * @since      1.0.0
 *
 * @package    Velogistics
 * @subpackage Velogistics/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Velogistics
 * @subpackage Velogistics/includes
 * @author     Christian Voigt <1pxsolidblack@gmail.com>
 */
class Velogistics_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// unpublish on deactivation
		$settings = get_option( 'velogistics_settings_name' );
		$settings["publish"] = 0;
		update_option('velogistics_settings_name', $settings);
		$url = $settings['notification_url'].'?url='.urlencode(get_rest_url(null, VELOGISTICS_COMMONS_API_ENDPOINT));
		wp_remote_get($url);
	}

}
