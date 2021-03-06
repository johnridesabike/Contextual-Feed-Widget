<?php
/**
 * Contextual Feed Widget Plugin
 *
 * @package Contextual_Feed_Widget
 * @version 1.0
 * @link https://johnridesa.bike/tag/contextual-feed-widget/
 */

/*
Plugin Name: Contextual Feed Widget
Plugin URI: https://johnridesa.bike/tag/contextual-feed-widget/
Description: This widget displays different feed links depending on which page is displayed.
Author: John Jackson
Version: 1.0.1
Author URI: https://johnridesa.bike/
Text Domain: context-feed-widget
License: GPL-3.0+
License URI: https://www.gnu.org/licenses/gpl-3.0.txt
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The class for the Contextual Feed Widget.
 */
class Contextual_Feed_Widget extends WP_Widget {
	/**
	 * Default instance.
	 *
	 * @var array
	 */
	protected $default_instance = array(
		'title'       => '',
		'text_before' => '',
		'icon_code'   => '<span class="dashicons dashicons-rss"></span>',
		// 'icon_code'   => '<svg class="svg-icon" width="16" height="16" aria-hidden="true" role="img" focusable="false"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" ><path d="M2,8.667V12c5.515,0,10,4.485,10,10h3.333C15.333,14.637,9.363,8.667,2,8.667z M2,2v3.333 c9.19,0,16.667,7.477,16.667,16.667H22C22,10.955,13.045,2,2,2z M4.5,17C3.118,17,2,18.12,2,19.5S3.118,22,4.5,22S7,20.88,7,19.5 S5.882,17,4.5,17z"></path></svg>',
	);

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'   => 'widget_contextual_feed',
			'description' => esc_html__(
				'Displays appropriate RSS links based on which page is open.',
				'context-feed-widget'
			),
		);
		parent::__construct(
			'contextual_feed_widget',
			esc_html__( 'Contextual Feed Widget', 'context-feed-widget' ),
			$widget_ops
		);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args The widget arguments.
	 * @param array $instance The widget instance.
	 */
	public function widget( $args, $instance ) {
		$instance = array_merge( $this->default_instance, $instance );
		echo $args['before_widget']; /* phpcs:ignore */
		if ( ! empty( $instance['title'] ) ) {
			// phpcs:ignore
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		if ( ! empty( $instance['text_before'] ) ) {
			// phpcs:ignore
			echo '<div class="text-before">' . apply_filters( 'widget_text', $instance['text_before'] ) . '</div>';
		}
		?>
		<ul>
		<li class="feed-link">
			<a href="<?php echo get_feed_link(); /* phpcs:ignore */ ?>">
				<?php echo $instance['icon_code']; /* phpcs:ignore */ ?>
				<?php esc_html_e( 'Feed for all entries', 'context-feed-widget' ); ?>
			</a>
		</li>
		<?php
		$sub_feed = array();
		if ( is_category() ) {
			$tax      = get_queried_object();
			$sub_feed = array(
				/* Translators: %s: the name of the category */
				'name' => sprintf( esc_html__( 'Feed for &ldquo;%s&rdquo; entries', 'context-feed-widget' ), $tax->name ),
				'link' => get_term_feed_link( $tax->term_id, 'category' ),
			);
		} elseif ( is_tag() ) {
			$tax      = get_queried_object();
			$sub_feed = array(
				/* Translators: %s: the name of the tag */
				'name' => sprintf( esc_html__( 'Feed for entries tagged &ldquo;%s&rdquo;', 'context-feed-widget' ), $tax->name ),
				'link' => get_term_feed_link( $tax->term_id, 'post_tag' ),
			);
		} elseif ( is_single() ) {
			$sub_feed = array(
				/* Translators: %s: the name of the single post */
				'name' => sprintf( esc_html__( 'Feed for comments on &ldquo;%s&rdquo;', 'context-feed-widget' ), get_the_title() ),
				'link' => get_post_comments_feed_link(),
			);
		}
		if ( $sub_feed ) :
			?>
			<li class="feed-link">
				<a href="<?php echo $sub_feed['link']; /* phpcs:ignore */ ?>">
					<?php echo $instance['icon_code']; /* phpcs:ignore */ ?>
					<?php echo $sub_feed['name']; /* phpcs:ignore */ ?>
				</a>
			</li>
			<?php
		endif;
		?>
			<li class="feed-link">
				<a href="<?php bloginfo( 'comments_rss2_url' ); ?>">
					<?php echo $instance['icon_code']; /* phpcs:ignore */ ?>
					<?php esc_html_e( 'Feed for all site comments', 'context-feed-widget' ); ?>
				</a>
			</li>
		</ul>
		<?php
		echo $args['after_widget']; /* phpcs:ignore */
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->default_instance );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title:', 'context-feed-widget' ); ?>
			</label> 
			<input  class="widefat" type="text" 
					id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
					name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" 
					value="<?php echo esc_html( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'text_before' ) ); ?>">
				<?php esc_html_e( 'Text to include before the links:', 'context-feed-widget' ); ?>
			</label> 
			<textarea class="widefat" rows="4" cols="20" 
				id="<?php echo esc_attr( $this->get_field_id( 'text_before' ) ); ?>" 
				name="<?php echo esc_attr( $this->get_field_name( 'text_before' ) ); ?>" 
				><?php echo esc_textarea( $instance['text_before'] ); ?></textarea>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'icon_code' ) ); ?>">
				<?php esc_html_e( 'HTML code to include before each link (e.g. for icons):', 'context-feed-widget' ); ?>
			</label> 
			<textarea class="widefat" rows="4" cols="20"
				id="<?php echo esc_attr( $this->get_field_id( 'icon_code' ) ); ?>" 
				name="<?php echo esc_attr( $this->get_field_name( 'icon_code' ) ); ?>" 
				><?php echo esc_textarea( $instance['icon_code'] ); ?></textarea>
		</p>
		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options.
	 * @param array $old_instance The previous options.
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = array_merge( $this->default_instance, $old_instance );
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		if ( current_user_can( 'unfiltered_html' ) ) {
			$instance['text_before'] = $new_instance['text_before'];
			$instance['icon_code']   = $new_instance['icon_code'];
		} else {
			$instance['text_before'] = wp_kses_post( $new_instance['text_before'] );
			$instance['icon_code']   = wp_kses_post( $new_instance['icon_code'] );
		}
		return $instance;
	}
}

add_action(
	'widgets_init',
	function() {
		register_widget( 'Contextual_Feed_Widget' );
	}
);
