<?php

namespace Barn2\Plugin\Better_Recent_Comments;

use Barn2\Lib\Plugin\Simple_Plugin,
    Barn2\Lib\Registerable,
    Barn2\Plugin\Better_Recent_Comments\Widget;

/**
 * The main plugin class.
 *
 * @package   Barn2\better-recent-comments
 * @author    Barn2 Plugins <support@barn2.co.uk>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Plugin extends Simple_Plugin implements Registerable {

    const SHORTCODE = 'better_recent_comments';

    public function __construct( $file, $version = '1.0' ) {
        parent::__construct( [
            'name'    => 'Better Recent Comments',
            'file'    => $file,
            'version' => $version
        ] );

        include_once __DIR__ . '/deprecated.php';
    }

    public function register() {
        // Load the text domain
        \add_action( 'init', array( $this, 'load_textdomain' ) );

        // Register the widget
        \add_action( 'widgets_init', array( $this, 'register_widget' ) );

        // Register shortcode
        \add_shortcode( self::SHORTCODE, array( $this, 'shortcode' ) );

        // Register styles and scripts
        if ( \apply_filters( 'recent_comments_lang_load_styles', true ) ) {
            \add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );
        }

        // Filters for comment text.
        \add_filter( 'better_recent_comments_comment_text', 'wptexturize' );
        \add_filter( 'better_recent_comments_comment_text', 'convert_chars' );
        \add_filter( 'better_recent_comments_comment_text', 'convert_smilies', 20 );
    }

    public function load_textdomain() {
        \load_plugin_textdomain( 'better-recent-comments', false, $this->get_slug() . '/languages' );
    }

    public function register_widget() {
        \register_widget( Widget::class );
    }

    public function register_styles() {
        \wp_enqueue_style( 'better-recent-comments', \plugins_url( 'assets/css/better-recent-comments.min.css', $this->get_file() ) );
    }

    public function shortcode( $atts, $content = '' ) {
        $atts = \shortcode_atts( Util::default_shortcode_args(), $atts, self::SHORTCODE );
        return Util::get_recent_comments( $atts );
    }

}
