<?php

Class BP_Events_Event {
	var $id;
	var $creator_id;
	var $name;
	var $slug;
	var $tagline;
	var $description;
	var $news;
	var $location;
	var $status;
	var $enable_wire;
	var $enable_forum;
	var $date_created;
	var $date_start;
  var $date_end;
  var $is_alday;

	var $user_dataset;

	var $admins;
	var $total_member_count;
	var $random_members;
	var $latest_wire_posts;

	var $link_group;

	function bp_events_event( $id = null, $single = false, $get_user_dataset = true ) {
		if ( $id ) {
			$this->id = $id;
			$this->populate( $get_user_dataset );
		}

		if ( $single ) {
			$this->populate_meta();
		}
	}

	function populate( $get_user_dataset ) {
		global $wpdb, $bp;

		$sql = $wpdb->prepare( "SELECT * FROM {$bp->events->table_name} WHERE id = %d", $this->id );
		$event = $wpdb->get_row($sql);

		if ( $event ) {
			$this->id = $event->id;
			$this->creator_id = $event->creator_id;
			$this->name = stripslashes($event->name);
			$this->slug = $event->slug;
			$this->tagline = stripslashes($event->tagline);
			$this->description = stripslashes($event->description);
			$this->news = stripslashes($event->news);
			$this->location = stripslashes($event->location);
			$this->status = $event->status;
			$this->enable_wire = $event->enable_wire;
			$this->enable_forum = $event->enable_forum;
			$this->date_created = strtotime($event->date_created);
			$this->date_start = strtotime($event->date_start);
 			$this->date_end  = strtotime($event->date_end);
   		$this->is_allday = $event->is_allday;
			$this->total_member_count = events_get_eventmeta( $this->id, 'total_member_count' );
			$this->link_group = $event->link_group;

			$gravatar_url = apply_filters( 'bp_gravatar_url', 'http://www.gravatar.com/avatar/' );

			if ( $get_user_dataset ) {
				$this->user_dataset = $this->get_user_dataset();

				//if ( !$this->total_member_count ) {
				$this->total_member_count = count( $this->user_dataset );
				events_update_eventmeta( $this->id, 'total_member_count', $this->total_member_count );
				//}
			}
		}
	}

	function populate_meta() {
		if ( $this->id ) {
			$this->admins = $this->get_administrators();
			$this->random_members = $this->get_random_members();
		}
	}

	function save() {
		global $wpdb, $bp;

		$this->creator_id = apply_filters( 'events_event_creator_id_before_save', $this->creator_id, $this->id );
		$this->name = apply_filters( 'events_event_name_before_save', $this->name, $this->id );
 		$this->slug = apply_filters( 'events_event_slug_before_save', $this->slug, $this->id );
 		$this->tagline = apply_filters( 'events_event_tagline_before_save', $this->tagline, $this->id );
		$this->description = apply_filters( 'events_event_description_before_save', $this->description, $this->id );
 		$this->news = apply_filters( 'events_event_news_before_save', $this->news, $this->id );
 		$this->location = apply_filters( 'events_event_location_before_save', $this->location, $this->id );
 		$this->is_allday = apply_filters( 'events_event_allday_before_save', $this->is_allday, $this->id );
		$this->status = apply_filters( 'events_event_status_before_save', $this->status, $this->id );
		$this->enable_wire = apply_filters( 'events_event_enable_wire_before_save', $this->enable_wire, $this->id );
		$this->enable_forum = apply_filters( 'events_event_enable_forum_before_save', $this->enable_forum, $this->id );
		$this->date_created = apply_filters( 'events_event_date_created_before_save', $this->date_created, $this->id );

		do_action( 'events_event_before_save', $this );

		if ( $this->id ) {
			$sql = $wpdb->prepare(
				"UPDATE {$bp->events->table_name} SET
					creator_id = %d,
					name = %s,
					slug = %s,
					tagline = %s,
					description = %s,
					news = %s,
					location = %s,
					status = %s,
					enable_wire = %d,
					enable_forum = %d,
					date_created = FROM_UNIXTIME(%d),
					date_start = FROM_UNIXTIME(%d),
          date_end = FROM_UNIXTIME(%d),
          is_allday = %d,
					link_group = %d
				WHERE
					id = %d
				",
					$this->creator_id,
					$this->name,
					$this->slug,
					$this->tagline,
					$this->description,
					$this->news,
					$this->location,
					$this->status,
					$this->enable_wire,
					$this->enable_forum,
					$this->date_created,
          $this->date_start,
          $this->date_end,
          $this->is_allday,
					$this->link_group,
					$this->id
			);
		} else {
			$sql = $wpdb->prepare(
				"INSERT INTO {$bp->events->table_name} (
					creator_id,
					name,
					slug,
					tagline,
					description,
					news,
					location,
					status,
					enable_wire,
					enable_forum,
					date_created,
					date_start,
					date_end,
					is_allday,
					link_group
				) VALUES (
					%d, %s, %s, %s, %s, %s, %s, %s, %d, %d, FROM_UNIXTIME(%d), FROM_UNIXTIME(%d), FROM_UNIXTIME(%d), %d, %d
				)",
					$this->creator_id,
					$this->name,
					$this->slug,
					$this->tagline,
					$this->description,
					$this->news,
					$this->location,
					$this->status,
					$this->enable_wire,
					$this->enable_forum,
					$this->date_created,
          $this->date_start,
          $this->date_end,
          $this->is_allday,
					$this->link_group
			);
		}

		if ( false === $wpdb->query($sql) )
			return false;

		if ( !$this->id ) {
			$this->id = $wpdb->insert_id;
		}

		do_action( 'events_event_after_save', $this );

		return true;
	}

	function get_user_dataset() {
		global $wpdb, $bp;

		return $wpdb->get_results( $wpdb->prepare( "SELECT user_id, is_admin, inviter_id, user_title, is_mod FROM {$bp->events->table_name_members} WHERE event_id = %d AND is_confirmed = 1 AND is_banned = 0 ORDER BY rand()", $this->id ) );
	}

	function get_administrators() {
		for ( $i = 0; $i < count($this->user_dataset); $i++ ) {
			if ( $this->user_dataset[$i]->is_admin )
				$admins[] = new BP_Events_Member( $this->user_dataset[$i]->user_id, $this->id );
		}

		return $admins;
	}

	function get_random_members() {
		$total_randoms = ( $this->total_member_count > 5 ) ? 5 : $this->total_member_count;

		for ( $i = 0; $i < $total_randoms; $i++ ) {
			if ( !(int)$this->user_dataset[$i]->is_banned )
				$users[] = new BP_Events_Member( $this->user_dataset[$i]->user_id, $this->id );
		}
		return $users;
	}

	function is_member() {
		global $bp;

		for ( $i = 0; $i < count($this->user_dataset); $i++ ) {
			if ( $this->user_dataset[$i]->user_id == $bp->loggedin_user->id ) {
				return true;
			}
		}

		return false;
	}

	function delete() {
		global $wpdb, $bp;

		// Delete eventmeta for the event
		events_delete_eventmeta( $this->id );

		// Modify event count usermeta for members
		for ( $i = 0; $i < count($this->user_dataset); $i++ ) {
			$user = $this->user_dataset[$i];

			$total_count = get_usermeta( $user->user_id, 'total_event_count' );

			if ( $total_count != '' ) {
				update_usermeta( $user->user_id, 'total_event_count', (int)$total_count - 1 );
			}

			// Now delete the event member record
			BP_Events_Member::delete( $user->user_id, $this->id, false );
		}

		// Delete the wire posts for this event if the wire is installed
		if ( function_exists('bp_wire_install') ) {
			BP_Wire_Post::delete_all_for_item( $this->id, $bp->events->table_name_wire );
		}

		// Finally remove the event entry from the DB
		if ( !$wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->events->table_name} WHERE id = %d", $this->id ) ) )
			return false;

		return true;
	}


	/* Static Functions */

	function event_exists( $slug, $table_name = false ) {
		global $wpdb, $bp;

		if ( !$table_name )
			$table_name = $bp->events->table_name;

		if ( !$slug )
			return false;

		return $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table_name} WHERE slug = %s", $slug ) );
	}

	function get_id_from_slug( $slug ) {
		return BP_Events_Event::event_exists( $slug );
	}

	function get_invites( $user_id, $event_id ) {
		global $wpdb, $bp;
		return $wpdb->get_col( $wpdb->prepare( "SELECT user_id FROM {$bp->events->table_name_members} WHERE event_id = %d and is_confirmed = 0 AND inviter_id = %d", $event_id, $user_id ) );
	}

  function get_events_for_group( $group_id ) {
    	global $wpdb, $bp;

    	if ($group_id) {

     	  $oldevents = get_old_events_filter('AND');

    	  $events =  $wpdb->get_results( $wpdb->prepare( "SELECT id as event_id FROM {$bp->events->table_name} WHERE link_group = %d" . $oldevents . " ORDER BY date_start ASC", $group_id ) );
    	  $total = 	$wpdb->get_var( $wpdb->prepare( "SELECT count(id) FROM {$bp->events->table_name} WHERE link_group = %d" . $oldevents . " ORDER BY date_start ASC", $group_id ) );
          return array( 'events' => $events, 'total' => $total );

        } else {

 			return false;
 	    }
    }

	function filter_user_events( $filter, $user_id = false, $order = false, $limit = null, $page = null ) {
		global $wpdb, $bp;

		if ( !$user_id )
			$user_id = $bp->displayed_user->id;

		like_escape($filter);

		if ( $limit && $page )
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		// Get all the event ids for the current user's events.
		$gids = BP_Events_Member::get_event_ids( $user_id );

		$oldevents = get_old_events_filter('AND');

		if ( !$gids['events'] )
			return false;

		$gids = implode( ',', $gids['events'] );

		$paged_events = $wpdb->get_results( $wpdb->prepare( "SELECT id as event_id FROM {$bp->events->table_name} WHERE ( name LIKE '{$filter}%%' OR description LIKE '{$filter}%%' ) AND id IN ({$gids}) " . $oldevents . " {$pag_sql}" ) );
		$total_events = $wpdb->get_var( $wpdb->prepare( "SELECT count(id) FROM {$bp->events->table_name} WHERE ( name LIKE '{$filter}%%' OR description LIKE '{$filter}%%' ) AND id IN ({$gids})" . $oldevents ) );

		return array( 'events' => $paged_events, 'total' => $total_events );
	}

	function search_events( $filter, $limit = null, $page = null, $sort_by = false, $order = false ) {
		global $wpdb, $bp;

		$filter = like_escape( $wpdb->escape( $filter ) );

		if ( $limit && $page )
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		if ( $sort_by && $order ) {
			$sort_by = $wpdb->escape( $sort_by );
			$order = $wpdb->escape( $order );
			$order_sql = "ORDER BY $sort_by $order";
		}

		$oldevents = get_old_events_filter('AND');

		if ( !is_site_admin() )
			$hidden_sql = "AND status != 'hidden'";

		$paged_events = $wpdb->get_results( $wpdb->prepare( "SELECT id as event_id FROM {$bp->events->table_name} WHERE ( name LIKE '%%$filter%%' OR description LIKE '%%$filter%%' ) {$hidden_sql} {$order_sql} {$pag_sql}" ) );
		$total_events = $wpdb->get_var( $wpdb->prepare( "SELECT count(id) FROM {$bp->events->table_name} WHERE ( name LIKE '%%$filter%%' OR description LIKE '%%$filter%%' ) {$hidden_sq}" ) );

		return array( 'events' => $paged_events, 'total' => $total_events );
	}

	function check_slug( $slug ) {
		global $wpdb, $bp;

		return $wpdb->get_var( $wpdb->prepare( "SELECT slug FROM {$bp->events->table_name} WHERE slug = %s", $slug ) );
	}

	function get_slug( $event_id ) {
		global $wpdb, $bp;

		return $wpdb->get_var( $wpdb->prepare( "SELECT slug FROM {$bp->events->table_name} WHERE id = %d", $event_id ) );
	}

	function has_members( $event_id ) {
		global $wpdb, $bp;

		$members = $wpdb->get_var( $wpdb->prepare( "SELECT count(id) FROM {$bp->events->table_name_members} WHERE event_id = %d", $event_id ) );

		if ( !$members )
			return false;

		return true;
	}

	function has_membership_requests( $event_id ) {
		global $wpdb, $bp;

		return $wpdb->get_var( $wpdb->prepare( "SELECT count(id) FROM {$bp->events->table_name_members} WHERE event_id = %d AND is_confirmed = 0", $event_id ) );
	}

	function get_membership_requests( $event_id, $limit = null, $page = null ) {
		global $wpdb, $bp;

		if ( $limit && $page ) {
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );
		}

		$paged_requests = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$bp->events->table_name_members} WHERE event_id = %d AND is_confirmed = 0 AND inviter_id = 0{$pag_sql}", $event_id ) );
		$total_requests = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$bp->events->table_name_members} WHERE event_id = %d AND is_confirmed = 0 AND inviter_id = 0", $event_id ) );

		return array( 'requests' => $paged_requests, 'total' => $total_requests );
	}

	function get_newest( $limit = null, $page = null ) {
		global $wpdb, $bp;

		if ( $limit && $page )
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		if ( !is_site_admin() ) {
			$hidden_sql = "WHERE status != 'hidden'";
			$oldevents = get_old_events_filter('AND');
		} else {
			$hidden_sql = "";
			$oldevents = get_old_events_filter('WHERE');
		}

		$paged_events = $wpdb->get_results( $wpdb->prepare( "SELECT id as event_id FROM {$bp->events->table_name} {$hidden_sql} {$oldevents} ORDER BY date_created DESC {$pag_sql}" ) );
		$total_events = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$bp->events->table_name} {$hidden_sql} {$oldevents} ORDER BY date_created DESC", $limit ) );

		return array( 'events' => $paged_events, 'total' => $total_events );
	}

  function get_upcoming( $limit = null, $page = null ) {
		global $wpdb, $bp;

		$oldevents = get_old_events_filter('AND');

		if ( !is_site_admin() )
			$hidden_sql = " AND status != 'hidden'";

		if ( $limit && $page ) {
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );
			$total_events = $wpdb->get_var( $wpdb->prepare( "SELECT count(id) FROM {$bp->events->table_name} WHERE (date_start > NOW() OR (date_start < NOW() AND date_end > NOW())) {$hidden_sql} {$oldevents} ORDER BY date_start ASC", $limit ) );
		}

		$paged_events = $wpdb->get_results( $wpdb->prepare( "SELECT id as event_id FROM {$bp->events->table_name} WHERE (date_start > NOW() OR (date_start < NOW() AND date_end > NOW())) {$hidden_sql} {$oldevents} ORDER BY date_start ASC {$pag_sql}" ) );

		return array( 'events' => $paged_events, 'total' => $total_events );
	}

	function get_active( $limit = null, $page = null ) {
		global $wpdb, $bp;

		if ( $limit && $page )
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		if ( !is_site_admin() )
			$hidden_sql = "AND g.status != 'hidden'";

		$paged_events = $wpdb->get_results( $wpdb->prepare( "SELECT event_id FROM {$bp->events->table_name_eventmeta} gm, {$bp->events->table_name} g WHERE g.id = gm.event_id {$hidden_sql} AND gm.meta_key = 'last_activity' ORDER BY CONVERT(gm.meta_value, SIGNED) DESC {$pag_sql}" ) );
		$total_events = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT (event_id) FROM {$bp->events->table_name_eventmeta} gm, {$bp->events->table_name} g WHERE g.id = gm.event_id {$hidden_sql} AND gm.meta_key = 'last_activity' ORDER BY CONVERT(gm.meta_value, SIGNED) DESC" ) );

		return array( 'events' => $paged_events, 'total' => $total_events );
	}

	function get_popular( $limit = null, $page = null ) {
		global $wpdb, $bp;

		if ( $limit && $page ) {
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );
		}

		if ( !is_site_admin() )
			$hidden_sql = "AND g.status != 'hidden'";

		$oldevents = get_old_events_filter('AND');

		$paged_events = $wpdb->get_results( $wpdb->prepare( "SELECT gm.event_id FROM {$bp->events->table_name_eventmeta} gm, {$bp->events->table_name} g WHERE g.id = gm.event_id {$hidden_sql} " . $oldevents . " AND gm.meta_key = 'total_member_count' ORDER BY CONVERT(gm.meta_value, SIGNED) DESC {$pag_sql}" ) );
		$total_events = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(gm.event_id) FROM {$bp->events->table_name_eventmeta} gm, {$bp->events->table_name} g WHERE g.id = gm.event_id {$hidden_sql} " . $oldevents . " AND gm.meta_key = 'total_member_count' ORDER BY CONVERT(gm.meta_value, SIGNED) DESC" ) );

		return array( 'events' => $paged_events, 'total' => $total_events );
	}

  function get_today( $limit = null, $page = null ) {
		global $wpdb, $bp;

		if ( !is_site_admin() )
			$hidden_sql = "WHERE status != 'hidden'";

		if ( $limit && $page ) {
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );
			$total_events = $wpdb->get_var( $wpdb->prepare( "SELECT count(id) FROM {$bp->events->table_name} {$hidden_sql} " . $oldevents . " AND (DATE(date_start) = CURDATE() OR (date_start < NOW() AND date_end > NOW())) ORDER BY date_start ASC", $limit ) );
		}

		$oldevents = get_old_events_filter('AND');

		$paged_events = $wpdb->get_results( $wpdb->prepare( "SELECT id as event_id FROM {$bp->events->table_name} {$hidden_sql} " . $oldevents . " AND (DATE(date_start) = CURDATE() OR (date_start < NOW() AND date_end > NOW())) ORDER BY date_start ASC {$pag_sql}" ) );

		return array( 'events' => $paged_events, 'total' => $total_events );
	}

	function get_daterange( $user_id, $startdate, $enddate ) {
		global $wpdb, $bp;

		// change UNIX dates to MYSQL format, add start and end times of day
		$msstartdate = date( 'Y-m-d 00:00:00', $startdate );
    $msenddate = date( 'Y-m-d 23:59:59', $enddate );

    // prepare the two range checks
    $range1 = $wpdb->prepare("(g.date_start > %s AND g.date_start < %s)",$msstartdate,$msenddate);
    $range2 = $wpdb->prepare("(g.date_start < %s AND g.date_end > %s)",$msstartdate,$msstartdate);

		$oldevents = get_old_events_filter('AND');

		// check if caller supplied a user_id
    if ($user_id) {
    	// get events only relevant to given userid (WILL or MAY attend)
      $select = "SELECT DISTINCT m.event_id as event_id FROM {$bp->events->table_name_members} m, {$bp->events->table_name} g";
    	$where = " WHERE m.event_id = g.id AND ($range1 OR $range2)";
      $where .= $wpdb->prepare(" AND m.user_id = %s AND m.is_banned = 0 AND (m.is_confirmed=1 OR m.is_maybe=1),$user_id");
    } else {
    	// get every event
      $select = "SELECT DISTINCT g.id as event_id FROM {$bp->events->table_name} g";
    	$where = " WHERE ($range1 OR $range2)";
    }

		if ( !is_site_admin() )
		$where .=  " AND g.status != 'hidden'";

    $where .= " $oldevents";

    $order = " ORDER BY date_start ASC";

    $sql = "$select$where$order";

    $events =  $wpdb->get_results($sql);

    return array( 'events' => $events, 'total' => 10 );
	}

	function get_alphabetically( $limit = null, $page = null ) {
		global $wpdb, $bp;

		if ( $limit && $page )
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		if ( !is_site_admin() )
			$hidden_sql = "WHERE status != 'hidden'";

		$oldevents = get_old_events_filter('AND');

		$paged_events = $wpdb->get_results( $wpdb->prepare( "SELECT id as event_id FROM {$bp->events->table_name} {$hidden_sql} " . $oldevents . " ORDER BY name ASC {$pag_sql}" ) );
		$total_events = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$bp->events->table_name} {$hidden_sql} " . $oldevents . " ORDER BY name ASC", $limit ) );

		return array( 'events' => $paged_events, 'total' => $total_events );
	}

	function get_by_most_forum_topics( $limit = null, $page = null ) {
		global $wpdb, $bp, $bbdb;

		if ( $limit && $page ) {
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );
		}

		$paged_events = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT gm.event_id FROM {$bbdb->forums} AS f, {$bp->events->table_name} AS g LEFT JOIN {$bp->events->table_name_eventmeta} AS gm ON g.id = gm.event_id WHERE (gm.meta_key = 'forum_id' AND gm.meta_value = f.forum_id) AND g.status = 'public' ORDER BY f.topics DESC {$pag_sql}" ) );
		$total_events = $wpdb->get_var( $wpdb->prepare( "SELECT DISTINCT COUNT(gm.event_id) FROM {$bbdb->forums} AS f, {$bp->events->table_name} AS g LEFT JOIN {$bp->events->table_name_eventmeta} AS gm ON g.id = gm.event_id WHERE (gm.meta_key = 'forum_id' AND gm.meta_value = f.forum_id) AND g.status = 'public' ORDER BY f.topics DESC" ) );

		return array( 'events' => $paged_events, 'total' => $total_events );
	}

	function get_by_most_forum_posts( $limit = null, $page = null ) {
		global $wpdb, $bp, $bbdb;

		if ( $limit && $page ) {
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );
		}

		$paged_events = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT gm.event_id FROM {$bbdb->forums} AS f, {$bp->events->table_name} AS g LEFT JOIN {$bp->events->table_name_eventmeta} AS gm ON g.id = gm.event_id WHERE (gm.meta_key = 'forum_id' AND gm.meta_value = f.forum_id) AND g.status = 'public' ORDER BY f.posts DESC {$pag_sql}" ) );
		$total_events = $wpdb->get_var( $wpdb->prepare( "SELECT DISTINCT COUNT(gm.event_id) FROM {$bbdb->forums} AS f, {$bp->events->table_name} AS g LEFT JOIN {$bp->events->table_name_eventmeta} AS gm ON g.id = gm.event_id WHERE (gm.meta_key = 'forum_id' AND gm.meta_value = f.forum_id) AND g.status = 'public' ORDER BY f.posts DESC" ) );

		return array( 'events' => $paged_events, 'total' => $total_events );
	}

	function get_all( $limit = null, $page = null, $only_public = true, $sort_by = false, $order = false ) {
		global $wpdb, $bp;

		$oldevents = get_old_events_filter('AND g.');

		if ( $only_public )
			$public_sql = $wpdb->prepare( " WHERE g.status = 'public'" );

    $oldevent_sql = $wpdb->prepare( $oldevents );

		if ( !is_site_admin() )
			$hidden_sql = $wpdb->prepare( " AND g.status != 'hidden'");

		if ( $limit && $page )
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		if ( $sort_by && $order ) {
			$sort_by = $wpdb->escape( $sort_by );
			$order = $wpdb->escape( $order );
			$order_sql = "ORDER BY g.$sort_by $order";

			switch ( $sort_by ) {
				default:
					$sql = $wpdb->prepare( "SELECT g.id as event_id, g.slug FROM {$bp->events->table_name} g {$public_sql} {$hidden_sql} {$oldevent_sql} {$order_sql} {$pag_sql}" );
					break;
				case 'members':
					$sql = $wpdb->prepare( "SELECT g.id as event_id, g.slug FROM {$bp->events->table_name} g, {$bp->events->table_name_eventmeta} gm WHERE g.id = gm.event_id AND gm.meta_key = 'total_member_count' {$hidden_sql} {$oldevent_sql} {$public_sql} ORDER BY CONVERT(gm.meta_value, SIGNED) {$order} {$pag_sql}" );
					break;
				case 'last_active':
					$sql = $wpdb->prepare( "SELECT g.id as event_id, g.slug FROM {$bp->events->table_name} g, {$bp->events->table_name_eventmeta} gm WHERE g.id = gm.event_id AND gm.meta_key = 'last_activity' {$hidden_sql} {$oldevent_sql} {$public_sql} ORDER BY CONVERT(gm.meta_value, SIGNED) {$order} {$pag_sql}" );
					break;
			}
		} else {
			$sql = $wpdb->prepare( "SELECT g.id as event_id, g.slug FROM {$bp->events->table_name} g {$public_sql} {$hidden_sql} {$oldevent_sql} {$order_sql} {$pag_sql}" );
		}

		return $wpdb->get_results($sql);
	}

	function get_by_letter( $letter, $limit = null, $page = null ) {
		global $wpdb, $bp;

		if ( strlen($letter) > 1 || is_numeric($letter) || !$letter )
			return false;

		if ( !is_site_admin() )
			$hidden_sql = $wpdb->prepare( " AND status != 'hidden'");

		$letter = like_escape( $wpdb->escape( $letter ) );

		if ( $limit && $page ) {
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );
			$total_events = $wpdb->get_var( $wpdb->prepare( "SELECT count(id) FROM {$bp->events->table_name} WHERE name LIKE '$letter%%' {$hidden_sql} ORDER BY name ASC" ) );
		}

		$paged_events = $wpdb->get_results( $wpdb->prepare( "SELECT id as event_id FROM {$bp->events->table_name} WHERE name LIKE '$letter%%' {$hidden_sql} ORDER BY name ASC {$pag_sql}" ) );

		return array( 'events' => $paged_events, 'total' => $total_events );
	}


	function get_random( $limit = null, $page = null ) {
		global $wpdb, $bp;

		$oldevents = get_old_events_filter('AND');
		$oldevent_sql = $wpdb->prepare( $oldevents );

		if ( !is_site_admin() )
			$hidden_sql = $wpdb->prepare( " AND status != 'hidden'");

		if ( $limit && $page ) {
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );
			$total_events = $wpdb->get_var( $wpdb->prepare( "SELECT id as event_id, slug FROM {$bp->events->table_name} WHERE status = 'public' {$hidden_sql} " . $oldevents . " ORDER BY rand()" ) );
		}

		$paged_events = $wpdb->get_results( $wpdb->prepare( "SELECT id as event_id, slug FROM {$bp->events->table_name} WHERE status = 'public' {$hidden_sql} " . $oldevents . " ORDER BY rand() {$pag_sql}" ) );

		return array( 'events' => $paged_events, 'total' => $total_events );
	}

	function delete_all_invites( $event_id ) {
		global $wpdb, $bp;

		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->events->table_name_members} WHERE event_id = %d AND invite_sent = 1", $event_id ) );
	}

	function get_global_forum_topic_count( $type ) {
		global $bbdb, $wpdb, $bp;

		if ( 'unreplied' == $type )
			$bp->events->filter_sql = ' AND t.topic_posts = 1';

		$extra_sql = apply_filters( 'events_total_public_forum_topic_count', $bp->events->filter_sql, $type );

		return $wpdb->get_var( "SELECT count(t.topic_id) FROM {$bbdb->topics} AS t, {$bp->events->table_name} AS g LEFT JOIN {$bp->events->table_name_eventmeta} AS gm ON g.id = gm.event_id WHERE (gm.meta_key = 'forum_id' AND gm.meta_value = t.forum_id) AND g.status = 'public' AND t.topic_status = '0' AND t.topic_sticky != '2' {$extra_sql} " );
	}
}

