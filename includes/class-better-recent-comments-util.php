<?php
// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class provides utility functions for the Better Recent Comments plugin.
 *
 * @package   Barn2\Better_Recent_Comments
 * @author    Andrew Keith <andy@barn2.co.uk>
 * @license   GPL-3.0
 * @link      https://barn2.co.uk
 * @copyright 2016-2018 Barn2 Media
 */
class Better_Recent_Comments_Util {

	public static function default_shortcode_args() {
		return array(
			'number'		 => 5,
			'format'		 => self::get_comment_format(),
			'date_format'	 => 'M j, H:i',
			'avatar_size'	 => 50,
			'post_status'	 => 'publish',
			'excerpts'		 => true
		);
	}

	public static function get_comment_format( $date = true, $comment = true, $post_link = true, $avatar = false ) {
		$format = '';

		if ( $avatar ) {
			$format .= '{avatar} ';
		}
		if ( $post_link ) {
			//* translators: comments widget: 1: comment author, 2: post link */
			$format .= sprintf( _x( '%1$s on %2$s', 'recent comment', 'better-recent-comments' ), '{author}', '{post}' );
		} else {
			$format .= '{author}';
		}
		if ( $comment ) {
			$format .= ': &ldquo;{comment}&rdquo;';
		}
		if ( $date ) {
			$format .= ' {date}';
		}

		return $format;
	}

	public static function get_recent_comments( $args ) {

		$defaults	 = self::default_shortcode_args();
		$args		 = wp_parse_args( $args, $defaults );

		// Sanitize post status used to retrieve comments
		$post_status = array_filter( array_map( 'sanitize_key', explode( ',', $args['post_status'] ) ) );

		$comment_args = array(
			'number'		 => absint( filter_var( $args['number'], FILTER_VALIDATE_INT ) ),
			'status'		 => 'approve',
			'post_status'	 => $post_status,
			'type'			 => apply_filters( 'better_recent_comments_comment_type', 'comment' )
		);

		if ( class_exists( 'SitePress' ) ) {
			// WPML active - get all published posts & pages in the current language
			$posts_current_lang = get_posts( apply_filters( 'better_recent_comments_post_args_wpml', array(
				'post_type'			 => array( 'post', 'page' ),
				'posts_per_page'	 => 2000,
				'post_status'		 => $post_status,
				'fields'			 => 'ids',
				'suppress_filters'	 => false // Ensure WPML filters run on this query
				) ) );

			if ( $posts_current_lang ) {
				$comment_args['post__in'] = $posts_current_lang;
			}
		}

		// Get recent comments limited to post IDs above
		$comments = get_comments( apply_filters( 'better_recent_comments_comment_args', $comment_args ) );

		$output				 = $comment_item_style	 = '';
		$comments_list_class = 'recent-comments-list';

		// Use .recentcomments class on li's to match WP_Recent_Comments widget
		$comment_li_fmt = '<li class="recentcomments recent-comment"><div class="comment-wrap"%s>%s</div></li>';

		if ( is_array( $comments ) && $comments ) {
			// Prime cache for associated posts. (Prime post term cache if we need it for permalinks.)
			$post_ids = array_unique( wp_list_pluck( $comments, 'comment_post_ID' ) );
			_prime_post_caches( $post_ids, strpos( get_option( 'permalink_structure' ), '%category%' ), false );

			$format		 = empty( $args['format'] ) ? self::get_comment_format() : $args['format'];
			$date_format = empty( $args['date_format'] ) ? $defaults['date_format'] : $args['date_format'];

			$link_from	 = self::comment_link_from( $format );
			$excerpts	 = isset( $args['excerpts'] ) ? filter_var( $args['excerpts'], FILTER_VALIDATE_BOOLEAN ) : $defaults['excerpts'];
			$avatar_size = empty( $args['avatar_size'] ) ? false : filter_var( $args['avatar_size'], FILTER_VALIDATE_INT );

			if ( ! $avatar_size ) {
				$avatar_size = $defaults['avatar_size'];
			}

			if ( strpos( $format, '{avatar}' ) !== false ) {
				$comments_list_class .= ' with-avatars';
				$comment_item_style	 = sprintf( ' style="padding-left:%1$upx; min-height:%2$upx;"', round( $avatar_size + ($avatar_size / 4) ), $avatar_size + 4 );
			}

			foreach ( (array) $comments as $comment ) {
				$link_fmt = '<a href="' . esc_url( get_comment_link( $comment->comment_ID ) ) . '">%s</a>';

				$avatar	 = get_avatar( $comment, $avatar_size );
				$author	 = get_comment_author_link( $comment->comment_ID );
				$date	 = get_comment_date( $date_format, $comment->comment_ID );
				$post	 = get_the_title( $comment->comment_post_ID );

				if ( $excerpts ) {
					$comment_text = get_comment_excerpt( $comment->comment_ID );
				} else {
					if ( apply_filters( 'better_recent_comments_strip_formatting', true ) ) {
						$comment_text = strip_tags( str_replace( array( "\n", "\r" ), ' ', $comment->comment_content ) );
					} else {
						$comment_text = wpautop( $comment->comment_content );
					}
				}

				if ( 'post' === $link_from ) {
					$post = sprintf( $link_fmt, $post );
				} elseif ( 'date' === $link_from ) {
					$date = sprintf( $link_fmt, $date );
				} elseif ( 'comment' === $link_from ) {
					$comment_text = sprintf( $link_fmt, $comment_text );
				}

				$comment_content = str_replace(
					array( '{avatar}', '{author}', '{comment}', '{date}', '{post}' ), array(
					'<span class="comment-avatar">' . $avatar . '</span>',
					'<span class="comment-author-link">' . $author . '</span>',
					'<span class="comment-excerpt">' . $comment_text . '</span>',
					'<span class="comment-date">' . $date . '</span>',
					'<span class="comment-post">' . $post . '</span>'
					), $format
				);

				$output .= sprintf( $comment_li_fmt, $comment_item_style, $comment_content );
			} // foreach comment
		} else {
			$output = sprintf( $comment_li_fmt, '', __( 'No recent comments available.', 'better-recent-comments' ) );
		} // if comments

		return apply_filters( 'better_recent_comments_list', sprintf( '<ul id="better-recent-comments" class="%s">%s</ul>', $comments_list_class, $output ) );
	}

// get_comments_list

	private static function comment_link_from( $format ) {
		$link_from = 'post';
		if ( false === strpos( $format, 'post' ) ) {
			$link_from = 'date';
			if ( false === strpos( $format, 'date' ) ) {
				$link_from = 'comment';
			}
		}
		return apply_filters( 'better_recent_comments_link_from', $link_from );
	}

}
// end class Better_Recent_Comments_Util