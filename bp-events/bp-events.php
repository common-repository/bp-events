<?php
/*
Plugin Name: BP Events
Plugin URI: http://erwingerrits.com/
Description: Allows users to create, join and participate in events. (Updated for BuddyPress 1.1 by Marius Ooms and John James Jacoby)
Author: Erwin Gerrits
Author URI: http://erwingerrits.com
Version: 1.1
License: (Events: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html)
Site Wide Only: true
*/

//define ( 'BP_EVENTS_IS_INSTALLED', 1 );
//define ( 'BP_EVENTS_VERSION', '1.1' );
define ( 'BP_EVENTS_DB_VERSION', '2100' );

/* Define the slug for the component */
if ( !defined( 'BP_EVENTS_SLUG' ) )
	define ( 'BP_EVENTS_SLUG', 'events' );
		/*
require ( WP_PLUGIN_DIR . '/bp-events/bp-events-classes.php' );
require ( WP_PLUGIN_DIR . '/bp-events/bp-events-templatetags.php' );
require ( WP_PLUGIN_DIR . '/bp-events/bp-events-widgets.php' );
require ( WP_PLUGIN_DIR . '/bp-events/bp-events-filters.php' );
	*/
/* Include deprecated functions if settings allow */
//if ( !defined( 'BP_IGNORE_DEPRECATED' ) )
//	require ( WP_PLUGIN_DIR . '/bp-events/deprecated/bp-events-deprecated.php' );

function events_install() {
	global $wpdb, $bp;

	if ( !empty($wpdb->charset) )
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";

	$sql[] = "CREATE TABLE {$bp->events->table_name} (
	  	id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			creator_id bigint(20) NOT NULL,
	  	name varchar(100) NOT NULL,
	  	slug varchar(100) NOT NULL,
			tagline varchar(100) NOT NULL,
	  	description longtext NOT NULL,
			news longtext NOT NULL,
			location varchar(100) NOT NULL,
			status varchar(10) NOT NULL DEFAULT 'public',
			enable_wire tinyint(1) NOT NULL DEFAULT '1',
			enable_forum tinyint(1) NOT NULL DEFAULT '1',
			date_created datetime NOT NULL,
			date_start datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
      date_end datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
      is_allday tinyint(1) NOT NULL DEFAULT '0',
			link_group int(11) NOT NULL DEFAULT '0',
		  KEY creator_id (creator_id),
		  KEY status (status)
	 	   ) {$charset_collate};";

	$sql[] = "CREATE TABLE {$bp->events->table_name_members} (
	  	id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			event_id bigint(20) NOT NULL,
			user_id bigint(20) NOT NULL,
			inviter_id bigint(20) NOT NULL,
			is_admin tinyint(1) NOT NULL DEFAULT '0',
			is_mod tinyint(1) NOT NULL DEFAULT '0',
			user_title varchar(100) NOT NULL,
			date_modified datetime NOT NULL,
			comments longtext NOT NULL,
			is_confirmed tinyint(1) NOT NULL DEFAULT '0',
			is_banned tinyint(1) NOT NULL DEFAULT '0',
			invite_sent tinyint(1) NOT NULL DEFAULT '0',
			KEY event_id (event_id),
			KEY is_admin (is_admin),
			KEY is_mod (is_mod),
		 	KEY user_id (user_id),
			KEY inviter_id (inviter_id),
			KEY is_confirmed (is_confirmed)
	 	   ) {$charset_collate};";

	$sql[] = "CREATE TABLE {$bp->events->table_name_eventmeta} (
			id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			event_id bigint(20) NOT NULL,
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext DEFAULT NULL,
			KEY event_id (event_id),
			KEY meta_key (meta_key)
		   ) {$charset_collate};";

	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	dbDelta($sql);
	
/* On upgrade, handle moving of old event avatars */
	$events = events_get_all();

	foreach ( $events as $event ) {
		/* Don't fetch and move gravs, default images or empties */
		if ( empty($event->avatar_thumb) || strpos( $event->avatar_thumb, 'gravatar.com' ) || strpos( $event->avatar_thumb, 'identicon' ) || strpos( $event->avatar_thumb, 'none-thumbnail' ) )
			continue;

		$start = strpos( $event->avatar_thumb, 'blogs.dir' );

		if ( false !== $start ) {
			$avatar_thumb = WP_CONTENT_DIR . '/' . substr( $event->avatar_thumb, $start, strlen( $event->avatar_thumb ) );
			$avatar_full = WP_CONTENT_DIR . '/' . substr( $event->avatar_full, $start, strlen( $event->avatar_full ) );

			if ( !file_exists( $avatar_thumb ) || !file_exists( $avatar_full ) )
				continue;

			$upload_dir = events_avatar_upload_dir( $event->id );

			copy( $avatar_thumb, $upload_dir['path'] . '/' . basename($avatar_thumb) );
			copy( $avatar_full, $upload_dir['path'] . '/' . basename($avatar_full) );
		}
	}


	if ( function_exists('bp_wire_install') )
		events_wire_install();

	update_site_option( 'bp-events-db-version', BP_EVENTS_DB_VERSION );
}

function events_wire_install() {
	global $wpdb, $bp;

	if ( !empty($wpdb->charset) )
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";

	$sql[] = "CREATE TABLE {$bp->events->table_name_wire} (
	  	id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			item_id bigint(20) NOT NULL,
			user_id bigint(20) NOT NULL,
			content longtext NOT NULL,
			date_posted datetime NOT NULL,
			KEY item_id (item_id),
			KEY user_id (user_id)
	 	   ) {$charset_collate};";

	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	dbDelta($sql);
}

function events_setup_globals() {
	global $bp, $wpdb;

	/* For internal identification */
	$bp->events->id = 'events';

	$bp->events->table_name = $wpdb->base_prefix . 'bp_events';
	$bp->events->date_format = $wpdb->base_prefix . 'date_format';
	$bp->events->time_format = $wpdb->base_prefix . 'time_format';
	$bp->events->table_name_members = $wpdb->base_prefix . 'bp_events_members';
	$bp->events->table_name_eventmeta = $wpdb->base_prefix . 'bp_events_eventmeta';
	$bp->events->image_base = STYLESHEETPATH . '/events/_inc/images';
	$bp->events->format_notification_function = 'events_format_notifications';
	$bp->events->slug = BP_EVENTS_SLUG;
	$bp->events->date_format = get_option('date_format');
	$bp->events->time_format = get_option('time_format');

	/* Register this in the active components array */
	$bp->active_components[$bp->events->slug] = $bp->events->id;

	if ( function_exists('bp_wire_install') )
		$bp->events->table_name_wire = $wpdb->base_prefix . 'bp_events_wire';

	$bp->events->forbidden_names = apply_filters( 'events_forbidden_names', array( 'my-events', 'event-finder', 'create', 'invites', 'delete', 'add', 'admin', 'request-membership' ) );

	$bp->events->event_creation_steps = apply_filters( 'events_create_event_steps', array(
		'event-details' => array( 'name' => __( 'Event Details', 'bp-events' ), 'position' => 0 ),
		'event-dates' => array( 'name' => __( 'Event Dates', 'bp-events' ), 'position' => 10 ),
		'event-settings' => array( 'name' => __( 'Event Settings', 'bp-events' ), 'position' => 20 ),
		'event-avatar' => array( 'name' => __( 'Event Avatar', 'bp-events' ), 'position' => 30 ),
		'event-invites' => array( 'name' => __( 'Event Invites', 'bp-events' ), 'position' => 40 )
	) );

	$bp->events->valid_status = apply_filters( 'events_valid_status', array( 'public', 'private', 'hidden' ) );

	do_action( 'events_setup_globals' );
}
add_action( 'plugins_loaded', 'events_setup_globals', 5 );
add_action( 'admin_menu', 'events_setup_globals', 2 );

function events_setup_root_component() {
	/* Register 'events' as a root component */
	bp_core_add_root_component( BP_EVENTS_SLUG );
}
add_action( 'plugins_loaded', 'events_setup_root_component', 2 );

function events_check_installed() {
	global $wpdb, $bp;

	require ( WP_PLUGIN_DIR . '/bp-events/bp-events-admin.php' );

	/* Need to check db tables exist, activate hook no-worky in mu-plugins folder. */
	if ( get_site_option('bp-events-db-version') < BP_EVENTS_DB_VERSION )
		events_install();
}
add_action( 'admin_menu', 'events_check_installed' );

function events_add_admin_menu() {
	global $wpdb, $bp;

	if ( !is_site_admin() )
		return false;

	/* Add the administration tab under the "Site Admin" tab for site administrators */
	add_submenu_page( 'wpmu-admin.php', __("Events", 'bp-events'), __("Events", 'bp-events'), 1, "events_admin_settings", "events_admin_settings" );

	/* Add the administration tab under the "Site Admin" tab for site administrators */
	add_submenu_page( 'wpmu-admin.php', __("Events Settings", 'bp-events'), __("Events Settings", 'bp-events'), 2, "events_settings_admin_settings", "events_settings_admin_settings" );
}
add_action( 'admin_menu', 'events_add_admin_menu' );

function events_setup_nav() {
	global $bp, $current_blog;

	if ( $event_id = BP_Events_Event::event_exists($bp->current_action) ) {

		/* This is a single event page. */
		$bp->is_single_item = true;
		$bp->events->current_event = &new BP_Events_Event( $event_id );

		/* Using "item" not "event" for generic support in other components. */
		if ( is_site_admin() )
			$bp->is_item_admin = 1;
		else
			$bp->is_item_admin = events_is_user_admin( $bp->loggedin_user->id, $bp->events->current_event->id );

		/* If the user is not an admin, check if they are a moderator */
		if ( !$bp->is_item_admin )
			$bp->is_item_mod = events_is_user_mod( $bp->loggedin_user->id, $bp->events->current_event->id );

		/* Is the logged in user a member of the event? */
		$bp->events->current_event->is_user_member = ( is_user_logged_in() && events_is_user_member( $bp->loggedin_user->id, $bp->events->current_event->id ) ) ? true : false;

		/* Should this event be visible to the logged in user? */
		$bp->events->current_event->is_event_visible_to_member = ( 'public' == $bp->events->current_event->status || $is_member ) ? true : false;

      /* Pre 1.1 backwards compatibility - use $bp->events->current_event instead */
		$event_obj = &$bp->events->current_event;
	}

	/* Add 'Events' to the main navigation */
	bp_core_new_nav_item( array( 'name' => __('Events', 'bp-events'), 'slug' => $bp->events->slug, 'position' => 70, 'screen_function' => 'events_screen_my_events', 'default_subnav_slug' => 'my-events', 'item_css_id' => $bp->events->id ) );

	$events_link = $bp->loggedin_user->domain . $bp->events->slug . '/';

	/* Add the subnav items to the events nav item */
	bp_core_new_subnav_item( array( 'name' => __( 'My Events', 'bp-events' ), 'slug' => 'my-events', 'parent_url' => $events_link, 'parent_slug' => $bp->events->slug, 'screen_function' => 'events_screen_my_events', 'position' => 10, 'item_css_id' => 'events-my-events' ) );
	bp_core_new_subnav_item( array( 'name' => __( 'Create an Event', 'bp-events' ), 'slug' => 'create', 'parent_url' => $events_link, 'parent_slug' => $bp->events->slug, 'screen_function' => 'events_screen_create_event', 'position' => 20, 'user_has_access' => bp_is_home() ) );
	bp_core_new_subnav_item( array( 'name' => __( 'Invites', 'bp-events' ), 'slug' => 'invites', 'parent_url' => $events_link, 'parent_slug' => $bp->events->slug, 'screen_function' => 'events_screen_event_invites', 'position' => 30, 'user_has_access' => bp_is_home() ) );

	if ( $bp->current_component == $bp->events->slug ) {

		if ( bp_is_home() && !$bp->is_single_item ) {

			$bp->bp_options_title = __( 'My Events', 'bp-events' );

		} else if ( !bp_is_home() && !$bp->is_single_item ) {

			$bp->bp_options_avatar = bp_core_fetch_avatar( array( 'item_id' => $bp->displayed_user->id, 'type' => 'thumb' ) );
			$bp->bp_options_title = $bp->displayed_user->fullname;

		} else if ( $bp->is_single_item ) {
			// We are viewing a single event, so set up the
			// event navigation menu using the $bp->events->current_event global.

			/* When in a single event, the first action is bumped down one because of the
			   event name, so we need to adjust this and set the event name to current_item. */
			$bp->current_item = $bp->current_action;
			$bp->current_action = $bp->action_variables[0];
			array_shift($bp->action_variables);

			$bp->bp_options_title = $bp->events->current_event->name;

			if ( !$bp->bp_options_avatar = bp_core_fetch_avatar( array( 'item_id' => $bp->events->current_event->id, 'object' => 'event', 'type' => 'thumb', 'avatar_dir' => 'event-avatars', 'alt' => __( 'Event Avatar', 'bp-events' ) ) ) )
				$bp->bp_options_avatar = '<img src="' . attribute_escape( $event->avatar_full ) . '" class="avatar" alt="' . attribute_escape( $event->name ) . '" />';

			$event_link = $bp->root_domain . '/' . $bp->events->slug . '/' . $bp->events->current_event->slug . '/';

			// If this is a private or hidden event, does the user have access?
			if ( 'private' == $bp->events->current_event->status || 'hidden' == $bp->events->current_event->status ) {
				if ( $bp->events->current_event->is_user_member && is_user_logged_in() )
					$bp->events->current_event->user_has_access = true;
				else
					$bp->events->current_event->user_has_access = false;
			} else {
				$bp->events->current_event->user_has_access = true;
			}

			/* Reset the existing subnav items */
			bp_core_reset_subnav_items($bp->events->slug);

			/* Add a new default subnav item for when the events nav is selected. */
			bp_core_new_nav_default( array( 'parent_slug' => $bp->events->slug, 'screen_function' => 'events_screen_event_home', 'subnav_slug' => 'event' ) );

			/* Add the "Home" subnav item, as this will always be present */
			bp_core_new_subnav_item( array( 'name' => __( 'Home', 'bp-events' ), 'slug' => 'home', 'parent_url' => $event_link, 'parent_slug' => $bp->events->slug, 'screen_function' => 'events_screen_event_home', 'position' => 10, 'item_css_id' => 'event-home' ) );

			/* If the user is a event mod or more, then show the event admin nav item */
			if ( $bp->is_item_mod || $bp->is_item_admin )
				bp_core_new_subnav_item( array( 'name' => __( 'Admin', 'bp-events' ), 'slug' => 'admin', 'parent_url' => $event_link, 'parent_slug' => $bp->events->slug, 'screen_function' => 'events_screen_event_admin', 'position' => 20, 'user_has_access' => ( $bp->is_item_admin + (int)$bp->is_item_mod ), 'item_css_id' => 'event-admin' ) );

			// If this is a private event, and the user is not a member, show a "Request Membership" nav item.
			if ( is_user_logged_in() && !$bp->events->current_event->is_user_member && !events_check_for_membership_request( $bp->loggedin_user->id, $bp->events->current_event->id ) && $bp->events->current_event->status == 'private' )
				bp_core_new_subnav_item( array( 'name' => __( 'Request Membership', 'bp-events' ), 'slug' => 'request-membership', 'parent_url' => $event_link, 'parent_slug' => $bp->events->slug, 'screen_function' => 'events_screen_event_request_membership', 'position' => 30 ) );

			if ( $bp->events->current_event->enable_forum && function_exists('bp_forums_setup') )
				bp_core_new_subnav_item( array( 'name' => __( 'Forum', 'bp-events' ), 'slug' => 'forum', 'parent_url' => $event_link, 'parent_slug' => $bp->events->slug, 'screen_function' => 'events_screen_event_forum', 'position' => 40, 'user_has_access' => $bp->events->current_event->user_has_access, 'item_css_id' => 'event-forum' ) );

			if ( $bp->events->current_event->enable_wire && function_exists('bp_wire_install') )
				bp_core_new_subnav_item( array( 'name' => __( 'Wire', 'bp-events' ), 'slug' => BP_WIRE_SLUG, 'parent_url' => $event_link, 'parent_slug' => $bp->events->slug, 'screen_function' => 'events_screen_event_wire', 'position' => 50, 'user_has_access' => $bp->events->current_event->user_has_access, 'item_css_id' => 'event-wire'  ) );

			bp_core_new_subnav_item( array( 'name' => __( 'Members', 'bp-events' ), 'slug' => 'members', 'parent_url' => $event_link, 'parent_slug' => $bp->events->slug, 'screen_function' => 'events_screen_event_members', 'position' => 60, 'user_has_access' => $bp->events->current_event->user_has_access, 'item_css_id' => 'event-members'  ) );

			if ( is_user_logged_in() && events_is_user_member( $bp->loggedin_user->id, $bp->events->current_event->id ) ) {
				if ( function_exists('friends_install') )
					bp_core_new_subnav_item( array( 'name' => __( 'Send Invites', 'bp-events' ), 'slug' => 'send-invites', 'parent_url' => $event_link, 'parent_slug' => $bp->events->slug, 'screen_function' => 'events_screen_event_invite', 'item_css_id' => 'event-invite', 'position' => 70, 'user_has_access' => $bp->events->current_event->user_has_access ) );

				bp_core_new_subnav_item( array( 'name' => __( 'Leave Event', 'bp-events' ), 'slug' => 'leave-event', 'parent_url' => $event_link, 'parent_slug' => $bp->events->slug, 'screen_function' => 'events_screen_event_leave', 'item_css_id' => 'event-leave', 'position' => 110, 'user_has_access' => $bp->events->current_event->user_has_access ) );

			}
		}
	}

	do_action( 'events_setup_nav', $bp->events->current_event->user_has_access );
}
add_action( 'plugins_loaded', 'events_setup_nav' );
add_action( 'admin_menu', 'events_setup_nav' );