Class BP_Events_Member {
	var $id;
	var $event_id;
	var $user_id;
	var $inviter_id;
	var $is_admin;
	var $is_mod;
	var $is_banned;
	var $user_title;
	var $date_modified;
	var $is_confirmed;
	var $comments;
	var $invite_sent;

	var $user;

	function bp_events_member( $user_id = false, $event_id = false, $id = false, $populate = true ) {
		if ( $user_id && $event_id && !$id ) {
			$this->user_id = $user_id;
			$this->event_id = $event_id;

			if ( $populate )
				$this->populate();
		}

		if ( $id ) {
			$this->id = $id;

			if ( $populate )
				$this->populate();
		}
	}

	function populate() {
		global $wpdb, $bp;

		if ( $this->user_id && $this->event_id && !$this->id )
			$sql = $wpdb->prepare( "SELECT * FROM {$bp->events->table_name_members} WHERE user_id = %d AND event_id = %d", $this->user_id, $this->event_id );

		if ( $this->id )
			$sql = $wpdb->prepare( "SELECT * FROM {$bp->events->table_name_members} WHERE id = %d", $this->id );

		$member = $wpdb->get_row($sql);

		if ( $member ) {
			$this->id = $member->id;
			$this->event_id = $member->event_id;
			$this->user_id = $member->user_id;
			$this->inviter_id = $member->inviter_id;
			$this->is_admin = $member->is_admin;
			$this->is_mod = $member->is_mod;
			$this->is_banned = $member->is_banned;
			$this->user_title = $member->user_title;
			$this->date_modified = strtotime($member->date_modified);
			$this->is_confirmed = $member->is_confirmed;
			$this->comments = $member->comments;
			$this->invite_sent = $member->invite_sent;

			$this->user = new BP_Core_User( $this->user_id );
		}
	}

	function save() {
		global $wpdb, $bp;

		$this->user_id = apply_filters( 'events_member_user_id_before_save', $this->user_id, $this->id );
		$this->event_id = apply_filters( 'events_member_event_id_before_save', $this->event_id, $this->id );
		$this->inviter_id = apply_filters( 'events_member_inviter_id_before_save', $this->inviter_id, $this->id );
		$this->is_admin = apply_filters( 'events_member_is_admin_before_save', $this->is_admin, $this->id );
		$this->is_mod = apply_filters( 'events_member_is_mod_before_save', $this->is_mod, $this->id );
		$this->is_banned = apply_filters( 'events_member_is_banned_before_save', $this->is_banned, $this->id );
		$this->user_title = apply_filters( 'events_member_user_title_before_save', $this->user_title, $this->id );
		$this->date_modified = apply_filters( 'events_member_date_modified_before_save', $this->date_modified, $this->id );
		$this->is_confirmed = apply_filters( 'events_member_is_confirmed_before_save', $this->is_confirmed, $this->id );
		$this->comments = apply_filters( 'events_member_comments_before_save', $this->comments, $this->id );
		$this->invite_sent = apply_filters( 'events_member_invite_sent_before_save', $this->invite_sent, $this->id );

		do_action( 'events_member_before_save', $this );

		if ( $this->id ) {
			$sql = $wpdb->prepare( "UPDATE {$bp->events->table_name_members} SET inviter_id = %d, is_admin = %d, is_mod = %d, is_banned = %d, user_title = %s, date_modified = FROM_UNIXTIME(%d), is_confirmed = %d, comments = %s, invite_sent = %d WHERE id = %d", $this->inviter_id, $this->is_admin, $this->is_mod, $this->is_banned, $this->user_title, $this->date_modified, $this->is_confirmed, $this->comments, $this->invite_sent, $this->id );
		} else {
			$sql = $wpdb->prepare( "INSERT INTO {$bp->events->table_name_members} ( user_id, event_id, inviter_id, is_admin, is_mod, is_banned, user_title, date_modified, is_confirmed, comments, invite_sent ) VALUES ( %d, %d, %d, %d, %d, %d, %s, FROM_UNIXTIME(%d), %d, %s, %d )", $this->user_id, $this->event_id, $this->inviter_id, $this->is_admin, $this->is_mod, $this->is_banned, $this->user_title, $this->date_modified, $this->is_confirmed, $this->comments, $this->invite_sent );
		}

		if ( !$wpdb->query($sql) )
			return false;

		$this->id = $wpdb->insert_id;

		do_action( 'events_member_after_save', $this );

		return true;
	}

	function promote( $status = 'mod' ) {
		if ( 'mod' == $status ) {
			$this->is_admin = 0;
			$this->is_mod = 1;
			$this->user_title = __( 'Event Mod', 'bp-events' );
		}

		if ( 'admin' == $status ) {
			$this->is_admin = 1;
			$this->is_mod = 0;
			$this->user_title = __( 'Event Admin', 'bp-events' );
		}

		return $this->save();
	}

	function demote() {
		$this->is_mod = 0;
		$this->is_admin = 0;
		$this->user_title = false;

		return $this->save();
	}

	function ban() {
		if ( $this->is_admin )
			return false;

		$this->is_mod = 0;
		$this->is_banned = 1;

		events_update_eventmeta( $this->event_id, 'total_member_count', ( (int) events_get_eventmeta( $this->event_id, 'total_member_count' ) - 1 ) );

		return $this->save();
	}

	function unban() {
		if ( $this->is_admin )
			return false;

		$this->is_banned = 0;

		events_update_eventmeta( $this->event_id, 'total_member_count', ( (int) events_get_eventmeta( $this->event_id, 'total_member_count' ) + 1 ) );

		return $this->save();
	}

	function accept_invite() {
		$this->inviter_id = 0;
		$this->is_confirmed = 1;
		$this->date_modified = time();
	}

	function accept_request() {
		$this->is_confirmed = 1;
		$this->date_modified = time();
	}

	/* Static Functions */

	function delete( $user_id, $event_id, $check_empty = true ) {
		global $wpdb, $bp;

		$delete_result = $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->events->table_name_members} WHERE user_id = %d AND event_id = %d", $user_id, $event_id ) );

		return $delete_result;
	}

	function get_event_ids( $user_id, $limit = false, $page = false ) {
		global $wpdb, $bp;

		if ( $limit && $page )
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		$oldevents = get_old_events_filter('AND g.');

		// If the user is logged in and viewing their own events, we can show hidden and private eventss
		if ( bp_is_home() ) {
			$event_sql = $wpdb->prepare( "SELECT DISTINCT event_id FROM {$bp->events->table_name_members} WHERE user_id = %d AND inviter_id = 0 AND is_banned = 0 " . $oldevents . " {$pag_sql}", $user_id );
			$total_events = $wpdb->get_var( $wpdb->prepare( "SELECT DISTINCT count(event_id) FROM {$bp->events->table_name_members} WHERE user_id = %d AND inviter_id = 0 AND is_banned = 0 " . $oldevents, $user_id ) );
		} else {
			$event_sql = $wpdb->prepare( "SELECT DISTINCT m.event_id FROM {$bp->events->table_name_members} m, {$bp->events->table_name} g WHERE m.event_id = g.id AND g.status != 'hidden' AND m.user_id = %d AND m.inviter_id = 0 AND m.is_banned = 0 " . $oldevents . " {$pag_sql}", $user_id );
			$total_events = $wpdb->get_var( $wpdb->prepare( "SELECT DISTINCT count(m.event_id) FROM {$bp->events->table_name_members} m, {$bp->events->table_name} g WHERE m.event_id = g.id AND g.status != 'hidden' AND m.user_id = %d AND m.inviter_id = 0 AND m.is_banned = 0" . $oldevents, $user_id ) );
		}

		$events = $wpdb->get_col( $event_sql );

		return array( 'events' => $events, 'total' => (int) $total_events );
	}

	function get_recently_joined( $user_id, $limit = false, $page = false, $filter = false ) {
		global $wpdb, $bp;

		if ( $limit && $page )
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		$oldevents = get_old_events_filter('AND g.');

		if ( $filter ) {
			$filter = like_escape( $wpdb->escape( $filter ) );
			$filter_sql = " AND ( g.name LIKE '{$filter}%%' OR g.description LIKE '{$filter}%%' )";
		}

		if ( !bp_is_home() )
			$hidden_sql = " AND g.status != 'hidden'";

		$paged_events = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT m.event_id FROM {$bp->events->table_name_members} m, {$bp->events->table_name} g WHERE m.event_id = g.id{$hidden_sql}{$filter_sql} AND m.user_id = %d AND m.inviter_id = 0 AND m.is_banned = 0 " . $oldevents . " AND m.is_confirmed = 1 ORDER BY m.date_modified DESC {$pag_sql}", $user_id ) );
		$total_events = $wpdb->get_var( $wpdb->prepare( "SELECT DISTINCT count(m.event_id) FROM {$bp->events->table_name_members} m, {$bp->events->table_name} g WHERE m.event_id = g.id{$hidden_sql}{$filter_sql} AND m.user_id = %d AND m.inviter_id = 0 AND m.is_banned = 0 " . $oldevents . " AND m.is_confirmed = 1 ORDER BY m.date_modified DESC", $user_id ) );

		return array( 'events' => $paged_events, 'total' => $total_events );
	}

	function get_most_popular( $user_id, $limit = false, $page = false, $filter = false ) {
		global $wpdb, $bp;

		if ( $limit && $page )
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		$oldevents = get_old_events_filter('AND g.');

		if ( $filter ) {
			like_escape( $wpdb->escape( $filter ) );
			$filter_sql = " AND ( g.name LIKE '{$filter}%%' OR g.description LIKE '{$filter}%%' )";
		}

		if ( !bp_is_home() )
			$hidden_sql = " AND g.status != 'hidden'";

		$paged_events = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT m.event_id FROM {$bp->events->table_name_members} m INNER JOIN {$bp->events->table_name} g ON m.event_id = g.id{$hidden_sql}{$filter_sql} AND m.user_id = %d AND m.inviter_id = 0 AND m.is_banned = 0 " . $oldevents . " AND m.is_confirmed = 1 LEFT JOIN {$bp->events->table_name_eventmeta} gm ON m.event_id = gm.event_id AND gm.meta_key = 'total_member_count' ORDER BY CONVERT( gm.meta_value, SIGNED ) DESC {$pag_sql}", $user_id ) );
		$total_events = $wpdb->get_var( $wpdb->prepare( "SELECT DISTINCT count(m.event_id) FROM {$bp->events->table_name_members} m INNER JOIN {$bp->events->table_name} g ON m.event_id = g.id{$hidden_sql}{$filter_sql} AND m.user_id = %d AND m.inviter_id = 0 AND m.is_banned = 0 " . $oldevents . " AND m.is_confirmed = 1 LEFT JOIN {$bp->events->table_name_eventmeta} gm ON m.event_id = gm.event_id AND gm.meta_key = 'total_member_count' ORDER BY CONVERT( gm.meta_value, SIGNED ) DESC", $user_id ) );

		return array( 'events' => $paged_events, 'total' => $total_events );
	}

	function get_coming_up( $user_id, $limit = false, $page = false, $filter = false ) {
		global $wpdb, $bp;

		if ( $limit && $page )
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		$oldevents = get_old_events_filter('AND g.');

		if ( $filter ) {
			$filter = like_escape( $wpdb->escape( $filter ) );
			$filter_sql = " AND ( g.name LIKE '{$filter}%%' OR g.description LIKE '{$filter}%%' )";
		}

		if ( !bp_is_home() )
			$hidden_sql = " AND g.status != 'hidden'";

		$paged_events = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT m.event_id FROM {$bp->events->table_name_members} m, {$bp->events->table_name} g WHERE m.event_id = g.id{$hidden_sql}{$filter_sql} AND m.user_id = %d AND m.inviter_id = 0 AND m.is_banned = 0 " . $oldevents . " ORDER BY g.date_start ASC {$pag_sql}", $user_id ) );
		$total_events = $wpdb->get_var( $wpdb->prepare( "SELECT DISTINCT count(m.event_id) FROM {$bp->events->table_name_members} m, {$bp->events->table_name} g WHERE m.event_id = g.id{$hidden_sql}{$filter_sql} AND m.user_id = %d AND m.inviter_id = 0 AND m.is_banned = 0 " . $oldevents . " ORDER BY g.date_start ASC", $user_id ) );

		return array( 'events' => $paged_events, 'total' => $total_events );
	}

	function get_recently_active( $user_id, $limit = false, $page = false, $filter = false ) {
		global $wpdb, $bp;

		if ( $limit && $page )
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		$oldevents = get_old_events_filter('AND g.');

		if ( $filter ) {
			$filter = like_escape( $wpdb->escape( $filter ) );
			$filter_sql = " AND ( g.name LIKE '{$filter}%%' OR g.description LIKE '{$filter}%%' )";
		}

		if ( !bp_is_home() )
			$hidden_sql = " AND g.status != 'hidden'";

		$paged_events = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT m.event_id FROM {$bp->events->table_name_members} m LEFT JOIN {$bp->events->table_name_eventmeta} gm ON m.event_id = gm.event_id INNER JOIN {$bp->events->table_name} g ON m.event_id = g.id WHERE gm.meta_key = 'last_activity'{$hidden_sql}{$filter_sql} AND m.user_id = %d AND m.inviter_id = 0 AND m.is_banned = 0 " . $oldevents . " ORDER BY gm.meta_value DESC {$pag_sql}", $user_id ) );
		$total_events = $wpdb->get_var( $wpdb->prepare( "SELECT DISTINCT count(m.event_id) FROM {$bp->events->table_name_members} m LEFT JOIN {$bp->events->table_name_eventmeta} gm ON m.event_id = gm.event_id INNER JOIN {$bp->events->table_name} g ON m.event_id = g.id WHERE gm.meta_key = 'last_activity'{$hidden_sql}{$filter_sql} AND m.user_id = %d AND m.inviter_id = 0 AND m.is_banned = 0 " . $oldevents . " ORDER BY gm.meta_value DESC", $user_id ) );

		return array( 'events' => $paged_events, 'total' => $total_events );
	}

	function get_alphabetically( $user_id, $limit = false, $page = false, $filter = false ) {
		global $wpdb, $bp;

		if ( $limit && $page )
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		$oldevents = get_old_events_filter('AND g.');

		if ( $filter ) {
			$filter = like_escape( $wpdb->escape( $filter ) );
			$filter_sql = " AND ( g.name LIKE '{$filter}%%' OR g.description LIKE '{$filter}%%' )";
		}

		if ( !bp_is_home() )
			$hidden_sql = " AND g.status != 'hidden'";

		$paged_events = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT m.event_id FROM {$bp->events->table_name_members} m, {$bp->events->table_name} g WHERE m.event_id = g.id{$hidden_sql}{$filter_sql} AND m.user_id = %d AND m.inviter_id = 0 AND m.is_banned = 0 " . $oldevents . " ORDER BY g.name ASC {$pag_sql}", $user_id ) );
		$total_events = $wpdb->get_var( $wpdb->prepare( "SELECT DISTINCT count(m.event_id) FROM {$bp->events->table_name_members} m, {$bp->events->table_name} g WHERE m.event_id = g.id{$hidden_sql}{$filter_sql} AND m.user_id = %d AND m.inviter_id = 0 AND m.is_banned = 0 " . $oldevents . " ORDER BY g.name ASC", $user_id ) );

		return array( 'events' => $paged_events, 'total' => $total_events );
	}

	function get_archived( $user_id, $limit = false, $page = false, $filter = false ) {
		global $wpdb, $bp;

		if ( $limit && $page )
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		$oldevents = get_old_events_filter('AND g.', 1);

		if ( $filter ) {
			$filter = like_escape( $wpdb->escape( $filter ) );
			$filter_sql = " AND ( g.name LIKE '{$filter}%%' OR g.description LIKE '{$filter}%%' )";
		}

		if ( !bp_is_home() )
			$hidden_sql = " AND g.status != 'hidden'";

		$paged_events = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT m.event_id FROM {$bp->events->table_name_members} m, {$bp->events->table_name} g WHERE m.event_id = g.id{$hidden_sql}{$filter_sql} AND m.user_id = %d AND m.inviter_id = 0 AND m.is_banned = 0 " . $oldevents . " ORDER BY g.date_start ASC {$pag_sql}", $user_id ) );
		$total_events = $wpdb->get_var( $wpdb->prepare( "SELECT DISTINCT count(m.event_id) FROM {$bp->events->table_name_members} m, {$bp->events->table_name} g WHERE m.event_id = g.id{$hidden_sql}{$filter_sql} AND m.user_id = %d AND m.inviter_id = 0 AND m.is_banned = 0 " . $oldevents . " ORDER BY g.date_start ASC", $user_id ) );

		return array( 'events' => $paged_events, 'total' => $total_events );
	}

	function get_is_admin_of( $user_id, $limit = false, $page = false, $filter = false ) {
		global $wpdb, $bp;

		if ( $limit && $page )
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		$oldevents = get_old_events_filter('AND g.');

		if ( $filter ) {
			$filter = like_escape( $wpdb->escape( $filter ) );
			$filter_sql = " AND ( g.name LIKE '{$filter}%%' OR g.description LIKE '{$filter}%%' )";
		}

		if ( !bp_is_home() )
			$hidden_sql = " AND g.status != 'hidden'";

		$paged_events = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT m.event_id FROM {$bp->events->table_name_members} m, {$bp->events->table_name} g WHERE m.event_id = g.id{$hidden_sql}{$filter_sql} AND m.user_id = %d AND m.inviter_id = 0 AND m.is_banned = 0 AND m.is_admin = 1 " . $oldevents . " ORDER BY date_modified ASC {$pag_sql}", $user_id ) );
		$total_events = $wpdb->get_var( $wpdb->prepare( "SELECT DISTINCT count(m.event_id) FROM {$bp->events->table_name_members} m, {$bp->events->table_name} g WHERE m.event_id = g.id{$hidden_sql}{$filter_sql} AND m.user_id = %d AND m.inviter_id = 0 AND m.is_banned = 0 AND m.is_admin = 1 " . $oldevents . " ORDER BY date_modified ASC", $user_id ) );

		return array( 'events' => $paged_events, 'total' => $total_events );
	}

	function get_is_mod_of( $user_id, $limit = false, $page = false, $filter = false ) {
		global $wpdb, $bp;

		if ( $limit && $page )
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		$oldevents = get_old_events_filter('AND g.');

		if ( $filter ) {
			$filter = like_escape( $wpdb->escape( $filter ) );
			$filter_sql = " AND ( g.name LIKE '{$filter}%%' OR g.description LIKE '{$filter}%%' )";
		}

		if ( !bp_is_home() )
			$hidden_sql = " AND g.status != 'hidden'";

		$paged_events = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT m.event_id FROM {$bp->events->table_name_members} m, {$bp->events->table_name} g WHERE m.event_id = g.id{$hidden_sql}{$filter_sql} AND m.user_id = %d AND m.inviter_id = 0 AND m.is_banned = 0 AND m.is_mod = 1 " . $oldevents . " ORDER BY date_modified ASC {$pag_sql}", $user_id ) );
		$total_events = $wpdb->get_var( $wpdb->prepare( "SELECT DISTINCT count(m.event_id) FROM {$bp->events->table_name_members} m, {$bp->events->table_name} g WHERE m.event_id = g.id{$hidden_sql}{$filter_sql} AND m.user_id = %d AND m.inviter_id = 0 AND m.is_banned = 0 AND m.is_mod = 1 " . $oldevents . " ORDER BY date_modified ASC", $user_id ) );

		return array( 'events' => $paged_events, 'total' => $total_events );
	}

	function total_event_count( $user_id = false ) {
		global $bp, $wpdb;

		if ( !$user_id )
			$user_id = $bp->displayed_user->id;

		$oldevents = get_old_events_filter('AND');

		if ( bp_is_home() ) {
			return $wpdb->get_var( $wpdb->prepare( "SELECT DISTINCT count(event_id) FROM {$bp->events->table_name_members} WHERE user_id = %d AND inviter_id = 0 AND is_banned = 0" . $oldevents, $user_id ) );
		} else {
			return $wpdb->get_var( $wpdb->prepare( "SELECT DISTINCT count(m.event_id) FROM {$bp->events->table_name_members} m, {$bp->events->table_name} g WHERE m.event_id = g.id AND g.status != 'hidden' AND m.user_id = %d AND m.inviter_id = 0 AND m.is_banned = 0" . $oldevents, $user_id ) );
		}
	}

	function get_invites( $user_id ) {
		global $wpdb, $bp;

		$event_ids = $wpdb->get_results( $wpdb->prepare( "SELECT event_id FROM {$bp->events->table_name_members} WHERE user_id = %d and is_confirmed = 0 AND inviter_id != 0 AND invite_sent = 1", $user_id ) );

		return $event_ids;
	}

	function check_has_invite( $user_id, $event_id ) {
		global $wpdb, $bp;

		if ( !$user_id )
			return false;

		return $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$bp->events->table_name_members} WHERE user_id = %d AND event_id = %d AND is_confirmed = 0 AND inviter_id != 0 AND invite_sent = 1", $user_id, $event_id ) );
	}

	function delete_invite( $user_id, $event_id ) {
		global $wpdb, $bp;

		if ( !$user_id )
			return false;

		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->events->table_name_members} WHERE user_id = %d AND event_id = %d AND is_confirmed = 0 AND inviter_id != 0 AND invite_sent = 1", $user_id, $event_id ) );
	}

	function check_is_admin( $user_id, $event_id ) {
		global $wpdb, $bp;

		if ( !$user_id )
			return false;

		return $wpdb->query( $wpdb->prepare( "SELECT id FROM {$bp->events->table_name_members} WHERE user_id = %d AND event_id = %d AND is_admin = 1 AND is_banned = 0", $user_id, $event_id ) );
	}

	function check_is_mod( $user_id, $event_id ) {
		global $wpdb, $bp;

		if ( !$user_id )
			return false;

		return $wpdb->query( $wpdb->prepare( "SELECT id FROM {$bp->events->table_name_members} WHERE user_id = %d AND event_id = %d AND is_mod = 1 AND is_banned = 0", $user_id, $event_id ) );
	}

	function check_is_member( $user_id, $event_id ) {
		global $wpdb, $bp;

		if ( !$user_id )
			return false;

		return $wpdb->query( $wpdb->prepare( "SELECT id FROM {$bp->events->table_name_members} WHERE user_id = %d AND event_id = %d AND is_confirmed = 1 AND is_banned = 0", $user_id, $event_id ) );
	}

	function check_is_banned( $user_id, $event_id ) {
		global $wpdb, $bp;

		if ( !$user_id )
			return false;

		return $wpdb->get_var( $wpdb->prepare( "SELECT is_banned FROM {$bp->events->table_name_members} WHERE user_id = %d AND event_id = %d", $user_id, $event_id ) );
	}

	function check_for_membership_request( $user_id, $event_id ) {
		global $wpdb, $bp;

		if ( !$user_id )
			return false;

		return $wpdb->query( $wpdb->prepare( "SELECT id FROM {$bp->events->table_name_members} WHERE user_id = %d AND event_id = %d AND is_confirmed = 0 AND is_banned = 0 AND inviter_id = 0", $user_id, $event_id ) );
	}

	function get_random_events( $user_id, $total_events = 5 ) {
		global $wpdb, $bp;

		$oldevents = get_old_events_filter('AND g.');

		// If the user is logged in and viewing their random events, we can show hidden and private events
		if ( bp_is_home() ) {
			return $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT event_id FROM {$bp->events->table_name_members} WHERE user_id = %d AND is_confirmed = 1 AND is_banned = 0 " .$oldevents . " ORDER BY rand() LIMIT $total_events", $user_id ) );
		} else {
			return $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT m.event_id FROM {$bp->events->table_name_members} m, {$bp->events->table_name} g WHERE m.event_id = g.id AND g.status != 'hidden' AND m.user_id = %d AND m.is_confirmed = 1 AND m.is_banned = 0 " . $oldevents . " ORDER BY rand() LIMIT $total_events", $user_id ) );
		}
	}

	function get_event_administrator_ids( $event_id ) {
		global $bp, $wpdb;

		return $wpdb->get_results( $wpdb->prepare( "SELECT user_id, date_modified FROM {$bp->events->table_name_members} WHERE event_id = %d AND is_admin = 1 AND is_banned = 0", $event_id ) );
	}

	function get_event_moderator_ids( $event_id ) {
		global $bp, $wpdb;

		return $wpdb->get_results( $wpdb->prepare( "SELECT user_id, date_modified FROM {$bp->events->table_name_members} WHERE event_id = %d AND is_mod = 1 AND is_banned = 0", $event_id ) );
	}

	function get_all_membership_request_user_ids( $event_id ) {
		global $bp, $wpdb;

		return $wpdb->get_col( $wpdb->prepare( "SELECT user_id FROM {$bp->events->table_name_members} WHERE event_id = %d AND is_confirmed = 0 AND inviter_id = 0", $event_id ) );
	}

	function get_all_for_event( $event_id, $limit = false, $page = false, $exclude_admins_mods = true, $exclude_banned = true ) {
		global $bp, $wpdb;

		if ( $limit && $page )
			$pag_sql = $wpdb->prepare( "LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		if ( $exclude_admins_mods )
			$exclude_sql = $wpdb->prepare( "AND is_admin = 0 AND is_mod = 0" );

		if ( $exclude_banned )
			$banned_sql = $wpdb->prepare( " AND is_banned = 0" );

		$members = $wpdb->get_results( $wpdb->prepare( "SELECT user_id, date_modified FROM {$bp->events->table_name_members} WHERE event_id = %d AND is_confirmed = 1 {$banned_sql} {$exclude_sql} {$pag_sql}", $event_id ) );

		if ( !$members )
			return false;

		if ( !isset($pag_sql) )
			$total_member_count = count($members);
		else
			$total_member_count = $wpdb->get_var( $wpdb->prepare( "SELECT count(user_id) FROM {$bp->events->table_name_members} WHERE event_id = %d AND is_confirmed = 1 {$banned_sql} {$exclude_sql}", $event_id ) );

		return array( 'members' => $members, 'count' => $total_member_count );
	}

	function delete_all_for_user( $user_id ) {
		global $wpdb, $bp;

		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->events->table_name_members} WHERE user_id = %d", $user_id ) );
	}
}

