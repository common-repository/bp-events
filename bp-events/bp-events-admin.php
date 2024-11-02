<?php

function events_admin_settings() { 
	
	if ( isset( $_POST['events_admin_delete']) && isset( $_POST['allevents'] ) ) {
		if ( !check_admin_referer('bp-events-admin') )
			return false;
		
		$errors = false;
		foreach ( $_POST['allevents'] as $event_id ) {
			$event = new BP_Events_Event( $event_id );
			if ( !$event->delete() ) {
				$errors = true;
			}
		}
		
		if ( $errors ) {
			$message = __( 'There were errors when deleting events, please try again', 'bp-events' );
			$type = 'error';
		} else {
			$message = __( 'Events deleted successfully', 'bp-events' );
			$type = 'updated';
		}
	}
?>
	<?php if ( isset( $message ) ) { ?>
		<div id="message" class="<?php echo $type ?> fade">
			<p><?php echo $message ?></p>
		</div>
	<?php } ?>

	<div class="wrap" style="position: relative">
		<h2><?php _e( 'Events', 'bp-events' ) ?></h2>
	
		<form id="wpmu-search" method="post" action="">
			<input type="text" size="17" value="<?php echo attribute_escape( stripslashes( $_REQUEST['s'] ) ); ?>" name="s" />
			<input id="post-query-submit" class="button" type="submit" value="<?php _e( 'Search Events', 'bp-events' ) ?>" />
		</form>
		
		<?php if ( bp_has_site_events( 'type=active&per_page=10' ) ) : ?>
			<form id="bp-event-admin-list" method="post" action="">
				<div class="tablenav">
					<div class="tablenav-pages">
						<?php bp_site_events_pagination_count() ?> <?php bp_site_events_pagination_links() ?>
					</div>
					<div class="alignleft">
						<input class="button-secondary delete" type="submit" name="events_admin_delete" value="<?php _e( 'Delete', 'bp-events' ) ?>" onclick="if ( !confirm('<?php _e( 'Are you sure?', 'bp-events' ) ?>') ) return false"/>
						<?php wp_nonce_field('bp-events-admin') ?>
						<br class="clear"/>
					</div>
				</div>
				
				<br class="clear"/>
				
				<?php if ( isset( $_REQUEST['s'] ) && $_REQUEST['s'] != '' ) { ?>
					<p><?php echo sprintf( __( 'Events matching: "%s"', 'bp-events' ), $_REQUEST['s'] ) ?></p>
				<?php } ?>


				<table class="widefat" cellspacing="3" cellpadding="3">
					<thead>
						<tr>
							<th class="check-column" scope="col">
								<input id="event_check_all" type="checkbox" value="0" name="event_check_all" onclick="if ( jQuery(this).attr('checked') ) { jQuery('#event-list input[@type=checkbox]').attr('checked', 'checked'); } else { jQuery('#event-list input[@type=checkbox]').attr('checked', ''); }" />
							</th>
							<th scope="col">
							</th>
							<th scope="col">
									ID
							</th>
							<th scope="col">
									<?php _e( 'Name', 'bp-events' ) ?>
							</th>
							<th scope="col">
									<?php _e( 'Description', 'bp-events' ) ?>
							</th>
							<th scope="col">
									<?php _e( 'Type', 'bp-events' ) ?>
							</th>
							<th scope="col">
									<?php _e( 'Members', 'bp-events' ) ?>
							</th>
							<th scope="col">
									<?php _e( 'Created', 'bp-events' ) ?>
							</th>
							<th scope="col">
									<?php _e( 'Last Active', 'bp-events' ) ?>
							</th>
							<th scope="col">
							</th>
						</tr>
					</thead>
					<tbody id="event-list" class="list:events event-list">
					<?php $counter = 0 ?>
					<?php while ( bp_site_events() ) : bp_the_site_event(); ?>
						<tr<?php if ( 1 == $counter % 2 ) { ?> class="alternate"<?php }?>>
							<th class="check-column" scope="row">
								<input id="event_<?php bp_the_site_event_id() ?>" type="checkbox" value="<?php bp_the_site_event_id() ?>" name="allevents[<?php bp_the_site_event_id() ?>]" />
							</th>
							<td><?php bp_the_site_event_avatar_mini() ?></td>
							<td><?php bp_the_site_event_id() ?></td>
							<td><a href="<?php bp_the_site_event_link() ?>"><?php bp_the_site_event_name() ?></a></td>
							<td><?php bp_the_site_event_description_excerpt() ?></td>
							<td><?php bp_the_site_event_type() ?></td>
							<td><?php bp_the_site_event_member_count() ?></td>
							<td><?php bp_the_site_event_date_created() ?></td>
							<td><?php bp_the_site_event_last_active() ?></td>
							<td><a href="<?php bp_the_site_event_link() ?>/admin"><?php _e( 'Edit', 'bp-events') ?></a></td>
						</tr>
						<?php $counter++ ?>
					<?php endwhile; ?>
					</tbody>
				</table>	

		<?php else: ?>

			<div id="message" class="info">
				<p><?php _e( 'No events found.', 'bp-events' ) ?></p>
			</div>

		<?php endif; ?>

		<?php bp_the_site_event_hidden_fields() ?>
		</form>
	</div>
<?php 
}

