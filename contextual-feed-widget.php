<?php
/**
 * @package Contextual_Feed_Widget
 * @version 0.1
 * @link https://johnridesa.bike/category/web-development/
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
	 * Outputs the css classes for feed icons. Defaults to Dashicons. Additional classes can be specified.
	 *
	 * @param string $classes
	 */
    private function the_icon_class( $classes = '' ) {
        echo 'class="dashicons dashicons-rss ' . $classes . '"';
    }

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		// outputs the content of the widget
        echo $args['before_widget'];
        foreach( array('title', 'text_before') as $key )
            $instance[$key] = ! empty( $instance[$key] ) ? $instance[$key] : '';
		if ( ! empty( $instance['title'] ) )
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        if ( ! empty( $instance['text_before'] ) )
            echo '<div class="text-before">' . apply_filters( 'widget_text', $instance['text_before'] ) . '</div>';
        ?>
        <p class="feed-link">
            <a href="<?php echo get_feed_link(); ?>">
                <span <?php $this->the_icon_class();?>></span>
                <?php esc_html_e( 'Feed for all entries', 'context-feed-widget' ); ?>
            </a>
        </p>
        <?php
        $sub_feed = array();
        if ( is_category() ) {
            $tax = get_queried_object();
            $sub_feed = array( 
                'name'  => sprintf( esc_html__('Feed for only &ldquo;%s&rdquo; entries', 'context-feed-widget' ), 
                                    $tax->name ),
                'link'  => get_term_feed_link($tax->term_id, 'category') );
        } else if ( is_tag() ) {
            $tax = get_queried_object();
            $sub_feed = array( 
                'name'  => sprintf( esc_html__('Feed for only entries tagged &ldquo;%s&rdquo;', 'context-feed-widget' ), 
                                    $tax->name ),
                'link'  => get_term_feed_link($tax->term_id, 'post_tag') );
        } else if ( is_single() ) {
            $sub_feed = array( 
                'name'  => sprintf( esc_html__( "Feed for comments on &ldquo;%s&rdquo;" , 'context-feed-widget' ),
                                    get_the_title() ),
                'link'  => get_post_comments_feed_link()
            );
        }
        if ( $sub_feed ): ?>
            <p class="feed-link">
                <a href="<?php echo $sub_feed['link']; ?>">
                    <span <?php $this->the_icon_class();?>></span>
                    <?php echo $sub_feed['name']; ?>
                </a>
            </p>
        <?php
        endif;
        ?>
        <p class="feed-link">
            <a href="<?php bloginfo('comments_rss2_url') ?>">
                <span <?php $this->the_icon_class();?>></span>
                <?php esc_html_e( 'Feed for all comments', 'context-feed-widget' ); ?>
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
        foreach( array('title', 'text_before') as $key )
            $instance[$key] = ! empty( $instance[$key] ) ? $instance[$key] : '';
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
            <textarea class="widefat" rows="8" cols="20" 
                      id="<?php esc_attr_e( $this->get_field_id( 'text_before' ) ); ?>" 
                      name="<?php esc_attr_e( $this->get_field_name( 'text_before' ) ); ?>" 
                      ><?php echo esc_textarea( $instance['text_before'] ); ?></textarea>
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
		$new_instance = wp_parse_args( $new_instance, array(
			'title' => '',
			'text_before' => '',
		) );
        $new_instance['title'] = sanitize_text_field( $new_instance['title'] );
		if ( current_user_can( 'unfiltered_html' ) )
			$new_instance['text_before'] = $new_instance['text_before'];
		else
			$new_instance['text_before'] = wp_kses_post( $new_instance['text_before'] );
		return $new_instance;
	}
}

add_action( 'widgets_init', function(){
	register_widget( 'Contextual_Feed_Widget' );
});