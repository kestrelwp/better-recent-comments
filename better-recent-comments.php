<?php
/**
 * The main plugin file for Better Recent Comments.
 *
 * @wordpress-plugin
 * Plugin Name:       Better Recent Comments
 * Description:       This plugin provides an improved widget and shortcode to show your most recent comments. If using WPML, comments are limited to posts in the current language.
 * Version:           1.0.5
 * Author:            Barn2 Media
 * Author URI:        http://barn2.co.uk
 * Text Domain:       better-recent-comments
 * Domain Path:       /languages
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */
// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The main plugin class.

 * @package   Barn2\Better_Recent_Comments
 * @author    Andrew Keith <andy@barn2.co.uk>
 * @license   GPL-3.0
 * @link      https://barn2.co.uk
 * @copyright 2016-2018 Barn2 Media
 */
class Better_Recent_Comments_Plugin {

	private $shortcode = 'better_recent_comments';

	public static function bootstrap() {
		$self = new self();
		$self->load();
	}

	public function load() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-better-recent-comments-util.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-widget-better-recent-comments.php';

		// Load the text domain
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Register the widget
		add_action( 'widgets_init', array( $this, 'register_widget' ) );

		// Register shortcode
		add_shortcode( $this->shortcode, array( $this, 'shortcode' ) );

		// Register styles and scripts
		if ( apply_filters( 'recent_comments_lang_load_styles', true ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );
		}

		add_filter( 'widget_text', 'do_shortcode' );
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'better-recent-comments', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public function register_widget() {
		if ( class_exists( 'Widget_Better_Recent_Comments' ) ) {
			register_widget( 'Widget_Better_Recent_Comments' );
		}
	}

	public function register_styles() {
		wp_enqueue_style( 'better-recent-comments', plugins_url( 'assets/css/better-recent-comments.min.css', __FILE__ ) );
	}

	public function shortcode( $atts, $content = '' ) {
		$atts = shortcode_atts(
			Better_Recent_Comments_Util::default_shortcode_args(), $atts, $this->shortcode
		);

		return Better_Recent_Comments_Util::get_recent_comments( $atts );
	}

}
// end class

/* Load the plugin */
Better_Recent_Comments_Plugin::bootstrap();
