<?php
/**
 * @package Contextual_Feed_Widget
 * @version 1.0
 * @link https://johnridesa.bike/tag/contextual-feed-widget/
 */
/*
Plugin Name: Contextual Feed Widget
Plugin URI: https://johnridesa.bike/category/web-development/
Description: This widget displays appropriate RSS feed links based on what page the user is viewing.
Author: John Jackson
Version: 0.1
Author URI: https://johnridesa.bike/
Text Domain: context-feed-widget
License: GPL-3.0+
License URI: https://www.gnu.org/licenses/gpl-3.0.txt
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) )
	die;

class Contextual_Feed_Widget extends WP_Widget {
    
    /**
     * Default instance.
     *
     * @var array
     */
    protected $default_instance = array(
        'title' => '',
        'text_before' => '',
        'icon_code' => '<span class="dashicons dashicons-rss"></span>'
    );

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array( 
			'classname' => 'widget_contextual_feed',
			'description' => esc_html__( 'Displays appropriate RSS links based on which page is open.', 
                                         'context-feed-widget' ),
		);
		parent::__construct( 
            'contextual_feed_widget', 
            esc_html__( 'Contextual Feed Widget', 'context-feed-widget' ), 
            $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		// outputs the content of the widget
		$instance = array_merge( $this->default_instance, $instance );
        echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) )
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        if ( ! empty( $instance['text_before'] ) )
            echo '<div class="text-before">' . apply_filters( 'widget_text', $instance['text_before'] ) . '</div>';
        ?>
        <p class="feed-link">
            <a href="<?php echo get_feed_link(); ?>">
                <?php echo $instance['icon_code']; ?>
                <?php esc_html_e( 'Feed for all entries', 'context-feed-widget' ); ?>
            </a>
        </p>
        <?php
        $sub_feed = array();
        if ( is_category() ) {
            $tax = get_queried_object();
            $sub_feed = array( 
                'name'  => sprintf( esc_html__('Feed for &ldquo;%s&rdquo; entries', 'context-feed-widget' ), 
                                    $tax->name ),
                'link'  => get_term_feed_link($tax->term_id, 'category') );
        } elseif ( is_tag() ) {
            $tax = get_queried_object();
            $sub_feed = array( 
                'name'  => sprintf( esc_html__('Feed for entries tagged &ldquo;%s&rdquo;', 'context-feed-widget' ), 
                                    $tax->name ),
                'link'  => get_term_feed_link($tax->term_id, 'post_tag') );
        } elseif ( is_single() ) {
            $sub_feed = array( 
                'name'  => sprintf( esc_html__( "Feed for comments on &ldquo;%s&rdquo;" , 'context-feed-widget' ),
                                    get_the_title() ),
                'link'  => get_post_comments_feed_link() );
        }
        if ( $sub_feed ): ?>
            <p class="feed-link">
                <a href="<?php echo $sub_feed['link']; ?>">
                    <?php echo $instance['icon_code']; ?>
                    <?php echo $sub_feed['name']; ?>
                </a>
            </p>
        <?php
        endif;
        ?>
        <p class="feed-link">
            <a href="<?php bloginfo('comments_rss2_url') ?>">
                <?php echo $instance['icon_code']; ?>
                <?php esc_html_e( 'Feed for all site comments', 'context-feed-widget' ); ?>
            </a>
        </p>
        <?php
		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		// outputs the options form on admin
        $instance = wp_parse_args( (array) $instance, $this->default_instance );
        ?>
        <p>
            <label for="<?php esc_attr_e( $this->get_field_id( 'title' ) ); ?>">
                <?php esc_html_e('Title:', 'context-feed-widget'); ?>
            </label> 
            <input  class="widefat" type="text" 
                    id="<?php esc_attr_e( $this->get_field_id( 'title' ) ); ?>" 
                    name="<?php esc_attr_e( $this->get_field_name( 'title' ) ); ?>" 
                    value="<?php esc_html_e( $instance['title'] ); ?>" />
        </p>
        <p>
            <label for="<?php esc_attr_e( $this->get_field_id( 'text_before' ) ); ?>">
                <?php esc_html_e('Text to include before the links:', 'context-feed-widget'); ?>
            </label> 
            <textarea class="widefat" rows="4" cols="20" 
                      id="<?php esc_attr_e( $this->get_field_id( 'text_before' ) ); ?>" 
                      name="<?php esc_attr_e( $this->get_field_name( 'text_before' ) ); ?>" 
                      ><?php echo esc_textarea( $instance['text_before'] ); ?></textarea>
        </p>
        <p>
            <label for="<?php esc_attr_e( $this->get_field_id( 'icon_code' ) ); ?>">
                <?php esc_html_e('HTML code to include before each link (e.g. for icons):', 'context-feed-widget'); ?>
            </label> 
            <textarea class="widefat" rows="4" cols="20" 
                      id="<?php esc_attr_e( $this->get_field_id( 'icon_code' ) ); ?>" 
                      name="<?php esc_attr_e( $this->get_field_name( 'icon_code' ) ); ?>" 
                      ><?php echo esc_textarea( $instance['icon_code'] ); ?></textarea>
        </p>
        <?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
		$instance = array_merge( $this->default_instance, $old_instance );
        $instance['title'] = sanitize_text_field( $new_instance['title'] );
		if ( current_user_can( 'unfiltered_html' ) ) {
			$instance['text_before'] = $new_instance['text_before'];
			$instance['icon_code'] = $new_instance['icon_code'];
        } else {
			$instance['text_before'] = wp_kses_post( $new_instance['text_before'] );
			$instance['icon_code'] = wp_kses_post( $new_instance['icon_code'] );
        }
		return $instance;
	}
}

add_action( 'widgets_init', function() {
	register_widget( 'Contextual_Feed_Widget' );
} );