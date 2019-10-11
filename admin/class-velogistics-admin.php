<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       github.com/cvoigt
 * @since      1.0.0
 *
 * @package    Velogistics
 * @subpackage Velogistics/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Velogistics
 * @subpackage Velogistics/admin
 * @author     Christian Voigt <1pxsolidblack@gmail.com>
 */
class Velogistics_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public $settings = array(
		'publish' => array(
			'id' => 'publish',
			'title' => 'Publish cargobike availability on Velogistics',
			'group' => 'velogistics_settings_group',
			'section' => 'velogistics_general_section',
			'type' => 'checkbox'
		),
		'prepend_metadata' => array(
			'id' => 'prepend_metadata',
			'title' => 'Prepend cargo bike metadata to post content',
			'group' => 'velogistics_settings_group',
			'section' => 'velogistics_general_section',
			'type' => 'checkbox'
		),
		'notification_url' => array(
			'id' => 'notification_url',
			'title' => 'URL under which velgogistics will be notified',
			'group' => 'velogistics_settings_group',
			'section' => 'velogistics_general_section',
			'type' => 'url'
		)
	);

	public function register_settings(){
		add_option( 'velogistics_settings_group');
		add_settings_section(
			'velogistics_general_section',
			'',
			array( $this, 'render_settings_section' ),
			'velogistics_settings_group'
		);
		foreach($this->settings as $setting){
			$args = array();
			$cb = array($this);
			switch($setting['type']){
				case 'checkbox':
					$cb[] = 'render_checkbox_input';
					$args['option'] = $setting['id'];
					break;
				case 'url':
					$cb[] = 'render_text_input';
					$args['option'] = $setting['id'];
				break;
		}
			add_settings_field(
				$setting['id'],
				$setting['title'],
				$cb,
				$setting['group'],
				$setting['section'],
				$args
			);
		}	  
		register_setting(
            'velogistics_settings_group', // Option group
			'velogistics_settings_name ', // Option name
			array(
				'sanitize_callback'=> array($this, 'sanitize_settings')
			)
      );	
	}

	function sanitize_settings( $input ) {
		$valid_input = get_option( 'velogistics_settings_name' );
		foreach( $this->settings as $key => $value ) {
			print('Key: '.$key);
			if( 'checkbox' == $value['type'] ) { 
				 if(isset( $input[$key])){
					$valid_input[$key] = (true == $input[$key])? 1 : 0;
				 }
			}else if('url' == $value['type']){
			 $valid_input[$key] = esc_url_raw($input[key]);
			}
		}
		  return $valid_input;
	}
	function sanitize_float($input){
		return filter_var($input,FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
	}
	public function render_settings_section() {
		echo '<p>If you choose to publish your cargobikes, Velogistics will regularly retrieve items, locations, owners, projects and availability slots from your Wordpress installation. Only publicly accessible data of Commons Booking 2 is synchronized (through the Commons API).</p> 
		<p>Apart from the Wordpress username and homepage url of cargobike owners, no user data is published.</p>
		<p>By publishing your data you agree to the <a href="#">Terms of Service</a> of velogistics.net.</p>';	
	}
	public function render_checkbox_input( $args ) {
		$options = get_option( 'velogistics_settings_name' );
		$value = ( isset( $options[ $args['option'] ] )? $options[ $args['option'] ] : 0 );
		$html = '<input type="checkbox" id="velogistics_settings_name['. $args['option'] .']" name="velogistics_settings_name['. $args['option'] .']" value="1" '.checked('1', $value, false).'>';
		echo $html;
	}
public function render_text_input( $args ) {
  $options = get_option( 'velogistics_settings_name' );
  $value = ( isset( $options[ $args['option'] ] )? $options[ $args['option'] ] : '' );
  $html = '<input type="text" id="velogistics_settings_name['. $args['option'] .']" name="velogistics_settings_name['. $args['option'] .']" value="'. $value .'"/>';

  echo $html;
}
	public function register_options_page(){
		add_options_page( 
			'Velogistics Settings', 
			'Velogistics', 
			'manage_options', 
			'velogistics-options', 
			array($this, 'create_options_page') 
		);
	}
	public function create_options_page(){
		?>
		<div class="wrap">
		  <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		  <form action="options.php" method="post">
		  <?php
		settings_fields( 'velogistics_settings_group' );

		do_settings_sections( 'velogistics_settings_group' );

		submit_button( 'Save Settings' );		 
?> </form>
		</div>
		<?php		
	}
	public function sanitize_boolean($input){
	
	return ( ( isset( $input ) && true == $input ) ? 1 : 0 );
	}

	public function add_cargobike_metaboxes($metaboxes){
		$metadata_options = ( array ) json_decode( file_get_contents( plugin_dir_path( __DIR__ ). "vendor/wielebenwir/commons-api/velogistics-metadata.json" ) );
		$features_options = array();
		foreach($metadata_options["features"] as $item) {
				$features_options[$item->id] = $item->name;
		}
		$item_type_options= array();
		foreach($metadata_options["itemType"] as $item){
			$item_type_options[$item->id] = $item->name;
		}
		// Start with an underscore to hide fields from custom fields list
		$prefix = '_velogistics_';

		$cmb = array(
			'id'            => 'velogistics_metabox',
			'title'         => __( 'Velogistics' ),
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true, // Show field names on the left
			// 'cmb_styles' => false, // false to disable the CMB stylesheet
			// 'closed'     => true, // Keep the metabox closed by default
		 );

		$cmb['fields'] = array( 
			array(
				'name' => esc_html__('Item Type'),
				'id' => $prefix. 'item_type',
				'type' => 'select',
				'show_option_none' => true,
				'options' => $item_type_options
			),
			array(
			'name' => esc_html__( 'Commercial offer'),
			'id'   => $prefix . 'is_commerical',
			'type' => 'checkbox',
			'sanitization_cb' => array($this, 'sanitize_boolean')
			),
			array(
				'name' => __( 'Load capacity (kg)' ),
				'id'   => $prefix . 'load_capacity',
				'type' => 'text',
				'attributes' => array(
					'type' => 'number',
					'pattern' => '\d*\.?\d*',
					'step' => 0.01
				),
				'sanitization_cb' => array($this, 'sanitize_float'),
					'escape_cb'       => array($this, 'sanitize_float'),
			),
			array(
				'name'    => 'Features',
				'desc'    => 'features of your cargo-bike',
				'id'   => $prefix . 'features',
				'type'    => 'multicheck',
				'options' => $features_options,
			),
			array(
				'name' => 'Box dimensions (cm)',
				'desc' => "Size of storage space inside the cargo bike's box",
				'type' => 'title',
				'id'   => $prefix.'box_dimensions_title'
			),
			array(
				'name' => __( 'Length' ),
				'id'   => $prefix . 'box_length',
				'type' => 'text_small',
				'attributes' => array(
					'type' => 'number',
					'pattern' => '\d*\.?\d*',
					'step' => 0.01
				),
				'sanitization_cb' => array($this, 'sanitize_float'),
					'escape_cb'       => array($this, 'sanitize_float'),
			),
			array(
				'name' => __( 'Width' ),
				'id'   => $prefix . 'box_width',
				'type' => 'text_small',
				'attributes' => array(
					'type' => 'number',
					'pattern' => '\d*\.?\d*',
					'step' => 0.01
				),
				'sanitization_cb' => array($this, 'sanitize_float'),
					'escape_cb'       => array($this, 'sanitize_float'),
			),
			array(
				'name' => __( 'Height' ),
				'id'   => $prefix . 'box_height',
				'type' => 'text_small',
				'attributes' => array(
					'type' => 'number',
					'pattern' => '\d*\.?\d*',
					'step' => 0.01
				),
				'sanitization_cb' => array($this, 'sanitize_float'),
					'escape_cb'       => array($this, 'sanitize_float'),
			),
		);

		$metaboxes[] = $cmb;
		return $metaboxes;
	}

	public function update_option($old_value, $new_value){
		// notify velogistics
		if($old_value['publish'] != $new_value['publish']){
			$url = $new_value['notification_url'].'?url='.urlencode(get_rest_url(null, VELOGISTICS_COMMONS_API_ENDPOINT));
			wp_remote_get($url);
		}
	}

	public function pause_notification(){
		$settings = get_option( 'velogistics_settings_name' );
		$settings["pause"] = 1;
		update_option('velogistics_settings_name', $settings);
	}
	public function unpause_notification(){
		$settings = get_option( 'velogistics_settings_name' );
		$settings["pause"] = 0;
		update_option('velogistics_settings_name', $settings);
	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/velogistics-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/velogistics-admin.js', array( 'jquery' ), $this->version, false );

	}

}