/****** Add Events as a main menu tab ********/

function add_events_to_main_menu() {

	$class = (bp_is_page('events')) ? ' class="selected" ' : '';

	echo  '<li ' . $class. '><a href="' . get_option('home') . '/events" title="' . __( 'Events', 'bp-events' ) .'">' .  __( 'Events', 'bp-events' ) .'</a></li>';

}
add_action('bp_nav_items','add_events_to_main_menu');

function events_directory_events_setup() {
	global $bp;

	if ( $bp->current_component == $bp->events->slug && empty( $bp->current_action ) && empty( $bp->current_item ) ) {
		$bp->is_directory = true;

		do_action( 'events_directory_events_setup' );
		bp_core_load_template( apply_filters( 'events_template_directory_events', '/events/directories/index' ) );
	}
}
add_action( 'wp', 'events_directory_events_setup', 2 );

function events_setup_adminbar_menu() {
	global $bp;

	if ( !$bp->events->current_event )
		return false;

	/* Don't show this menu to non site admins or if you're viewing your own profile */
	if ( !is_site_admin() )
		return false;
	?>
	<li id="bp-adminbar-adminoptions-menu">
		<a href=""><?php _e( 'Admin Options', 'bp-events' ) ?></a>

		<ul>
			<li><a class="confirm" href="<?php echo wp_nonce_url( bp_get_event_permalink( $bp->events->current_event ) . '/admin/delete-event/', 'events_delete_group' ) ?>&amp;delete-event-button=1&amp;delete-event-understand=1"><?php _e( "Delete Event", 'bp-events' ) ?></a></li>

			<?php do_action( 'events_adminbar_menu_items' ) ?>
		</ul>
	</li>
	<?php
}
add_action( 'bp_adminbar_menus', 'events_setup_adminbar_menu', 20 );

/********************************************************************************
 * Screen Functions
 *
 * Screen functions are the controllers of BuddyPress. They will execute when their
 * specific URL is caught. They will first save or manipulate data using business
 * functions, then pass on the user to a template file.
 */

function events_screen_my_events() {
	global $bp;

	bp_core_delete_notifications_for_user_by_type( $bp->loggedin_user->id, $bp->events->slug, 'member_promoted_to_mod' );
	bp_core_delete_notifications_for_user_by_type( $bp->loggedin_user->id, $bp->events->slug, 'member_promoted_to_admin' );

	do_action( 'events_screen_my_events' );

	bp_core_load_template( apply_filters( 'events_template_my_events', 'events/index' ) );
}

function events_screen_event_invites() {
	global $bp;

	$event_id = $bp->action_variables[1];

	if ( isset($bp->action_variables) && in_array( 'accept', (array)$bp->action_variables ) && is_numeric($event_id) ) {
		/* Check the nonce */
		if ( !check_admin_referer( 'events_accept_invite' ) )
			return false;

		if ( !events_accept_invite( $bp->loggedin_user->id, $event_id ) ) {
			bp_core_add_message( __('Event invite could not be accepted', 'bp-events'), 'error' );
		} else {
			bp_core_add_message( __('Event invite accepted', 'bp-events') );

			/* Record this in activity streams */
			$event = new BP_Events_Event( $event_id, false, false );

			events_record_activity( array(
				'content' => apply_filters( 'events_activity_accepted_invite', sprintf( __( '%s joined the event %s', 'bp-events'), bp_core_get_userlink( $bp->loggedin_user->id ), '<a href="' . bp_get_event_permalink( $event ) . '">' . attribute_escape( $event->name ) . '</a>' ), $bp->loggedin_user->id, &$event ),
				'primary_link' => apply_filters( 'events_activity_accepted_invite_primary_link', bp_get_event_permalink( $event ), &$event ),
				'component_action' => 'joined_event',
				'item_id' => $event->id
			) );
		}

		bp_core_redirect( $bp->loggedin_user->domain . $bp->current_component . '/' . $bp->current_action );

	} else if ( isset($bp->action_variables) && in_array( 'reject', (array)$bp->action_variables ) && is_numeric($event_id) ) {
		/* Check the nonce */
		if ( !check_admin_referer( 'events_reject_invite' ) )
			return false;

		if ( !events_reject_invite( $bp->loggedin_user->id, $event_id ) ) {
			bp_core_add_message( __('Event invite could not be rejected', 'bp-events'), 'error' );
		} else {
			bp_core_add_message( __('Event invite rejected', 'bp-events') );
		}

		bp_core_redirect( $bp->loggedin_user->domain . $bp->current_component . '/' . $bp->current_action );
	}

	// Remove notifications
	bp_core_delete_notifications_for_user_by_type( $bp->loggedin_user->id, $bp->events->slug, 'event_invite' );

	do_action( 'events_screen_event_invites', $event_id );

	if ( '' != locate_template( array( 'events/invites.php' ), false ) )
		bp_core_load_template( apply_filters( 'events_template_event_invites', 'events/invites' ) );
	else
		bp_core_load_template( apply_filters( 'events_template_event_invites', 'events/list-invites' ) );
}

function events_screen_create_event() {
	global $bp;

	/* Initial check of action variable[0] to prevent conflicts */
	if ( !empty( $bp->action_variables[0] ) && $bp->action_variables[0] != 'step' )
		return false;

	/* If no current step is set, reset everything so we can start a fresh event creation */
	if ( !$bp->events->current_create_step = $bp->action_variables[1] ) {

		unset( $bp->events->current_create_step );
		unset( $bp->events->completed_create_steps );

		setcookie( 'bp_new_event_id', false, time() - 1000, COOKIEPATH );
		setcookie( 'bp_completed_create_steps', false, time() - 1000, COOKIEPATH );

		$reset_steps = true;
		bp_core_redirect( $bp->loggedin_user->domain . $bp->events->slug . '/create/step/' . array_shift( array_keys( $bp->events->event_creation_steps )  ) );
	}

	/* If this is a creation step that is not recognized, just redirect them back to the first screen */
	if ( $bp->action_variables[1] && !$bp->events->event_creation_steps[$bp->action_variables[1]] ) {
		bp_core_add_message( __('There was an error saving event details. Please try again.', 'bp-events'), 'error' );
		bp_core_redirect( $bp->loggedin_user->domain . $bp->events->slug . '/create' );
	}

	/* Fetch the currently completed steps variable */
	if ( isset( $_COOKIE['bp_completed_create_steps'] ) && !$reset_steps )
		$bp->events->completed_create_steps = unserialize( stripslashes( $_COOKIE['bp_completed_create_steps'] ) );

	/* Set the ID of the new event, if it has already been created in a previous step */
	if ( isset( $_COOKIE['bp_new_event_id'] ) ) {
		$bp->events->new_event_id = $_COOKIE['bp_new_event_id'];
		$bp->events->current_event = new BP_Events_Event( $bp->events->new_event_id, false, false );
	}

	/* If the save, upload or skip button is hit, lets calculate what we need to save */
	if ( isset( $_POST['save'] ) ) {

		/* Check the nonce */
		check_admin_referer( 'events_create_save_' . $bp->events->current_create_step );

		if ( 'event-details' == $bp->events->current_create_step ) {
			if ( empty( $_POST['event-name'] ) || empty( $_POST['event-desc'] ) ) {
				bp_core_add_message( __( 'Please fill in all of the required fields', 'bp-events' ), 'error' );
				bp_core_redirect( $bp->loggedin_user->domain . $bp->events->slug . '/create/step/' . $bp->events->current_create_step );
			}

			if ( !$bp->events->new_event_id = events_create_event( array( 'event_id' => $bp->events->new_event_id, 'name' => $_POST['event-name'], 'tagline' => $_POST['event-tagline'], 'description' => $_POST['event-desc'], 'news' => $_POST['event-news'], 'location' => $_POST['event-location'], 'link_group' => $_POST['event-group'], 'slug' => events_check_slug( sanitize_title($_POST['event-name']) ), 'date_created' => time() ) ) ) {
				bp_core_add_message( __( 'There was an error saving event details, please try again.', 'bp-events' ), 'error' );
				bp_core_redirect( $bp->loggedin_user->domain . $bp->events->slug . '/create/step/' . $bp->events->current_create_step );
			}

			events_update_eventmeta( $bp->events->new_event_id, 'total_member_count', 1 );
			events_update_eventmeta( $bp->events->new_event_id, 'last_activity', time() );
			events_update_eventmeta( $bp->events->new_event_id, 'theme', 'bp-events' );
			events_update_eventmeta( $bp->events->new_event_id, 'stylesheet', 'bp-events' );
		}

		if ( 'event-dates' == $bp->events->current_create_step ) {
			$event_is_allday = 1;

			if ( !isset($_POST['event-allday']) )
				$event_is_allday = 0;

			$event = events_process_eventtime_form();

			if ( !$bp->events->new_event_id = events_create_event( array( 'event_id' => $bp->events->new_event_id, 'date_start' => $event->date_start, 'date_end' => $event->date_end, 'is_allday' => $event_is_allday ) ) ) {
				bp_core_add_message( __( 'There was an error saving event details, please try again.', 'bp-events' ), 'error' );
				bp_core_redirect( $bp->loggedin_user->domain . $bp->events->slug . '/create/step/' . $bp->events->current_create_step );
			}
		}

		if ( 'event-settings' == $bp->events->current_create_step ) {
			$event_status = 'public';
			$event_enable_wire = 1;
			$event_enable_forum = 1;

			if ( !isset($_POST['event-show-wire']) )
				$event_enable_wire = 0;

			if ( !isset($_POST['event-show-forum']) ) {
				$event_enable_forum = 0;
			} else {
				/* Create the forum if enable_forum = 1 */
				if ( function_exists( 'bp_forums_setup' ) && '' == events_get_eventmeta( $bp->events->new_event_id, 'forum_id' ) ) {
					events_new_event_forum();
				}
			}

			if ( 'private' == $_POST['event-status'] )
				$event_status = 'private';
			else if ( 'hidden' == $_POST['event-status'] )
				$event_status = 'hidden';

			if ( !$bp->events->new_event_id = events_create_event( array( 'event_id' => $bp->events->new_event_id, 'status' => $event_status, 'enable_wire' => $event_enable_wire, 'enable_forum' => $event_enable_forum ) ) ) {
				bp_core_add_message( __( 'There was an error saving event details, please try again.', 'bp-events' ), 'error' );
				bp_core_redirect( $bp->loggedin_user->domain . $bp->events->slug . '/create/step/' . $bp->events->current_create_step );
			}
		}

		if ( 'event-invites' == $bp->events->current_create_step ) {
			events_send_invites( $bp->events->new_event_id, $bp->loggedin_user->id );
		}

		do_action( 'events_create_event_step_save_' . $bp->events->current_create_step );
		do_action( 'events_create_event_step_complete' ); // Mostly for clearing cache on a generic action name

		/**
		 * Once we have successfully saved the details for this step of the creation process
		 * we need to add the current step to the array of completed steps, then update the cookies
		 * holding the information
		 */
		if ( !in_array( $bp->events->current_create_step, (array)$bp->events->completed_create_steps ) )
			$bp->events->completed_create_steps[] = $bp->events->current_create_step;

		/* Reset cookie info */
		setcookie( 'bp_new_event_id', $bp->events->new_event_id, time()+60*60*24, COOKIEPATH );
		setcookie( 'bp_completed_create_steps', serialize( $bp->events->completed_create_steps ), time()+60*60*24, COOKIEPATH );

		/* If we have completed all steps and hit done on the final step we can redirect to the completed event */
		if ( count( $bp->events->completed_create_steps ) == count( $bp->events->event_creation_steps ) && $bp->events->current_create_step == array_pop( array_keys( $bp->events->event_creation_steps ) ) ) {
			unset( $bp->events->current_create_step );
			unset( $bp->events->completed_create_steps );

			/* Once we compelete all steps, record the event creation in the activity stream. */
			events_record_activity( array(
				'content' => apply_filters( 'events_activity_created_group', sprintf( __( '%s created the event %s', 'bp-events'), bp_core_get_userlink( $bp->loggedin_user->id ), '<a href="' . bp_get_event_permalink( $bp->events->current_event ) . '">' . attribute_escape( $bp->events->current_event->name ) . '</a>' ) ),
				'primary_link' => apply_filters( 'events_activity_created_event_primary_link', bp_get_event_permalink( $bp->events->current_event ) ),
				'component_action' => 'created_event',
				'item_id' => $bp->events->new_event_id
			) );

			do_action( 'events_event_create_complete', $bp->events->new_event_id );

			bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) );
		} else {
			/**
			 * Since we don't know what the next step is going to be (any plugin can insert steps)
			 * we need to loop the step array and fetch the next step that way.
			 */
			foreach ( $bp->events->event_creation_steps as $key => $value ) {
				if ( $key == $bp->events->current_create_step ) {
					$next = 1;
					continue;
				}

				if ( $next ) {
					$next_step = $key;
					break;
				}
			}

			bp_core_redirect( $bp->loggedin_user->domain . $bp->events->slug . '/create/step/' . $next_step );
		}
	}


	/* Event avatar is handled separately */
	if ( 'event-avatar' == $bp->events->current_create_step && isset( $_POST['upload'] ) ) {
		if ( !empty( $_FILES ) && isset( $_POST['upload'] ) ) {
			/* Normally we would check a nonce here, but the event save nonce is used instead */

			/* Pass the file to the avatar upload handler */
			if ( bp_core_avatar_handle_upload( $_FILES, 'events_avatar_upload_dir' ) ) {
				$bp->avatar_admin->step = 'crop-image';

				/* Make sure we include the jQuery jCrop file for image cropping */
				add_action( 'wp', 'bp_core_add_jquery_cropper' );
			}
		}

		/* If the image cropping is done, crop the image and save a full/thumb version */
		if ( isset( $_POST['avatar-crop-submit'] ) && isset( $_POST['upload'] ) ) {
			/* Normally we would check a nonce here, but the event save nonce is used instead */

			if ( !bp_core_avatar_handle_crop( array( 'object' => 'event', 'avatar_dir' => 'event-avatars', 'item_id' => $bp->events->current_event->id, 'original_file' => $_POST['image_src'], 'crop_x' => $_POST['x'], 'crop_y' => $_POST['y'], 'crop_w' => $_POST['w'], 'crop_h' => $_POST['h'] ) ) )
				bp_core_add_message( __( 'There was an error saving the event avatar, please try uploading again.', 'bp-events' ), 'error' );
			else
				bp_core_add_message( __( 'The event avatar was uploaded successfully!', 'bp-events' ) );
		}
	}

 	bp_core_load_template( apply_filters( 'events_template_create_event', 'events/create' ) );
}


