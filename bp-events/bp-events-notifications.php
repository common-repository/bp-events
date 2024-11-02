<?php

function events_notification_new_wire_post( $event_id, $wire_post_id ) {
	global $bp;
	
	if ( !isset( $_POST['wire-post-email-notify'] ) )
		return false;
	
	$wire_post = new BP_Wire_Post( $bp->events->table_name_wire, $wire_post_id );
	$event = new BP_Events_Event( $event_id, false, true );
	
	$poster_name = bp_core_get_user_displayname( $wire_post->user_id );
	$poster_profile_link = bp_core_get_user_domain( $wire_post->user_id );

	$subject = '[' . get_blog_option( BP_ROOT_BLOG, 'blogname' ) . '] ' . sprintf( __( 'New wire post on event: %s', 'bp-events' ), stripslashes($event->name) );

	foreach ( $event->user_dataset as $user ) {
		if ( 'no' == get_usermeta( $user->user_id, 'notification_events_wire_post' ) ) continue;
		
		$ud = get_userdata( $user->user_id );
		
		// Set up and send the message
		$to = $ud->user_email;

		$wire_link = site_url( $bp->events->slug . '/' . $event->slug . '/wire/' );
		$event_link = site_url( $bp->events->slug . '/' . $event->slug . '/' );
		$settings_link = bp_core_get_user_domain( BP_MEMBERS_SLUG . '/' . $user->user_id ) . '/settings/notifications/';

		$message = sprintf( __( 
'%s posted on the wire of the event "%s":

"%s"

To view the event wire: %s

To view the event home: %s

To view %s\'s profile page: %s

---------------------
', 'bp-events' ), $poster_name, stripslashes($event->name), stripslashes( attribute_escape( $wire_post->content ) ), $wire_link, $event_link, $poster_name, $poster_profile_link );

		$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'bp-events' ), $settings_link );

		// Send it
		wp_mail( $to, $subject, $message );
		
		unset( $message, $to );
	}
}
add_action( 'events_new_wire_post', 'events_notification_new_wire_post', 10, 2 );

function events_notification_event_updated( $event_id ) {
	global $bp;
	
	$event = new BP_Events_Event( $event_id, false, true );
	$subject = '[' . get_blog_option( BP_ROOT_BLOG, 'blogname' ) . '] ' . __( 'Event Details Updated', 'bp-events' );

	foreach ( $event->user_dataset as $user ) {
		if ( 'no' == get_usermeta( $user->user_id, 'notification_events_event_updated' ) ) continue;
		
		$ud = get_userdata( $user->user_id );
		
		// Set up and send the message
		$to = $ud->user_email;

		$event_link = site_url( $bp->events->slug . '/' . $event->slug );
		$settings_link = bp_core_get_user_domain( $user->user_id ) . 'settings/notifications/';

		$message = sprintf( __( 
'Event details for the event "%s" were updated:

To view the event: %s

---------------------
', 'bp-events' ), stripslashes( attribute_escape( $event->name ) ), $event_link );

		$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'bp-events' ), $settings_link );

		// Send it
		wp_mail( $to, $subject, $message );

		unset( $message, $to );
	}
}

function events_notification_new_membership_request( $requesting_user_id, $admin_id, $event_id, $membership_id ) {
	global $bp;

	bp_core_add_notification( $requesting_user_id, $admin_id, 'events', 'new_membership_request', $event_id );

	if ( 'no' == get_usermeta( $admin_id, 'notification_events_membership_request' ) )
		return false;
		
	$requesting_user_name = bp_core_get_user_displayname( $requesting_user_id );
	$event = new BP_Events_Event( $event_id, false, false );
	
	$ud = get_userdata($admin_id);
	$requesting_ud = get_userdata($requesting_user_id);

	$event_requests = bp_get_event_permalink( $event ) . '/admin/membership-requests';
	$profile_link = bp_core_get_user_domain( $requesting_user_id );
	$settings_link = bp_core_get_user_domain( $requesting_user_id ) . 'settings/notifications/';

	// Set up and send the message
	$to = $ud->user_email;
	$subject = '[' . get_blog_option( BP_ROOT_BLOG, 'blogname' ) . '] ' . sprintf( __( 'Membership request for event: %s', 'bp-events' ), stripslashes( attribute_escape( $event->name ) ) );

$message = sprintf( __( 
'%s wants to join the event "%s".

Because you are the administrator of this event, you must either accept or reject the membership request.

To view all pending membership requests for this event, please visit:
%s

To view %s\'s profile: %s

---------------------
', 'bp-events' ), $requesting_user_name, stripslashes( attribute_escape( $event->name ) ), $event_requests, $requesting_user_name, $profile_link );

	$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'bp-events' ), $settings_link );

	// Send it
	wp_mail( $to, $subject, $message );	
}

