<?php
/**
 * The main plugin file for Better Recent Comments.
 *
 * @package   Better_Recent_Comments
 * @author    Andrew Keith <andy@barn2.co.uk>
 * @license   GPL-2.0+
 * @link      http://barn2.co.uk
 * @copyright 2016 Barn2 Media
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
if ( ! defined ( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-better-recent-comments-util.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-widget-better-recent-comments.php';

class Better_Recent_Comments_Plugin {
    
    private $shortcode = 'better_recent_comments';
    
    public function __construct() {
        
        // Load the text domain - should go on 'plugins_loaded' hook to make sure strings load prior to register_widget call
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        
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
        load_plugin_textdomain( 'better-recent-comments', false, dirname( plugin_basename( __FILE__  ) ) . '/languages' );
    }
    
    public function register_widget() {
        if ( class_exists( 'Widget_Better_Recent_Comments') ) {
            register_widget( 'Widget_Better_Recent_Comments' );
        }
    }
    
	public function register_styles() {
		wp_enqueue_style( 'better-recent-comments', plugins_url( 'assets/css/better-recent-comments.min.css', __FILE__ ) );
	}
    
    public function shortcode( $atts, $content = '' ) {
        $atts = shortcode_atts( 
            Better_Recent_Comments_Util::default_shortcode_args(), 
            $atts, 
            $this->shortcode 
        );
        
        return Better_Recent_Comments_Util::get_recent_comments( $atts );
    }
    
} // end class
$better_recent_comments = new Better_Recent_Comments_Plugin();