function events_screen_event_home() {
	global $bp;

	if ( $bp->is_single_item ) {

		if ( isset($_GET['new']) ) {
			// Delete event request notifications for the user
			bp_core_delete_notifications_for_user_by_type( $bp->loggedin_user->id, $bp->events->slug, 'membership_request_accepted' );
			bp_core_delete_notifications_for_user_by_type( $bp->loggedin_user->id, $bp->events->slug, 'membership_request_rejected' );
			bp_core_delete_notifications_for_user_by_type( $bp->loggedin_user->id, $bp->events->slug, 'member_promoted_to_mod' );
			bp_core_delete_notifications_for_user_by_type( $bp->loggedin_user->id, $bp->events->slug, 'member_promoted_to_admin' );
		}

		do_action( 'events_screen_event_home' );

		if ( '' != locate_template( array( 'events/single/home.php' ), false ) )
			bp_core_load_template( apply_filters( 'events_template_event_home', 'events/single/home' ) );
		else
			bp_core_load_template( apply_filters( 'events_template_event_home', 'event/event-home' ) );
	}
}

function events_screen_event_forum() {
	global $bp;

	if ( $bp->is_single_item && $bp->events->current_event->user_has_access ) {

		/* Fetch the details we need */
		$topic_slug = $bp->action_variables[1];
		$topic_id = bp_forums_get_topic_id_from_slug( $topic_slug );
		$forum_id = events_get_eventmeta( $bp->events->current_event->id, 'forum_id' );

		if ( $topic_slug && $topic_id ) {

			/* Posting a reply */
			if ( !$bp->action_variables[2] && isset( $_POST['submit_reply'] ) ) {
				/* Check the nonce */
				check_admin_referer( 'bp_forums_new_reply' );

				/* Auto join this user if they are not yet a member of this event */
				if ( !is_site_admin() && 'public' == $bp->events->current_event->status && !events_is_user_member( $bp->loggedin_user->id, $bp->events->current_event->id ) )
					events_join_event( $bp->events->current_event->id, $bp->loggedin_user->id );

				if ( !events_new_event_forum_post( $_POST['reply_text'], $topic_id ) )
					bp_core_add_message( __( 'There was an error when replying to that topic', 'bp-events'), 'error' );
				else
					bp_core_add_message( __( 'Your reply was posted successfully', 'bp-events') );

				if ( $_SERVER['QUERY_STRING'] )
					$query_vars = '?' . $_SERVER['QUERY_STRING'];

				bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) . '/forum/topic/' . $topic_slug . '/' . $query_vars );
			}

			/* Sticky a topic */
			else if ( 'stick' == $bp->action_variables[2] && ( $bp->is_item_admin || $bp->is_item_mod ) ) {
				/* Check the nonce */
				check_admin_referer( 'bp_forums_stick_topic' );

				if ( !bp_forums_sticky_topic( array( 'topic_id' => $topic_id ) ) )
					bp_core_add_message( __( 'There was an error when making that topic a sticky', 'bp-events' ), 'error' );
				else
					bp_core_add_message( __( 'The topic was made sticky successfully', 'bp-events' ) );

				do_action( 'events_stick_forum_topic', $topic_id );
				bp_core_redirect( wp_get_referer() );
			}

			/* Un-Sticky a topic */
			else if ( 'unstick' == $bp->action_variables[2] && ( $bp->is_item_admin || $bp->is_item_mod ) ) {
				/* Check the nonce */
				check_admin_referer( 'bp_forums_unstick_topic' );

				if ( !bp_forums_sticky_topic( array( 'topic_id' => $topic_id, 'mode' => 'unstick' ) ) )
					bp_core_add_message( __( 'There was an error when unsticking that topic', 'bp-events'), 'error' );
				else
					bp_core_add_message( __( 'The topic was unstuck successfully', 'bp-events') );

				do_action( 'events_unstick_forum_topic', $topic_id );
				bp_core_redirect( wp_get_referer() );
			}

			/* Close a topic */
			else if ( 'close' == $bp->action_variables[2] && ( $bp->is_item_admin || $bp->is_item_mod ) ) {
				/* Check the nonce */
				check_admin_referer( 'bp_forums_close_topic' );

				if ( !bp_forums_openclose_topic( array( 'topic_id' => $topic_id ) ) )
					bp_core_add_message( __( 'There was an error when closing that topic', 'bp-events'), 'error' );
				else
					bp_core_add_message( __( 'The topic was closed successfully', 'bp-events') );

				do_action( 'events_close_forum_topic', $topic_id );
				bp_core_redirect( wp_get_referer() );
			}

			/* Open a topic */
			else if ( 'open' == $bp->action_variables[2] && ( $bp->is_item_admin || $bp->is_item_mod ) ) {
				/* Check the nonce */
				check_admin_referer( 'bp_forums_open_topic' );

				if ( !bp_forums_openclose_topic( array( 'topic_id' => $topic_id, 'mode' => 'open' ) ) )
					bp_core_add_message( __( 'There was an error when opening that topic', 'bp-events'), 'error' );
				else
					bp_core_add_message( __( 'The topic was opened successfully', 'bp-events') );

				do_action( 'events_open_forum_topic', $topic_id );
				bp_core_redirect( wp_get_referer() );
			}

			/* Delete a topic */
			else if ( 'delete' == $bp->action_variables[2] && empty( $bp->action_variables[3] ) ) {
				/* Fetch the topic */
				$topic = bp_forums_get_topic_details( $topic_id );

				/* Check the logged in user can delete this topic */
				if ( !$bp->is_item_admin && !$bp->is_item_mod && (int)$bp->loggedin_user->id != (int)$topic->topic_poster )
					bp_core_redirect( wp_get_referer() );

				/* Check the nonce */
				check_admin_referer( 'bp_forums_delete_topic' );

				if ( !events_delete_event_forum_topic( $topic_id ) )
					bp_core_add_message( __( 'There was an error deleting the topic', 'bp-events'), 'error' );
				else
					bp_core_add_message( __( 'The topic was deleted successfully', 'bp-events') );

				do_action( 'events_delete_forum_topic', $topic_id );
				bp_core_redirect( wp_get_referer() );
			}

			/* Editing a topic */
			else if ( 'edit' == $bp->action_variables[2] && empty( $bp->action_variables[3] ) ) {
				/* Fetch the topic */
				$topic = bp_forums_get_topic_details( $topic_id );

				/* Check the logged in user can edit this topic */
				if ( !$bp->is_item_admin && !$bp->is_item_mod && (int)$bp->loggedin_user->id != (int)$topic->topic_poster )
					bp_core_redirect( wp_get_referer() );

				if ( isset( $_POST['save_changes'] ) ) {
					/* Check the nonce */
					check_admin_referer( 'bp_forums_edit_topic' );

					if ( !events_update_event_forum_topic( $topic_id, $_POST['topic_title'], $_POST['topic_text'] ) )
						bp_core_add_message( __( 'There was an error when editing that topic', 'bp-events'), 'error' );
					else
						bp_core_add_message( __( 'The topic was edited successfully', 'bp-events') );

					do_action( 'events_edit_forum_topic', $topic_id );
					bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) . '/forum/topic/' . $topic_slug . '/' );
				}

				bp_core_load_template( apply_filters( 'events_template_event_forum_topic_edit', 'events/single/forum/edit' ) );
			}

			/* Delete a post */
			else if ( 'delete' == $bp->action_variables[2] && $post_id = $bp->action_variables[4] ) {
				/* Fetch the post */
				$post = bp_forums_get_post( $post_id );

				/* Check the logged in user can edit this topic */
				if ( !$bp->is_item_admin && !$bp->is_item_mod && (int)$bp->loggedin_user->id != (int)$post->poster_id )
					bp_core_redirect( wp_get_referer() );

				/* Check the nonce */
				check_admin_referer( 'bp_forums_delete_post' );

				if ( !events_delete_event_forum_post( $bp->action_variables[4], $topic_id ) )
					bp_core_add_message( __( 'There was an error deleting that post', 'bp-events'), 'error' );
				else
					bp_core_add_message( __( 'The post was deleted successfully', 'bp-events') );

				do_action( 'events_delete_forum_post', $post_id );
				bp_core_redirect( wp_get_referer() );
			}

			/* Editing a post */
			else if ( 'edit' == $bp->action_variables[2] && $post_id = $bp->action_variables[4] ) {
				/* Fetch the post */
				$post = bp_forums_get_post( $bp->action_variables[4] );

				/* Check the logged in user can edit this topic */
				if ( !$bp->is_item_admin && !$bp->is_item_mod && (int)$bp->loggedin_user->id != (int)$post->poster_id )
					bp_core_redirect( wp_get_referer() );

				if ( isset( $_POST['save_changes'] ) ) {
					/* Check the nonce */
					check_admin_referer( 'bp_forums_edit_post' );

					if ( !events_update_event_forum_post( $post_id, $_POST['post_text'], $topic_id ) )
						bp_core_add_message( __( 'There was an error when editing that post', 'bp-events'), 'error' );
					else
						bp_core_add_message( __( 'The post was edited successfully', 'bp-events') );

					do_action( 'events_edit_forum_post', $post_id );
					bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) . '/forum/topic/' . $topic_slug . '/' );
				}

				bp_core_load_template( apply_filters( 'events_template_event_forum_topic_edit', 'events/single/forum/edit' ) );
			}

			/* Standard topic display */
			else {
				if ( '' != locate_template( array( 'events/single/forum/topic.php' ), false ) )
					bp_core_load_template( apply_filters( 'events_template_event_forum_topic', 'events/single/forum/topic' ) );
				else
					bp_core_load_template( apply_filters( 'events_template_event_forum_topic', 'events/forum/topic' ) );
			}

		} else {

			/* Posting a topic */
			if ( isset( $_POST['submit_topic'] ) && function_exists( 'bp_forums_new_topic') ) {
				/* Check the nonce */
				check_admin_referer( 'bp_forums_new_topic' );

				/* Auto join this user if they are not yet a member of this event */
				if ( !is_site_admin() && 'public' == $bp->events->current_event->status && !events_is_user_member( $bp->loggedin_user->id, $bp->events->current_event->id ) )
					events_join_event( $bp->events->current_event->id, $bp->loggedin_user->id );

				if ( !$topic = events_new_event_forum_topic( $_POST['topic_title'], $_POST['topic_text'], $_POST['topic_tags'], $forum_id ) )
					bp_core_add_message( __( 'There was an error when creating the topic', 'bp-events'), 'error' );
				else
					bp_core_add_message( __( 'The topic was created successfully', 'bp-events') );

				bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) . '/forum/topic/' . $topic->topic_slug . '/' );
			}

			do_action( 'events_screen_event_forum', $topic_id, $forum_id );

			if ( '' != locate_template( array( 'events/single/forum/index.php' ), false ) )
				bp_core_load_template( apply_filters( 'events_template_event_forum', 'events/single/forum/index' ) );
			else
				bp_core_load_template( apply_filters( 'events_template_event_forum', 'events/forum/index' ) );
		}
	}
}


function events_screen_event_wire() {
	global $bp;

	$wire_action = $bp->action_variables[0];

	if ( $bp->is_single_item ) {
		if ( 'post' == $wire_action && ( is_site_admin() || events_is_user_member( $bp->loggedin_user->id, $bp->events->current_event->id ) ) ) {
			/* Check the nonce first. */
			if ( !check_admin_referer( 'bp_wire_post' ) )
				return false;

         if ( !events_new_wire_post( $bp->events->current_event->id, $_POST['wire-post-textarea'] ) )
				bp_core_add_message( __('Wire message could not be posted.', 'bp-events'), 'error' );
			else
				bp_core_add_message( __('Wire message successfully posted.', 'bp-events') );

			if ( !strpos( wp_get_referer(), $bp->wire->slug ) )
				bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) );
			else
				bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) . '/' . $bp->wire->slug );

		} else if ( 'delete' == $wire_action && ( is_site_admin() || events_is_user_member( $bp->loggedin_user->id, $bp->events->current_event->id ) ) ) {
			$wire_message_id = $bp->action_variables[1];

			/* Check the nonce first. */
			if ( !check_admin_referer( 'bp_wire_delete_link' ) )
				return false;

			if ( !events_delete_wire_post( $wire_message_id, $bp->events->table_name_wire ) )
				bp_core_add_message( __('There was an error deleting the wire message.', 'bp-events'), 'error' );
			else
				bp_core_add_message( __('Wire message successfully deleted.', 'bp-events') );

			if ( !strpos( wp_get_referer(), $bp->wire->slug ) )
				bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) );
			else
				bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) . '/' . $bp->wire->slug );

		} else if ( ( !$wire_action || 'latest' == $bp->action_variables[1] ) ) {
			if ( '' != locate_template( array( 'events/single/wire.php' ), false ) )
				bp_core_load_template( apply_filters( 'events_template_event_wire', 'events/single/wire' ) );
			else
				bp_core_load_template( apply_filters( 'events_template_event_wire', 'events/wire' ) );
		} else {
			if ( '' != locate_template( array( 'events/single/home.php' ), false ) )
				bp_core_load_template( apply_filters( 'events_template_event_home', 'events/single/home' ) );
			else
				bp_core_load_template( apply_filters( 'events_template_event_home', 'events/group-home' ) );
		}
	}
}


function events_screen_event_members() {
	global $bp;

	if ( $bp->is_single_item ) {
		do_action( 'events_screen_event_members', $bp->events->current_event->id );

		if ( '' != locate_template( array( 'events/single/members.php' ), false ) )
			bp_core_load_template( apply_filters( 'events_template_event_forum', 'events/single/members' ) );
		else
			bp_core_load_template( apply_filters( 'events_template_event_forum', 'events/list-members' ) );
	}
}


function events_screen_event_invite() {
	global $bp;

	if ( $bp->is_single_item ) {
		if ( isset($bp->action_variables) && 'send' == $bp->action_variables[0] ) {

			if ( !check_admin_referer( 'events_send_invites', '_wpnonce_send_invites' ) )
				return false;

			// Send the invites.
			events_send_invites( $bp->loggedin_user->id, $bp->events->current_event->id );

			bp_core_add_message( __('Event invites sent.', 'bp-events') );

			do_action( 'events_screen_event_invite', $bp->events->current_event->id );

			bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) );
		} else {
			// Show send invite page
			if ( '' != locate_template( array( 'events/single/send-invite.php' ), false ) )
				bp_core_load_template( apply_filters( 'events_template_event_invite', 'events/single/send-invite' ) );
			else
				bp_core_load_template( apply_filters( 'events_template_event_invite', 'events/send-invite' ) );
		}
	}
}