function events_notification_membership_request_completed( $requesting_user_id, $event_id, $accepted = true ) {
	global $bp;
	
	// Post a screen notification first.
	if ( $accepted )
		bp_core_add_notification( $event_id, $requesting_user_id, 'events', 'membership_request_accepted' );
	else
		bp_core_add_notification( $event_id, $requesting_user_id, 'events', 'membership_request_rejected' );
	
	if ( 'no' == get_usermeta( $requesting_user_id, 'notification_membership_request_completed' ) )
		return false;
		
	$event = new BP_Events_Event( $event_id, false, false );
	
	$ud = get_userdata($requesting_user_id);

	$event_link = bp_get_event_permalink( $event );
	$settings_link = bp_core_get_user_domain( $requesting_user_id ) . 'settings/notifications/';

	// Set up and send the message
	$to = $ud->user_email;
	
	if ( $accepted ) {
		$subject = '[' . get_blog_option( BP_ROOT_BLOG, 'blogname' ) . '] ' . sprintf( __( 'Membership request for event "%s" accepted', 'bp-events' ), stripslashes( attribute_escape( $event->name ) ) );
		$message = sprintf( __( 
'Your membership request for the event "%s" has been accepted.

To view the event please login and visit: %s

---------------------
', 'bp-events' ), stripslashes( attribute_escape( $event->name ) ), $event_link );
		
	} else {
		$subject = '[' . get_blog_option( BP_ROOT_BLOG, 'blogname' ) . '] ' . sprintf( __( 'Membership request for event "%s" rejected', 'bp-events' ), stripslashes( attribute_escape( $event->name ) ) );
		$message = sprintf( __( 
'Your membership request for the event "%s" has been rejected.

To submit another request please log in and visit: %s

---------------------
', 'bp-events' ), stripslashes( attribute_escape( $event->name ) ), $event_link );
	}
	
	$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'bp-events' ), $settings_link );

	// Send it
	wp_mail( $to, $subject, $message );	
}

function events_notification_promoted_member( $user_id, $event_id ) {
	global $bp;

	if ( events_is_user_admin( $user_id, $event_id ) ) {
		$promoted_to = __( 'an administrator', 'bp-events' );
		$type = 'member_promoted_to_admin';
	} else {
		$promoted_to = __( 'a moderator', 'bp-events' );
		$type = 'member_promoted_to_mod';
	}
	
	// Post a screen notification first.
	bp_core_add_notification( $event_id, $user_id, 'events', $type );

	if ( 'no' == get_usermeta( $user_id, 'notification_events_admin_promotion' ) )
		return false;

	$event = new BP_Events_Event( $event_id, false, false );
	$ud = get_userdata($user_id);

	$event_link = bp_get_event_permalink( $event );
	$settings_link = bp_core_get_user_domain( $user_id ) . 'settings/notifications/';

	// Set up and send the message
	$to = $ud->user_email;

	$subject = '[' . get_blog_option( BP_ROOT_BLOG, 'blogname' ) . '] ' . sprintf( __( 'You have been promoted in the event: "%s"', 'bp-events' ), stripslashes( attribute_escape( $event->name ) ) );

	$message = sprintf( __( 
'You have been promoted to %s for the event: "%s".

To view the event please visit: %s

---------------------
', 'bp-events' ), $promoted_to, stripslashes( attribute_escape( $event->name ) ), $event_link );

	$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'bp-events' ), $settings_link );

	// Send it
	wp_mail( $to, $subject, $message );
}
add_action( 'events_promoted_member', 'events_notification_promoted_member', 10, 2 );

function events_notification_event_invites( &$event, &$member, $inviter_user_id ) {
	global $bp;
	
	$inviter_ud = get_userdata( $inviter_user_id );
	$inviter_name = bp_core_get_userlink( $inviter_user_id, true, false, true );
	$inviter_link = bp_core_get_user_domain( $inviter_user_id );
	
	$event_link = bp_get_event_permalink( $event );
	
	if ( !$member->invite_sent ) {
		$invited_user_id = $member->user_id;

		// Post a screen notification first.
		bp_core_add_notification( $event->id, $invited_user_id, 'events', 'event_invite' );

		if ( 'no' == get_usermeta( $invited_user_id, 'notification_events_invite' ) )
			return false;

		$invited_ud = get_userdata($invited_user_id);
		$settings_link = bp_core_get_user_domain( $invited_user_id ) . 'settings/notifications/';
		$invited_link = bp_core_get_user_domain( $invited_user_id );
		$invites_link = $invited_link . '/' . $bp->events->slug . '/invites';

		// Set up and send the message
		$to = $invited_ud->user_email;

		$subject = '[' . get_blog_option( BP_ROOT_BLOG, 'blogname' ) . '] ' . sprintf( __( 'You have an invitation to the event: "%s"', 'bp-events' ), stripslashes( attribute_escape( $event->name ) ) );

		$message = sprintf( __( 
'One of your friends %s has invited you to the event: "%s".

To view your event invites visit: %s

To view the event visit: %s

To view %s\'s profile visit: %s

---------------------
', 'bp-events' ), $inviter_name, stripslashes( attribute_escape( $event->name ) ), $invites_link, $event_link, $inviter_name, $inviter_link );

		$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'bp-events' ), $settings_link );

		// Send it
		wp_mail( $to, $subject, $message );
	}
}

?>