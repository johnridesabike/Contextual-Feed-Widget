<?php
/**
 * @package Contextual_Feed_Widget
 * @version 0.1
 */
/*
Plugin Name: Contextual Feed Widget
Plugin URI: https://johnridesa.bike/category/web-development/
Description: This widget displays appropriate RSS feed links based on what page the user is viewing.
Author: John Jackson
Version: 0.1
Author URI: https://johnridesa.bike/
Text Domain: context-feed-widget
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Contextual_Feed_Widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array( 
			'classname' => esc_html__( 'widget_contextual_feed', 'context-feed-widget' ),
			'description' => esc_html__( 'Displays RSS links based on what page its on.', 'context-feed-widget' ),
		);
		parent::__construct( 
            'contextual_feed_widget', 
            esc_html__( 'Contextual Feed Widget', 'context-feed-widget' ), 
            $widget_ops );
        /*$this->settings = array(
            'title' => 'Title:',
            'text_before' => 'Text to include before the links:',
            'text_after' => 'Text to include after the links:' );
        /*$this->settings = array(
            'title'               => array( esc_attr__( 'Title:', 
                                                        'context-feed-widget' ),
                                            esc_html__( '', 'context-feed-widget' ) ),
            'main_description'    => array( esc_attr__( 'Main feed description:', 
                                                        'context-feed-widget' ),
                                            esc_html__( ' - Plug it into your favorite feed reader.', 
                                                        'context-feed-widget') ),
            'cat_description'     => array( esc_attr__( 'Category feed description:', 
                                                        'context-feed-widget' ),
                                            esc_html__( ' - A feed just for the &ldquo;%name%&rdquo; category.', 
                                                        'context-feed-widget') ),
            'tag_description'     => array( esc_attr__( 'Tag feed description:', 
                                                        'context-feed-widget' ),
                                            esc_html__( ' - A feed just for items tagged &ldquo;%name%&rdquo;.', 
                                                        'context-feed-widget') ),
            'comment_description' => array( esc_attr__( 'Comments feed description:', 
                                                        'context-feed-widget' ),
                                            esc_html__( ' - A feed for just the comments on this post.', 
                                                        'context-feed-widget') ) );*/
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		// outputs the content of the widget
        
        foreach (array('title', 'text_before', 'text_after') as $key)
            $instance[$key] = ! empty( $instance[$key] ) ? $instance[$key] : '';
        
        echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) )
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        if ( ! empty( $instance['text_before'] ) )
            echo '<div class="text-before">' . apply_filters( 'widget_text', $instance['text_before'] ) . '</div>';
        ?>
        <p>
            <a href="<?php echo get_feed_link(); ?>">
                <span class="fas fa-rss"></span>
                <?php esc_html_e( 'Feed for all posts', 'context-feed-widget' ); ?>
            </a>
        </p>
        <?php
        $sub_feed = array();
        if ( is_category() ) {
            $tax = get_queried_object();
            $sub_feed = array( 
                'name'          => sprintf( esc_html__('Feed for only &ldquo;%s&rdquo; posts', 'context-feed-widget' ), 
                                            $tax->name ),
                'link'          => get_term_feed_link($tax->term_id, 'category') );
        } else if ( is_tag() ) {
            $tax = get_queried_object();
            $sub_feed = array( 
                'name'          => sprintf( esc_html__('Feed for only posts tagged &ldquo;%s&rdquo;', 'context-feed-widget' ), 
                                            $tax->name ),
                'link'          => get_term_feed_link($tax->term_id, 'post_tag') );
        } else if ( is_single() ) {
            $sub_feed = array( 
                'name'          => sprintf( esc_html__( "Feed for comments on &ldquo;%s&rdquo;" , 'context-feed-widget' ),
                                           get_the_title() ),
                'link'          => get_post_comments_feed_link()
            );
        }
        
        if ( $sub_feed ): ?>
            <p>
                <a href="<?php echo $sub_feed['link']; ?>">
                    <span class="fas fa-rss"></span>
                    <?php echo $sub_feed['name']; ?>
                </a>
            </p>
        <?php
        endif;
        ?>
        <p>
            <a href="<?php bloginfo('comments_rss2_url') ?>">
                <span class="fas fa-rss"></span>
                <?php esc_html_e( 'Feed for all comments', 'context-feed-widget' ); ?>
            </a>
        </p>
        <?php
        if ( ! empty( $instance['text_after'] ) )
            echo '<div class="text-before">' . apply_filters( 'widget_text', $instance['text_after'] ) . '</div>';
		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		// outputs the options form on admin
        foreach( array('title', 'text_before', 'text_after') as $key => $value )
            $instance[$key] = ! empty( $instance[$key] ) ? $instance[$key] : '';
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
                <?php echo esc_html__('Title:', 'context-feed-widget'); ?>
            </label> 
            <input  class="widefat" 
                    id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
                    name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" 
                    type="text" 
                    value="<?php echo esc_html( $instance['title'] ); ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'text_before' ) ); ?>">
                <?php echo esc_html__('Text to include before the links:', 'context-feed-widget'); ?>
            </label> 
            <textarea  class="widefat" rows="16" cols="20" id="<?php echo esc_attr( $this->get_field_id( 'text_before' ) ); ?>" 
                        name="<?php echo esc_attr( $this->get_field_name( 'text_before' ) ); ?>" 
                      ><?php echo esc_textarea( $instance['text_before'] ); ?></textarea>
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'text_after' ) ); ?>">
                <?php echo esc_html__('Text to include after the links:', 'context-feed-widget'); ?>
            </label> 
            <textarea  class="widefat" rows="16" cols="20" id="<?php echo esc_attr( $this->get_field_id( 'text_after' ) ); ?>" 
                        name="<?php echo esc_attr( $this->get_field_name( 'text_after' ) ); ?>" 
                      ><?php echo esc_textarea( $instance['text_after'] ); ?></textarea>
        </p>
        <?php
        //echo '<p>' . __( 'Use %name% for the name of the selected taxonomy.', 'context-feed-widget' ) . '</p>';
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
		$instance = array();
        foreach( array('title', 'text_before', 'text_after') as $key)
            $instance[$key] = ( ! empty( $new_instance[$key] ) ) ? sanitize_text_field( $new_instance[$key] ) : '';

		return $instance;
	}
}

add_action( 'widgets_init', function(){
	register_widget( 'Contextual_Feed_Widget' );
});