function events_screen_event_leave() {
	global $bp;

	if ( $bp->is_single_item ) {
		if ( isset($bp->action_variables) && 'yes' == $bp->action_variables[0] ) {

			// Check if the user is the event admin first.
			if ( count( events_get_event_admins( $bp->events->current_event->id ) ) < 2 ) {
				if ( events_is_user_admin( $bp->loggedin_user->id, $bp->events->current_event->id ) ) {
					bp_core_add_message(  __('As the only event administrator, you cannot leave this event.', 'bp-events'), 'error' );
					bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) );
				}
			}

			// remove the user from the event.
			if ( !events_leave_event( $bp->events->current_event->id ) ) {
				bp_core_add_message(  __('There was an error leaving the event. Please try again.', 'bp-events'), 'error' );
				bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) );
			} else {
				bp_core_add_message( __('You left the event successfully.', 'bp-events') );
				bp_core_redirect( $bp->loggedin_user->domain . $bp->events->slug );
			}

		} else if ( isset($bp->action_variables) && 'no' == $bp->action_variables[0] ) {

			bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) );

		} else {

			do_action( 'events_screen_event_leave', $bp->events->current_event->id );

			// Show leave event page
			if ( '' != locate_template( array( 'events/single/leave-confirm.php' ), false ) )
				bp_core_load_template( apply_filters( 'events_template_event_leave', 'events/single/leave-confirm' ) );
			else
				bp_core_load_template( apply_filters( 'events_template_event_leave', 'events/leave-group-confirm' ) );
		}
	}
}

function events_screen_event_request_membership() {
	global $bp;

	if ( !is_user_logged_in() )
		return false;

	if ( 'private' == $bp->events->current_event->status ) {
		// If the user has submitted a request, send it.
		if ( isset( $_POST['event-request-send']) ) {
			/* Check the nonce first. */
			if ( !check_admin_referer( 'events_request_membership' ) )
				return false;

			if ( !events_send_membership_request( $bp->loggedin_user->id, $bp->events->current_event->id ) ) {
				bp_core_add_message( __( 'There was an error sending your event membership request, please try again.', 'bp-events' ), 'error' );
			} else {
				bp_core_add_message( __( 'Your membership request was sent to the event administrator successfully. You will be notified when the event administrator responds to your request.', 'bp-events' ) );
			}
			bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) );
		}

		do_action( 'events_screen_event_request_membership', $bp->events->current_event->id );

		if ( '' != locate_template( array( 'events/single/request-membership.php' ), false ) )
			bp_core_load_template( apply_filters( 'events_template_event_request_membership', 'events/single/request-membership' ) );
		else
			bp_core_load_template( apply_filters( 'events_template_event_request_membership', 'events/request-membership' ) );
	}
}

function events_screen_event_admin() {
	global $bp;

	if ( $bp->current_component != BP_EVENTS_SLUG || 'admin' != $bp->current_action )
		return false;

	if ( !empty( $bp->action_variables[0] ) )
		return false;

	bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) . '/admin/edit-details' );
}

function events_screen_event_admin_edit_details() {
	global $bp;

	if ( $bp->current_component == $bp->events->slug && 'edit-details' == $bp->action_variables[0] ) {

		if ( $bp->is_item_admin || $bp->is_item_mod  ) {

			// If the edit form has been submitted, save the edited details
			if ( isset( $_POST['save'] ) ) {
				/* Check the nonce first. */
				if ( !check_admin_referer( 'events_edit_event_details' ) )
					return false;

				if ( !events_edit_base_event_details( $_POST['event-id'], $_POST['event-name'], $_POST['event-tagline'], $_POST['event-desc'], $_POST['event-news'], $_POST['event-location'], (int)$_POST['event-notify-members'], $_POST['event-group'] ) ) {
					bp_core_add_message( __( 'There was an error updating event details, please try again.', 'bp-events' ), 'error' );
				} else {
					bp_core_add_message( __( 'Event details were successfully updated.', 'bp-events' ) );
				}

				do_action( 'events_event_details_edited', $bp->events->current_event->id );

				bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) . '/admin/edit-details' );
			}

			do_action( 'events_screen_event_admin_edit_details', $bp->events->current_event->id );

			if ( '' != locate_template( array( 'events/single/admin.php' ), false ) )
				bp_core_load_template( apply_filters( 'events_template_event_admin', 'events/single/admin' ) );
			else
				bp_core_load_template( apply_filters( 'events_template_event_admin', 'events/admin/edit-details' ) );
		}
	}
}
add_action( 'wp', 'events_screen_event_admin_edit_details', 4 );


function events_screen_event_admin_dates() {
	global $bp;

	if ( $bp->current_component == $bp->events->slug && 'event-dates' == $bp->action_variables[0] ) {

		if ( !$bp->is_item_admin )
			return false;

		// If the edit form has been submitted, save the edited details
		if ( isset( $_POST['save'] ) ) {
		  $is_allday = ( isset($_POST['event-allday'] ) ) ? 1 : 0;

			/* Check the nonce first. */
			if ( !check_admin_referer( 'events_edit_event_dates' ) )
				return false;

			$event = events_process_eventtime_form();

			// Validate that start date is before end date
			if ( $event->date_start > $event->date_end ) {
				bp_core_add_message( __( 'The start date should be earlier than the end date. Please fix this and try again.', 'bp-events' ), 'error' );
				bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) . '/admin/event-dates' );
			}

			// Validate that the event end date is still in the future
			$now = strtotime ( date('Y-m-d H:m:s',time()) );

			if ( ($event->date_end < $now) ) {
				bp_core_add_message( __( 'The event should not be in the past. Please fix this and try again.', 'bp-events' ), 'error' );
				bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) . '/admin/event-dates' );
			}

			if ( !events_edit_event_dates( $_POST['event-id'], $event->date_start, $event->date_end, $is_allday ) ) {
				bp_core_add_message( __( 'There was an error updating event dates, please try again.', 'bp-events' ), 'error' );
			} else {
				bp_core_add_message( __( 'Event dates were successfully updated.', 'bp-events' ) );
			}

			do_action( 'events_screen_dates_edited', $bp->events->current_event->id );

			bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) . '/admin/event-dates' );
		}

		do_action( 'events_screen_event_admin_dates', $bp->events->current_event->id );

		bp_core_load_template( apply_filters( 'events_template_event_admin_times', 'events/single/admin' ) );
	}
}
add_action( 'wp', 'events_screen_event_admin_dates', 4 );

function events_screen_event_admin_settings() {
	global $bp;

	if ( $bp->current_component == $bp->events->slug && 'event-settings' == $bp->action_variables[0] ) {

		if ( !$bp->is_item_admin )
			return false;

		// If the edit form has been submitted, save the edited details
		if ( isset( $_POST['save'] ) ) {
			$enable_wire = ( isset($_POST['event-show-wire'] ) ) ? 1 : 0;
			$enable_forum = ( isset($_POST['event-show-forum'] ) ) ? 1 : 0;
			$enable_photos = ( isset($_POST['event-show-photos'] ) ) ? 1 : 0;
			$photos_admin_only = ( $_POST['event-photos-status'] != 'all' ) ? 1 : 0;

			$allowed_status = apply_filters( 'events_allowed_status', array( 'public', 'private', 'hidden' ) );
			$status = ( in_array( $_POST['event-status'], (array)$allowed_status ) ) ? $_POST['event-status'] : 'public';

			/* Check the nonce first. */
			if ( !check_admin_referer( 'events_edit_event_settings' ) )
				return false;

			if ( !events_edit_event_settings( $_POST['event-id'], $enable_wire, $enable_forum, $enable_photos, $photos_admin_only, $status ) ) {
				bp_core_add_message( __( 'There was an error updating event settings, please try again.', 'bp-events' ), 'error' );
			} else {
				bp_core_add_message( __( 'Event settings were successfully updated.', 'bp-events' ) );
			}

			do_action( 'events_event_settings_edited', $bp->events->current_event->id );

			bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) . '/admin/event-settings' );
		}

		do_action( 'events_screen_event_admin_settings', $bp->events->current_event->id );

		if ( '' != locate_template( array( 'events/single/admin.php' ), false ) )
			bp_core_load_template( apply_filters( 'events_template_event_admin_settings', 'events/single/admin' ) );
		else
			bp_core_load_template( apply_filters( 'events_template_event_admin_settings', 'events/admin/event-settings' ) );
	}
}
add_action( 'wp', 'events_screen_event_admin_settings', 4 );


function events_screen_event_admin_avatar() {
	global $bp;

	if ( $bp->current_component == $bp->events->slug && 'event-avatar' == $bp->action_variables[0] ) {

		if ( !$bp->is_item_admin )
			return false;

		/* If the event admin has deleted the admin avatar */
		if ( 'delete' == $bp->action_variables[1] ) {

			/* Check the nonce */
			check_admin_referer( 'bp_event_avatar_delete' );

			if ( bp_core_delete_existing_avatar( array( 'item_id' => $bp->events->current_event->id, 'object' => 'event', 'avatar_dir' => 'event-avatars' ) ) )
				bp_core_add_message( __( 'Your avatar was deleted successfully!', 'bp-events' ) );
			else
				bp_core_add_message( __( 'There was a problem deleting that avatar, please try again.', 'bp-events' ), 'error' );
		}

		$bp->avatar_admin->step = 'upload-image';

		if ( !empty( $_FILES ) ) {

			/* Check the nonce */
			check_admin_referer( 'bp_avatar_upload' );

			/* Pass the file to the avatar upload handler */
			if ( bp_core_avatar_handle_upload( $_FILES, 'events_avatar_upload_dir' ) ) {
				$bp->avatar_admin->step = 'crop-image';

				/* Make sure we include the jQuery jCrop file for image cropping */
				add_action( 'wp', 'bp_core_add_jquery_cropper' );
			}

		}

		/* If the image cropping is done, crop the image and save a full/thumb version */
		if ( isset( $_POST['avatar-crop-submit'] ) ) {

			/* Check the nonce */
			check_admin_referer( 'bp_avatar_cropstore' );

			if ( !bp_core_avatar_handle_crop( array( 'object' => 'event', 'avatar_dir' => 'event-avatars', 'item_id' => $bp->events->current_event->id, 'original_file' => $_POST['image_src'], 'crop_x' => $_POST['x'], 'crop_y' => $_POST['y'], 'crop_w' => $_POST['w'], 'crop_h' => $_POST['h'] ) ) )
				bp_core_add_message( __( 'There was a problem cropping the avatar, please try uploading it again', 'bp-events' ) );
			else
				bp_core_add_message( __( 'The new event avatar was uploaded successfully!', 'bp-events' ) );

		}

		do_action( 'events_screen_event_admin_avatar', $bp->events->current_event->id );

		if ( '' != locate_template( array( 'events/single/admin.php' ), false ) )
			bp_core_load_template( apply_filters( 'events_template_event_admin_avatar', 'events/single/admin' ) );
		else
			bp_core_load_template( apply_filters( 'events_template_event_admin_avatar', 'events/admin/event-avatar' ) );
	}
}
add_action( 'wp', 'events_screen_event_admin_avatar', 4 );

function events_screen_event_admin_manage_members() {
	global $bp;

	if ( $bp->current_component == $bp->events->slug && 'manage-members' == $bp->action_variables[0] ) {

		if ( !$bp->is_item_admin )
			return false;

		if ( 'promote' == $bp->action_variables[1] && ( 'mod' == $bp->action_variables[2] || 'admin' == $bp->action_variables[2] ) && is_numeric( $bp->action_variables[3] ) ) {
			$user_id = $bp->action_variables[3];
			$status = $bp->action_variables[2];

			/* Check the nonce first. */
			if ( !check_admin_referer( 'events_promote_member' ) )
				return false;

			// Promote a user.
			if ( !events_promote_member( $user_id, $bp->events->current_event->id, $status ) ) {
				bp_core_add_message( __( 'There was an error when promoting that user, please try again', 'bp-events' ), 'error' );
			} else {
				bp_core_add_message( __( 'User promoted successfully', 'bp-events' ) );
			}

			do_action( 'events_promoted_member', $user_id, $bp->events->current_event->id );

			bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) . '/admin/manage-members' );
		}

		if ( 'demote' == $bp->action_variables[1] && is_numeric( $bp->action_variables[2] ) ) {
			$user_id = $bp->action_variables[2];

			/* Check the nonce first. */
			if ( !check_admin_referer( 'events_demote_member' ) )
				return false;

			// Demote a user.
			if ( !events_demote_member( $user_id, $bp->events->current_event->id ) ) {
				bp_core_add_message( __( 'There was an error when demoting that user, please try again', 'bp-events' ), 'error' );
			} else {
				bp_core_add_message( __( 'User demoted successfully', 'bp-events' ) );
			}

			do_action( 'events_demoted_member', $user_id, $bp->events->current_event->id );

			bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) . '/admin/manage-members' );
		}

		if ( 'ban' == $bp->action_variables[1] && is_numeric( $bp->action_variables[2] ) ) {
			$user_id = $bp->action_variables[2];

			/* Check the nonce first. */
			if ( !check_admin_referer( 'events_ban_member' ) )
				return false;

			// Ban a user.
			if ( !events_ban_member( $user_id, $bp->events->current_event->id ) ) {
				bp_core_add_message( __( 'There was an error when banning that user, please try again', 'bp-events' ), 'error' );
			} else {
				bp_core_add_message( __( 'User banned successfully', 'bp-events' ) );
			}

			do_action( 'events_banned_member', $user_id, $bp->events->current_event->id );

			bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) . '/admin/manage-members' );
		}

		if ( 'unban' == $bp->action_variables[1] && is_numeric( $bp->action_variables[2] ) ) {
			$user_id = $bp->action_variables[2];

			/* Check the nonce first. */
			if ( !check_admin_referer( 'events_unban_member' ) )
				return false;

			// Remove a ban for user.
			if ( !events_unban_member( $user_id, $bp->events->current_event->id ) ) {
				bp_core_add_message( __( 'There was an error when unbanning that user, please try again', 'bp-events' ), 'error' );
			} else {
				bp_core_add_message( __( 'User ban removed successfully', 'bp-events' ) );
			}

			do_action( 'events_unbanned_member', $user_id, $bp->events->current_event->id );

			bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) . '/admin/manage-members' );
		}

		do_action( 'events_screen_event_admin_manage_members', $bp->events->current_event->id );

		if ( '' != locate_template( array( 'events/single/admin.php' ), false ) )
			bp_core_load_template( apply_filters( 'events_template_event_admin_manage_members', 'events/single/admin' ) );
		else
			bp_core_load_template( apply_filters( 'events_template_event_admin_manage_members', 'events/admin/manage-members' ) );
	}
}
add_action( 'wp', 'events_screen_event_admin_manage_members', 4 );

function events_screen_event_admin_requests() {
	global $bp;

	if ( $bp->current_component == $bp->events->slug && 'membership-requests' == $bp->action_variables[0] ) {

		if ( !$bp->is_item_admin || 'public' == $bp->events->current_event->status )
			return false;

		// Remove any screen notifications
		bp_core_delete_notifications_for_user_by_type( $bp->loggedin_user->id, $bp->events->slug, 'new_membership_request' );

		$request_action = $bp->action_variables[1];
		$membership_id = $bp->action_variables[2];

		if ( isset($request_action) && isset($membership_id) ) {
			if ( 'accept' == $request_action && is_numeric($membership_id) ) {

				/* Check the nonce first. */
				if ( !check_admin_referer( 'events_accept_membership_request' ) )
					return false;

				// Accept the membership request
				if ( !events_accept_membership_request( $membership_id ) ) {
					bp_core_add_message( __( 'There was an error accepting the membership request, please try again.', 'bp-events' ), 'error' );
				} else {
					bp_core_add_message( __( 'Event membership request accepted', 'bp-events' ) );
				}

			} else if ( 'reject' == $request_action && is_numeric($membership_id) ) {
				/* Check the nonce first. */
				if ( !check_admin_referer( 'events_reject_membership_request' ) )
					return false;

				// Reject the membership request
				if ( !events_reject_membership_request( $membership_id ) ) {
					bp_core_add_message( __( 'There was an error rejecting the membership request, please try again.', 'bp-events' ), 'error' );
				} else {
					bp_core_add_message( __( 'Event membership request rejected', 'bp-events' ) );
				}

			}

			do_action( 'events_event_request_managed', $bp->events->current_event->id, $request_action, $membership_id );

			bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) . '/admin/membership-requests' );
		}

		do_action( 'events_screen_event_admin_requests', $bp->events->current_event->id );

		if ( '' != locate_template( array( 'events/single/admin.php' ), false ) )
			bp_core_load_template( apply_filters( 'events_template_event_admin_requests', 'events/single/admin' ) );
		else
			bp_core_load_template( apply_filters( 'events_template_event_admin_requests', 'events/admin/membership-requests' ) );
	}
}
add_action( 'wp', 'events_screen_event_admin_requests', 4 );

