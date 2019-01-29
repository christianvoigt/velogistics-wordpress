<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       github.com/cvoigt
 * @since      1.0.0
 *
 * @package    Velogistics
 * @subpackage Velogistics/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Velogistics
 * @subpackage Velogistics/public
 * @author     Christian Voigt <1pxsolidblack@gmail.com>
 */
class Velogistics_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Velogistics_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Velogistics_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/velogistics-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Velogistics_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Velogistics_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/velogistics-public.js', array( 'jquery' ), $this->version, false );

	}
	public function prepend_metadata($content){
		global $post;
		$settings = get_option( 'velogistics_settings_name' );
		if (isset($settings['prepend_metadata']) && $settings['prepend_metadata'] == '1' && $post->post_type == 'item') {
			$content = velogistics_get_template_part('velogistics/public/partials', 'cargobike-metadata', '', true).$content;
		}
		return $content;
	}
	public function notify_velogistics(){
		echo "notify_velogistics";
		$settings = get_option( 'velogistics_settings_name' );
		if (!isset($settings['publish']) || $settings['publish'] == '0') {
			return;
		}
		// $data = wp_remote_post($url, array(
		// 	'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
		// 	'body'        => json_encode(CB2_API::get_items()),
		// 	'method'      => 'POST',
		// 	'data_format' => 'body',
		// ));
		$url = VELOGISTICS_NOTIFICATION_URL.'?url='.urlencode(get_rest_url(null, 'cb2/v1/items'));
		echo "Notifying velogistics: ".$url;
		wp_remote_get($url);
	}

}
