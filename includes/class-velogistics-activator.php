<?php

/**
 * Fired during plugin activation
 *
 * @link       github.com/cvoigt
 * @since      1.0.0
 *
 * @package    Velogistics
 * @subpackage Velogistics/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Velogistics
 * @subpackage Velogistics/includes
 * @author     Christian Voigt <1pxsolidblack@gmail.com>
 */
class Velogistics_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		add_option('velogistics_settings_name',  array(
			'publish' => '1',
			'prepend_metadata' => '1',
			'notification_url' => 'https://velogistics.net/notify'
		));
	}
}
