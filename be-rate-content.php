<?php
/**
 * Plugin Name: BE Rate Content
 * Plugin URI:  https://github.com/billerickson/be-rate-content
 * Description: Allow users to rate content (thumbs up / down)
 * Author:      Bill Erickson
 * Author URI:  https://www.billerickson.net
 * Version:     1.0.0
 *
 * BE Rate Content is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * BE Rate Content is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with BE Rate Content. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    BE_Rate_Content
 * @author     Bill Erickson
 * @since      1.0.0
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2017
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main class
 *
 * @since 1.0.0
 * @package BE_Rate_Content
 */
final class BE_Rate_Content {

	/**
	 * Instance of the class.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	private static $instance;

	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $version = '1.0.0';

	/**
	 * Settings
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $settings = array();

	/**
	 * Class Instance.
	 *
	 * @since 1.0.0
	 * @return BE_Rate_Content
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof BE_Rate_Content ) ) {
			self::$instance = new BE_Rate_Content;
			self::$instance->constants();
			self::$instance->load_textdomain();
			add_action( 'init', array( self::$instance, 'init' ) );
		}
		return self::$instance;
	}

	/**
	 * Constants
	 *
	 * @since 1.0.0
	 */
	function constants() {

		// Version
 		define( 'BE_RATE_CONTENT_VERSION', $this->version );

 		// Directory URL
 		define( 'BE_RATE_CONTENT_URL', plugin_dir_url( __FILE__ ) );
		define( 'BE_RATE_CONTENT_DIR', plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Load Textdomain for translations
	 *
	 * @since 1.1.0
	 */
	function load_textdomain() {

			 load_plugin_textdomain( 'be-rate-content', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	}


	/**
	 * Initialize
	 *
	 * @since 1.0.0
	 */
	function init() {

		$this->settings = apply_filters( 'be_rate_content_settings', $this->default_settings() );

		add_action( 'wp_enqueue_scripts',             array( $this, 'scripts' ) );
		add_action( 'wp_ajax_be_rate_content',        array( $this, 'update_count' ) );
		add_action( 'wp_ajax_nopriv_be_rate_content', array( $this, 'update_count' ) );

		// Dashboard Widget
		add_action( 'wp_dashboard_setup',             array( $this, 'register_dashboard_widget' ) );
	}

	/**
	 * Default Settings
	 *
	 * @since 1.0.0
	 * @return array
	 */
	function default_settings() {
		return array(
			'post_types' => array( 'post' ),
			'types'      => array( 'like', 'dislike' )
		);
	}

	/**
	 * Scripts
	 *
	 * @since 1.0.0
	 */
	function scripts() {

		wp_register_script( 'be-rate-content', BE_RATE_CONTENT_URL . 'assets/js/be-rate-content.min.js', array( 'jquery' ), BE_RATE_CONTENT_VERSION, true );
 		wp_localize_script( 'be-rate-content', 'be_rate_content', array( 'url' => admin_url( 'admin-ajax.php' ) ) );

	}

	/**
	 * Load Assets
	 *
	 * @since 1.0.0
	 */
	function load_assets() {

		if( apply_filters( 'be_rate_content_load_assets', true ) ) {

			wp_enqueue_script( 'be-rate-content' );
		}

	}

	/**
	 * Update Count
	 *
	 * @since 1.0.0
	 */
	function update_count() {

		$post_id = intval( $_POST[ 'post_id' ] );

		if( ! $post_id )
			wp_send_json_error( __( 'No Post ID', 'be-rate-content' ) );

		if( !in_array( get_post_type( $post_id ), $this->settings[ 'post_types' ] ) )
			wp_send_json_error( __( 'This post type does not support likes', 'be-rate-content' ) );


		$count = $this->count( $post_id );
		$count++;
		update_post_meta( $post_id, '_be_rate_content', $count );

		$data = $this->maybe_count( $post_id, $count );
		wp_send_json_success( $data );

		wp_die();
	}

	/**
	 * Display
	 *
	 * @since 1.0.0
	 */
	function display() {

		if( ! is_singular() || !in_array( get_post_type(), $this->settings[ 'post_types' ] ) )
			return;

		$this->load_assets();

		foreach( $this->settings['types'] as $type ) {
			printf(
				'<a href="#" class="%s" data-type="%s" data-postid="%s"><span class="icon">' . $this->icon( $type ) . '</span><span class="count">%s</span></a>',
				'be-rate-content be-rate-content-' . $type,
				$type,
				get_the_ID(),
				$this->count( $type, get_the_ID() )
			);
		}
	}

	/**
	 * Count
	 *
	 * @since 1.0.0
	 */
	function count( $type = 'like', $post_id = '' ) {

		if( empty( $post_id ) )
			return;

		if( !in_array( $type, $this->settings['types'] ) )
			return;

		$key = '_be_rate_content_' . esc_attr( $type );

		return intval( get_post_meta( $post_id, $key, true ) );
	}

	/**
	 * Icon
	 *
	 */
	function icon( $type = '' ) {
		$icon_path = BE_RATE_CONTENT_DIR . 'assets/icons/' . $type . '.svg';
		if( file_exists( $icon_path ) )
			return file_get_contents( $icon_path );
	}

	/**
	 * Register Dashboard Widgets
	 *
	 * @since 1.1.0
	 */
	function register_dashboard_widget() {

		wp_add_dashboard_widget(
	                 'be_rate_content_popular_widget',
	                 __( 'Popular Content', 'be-rate-content' ),
	                 array( $this, 'dashboard_widget' )
	        );
	}

	/**
	 * Popular Content, Dashboard Widget
	 *
	 * @since 1.1.0
	 */
	function dashboard_widget() {

		$args = array(
			'posts_per_page' => 20,
			'post_type'      => $this->settings[ 'post_types' ],
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
			'meta_key'       => '_be_rate_content_total',
		);
		$loop = new WP_Query( apply_filters( 'be_rate_content_popular_widget_args', $args ) );

		if( $loop->have_posts() ):
			echo '<ol>';
			while( $loop->have_posts() ): $loop->the_post();

				$counts = array();
				foreach( $this->settings['types'] as $type ) {
					$count = $this->count( $type, get_the_ID() );
					$counts[] = _n( $type, $type . 's', $count, 'be-rate-content' );
				}

				printf(
					'<li><a href="%s">%s</a> (%s)</li>',
					get_permalink(),
					get_the_title(),
					join( ', ', $counts )
				);

			endwhile;
			echo '</ol>';
		endif;
		wp_reset_postdata();
	}


}

/**
 * The function provides access to the class methods.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * @since 1.0.0
 * @return object
 */
function be_rate_content() {
	return BE_Rate_Content::instance();
}
be_rate_content();