/**
 * API for creating event extensions without having to hardcode the content into
 * the theme.
 *
 * This class must be extended for each event extension and the following methods overridden:
 *
 * BP_Event_Extension::widget_display(), BP_Event_Extension::display(),
 * BP_Event_Extension::edit_screen_save(), BP_Event_Extension::edit_screen(),
 * BP_Event_Extension::create_screen_save(), BP_Event_Extension::create_screen()
 *
 * @package BuddyPress
 * @subpackage Events
 * @since 1.1
 */
class BP_Event_Extension {
	var $name = false;
	var $slug = false;

	/* Will this extension be visible to non-members of a event? Options: public/private */
	var $visibility = 'public';

	var $create_step_position = 81;
	var $nav_item_position = 81;

	var $enable_create_step = true;
	var $enable_nav_item = true;
	var $enable_edit_item = true;

	var $nav_item_name = false;

	var $display_hook = 'events_custom_event_boxes';
	var $template_file = 'plugin-template';

	// Methods you should override

	function display() {
		die( 'function BP_Event_Extension::display() must be over-ridden in a sub-class.' );
	}

	function widget_display() {
		die( 'function BP_Event_Extension::widget_display() must be over-ridden in a sub-class.' );
	}

	function edit_screen() {
		die( 'function BP_Event_Extension::edit_screen() must be over-ridden in a sub-class.' );
	}

