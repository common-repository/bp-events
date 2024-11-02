<?php

function bp_events_header_tabs() {
	global $bp, $create_event_step, $completed_to_step;
?>
	<li<?php if ( !isset($bp->action_variables[0]) || 'upcoming' == $bp->action_variables[0] ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->displayed_user->domain . $bp->events->slug ?>/my-events/upcoming"><?php _e( 'Upcoming', 'bp-events' ) ?></a></li>
	<li<?php if ( 'most-popular' == $bp->action_variables[0] ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->displayed_user->domain . $bp->events->slug ?>/my-events/most-popular""><?php _e( 'Most Popular', 'bp-events' ) ?></a></li>
	<li<?php if ( 'admin-of' == $bp->action_variables[0] ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->displayed_user->domain . $bp->events->slug ?>/my-events/admin-of""><?php _e( 'Administrator Of', 'bp-events' ) ?></a></li>
	<li<?php if ( 'mod-of' == $bp->action_variables[0] ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->displayed_user->domain . $bp->events->slug ?>/my-events/mod-of""><?php _e( 'Moderator Of', 'bp-events' ) ?></a></li>
	<li<?php if ( 'alphabetically' == $bp->action_variables[0] ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->displayed_user->domain . $bp->events->slug ?>/my-events/alphabetically""><?php _e( 'Alphabetically', 'bp-events' ) ?></a></li>
	<li<?php if ( 'archived' == $bp->action_variables[0] ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->displayed_user->domain . $bp->events->slug ?>/my-events/archived"><?php _e( 'Archived', 'bp-events' ) ?></a></li>
<?php
	do_action( 'events_header_tabs' );
}

function bp_events_filter_title() {
	global $bp;

	$current_filter = $bp->action_variables[0];

	switch ( $current_filter ) {
		case 'upcoming': default:
			_e( 'Upcoming', 'bp-events' );
			break;
		case 'most-popular':
			_e( 'Most Popular', 'bp-events' );
			break;
		case 'admin-of':
			_e( 'Administrator Of', 'bp-events' );
			break;
		case 'mod-of':
			_e( 'Moderator Of', 'bp-events' );
			break;
		case 'alphabetically':
			_e( 'Alphabetically', 'bp-events' );
		break;
		case 'archived':
			_e( 'Archived', 'bp-events' );
			break;
	}
	do_action( 'bp_events_filter_title' );
}

function bp_is_event_admin_screen( $slug ) {
	global $bp;

	if ( $bp->current_component != BP_EVENTS_SLUG || 'admin' != $bp->current_action )
		return false;

	if ( $bp->action_variables[0] == $slug )
		return true;

	return false;
}

function bp_event_current_avatar() {
	global $bp;

	if ( $bp->events->current_event->avatar_full ) { ?>
		<img src="<?php echo attribute_escape( $bp->events->current_event->avatar_full ) ?>" alt="<?php _e( 'Event Avatar', 'bp-events' ) ?>" class="avatar" />
	<?php } else { ?>
		<img src="<?php echo $bp->events->image_base . '/none.gif' ?>" alt="<?php _e( 'No Event Avatar', 'bp-events' ) ?>" class="avatar" />
	<?php }
}

function bp_get_event_has_avatar() {
	global $bp;

	if ( !empty( $_FILES ) || !bp_core_fetch_avatar( array( 'item_id' => $bp->events->current_event->id, 'object' => 'event', 'avatar_dir' => 'event-avatars', 'no_grav' => true ) ) )
		return false;

	return true;
}

function bp_event_avatar_delete_link() {
	echo bp_get_event_avatar_delete_link();
}
	function bp_get_event_avatar_delete_link() {
		global $bp;

		return apply_filters( 'bp_get_event_avatar_delete_link', wp_nonce_url( bp_get_event_permalink( $bp->events->current_event ) . '/admin/event-avatar/delete', 'bp_event_avatar_delete' ) );
	}

function bp_event_avatar_edit_form() {
	events_avatar_upload();
}

function bp_custom_event_boxes() {
	do_action( 'events_custom_event_boxes' );
}

function bp_custom_event_admin_tabs() {
	do_action( 'events_custom_event_admin_tabs' );
}

function bp_custom_event_fields_editable() {
	do_action( 'events_custom_event_fields_editable' );
}

function bp_custom_event_fields() {
	do_action( 'events_custom_event_fields' );
}


/*****************************************************************************
 * User Events Template Class/Tags
 **/

class BP_Events_User_Events_Template {
	var $current_event = -1;
	var $event_count;
	var $events;
	var $event;

	var $in_the_loop;

	var $pag_page;
	var $pag_num;
	var $pag_links;
	var $total_event_count;

	var $single_event = false;

	var $sort_by;
	var $order;

	function bp_events_user_events_template( $user_id, $type, $per_page, $max, $slug, $filter ) {
		global $bp;

		if ( !$user_id )
			$user_id = $bp->displayed_user->id;

		$this->pag_page = isset( $_REQUEST['grpage'] ) ? intval( $_REQUEST['grpage'] ) : 1;
		$this->pag_num = isset( $_REQUEST['num'] ) ? intval( $_REQUEST['num'] ) : $per_page;

		switch ( $type ) {
			case 'recently-joined':
				$this->events = events_get_recently_joined_for_user( $user_id, $this->pag_num, $this->pag_page, $filter );
				break;

			case 'popular':
				$this->events = events_get_most_popular_for_user( $user_id, $this->pag_num, $this->pag_page, $filter );
				break;

			case 'admin-of':
				$this->events = events_get_user_is_admin_of( $user_id, $this->pag_num, $this->pag_page, $filter );
				break;

			case 'mod-of':
				$this->events = events_get_user_is_mod_of( $user_id, $this->pag_num, $this->pag_page, $filter );
				break;

			case 'alphabetical':
				$this->events = events_get_alphabetically_for_user( $user_id, $this->pag_num, $this->pag_page, $filter );
				break;

			case 'archived':
				$this->events = events_get_archived_for_user( $user_id, $this->pag_num, $this->pag_page, $filter );
				break;

			case 'invites':
				$this->events = events_get_invites_for_user();
				break;

			case 'single-event':
				$event = new stdClass;
				$event->event_id = BP_Events_Event::get_id_from_slug($slug);
				$this->events = array( $event );
				break;

			case 'active':
				$this->events = events_get_recently_active_for_user( $user_id, $this->pag_num, $this->pag_page, $filter );
				break;

			case 'upcoming': default:
				$this->events = events_get_upcoming_for_user( $user_id, $this->pag_num, $this->pag_page, $filter );
				break;
		}

		if ( 'invites' == $type ) {
			$this->total_event_count = count($this->events);
			$this->event_count = count($this->events);
		} else if ( 'single-event' == $type ) {
			$this->single_event = true;
			$this->total_event_count = 1;
			$this->event_count = 1;
		} else {
			if ( !$max || $max >= (int)$this->events['total'] )
				$this->total_event_count = (int)$this->events['total'];
			else
				$this->total_event_count = (int)$max;

			$this->events = $this->events['events'];

			if ( $max ) {
				if ( $max >= count($this->events) )
					$this->event_count = count($this->events);
				else
					$this->event_count = (int)$max;
			} else {
				$this->event_count = count($this->events);
			}
		}

		$this->pag_links = paginate_links( array(
			'base' => add_query_arg( array( 'grpage' => '%#%', 'num' => $this->pag_num, 's' => $_REQUEST['s'], 'sortby' => $this->sort_by, 'order' => $this->order ) ),
			'format' => '',
			'total' => ceil($this->total_event_count / $this->pag_num),
			'current' => $this->pag_page,
			'prev_text' => '&laquo;',
			'next_text' => '&raquo;',
			'mid_size' => 1
		));
	}

	function has_events() {
		if ( $this->event_count )
			return true;

		return false;
	}

	function next_event() {
		$this->current_event++;
		$this->event = $this->events[$this->current_event];

		return $this->event;
	}

	function rewind_events() {
		$this->current_event = -1;
		if ( $this->event_count > 0 ) {
			$this->event = $this->events[0];
		}
	}

	function user_events() {
		if ( $this->current_event + 1 < $this->event_count ) {
			return true;
		} elseif ( $this->current_event + 1 == $this->event_count ) {
			do_action('loop_end');
			// Do some cleaning up after the loop
			$this->rewind_events();
		}

		$this->in_the_loop = false;
		return false;
	}

	function the_event() {
		global $event;

		$this->in_the_loop = true;
		$this->event = $this->next_event();

		// If this is a single event then instantiate event meta when creating the object.
		if ( $this->single_event ) {
			if ( !$event = wp_cache_get( 'events_event_' . $this->event->event_id, 'bp' ) ) {
				$event = new BP_Events_Event( $this->event->event_id, true );
				wp_cache_set( 'events_event_' . $this->event->event_id, $event, 'bp' );
			}
		} else {
			if ( !$event = wp_cache_get( 'events_event_nouserdata_' . $this->event->event_id, 'bp' ) ) {
				$event = new BP_Events_Event( $this->event->event_id, false, false );
				wp_cache_set( 'events_event_nouserdata_' . $this->event->event_id, $event, 'bp' );
			}
		}

		$this->event = $event;

		if ( 0 == $this->current_event ) // loop has just started
			do_action('loop_start');
	}
}

function bp_has_events( $args = '' ) {
	global $events_template, $bp;

	$defaults = array(
		'type' => 'upcoming',
		'user_id' => false,
		'per_page' => 10,
		'max' => false,
		'slug' => false,
		'filter' => false
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	/* The following code will auto set parameters based on the page being viewed.
	 * for example on example.com/members/andy/events/my-events/most-popular/
	 * $type = 'most-popular'
	 */
	if ( 'my-events' == $bp->current_action ) {
		$order = $bp->action_variables[0];
		if ( 'upcoming' == $order )
			$type = 'upcoming';
		else if ( 'recently-joined' == $order )
			$type = 'recently-joined';
		else if ( 'most-popular' == $order )
			$type = 'popular';
		else if ( 'admin-of' == $order )
			$type = 'admin-of';
		else if ( 'mod-of' == $order )
			$type = 'mod-of';
		else if ( 'alphabetically' == $order )
			$type = 'alphabetical';
		else if ( 'archived' == $order )
			$type = 'archived';
	} else if ( 'invites' == $bp->current_action ) {
		$type = 'invites';
	} else if ( $bp->events->current_event->slug ) {
		$type = 'single-event';
		$slug = $bp->events->current_event->slug;
	}

	if ( isset( $_REQUEST['event-filter-box'] ) )
		$filter = $_REQUEST['event-filter-box'];

	$events_template = new BP_Events_User_Events_Template( $user_id, $type, $per_page, $max, $slug, $filter );
	return $events_template->has_events();
}

function bp_events() {
	global $events_template;
	return $events_template->user_events();
}

function bp_the_event() {
	global $events_template;
	return $events_template->the_event();
}

function bp_event_is_visible( $event = false ) {
	global $bp, $events_template;

	if ( !$event )
		$event =& $events_template->event;

	if ( 'public' == $event->status ) {
		return true;
	} else {
		if ( events_is_user_member( $bp->loggedin_user->id, $event->id ) ) {
			return true;
		}
	}

	return false;
}

function bp_event_has_news( $event = false ) {
	global $events_template;

	if ( !$event )
		$event =& $events_template->event;

	if ( empty( $event->news ) )
		return false;

	return true;
}

function bp_event_id( $deprecated = true, $deprecated2 = false ) {
	global $events_template;

	if ( !$deprecated )
		return bp_get_event_id();
	else
		echo bp_get_event_id();
}
	function bp_get_event_id( $event = false ) {
		global $events_template;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_id', $event->id );
	}

function bp_event_name( $deprecated = true, $deprecated2 = false ) {
	global $events_template;

	if ( !$deprecated )
		return bp_get_event_name();
	else
		echo bp_get_event_name();
}
	function bp_get_event_name( $event = false ) {
		global $events_template;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_name', $event->name );
	}

function bp_event_type() {
	echo bp_get_event_type();
}
	function bp_get_event_type( $event = false ) {
		global $events_template;

		if ( !$event )
			$event =& $events_template->event;

		if ( 'public' == $event->status ) {
			$type = __( "Public Event", "buddypress" );
		} else if ( 'hidden' == $event->status ) {
			$type = __( "Hidden Event", "buddypress" );
		} else if ( 'private' == $event->status ) {
			$type = __( "Private Event", "buddypress" );
		} else {
			$type = ucwords( $event->status ) . ' ' . __( 'Event', 'bp-events' );
		}

		return apply_filters( 'bp_get_event_type', $type . $forevent );
	}

function bp_event_status() {
	echo bp_get_event_status();
}
	function bp_get_event_status( $event = false ) {
		global $events_template;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_status', $event->status );
	}

function bp_event_avatar( $args = '' ) {
	echo bp_get_event_avatar( $args );
}
	function bp_get_event_avatar( $args = '' ) {
		global $bp, $events_template;

		$defaults = array(
			'type' => 'full',
			'width' => false,
			'height' => false,
			'class' => 'avatar',
			'id' => false,
			'alt' => __( 'Event avatar', 'bp-events' )
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		/* Fetch the avatar from the folder, if not provide backwards compat. */
		if ( !$avatar = bp_core_fetch_avatar( array( 'item_id' => $events_template->event->id, 'object' => 'event', 'type' => $type, 'avatar_dir' => 'event-avatars', 'alt' => $alt, 'css_id' => $id, 'class' => $class, 'width' => $width, 'height' => $height ) ) )
			$avatar = '<img src="' . attribute_escape( $events_template->event->avatar_thumb ) . '" class="avatar" alt="' . attribute_escape( $events_template->event->name ) . '" />';

		return apply_filters( 'bp_get_event_avatar', $avatar );
	}

function bp_event_avatar_thumb() {
	echo bp_get_event_avatar_thumb();
}
	function bp_get_event_avatar_thumb( $event = false ) {
		return bp_get_event_avatar( 'type=thumb' );
	}

function bp_event_avatar_mini() {
	echo bp_get_event_avatar_mini();
}
	function bp_get_event_avatar_mini( $event = false ) {
		return bp_get_event_avatar( 'type=thumb&width=30&height=30' );
	}

function bp_event_last_active( $deprecated = true, $deprecated2 = false ) {
	if ( !$deprecated )
		return bp_get_event_last_active();
	else
		echo bp_get_event_last_active();
}
	function bp_get_event_last_active( $event = false ) {
		global $events_template;

		if ( !$event )
			$event =& $events_template->event;

		$last_active = events_get_eventmeta( $event->id, 'last_activity' );

		if ( empty( $last_active ) ) {
			return __( 'not yet active', 'bp-events' );
		} else {
			return apply_filters( 'bp_get_event_last_active', bp_core_time_since( $last_active ) );
		}
	}

function bp_get_event_upcoming( $event = false ) {
		global $events_template;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_upcoming', bp_event_date_togo( $event, false ) );
	}

function bp_event_permalink( $deprecated = false, $deprecated2 = true ) {
	if ( !$deprecated2 )
		return bp_get_event_permalink();
	Else
		echo bp_get_event_permalink();
}
	function bp_get_event_permalink( $event = false ) {
		global $events_template, $bp;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_permalink', $bp->root_domain . '/' . $bp->events->slug . '/' . $event->slug );
	}

function bp_event_admin_permalink( $deprecated = true, $deprecated2 = false ) {
	if ( !$deprecated )
		return bp_get_event_admin_permalink();
	else
		echo bp_get_event_admin_permalink();
}
	function bp_get_event_admin_permalink( $event = false ) {
		global $events_template, $bp;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_admin_permalink', $bp->root_domain . '/' . $bp->events->slug . '/' . $event->slug . '/admin' );
	}

function bp_event_slug() {
	echo bp_get_event_slug();
}
	function bp_get_event_slug( $event = false ) {
		global $events_template;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_slug', $event->slug );
	}

function bp_event_description( $deprecated = false, $deprecated2 = true ) {
	if ( !$deprecated2 )
		return bp_get_event_description();
	else
		echo bp_get_event_description();
}
	function bp_get_event_description( $event = false ) {
		global $events_template;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_description', stripslashes($event->description) );
	}

function bp_event_description_editable( $deprecated = false ) {
	echo bp_get_event_description_editable();
}
	function bp_get_event_description_editable( $event = false ) {
		global $events_template;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_description_editable', $event->description );
	}

function bp_event_description_excerpt( $deprecated = false ) {
	echo bp_get_event_description_excerpt();
}
	function bp_get_event_description_excerpt( $event = false ) {
		global $events_template;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_description_excerpt', bp_create_excerpt( $event->description, 20 ) );
	}

function bp_event_news( $deprecated = false ) {
	echo bp_get_event_news();
}
	function bp_get_event_news( $event = false ) {
		global $events_template;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_news', stripslashes($events_template->event->news) );
	}

function bp_event_news_editable( $deprecated = false ) {
	echo bp_get_event_news_editable();
}
	function bp_get_event_news_editable( $event = false ) {
		global $events_template;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_news_editable', $event->news );
	}

function bp_event_public_status( $deprecated = false ) {
	echo bp_get_event_public_status();
}
	function bp_get_event_public_status( $event = false ) {
		global $events_template;

		if ( !$event )
			$event =& $events_template->event;

		if ( $event->is_public ) {
			return __( 'Public', 'bp-events' );
		} else {
			return __( 'Private', 'bp-events' );
		}
	}

function bp_event_is_public( $deprecated = false ) {
	echo bp_get_event_is_public();
}
	function bp_get_event_is_public( $event = false ) {
		global $events_template;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_is_public', $event->is_public );
	}

function bp_event_date_created( $deprecated = false ) {
	echo bp_get_event_date_created();
}
	function bp_get_event_date_created( $event = false ) {
		global $events_template;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_date_created', date( get_option( 'date_format' ), $event->date_created ) );
	}

function bp_event_is_admin() {
	global $bp;

	return $bp->is_item_admin;
}

function bp_event_is_mod() {
	global $bp;

	return $bp->is_item_mod;
}

function bp_event_list_admins( $full_list = true, $event = false ) {
	global $events_template;

	if ( !$event )
		$event =& $events_template->event;

	if ( !$admins = &$event->admins )
		$admins = $event->get_administrators();

	if ( $admins ) {
		if ( $full_list ) { ?>
			<ul id="event-admins">
			<?php for ( $i = 0; $i < count($admins); $i++ ) { ?>
				<li>
					<a href="<?php echo $admins[$i]->user->user_url ?>" title="<?php echo $admins[$i]->user->fullname ?>"><?php echo $admins[$i]->user->avatar_mini ?></a>
					<h5><?php echo $admins[$i]->user->user_link ?></h5>
					<span class="activity"><?php echo $admins[$i]->user_title ?></span>
					<hr />
				</li>
			<?php } ?>
			</ul>
		<?php } else { ?>
			<?php for ( $i = 0; $i < count($admins); $i++ ) { ?>
				<?php echo $admins[$i]->user->user_link ?>
			<?php } ?>
		<?php } ?>
	<?php } else { ?>
		<span class="activity"><?php _e( 'No Admins', 'bp-events' ) ?></span>
	<?php } ?>

<?php
}

function bp_event_list_mods( $full_list = true, $event = false ) {
	global $events_template;

	if ( !$event )
		$event =& $events_template->event;

	$event_mods = events_get_event_mods( $event->id );

	if ( $event_mods ) {
		if ( $full_list ) { ?>
			<ul id="event-mods" class="mods-list">
			<?php for ( $i = 0; $i < count($event_mods); $i++ ) { ?>
				<li>
					<a href="<?php echo bp_core_get_userlink( $event_mods[$i]->user_id, false, true ) ?>" title="<?php echo bp_core_get_user_displayname( $event_mods[$i]->user->user_id ) ?>"><?php echo bp_core_fetch_avatar( array( 'item_id' => $event_mods[$i]->user_id, 'type' => 'thumb', 'width' => 30, 'height' => 30 ) ) ?></a>
					<h5><?php echo bp_core_get_userlink( $event_mods[$i]->user_id ) ?></h5>
					<span class="activity"><?php _e( 'Event Mod', 'bp-events' ) ?></span>
					<div class="clear"></div>
				</li>
			<?php } ?>
			</ul>
		<?php } else { ?>
			<?php for ( $i = 0; $i < count($admins); $i++ ) { ?>
				<?php echo bp_core_get_userlink( $event_mods[$i]->user_id ) . ' ' ?>
			<?php } ?>
		<?php } ?>
	<?php } else { ?>
		<span class="activity"><?php _e( 'No Mods', 'bp-events' ) ?></span>
	<?php } ?>

<?php
}

function bp_event_all_members_permalink( $deprecated = true, $deprecated2 = false ) {
	global $events_template, $bp;

	if ( !$event )
		$event =& $events_template->event;

	if ( !$deprecated )
		return bp_get_event_all_members_permalink();
	else
		echo bp_get_event_all_members_permalink();
}
	function bp_get_event_all_members_permalink( $event = false ) {
		global $events_template, $bp;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_all_members_permalink', bp_get_event_permalink( $event ) . '/members' );
	}

function bp_event_search_form() {
	global $events_template, $bp;

	$action = $bp->displayed_user->domain . $bp->events->slug . '/my-events/search/';
	$label = __('Filter Events', 'bp-events');
	$name = 'event-filter-box';
?>
	<form action="<?php echo $action ?>" id="event-search-form" method="post">
		<label for="<?php echo $name ?>" id="<?php echo $name ?>-label"><?php echo $label ?></label>
		<input type="search" name="<?php echo $name ?>" id="<?php echo $name ?>" value="<?php echo $value ?>"<?php echo $disabled ?> />

		<?php wp_nonce_field( 'event-filter-box', '_wpnonce_event_filter' ) ?>
	</form>
<?php
}

function bp_event_show_no_events_message() {
	global $bp;

	if ( !events_total_events_for_user( $bp->displayed_user->id ) )
		return true;

	return false;
}

function bp_event_pagination() {
	echo bp_get_event_pagination();
}
	function bp_get_event_pagination() {
		global $events_template;

		return apply_filters( 'bp_get_event_pagination', $events_template->pag_links );
	}

function bp_event_pagination_count() {
	global $bp, $events_template;

	$from_num = intval( ( $events_template->pag_page - 1 ) * $events_template->pag_num ) + 1;
	$to_num = ( $from_num + ( $events_template->pag_num - 1 ) > $events_template->total_event_count ) ? $events_template->total_event_count : $from_num + ( $events_template->pag_num - 1) ;

	echo sprintf( __( 'Viewing event %d to %d (of %d events)', 'bp-events' ), $from_num, $to_num, $events_template->total_event_count ); ?> &nbsp;
	<span class="ajax-loader"></span><?php
}

function bp_total_event_count() {
	echo bp_get_total_event_count();
}
	function bp_get_total_event_count() {
		global $events_template;

		return apply_filters( 'bp_get_total_event_count', $events_template->total_event_count );
	}

function bp_event_total_members( $deprecated = true, $deprecated2 = false ) {
	if ( !$deprecated )
		return bp_get_event_total_members();
	else
		echo bp_get_event_total_members();
}
	function bp_get_event_total_members( $echo = true, $event = false ) {
		global $events_template;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_total_members', $event->total_member_count );
	}

function bp_event_show_wire_setting( $event = false ) {
	global $events_template;

	if ( !$event )
		$event =& $events_template->event;

	if ( $event->enable_wire )
		echo ' checked="checked"';
}

function bp_event_is_wire_enabled( $event = false ) {
	global $events_template;

	if ( !$event )
		$event =& $events_template->event;

	if ( $event->enable_wire )
		return true;

	return false;
}

function bp_event_forum_permalink( $deprecated = false ) {
	echo bp_get_event_forum_permalink();
}
	function bp_get_event_forum_permalink( $event = false ) {
		global $events_template;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_forum_permalink', bp_get_event_permalink( $event ) . '/forum' );
	}

function bp_event_is_forum_enabled( $event = false ) {
	global $events_template;

	if ( !$event )
		$event =& $events_template->event;

	if ( function_exists( 'bp_forums_is_installed_correctly' ) ) {
		if ( bp_forums_is_installed_correctly() ) {
			if ( $event->enable_forum )
				return true;

			return false;
		} else {
			return false;
		}
	}

	return false;
}

function bp_event_show_forum_setting( $event = false ) {
	global $events_template;

	if ( !$event )
		$event =& $events_template->event;

	if ( $event->enable_forum )
		echo ' checked="checked"';
}

function bp_event_show_status_setting( $setting, $event = false ) {
	global $events_template;

	if ( !$event )
		$event =& $events_template->event;

	if ( $setting == $event->status )
		echo ' checked="checked"';
}

function bp_event_admin_memberlist( $admin_list = false, $event = false ) {
	global $events_template;

	if ( !$event )
		$event =& $events_template->event;

	$admins = events_get_event_admins( $event->id );
?>
	<?php if ( $admins ) { ?>
		<ul id="admins-list" class="item-list<?php if ( $admin_list ) { ?> single-line<?php } ?>">
		<?php foreach ( $admins as $admin ) { ?>
			<?php if ( $admin_list ) { ?>
			<li>
				<?php echo bp_core_fetch_avatar( array( 'item_id' => $admin->user_id, 'type' => 'thumb', 'width' => 30, 'height' => 30 ) ) ?>
				<h5><?php echo bp_core_get_userlink( $admin->user_id ) ?>  <span class="small"> &mdash; <a class="confirm" href="<?php bp_event_member_demote_link($admin->user_id) ?>"><?php _e( 'Demote to Attendee', 'bp-events' ) ?></a></span></h5>
			</li>
			<?php } else { ?>
			<li>
				<?php echo bp_core_fetch_avatar( array( 'item_id' => $admin->user_id, 'type' => 'thumb' ) ) ?>
				<h5><?php echo bp_core_get_userlink( $admin->user_id ) ?></h5>
				<span class="activity"><?php echo bp_core_get_last_activity( strtotime( $admin->date_modified ), __( 'joined %s ago', 'bp-events') ); ?></span>

				<?php if ( function_exists( 'friends_install' ) ) : ?>
					<div class="action">
						<?php bp_add_friend_button( $admin->user_id ) ?>
					</div>
				<?php endif; ?>
			</li>
			<?php } ?>
		<?php } ?>
		</ul>
	<?php } else { ?>
		<div id="message" class="info">
			<p><?php _e( 'This event has no administrators', 'bp-events' ); ?></p>
		</div>
	<?php }
}

function bp_event_mod_memberlist( $admin_list = false, $event = false ) {
	global $events_template, $event_mods;

	if ( !$event )
		$event =& $events_template->event;

	$event_mods = events_get_event_mods( $event->id );
	?>
		<?php if ( $event_mods ) { ?>
			<ul id="mods-list" class="item-list<?php if ( $admin_list ) { ?> single-line<?php } ?>">
			<?php foreach ( $event_mods as $mod ) { ?>
				<?php if ( $admin_list ) { ?>
				<li>
					<?php echo bp_core_fetch_avatar( array( 'item_id' => $mod->user_id, 'type' => 'thumb', 'width' => 30, 'height' => 30 ) ) ?>
					<h5><?php echo bp_core_get_userlink( $mod->user_id ) ?>  <span class="small"> &mdash; <a href="<?php bp_event_member_promote_admin_link( array( 'user_id' => $mod->user_id ) ) ?>" class="confirm" title="<?php _e( 'Promote to Admin', 'bp-events' ); ?>"><?php _e( 'Promote to Admin', 'bp-events' ); ?></a> | <a class="confirm" href="<?php bp_event_member_demote_link($mod->user_id) ?>"><?php _e( 'Demote to Member', 'bp-events' ) ?></a></span></h5>
				</li>
				<?php } else { ?>
				<li>
					<?php echo bp_core_fetch_avatar( array( 'item_id' => $mod->user_id, 'type' => 'thumb' ) ) ?>
					<h5><?php echo bp_core_get_userlink( $mod->user_id ) ?></h5>
					<span class="activity"><?php echo bp_core_get_last_activity( strtotime( $mod->date_modified ), __( 'joined %s ago', 'bp-events') ); ?></span>

					<?php if ( function_exists( 'friends_install' ) ) : ?>
						<div class="action">
							<?php bp_add_friend_button( $mod->user_id ) ?>
						</div>
					<?php endif; ?>
				</li>
				<?php } ?>
			<?php } ?>
			</ul>
		<?php } else { ?>
			<div id="message" class="info">
				<p><?php _e( 'This event has no moderators', 'bp-events' ); ?></p>
			</div>
		<?php }
}

function bp_event_has_moderators( $event = false ) {
	global $event_mods, $events_template;

	if ( !$event )
		$event =& $events_template->event;

	return apply_filters( 'bp_event_has_moderators', events_get_event_mods( $event->id ) );
}

function bp_event_member_promote_mod_link( $args = '' ) {
	echo bp_get_event_member_promote_mod_link( $args );
}
	function bp_get_event_member_promote_mod_link( $args = '' ) {
		global $members_template, $events_template, $bp;

		$defaults = array(
			'user_id' => $members_template->member->user_id,
			'event' => &$events_template->event
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		return apply_filters( 'bp_get_event_member_promote_mod_link', wp_nonce_url( bp_get_event_permalink( $event ) . '/admin/manage-members/promote/mod/' . $user_id, 'events_promote_member' ) );
	}

function bp_event_member_promote_admin_link( $args = '' ) {
	echo bp_get_event_member_promote_admin_link( $args );
}
	function bp_get_event_member_promote_admin_link( $args = '' ) {
		global $members_template, $events_template, $bp;

		$defaults = array(
			'user_id' => $members_template->member->user_id,
			'event' => &$events_template->event
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		return apply_filters( 'bp_get_event_member_promote_admin_link', wp_nonce_url( bp_get_event_permalink( $event ) . '/admin/manage-members/promote/admin/' . $user_id, 'events_promote_member' ) );
	}

function bp_event_member_demote_link( $user_id = false, $deprecated = false ) {
	global $members_template;

	if ( !$user_id )
		$user_id = $members_template->member->user_id;

	echo bp_get_event_member_demote_link( $user_id );
}
	function bp_get_event_member_demote_link( $user_id = false, $event = false ) {
		global $members_template, $events_template, $bp;

		if ( !$event )
			$event =& $events_template->event;

		if ( !$user_id )
			$user_id = $members_template->member->user_id;

		return apply_filters( 'bp_get_event_member_demote_link', wp_nonce_url( bp_get_event_permalink( $event ) . '/admin/manage-members/demote/' . $user_id, 'events_demote_member' ) );
	}

function bp_event_member_ban_link( $user_id = false, $deprecated = false ) {
	global $members_template;

	if ( !$user_id )
		$user_id = $members_template->member->user_id;

	echo bp_get_event_member_ban_link( $user_id );
}
	function bp_get_event_member_ban_link( $user_id = false, $event = false ) {
		global $members_template, $events_template, $bp;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_member_ban_link', wp_nonce_url( bp_get_event_permalink( $event ) . '/admin/manage-members/ban/' . $user_id, 'events_ban_member' ) );
	}

function bp_event_member_unban_link( $user_id = false, $deprecated = false ) {
	global $members_template;

	if ( !$user_id )
		$user_id = $members_template->member->user_id;

	echo bp_get_event_member_unban_link( $user_id );
}
	function bp_get_event_member_unban_link( $user_id = false, $event = false ) {
		global $members_template;

		if ( !$user_id )
			$user_id = $members_template->member->user_id;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_member_unban_link', wp_nonce_url( bp_get_event_permalink( $event ) . '/admin/manage-members/unban/' . $user_id, 'events_unban_member' ) );
	}

function bp_event_admin_tabs( $event = false ) {
	global $bp, $events_template;

	if ( !$event )
		$event = ( $events_template->event ) ? $events_template->event : $bp->events->current_event;

	$current_tab = $bp->action_variables[0];
?>
	<?php if ( $bp->is_item_admin || $bp->is_item_mod ) { ?>
		<li<?php if ( 'edit-details' == $current_tab || empty( $current_tab ) ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->root_domain . '/' . $bp->events->slug ?>/<?php echo $event->slug ?>/admin/edit-details"><?php _e('Edit Details', 'bp-events') ?></a></li>
	<?php } ?>

	<?php
		if ( !$bp->is_item_admin )
			return false;
	?>
	<li<?php if ( 'event-dates' == $current_tab ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->root_domain . '/' . $bp->events->slug ?>/<?php echo $event->slug ?>/admin/event-dates"><?php _e('Event Dates', 'bp-events') ?></a></li>
	<li<?php if ( 'event-settings' == $current_tab ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->root_domain . '/' . $bp->events->slug ?>/<?php echo $event->slug ?>/admin/event-settings"><?php _e('Event Settings', 'bp-events') ?></a></li>
	<li<?php if ( 'event-avatar' == $current_tab ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->root_domain . '/' . $bp->events->slug ?>/<?php echo $event->slug ?>/admin/event-avatar"><?php _e('Event Avatar', 'bp-events') ?></a></li>
	<li<?php if ( 'manage-members' == $current_tab ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->root_domain . '/' . $bp->events->slug ?>/<?php echo $event->slug ?>/admin/manage-members"><?php _e('Manage Members', 'bp-events') ?></a></li>

	<?php if ( $events_template->event->status == 'private' ) : ?>
		<li<?php if ( 'membership-requests' == $current_tab ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->root_domain . '/' . $bp->events->slug ?>/<?php echo $event->slug ?>/admin/membership-requests"><?php _e('Membership Requests', 'bp-events') ?></a></li>
	<?php endif; ?>

	<?php do_action( 'events_admin_tabs', $current_tab, $event->slug ) ?>

	<li<?php if ( 'delete-event' == $current_tab ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->root_domain . '/' . $bp->events->slug ?>/<?php echo $event->slug ?>/admin/delete-event"><?php _e('Delete Event', 'bp-events') ?></a></li>
<?php
}

function bp_event_total_for_member() {
	echo bp_get_event_total_for_member();
}
	function bp_get_event_total_for_member() {
		return apply_filters( 'bp_get_event_total_for_member', BP_Events_Member::total_event_count() );
	}

function bp_event_form_action( $page, $deprecated = false ) {
	echo bp_get_event_form_action( $page );
}
	function bp_get_event_form_action( $page, $event = false ) {
		global $bp, $events_template;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_event_form_action', bp_get_event_permalink( $event ) . '/' . $page );
	}

function bp_event_admin_form_action( $page = false, $deprecated = false ) {
	echo bp_get_event_admin_form_action( $page );
}
	function bp_get_event_admin_form_action( $page = false, $event = false ) {
		global $bp, $events_template;

		if ( !$event )
			$event =& $events_template->event;

		if ( !$page )
			$page = $bp->action_variables[0];

		return apply_filters( 'bp_event_admin_form_action', bp_get_event_permalink( $event ) . '/admin/' . $page );
	}

function bp_event_has_requested_membership( $event = false ) {
	global $bp, $events_template;

	if ( !$event )
		$event =& $events_template->event;

	if ( events_check_for_membership_request( $bp->loggedin_user->id, $event->id ) )
		return true;

	return false;
}

function bp_event_is_member( $event = false ) {
	global $bp, $events_template;

	if ( !$event )
		$event =& $events_template->event;

	if ( events_is_user_member( $bp->loggedin_user->id, $event->id ) )
		return true;

	return false;
}

function bp_event_accept_invite_link( $deprecated = false ) {
	echo bp_get_event_accept_invite_link();
}
	function bp_get_event_accept_invite_link( $event = false ) {
		global $events_template, $bp;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_accept_invite_link', wp_nonce_url( $bp->loggedin_user->domain . $bp->events->slug . '/invites/accept/' . $event->id, 'events_accept_invite' ) );
	}

function bp_event_reject_invite_link( $deprecated = false ) {
	echo bp_get_event_reject_invite_link();
}
	function bp_get_event_reject_invite_link( $event = false ) {
		global $events_template, $bp;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_reject_invite_link', wp_nonce_url( $bp->loggedin_user->domain . $bp->events->slug . '/invites/reject/' . $event->id, 'events_reject_invite' ) );
	}

function bp_event_leave_confirm_link( $deprecated = false ) {
	echo bp_get_event_leave_confirm_link();
}
	function bp_get_event_leave_confirm_link( $event = false ) {
		global $events_template, $bp;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_event_leave_confirm_link', wp_nonce_url( bp_get_event_permalink( $event ) . '/leave-event/yes', 'events_leave_event' ) );
	}

function bp_event_leave_reject_link( $deprecated = false ) {
	echo bp_get_event_leave_reject_link();
}
	function bp_get_event_leave_reject_link( $event = false ) {
		global $events_template, $bp;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_leave_reject_link', bp_get_event_permalink( $event ) );
	}

function bp_event_send_invite_form_action( $deprecated = false ) {
	echo bp_get_event_send_invite_form_action();
}
	function bp_get_event_send_invite_form_action( $event = false ) {
		global $events_template, $bp;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_event_send_invite_form_action', bp_get_event_permalink( $event ) . '/send-invites/send' );
	}

function bp_events_has_friends_to_invite( $event = false ) {
	global $events_template, $bp;

	if ( !function_exists('friends_install') )
		return false;

	if ( !$event )
		$event =& $events_template->event;

	if ( !friends_check_user_has_friends( $bp->loggedin_user->id ) || !friends_count_invitable_friends( $bp->loggedin_user->id, $event->id ) )
		return false;

	return true;
}

function bp_event_join_button( $event = false ) {
	global $bp, $events_template;

	if ( !$event )
		$event =& $events_template->event;

	// If they're not logged in or are banned from the event, no join button.
	if ( !is_user_logged_in() || events_is_user_banned( $bp->loggedin_user->id, $event->id ) )
		return false;

	if ( !$event->status )
		return false;

	echo '<div class="generic-button event-button ' . $event->status . '" id="eventbutton-' . $event->id . '">';

	switch ( $event->status ) {
		case 'public':
			if ( BP_Events_Member::check_is_member( $bp->loggedin_user->id, $event->id ) )
				echo '<a class="leave-event" href="' . wp_nonce_url( bp_get_event_permalink( $event ) . '/leave-event', 'events_leave_event' ) . '">' . __( 'Leave Event', 'bp-events' ) . '</a>';
			else
				echo '<a class="join-event" href="' . wp_nonce_url( bp_get_event_permalink( $event ) . '/join', 'events_join_event' ) . '">' . __( 'Join Event', 'bp-events' ) . '</a>';
		break;

		case 'private':
			if ( BP_Events_Member::check_is_member( $bp->loggedin_user->id, $event->id ) ) {
				echo '<a class="leave-event" href="' . wp_nonce_url( bp_get_event_permalink( $event ) . '/leave-event', 'events_leave_event' ) . '">' . __( 'Leave Event', 'bp-events' ) . '</a>';
			} else {
				if ( !bp_event_has_requested_membership( $event ) )
					echo '<a class="request-membership" href="' . wp_nonce_url( bp_get_event_permalink( $event ) . '/request-membership', 'events_request_membership' ) . '">' . __('Request Membership', 'bp-events') . '</a>';
				else
					echo '<a class="membership-requested" href="' . bp_get_event_permalink( $event ) . '">' . __( 'Request Sent', 'bp-events' ) . '</a>';
			}
		break;
	}

	echo '</div>';
}

function bp_event_status_message( $event = false ) {
	global $events_template;

	if ( !$event )
		$event =& $events_template->event;

	if ( 'private' == $event->status ) {
		if ( !bp_event_has_requested_membership() )
			if ( is_user_logged_in() )
				_e( 'This is a private event and you must request event membership in order to join.', 'bp-events' );
			else
				_e( 'This is a private event. To join you must be a registered site member and request event membership.', 'bp-events' );
		else
			_e( 'This is a private event. Your membership request is awaiting approval from the event administrator.', 'bp-events' );
	} else {
		_e( 'This is a hidden event and only invited members can join.', 'bp-events' );
	}
}


/***************************************************************************
 * Event Members Template Tags
 **/

class BP_Events_Event_Members_Template {
	var $current_member = -1;
	var $member_count;
	var $members;
	var $member;

	var $in_the_loop;

	var $pag_page;
	var $pag_num;
	var $pag_links;
	var $total_event_count;

	function bp_events_event_members_template( $event_id, $per_page, $max, $exclude_admins_mods, $exclude_banned ) {
		global $bp;

		$this->pag_page = isset( $_REQUEST['mlpage'] ) ? intval( $_REQUEST['mlpage'] ) : 1;
		$this->pag_num = isset( $_REQUEST['num'] ) ? intval( $_REQUEST['num'] ) : $per_page;

		$this->members = BP_Events_Member::get_all_for_event( $event_id, $this->pag_num, $this->pag_page, $exclude_admins_mods, $exclude_banned );

		if ( !$max || $max >= (int)$this->members['count'] )
			$this->total_member_count = (int)$this->members['count'];
		else
			$this->total_member_count = (int)$max;

		$this->members = $this->members['members'];

		if ( $max ) {
			if ( $max >= count($this->members) )
				$this->member_count = count($this->members);
			else
				$this->member_count = (int)$max;
		} else {
			$this->member_count = count($this->members);
		}

		if ( (int) $this->total_event_count && (int) $this->pag_num ) {
			$this->pag_links = paginate_links( array(
				'base' => add_query_arg( 'mlpage', '%#%' ),
				'format' => '',
				'total' => ceil( $this->total_member_count / $this->pag_num ),
				'current' => $this->pag_page,
				'prev_text' => '&laquo;',
				'next_text' => '&raquo;',
				'mid_size' => 1
			));
		}
	}

	function has_members() {
		if ( $this->member_count )
			return true;

		return false;
	}

	function next_member() {
		$this->current_member++;
		$this->member = $this->members[$this->current_member];

		return $this->member;
	}

	function rewind_members() {
		$this->current_member = -1;
		if ( $this->member_count > 0 ) {
			$this->member = $this->members[0];
		}
	}

	function members() {
		if ( $this->current_member + 1 < $this->member_count ) {
			return true;
		} elseif ( $this->current_member + 1 == $this->member_count ) {
			do_action('loop_end');
			// Do some cleaning up after the loop
			$this->rewind_members();
		}

		$this->in_the_loop = false;
		return false;
	}

	function the_member() {
		global $member;

		$this->in_the_loop = true;
		$this->member = $this->next_member();

		if ( 0 == $this->current_member ) // loop has just started
			do_action('loop_start');
	}
}

function bp_event_has_members( $args = '' ) {
	global $bp, $members_template;

	$defaults = array(
		'event_id' => $bp->events->current_event->id,
		'per_page' => 10,
		'max' => false,
		'exclude_admins_mods' => 1,
		'exclude_banned' => 1
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	$members_template = new BP_Events_Event_Members_Template( $event_id, $per_page, $max, (int)$exclude_admins_mods, (int)$exclude_banned );

	return $members_template->has_members();
}

function bp_event_members() {
	global $members_template;

	return $members_template->members();
}

function bp_event_the_member() {
	global $members_template;

	return $members_template->the_member();
}

function bp_event_member_avatar() {
	echo bp_get_event_member_avatar();
}
	function bp_get_event_member_avatar() {
		global $members_template;

		return apply_filters( 'bp_get_event_member_avatar', bp_core_fetch_avatar( array( 'item_id' => $members_template->member->user_id, 'type' => 'full' ) ) );
	}

function bp_event_member_avatar_thumb() {
	echo bp_get_event_member_avatar_thumb();
}
	function bp_get_event_member_avatar_thumb() {
		global $members_template;

		return apply_filters( 'bp_get_event_member_avatar_thumb', bp_core_fetch_avatar( array( 'item_id' => $members_template->member->user_id, 'type' => 'thumb' ) ) );
	}

function bp_event_member_avatar_mini( $width = 30, $height = 30 ) {
	echo bp_get_event_member_avatar_mini( $width, $height );
}
	function bp_get_event_member_avatar_mini( $width = 30, $height = 30 ) {
		global $members_template;

		return apply_filters( 'bp_get_event_member_avatar_mini', bp_core_fetch_avatar( array( 'item_id' => $members_template->member->user_id, 'type' => 'thumb', 'width' => $width, 'height' => $height ) ) );
	}

function bp_event_member_name() {
	echo bp_get_event_member_name();
}
	function bp_get_event_member_name() {
		global $members_template;

		return apply_filters( 'bp_get_event_member_name', bp_core_get_user_displayname( $members_template->member->user_id ) );
	}

function bp_event_member_url() {
	echo bp_get_event_member_url();
}
	function bp_get_event_member_url() {
		global $members_template;

		return apply_filters( 'bp_get_event_member_url', bp_core_get_userlink( $members_template->member->user_id, false, true ) );
	}

function bp_event_member_link() {
	echo bp_get_event_member_link();
}
	function bp_get_event_member_link() {
		global $members_template;

		return apply_filters( 'bp_get_event_member_link', bp_core_get_userlink( $members_template->member->user_id ) );
	}

function bp_event_member_is_banned() {
	echo bp_get_event_member_is_banned();
}
	function bp_get_event_member_is_banned() {
		global $members_template, $events_template;

		return apply_filters( 'bp_get_event_member_is_banned', events_is_user_banned( $members_template->member->user_id, $events_template->event->id ) );
	}

function bp_event_member_joined_since() {
	echo bp_get_event_member_joined_since();
}
	function bp_get_event_member_joined_since() {
		global $members_template;

		return apply_filters( 'bp_get_event_member_joined_since', bp_core_get_last_activity( strtotime( $members_template->member->date_modified ), __( 'joined %s ago', 'bp-events') ) );
	}

function bp_event_member_id() {
	echo bp_get_event_member_id();
}
	function bp_get_event_member_id() {
		global $members_template;

		return apply_filters( 'bp_get_event_member_id', $members_template->member->user_id );
	}

function bp_event_member_needs_pagination() {
	global $members_template;

	if ( $members_template->total_member_count > $members_template->pag_num )
		return true;

	return false;
}

function bp_event_pag_id() {
	echo bp_get_event_pag_id();
}
	function bp_get_event_pag_id() {
		global $bp;

		return apply_filters( 'bp_get_event_pag_id', 'pag' );
	}

function bp_event_member_pagination() {
	echo bp_get_event_member_pagination();
	wp_nonce_field( 'bp_events_member_list', '_member_pag_nonce' );
}
	function bp_get_event_member_pagination() {
		global $members_template;
		return apply_filters( 'bp_get_event_member_pagination', $members_template->pag_links );
	}

function bp_event_member_pagination_count() {
	echo bp_get_event_member_pagination_count();
}
	function bp_get_event_member_pagination_count() {
		global $members_template;

		$from_num = intval( ( $members_template->pag_page - 1 ) * $members_template->pag_num ) + 1;
		$to_num = ( $from_num + ( $members_template->pag_num - 1 ) > $members_template->total_member_count ) ? $members_template->total_member_count : $from_num + ( $members_template->pag_num - 1 );

		return apply_filters( 'bp_get_event_member_pagination_count', sprintf( __( 'Viewing members %d to %d (of %d members)', 'bp-events' ), $from_num, $to_num, $members_template->total_member_count ) );
	}

function bp_event_member_admin_pagination() {
	echo bp_get_event_member_admin_pagination();
	wp_nonce_field( 'bp_events_member_admin_list', '_member_admin_pag_nonce' );
}
	function bp_get_event_member_admin_pagination() {
		global $members_template;

		return $members_template->pag_links;
	}


/***************************************************************************
 * Event Creation Process Template Tags
 **/

function bp_event_creation_tabs() {
	global $bp;

	if ( !is_array( $bp->events->event_creation_steps ) )
		return false;

	if ( !$bp->events->current_create_step )
		$bp->events->current_create_step = array_shift( array_keys( $bp->events->event_creation_steps ) );

	$counter = 1;
	foreach ( $bp->events->event_creation_steps as $slug => $step ) {
		$is_enabled = bp_are_previous_event_creation_steps_complete( $slug ); ?>

		<li<?php if ( $bp->events->current_create_step == $slug ) : ?> class="current"<?php endif; ?>><?php if ( $is_enabled ) : ?><a href="<?php echo $bp->loggedin_user->domain . $bp->events->slug ?>/create/step/<?php echo $slug ?>"><?php endif; ?><?php echo $counter ?>. <?php echo $step['name'] ?><?php if ( $is_enabled ) : ?></a><?php endif; ?></li><?php
		$counter++;
	}

	unset( $is_enabled );

	do_action( 'events_creation_tabs' );
}

function bp_event_creation_stage_title() {
	global $bp;

	echo apply_filters( 'bp_event_creation_stage_title', '<span>&mdash; ' . $bp->events->event_creation_steps[$bp->events->current_create_step]['name'] . '</span>' );
}

function bp_event_creation_form_action() {
	echo bp_get_event_creation_form_action();
}
	function bp_get_event_creation_form_action() {
		global $bp;

		if ( empty( $bp->action_variables[1] ) )
			$bp->action_variables[1] = array_shift( array_keys( $bp->events->event_creation_steps ) );

		return apply_filters( 'bp_get_event_creation_form_action', $bp->loggedin_user->domain . $bp->events->slug . '/create/step/' . $bp->action_variables[1] );
	}

function bp_is_event_creation_step( $step_slug ) {
	global $bp;

	/* Make sure we are in the events component */
	if ( $bp->current_component != BP_EVENTS_SLUG || 'create' != $bp->current_action )
		return false;

	/* If this the first step, we can just accept and return true */
	if ( !$bp->action_variables[1] && array_shift( array_keys( $bp->events->event_creation_steps ) ) == $step_slug )
		return true;

	/* Before allowing a user to see a event creation step we must make sure previous steps are completed */
	if ( !bp_is_first_event_creation_step() ) {
		if ( !bp_are_previous_event_creation_steps_complete( $step_slug ) )
			return false;
	}

	/* Check the current step against the step parameter */
	if ( $bp->action_variables[1] == $step_slug )
		return true;

	return false;
}

function bp_is_event_creation_step_complete( $step_slugs ) {
	global $bp;

	if ( !$bp->events->completed_create_steps )
		return false;

	if ( is_array( $step_slugs ) ) {
		$found = true;

		foreach ( $step_slugs as $step_slug ) {
			if ( !in_array( $step_slug, $bp->events->completed_create_steps ) )
				$found = false;
		}

		return $found;
	} else {
		return in_array( $step_slugs, $bp->events->completed_create_steps );
	}

	return true;
}

function bp_are_previous_event_creation_steps_complete( $step_slug ) {
	global $bp;

	/* If this is the first event creation step, return true */
	if ( array_shift( array_keys( $bp->events->event_creation_steps ) ) == $step_slug )
		return true;

	reset( $bp->events->event_creation_steps );
	unset( $previous_steps );

	/* Get previous steps */
	foreach ( $bp->events->event_creation_steps as $slug => $name ) {
		if ( $slug == $step_slug )
			break;

		$previous_steps[] = $slug;
	}

	return bp_is_event_creation_step_complete( $previous_steps );
}

function bp_new_event_id() {
	echo bp_get_new_event_id();
}
	function bp_get_new_event_id() {
		global $bp;
		return apply_filters( 'bp_get_new_event_id', $bp->events->new_event_id );
	}

function bp_new_event_name() {
	echo bp_get_new_event_name();
}
	function bp_get_new_event_name() {
		global $bp;
		return apply_filters( 'bp_get_new_event_name', $bp->events->current_event->name );
	}

function bp_new_event_description() {
	echo bp_get_new_event_description();
}
	function bp_get_new_event_description() {
		global $bp;
		return apply_filters( 'bp_get_new_event_description', $bp->events->current_event->description );
	}

function bp_new_event_news() {
	echo bp_get_new_event_news();
}
	function bp_get_new_event_news() {
		global $bp;
		return apply_filters( 'bp_get_new_event_news', $bp->events->current_event->news );
	}

function bp_new_event_enable_wire() {
	echo bp_get_new_event_enable_wire();
}
	function bp_get_new_event_enable_wire() {
		global $bp;
		return (int) apply_filters( 'bp_get_new_event_enable_wire', $bp->events->current_event->enable_wire );
	}

function bp_new_event_enable_forum() {
	echo bp_get_new_event_enable_forum();
}
	function bp_get_new_event_enable_forum() {
		global $bp;
		return (int) apply_filters( 'bp_get_new_event_enable_forum', $bp->events->current_event->enable_forum );
	}

function bp_new_event_status() {
	echo bp_get_new_event_status();
}
	function bp_get_new_event_status() {
		global $bp;
		return apply_filters( 'bp_get_new_event_status', $bp->events->current_event->status );
	}

function bp_new_event_avatar( $args = '' ) {
	echo bp_get_new_event_avatar( $args );
}
	function bp_get_new_event_avatar( $args = '' ) {
		global $bp;

		$defaults = array(
			'type' => 'full',
			'width' => false,
			'height' => false,
			'class' => 'avatar',
			'id' => 'avatar-crop-preview',
			'alt' => __( 'Event avatar', 'bp-events' )
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		return apply_filters( 'bp_get_new_event_avatar', bp_core_fetch_avatar( array( 'item_id' => $bp->events->current_event->id, 'object' => 'event', 'type' => $type, 'avatar_dir' => 'event-avatars', 'alt' => $alt, 'width' => $width, 'height' => $height, 'class' => $class ) ) );
	}

function bp_event_creation_previous_link() {
	echo bp_get_event_creation_previous_link();
}
	function bp_get_event_creation_previous_link() {
		global $bp;

		foreach ( $bp->events->event_creation_steps as $slug => $name ) {
			if ( $slug == $bp->action_variables[1] )
				break;

			$previous_steps[] = $slug;
		}

		return apply_filters( 'bp_get_event_creation_previous_link', $bp->loggedin_user->domain . $bp->events->slug . '/create/step/' . array_pop( $previous_steps ) );
	}

function bp_is_last_event_creation_step() {
	global $bp;

	$last_step = array_pop( array_keys( $bp->events->event_creation_steps ) );

	if ( $last_step == $bp->events->current_create_step )
		return true;

	return false;
}

function bp_is_first_event_creation_step() {
	global $bp;

	$first_step = array_shift( array_keys( $bp->events->event_creation_steps ) );

	if ( $first_step == $bp->events->current_create_step )
		return true;

	return false;
}

function bp_new_event_invite_friend_list() {
	echo bp_get_new_event_invite_friend_list();
}
	function bp_get_new_event_invite_friend_list( $args = '' ) {
		global $bp;

		if ( !function_exists('friends_install') )
			return false;

		$defaults = array(
			'event_id' => false,
			'separator' => 'li'
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		if ( !$event_id )
			$event_id = ( $bp->events->new_event_id ) ? $bp->events->new_event_id : $bp->events->current_event->id;

		$friends = friends_get_friends_invite_list( $bp->loggedin_user->id, $event_id );

		if ( $friends ) {
			$invites = events_get_invites_for_event( $bp->loggedin_user->id, $event_id );

			for ( $i = 0; $i < count( $friends ); $i++ ) {
				if ( $invites ) {
					if ( in_array( $friends[$i]['id'], $invites ) ) {
						$checked = ' checked="checked"';
					} else {
						$checked = '';
					}
				}

				$items[] = '<' . $separator . '><input' . $checked . ' type="checkbox" name="friends[]" id="f-' . $friends[$i]['id'] . '" value="' . attribute_escape( $friends[$i]['id'] ) . '" /> ' . $friends[$i]['full_name'] . '</' . $separator . '>';
			}
		}

		return implode( "\n", (array)$items );
	}

/********************************************************************************
 * Site Events Template Tags
 **/

class BP_Events_Site_Events_Template {
	var $current_event = -1;
	var $event_count;
	var $events;
	var $event;

	var $in_the_loop;

	var $pag_page;
	var $pag_num;
	var $pag_links;
	var $total_event_count;

	function bp_events_site_events_template( $type, $per_page, $max ) {
		global $bp;

		/* TODO: Move $_REQUEST vars out of here */

		$this->pag_page = isset( $_REQUEST['gpage'] ) ? intval( $_REQUEST['gpage'] ) : 1;
		$this->pag_num = isset( $_REQUEST['num'] ) ? intval( $_REQUEST['num'] ) : $per_page;

		if ( isset( $_REQUEST['s'] ) && '' != $_REQUEST['s'] && $type != 'random' ) {
			$this->events = BP_Events_Event::search_events( $_REQUEST['s'], $this->pag_num, $this->pag_page );
		} else if ( isset( $_REQUEST['letter'] ) && '' != $_REQUEST['letter'] ) {
			$this->events = BP_Events_Event::get_by_letter( $_REQUEST['letter'], $this->pag_num, $this->pag_page );

		} else {
			switch ( $type ) {
				case 'upcoming': default:
					$this->events = events_get_upcoming( $this->pag_num, $this->pag_page );
					break;

				case 'active':
					$this->events = events_get_active( $this->pag_num, $this->pag_page );
					break;

				case 'alphabetical': default:
					$this->events = events_get_alphabetically( $this->pag_num, $this->pag_page );
					break;

				case 'random':
					$this->events = events_get_random_events( $this->pag_num, $this->pag_page );
					break;

				case 'newest':
					$this->events = events_get_newest( $this->pag_num, $this->pag_page );
					break;

				case 'popular':
					$this->events = events_get_popular( $this->pag_num, $this->pag_page );
					break;

				case 'most-forum-topics':
					$this->events = events_get_by_most_forum_topics( $this->pag_num, $this->pag_page );
					break;

				case 'most-forum-posts':
					$this->events = events_get_by_most_forum_posts( $this->pag_num, $this->pag_page );
					break;
			}
		}

		if ( !$max || $max >= (int)$this->events['total'] )
			$this->total_event_count = (int)$this->events['total'];
		else
			$this->total_event_count = (int)$max;

		$this->events = $this->events['events'];

		if ( $max ) {
			if ( $max >= count($this->events) )
				$this->event_count = count($this->events);
			else
				$this->event_count = (int)$max;
		} else {
			$this->event_count = count($this->events);
		}

		$this->pag_links = paginate_links( array(
			'base' => add_query_arg( 'gpage', '%#%' ),
			'format' => '',
			'total' => ceil( (int) $this->total_event_count / (int) $this->pag_num ),
			'current' => (int) $this->pag_page,
			'prev_text' => '&laquo;',
			'next_text' => '&raquo;',
			'mid_size' => 1
		));
	}

	function has_events() {
		if ( $this->event_count )
			return true;

		return false;
	}

	function next_event() {
		$this->current_event++;
		$this->event = $this->events[$this->current_event];

		return $this->event;
	}

	function rewind_events() {
		$this->current_event = -1;
		if ( $this->event_count > 0 ) {
			$this->event = $this->events[0];
		}
	}

	function events() {
		if ( $this->current_event + 1 < $this->event_count ) {
			return true;
		} elseif ( $this->current_event + 1 == $this->event_count ) {
			do_action('loop_end');
			// Do some cleaning up after the loop
			$this->rewind_events();
		}

		$this->in_the_loop = false;
		return false;
	}

	function the_event() {
		global $event;

		$this->in_the_loop = true;
		$this->event = $this->next_event();

		if ( !$event = wp_cache_get( 'events_event_nouserdata_' . $this->event->event_id, 'bp' ) ) {
			$event = new BP_Events_Event( $this->event->event_id, false, false );
			wp_cache_set( 'events_event_nouserdata_' . $this->event->event_id, $event, 'bp' );
		}

		$this->event = $event;

		if ( 0 == $this->current_event ) // loop has just started
			do_action('loop_start');
	}
}

function bp_rewind_site_events() {
	global $site_events_template;

	$site_events_template->rewind_events();
}

function bp_has_site_events( $args = '' ) {
	global $site_events_template;

	$defaults = array(
		'type' => 'upcoming',
		'per_page' => 10,
		'max' => false
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	// type: upcoming ( default ) | active | random | newest | popular

	if ( $max ) {
		if ( $per_page > $max )
			$per_page = $max;
	}

	$site_events_template = new BP_Events_Site_Events_Template( $type, $per_page, $max );

	return $site_events_template->has_events();
}

function bp_site_events() {
	global $site_events_template;

	return $site_events_template->events();
}

function bp_the_site_event() {
	global $site_events_template;

	return $site_events_template->the_event();
}

function bp_site_events_pagination_count() {
	global $bp, $site_events_template;

	$from_num = intval( ( $site_events_template->pag_page - 1 ) * $site_events_template->pag_num ) + 1;
	$to_num = ( $from_num + ( $site_events_template->pag_num - 1 ) > $site_events_template->total_event_count ) ? $site_events_template->total_event_count : $from_num + ( $site_events_template->pag_num - 1) ;

	echo sprintf( __( 'Viewing event %d to %d (of %d events)', 'bp-events' ), $from_num, $to_num, $site_events_template->total_event_count ); ?> &nbsp;
	<span class="ajax-loader"></span><?php
}

function bp_site_events_pagination_links() {
	echo bp_get_site_events_pagination_links();
}
	function bp_get_site_events_pagination_links() {
		global $site_events_template;

		return apply_filters( 'bp_get_site_events_pagination_links', $site_events_template->pag_links );
	}

function bp_the_site_event_id() {
	echo bp_get_the_site_event_id();
}
	function bp_get_the_site_event_id() {
		global $site_events_template;

		return apply_filters( 'bp_get_the_site_event_id', $site_events_template->event->id );
	}

function bp_the_site_event_avatar() {
	echo bp_get_the_site_event_avatar();
}
	function bp_get_the_site_event_avatar() {
		global $site_events_template;

		return apply_filters( 'bp_get_the_site_event_avatar', bp_core_fetch_avatar( array( 'item_id' => $site_events_template->event->id, 'object' => 'event', 'type' => 'full', 'avatar_dir' => 'event-avatars', 'alt' => __( 'Event Avatar', 'bp-events' ) ) ) );
	}

function bp_the_site_event_avatar_thumb() {
	echo bp_get_the_site_event_avatar_thumb();
}
	function bp_get_the_site_event_avatar_thumb() {
		global $site_events_template;

		return apply_filters( 'bp_get_the_site_event_avatar_thumb', bp_core_fetch_avatar( array( 'item_id' => $site_events_template->event->id, 'object' => 'event', 'type' => 'thumb', 'avatar_dir' => 'event-avatars', 'alt' => __( 'Event Avatar', 'bp-events' ) ) ) );
	}

function bp_the_site_event_avatar_mini() {
	echo bp_get_the_site_event_avatar_mini();
}
	function bp_get_the_site_event_avatar_mini() {
		global $site_events_template;

		return apply_filters( 'bp_get_the_site_event_avatar_mini', bp_core_fetch_avatar( array( 'item_id' => $site_events_template->event->id, 'object' => 'event', 'type' => 'thumb', 'width' => 30, 'height' => 30, 'avatar_dir' => 'event-avatars', 'alt' => __( 'Event Avatar', 'bp-events' ) ) ) );
	}

function bp_the_site_event_link() {
	echo bp_get_the_site_event_link();
}
	function bp_get_the_site_event_link() {
		global $site_events_template;

		return apply_filters( 'bp_get_the_site_event_link', bp_get_event_permalink( $site_events_template->event ) );
	}

function bp_the_site_event_name() {
	echo bp_get_the_site_event_name();
}
	function bp_get_the_site_event_name() {
		global $site_events_template;

		return apply_filters( 'bp_get_the_site_event_name', bp_get_event_name( $site_events_template->event ) );
	}

function bp_the_site_event_last_active() {
	echo bp_get_the_site_event_last_active();
}
	function bp_get_the_site_event_last_active() {
		global $site_events_template;

		return apply_filters( 'bp_get_the_site_event_last_active', sprintf( __( 'active %s ago', 'bp-events' ), bp_get_event_last_active( $site_events_template->event ) ) );
	}

function bp_the_site_event_upcoming() {
	echo bp_get_the_site_event_upcoming();
}
	function bp_get_the_site_event_upcoming() {
		global $site_events_template;

		return apply_filters( 'bp_get_the_site_event_upcoming', bp_get_event_upcoming( $site_events_template->event ) );
	}

function bp_the_site_event_join_button() {
	global $site_events_template;

	echo bp_event_join_button( $site_events_template->event );
}

function bp_the_site_event_description() {
	echo bp_get_the_site_event_description();
}
	function bp_get_the_site_event_description() {
		global $site_events_template;

		return apply_filters( 'bp_get_the_site_event_description', bp_get_event_description( $site_events_template->event ) );
	}

function bp_the_site_event_description_excerpt() {
	echo bp_get_the_site_event_description_excerpt();
}
	function bp_get_the_site_event_description_excerpt() {
		global $site_events_template;

		return apply_filters( 'bp_get_the_site_event_description_excerpt', bp_create_excerpt( bp_get_event_description( $site_events_template->event, false ), 25 ) );
	}

function bp_the_site_event_date_created() {
	echo bp_get_the_site_event_date_created();
}
	function bp_get_the_site_event_date_created() {
		global $site_events_template;

		return apply_filters( 'bp_get_the_site_event_date_created', bp_core_time_since( $site_events_template->event->date_created ) );
	}

function bp_the_site_event_member_count() {
	echo bp_get_the_site_event_member_count();
}
	function bp_get_the_site_event_member_count() {
		global $site_events_template;

		if ( 1 == (int) $site_events_template->event->total_member_count )
			return apply_filters( 'bp_get_the_site_event_member_count', sprintf( __( '%d member', 'bp-events' ), (int) $site_events_template->event->total_member_count ) );
		else
			return apply_filters( 'bp_get_the_site_event_member_count', sprintf( __( '%d members', 'bp-events' ), (int) $site_events_template->event->total_member_count ) );
	}

function bp_the_site_event_type() {
	echo bp_get_the_site_event_type();
}
	function bp_get_the_site_event_type() {
		global $site_events_template;

		return apply_filters( 'bp_get_the_site_event_type', bp_get_event_type( $site_events_template->event ) );
	}

function bp_the_site_event_forum_topic_count( $args = '' ) {
	echo bp_get_the_site_event_forum_topic_count( $args );
}
	function bp_get_the_site_event_forum_topic_count( $args = '' ) {
		global $site_events_template;

		$defaults = array(
			'showtext' => false
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		if ( !$forum_id = events_get_eventmeta( $site_events_template->event->id, 'forum_id' ) )
			return false;

		if ( !function_exists( 'bp_forums_get_forum_topicpost_count' ) )
			return false;

		if ( !$site_events_template->event->forum_counts )
			$site_events_template->event->forum_counts = bp_forums_get_forum_topicpost_count( (int)$forum_id );

		if ( (bool) $showtext ) {
			if ( 1 == (int) $site_events_template->event->forum_counts[0]->topics )
				$total_topics = sprintf( __( '%d topic', 'bp-events' ), (int) $site_events_template->event->forum_counts[0]->topics );
			else
				$total_topics = sprintf( __( '%d topics', 'bp-events' ), (int) $site_events_template->event->forum_counts[0]->topics );
		} else {
			$total_topics = (int) $site_events_template->event->forum_counts[0]->topics;
		}

		return apply_filters( 'bp_get_the_site_event_forum_topic_count', $total_topics, (bool)$showtext );
	}

function bp_the_site_event_forum_post_count( $args = '' ) {
	echo bp_get_the_site_event_forum_post_count( $args );
}
	function bp_get_the_site_event_forum_post_count( $args = '' ) {
		global $site_events_template;

		$defaults = array(
			'showtext' => false
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		if ( !$forum_id = events_get_eventmeta( $site_events_template->event->id, 'forum_id' ) )
			return false;

		if ( !function_exists( 'bp_forums_get_forum_topicpost_count' ) )
			return false;

		if ( !$site_events_template->event->forum_counts )
			$site_events_template->event->forum_counts = bp_forums_get_forum_topicpost_count( (int)$forum_id );

		if ( (bool) $showtext ) {
			if ( 1 == (int) $site_events_template->event->forum_counts[0]->posts )
				$total_posts = sprintf( __( '%d post', 'bp-events' ), (int) $site_events_template->event->forum_counts[0]->posts );
			else
				$total_posts = sprintf( __( '%d posts', 'bp-events' ), (int) $site_events_template->event->forum_counts[0]->posts );
		} else {
			$total_posts = (int) $site_events_template->event->forum_counts[0]->posts;
		}

		return apply_filters( 'bp_get_the_site_event_forum_post_count', $total_posts, (bool)$showtext );
	}

function bp_the_site_event_hidden_fields() {
	if ( isset( $_REQUEST['s'] ) ) {
		echo '<input type="hidden" id="search_terms" value="' . attribute_escape( $_REQUEST['s'] ) . '" name="search_terms" />';
	}

	if ( isset( $_REQUEST['letter'] ) ) {
		echo '<input type="hidden" id="selected_letter" value="' . attribute_escape( $_REQUEST['letter'] ) . '" name="selected_letter" />';
	}

	if ( isset( $_REQUEST['events_search'] ) ) {
		echo '<input type="hidden" id="search_terms" value="' . attribute_escape( $_REQUEST['events_search'] ) . '" name="search_terms" />';
	}
}

function bp_directory_events_search_form() {
	global $bp; ?>
	<form action="" method="get" id="search-events-form">
		<label><input type="text" name="s" id="events_search" value="<?php if ( isset( $_GET['s'] ) ) { echo attribute_escape( $_GET['s'] ); } else { _e( 'Search anything...', 'bp-events' ); } ?>"  onfocus="if (this.value == '<?php _e( 'Search anything...', 'bp-events' ) ?>') {this.value = '';}" onblur="if (this.value == '') {this.value = '<?php _e( 'Search anything...', 'bp-events' ) ?>';}" /></label>
		<input type="submit" id="events_search_submit" name="events_search_submit" value="<?php _e( 'Search', 'bp-events' ) ?>" />
	</form>
<?php
}

/************************************************************************************
 * Membership Requests Template Tags
 **/

class BP_Events_Membership_Requests_Template {
	var $current_request = -1;
	var $request_count;
	var $requests;
	var $request;

	var $in_the_loop;

	var $pag_page;
	var $pag_num;
	var $pag_links;
	var $total_request_count;

	function bp_events_membership_requests_template( $event_id, $per_page, $max ) {
		global $bp;

		$this->pag_page = isset( $_REQUEST['mrpage'] ) ? intval( $_REQUEST['mrpage'] ) : 1;
		$this->pag_num = isset( $_REQUEST['num'] ) ? intval( $_REQUEST['num'] ) : $per_page;

		$this->requests = BP_Events_Event::get_membership_requests( $event_id, $this->pag_num, $this->pag_page );

		if ( !$max || $max >= (int)$this->requests['total'] )
			$this->total_request_count = (int)$this->requests['total'];
		else
			$this->total_request_count = (int)$max;

		$this->requests = $this->requests['requests'];

		if ( $max ) {
			if ( $max >= count($this->requests) )
				$this->request_count = count($this->requests);
			else
				$this->request_count = (int)$max;
		} else {
			$this->request_count = count($this->requests);
		}

		$this->pag_links = paginate_links( array(
			'base' => add_query_arg( 'mrpage', '%#%' ),
			'format' => '',
			'total' => ceil( $this->total_request_count / $this->pag_num ),
			'current' => $this->pag_page,
			'prev_text' => '&laquo;',
			'next_text' => '&raquo;',
			'mid_size' => 1
		));
	}

	function has_requests() {
		if ( $this->request_count )
			return true;

		return false;
	}

	function next_request() {
		$this->current_request++;
		$this->request = $this->requests[$this->current_request];

		return $this->request;
	}

	function rewind_requests() {
		$this->current_request = -1;
		if ( $this->request_count > 0 ) {
			$this->request = $this->requests[0];
		}
	}

	function requests() {
		if ( $this->current_request + 1 < $this->request_count ) {
			return true;
		} elseif ( $this->current_request + 1 == $this->request_count ) {
			do_action('loop_end');
			// Do some cleaning up after the loop
			$this->rewind_requests();
		}

		$this->in_the_loop = false;
		return false;
	}

	function the_request() {
		global $request;

		$this->in_the_loop = true;
		$this->request = $this->next_request();

		if ( 0 == $this->current_request ) // loop has just started
			do_action('loop_start');
	}
}

function bp_event_has_membership_requests( $args = '' ) {
	global $requests_template, $events_template;

	$defaults = array(
		'event_id' => $events_template->event->id,
		'per_page' => 10,
		'max' => false
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	$requests_template = new BP_Events_Membership_Requests_Template( $event_id, $per_page, $max );
	return $requests_template->has_requests();
}

function bp_event_membership_requests() {
	global $requests_template;

	return $requests_template->requests();
}

function bp_event_the_membership_request() {
	global $requests_template;

	return $requests_template->the_request();
}

function bp_event_request_user_avatar_thumb() {
	global $requests_template;

	echo apply_filters( 'bp_event_request_user_avatar_thumb', bp_core_fetch_avatar( array( 'item_id' => $requests_template->request->user_id, 'type' => 'thumb' ) ) );
}

function bp_event_request_reject_link() {
	global $requests_template, $events_template;

	echo apply_filters( 'bp_event_request_reject_link', wp_nonce_url( bp_get_event_permalink( $events_template->event ) . '/admin/membership-requests/reject/' . $requests_template->request->id, 'events_reject_membership_request' ) );
}

function bp_event_request_accept_link() {
	global $requests_template, $events_template;

	echo apply_filters( 'bp_event_request_accept_link', wp_nonce_url( bp_get_event_permalink( $events_template->event ) . '/admin/membership-requests/accept/' . $requests_template->request->id, 'events_accept_membership_request' ) );
}

function bp_event_request_time_since_requested() {
	global $requests_template;

	echo apply_filters( 'bp_event_request_time_since_requested', sprintf( __( 'requested %s ago', 'bp-events' ), bp_core_time_since( strtotime( $requests_template->request->date_modified ) ) ) );
}

function bp_event_request_comment() {
	global $requests_template;

	echo apply_filters( 'bp_event_request_comment', strip_tags( stripslashes( $requests_template->request->comments ) ) );
}

function bp_event_request_user_link() {
	global $requests_template;

	echo apply_filters( 'bp_event_request_user_link', bp_core_get_userlink( $requests_template->request->user_id ) );
}


/************************************************************************************
 * Invite Friends Template Tags
 **/

class BP_Events_Invite_Template {
	var $current_invite = -1;
	var $invite_count;
	var $invites;
	var $invite;

	var $in_the_loop;

	var $pag_page;
	var $pag_num;
	var $pag_links;
	var $total_invite_count;

	function bp_events_invite_template( $user_id, $event_id ) {
		global $bp;

		$this->invites = events_get_invites_for_event( $user_id, $event_id );
		$this->invite_count = count( $this->invites );
	}

	function has_invites() {
		if ( $this->invite_count )
			return true;

		return false;
	}

	function next_invite() {
		$this->current_invite++;
		$this->invite = $this->invites[$this->current_invite];

		return $this->invite;
	}

	function rewind_invites() {
		$this->current_invite = -1;
		if ( $this->invite_count > 0 ) {
			$this->invite = $this->invites[0];
		}
	}

	function invites() {
		if ( $this->current_invite + 1 < $this->invite_count ) {
			return true;
		} elseif ( $this->current_invite + 1 == $this->invite_count ) {
			do_action('loop_end');
			// Do some cleaning up after the loop
			$this->rewind_invites();
		}

		$this->in_the_loop = false;
		return false;
	}

	function the_invite() {
		global $invite;

		$this->in_the_loop = true;
		$user_id = $this->next_invite();

		$this->invite = new stdClass;
		$this->invite->user = new BP_Core_User( $user_id );
		$this->invite->event_id = $event_id; // Globaled in bp_event_has_invites()

		if ( 0 == $this->current_invite ) // loop has just started
			do_action('loop_start');
	}
}

function bp_event_has_invites( $args = '' ) {
	global $bp, $invites_template, $event_id;

	$defaults = array(
		'event_id' => false,
		'user_id' => $bp->loggedin_user->id
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	if ( !$event_id ) {
		/* Backwards compatibility */
		if ( $bp->events->current_event ) $event_id = $bp->events->current_event->id;
		if ( $bp->events->new_event_id ) $event_id = $bp->events->new_event_id;
	}

	if ( !$event_id )
		return false;

	$invites_template = new BP_Events_Invite_Template( $user_id, $event_id );
	return $invites_template->has_invites();
}

function bp_event_invites() {
	global $invites_template;

	return $invites_template->invites();
}

function bp_event_the_invite() {
	global $invites_template;

	return $invites_template->the_invite();
}

function bp_event_invite_item_id() {
	echo bp_get_event_invite_item_id();
}
	function bp_get_event_invite_item_id() {
		global $invites_template;

		return apply_filters( 'bp_get_event_invite_item_id', 'uid-' . $invites_template->invite->user->id );
	}

function bp_event_invite_user_avatar() {
	echo bp_get_event_invite_user_avatar();
}
	function bp_get_event_invite_user_avatar() {
		global $invites_template;

		return apply_filters( 'bp_get_event_invite_user_avatar', $invites_template->invite->user->avatar_thumb );
	}

function bp_event_invite_user_link() {
	echo bp_get_event_invite_user_link();
}
	function bp_get_event_invite_user_link() {
		global $invites_template;

		return apply_filters( 'bp_get_event_invite_user_link', bp_core_get_userlink( $invites_template->invite->user->id ) );
	}

function bp_event_invite_user_last_active() {
	echo bp_get_event_invite_user_last_active();
}
	function bp_get_event_invite_user_last_active() {
		global $invites_template;

		return apply_filters( 'bp_get_event_invite_user_last_active', $invites_template->invite->user->last_active );
	}

function bp_event_invite_user_remove_invite_url() {
	echo bp_get_event_invite_user_remove_invite_url();
}
	function bp_get_event_invite_user_remove_invite_url() {
		global $invites_template;

		return wp_nonce_url( site_url( BP_EVENTS_SLUG . '/' . $invites_template->invite->event_id . '/invites/remove/' . $invites_template->invite->user->id ), 'events_invite_uninvite_user' );
	}

/**
 * Here we start defining our own function that are not part of the core component structure.
 *
 * This makes for easier upgrading when the groups component upgrades and the changes we added.
 *
 * Or so we hope. Let the games begin...
 */

function bp_event_has_tagline( $event = false ) {
	global $events_template;

	if ( !$event )
		$event =& $events_template->event;

	if ( empty( $event->tagline ) )
		return false;

	return true;
}

function bp_event_tagline( $deprecated = false ) {
	echo bp_get_event_tagline();
}
	function bp_get_event_tagline( $event = false ) {
		global $events_template;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_tagline', $events_template->event->tagline );
	}

function bp_event_tagline_editable( $deprecated = false ) {
	echo bp_get_event_tagline_editable();
}
	function bp_get_event_tagline_editable( $event = false ) {
		global $events_template;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_tagline_editable', $event->tagline );
	}

function bp_new_event_tagline() {
	echo bp_get_new_event_tagline();
}
	function bp_get_new_event_tagline() {
		global $bp;
		return apply_filters( 'bp_get_new_event_tagline', $bp->events->current_event->tagline );
	}

function bp_event_has_location( $event = false ) {
	global $events_template;

	if ( !$event )
		$event =& $events_template->event;

	if ( empty( $event->location ) )
		return false;

	return true;
}

function bp_event_location( $deprecated = false ) {
	echo bp_get_event_location();
}
	function bp_get_event_location( $event = false ) {
		global $events_template;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_location', $events_template->event->location );
	}

function bp_event_location_editable( $deprecated = false ) {
	echo bp_get_event_location_editable();
}
	function bp_get_event_location_editable( $event = false ) {
		global $events_template;

		if ( !$event )
			$event =& $events_template->event;

		return apply_filters( 'bp_get_event_location_editable', $event->location );
	}

function bp_new_event_location() {
	echo bp_get_new_event_location();
}
	function bp_get_new_event_location() {
		global $bp;
		return apply_filters( 'bp_get_new_event_location', $bp->events->current_event->location );
	}

/**
 * Forums support for Events
 *
 */
function bp_event_forum() {
 echo bp_get_event_forum();
}
	function bp_get_event_forum() {
		global $bp;

		if ( !$forum_id && $bp->current_component == $bp->events->slug )
			$forum_id = events_get_eventmeta( $bp->events->current_event->id, 'forum_id' );

		return apply_filters( 'bp_get_event_forum', $forum_id );
	}

function bp_event_forum_topic() {
 echo bp_get_event_forum_topic();
}
	function bp_get_event_forum_topic() {
		global $bp;

		if ( !$topic_id && $bp->current_component == $bp->events->slug && 'forum' == $bp->current_action && 'topic' == $bp->action_variables[0] )
			$topic_id = bp_forums_get_topic_id_from_slug( $bp->action_variables[1] );

		return apply_filters( 'bp_get_event_forum_topic', $topic_id );
	}

/**
 * Group Events
 *
 */
function bp_event_get_group( $event = false ) {
	global $events_template;

 	if ( !$event)
		$event =& $events_template->event;

	return apply_filters( 'bp_event_get_group', $event->link_group );
}

function bp_event_groups_dropdown( $grp_id ) {
	global $bp;

  if (!$user_id)
  	$user_id = $bp->loggedin_user->id;

  $groups = groups_get_user_is_admin_of( $user_id );

  if ( $groups['groups'] ) {

		echo '<label for="event-group">' . __('Make this a group event for one of your groups', 'bp-events') . '</label>';

		echo '<select id="event-group" name="event-group">';
	  	echo '<option value="0"></option>';

		foreach ($groups['groups'] as $group) {
			$grp = new BP_Groups_Group($group->group_id);

	   		echo "<option value='".$grp->id."'";
	    	if ($grp_id == $grp->id)
	       		echo " selected";
	    	echo ">".$grp->name."</option>";
	  	}

	  echo '</select>';
  }
}

function bp_event_group_avatar() {
	echo bp_get_event_group_avatar();
}
	function bp_get_event_group_avatar( $args = '' ) {
		$group = new BP_Groups_Group( bp_event_get_group( $event ) );

		return apply_filters( 'bp_get_event_avatar', bp_core_fetch_avatar( array( 'item_id' => $group->id, 'object' => 'group', 'type' => 'full', 'avatar_dir' => 'group-avatars', 'alt' => attribute_escape($group->name), 'class' => 'avatar' ) ) );
	}

function bp_event_group_avatar_thumb() {
	echo bp_get_event_group_avatar_thumb();
}
	function bp_get_event_group_avatar_thumb( $args = '' ) {
		$group = new BP_Groups_Group( bp_event_get_group( $event ) );

		return apply_filters( 'bp_get_event_avatar_thumb', bp_core_fetch_avatar( array( 'item_id' => $group->id, 'object' => 'group', 'type' => 'thumb', 'avatar_dir' => 'group-avatars', 'alt' => attribute_escape($group->name), 'class' => 'avatar' ) ) );
	}

function bp_event_group_name() {
	echo bp_get_event_group_name();
}
	function bp_get_event_group_name() {
		$group = new BP_Groups_Group( bp_event_get_group( $event ) );

		return apply_filters( 'bp_get_event_group_name', $group->name );
	}

function bp_event_group_permalink() {
	echo bp_get_event_group_permalink();
}
	function bp_get_event_group_permalink() {
	  global $bp;

		$group = new BP_Groups_Group( bp_event_get_group( $event ) );

		return apply_filters( 'bp_get_group_permalink', $bp->root_domain . '/' . $bp->groups->slug . '/' . $group->slug );
	}

// Recomended: Needs a template loop so a group events loop can be themed.
function bp_events_group_events() {
  global $groups_template, $bp;

  $events = BP_Events_Event::get_events_for_group( bp_group_id(false) );

  if ( $events['events'] )  { ?>

      <ul id="event-list" class="item-list">
        <?php foreach ( $events['events'] as $event ) {

			  $event = new BP_Events_Event( $event->event_id, false, false );

			  $avatar = bp_core_fetch_avatar( array( 'item_id' => $event->id, 'object' => 'event', 'type' => 'thumb', 'avatar_dir' => 'event-avatars', 'alt' => attribute_escape($event->name), 'class' => 'avatar' ) ); ?>

				<li>
					<?php echo $avatar; ?>
					<h5><a href="<?php echo $bp->root_domain . '/' . $bp->events->slug . '/' . $event->slug; ?>"><?php echo $event->name; ?></a><span class="small"> - <?php echo $event->tagline ?></span></h5>
					<div class="event-meta">
						<p class="desc">
						  <?php bp_event_date( $event, false ) ?>
						</p>
					  <p class="desc">
					    <?php printf( __( '%s Invites', 'bp-events' ), $event->total_member_count ); ?> &middot; <?php bp_event_date_togo( $event, false ); ?>
					  </p>
	        </div>
				</li>

        <?php
    		}
    		?>
    	</ul>
	<?php
  }
}

/**
 * Event Dates
 *
 */
function bp_new_event_allday() {
	echo bp_get_new_event_allday();
}
	function bp_get_new_event_allday() {
		global $bp;
		return (int) apply_filters( 'bp_get_new_event_allday', $bp->events->current_event->is_allday );
	}

function bp_event_startdate( $event = false ) {
	global $events_template;
  	$comp = get_option('date_format');

	if ( !$event)
		$event = & $events_template->event;

	echo apply_filters( 'bp_event_startdate', Date($comp, $event->date_start) );
}

function bp_event_enddate( $event = false ) {
	global $events_template;
    $comp = get_option('date_format');

	if ( !$event)
		$event = & $events_template->event;

	echo apply_filters( 'bp_event_enddate', Date($comp, $event->date_end ) );
}

function bp_event_get_startdate( $event = false ) {
	global $events_template;

	if ( !$event)
		$event = & $events_template->event;

	return apply_filters( 'bp_event_get_startdate', $event->date_start );
}

function bp_event_get_enddate( $event = false ) {
	global $events_template;

	if ( !$event)
		$event = & $events_template->event;

	return apply_filters( 'bp_event_get_enddate', $event->date_end );
}

function bp_event_starttime( $event = false ) {
	global $events_template;
    $comp = get_option('time_format');

	if ( !$event)
		$event = & $events_template->event;

	echo apply_filters( 'bp_event_startdate', Date($comp, $event->date_start) );
}

function bp_event_endtime( $event = false ) {
	global $events_template;

	if ( !$event)
		$event = & $events_template->event;

    $comp = get_option('time_format');

	echo apply_filters( 'bp_event_enddate', Date($comp, $event->date_end ) );
}

function bp_event_starthour( $event = false ) {
	global $events_template;

	if ( !$event)
		$event = & $events_template->event;

	echo apply_filters( 'bp_event_startdate', Date('h', $event->date_start ) );
}

function bp_event_endhour( $event = false ) {
	global $events_template;

	if ( !$event)
		$event = & $events_template->event;

	echo apply_filters( 'bp_event_enddate', Date('h', $event->date_end ) );
}

function bp_event_startminute( $event = false ) {
	global $events_template;

	if ( !$event)
		$event = & $events_template->event;

	echo apply_filters( 'bp_event_startdate', Date('i', $event->date_start ) );
}

function bp_event_endminute( $event = false ) {
	global $events_template;

	if ( !$event)
		$event = & $events_template->event;

	echo apply_filters( 'bp_event_enddate', Date('i', $event->date_end ) );
}

function bp_event_is_allday( $event = false ) {
	global $events_template;

	if ( !$event)
		$event = & $events_template->event;

	return apply_filters( 'bp_event_is_allday', $event->is_allday );
}

function bp_event_date_togo( $event = null, $echo = true ) {
	global $events_template;

  if (!$event) {
    $ds = $events_template->event->date_start;
    $de = $events_template->event->date_end;
  } else {
    $ds = $event->date_start;
  	$de = $event->date_end;
  }

  if ($ds <= time()) {
  	if ($de>time()) {
  		$out.= __('Event is in progress','bp-events');
    } else {
    	$out.= __('Event has ended','bp-events');
    }
  } else {
    $out.= __('Event starts in ','bp-events').timetogo($ds);
  }
  if ($echo)
  	echo apply_filters( 'bp_event_date_togo', $out );
  else
  	return  apply_filters( 'bp_event_date_togo', $out );
}

function bp_event_date( $event = null ) {
	global $events_template;

  if ( !$event ) {
    $ds = $events_template->event->date_start;
    $de = $events_template->event->date_end;
    $du = $events_template->event->is_allday;
  } else {
    $ds = $event->date_start;
    $de = $event->date_end;
    $du = $event->is_allday;
  }

  $out = '';
  $tf = get_option( 'time_format' );

  if ( $du ) {
    // All day event, no times set
    $out .= '<span class="day">' . Date( 'D', $ds ). '</span>';
    $out .= ', ';
    $out .= '<span class="month">' . Date( 'M', $ds ). '</span>';
    $out .= ' ';
    $out .= '<span class="day-digit">' . Date( 'j', $ds ). '</span>';
    if ( Date( 'Y', $ds ) != Date( 'Y' ) ) {
      $out .= ', ';
    	$out .= '<span class="year">' . Date( 'Y', $ds ). '</span>';
    }
    $out .= ' - ';
    $out .= __( 'all day event', 'bp-events' );
  }
  else {
    if ( Date( 'M j Y', $ds ) == Date( 'M j Y', $de ) ) {
      // Same day event with set times
      $out .= '<span class="day">' . Date( 'D', $ds ). '</span>';
      $out .= ', ';
      $out .= '<span class="month">' . Date( 'M', $ds ). '</span>';
      $out .= ' ';
      $out .= '<span class="day-digit">' . Date( 'j', $ds ). '</span>';
      $out .= ' - ';
      $out .= '<span class="time">' . Date( $tf, $ds ). '</span>';
      /* Don't display end time if equal to start time */
      if ( Date( $tf, $ds ) != Date( $tf, $de ) ) {
      	$out .= ' ' . __( 'to', 'bp-events' ) . ' ';
      	$out .= '<span class="time">' . Date( $tf, $de ). '</span>';
      }
    }
    else {
      // Multi date events, Event start
      $out .= '<span class="month">' . Date( 'M', $ds ). '</span>';
      $out .= ' ';
      $out .= '<span class="day-digit">' . Date( 'j', $ds ). '</span>';
      /* Display year if not current year */
      if ( Date( 'Y', $ds ) && Date( 'Y', $de ) != Date( 'Y' ) ) {
        $out .= ', ';
      	$out .= '<span class="year">' . Date( 'Y', $ds ). '</span>';
      }
      $out .= ' - ';
      $out .= '<span class="time">' . Date( $tf, $ds ). '</span>';
      $out .= ' ' . __('to','bp-events') . ' ';
      // Multi date events, Event end
      $out .= '<span class="month">' . Date( 'M', $de ). '</span> ';
      $out .= ' ';
      $out .= '<span class="day-digit">' . Date( 'j', $de ). '</span>';
      /* Display year if not current year */
      if ( Date( 'Y', $ds ) && Date( 'Y', $de ) != Date( 'Y' ) ) {
        $out .= ', ';
      	$out .= '<span class="year">' . Date( 'Y', $de ). '</span> ';
      }
      $out .= ' - ';
      $out .= '<span class="time">' . Date( $tf, $de ). '</span>';
    }
  }

  echo apply_filters( 'bp_event_date', $out );
}

function bp_event_has_passed( $event = null ) {
	global $events_template;

  if (!$event) {
    $ds = $events_template->event->date_start;
    $de = $events_template->event->date_end;
  } else {
    $ds = $event->date_start;
    $de = $event->date_end;
  }

  return $de <= time();
}

function bp_event_in_progress( $event = null ) {
	global $events_template;

  if (!$event) {
    $ds = $events_template->event->date_start;
    $de = $events_template->event->date_end;
  } else {
    $ds = $event->date_start;
    $de = $event->date_send;
  }

  return ($ds <=time() && $de >= time());
}

function bp_event_show_allday( $event = false ) {
	global $events_template;

	if ( !$event )
		$event =& $events_template->event;

	if ( $event->is_allday )
		echo ' checked="checked"';
}



function timetogo( $datefrom ) {
	//	return human_time_diff( $time, $datefrom );

	// Defaults and assume if 0 is passed in that
	// its an error rather than the epoch

	if($datefrom<=0) { return "A long, long time ago"; }

	$dateto = time();

	// Calculate the difference in seconds betweeen
	// the two timestamps

	$difference = $datefrom - $dateto;

	// If difference is less than 60 seconds,
	// seconds is a good interval of choice

	if($difference < 60) {
		$interval = "s";
	}

	// If difference is between 60 seconds and
	// 60 minutes, minutes is a good interval
	elseif($difference >= 60 && $difference<60*60) {
		$interval = "n";
	}

	// If difference is between 1 hour and 24 hours
	// hours is a good interval
	elseif($difference >= 60*60 && $difference<60*60*24) {
		$interval = "h";
	}

	// If difference is between 1 day and 7 days
	// days is a good interval
	elseif($difference >= 60*60*24 && $difference<60*60*24*7) {
		$interval = "d";
	}

	// If difference is between 1 week and 30 days
	// weeks is a good interval
	elseif($difference >= 60*60*24*7 && $difference <
	60*60*24*30) {
		$interval = "ww";
	}

	// If difference is between 30 days and 365 days
	// months is a good interval, again, the same thing
	// applies, if the 29th February happens to exist
	// between your 2 dates, the function will return
	// the 'incorrect' value for a day
	elseif($difference >= 60*60*24*30 && $difference <
	60*60*24*365) {
		$interval = "m";
	}

	// If difference is greater than or equal to 365
	// days, return year. This will be incorrect if
	// for example, you call the function on the 28th April
	// 2008 passing in 29th April 2007. It will return
	// 1 year ago when in actual fact (yawn!) not quite
	// a year has gone by
	elseif($difference >= 60*60*24*365) {
		$interval = "y";
	}

	// Based on the interval, determine the
	// number of units between the two dates
	// From this point on, you would be hard
	// pushed telling the difference between
	// this function and DateDiff. If the $datediff
	// returned is 1, be sure to return the singular
	// of the unit, e.g. 'day' rather 'days'

	switch($interval) {
		case "m":
		$months_difference = floor($difference / 60 / 60 / 24 /
		29);
		while (mktime(date("H", $datefrom), date("i", $datefrom),
		date("s", $datefrom), date("n", $datefrom)+($months_difference),
		date("j", $dateto), date("Y", $datefrom)) < $dateto) {
			$months_difference++;
		}
		$datediff = $months_difference;

		// We need this in here because it is possible
		// to have an 'm' interval and a months
		// difference of 12 because we are using 29 days
		// in a month

		if($datediff==12)	{
			$datediff--;
		}

		$res = ($datediff==1) ? "$datediff ".__("month", 'bp-events') : "$datediff ".__("months", 'bp-events');
		break;

		case "y":
		$datediff = floor($difference / 60 / 60 / 24 / 365);
		$res = ($datediff==1) ? "$datediff ".__("year", 'bp-events') : "$datediff ".__("years", 'bp-events');
		break;

		case "d":
		$datediff = floor($difference / 60 / 60 / 24);
		$res = ($datediff==1) ? "$datediff ".__("day", 'bp-events') : "$datediff ".__("days", 'bp-events');
		break;

		case "ww":
		$datediff = floor($difference / 60 / 60 / 24 / 7);
		$res = ($datediff==1) ? "$datediff ".__("week", 'bp-events') : "$datediff ".__("weeks", 'bp-events');
		break;

		case "h":
		$datediff = floor($difference / 60 / 60);
		$res = ($datediff==1) ? "$datediff ".__("hour", 'bp-events') : "$datediff ".__("hours", 'bp-events');
		break;

		case "n":
		$datediff = floor($difference / 60);
		$res = ($datediff==1) ? "$datediff ".__("minute", 'bp-events') :"$datediff ".__("minutes", 'bp-events');
		break;

		case "s":
		$datediff = $difference;
		$res = ($datediff==1) ? "$datediff ".__("second", 'bp-events') : "$datediff ".__("seconds", 'bp-events');
		break;
	}
	return $res;

}

function bp_event_print_datepicker( $dt = null, $fieldname ) {

	if (!$dt)
  	$dt = time();

  $dy = Date("j",$dt);
  $mn = Date("n",$dt);
  $yr = Date("Y",$dt);

  $format = get_option('date_format');
  $array = explode("\r\n",trim(chunk_split($format,1)));

  echo "<span id='$fieldname-dates' name='$fieldname-dates'>";

	  foreach($array as $code) {

	  	switch ($code) {
	    case 'd':
	    case 'j': ?>
			  <select name="<?php echo $fieldname ?>_day" id="<?php echo $fieldname ?>_day" style="width:60px;">
			  	<option value=""><?php _e('day','bp-events');?></option><?php

						for ($i=1; $i<=31; $i++) {
				    	$m = date($code, mktime(0,0,0,1,$i));

							if ($i==$dy)
				  			echo '<option value="'.$i.'" SELECTED>'.__($m,'bp-events').'</option>';
							else
				  			echo '<option value="'.$i.'">'.__($m,'bp-events').'</option>';
				  	} ?>
				</select><?php
			break;

			case 'F':
			case 'm':
			case 'M':
			case 'n':
				if ($code=='F')
				  $width="width:120px";
				else
				  $width="width:80px"; ?>

				<select name="<?php echo $fieldname ?>_month" id="<?php echo $fieldname ?>_month" style="<?php echo $width?>;">
					<option value=""><?php _e('month','bp-events');?></option><?php

	      		for ($i=1; $i<=12; $i++) {
		  				$m = date($code, mktime(0,0,0,$i,1));

		  				if ($i == $mn)
		    			  echo '<option value="'.$i.'" SELECTED>'.__($m,'bp-events').'</option>';
		  				else
		     			  echo '<option value="'.$i.'">'.__($m,'bp-events').'</option>';
						} ?>
				</select><?php
	    break;

	  	case 'Y':
	  	case 'y': ?>
	  		<select name="<?php echo $fieldname ?>_year" id="<?php echo $fieldname ?>_year" style="width:80px;">
	  			<option value=""><?php _e('year','bp-events');?></option><?php

						for ($i=0; $i<=9; $i++) {
							$y = $i + Date("Y");
							$m = date($code, mktime(0,0,0,1,1,$y));

							if ($y == $yr)
							  echo '<option value="'.$y.'" SELECTED>'.$m.'</option>';
							else
								echo '<option value="'.$y.'">'.$m.'</option>';
						} ?>
				</select><?php
	    break;

	    default:
	    	echo " $code ";
	    break;

			}
		}

	echo "</span>";

	echo "<span id='$fieldname-times' name='$fieldname-times'>";

    echo _e(" at ",'bp-events');

    $hr12 = Date("g",$dt);
    $hr24 = Date("G",$dt);
    $mn = Date("i", ceilTime( "15 minutes",$dt));
    $ap = Date("a",$dt);

    $format = get_option('time_format');
    $array = explode("\r\n",trim(chunk_split($format,1)));

    foreach($array as $code) {

      switch ($code) {
    	case 'g':
    	case 'h': ?>
    		<select name="<?php echo $fieldname ?>_hour" id="<?php echo $fieldname ?>_hour" style="width:60px;">
    			<option value=""><?php _e('hour','bp-events');?></option><?php

	    			for ($i=1; $i<=12; $i++) {
	    	  	  $m = date($code, mktime($i,0,0,1,1));

	    				if ($i==$hr12)
	        			echo '<option value="'.$i.'" SELECTED>'.$m.'</option>';
	    				else
	        			echo '<option value="'.$i.'">'.$m.'</option>';
	     			} ?>
	     	</select><?php
      break;

    	case 'G':
    	case 'H': ?>
    		<select name="<?php echo $fieldname ?>_hour" id="<?php echo $fieldname ?>_hour" style="width:60px;">
    			<option value=""><?php _e('hour','bp-events');?></option><?php

      			for ($i=0; $i<=23; $i++) {
      	    	$m = date($code, mktime($i,0,0,1,1));

      				if ($i==$hr24)
          			echo '<option value="'.$i.'" SELECTED>'.$m.'</option>';
      				else
          			echo '<option value="'.$i.'">'.$m.'</option>';
     				} ?>
     		</select><?php
      break;

    	case 'i': ?>
    		<select name="<?php echo $fieldname ?>_minute" id="<?php echo $fieldname ?>_minute" style="width:60px;">
    			<option value=""><?php _e('min','bp-events');?></option><?php

      			for ($i=0; $i<=3; $i++) {
          	  $j = $i * 15;
      				$m = date($code, mktime(0,$j,0,1,1));

      				if ($j == $mn)
        			  echo '<option value="'.$j.'" SELECTED>'.$m.'</option>';
      				else
         			  echo '<option value="'.$j.'">'.$m.'</option>';
      			} ?>
      	</select><?php
    	break;

    	case 'a':
    	case 'A': ?>
    		<select name="<?php echo $fieldname ?>_ampm" id="<?php echo $fieldname ?>_ampm" style="width:60px;">
    			<?php
    	      if ($ap=='am') {
    	      	$sela = "SELECTED";
    	        $selp = "";
    	      } else {
    	        $selp = "SELECTED";
    	        $sela = "";
    	      }
    	      echo '<option value="1" '.$sela.'>'.__('am','bp-events').'</option>';
    	      echo '<option value="2" '.$selp.'>'.__('pm','bp-events').'</option>';
					?>
				</select><?php
	    break;

    	default:
    		echo " $code ";
      break;

      }
		}

	echo "</span>";
}

function ceilTime( $increment, $timestamp ) {
	$mins = Date("i",$timestamp);

  // check if already reounded just return if so
  if (0 == $mins % 15)
    return $timestamp;

  $increment = strtotime($increment, 1) - 1;
  $this_hour = strtotime(date("Y-m-d H:", strtotime("-1 Hour", $timestamp))."00:00");
  $next_hour = strtotime(date("Y-m-d H:", strtotime("+1 Hour", $timestamp))."00:00");

  $increments = array();
  $differences = array();

  for($i = $this_hour; $i <= $next_hour; $i += $increment) {
    if($i > $timestamp) return $i;
  }
}


function mini_calendar($datum = null, $cat = null)
{
         //If no parameter is passed use the current date.
         if($datum == null)
            $datum = getDate();

         $day = $datum["mday"];
         $month = $datum["mon"];
         $month_name = $datum["month"];
         $year = $datum["year"];

         $prettydate = date('r', $datum);
         $nextmonth = '"'.strtotime($prettydate.' + 1 month').'"';

         $limit = 6;

         $m = getmonth($month,$year);

         $days_in_this_month = $m['days'];
         $first_week_day = $m['first_wday'];
         $first_day = $m['first_day'];
         $last_day = $m['last_day'];

    	 $events = events_get_all_for_daterange(false, $first_day,$last_day);
         $booked = book_events($events, $m, $cat);

         $path = '"' . STYLESHEETPATH . '/events/mini-calendar.php"';
         $loading = '"ajax-loader-events"';

		 $calendar_html .= "<div id='main' name='main'>";
         $calendar_html .= "<table class='mini_calendar_table'>";

         $calendar_html .= "<tr class='mini_calendar_header_month_row'><th class='mini_calendar_header_month_cell' colspan='7'>";
         $calendar_html .= "<a onClick='SendData($nextmonth,$loading,$path);' id='previous-year' class='mini_calendar_previous_year'>&lt;&lt;</a>";
         $calendar_html .= "<a onClick='SendData($nextmonth,$loading,$path);' id='previous-month' class='mini_calendar_previous_month'>&lt;</a>";
         $calendar_html .= $month_name . " " . $year;
         $calendar_html .= "<a onClick='SendData($nextmonth,$loading,$path);' id='next-month' class='mini_calendar_next_month'>&gt;</a>";
         $calendar_html .= "<a onClick='SendData($nextmonth,$loading,$path);' id='next-year' class='mini_calendar_next_year'>&gt;&gt;</a>";

         $calendar_html . "</th></tr>";

         $calendar_html .="<tr class='mini_calendar_header_row'>";
         $calendar_html .= "<th class='mini_calendar_header_cell'>".__('Sun','bp-events')."</th>";
         $calendar_html .= "<th class='mini_calendar_header_cell'>".__('Mon','bp-events')."</th>";
         $calendar_html .= "<th class='mini_calendar_header_cell'>".__('Tue','bp-events')."</th>";
         $calendar_html .= "<th class='mini_calendar_header_cell'>".__('Wed','bp-events')."</th>";
         $calendar_html .= "<th class='mini_calendar_header_cell'>".__('Thu','bp-events')."</th>";
         $calendar_html .= "<th class='mini_calendar_header_cell'>".__('Fri','bp-events')."</th>";
         $calendar_html .= "<th class='mini_calendar_header_cell'>".__('Sat','bp-events')."</th>";
         $calendar_html .="</tr>";

         $calendar_html .= "<tr>";

         //Fill the first week of the month with the appropriate number of blanks.
         for($week_day = 0; $week_day < $first_week_day; $week_day++)
            {
            $calendar_html .= "<td class='mini_calendar_previous_month_cell'>&nbsp;</td>";
            }

         $week_day = $first_week_day;
         for($day_counter = 1; $day_counter <= $days_in_this_month; $day_counter++)
            {
            $week_day %= 7;

            if($week_day == 0)
               $calendar_html .= "</tr><tr>";

			if($day == $day_counter)
				$tdclass = 'mini_calendar_cell_today';
            else
				$tdclass = 'mini_calendar_cell';

         	for ($i=1; $i<$limit; $i++) {
         	    if ($booked[$i][$day_counter]) {
              		$tdclass .= '_event';
					break;
				}
    		}

            $calendar_html .= "<td class=$tdclass>" . $day_counter;


        // 		$calendar_html .= "<div $divclass style='height:10px;overflow:hidden;'>$eventavatar&nbsp;$eventname</div>";

            $calendar_html .= " </td>";

            $week_day++;
         }

         for ($i = $weekday; $i<5; $i++) {
         	  $calendar_html .="<td class='mini_calendar_next_month_cell'>&nbsp;</td>";
         }

         $calendar_html .= "</tr>";
         $calendar_html .= "</table></div>";

         return($calendar_html);
}

function month_calendar($date = null, $cat = null)
{
         //If no parameter is passed use the current date.
         if($date == null)
            $date = getDate();

         $day = $date["mday"];
         $month = $date["mon"];
         $month_name = $date["month"];
         $year = $date["year"];

         $limit = 6;

         $m = getmonth($month,$year);

         $days_in_this_month = $m['days'];
         $first_week_day = $m['first_wday'];
         $first_day = $m['first_day'];
         $last_day = $m['last_day'];

    	 $events = events_get_all_for_daterange(false, $first_day,$last_day);
         $booked = book_events($events, $m, $cat);


         $calendar_html = __('Month Calendar', 'bp-events');

         if ($cat > 0) {
            $category = new BP_Events_Category($cat);

            if ($category)
            	$calendar_html .= __(' Filtered by category: ', 'bp-events') . $category->name;
         }

 		 $calendar_html .= "<span class='ajax-loader' id='ajax-loader-events'></span>";
         $calendar_html .= "<table style=\"background-color:666699; width:100%; color:ffffff; border:1px solid;\">";

         $calendar_html .= "<tr><td colspan=\"7\" align=\"center\" style=\"background-color:9999cc; color:000000;\">" .
                           $month_name . " " . $year . "</td></tr>";

         $calendar_html .= "<tr>";

         //Fill the first week of the month with the appropriate number of blanks.
         for($week_day = 0; $week_day < $first_week_day; $week_day++)
            {
            $calendar_html .= "<td style=\"background-color:9999cc; color:000000; width:14%; border:1px solid;\"> </td>";
            }

         $week_day = $first_week_day;
         for($day_counter = 1; $day_counter <= $days_in_this_month; $day_counter++)
            {
            $week_day %= 7;

            if($week_day == 0)
               $calendar_html .= "</tr><tr>";

            //Do something different for the current day.
            if($day == $day_counter)
               $calendar_html .= "<td style=\"border:1px solid; width:14%;\"><b>" . $day_counter . $y."</b>";
            else
               $calendar_html .= "<td style=\"background-color:9999cc; color:000000;border:1px solid; width:14%;\">&nbsp;" . $day_counter . $y;

         	for ($i=1; $i<$limit; $i++) {
         	    if ($booked[$i][$day_counter]) {
					    $event = new BP_Events_Event( $booked[$i][$day_counter], false, false );
					    $divclass = "class='category".$event->category."'";
          			            $popup = "$event->name<br>" . bp_event_date($event, false) . "<br>$event->description";
					    $eventpopup = "onmouseover=\"popup('$popup')\" onmouseout=\"popout();\"";
					    $eventname = "<a href='" . bp_event_permalink($event, false) ."' $eventpopup>$event->name</a>";
					    $eventavatar =  bp_get_event_avatar_mini( $event );
				} else {					    $divclass = '';
					    $eventname = '';
					    $eventavatar = '';
					    $eventpopup = '';
				}


         		$calendar_html .= "<div $divclass style='height:20px;overflow:hidden;'>$eventavatar&nbsp;$eventname</div>";
            }

            $calendar_html .= " </td>";

            $week_day++;
         }

         for ($i = $weekday; $i<5; $i++) {
         	  $calendar_html .="<td style=\"background-color:9999cc; color:000000;border:1px solid; width:24px;\"></td>";
         }

         $calendar_html .= "</tr>";
         $calendar_html .= "</table>";

         return($calendar_html);
}


function book_events($events,$m, $cat)
{
    $book = array_fill(1, 10, array_fill(1, 31, 0));


    foreach ( $events['events'] as $event ) {
		$event = new BP_Events_Event( $event->event_id, false, false );

		if ($cat)
		  if ($event->category != $cat)
		  	continue;

		// get the dates
        $firstd = $event->date_start;
        $lastd = $event->date_end;

		// get event start and end months
        $event_start_month = Date('m',$firstd);
        $event_end_month = Date('m',$lastd);

		// prepare first and last days
		// if event started before this month, start at 1
		// if event ends next month, end at # of days

        $first = Date('j',$firstd);
        $last = Date('j',$lastd);

        if ($event_start_month < $m['mon'])
        	$first = 1;
        if ($event_end_month > $m['mon'])
        	$last = $m['days'];

		$found = 0;
		$limit = 10;
		$exlast = $last + 1;

		for ($i=1; $i <= $limit; $i++) {

			$c = 0;

	    	for ($j=$first; $j <= $last; $j++) {

	    		if ($book[$i][$j]==0) {
	    			$c +=1;
	       	 } else {
	        		break;
	        	}

	    	 }


	    	 if ($c == ($last-$first + 1)) {
	       		$found = $i;
	       	 break;
	     	}

		}



		if ($found) {

	//	    echo "$first:$last;$found";

			for ($i = $first;$i <= $last; $i++) {
				$book[$found][$i] = $event->id;
			}

   		 }

   	}

   	return $book;


}

function getmonth ($month = null, $year = null)
  {
      // The current month is used if none is supplied.
      if (is_null($month))
          $month = date('n');

      // The current year is used if none is supplied.
      if (is_null($year))
          $year = date('Y');

      // Verifying if the month exist
      if (!checkdate($month, 1, $year))
          return null;

      // Calculating the days of the month
      $first_of_month = mktime(0, 0, 0, $month, 1, $year);
      $days_in_month = date('t', $first_of_month);
      $last_of_month = mktime(0, 0, 0, $month, $days_in_month, $year);

      $m = array();
      $m['first_mday'] = 1;
      $m['first_wday'] = date('w', $first_of_month);
      $m['first_weekday'] = strftime('%A', $first_of_month);
      $m['first_yday'] = date('z', $first_of_month);
      $m['first_week'] = date('W', $first_of_month);
      $m['last_mday'] = $days_in_month;
      $m['last_wday'] = date('w', $last_of_month);
      $m['last_weekday'] = strftime('%A', $last_of_month);
      $m['last_yday'] = date('z', $last_of_month);
      $m['last_week'] = date('W', $last_of_month);
      $m['mon'] = $month;
      $m['month'] = strftime('%B', $first_of_month);
      $m['year'] = $year;
      $m['days'] = $days_in_month;
      $m['last_day'] = $last_of_month;
      $m['first_day'] = $first_of_month;


      return $m;
  }

function get_old_events_filter($clause, $archived = '0') {

  // show or hide old events
  if (get_option('events_handle_old_events') == 2) {
    $oldevents = get_option('events_what_are_old_events');
	  switch ($oldevents) {
	    case 1:
	  		$diff = 24 * 60 * 60;
	  	  break;
	  	case 2:
	  	  $diff = 7 * 24 * 60 * 60;
	  	  break;
	  	case 3:
	  	  $diff = 30 * 24 * 60 * 60;
	  	  break;
	  	case 4:
	  	  $diff = 365 * 24 * 60 * 60;
	      break;
	  }

    $threshold = date('Y-m-d H:m:s',time() - $diff);

	  if ( !$archived ) {
	    $c = " $clause date_end > '$threshold'";
	  } else {
	    $c = " $clause date_end < '$threshold'";
	  }

  } else {
  	$c = '';
  }

  return $c;
}

?>