function events_screen_event_admin_delete_event() {
	global $bp;

	if ( $bp->current_component == $bp->events->slug && 'delete-event' == $bp->action_variables[0] ) {

		if ( !$bp->is_item_admin && !is_site_admin() )
			return false;

			if ( isset( $_REQUEST['delete-event-button'] ) && isset( $_REQUEST['delete-event-understand'] ) ) {
			/* Check the nonce first. */
			if ( !check_admin_referer( 'events_delete_event' ) )
				return false;

			// Event admin has deleted the event, now do it.
			if ( !events_delete_event( $bp->events->current_event->id ) ) {
				bp_core_add_message( __( 'There was an error deleting the event, please try again.', 'bp-events' ), 'error' );
			} else {
				bp_core_add_message( __( 'The event was deleted successfully', 'bp-events' ) );

				do_action( 'events_event_deleted', $bp->events->current_event->id );

				bp_core_redirect( $bp->loggedin_user->domain . $bp->events->slug . '/' );
			}

			bp_core_redirect( $bp->loggedin_user->domain . $bp->current_component );
		}

		do_action( 'events_screen_event_admin_delete_event', $bp->events->current_event->id );

		if ( '' != locate_template( array( 'events/single/admin.php' ), false ) )
			bp_core_load_template( apply_filters( 'events_template_event_admin_delete_event', 'events/single/admin' ) );
		else
			bp_core_load_template( apply_filters( 'events_template_event_admin_delete_event', 'events/admin/delete-group' ) );
	}
}
add_action( 'wp', 'events_screen_event_admin_delete_event', 4 );


function events_screen_notification_settings() {
	global $current_user; ?>
	<table class="notification-settings" id="events-notification-settings">
		<tr>
			<th class="icon"></th>
			<th class="title"><?php _e( 'Events', 'bp-events' ) ?></th>
			<th class="yes"><?php _e( 'Yes', 'bp-events' ) ?></th>
			<th class="no"><?php _e( 'No', 'bp-events' )?></th>
		</tr>
		<tr>
			<td></td>
			<td><?php _e( 'A member invites you to join a event', 'bp-events' ) ?></td>
			<td class="yes"><input type="radio" name="notifications[notification_events_invite]" value="yes" <?php if ( !get_usermeta( $current_user->id, 'notification_events_invite') || 'yes' == get_usermeta( $current_user->id, 'notification_events_invite') ) { ?>checked="checked" <?php } ?>/></td>
			<td class="no"><input type="radio" name="notifications[notification_events_invite]" value="no" <?php if ( 'no' == get_usermeta( $current_user->id, 'notification_events_invite') ) { ?>checked="checked" <?php } ?>/></td>
		</tr>
		<tr>
			<td></td>
			<td><?php _e( 'Event information is updated', 'bp-events' ) ?></td>
			<td class="yes"><input type="radio" name="notifications[notification_events_event_updated]" value="yes" <?php if ( !get_usermeta( $current_user->id, 'notification_events_event_updated') || 'yes' == get_usermeta( $current_user->id, 'notification_events_event_updated') ) { ?>checked="checked" <?php } ?>/></td>
			<td class="no"><input type="radio" name="notifications[notification_events_event_updated]" value="no" <?php if ( 'no' == get_usermeta( $current_user->id, 'notification_events_event_updated') ) { ?>checked="checked" <?php } ?>/></td>
		</tr>
		<?php if ( function_exists('bp_wire_install') ) { ?>
		<tr>
			<td></td>
			<td><?php _e( 'A member posts on the wire of a event you belong to', 'bp-events' ) ?></td>
			<td class="yes"><input type="radio" name="notifications[notification_events_wire_post]" value="yes" <?php if ( !get_usermeta( $current_user->id, 'notification_events_wire_post') || 'yes' == get_usermeta( $current_user->id, 'notification_events_wire_post') ) { ?>checked="checked" <?php } ?>/></td>
			<td class="no"><input type="radio" name="notifications[notification_events_wire_post]" value="no" <?php if ( 'no' == get_usermeta( $current_user->id, 'notification_events_wire_post') ) { ?>checked="checked" <?php } ?>/></td>
		</tr>
		<?php } ?>
		<tr>
			<td></td>
			<td><?php _e( 'You are promoted to a event administrator or moderator', 'bp-events' ) ?></td>
			<td class="yes"><input type="radio" name="notifications[notification_events_admin_promotion]" value="yes" <?php if ( !get_usermeta( $current_user->id, 'notification_events_admin_promotion') || 'yes' == get_usermeta( $current_user->id, 'notification_events_admin_promotion') ) { ?>checked="checked" <?php } ?>/></td>
			<td class="no"><input type="radio" name="notifications[notification_events_admin_promotion]" value="no" <?php if ( 'no' == get_usermeta( $current_user->id, 'notification_events_admin_promotion') ) { ?>checked="checked" <?php } ?>/></td>
		</tr>
		<tr>
			<td></td>
			<td><?php _e( 'A member requests to join a private event for which you are an admin', 'bp-events' ) ?></td>
			<td class="yes"><input type="radio" name="notifications[notification_events_membership_request]" value="yes" <?php if ( !get_usermeta( $current_user->id, 'notification_events_membership_request') || 'yes' == get_usermeta( $current_user->id, 'notification_events_membership_request') ) { ?>checked="checked" <?php } ?>/></td>
			<td class="no"><input type="radio" name="notifications[notification_events_membership_request]" value="no" <?php if ( 'no' == get_usermeta( $current_user->id, 'notification_events_membership_request') ) { ?>checked="checked" <?php } ?>/></td>
		</tr>

		<?php do_action( 'events_screen_notification_settings' ); ?>
	</table>
<?php
}
add_action( 'bp_notification_settings', 'events_screen_notification_settings' );



/********************************************************************************
 * Action Functions
 *
 * Action functions are exactly the same as screen functions, however they do not
 * have a template screen associated with them. Usually they will send the user
 * back to the default screen after execution.
 */

function events_action_join_event() {
	global $bp;

	if ( !$bp->is_single_item || $bp->current_component != $bp->events->slug || $bp->current_action != 'join' )
		return false;

	// user wants to join a event
	if ( !events_is_user_member( $bp->loggedin_user->id, $bp->events->current_event->id ) && !events_is_user_banned( $bp->loggedin_user->id, $bp->events->current_event->id ) ) {
		if ( !events_join_event($bp->events->current_event->id) ) {
			bp_core_add_message( __('There was an error joining the event.', 'bp-events'), 'error' );
		} else {
			bp_core_add_message( __('You joined the event!', 'bp-events') );
		}
		bp_core_redirect( bp_get_event_permalink( $bp->events->current_event ) );
	}

	if ( '' != locate_template( array( 'events/single/admin.php' ), false ) )
		bp_core_load_template( apply_filters( 'events_template_event_home', 'events/single/home' ) );
	else
		bp_core_load_template( apply_filters( 'events_template_event_home', 'events/event-home' ) );
}
add_action( 'wp', 'events_action_join_event', 3 );

function events_action_sort_creation_steps() {
	global $bp;

	if ( $bp->current_component != BP_EVENTS_SLUG && $bp->current_action != 'create' )
		return false;

	if ( !is_array( $bp->events->event_creation_steps ) )
		return false;

	foreach ( $bp->events->event_creation_steps as $slug => $step )
		$temp[$step['position']] = array( 'name' => $step['name'], 'slug' => $slug );

	/* Sort the steps by their position key */
	ksort($temp);
	unset($bp->events->event_creation_steps);

	foreach( $temp as $position => $step )
		$bp->events->event_creation_steps[$step['slug']] = array( 'name' => $step['name'], 'position' => $position );
}
add_action( 'wp', 'events_action_sort_creation_steps', 3 );

function events_aciton_redirect_to_random_event() {
	global $bp, $wpdb;

	if ( $bp->current_component == $bp->events->slug && isset( $_GET['random-event'] ) ) {
		$event = events_get_random_events( 1, 1 );

		bp_core_redirect( $bp->root_domain . '/' . $bp->events->slug . '/' . $event['events'][0]->slug );
	}
}
add_action( 'wp', 'events_aciton_redirect_to_random_event', 6 );


/********************************************************************************
 * Activity & Notification Functions
 *
 * These functions handle the recording, deleting and formatting of activity and
 * notifications for the user and for this specific component.
 */

function events_register_activity_actions() {
	global $bp;

	if ( !function_exists( 'bp_activity_set_action' ) )
		return false;

	bp_activity_set_action( $bp->events->id, 'created_event', __( 'Created an event', 'bp-events' ) );
	bp_activity_set_action( $bp->events->id, 'joined_event', __( 'Joined an event', 'bp-events' ) );
	bp_activity_set_action( $bp->events->id, 'new_wire_post', __( 'New event wire post', 'bp-events' ) );
	bp_activity_set_action( $bp->events->id, 'new_forum_topic', __( 'New event forum topic', 'bp-events' ) );
	bp_activity_set_action( $bp->events->id, 'new_forum_post', __( 'New event forum post', 'bp-events' ) );

	do_action( 'events_register_activity_actions' );
}
add_action( 'plugins_loaded', 'events_register_activity_actions' );


