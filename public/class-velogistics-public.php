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
		if (isset($settings['prepend_metadata']) && $settings['prepend_metadata'] == '1' && $post->post_type == 'cb2_item') {
			$metadata_options = ( array ) json_decode( file_get_contents( plugin_dir_path( __DIR__ ). "vendor/wielebenwir/commons-api/velogistics-metadata.json" ) );
			$content = velogistics_get_template_part('velogistics/public/partials', 'cargobike-metadata', '', true,array("metadata_options"=>$metadata_options)).$content;
		}
		return $content;
	}
	public function add_api_item_metadata($itemApiData, $item){
		$prefix = '_velogistics_';
		$id = $item->ID;
		$itemApiData['isCommercial'] = get_post_meta( $id, $prefix.'is_commercial', true )? true:false;
		$itemApiData['loadCapacity'] = (float)get_post_meta( $id, $prefix.'load_capacity', true );
		$itemApiData['nrOfWheels'] = (int)get_post_meta( $id, $prefix.'nr_of_wheels', true );
		$itemApiData['seatsForChildren'] = (int)get_post_meta( $id, $prefix.'seats_for_children', true );
		$itemApiData['itemType'] = sanitize_text_field(get_post_meta( $id, $prefix.'item_type', true ));
		$itemApiData['features'] = array();
		$features = get_post_meta( $id, $prefix.'features', true );
		if(is_array($features)){
			foreach($features as $item){
				$itemApiData["features"][] = sanitize_text_field($item);
			}	
		}
		$itemApiData["boxDimensions"] = array(
			"width"=> (float)get_post_meta( $id, $prefix.'box_width', true ),
			"height"=> (float)get_post_meta( $id, $prefix.'box_height', true ),
			"length"=> (float)get_post_meta( $id, $prefix.'box_length', true ),
		);
		$itemApiData["bikeDimensions"] = array(
			"width"=> (float)get_post_meta( $id, $prefix.'bike_width', true ),
			"height"=> (float)get_post_meta( $id, $prefix.'bike_height', true ),
			"length"=> (float)get_post_meta( $id, $prefix.'bike_length', true ),
		);

		return $itemApiData;
	}
	public function add_publishOnVelogistics_flag($data){
		$settings = get_option( 'velogistics_settings_name' );
		$publish = isset($settings['publish']) && $settings['publish'] == '1';
		$paused = isset($settings['pause']) && $settings['pause'] == '1';
		$data['publishOnVelogistics'] = $publish && !$paused;
		return $data;
	}
	public function notify_velogistics($post_id, $post){
		$settings = get_option( 'velogistics_settings_name' );
		$paused = isset($settings['pause']) && $settings['pause'] == '1';
		if (!isset($settings['publish']) || $settings['publish'] == '0' || $paused) {
			return;
		}
		// in the future we might have to check if the booking is used once (otherwise it might affect other things in the system)
		// var_dump($post->period_group->used_once());
		// Is this a new booking?
		if(get_post_type($post) == "cb2_prdent-tf-user" && get_post_status($post) == "publish" && $post->period_status_type->ID ==2){
			$url = $settings['notification_url'].'?url='.urlencode(get_rest_url(null, VELOGISTICS_COMMONS_API_ENDPOINT)).'&item='.$post->item_ID.'&availability_changed=true';
		}else{
			$url = $settings['notification_url'].'?url='.urlencode(get_rest_url(null, VELOGISTICS_COMMONS_API_ENDPOINT));
		}
		// $data = wp_remote_post($url, array(
		// 	'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
		// 	'body'        => json_encode(CB2_API::get_items()),
		// 	'method'      => 'POST',
		// 	'data_format' => 'body',
		// ));
		wp_remote_get($url);
	}

}
