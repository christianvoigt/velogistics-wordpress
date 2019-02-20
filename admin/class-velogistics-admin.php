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
			'section' => 'velogistics_example_section',
			'type' => 'checkbox'
		),
		'prepend_metadata' => array(
			'id' => 'prepend_metadata',
			'title' => 'Prepend cargo bike metadata to post content',
			'group' => 'velogistics_settings_group',
			'section' => 'velogistics_example_section',
			'type' => 'checkbox'
		)
	);

	public function register_settings(){
		add_option( 'velogistics_settings_group');
		add_settings_section(
			'velogistics_example_section',
			'Example Section',
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
				'sanitize_callback'=> array($this, 'sanitize_settings'),
				'default' => array(
					'publish' => '1',
					'prepend_metadata' => '1'
				)
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
			 }
		 }
		 return $valid_input;
	}
	public function render_settings_section() {
		//echo additional content between section header and content
	}
	public function render_checkbox_input( $args ) {
		$options = get_option( 'velogistics_settings_name' );
		$value = ( isset( $options[ $args['option'] ] )? $options[ $args['option'] ] : 0 );
		$html = '<input type="checkbox" id="velogistics_settings_name['. $args['option'] .']" name="velogistics_settings_name['. $args['option'] .']" value="1" '.checked('1', $value, false).'>';
		echo $html;
	}
	public function register_options_page(){
		add_options_page( 
			'Velogistics Plugin Settings', 
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

	public function add_cargobike_metaboxes(){
		// Start with an underscore to hide fields from custom fields list
		$prefix = '_velogistics_';

		/**
		 * Initiate the metabox
		 */
		$cmb = new_cmb2_box( array(
			'id'            => 'velogistics_metabox',
			'title'         => __( 'Velogistics' ),
			'object_types'  => array( 'item', ), // Post type
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true, // Show field names on the left
			// 'cmb_styles' => false, // false to disable the CMB stylesheet
			// 'closed'     => true, // Keep the metabox closed by default
		) );

		$cmb->add_field( array(
			'name' => esc_html__( 'Suited for children transport'),
			'id'   => $prefix . 'can_transport_children',
			'type' => 'checkbox',
			'sanitization_cb' => array($this, 'sanitize_boolean')
		) );
		$cmb->add_field( array(
			'name' => __( 'Maximum transport weight (kg)' ),
			'id'   => $prefix . 'max_transport_weight',
			'type' => 'text',
			'attributes' => array(
				'type' => 'number',
				'pattern' => '\d*',
			),
			'sanitization_cb' => 'absint',
				'escape_cb'       => 'absint',
		) );
		$cmb->add_field( array(
			'name' => __( 'Number of wheels' ),
			'id'   => $prefix . 'nr_of_wheels',
			'type' => 'text',
			'attributes' => array(
				'type' => 'number',
				'pattern' => '\d*',
			),
			'sanitization_cb' => 'absint',
				'escape_cb'       => 'absint',
		) );
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/velogistics-admin.js', array( 'jquery' ), $this->version, false );

	}

}