function events_record_activity( $args = '' ) {
	global $bp;

	if ( !function_exists( 'bp_activity_add' ) )
		return false;

	/* If the event is not public, no recording of activity please. */
	if ( 'public' != $bp->events->current_event->status )
		return false;

	$defaults = array(
		'user_id' => $bp->loggedin_user->id,
		'content' => false,
		'primary_link' => false,
		'component_name' => $bp->events->id,
		'component_action' => false,
		'item_id' => false,
		'secondary_item_id' => false,
		'recorded_time' => time(),
		'hide_sitewide' => false
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	return bp_activity_add( array( 'user_id' => $user_id, 'content' => $content, 'primary_link' => $primary_link, 'component_name' => $component_name, 'component_action' => $component_action, 'item_id' => $item_id, 'secondary_item_id' => $secondary_item_id, 'recorded_time' => $recorded_time, 'hide_sitewide' => $hide_sitewide ) );
}

function events_update_last_activity( $event_id ) {
	events_update_eventmeta( $event_id, 'last_activity', time() );
}
add_action( 'events_deleted_wire_post', 'events_update_last_activity' );
add_action( 'events_new_wire_post', 'events_update_last_activity' );
add_action( 'events_joined_event', 'events_update_last_activity' );
add_action( 'events_leave_event', 'events_update_last_activity' );
add_action( 'events_created_event', 'events_update_last_activity' );
add_action( 'events_new_forum_topic', 'events_update_last_activity' );
add_action( 'events_new_forum_topic_post', 'events_update_last_activity' );

function events_format_notifications( $action, $item_id, $secondary_item_id, $total_items ) {
	global $bp;

	switch ( $action ) {
		case 'new_membership_request':
			$event_id = $secondary_item_id;
			$requesting_user_id = $item_id;

			$event = new BP_Events_Event( $event_id, false, false );

			$event_link = bp_get_event_permalink( $event );

			if ( (int)$total_items > 1 ) {
				return apply_filters( 'bp_events_multiple_new_membership_requests_notification', '<a href="' . $event_link . '/admin/membership-requests/" title="' . __( 'Event Membership Requests', 'bp-events' ) . '">' . sprintf( __('%d new membership requests for the event "%s"', 'bp-events' ), (int)$total_items, $event->name ) . '</a>', $event_link, $total_items, $event->name );
			} else {
				$user_fullname = bp_core_get_user_displayname( $requesting_user_id );
				return apply_filters( 'bp_events_single_new_membership_request_notification', '<a href="' . $event_link . '/admin/membership-requests/" title="' . $user_fullname .' requests event membership">' . sprintf( __('%s requests membership for the event "%s"', 'bp-events' ), $user_fullname, $event->name ) . '</a>', $event_link, $user_fullname, $event->name );
			}
		break;

		case 'membership_request_accepted':
			$event_id = $item_id;

			$event = new BP_Events_Event( $event_id, false, false );
			$event_link = bp_get_event_permalink( $event )  . '/?new';

			if ( (int)$total_items > 1 ) {
				return apply_filters( 'bp_events_multiple_membership_request_accepted_notification', '<a href="' . $bp->loggedin_user->domain . $bp->events->slug . '" title="' . __( 'Events', 'bp-events' ) . '">' . sprintf( __('%d accepted event membership requests', 'bp-events' ), (int)$total_items, $event->name ) . '</a>', $total_items, $event_name );
			} else {
				return apply_filters( 'bp_events_single_membership_request_accepted_notification', '<a href="' . $event_link . '">' . sprintf( __('Membership for event "%s" accepted'), $event->name ) . '</a>', $event_link, $event->name );
			}
		break;

		case 'membership_request_rejected':
			$event_id = $item_id;

			$event = new BP_Events_Event( $event_id, false, false );
			$event_link = bp_get_event_permalink( $event )  . '/?new';

			if ( (int)$total_items > 1 ) {
				return apply_filters( 'bp_events_multiple_membership_request_rejected_notification', '<a href="' . site_url() . '/' . BP_MEMBERS_SLUG . '/' . $bp->events->slug . '" title="' . __( 'Events', 'bp-events' ) . '">' . sprintf( __('%d rejected event membership requests', 'bp-events' ), (int)$total_items, $event->name ) . '</a>', $total_items, $event->name );
			} else {
				return apply_filters( 'bp_events_single_membership_request_rejected_notification', '<a href="' . $event_link . '">' . sprintf( __('Membership for event "%s" rejected'), $event->name ) . '</a>', $event_link, $event->name );
			}

		break;

		case 'member_promoted_to_admin':
			$event_id = $item_id;

			$event = new BP_Events_Event( $event_id, false, false );
			$event_link = bp_get_event_permalink( $event )  . '/?new';

			if ( (int)$total_items > 1 ) {
				return apply_filters( 'bp_events_multiple_member_promoted_to_admin_notification', '<a href="' . $bp->loggedin_user->domain . $bp->events->slug . '" title="' . __( 'Events', 'bp-events' ) . '">' . sprintf( __('You were promoted to an admin in %d events', 'bp-events' ), (int)$total_items ) . '</a>', $total_items );
			} else {
				return apply_filters( 'bp_events_single_member_promoted_to_admin_notification', '<a href="' . $event_link . '">' . sprintf( __('You were promoted to an admin in the event %s'), $event->name ) . '</a>', $event_link, $event->name );
			}
		break;

		case 'member_promoted_to_mod':
			$event_id = $item_id;

			$event = new BP_Events_Event( $event_id, false, false );
			$event_link = bp_get_event_permalink( $event )  . '/?new';

			if ( (int)$total_items > 1 ) {
				return apply_filters( 'bp_events_multiple_member_promoted_to_mod_notification', '<a href="' . $bp->loggedin_user->domain . $bp->events->slug . '" title="' . __( 'Events', 'bp-events' ) . '">' . sprintf( __('You were promoted to a mod in %d events', 'bp-events' ), (int)$total_items ) . '</a>', $total_items );
			} else {
				return apply_filters( 'bp_events_single_member_promoted_to_mod_notification', '<a href="' . $event_link . '">' . sprintf( __('You were promoted to a mod in the event %s'), $event->name ) . '</a>', $event_link, $event->name );
			}
		break;

		case 'event_invite':
			$event_id = $item_id;

			$event = new BP_Events_Event( $event_id, false, false );
			$user_url = bp_core_get_userurl( $user_id );

			if ( (int)$total_items > 1 ) {
				return apply_filters( 'bp_events_multiple_event_invite_notification', '<a href="' . $bp->loggedin_user->domain . $bp->events->slug . '/invites" title="' . __( 'Event Invites', 'bp-events' ) . '">' . sprintf( __('You have %d new event invitations', 'bp-events' ), (int)$total_items ) . '</a>', $total_items );
			} else {
				return apply_filters( 'bp_events_single_event_invite_notification', '<a href="' . $bp->loggedin_user->domain . $bp->events->slug . '/invites" title="' . __( 'Event Invites', 'bp-events' ) . '">' . sprintf( __('You have an invitation to the event: %s', 'bp-events' ), $event->name ) . '</a>', $event->name );
			}
		break;
	}

	do_action( 'events_format_notifications', $action, $item_id, $secondary_item_id, $total_items );

	return false;
}


/********************************************************************************
 * Business Functions
 *
 * Business functions are where all the magic happens in BuddyPress. They will
 * handle the actual saving or manipulation of information. Usually they will
 * hand off to a database class for data access, then return
 * true or false on success or failure.
 */

/*** Event Creation, Editing & Deletion *****************************************/

function events_create_event( $args = '' ) {
	global $bp;

	extract( $args );

	/**
	 * Possible parameters (pass as assoc array):
	 *	'event_id'
	 *	'creator_id'
	 *	'name'
	 *	'tagline'
	 *	'description'
	 *	'news'
	 *	'location'
	 *	'slug'
	 *	'status'
	 *	'enable_wire'
	 *	'enable_forum'
	 *  'link_group'
	 *	'date_created'
	 *  'date_start'
	 *  'date_end'
	 *  'is_allday'
	 */

	if ( $event_id )
		$event = new BP_Events_Event( $event_id );
	else
		$event = new BP_Events_Event;

	if ( $creator_id ) {
		$event->creator_id = $creator_id;
	} else {
		$event->creator_id = $bp->loggedin_user->id;
	}

	if ( isset( $name ) )
		$event->name = $name;

	if ( isset( $tagline ) )
		$event->tagline = $tagline;

	if ( isset( $description ) )
		$event->description = $description;

	if ( isset( $news ) )
		$event->news = $news;

	if ( isset( $location ) )
		$event->location = $location;

	if ( isset( $slug ) && events_check_slug( $slug ) )
		$event->slug = $slug;

	if ( isset( $date_start ) )
		$event->date_start = $date_start;

	if ( isset( $date_end ) )
		$event->date_end = $date_end;

	if ( isset( $is_allday ) )
		$event->is_allday = $is_allday;

	if ( isset( $status ) ) {
		if ( events_is_valid_status( $status ) )
			$event->status = $status;
	}

	if ( isset( $enable_wire ) )
		$event->enable_wire = $enable_wire;
	else if ( !$event_id && !isset( $enable_wire ) )
		$event->enable_wire = 1;

	if ( isset( $enable_forum ) )
		$event->enable_forum = $enable_forum;
	else if ( !$event_id && !isset( $enable_forum ) )
		$event->enable_forum = 1;

	if ( isset( $date_created ) )
		$event->date_created = $date_created;

	if ( isset( $link_group ) )
		$event->link_group = $link_group;

	if ( !$event->save() )
		return false;

	if ( !$event_id ) {
		/* If this is a new event, set up the creator as the first member and admin */
		$member = new BP_Events_Member;
		$member->event_id = $event->id;
		$member->user_id = $event->creator_id;
		$member->is_admin = 1;
		$member->user_title = __( 'Event Admin', 'bp-events' );
		$member->is_confirmed = 1;

		$member->save();
	}

	return $event->id;
}

function events_edit_base_event_details( $event_id, $event_name, $event_tagline, $event_desc, $event_news, $event_location, $notify_members, $event_group ) {
	global $bp;

	if ( empty( $event_name ) || empty( $event_desc ) )
		return false;

	$event = new BP_Events_Event( $event_id, false, false );
	$event->name = $event_name;
	$event->tagline = $event_tagline;
	$event->description = $event_desc;
	$event->news = $event_news;
	$event->location = $event_location;
	$event->link_group = $event_group;

	if ( !$event->save() )
		return false;

	if ( $notify_members ) {
		require_once ( WP_PLUGIN_DIR . '/bp-events/bp-events-notifications.php' );
		events_notification_event_updated( $event->id );
	}

	do_action( 'events_details_updated', $event->id );

	return true;
}

function events_edit_event_dates( $event_id, $event_date_start, $event_date_end, $event_is_allday ) {
	global $bp;

	/* Check the nonce first. */
	if ( !check_admin_referer( 'events_edit_event_dates' ) )
		return false;

	if ( empty( $event_date_start ) || empty( $event_date_end ) )
		return false;

	$event = new BP_Events_Event( $event_id, false, false );
	$event->date_start = $event_date_start;
	$event->date_end = $event_date_end;
	$event->is_allday = $event_is_allday;

	if ( !$event->save() )
		return false;

	do_action( 'events_dates_updated', $event->id );

	return true;
}

function events_edit_event_settings( $event_id, $enable_wire, $enable_forum, $enable_photos, $photos_admin_only, $status ) {
	global $bp;

	$event = new BP_Events_Event( $event_id, false, false );
	$event->enable_wire = $enable_wire;
	$event->enable_forum = $enable_forum;
	$event->enable_photos = $enable_photos;
	$event->photos_admin_only = $photos_admin_only;

	/***
	 * Before we potentially switch the event status, if it has been changed to public
	 * from private and there are outstanding membership requests, auto-accept those requests.
	 */
	if ( 'private' == $event->status && 'public' == $status )
		events_accept_all_pending_membership_requests( $event->id );

	/* Now update the status */
	$event->status = $status;

	if ( !$event->save() )
		return false;

	/* If forums have been enabled, and a forum does not yet exist, we need to create one. */
	if ( $event->enable_forum ) {
		if ( function_exists( 'bp_forums_setup' ) && '' == events_get_eventmeta( $event->id, 'forum_id' ) ) {
			events_new_event_forum( $event->id, $event->name, $event->description );
		}
	}

	do_action( 'events_settings_updated', $event->id );

	return true;
}

function events_delete_event( $event_id ) {
	global $bp;

	// Check the user is the event admin.
	if ( !$bp->is_item_admin )
		return false;

	// Get the event object
	$event = new BP_Events_Event( $event_id );

	if ( !$event->delete() )
		return false;

	/* Delete all event activity from activity streams */
	if ( function_exists( 'bp_activity_delete_by_item_id' ) ) {
		bp_activity_delete_by_item_id( array( 'item_id' => $event_id, 'component_name' => $bp->events->id ) );
	}

	// Remove all outstanding invites for this event
	events_delete_all_event_invites( $event_id );

	// Remove all notifications for any user belonging to this event
	bp_core_delete_all_notifications_by_type( $event_id, $bp->events->slug );

	do_action( 'events_delete_event', $event_id );

	return true;
}

function events_is_valid_status( $status ) {
	global $bp;

	return in_array( $status, (array)$bp->events->valid_status );
}

function events_check_slug( $slug ) {
	global $bp;

	if ( 'wp' == substr( $slug, 0, 2 ) )
		$slug = substr( $slug, 2, strlen( $slug ) - 2 );

	if ( in_array( $slug, (array)$bp->events->forbidden_names ) ) {
		$slug = $slug . '-' . rand();
	}

	if ( BP_Events_Event::check_slug( $slug ) ) {
		do {
			$slug = $slug . '-' . rand();
		}
		while ( BP_Events_Event::check_slug( $slug ) );
	}

	return $slug;
}

function events_get_slug( $event_id ) {
	$event = new BP_Events_Event( $event_id, false, false );
	return $event->slug;
}

/*** User Actions ***************************************************************/

function events_leave_event( $event_id, $user_id = false ) {
	global $bp;

	if ( !$user_id )
		$user_id = $bp->loggedin_user->id;

	// This is exactly the same as deleting and invite, just is_confirmed = 1 NOT 0.
	if ( !events_uninvite_user( $user_id, $event_id, true ) )
		return false;

	do_action( 'events_leave_event', $event_id, $user_id );

	/* Modify event member count */
	events_update_eventmeta( $event_id, 'total_member_count', (int) events_get_eventmeta( $event_id, 'total_member_count') - 1 );

	return true;
}

function events_join_event( $event_id, $user_id = false ) {
	global $bp;

	if ( !$user_id )
		$user_id = $bp->loggedin_user->id;

	if ( events_check_user_has_invite( $user_id, $event_id ) )
		events_delete_invite( $user_id, $event_id );

	$new_member = new BP_Events_Member;
	$new_member->event_id = $event_id;
	$new_member->user_id = $user_id;
	$new_member->inviter_id = 0;
	$new_member->is_admin = 0;
	$new_member->user_title = '';
	$new_member->date_modified = time();
	$new_member->is_confirmed = 1;

	if ( !$new_member->save() )
		return false;

	/* Record this in activity streams */
	events_record_activity( array(
		'content' => apply_filters( 'events_activity_joined_group', sprintf( __( '%s joined the event %s', 'bp-events'), bp_core_get_userlink( $user_id ), '<a href="' . bp_get_event_permalink( $bp->events->current_event ) . '">' . attribute_escape( $bp->events->current_event->name ) . '</a>' ) ),
		'primary_link' => apply_filters( 'events_activity_joined_event_primary_link', bp_get_event_permalink( $bp->events->current_event ) ),
		'component_action' => 'joined_event',
		'item_id' => $bp->events->current_event->id
	) );

	/* Modify event meta */
	events_update_eventmeta( $event_id, 'total_member_count', (int) events_get_eventmeta( $event_id, 'total_member_count') + 1 );
	events_update_eventmeta( $event_id, 'last_activity', time() );

	do_action( 'events_join_event', $event_id, $user_id );

	return true;
}

/*** General Event Functions ****************************************************/
function events_perfom_cleanup() {
	return BP_Events_Event::perform_cleanup();
}

function events_check_event_exists( $event_id ) {
	return BP_Events_Event::event_exists( $event_id );
}

function events_get_event_admins( $event_id ) {
	return BP_Events_Member::get_event_administrator_ids( $event_id );
}

function events_get_event_mods( $event_id ) {
	return BP_Events_Member::get_event_moderator_ids( $event_id );
}

function events_get_event_members( $event_id, $limit = false, $page = false ) {
	return BP_Events_Member::get_all_for_event( $event_id, $limit, $page );
}

/*** Event Dates & Times  *************************************/

function events_process_eventtime_form() {

	$event = new BP_Events_Event();

  $sap = $_POST['event-startdate_ampm'];
  $sd = $_POST['event-startdate_day'];
  $sm = $_POST['event-startdate_month'];
  $sy = $_POST['event-startdate_year'];
  $sh = $_POST['event-startdate_hour'];
  $sn = $_POST['event-startdate_minute'];

  $eap = $_POST['event-enddate_ampm'];
  $ed = $_POST['event-enddate_day'];
  $em = $_POST['event-enddate_month'];
  $ey = $_POST['event-enddate_year'];
  $eh = $_POST['event-enddate_hour'];
  $en = $_POST['event-enddate_minute'];

  if ($sap)
  	$sh += ($sap - 1) * 12;
  if ($eap)
    $eh += ($eap - 1) * 12;

 	if ( isset($_POST['event-allday']) ) {
 		// all day event: set enddate to startdate plus 11:59 for make computing easier

		$is_allday = '1';
		$ed = $sd;
		$em = $sm;
		$ey = $sy;
		$eh = 23;
		$en = 45;
		$sh = 0;
		$sn = 0;
	}
	else {
		$is_allday = '0';
	}

	$event->date_start = mktime($sh,$sn,0,$sm,$sd,$sy);
	$event->date_end = mktime($eh,$en,0,$em,$ed,$ey);
	$event->is_allday = $is_allday;

  return $event;
}

/*** Event Fetching, Filtering & Searching  *************************************/

function events_get_all( $limit = null, $page = 1, $only_public = false, $sort_by = false, $order = false ) {
	return BP_Events_Event::get_all( $limit, $page, $only_public, $sort_by, $order );
}

function events_get_newest( $limit = null, $page = 1 ) {
	return BP_Events_Event::get_newest( $limit, $page );
}

function events_get_upcoming( $limit = null, $page = 1 ) {
	return BP_Events_Event::get_upcoming( $limit, $page );
}

function events_get_active( $limit = null, $page = 1 ) {
	return BP_Events_Event::get_active( $limit, $page );
}

function events_get_popular( $limit = null, $page = 1 ) {
	return BP_Events_Event::get_popular( $limit, $page );
}

function events_get_random_events( $limit = null, $page = 1 ) {
	return BP_Events_Event::get_random( $limit, $page );
}

function events_get_alphabetically( $limit = null, $page = 1 ) {
	return BP_Events_Event::get_alphabetically( $limit, $page );
}

function events_get_by_most_forum_topics( $limit = null, $page = 1 ) {
	return BP_Events_Event::get_by_most_forum_topics( $limit, $page );
}

function events_get_by_most_forum_posts( $limit = null, $page = 1 ) {
	return BP_Events_Event::get_by_most_forum_posts( $limit, $page );
}

function events_get_user_events( $user_id = false, $pag_num = false, $pag_page = false ) {
	global $bp;

	if ( !$user_id )
		$user_id = $bp->displayed_user->id;

	return BP_Events_Member::get_event_ids( $user_id, $pag_num, $pag_page );
}

/* TODO: These user event functions could be merged with the above with an optional user ID param */

function events_get_recently_joined_for_user( $user_id = false, $pag_num = false, $pag_page = false, $filter = false ) {
	global $bp;

	if ( !$user_id )
		$user_id = $bp->displayed_user->id;

	return BP_Events_Member::get_recently_joined( $user_id, $pag_num, $pag_page, $filter );
}

function events_get_most_popular_for_user( $user_id = false, $pag_num = false, $pag_page = false, $filter = false ) {
	global $bp;

	if ( !$user_id )
		$user_id = $bp->displayed_user->id;

	return BP_Events_Member::get_most_popular( $user_id, $pag_num, $pag_page, $filter );
}

function events_get_upcoming_for_user( $user_id = false, $pag_num = false, $pag_page = false, $filter = false ) {
	global $bp;

	if ( !$user_id )
		$user_id = $bp->displayed_user->id;

	return BP_Events_Member::get_coming_up( $user_id, $pag_num, $pag_page, $filter );
}

function events_get_recently_active_for_user( $user_id = false, $pag_num = false, $pag_page = false, $filter = false ) {
	global $bp;

	if ( !$user_id )
		$user_id = $bp->displayed_user->id;

	return BP_Events_Member::get_recently_active( $user_id, $pag_num, $pag_page, $filter );
}

function events_get_alphabetically_for_user( $user_id = false, $pag_num = false, $pag_page = false, $filter = false ) {
	global $bp;

	if ( !$user_id )
		$user_id = $bp->displayed_user->id;

	return BP_Events_Member::get_alphabetically( $user_id, $pag_num, $pag_page, $filter );
}

function events_get_archived_for_user( $user_id = false, $pag_num = false, $pag_page = false, $filter = false ) {
	global $bp;

	if ( !$user_id )
		$user_id = $bp->displayed_user->id;

	return BP_Events_Member::get_archived( $user_id, $pag_num, $pag_page, $filter );
}

function events_get_user_is_admin_of( $user_id = false, $pag_num = false, $pag_page = false, $filter = false ) {
	global $bp;

	if ( !$user_id )
		$user_id = $bp->displayed_user->id;

	return BP_Events_Member::get_is_admin_of( $user_id, $pag_num, $pag_page, $filter );
}

function events_get_user_is_mod_of( $user_id = false, $pag_num = false, $pag_page = false, $filter = false ) {
	global $bp;

	if ( !$user_id )
		$user_id = $bp->displayed_user->id;

	return BP_Events_Member::get_is_mod_of( $user_id, $pag_num, $pag_page, $filter );
}

function events_total_events_for_user( $user_id = false ) {
	global $bp;

	if ( !$user_id )
		$user_id = $bp->displayed_user->id;

	return BP_Events_Member::total_event_count( $user_id );
}

function events_get_random_events_for_user( $user_id = false, $total_events = 5 ) {
	global $bp;

	if ( !$user_id )
		$user_id = $bp->displayed_user->id;

	return BP_Events_Member::get_random_events( $user_id, $total_events );
}


function events_get_all_for_daterange( $user_id = false, $startdate, $enddate ) {
	global $bp;

	if ( !$user_id )
		$user_id = $bp->displayed_user->id;

    return  BP_Events_Event::get_daterange( $user_id, $startdate, $enddate );
}

function events_search_events( $search_terms, $pag_num_per_page = 5, $pag_page = 1, $sort_by = false, $order = false ) {
	return BP_Events_Event::search_events( $search_terms, $pag_num_per_page, $pag_page, $sort_by, $order );
}

function events_filter_user_events( $filter, $user_id = false, $order = false, $pag_num_per_page = 5, $pag_page = 1 ) {
	return BP_Events_Event::filter_user_events( $filter, $user_id, $order, $pag_num_per_page, $pag_page );
}

/*** Event Avatars *************************************************************/

function events_avatar_upload_dir( $event_id = false ) {
	global $bp;

	if ( !$event_id )
		$event_id = $bp->events->current_event->id;

	$path  = get_blog_option( BP_ROOT_BLOG, 'upload_path' );
	$newdir = WP_CONTENT_DIR . str_replace( 'wp-content', '', $path );
	$newdir .= '/event-avatars/' . $event_id;

	$newbdir = $newdir;

	if ( !file_exists( $newdir ) )
		@wp_mkdir_p( $newdir );

	$newurl = WP_CONTENT_URL . '/blogs.dir/' . BP_ROOT_BLOG . '/files/event-avatars/' . $event_id;
	$newburl = $newurl;
	$newsubdir = '/event-avatars/' . $event_id;

	return apply_filters( 'events_avatar_upload_dir', array( 'path' => $newdir, 'url' => $newurl, 'subdir' => $newsubdir, 'basedir' => $newbdir, 'baseurl' => $newburl, 'error' => false ) );
}

/*** Event Member Status Checks ************************************************/

function events_is_user_admin( $user_id, $event_id ) {
	return BP_Events_Member::check_is_admin( $user_id, $event_id );
}

function events_is_user_mod( $user_id, $event_id ) {
	return BP_Events_Member::check_is_mod( $user_id, $event_id );
}

function events_is_user_member( $user_id, $event_id ) {
	return BP_Events_Member::check_is_member( $user_id, $event_id );
}

function events_is_user_banned( $user_id, $event_id ) {
	return BP_Events_Member::check_is_banned( $user_id, $event_id );
}

/*** Event Wire ****************************************************************/

function events_new_wire_post( $event_id, $content ) {
	global $bp;

	if ( !function_exists( 'bp_wire_new_post' ) )
		return false;

	if ( $wire_post_id = bp_wire_new_post( $event_id, $content, 'events' ) ) {

		/* Post an email notification if settings allow */
		require_once ( WP_PLUGIN_DIR . '/bp-events/bp-events-notifications.php' );
		events_notification_new_wire_post( $event_id, $wire_post->id );

		/* Record this in activity streams */
		$activity_content = sprintf( __( '%s wrote on the wire of the event %s:', 'bp-events'), bp_core_get_userlink( $bp->loggedin_user->id ), '<a href="' . bp_get_event_permalink( $bp->events->current_event ) . '">' . attribute_escape( $bp->events->current_event->name ) . '</a>' );
		$activity_content .= '<blockquote>' . bp_create_excerpt( attribute_escape( $content ) ) . '</blockquote>';

		events_record_activity( array(
			'content' => apply_filters( 'events_activity_new_wire_post', $activity_content ),
			'primary_link' => apply_filters( 'events_activity_new_wire_post_primary_link', bp_get_event_permalink( $bp->events->current_event ) ),
			'component_action' => 'new_wire_post',
			'item_id' => $bp->events->current_event->id,
			'secondary_item_id' => $wire_post->item_id
		) );

		do_action( 'events_new_wire_post', $event_id, $wire_post->id );

		return true;
	}

	return false;
}

function events_delete_wire_post( $wire_post_id, $table_name ) {
	if ( bp_wire_delete_post( $wire_post_id, 'events', $table_name ) ) {
		/* Delete the activity stream item */
		if ( function_exists( 'bp_activity_delete_by_item_id' ) ) {
			bp_activity_delete_by_item_id( array( 'item_id' => $wire_post_id, 'component_name' => 'events', 'component_action' => 'new_wire_post' ) );
		}

		do_action( 'events_deleted_wire_post', $wire_post_id );
		return true;
	}

	return false;
}

/*** Event Forums **************************************************************/

function events_new_event_forum( $event_id = false, $event_name = false, $event_desc = false ) {
	global $bp;

	if ( !$event_id )
		$event_id = $bp->events->current_event->id;

	if ( !$event_name )
		$event_name = $bp->events->current_event->name;

	if ( !$event_desc )
		$event_desc = $bp->events->current_event->description;

	$forum_id = bp_forums_new_forum( array( 'forum_name' => $event_name, 'forum_desc' => $event_desc ) );

	events_update_eventmeta( $event_id, 'forum_id', $forum_id );

	do_action( 'events_new_event_forum', $forum, $event_id );
}

function events_new_event_forum_post( $post_text, $topic_id ) {
	global $bp;

	if ( empty( $post_text ) )
		return false;

	if ( $forum_post = bp_forums_insert_post( array( 'post_text' => $post_text, 'topic_id' => $topic_id ) ) ) {
		$topic = bp_forums_get_topic_details( $topic_id );

		$activity_content = sprintf( __( '%s posted on the forum topic %s in the event %s:', 'bp-events'), bp_core_get_userlink( $bp->loggedin_user->id ), '<a href="' . bp_get_event_permalink( $bp->events->current_event ) . '/forum/topic/' . $topic->topic_slug .'">' . attribute_escape( $topic->topic_title ) . '</a>', '<a href="' . bp_get_event_permalink( $bp->events->current_event ) . '">' . attribute_escape( $bp->events->current_event->name ) . '</a>' );
		$activity_content .= '<blockquote>' . bp_create_excerpt( attribute_escape( $post_text ) ) . '</blockquote>';

		/* Record this in activity streams */
		events_record_activity( array(
			'content' => apply_filters( 'events_activity_new_forum_post', $activity_content, $post_text, &$topic, &$forum_post ),
			'primary_link' => apply_filters( 'events_activity_new_forum_post_primary_link', bp_get_event_permalink( $bp->events->current_event ) ),
			'component_action' => 'new_forum_post',
			'item_id' => $bp->events->current_event->id,
			'secondary_item_id' => $forum_post
		) );

		do_action( 'events_new_forum_topic_post', $bp->events->current_event->id, $forum_post );

		return $forum_post;
	}

	return false;
}

function events_new_event_forum_topic( $topic_title, $topic_text, $topic_tags, $forum_id ) {
	global $bp;

	if ( empty( $topic_title ) || empty( $topic_text ) )
		return false;

	if ( $topic_id = bp_forums_new_topic( array( 'topic_title' => $topic_title, 'topic_text' => $topic_text, 'topic_tags' => $topic_tags, 'forum_id' => $forum_id ) ) ) {
		$topic = bp_forums_get_topic_details( $topic_id );

		$activity_content = sprintf( __( '%s started the forum topic %s in the event %s:', 'bp-events'), bp_core_get_userlink( $bp->loggedin_user->id ), '<a href="' . bp_get_event_permalink( $bp->events->current_event ) . '/forum/topic/' . $topic->topic_slug .'">' . attribute_escape( $topic->topic_title ) . '</a>', '<a href="' . bp_get_event_permalink( $bp->events->current_event ) . '">' . attribute_escape( $bp->events->current_event->name ) . '</a>' );
		$activity_content .= '<blockquote>' . bp_create_excerpt( attribute_escape( $topic_text ) ) . '</blockquote>';

		/* Record this in activity streams */
		events_record_activity( array(
			'content' => apply_filters( 'events_activity_new_forum_topic', $activity_content, $topic_text, &$topic ),
			'primary_link' => apply_filters( 'events_activity_new_forum_topic_primary_link', bp_get_event_permalink( $bp->events->current_event ) ),
			'component_action' => 'new_forum_topic',
			'item_id' => $bp->events->current_event->id,
			'secondary_item_id' => $topic->topic_id
		) );

		do_action( 'events_new_forum_topic', $bp->events->current_event->id, &$topic );

		return $topic;
	}

	return false;
}

function events_update_event_forum_topic( $topic_id, $topic_title, $topic_text ) {
	global $bp;

	if ( $topic = bp_forums_update_topic( array( 'topic_title' => $topic_title, 'topic_text' => $topic_text, 'topic_id' => $topic_id ) ) ) {
		/* Update the activity stream item */
		if ( function_exists( 'bp_activity_delete_by_item_id' ) )
			bp_activity_delete_by_item_id( array( 'item_id' => $bp->events->current_event->id, 'secondary_item_id' => $topic_id, 'component_name' => $bp->events->id, 'component_action' => 'new_forum_topic' ) );

		$activity_content = sprintf( __( '%s started the forum topic %s in the event %s:', 'bp-events'), bp_core_get_userlink( $topic->topic_poster ), '<a href="' . bp_get_event_permalink( $bp->events->current_event ) . '/forum/topic/' . $topic->topic_slug .'">' . attribute_escape( $topic->topic_title ) . '</a>', '<a href="' . bp_get_event_permalink( $bp->events->current_event ) . '">' . attribute_escape( $bp->events->current_event->name ) . '</a>' );
		$activity_content .= '<blockquote>' . bp_create_excerpt( attribute_escape( $topic_text ) ) . '</blockquote>';

		/* Record this in activity streams */
		events_record_activity( array(
			'content' => apply_filters( 'events_activity_new_forum_topic', $activity_content, $topic_text, &$topic ),
			'primary_link' => apply_filters( 'events_activity_new_forum_topic_primary_link', bp_get_event_permalink( $bp->events->current_event ) ),
			'component_action' => 'new_forum_topic',
			'item_id' => (int)$bp->events->current_event->id,
			'user_id' => (int)$topic->topic_poster,
			'secondary_item_id' => $topic->topic_id,
			'recorded_time' => strtotime( $topic->topic_time )
		) );

		do_action( 'events_update_event_forum_topic', &$topic );

		return true;
	}

	return false;
}

function events_update_event_forum_post( $post_id, $post_text, $topic_id ) {
	global $bp;

	$post = bp_forums_get_post( $post_id );

	if ( $post_id = bp_forums_insert_post( array( 'post_id' => $post_id, 'post_text' => $post_text, 'post_time' => $post->post_time, 'topic_id' => $topic_id, 'poster_id' => $post->poster_id  ) ) ) {
		$topic = bp_forums_get_topic_details( $topic_id );

		/* Update the activity stream item */
		if ( function_exists( 'bp_activity_delete_by_item_id' ) )
			bp_activity_delete_by_item_id( array( 'item_id' => $bp->events->current_event->id, 'secondary_item_id' => $post_id, 'component_name' => $bp->events->id, 'component_action' => 'new_forum_post' ) );

		$activity_content = sprintf( __( '%s posted on the forum topic %s in the event %s:', 'bp-events'), bp_core_get_userlink( $post->poster_id ), '<a href="' . bp_get_event_permalink( $bp->events->current_event ) . '/forum/topic/' . $topic->topic_slug .'">' . attribute_escape( $topic->topic_title ) . '</a>', '<a href="' . bp_get_event_permalink( $bp->events->current_event ) . '">' . attribute_escape( $bp->events->current_event->name ) . '</a>' );
		$activity_content .= '<blockquote>' . bp_create_excerpt( attribute_escape( $post_text ) ) . '</blockquote>';

		/* Record this in activity streams */
		events_record_activity( array(
			'content' => apply_filters( 'events_activity_new_forum_post', $activity_content, $post_text, &$topic, &$forum_post ),
			'primary_link' => apply_filters( 'events_activity_new_forum_post_primary_link', bp_get_event_permalink( $bp->events->current_event ) ),
			'component_action' => 'new_forum_post',
			'item_id' => $bp->events->current_event->id,
			'user_id' => (int)$post->poster_id,
			'secondary_item_id' => $post_id,
			'recorded_time' => strtotime( $post->post_time )
		) );

		do_action( 'events_update_event_forum_post', &$post, &$topic );

		return true;
	}

	return false;
}

function events_delete_event_forum_topic( $topic_id ) {
	global $bp;

	if ( bp_forums_delete_topic( array( 'topic_id' => $topic_id ) ) ) {
		/* Delete the activity stream item */
		if ( function_exists( 'bp_activity_delete_by_item_id' ) ) {
			bp_activity_delete_by_item_id( array( 'item_id' => $topic_id, 'component_name' => $bp->events->id, 'component_action' => 'new_forum_topic' ) );
			bp_activity_delete_by_item_id( array( 'item_id' => $topic_id, 'component_name' => $bp->events->id, 'component_action' => 'new_forum_post' ) );
		}

		do_action( 'events_delete_event_forum_topic', $topic_id );

		return true;
	}

	return false;
}

function events_delete_event_forum_post( $post_id, $topic_id ) {
	global $bp;

	if ( bp_forums_delete_post( array( 'post_id' => $post_id ) ) ) {
		/* Delete the activity stream item */
		if ( function_exists( 'bp_activity_delete_by_item_id' ) ) {
			bp_activity_delete_by_item_id( array( 'item_id' => $bp->events->current_event->id, 'secondary_item_id' => $post_id, 'component_name' => $bp->events->id, 'component_action' => 'new_forum_post' ) );
		}

		do_action( 'events_delete_event_forum_post', $post_id, $topic_id );

		return true;
	}

	return false;
}


function events_total_public_forum_topic_count( $type = 'newest' ) {
	return apply_filters( 'events_total_public_forum_topic_count', BP_Events_Event::get_global_forum_topic_count( $type ) );
}

/*** Event Invitations *********************************************************/

function events_get_invites_for_user( $user_id = false ) {
	global $bp;

	if ( !$user_id )
		$user_id = $bp->loggedin_user->id;

	return BP_Events_Member::get_invites( $user_id );
}

function events_invite_user( $args = '' ) {
	global $bp;

	$defaults = array(
		'user_id' => false,
		'group_id' => false,
		'inviter_id' => $bp->loggedin_user->id,
		'date_modified' => time(),
		'is_confirmed' => 0
	);

	$args = wp_parse_args( $args, $defaults );
	extract( $args, EXTR_SKIP );

	if ( !$user_id || !$event_id )
		return false;

	if ( events_is_user_member( $user_id, $event_id ) )
		return false;

	$invite = new BP_Events_Member;
	$invite->event_id = $event_id;
	$invite->user_id = $user_id;
	$invite->date_modified = $date_modified;
	$invite->inviter_id = $inviter_id;
	$invite->is_confirmed = $is_confirmed;

	if ( !$invite->save() )
		return false;

	do_action( 'events_invite_user', $args );

	return true;
}

function events_uninvite_user( $user_id, $event_id ) {
	global $bp;

	if ( !BP_Events_Member::delete( $user_id, $event_id ) )
		return false;

	do_action( 'events_uninvite_user', $event_id, $user_id );

	return true;
}

function events_accept_invite( $user_id, $event_id ) {
	if ( events_is_user_member( $user_id, $event_id ) )
		return false;

	$member = new BP_Events_Member( $user_id, $event_id );
	$member->accept_invite();

	if ( !$member->save() )
		return false;

	do_action( 'events_accept_invite', $user_id, $event_id );
	return true;
}

function events_reject_invite( $user_id, $event_id ) {
	if ( !BP_Events_Member::delete( $user_id, $event_id ) )
		return false;

	do_action( 'events_reject_invite', $user_id, $event_id );

	return true;
}

function events_delete_invite( $user_id, $event_id ) {
	global $bp;

	$delete = BP_Events_Member::delete_invite( $user_id, $event_id );

	if ( $delete )
		bp_core_delete_notifications_for_user_by_item_id( $user_id, $event_id, $bp->events->slug, 'event_invite' );

	return $delete;
}

function events_send_invites( $user_id, $event_id ) {
	global $bp;

	require_once ( WP_PLUGIN_DIR . '/bp-events/bp-events-notifications.php' );

	if ( !$user_id )
		$user_id = $bp->loggedin_user->id;

	// Send friend invites.
	$invited_users = events_get_invites_for_event( $user_id, $event_id );
	$event = new BP_Events_Event( $event_id, false, false );

	for ( $i = 0; $i < count( $invited_users ); $i++ ) {
		$member = new BP_Events_Member( $invited_users[$i], $event_id );

		// Send the actual invite
		events_notification_event_invites( $event, $member, $user_id );

		$member->invite_sent = 1;
		$member->save();
	}

	do_action( 'events_send_invites', $bp->events->current_event->id, $invited_users );
}

function events_get_invites_for_event( $user_id, $event_id ) {
	return BP_Events_Event::get_invites( $user_id, $event_id );
}

function events_check_user_has_invite( $user_id, $event_id ) {
	return BP_Events_Member::check_has_invite( $user_id, $event_id );
}

function events_delete_all_event_invites( $event_id ) {
	return BP_Events_Event::delete_all_invites( $event_id );
}

/*** Event Promotion & Banning *************************************************/

function events_promote_member( $user_id, $event_id, $status ) {
	global $bp;

	if ( !$bp->is_item_admin )
		return false;

	$member = new BP_Events_Member( $user_id, $event_id );

	do_action( 'events_premote_member', $user_id, $event_id, $status );

	return $member->promote( $status );
}

function events_demote_member( $user_id, $event_id ) {
	global $bp;

	$member = new BP_Events_Member( $user_id, $event_id );

	do_action( 'events_demote_member', $user_id, $event_id );

	return $member->demote();
}

function events_ban_member( $user_id, $event_id ) {
	global $bp;

	if ( !$bp->is_item_admin )
		return false;

	$member = new BP_Events_Member( $user_id, $event_id );

	do_action( 'events_ban_member', $user_id, $event_id );

	return $member->ban();
}

function events_unban_member( $user_id, $event_id ) {
	global $bp;

	if ( !$bp->is_item_admin )
		return false;

	$member = new BP_Events_Member( $user_id, $event_id );

	do_action( 'events_unban_member', $user_id, $event_id );

	return $member->unban();
}

/*** Event Membership ****************************************************/

function events_send_membership_request( $requesting_user_id, $event_id ) {
	global $bp;

	$requesting_user = new BP_Events_Member;
	$requesting_user->event_id = $event_id;
	$requesting_user->user_id = $requesting_user_id;
	$requesting_user->inviter_id = 0;
	$requesting_user->is_admin = 0;
	$requesting_user->user_title = '';
	$requesting_user->date_modified = time();
	$requesting_user->is_confirmed = 0;
	$requesting_user->comments = $_POST['event-request-membership-comments'];

	if ( $requesting_user->save() ) {
		$admins = events_get_event_admins( $event_id );

		require_once ( WP_PLUGIN_DIR . '/bp-events/bp-events-notifications.php' );

		for ( $i = 0; $i < count( $admins ); $i++ ) {
			// Saved okay, now send the email notification
			events_notification_new_membership_request( $requesting_user_id, $admins[$i]->user_id, $event_id, $requesting_user->id );
		}

		do_action( 'events_membership_requested', $requesting_user_id, $admins, $event_id, $requesting_user->id );

		return true;
	}

	return false;
}

function events_accept_membership_request( $membership_id, $user_id = false, $event_id = false ) {
	global $bp;

	if ( $user_id && $event_id )
		$membership = new BP_Events_Member( $user_id, $event_id );
	else
		$membership = new BP_Events_Member( false, false, $membership_id );

	$membership->accept_request();

	if ( !$membership->save() )
		return false;

	/* Modify event member count */
	events_update_eventmeta( $membership->event_id, 'total_member_count', (int) events_get_eventmeta( $membership->event_id, 'total_member_count') + 1 );

	/* Record this in activity streams */
	$event = new BP_Events_Event( $event_id, false, false );

	events_record_activity( array(
		'content' => apply_filters( 'events_activity_membership_accepted', sprintf( __( '%s joined the event %s', 'bp-events'), bp_core_get_userlink( $user_id ), '<a href="' . bp_get_event_permalink( $event ) . '">' . attribute_escape( $event->name ) . '</a>' ), $user_id, &$event ),
		'primary_link' => apply_filters( 'events_activity_membership_accepted_primary_link', bp_get_event_permalink( $event ), &$event ),
		'component_action' => 'joined_event',
		'item_id' => $event->id,
		'user_id' => $user_id
	) );

	/* Send a notification to the user. */
	require_once ( WP_PLUGIN_DIR . '/bp-events/bp-events-notifications.php' );
	events_notification_membership_request_completed( $membership->user_id, $membership->event_id, true );

	do_action( 'events_membership_accepted', $membership->user_id, $membership->event_id );

	return true;
}

function events_reject_membership_request( $membership_id, $user_id = false, $event_id = false ) {
	if ( $user_id && $event_id )
		$membership = new BP_Events_Member( $user_id, $event_id );
	else
		$membership = new BP_Events_Member( false, false, $membership_id );

	if ( !BP_Events_Member::delete( $membership->user_id, $membership->event_id ) )
		return false;

	// Send a notification to the user.
	require_once ( WP_PLUGIN_DIR . '/bp-events/bp-events-notifications.php' );
	events_notification_membership_request_completed( $membership->user_id, $membership->event_id, false );

	do_action( 'events_membership_rejected', $membership->user_id, $membership->event_id );

	return true;
}

function events_check_for_membership_request( $user_id, $event_id ) {
	return BP_Events_Member::check_for_membership_request( $user_id, $event_id );
}

function events_accept_all_pending_membership_requests( $event_id ) {
	$user_ids = BP_Events_Member::get_all_membership_request_user_ids( $event_id );

	if ( !$user_ids )
		return false;

	foreach ( (array) $user_ids as $user_id ) {
		events_accept_membership_request( false, $user_id, $event_id );
	}

	do_action( 'events_accept_all_pending_membership_requests', $event_id );

	return true;
}

/*** Group Events ****************************************************/

function bp_event_add_group_links() {
	global $bp, $groups_template;

	$events = BP_Events_Event::get_events_for_group( bp_group_id(false) );

  	if ( $events['events'] )  { ?>
		<div class="bp-widget">
			<h4><?php _e( 'Group Events'. ' (' .$events['total']. ')', 'bp-events' ); ?></h4>
			<?php bp_events_group_events(); ?>
		</div>
  	<?php }
}

/*** Event Meta ****************************************************/

function events_delete_eventmeta( $event_id, $meta_key = false, $meta_value = false ) {
	global $wpdb, $bp;

	if ( !is_numeric( $event_id ) )
		return false;

	$meta_key = preg_replace('|[^a-z0-9_]|i', '', $meta_key);

	if ( is_array($meta_value) || is_object($meta_value) )
		$meta_value = serialize($meta_value);

	$meta_value = trim( $meta_value );

	if ( !$meta_key ) {
		$wpdb->query( $wpdb->prepare( "DELETE FROM " . $bp->events->table_name_eventmeta . " WHERE event_id = %d", $event_id ) );
	} else if ( $meta_value ) {
		$wpdb->query( $wpdb->prepare( "DELETE FROM " . $bp->events->table_name_eventmeta . " WHERE event_id = %d AND meta_key = %s AND meta_value = %s", $event_id, $meta_key, $meta_value ) );
	} else {
		$wpdb->query( $wpdb->prepare( "DELETE FROM " . $bp->events->table_name_eventmeta . " WHERE event_id = %d AND meta_key = %s", $event_id, $meta_key ) );
	}

	// TODO need to look into using this.
	// wp_cache_delete($event_id, 'events');

	return true;
}

function events_get_eventmeta( $event_id, $meta_key = '') {
	global $wpdb, $bp;

	$event_id = (int) $event_id;

	if ( !$event_id )
		return false;

	if ( !empty($meta_key) ) {
		$meta_key = preg_replace('|[^a-z0-9_]|i', '', $meta_key);

		// TODO need to look into using this.
		//$user = wp_cache_get($user_id, 'users');

		// Check the cached user object
		//if ( false !== $user && isset($user->$meta_key) )
		//	$metas = array($user->$meta_key);
		//else
		$metas = $wpdb->get_col( $wpdb->prepare("SELECT meta_value FROM " . $bp->events->table_name_eventmeta . " WHERE event_id = %d AND meta_key = %s", $event_id, $meta_key) );
	} else {
		$metas = $wpdb->get_col( $wpdb->prepare("SELECT meta_value FROM " . $bp->events->table_name_eventmeta . " WHERE event_id = %d", $event_id) );
	}

	if ( empty($metas) ) {
		if ( empty($meta_key) )
			return array();
		else
			return '';
	}

	$metas = array_map('maybe_unserialize', $metas);

	if ( 1 == count($metas) )
		return $metas[0];
	else
		return $metas;
}

function events_update_eventmeta( $event_id, $meta_key, $meta_value ) {
	global $wpdb, $bp;

	if ( !is_numeric( $event_id ) )
		return false;

	$meta_key = preg_replace( '|[^a-z0-9_]|i', '', $meta_key );

	if ( is_string($meta_value) )
		$meta_value = stripslashes($wpdb->escape($meta_value));

	$meta_value = maybe_serialize($meta_value);

	if (empty($meta_value)) {
		return events_delete_eventmeta( $event_id, $meta_key );
	}

	$cur = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $bp->events->table_name_eventmeta . " WHERE event_id = %d AND meta_key = %s", $event_id, $meta_key ) );

	if ( !$cur ) {
		$wpdb->query( $wpdb->prepare( "INSERT INTO " . $bp->events->table_name_eventmeta . " ( event_id, meta_key, meta_value ) VALUES ( %d, %s, %s )", $event_id, $meta_key, $meta_value ) );
	} else if ( $cur->meta_value != $meta_value ) {
		$wpdb->query( $wpdb->prepare( "UPDATE " . $bp->events->table_name_eventmeta . " SET meta_value = %s WHERE event_id = %d AND meta_key = %s", $meta_value, $event_id, $meta_key ) );
	} else {
		return false;
	}

	// TODO need to look into using this.
	// wp_cache_delete($user_id, 'users');

	return true;
}

