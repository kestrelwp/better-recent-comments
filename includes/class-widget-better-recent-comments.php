<?php
/**
 * This class provides the Better Recent Comments widget.
 *
 * @package   Better_Recent_Comments
 * @author    Andrew Keith <andy@barn2.co.uk>
 * @license   GPL-2.0+
 * @link      http://barn2.co.uk
 * @copyright 2016 Barn2 Media
 */

// Prevent direct file access
if ( ! defined ( 'ABSPATH' ) ) {
	exit;
}

class Widget_Better_Recent_Comments extends WP_Widget {

    // A unique identifier for the widget
    private $widget_slug = 'better_recent_comments';
            
	public function __construct() {

		parent::__construct(
			$this->widget_slug,
			__( 'Better Recent Comments', 'better-recent-comments' ),
			array(
				'classname'  => 'widget_recent_comments',
				'description' => __( 'An improved widget to show your site&#8217;s most recent comments.', 'better-recent-comments' )
			)
		);

		add_action( 'comment_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'edit_comment', array( $this, 'flush_widget_cache' ) );
		add_action( 'transition_comment_status', array( $this, 'flush_widget_cache' ) );
        
	} // end constructor
    
	public function flush_widget_cache() {
    	wp_cache_delete( $this->widget_slug, 'widget' );
	}
    
	/**
	 * Outputs the content of the widget.
	 *
	 * @param array args  The array of form elements
	 * @param array instance The current instance of the widget
	 */
	public function widget( $args, $instance ) {
		
		// Check if there is a cached output
		$cache = wp_cache_get( $this->widget_slug, 'widget' );

		if ( !is_array( $cache ) ) {
			$cache = array();
        }

		if ( ! isset ( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
        }

		if ( isset ( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
            return;
        }
        
        $output = $args['before_widget'];

        $title = ( !empty( $instance['title'] ) ) ? $instance['title'] : __( 'Recent Comments', 'better-recent-comments' );
        // This filter is documented in wp-includes/default-widgets.php
        $title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

        if ( $title ) {
            $output .= $args['before_title'] . $title . $args['after_title'];
        }
        
        $instance['format'] = Better_Recent_Comments_Util::get_comment_format( $instance['date'], $instance['comment'], $instance['link'], $instance['avatar'] );
        $instance['avatar_size'] = apply_filters( 'recent_comments_lang_widget_avatar_size', 40 );
        
        $output .= Better_Recent_Comments_Util::get_recent_comments( $instance );
        
        $output .= $args['after_widget'];
        
		echo $output;
        
        if ( !$this->is_preview() ) {
            $cache[ $args['widget_id'] ] = $output;
            wp_cache_set( $this->widget_slug, $cache, 'widget' );
        }

	} // end widget
    
	/**
	 * Processes the widget's options to be saved.
	 *
	 * @param array new_instance The new instance of values to be generated via the update.
	 * @param array old_instance The previous instance of values before the update.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = absint( $new_instance['number'] );
        $instance['avatar'] = isset( $new_instance['avatar'] ) ? true : false;
        $instance['date'] = isset( $new_instance['date'] ) ? true : false;
        $instance['comment'] = isset( $new_instance['comment'] ) ? true : false;
        $instance['link'] = isset( $new_instance['link'] ) ? true : false;
        
		$this->flush_widget_cache();
		return $instance;

	} // end widget

	/**
	 * Generates the administration form for the widget.
	 *
	 * @param array instance The array of keys and values for the widget.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( $instance, array( 'title' => '' ) );
        
        $number = isset( $instance['number'] ) ? filter_var( $instance['number'], FILTER_VALIDATE_INT ) : false;
        if ( !$number ) {
            $number = 5;
        }
        $show_avatar = isset( $instance['avatar'] ) ? (bool) $instance['avatar'] : true;
        $show_date = isset( $instance['date'] ) ? (bool) $instance['date'] : true;
        $show_comment = isset( $instance['comment'] ) ? (bool) $instance['comment'] : true;
        $show_link = isset( $instance['link'] ) ? (bool) $instance['link'] : true;
?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'better-recent-comments' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e( 'Number of comments to show:', 'better-recent-comments' ); ?></label>
            <input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" />
        </p>
        <p>
            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('avatar'); ?>" name="<?php echo $this->get_field_name('avatar'); ?>"<?php checked( $show_avatar ); ?> />
            <label for="<?php echo $this->get_field_id('avatar'); ?>"><?php _e( 'Show avatar', 'better-recent-comments' ); ?></label><br />
            
            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('date'); ?>" name="<?php echo $this->get_field_name('date'); ?>"<?php checked( $show_date ); ?> />
            <label for="<?php echo $this->get_field_id('date'); ?>"><?php _e( 'Show date', 'better-recent-comments' ); ?></label><br />
            
            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('comment'); ?>" name="<?php echo $this->get_field_name('comment'); ?>"<?php checked( $show_comment ); ?> />
            <label for="<?php echo $this->get_field_id('comment'); ?>"><?php _e( 'Show comment', 'better-recent-comments' ); ?></label><br />
            
            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('link'); ?>" name="<?php echo $this->get_field_name('link'); ?>"<?php checked( $show_link ); ?> />
            <label for="<?php echo $this->get_field_id('link'); ?>"><?php _e( 'Show post link', 'better-recent-comments' ); ?></label>
        </p>
<?php 
	} // end form
    
} // end class

