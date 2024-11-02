<?php

/* Register widgets for events component */
function events_register_widgets() {
	add_action('widgets_init', create_function('', 'return register_widget("BP_Events_Widget");') );
//	add_action('widgets_init', create_function('', 'return register_widget("BP_Events_Calendar_Widget");') );
}
add_action( 'plugins_loaded', 'events_register_widgets' );

/*** EVENTS WIDGET *****************/

class BP_Events_Widget extends WP_Widget {
	function bp_events_widget() {
		parent::WP_Widget( false, $name = __( 'Events', 'bp-events' ) );

		if ( is_active_widget( false, false, $this->id_base ) )
			wp_enqueue_script( 'events_widget_events_list-js', get_stylesheet_directory_uri() . '/events/_inc/js/widget-events.js', array('jquery', 'jquery-livequery-pack') );
	}

	function widget($args, $instance) {
		global $bp;

	    extract( $args );

		echo $before_widget;
		echo $before_title
		   . $widget_name
		   . $after_title; ?>

		<?php if ( bp_has_site_events( 'type=upcoming&per_page=' . $instance['max_events'] . '&max=' . $instance['max_events'] ) ) : ?>
			<div class="item-options" id="events-list-options">
				<span class="ajax-loader" id="ajax-loader-events"></span>
				<a href="<?php echo site_url() . '/' . $bp->events->slug ?>" id="upcoming-events" class="selected"><?php _e("Upcoming", 'bp-events') ?></a> |
				<a href="<?php echo site_url() . '/' . $bp->events->slug ?>" id="newest-events"><?php _e("Newest", 'bp-events') ?></a> |
				<a href="<?php echo site_url() . '/' . $bp->events->slug ?>" id="recently-active-events"><?php _e("Active", 'bp-events') ?></a> |
				<a href="<?php echo site_url() . '/' . $bp->events->slug ?>" id="popular-events"><?php _e("Popular", 'bp-events') ?></a>
			</div>

			<ul id="events-list" class="item-list">
				<?php while ( bp_site_events() ) : bp_the_site_event(); ?>
					<li>
						<div class="item-avatar">
							<a href="<?php bp_the_site_event_link() ?>"><?php bp_the_site_event_avatar_thumb() ?></a>
						</div>

						<div class="item">
							<div class="item-title"><a href="<?php bp_the_site_event_link() ?>" title="<?php bp_the_site_event_name() ?>"><?php bp_the_site_event_name() ?></a></div>
							<div class="item-meta"><span class="activity"><?php bp_the_site_event_member_count() ?></span></div>
						</div>
					</li>

				<?php endwhile; ?>
			</ul>
			<?php wp_nonce_field( 'events_widget_events_list', '_wpnonce-events' ); ?>
			<input type="hidden" name="events_widget_max" id="events_widget_max" value="<?php echo attribute_escape( $instance['max_events'] ); ?>" />

		<?php else: ?>

			<div class="widget-error">
				<?php _e('There are no events to display.', 'bp-events') ?>
			</div>

		<?php endif; ?>

		<?php echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['max_events'] = strip_tags( $new_instance['max_events'] );

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'max_events' => 5 ) );
		$max_events = strip_tags( $instance['max_events'] );
		?>

		<p><label for="bp-events-widget-events-max"><?php _e('Max events to show:', 'bp-events'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_events' ); ?>" name="<?php echo $this->get_field_name( 'max_events' ); ?>" type="text" value="<?php echo attribute_escape( $max_events ); ?>" style="width: 30%" /></label></p>
	<?php
	}
}

function events_ajax_widget_events_list() {
	global $bp;

	check_ajax_referer('events_widget_events_list');

	switch ( $_POST['filter'] ) {
		case 'upcoming-events':
			$type = 'upcoming';
		break;
		case 'newest-events':
			$type = 'newest';
		break;
		case 'recently-active-events':
			$type = 'active';
		break;
		case 'popular-events':
			$type = 'popular';
		break;
	}

	if ( bp_has_site_events( 'type=' . $type . '&per_page=' . $_POST['max_events'] . '&max=' . $_POST['max_events'] ) ) : ?>
		<?php echo "0[[SPLIT]]"; ?>

		<ul id="events-list" class="item-list">
			<?php while ( bp_site_events() ) : bp_the_site_event(); ?>
				<li>
					<div class="item-avatar">
						<a href="<?php bp_the_site_event_link() ?>"><?php bp_the_site_event_avatar_thumb() ?></a>
					</div>

					<div class="item">
						<div class="item-title"><a href="<?php bp_the_site_event_link() ?>" title="<?php bp_the_site_event_name() ?>"><?php bp_the_site_event_name() ?></a></div>
						<div class="item-meta">
							<span class="activity">
								<?php
								if ( 'newest-events' == $_POST['filter'] ) {
									bp_the_site_event_date_created();
								} else if ( 'recently-active-events' == $_POST['filter'] ) {
									bp_the_site_event_last_active();
								} else if ( 'popular-events' == $_POST['filter'] ) {
									bp_the_site_event_member_count();
								} else if ( 'upcoming-events' == $_POST['filter'] ) {
									bp_the_site_event_upcoming();
								}
								?>
							</span>
						</div>
					</div>
				</li>

			<?php endwhile; ?>
		</ul>
		<?php wp_nonce_field( 'events_widget_events_list', '_wpnonce-events' ); ?>
		<input type="hidden" name="events_widget_max" id="events_widget_max" value="<?php echo attribute_escape( $_POST['max_events'] ); ?>" />

	<?php else: ?>

		<?php echo "-1[[SPLIT]]<li>" . __("No events matched the current filter.", 'bp-events'); ?>

	<?php endif;

}
add_action( 'wp_ajax_widget_events_list', 'events_ajax_widget_events_list' );


/*** EVENTS WIDGET *****************
 *
 * STILL WORKING ON THIS - WORKS BUT LOOKS UGLY!
 *
/*
class BP_Events_Calendar_Widget extends WP_Widget {
	function bp_events_calendar_widget() {
		parent::WP_Widget( false, $name = __( 'Event Calendar', 'bp-events' ) );

		if ( is_active_widget( false, false, $this->id_base ) )
			wp_enqueue_script( 'events_widget_events_list-js', get_stylesheet_directory_uri() . '/events/_inc/js/widget-events.js', array('jquery', 'jquery-livequery-pack') );
	}

	function widget($args, $instance) {
		global $bp;

	    extract( $args );

		echo $before_widget;
		echo $before_title
		   . $widget_name
		   . $after_title; ?>

		<?php echo mini_calendar(); ?>

		<?php echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['max_events'] = strip_tags( $new_instance['max_events'] );

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'max_events' => 5 ) );
		$max_events = strip_tags( $instance['max_events'] );
		?>

		<p><label for="bp-events-widget-events-max"><?php _e('Max events to show:', 'bp-events'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_events' ); ?>" name="<?php echo $this->get_field_name( 'max_events' ); ?>" type="text" value="<?php echo attribute_escape( $max_events ); ?>" style="width: 30%" /></label></p>
	<?php
	}
}
*/

?>