/**
 * events_load_textdomain()
 *
 * Load the bp-events translation file for current language
 *
 */
function events_load_textdomain() {
	$locale = apply_filters( 'bp_events_locale', get_locale() );
	$mofile = WP_PLUGIN_DIR . "/bp-events/langs/bp-events-$locale.mo";

	if ( file_exists( $mofile ) )
		load_textdomain( 'bp-events', $mofile );
}
add_action ( 'plugins_loaded', 'events_load_textdomain', 9 );

/* Check for BP existance */
function events_load_buddypress() {
	if ( function_exists( 'bp_core_setup_globals' ) )
		return true;

	/* Get the list of active sitewide plugins */
	$active_sitewide_plugins = maybe_unserialize( get_site_option( 'active_sitewide_plugins' ) );

	if ( !isset( $active_sidewide_plugins['buddypress/bp-loader.php'] ) )
		return false;

	if ( isset( $active_sidewide_plugins['buddypress/bp-loader.php'] ) && !function_exists( 'bp_core_setup_globals' ) ) {
		require_once( WP_PLUGIN_DIR . '/buddypress/bp-loader.php' );
		return true;
	}

	return false;
}

/*** Event Cleanup Functions ****************************************************/

function events_remove_data_for_user( $user_id ) {
	BP_Events_Member::delete_all_for_user($user_id);

	do_action( 'events_remove_data_for_user', $user_id );
}
add_action( 'wpmu_delete_user', 'events_remove_data_for_user', 1 );
add_action( 'delete_user', 'events_remove_data_for_user', 1 );
add_action( 'make_spam_user', 'events_remove_data_for_user', 1 );