	function edit_screen_save() {
		die( 'function BP_Event_Extension::edit_screen_save() must be over-ridden in a sub-class.' );
	}

	function create_screen() {
		die( 'function BP_Event_Extension::create_screen() must be over-ridden in a sub-class.' );
	}

	function create_screen_save() {
		die( 'function BP_Event_Extension::create_screen_save() must be over-ridden in a sub-class.' );
	}

	// Private Methods

	function _register() {
		global $bp;

		if ( $this->enable_create_step ) {
			/* Insert the event creation step for the new event extension */
			$bp->events->event_creation_steps[$this->slug] = array( 'name' => $this->name, 'slug' => $this->slug, 'position' => $this->create_step_position );

			/* Attach the event creation step display content action */
			add_action( 'events_custom_create_steps', array( &$this, 'create_screen' ) );

			/* Attach the event creation step save content action */
			add_action( 'events_create_event_step_save_' . $this->slug, array( &$this, 'create_screen_save' ) );
		}

		/* Construct the admin edit tab for the new event extension */
		if ( $this->enable_edit_item ) {
			add_action( 'events_admin_tabs', create_function( '$current, $event_slug', 'if ( "' . attribute_escape( $this->slug ) . '" == $current ) $selected = " class=\"current\""; echo "<li{$selected}><a href=\"' . $bp->root_domain . '/' . $bp->events->slug . '/{$event_slug}/admin/' . attribute_escape( $this->slug ) . '\">' . attribute_escape( $this->name ) . '</a></li>";' ), 10, 2 );

			/* Catch the edit screen and forward it to the plugin template */
			if ( $bp->current_component == $bp->events->slug && 'admin' == $bp->current_action && $this->slug == $bp->action_variables[0] ) {
				add_action( 'wp', array( &$this, 'edit_screen_save' ) );
				add_action( 'events_custom_edit_steps', array( &$this, 'edit_screen' ) );

				bp_core_load_template( apply_filters( 'events_template_event_admin', 'events/single/admin' ) );
			}
		}

		/* When we are viewing a single event, add the event extension nav item */
		if ( $this->visbility == 'public' || ( $this->visbility != 'public' && $bp->events->current_event->user_has_access ) ) {
			if ( $this->enable_nav_item ) {
				if ( $bp->current_component == $bp->events->slug && $bp->is_single_item )
					bp_core_new_subnav_item( array( 'name' => ( !$this->nav_item_name ) ? $this->name : $this->nav_item_name, 'slug' => $this->slug, 'parent_slug' => BP_EVENTS_SLUG, 'parent_url' => bp_get_event_permalink( $bp->events->current_event ) . '/', 'position' => $this->nav_item_position, 'item_css_id' => 'nav-' . $this->slug, 'screen_function' => array( &$this, '_display_hook' ), 'user_has_access' => $this->enable_nav_item ) );

				/* When we are viewing the extension display page, set the title and options title */
				if ( $bp->current_component == $bp->events->slug && $bp->is_single_item && $bp->current_action == $this->slug ) {
					add_action( 'bp_template_content_header', create_function( '', 'echo "' . attribute_escape( $this->name ) . '";' ) );
			 		add_action( 'bp_template_title', create_function( '', 'echo "' . attribute_escape( $this->name ) . '";' ) );
				}
			}

			/* Hook the event home widget */
			if ( $bp->current_component == $bp->events->slug && $bp->is_single_item && ( !$bp->current_action || 'home' == $bp->current_action ) )
				add_action( $this->display_hook, array( &$this, 'widget_display' ) );
		}
	}

	function _display_hook() {
		add_action( 'bp_template_content', array( &$this, 'display' ) );
		bp_core_load_template( $this->template_file );
	}
}

function bp_register_event_extension( $event_extension_class ) {
	global $bp;

	if ( !class_exists( $event_extension_class ) )
		return false;

	/* Register the event extension on the plugins_loaded action so we have access to all plugins */
	add_action( 'plugins_loaded', create_function( '', '$extension = new ' . $event_extension_class . '; add_action( "wp", array( &$extension, "_register" ), 2 );' ) );}


?>