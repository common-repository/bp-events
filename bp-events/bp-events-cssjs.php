<?php

/**
 * bp_events_add_js()
 */
function bp_events_add_js() {
  global $bp;

	if ( $bp->current_component == $bp->events->slug )
		wp_enqueue_script( 'bp-events-js', get_stylesheet_directory_uri() . '/events/_inc/js/general.js' );
}
add_action( 'template_redirect', 'bp_events_add_js', 1 );

/**
 * events_directory_events_js()
 */
function events_directory_events_js() {
	global $bp;

    if ( $bp->current_component == $bp->events->slug && empty( $bp->current_action ) ) {
		$bp->is_directory = true;

		wp_enqueue_script( 'bp-events-directory-events', get_stylesheet_directory_uri() . '/events/_inc/js/directory-events.js', array( 'jquery', 'jquery-livequery-pack' ) );
	}
}
add_action( 'template_redirect', 'events_directory_events_js', 1 );

/**
 * bp_events_add_screen_css()
 */
function bp_events_add_screen_css() {
	wp_enqueue_style( 'bp-events-screen', get_stylesheet_directory_uri() . '/events/_inc/css/screen.css' );
	wp_enqueue_style( 'bp-events-calendar', get_stylesheet_directory_uri() . '/events/_inc/css/widget-events.css' );
}
add_action( 'wp_print_styles', 'bp_events_add_screen_css' );

?>
