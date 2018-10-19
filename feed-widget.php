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
			'classname' => esc_html__( 'feed_widget', 'context-feed-widget' ),
			'description' => esc_html__( 'Displays RSS links based on what page its on.', 'context-feed-widget' ),
		);
		parent::__construct( 
            'feed_widget', 
            esc_html__( 'Feed Widget', 'context-feed-widget' ), 
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
        echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		
        $main_feed = get_feed_link();
        ?>
        <p>
            <a href="<?php echo $main_feed; ?>">
                <i class="fas fa-rss"></i>
                <abbr title="Really Simple Syndication">RSS</abbr>
                Feed
            </a>
            - Plug it into your favorite feed reader.
        </p>
        <?php
        $sub_feed = array();
        if ( is_category() ) {
            $tax = get_the_category()[0];
            $sub_feed = array( 
                'name'          => $tax->name . ' Category',
                'description'   => 'A feed for just the &ldquo;' . $tax->name . '&rdquo; category.',
                'link'          => get_term_feed_link($tax->term_id, 'category')
            );
        } else if ( is_tag() ) {
            $tax = get_the_tags(false)[0];
            $sub_feed = array( 
                'name'          => $tax->name . ' Tag',
                'description'   => 'A feed for just the &ldquo;' . $tax->name . '&rdquo; tag.',
                'link'          => get_term_feed_link($tax->term_id, 'post_tag')
            );
        } else if ( is_single() ) {
            $sub_feed = array( 
                'name'          => 'Comments',
                'description'   => 'A feed for just the comments on this post.',
                'link'          => get_post_comments_feed_link()
            );
        }
        
        if ( $sub_feed ): ?>
            <p>
                <a href="<?php echo $sub_feed['link']; ?>">
                    <i class="fas fa-rss"></i>
                    <?php echo $sub_feed['name']; ?>
                    <abbr title="Really Simple Syndication">RSS</abbr>
                </a>
                - <?php echo $sub_feed['description']; ?>
            </p>
        <?php
        endif;
		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		// outputs the options form on admin
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'New title', 'context-feed-widget' );
		?>
		<p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
                <?php esc_attr_e( 'Title:', 'context-feed-widget' ); ?>
            </label> 
		  <input class="widefat" 
                 id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
                 name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" 
                 type="text" 
                 value="<?php echo esc_attr( $title ); ?>">
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
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';

		return $instance;
	}
}

add_action( 'widgets_init', function(){
	register_widget( 'Contextual_Feed_Widget' );
});