function events_clear_event_object_cache( $event_id ) {
	wp_cache_delete( 'events_event_nouserdata_' . $event_id, 'bp' );
	wp_cache_delete( 'events_event_' . $event_id, 'bp' );
	wp_cache_delete( 'newest_events', 'bp' );
	wp_cache_delete( 'active_events', 'bp' );
	wp_cache_delete( 'popular_events', 'bp' );
	wp_cache_delete( 'events_random_events', 'bp' );
}

// List actions to clear object caches on
add_action( 'events_event_deleted', 'events_clear_event_object_cache' );
add_action( 'events_settings_updated', 'events_clear_event_object_cache' );
add_action( 'events_details_updated', 'events_clear_event_object_cache' );
add_action( 'events_event_avatar_updated', 'events_clear_event_object_cache' );

// List actions to clear super cached pages on, if super cache is installed
add_action( 'events_new_wire_post', 'bp_core_clear_cache' );
add_action( 'events_deleted_wire_post', 'bp_core_clear_cache' );
add_action( 'events_join_event', 'bp_core_clear_cache' );
add_action( 'events_leave_event', 'bp_core_clear_cache' );
add_action( 'events_accept_invite', 'bp_core_clear_cache' );
add_action( 'events_reject_invite', 'bp_core_clear_cache' );
add_action( 'events_invite_user', 'bp_core_clear_cache' );
add_action( 'events_uninvite_user', 'bp_core_clear_cache' );
add_action( 'events_details_updated', 'bp_core_clear_cache' );
add_action( 'events_settings_updated', 'bp_core_clear_cache' );
add_action( 'events_unban_member', 'bp_core_clear_cache' );
add_action( 'events_ban_member', 'bp_core_clear_cache' );
add_action( 'events_demote_member', 'bp_core_clear_cache' );
add_action( 'events_premote_member', 'bp_core_clear_cache' );
add_action( 'events_membership_rejected', 'bp_core_clear_cache' );
add_action( 'events_membership_accepted', 'bp_core_clear_cache' );
add_action( 'events_membership_requested', 'bp_core_clear_cache' );
add_action( 'events_create_event_step_complete', 'bp_core_clear_cache' );
add_action( 'events_created_event', 'bp_core_clear_cache' );
add_action( 'events_event_avatar_updated', 'bp_core_clear_cache' );

// Check for BuddyPress and don't include the other files if it's not there.
// @TODO: Needs testing but should fail gracefully.
If (events_load_buddypress()) {
	require ( WP_PLUGIN_DIR . '/bp-events/bp-events-cssjs.php' );
	require ( WP_PLUGIN_DIR . '/bp-events/bp-events-classes.php' );
	require ( WP_PLUGIN_DIR . '/bp-events/bp-events-templatetags.php' );
	require ( WP_PLUGIN_DIR . '/bp-events/bp-events-widgets.php' );
	require ( WP_PLUGIN_DIR . '/bp-events/bp-events-filters.php' );
}

?>