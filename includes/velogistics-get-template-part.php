<?php
/**
 * Load template files of the plugin also include a filter velogistics_get_template_part.
 * Uses Cache.
 *
 * Based on https://github.com/humanmade/hm-core/blob/master/hm-core.functions.php
 * Based on https://github.com/WPBP/Template
 * Based on WooCommerce function<br>
 *
 *
 * @license   GPL-2.0+
 * @since     2.0.0
 */

if ( !function_exists( 'velogistics_get_template_part' ) ) {
    /**
     *
     * @param string $plugin_slug
     * @param string|array $slugs
     * @param string $name
     * @param array $template_args  wp_args style argument list
     * @return string
     */
    function velogistics_get_template_part( $plugin_slug, $slugs, $name = '', $return_content = false, $template_args = array(), $cache_args = array() ) {
			$template    = '';
			$plugin_slug = $plugin_slug . '/';
			$path        = WP_PLUGIN_DIR . '/'. $plugin_slug;
			$slug        = $slugs;
			if ( is_array( $slugs ) ) $slug = $slugs[0];

			// Look in yourtheme/slug-name.php and yourtheme/plugin-name/slug-name.php
			if ( $name ) {
				$template = locate_template( array( "{$slug}-{$name}.php", $plugin_slug . "{$slug}-{$name}.php" ) );
			} else {
				$template = locate_template( array( "{$slug}.php", $plugin_slug . "{$slug}.php" ) );
			}

			// Get default slug-name.php
			if ( !$template ) {
				if ( empty( $name ) ) {
					if ( file_exists( $path . "{$slug}.php" ) ) {
						$template = $path . "{$slug}.php";
					}
				} else if ( file_exists( $path . "{$slug}-{$name}.php" ) ) {
					$template = $path . "{$slug}-{$name}.php";
				}
			}

			// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/plugin-name/slug.php
			if ( !$template ) {
				$template = locate_template( array( "{$slug}.php", $plugin_slug . "{$slug}.php" ) );
			}

			// Allow 3rd party plugin filter template file from their plugin
			$template = apply_filters( 'velogistics_get_template_part', $template, $slug, $name, $plugin_slug );

			// Template existence check
			if ( empty( $template ) )
				throw new Exception( "Template does not exist [$path$slug.php] [$name]" );

			// Parse submitted args
			$template_args = wp_parse_args( $template_args );
			$cache_args = wp_parse_args( $cache_args );

			// cached args
			if ( $cache_args ) {
				foreach ( $template_args as $key => $value ) {
					if ( is_scalar( $value ) || is_array( $value ) ) {
						$cache_args[$key] = $value;
					} else if ( is_object( $value ) && method_exists( $value, 'get_id' ) ) {
						$cache_args[$key] = call_user_method( 'get_id', $value );
					}
				}
				if ( ( $cache = wp_cache_get( $file, serialize( $cache_args ) ) ) !== false ) {
					if ( ! empty( $template_args['return'] ) )
						return $cache;
					echo $cache;
					return;
				}
			}

			$file_handle = $template;
			do_action( 'start_operation', 'cb_template_part::' . $file_handle );

			ob_start();
			$return_template = require( $template );
			$data = ob_get_clean();
			if ( WP_DEBUG ) {
				global $post;
				$Class      = get_class( $post );
				$file_debug = basename($template);
				$data = "<!-- $Class => $file_debug -->\n$data";
			}

			do_action( 'end_operation', 'cb_template_part::' . $file_handle );

			if ( $cache_args ) {
				wp_cache_set( $template, $data, serialize( $cache_args ), 3600 );
			}
			if ( $return_content === true )
				if ( $return_template === false )
					return false;
				else
					return $data;

			echo $data;
    }
}