/**
 * events_settings_admin_settings()
 *
 */
function events_settings_admin_settings() {
	global $bp;

	if ( isset( $_POST['events_savesettings']) ) {
		if ( !check_admin_referer('bp-events-admin') )
			return false;

		$errors = false;

		update_option( 'events_user_forum', $_POST['createforum'] );
		update_option( 'events_user_wire', $_POST['createwire'] );
		update_option( 'events_handle_old_events', $_POST['old'] );
		update_option( 'events_what_are_old_events', $_POST['handleold'] );

		$updated = true;

		if ( $errors ) {
			$message = __( 'There were errors updating event settings, please try again', 'bp-events' );
			$type = 'error';
		} else {
			$message = __( 'Event settings successfully updated', 'bp-events' );
			$type = 'updated';
		}
	}
	
	$oldevents = get_option('events_what_are_old_events');
	
  ?>
	<div class="wrap" style="position: relative">
		<h2><?php _e( 'Event Settings', 'bp-events' ) ?></h2>

		<form id="event-settings-admin-settings" method="post" action="">

			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="target_uri"><?php _e( 'Allow users to have a forum option for events', 'bp-events' ) ?></label></th>
					<td>
	        	<input type="radio" name="createforum" value="1" <?php if ( 1 == get_option('events_user_forum') ) { ?>checked="checked" <?php } ?>/> <?php _e( 'Yes', 'bp-events') ?><br />
	          <input type="radio" name="createforum" value="0" <?php if ( 0 == get_option('events_user_forum') ) { ?>checked="checked" <?php } ?>/> <?php _e( 'No', 'bp-events') ?><br />
	          <label><?php _e( 'If yes, users will have a "create forum" box on the event create/edit screens', 'bp-events' ) ?></label>
					</td>
				</tr>
			</table>

			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="target_uri"><?php _e( 'Allow users to have a wire option for events', 'bp-events' ) ?></label></th>
					<td>
	          <input type="radio" name="createwire" value="1" <?php if ( 1 == get_option('events_user_wire') ) { ?>checked="checked" <?php } ?>/> <?php _e( 'Yes', 'bp-events') ?><br />
	          <input type="radio" name="createwire" value="0" <?php if ( 0 == get_option('events_user_wire') ) { ?>checked="checked" <?php } ?>/> <?php _e( 'No', 'bp-events') ?><br />
	          <label><?php _e( 'If yes, users will have a "create wire" box on the event create/edit screens', 'bp-events' ) ?></label>
					</td>
				</tr>
			</table>

      <!-- HANDLE OLD EVENTS SECTION -->
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="target_uri"><?php _e( 'What to do with past events', 'bp-events' ) ?></label></th>
					<td>
	          <input type="radio" name="old" value="1" <?php if ( 1 == get_option('events_handle_old_events') ) { ?>checked="checked" <?php } ?>/> <?php _e( 'Show past events. (All events remain seen on all the event pages.)', 'bp-events' ) ?><br>
	          <input type="radio" name="old" value="2" <?php if ( 2 == get_option('events_handle_old_events') ) { ?>checked="checked" <?php } ?>/> <?php _e( 'Archive past events. (Past events can be listed by using the archive sort tab.)', 'bp-events' ) ?><br>
	            <br>
					  <label><?php _e( 'What do you consider to be a past event?', 'bp-events' ) ?></label><br>
	          <select name="handleold" id="handleold">
              <option value="1" <?php if ( 1 == $oldevents ) { ?>SELECTED <?php } ?>/>One day old</option>
              <option value="2" <?php if ( 2 == $oldevents ) { ?>SELECTED <?php } ?>/>One week old</option>
              <option value="3" <?php if ( 3 == $oldevents ) { ?>SELECTED <?php } ?>/>One month old</option>
              <option value="4" <?php if ( 4 == $oldevents ) { ?>SELECTED <?php } ?>/>One year old</option>
	          </select>
					</td>
				</tr>
			</table>
						
			<p class="submit">
				<input type="submit" name="events_savesettings" value="<?php _e('Save Settings', 'bp-events') ?>"/>
			</p>
			
			<?php wp_nonce_field('bp-events-admin') ?>
			
		</form>	
	</div>
<?php
}
?>