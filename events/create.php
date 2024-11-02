<?php get_header() ?>

	<div class="content-header">
		<ul class="content-header-nav">
			<?php bp_event_creation_tabs(); ?>
		</ul>
	</div>

	<div id="content">
		<h2><?php _e( 'Create a Event', 'bp-events' ) ?> <?php bp_event_creation_stage_title() ?></h2>
		<?php do_action( 'template_notices' ) // (error/success feedback) ?>

		<?php do_action( 'bp_before_event_creation_content' ) ?>

	  <form action="<?php bp_event_creation_form_action() ?>" method="post" id="create-event-form" class="standard-form" enctype="multipart/form-data">

			<!-- Event creation step 1: Basic event details -->
			<?php if ( bp_is_event_creation_step( 'event-details' ) ) : ?>

				<label for="event-name"><?php _e('* Event Name', 'bp-events') ?> <span class="required"><?php _e('(required)', 'buddypress') ?></span></label>
				<input type="text" name="event-name" id="event-name" value="<?php bp_new_event_name() ?>" />

				<label for="event-desc"><?php _e('* Event Description', 'bp-events') ?> <span class="required"><?php _e('(required)', 'buddypress') ?></label>
				<textarea name="event-desc" id="event-desc"><?php bp_new_event_description() ?></textarea>

				<label for="event-news"><?php _e('Recent News', 'bp-events') ?></label>
				<textarea name="event-news" id="event-news"><?php bp_new_event_news() ?></textarea>

				<label for="event-tagline"><?php _e('Tagline', 'bp-events') ?></label>
				<input type="text" name="event-tagline" id="event-tagline" value="<?php bp_new_event_tagline() ?>" />

				<label for="event-location"><?php _e('Location', 'bp-events') ?></label>
				<input type="text" name="event-location" id="event-location" value="<?php bp_new_event_location() ?>" />

				<?php bp_event_groups_dropdown( $_POST['event-group'] ) ?>

				<?php do_action( 'events_custom_event_fields_editable' ) ?>

				<?php wp_nonce_field( 'events_create_save_event-details' ) ?>

			<?php endif; ?>

			<!-- Event creation step 2: Event Dates -->
			<?php if ( bp_is_event_creation_step( 'event-dates' ) ) : ?>

				<?php do_action( 'bp_before_event_dates_creation_step' ); ?>

       	<div class="checkbox">
					<label><input type="checkbox" name="event-allday" id="event-allday" value="1"<?php if ( bp_get_new_event_allday() ) { ?> checked="checked"<?php } ?> /> <?php _e('This is an all day event', 'bp-events') ?></label>
				</div>

				<label for="startdate"><?php _e( 'Event start date and time', 'bp-events' ) ?></label>
				<?php bp_event_print_datepicker( bp_event_get_startdate(), 'event-startdate' ) ?>

        <label for="enddate"><?php _e( 'Event end date and time', 'bp-events' ) ?></label>
        <?php bp_event_print_datepicker( bp_event_get_enddate(), 'event-enddate' ) ?>

				<?php do_action( 'bp_after_event_dates_creation_step' ); ?>

				<?php wp_nonce_field( 'events_create_save_event-dates' ) ?>

			<?php endif; ?>

			<!-- Event creation step 3: Event settings -->
			<?php if ( bp_is_event_creation_step( 'event-settings' ) ) : ?>

				<?php do_action( 'bp_before_event_settings_creation_step' ); ?>

				<?php if ( function_exists('bp_wire_install') ) : ?>
					<?php if ( get_option('events_user_wire') ) : ?>
					<div class="checkbox">
						<label><input type="checkbox" name="event-show-wire" id="event-show-wire" value="1"<?php if ( bp_get_new_event_enable_wire() ) { ?> checked="checked"<?php } ?> /> <?php _e('Enable comment wire', 'bp-events') ?></label>
					</div>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( function_exists('bp_forums_setup') ) : ?>
					<?php if ( get_option('events_user_forum') ) : ?>
						<?php if ( bp_forums_is_installed_correctly() ) : ?>
							<div class="checkbox">
								<label><input type="checkbox" name="event-show-forum" id="event-show-forum" value="1"<?php if ( bp_get_new_event_enable_forum() ) { ?> checked="checked"<?php } ?> /> <?php _e('Enable discussion forum', 'bp-events') ?></label>
							</div>
						<?php else : ?>
							<?php if ( is_site_admin() ) : ?>
								<div class="checkbox">
									<label><input type="checkbox" disabled="disabled" name="disabled" id="disabled" value="0" /> <?php printf( __('<strong>Attention Site Admin:</strong> Event forums require the <a href="%s">correct setup and configuration</a> of a bbPress installation.', 'bp-events' ), $bp->root_domain . '/wp-admin/admin.php?page=' . BP_PLUGIN_DIR . '/bp-forums/bp-forums-admin.php' ) ?></label>
								</div>
							<?php endif; ?>
						<?php endif; ?>
					<?php endif; ?>
				<?php endif; ?>

				<h3><?php _e( 'Privacy Options', 'bp-events' ); ?></h3>

				<div class="radio">
					<label><input type="radio" name="event-status" value="public"<?php if ( 'public' == bp_get_new_event_status() || !bp_get_new_event_status() ) { ?> checked="checked"<?php } ?> />
						<strong><?php _e( 'This is a public event', 'bp-events' ) ?></strong>
						<ul>
							<li><?php _e( 'Any site member can join this event.', 'bp-events' ) ?></li>
							<li><?php _e( 'This event will be listed in the events directory and in search results.', 'bp-events' ) ?></li>
							<li><?php _e( 'Event content and activity will be visible to any site member.', 'bp-events' ) ?></li>
						</ul>
					</label>

					<label><input type="radio" name="event-status" value="private"<?php if ( 'private' == bp_get_new_event_status() ) { ?> checked="checked"<?php } ?> />
						<strong><?php _e( 'This is a private event', 'bp-events' ) ?></strong>
						<ul>
							<li><?php _e( 'Only users who request membership and are accepted can join the event.', 'bp-events' ) ?></li>
							<li><?php _e( 'This event will be listed in the events directory and in search results.', 'bp-events' ) ?></li>
							<li><?php _e( 'Event content and activity will only be visible to members of the event.', 'bp-events' ) ?></li>
						</ul>
					</label>

					<label><input type="radio" name="event-status" value="hidden"<?php if ( 'hidden' == bp_get_new_event_status() ) { ?> checked="checked"<?php } ?> />
						<strong><?php _e('This is a hidden event', 'bp-events') ?></strong>
						<ul>
							<li><?php _e( 'Only users who are invited can join the event.', 'bp-events' ) ?></li>
							<li><?php _e( 'This event will not be listed in the events directory or search results.', 'bp-events' ) ?></li>
							<li><?php _e( 'Event content and activity will only be visible to members of the event.', 'bp-events' ) ?></li>
						</ul>
					</label>
				</div>

				<?php do_action( 'bp_after_event_settings_creation_step' ); ?>

				<?php wp_nonce_field( 'events_create_save_event-settings' ) ?>

			<?php endif; ?>

			<!-- Event creation step 4: Avatar Uploads -->
			<?php if ( bp_is_event_creation_step( 'event-avatar' ) ) : ?>

				<?php do_action( 'bp_before_event_avatar_creation_step' ); ?>

				<?php if ( !bp_get_avatar_admin_step() ) : ?>

					<div class="left-menu">
						<?php bp_new_event_avatar() ?>
					</div>

					<div class="main-column">
						<p><?php _e("Upload an image to use as an avatar for this event. The image will be shown on the main event page, and in search results.", 'bp-events') ?></p>

						<p>
							<input type="file" name="file" id="file" />
							<input type="submit" name="upload" id="upload" value="<?php _e( 'Upload Image', 'bp-events' ) ?>" />
							<input type="hidden" name="action" id="action" value="bp_avatar_upload" />
						</p>

						<p><?php _e( 'To skip the avatar upload process, hit the "Next Step" button.', 'bp-events' ) ?></p>
					</div>

				<?php endif; ?>

				<?php if ( 'crop-image' == bp_get_avatar_admin_step() ) : ?>

					<h3><?php _e( 'Crop Event Avatar', 'bp-events' ) ?></h3>

					<img src="<?php bp_avatar_to_crop() ?>" id="avatar-to-crop" class="avatar" alt="<?php _e( 'Avatar to crop', 'bp-events' ) ?>" />

					<div id="avatar-crop-pane">
						<img src="<?php bp_avatar_to_crop() ?>" id="avatar-crop-preview" class="avatar" alt="<?php _e( 'Avatar preview', 'bp-events' ) ?>" />
					</div>

					<input type="submit" name="avatar-crop-submit" id="avatar-crop-submit" value="<?php _e( 'Crop Image', 'bp-events' ) ?>" />

					<input type="hidden" name="image_src" id="image_src" value="<?php bp_avatar_to_crop_src() ?>" />
					<input type="hidden" name="upload" id="upload" />
					<input type="hidden" id="x" name="x" />
					<input type="hidden" id="y" name="y" />
					<input type="hidden" id="w" name="w" />
					<input type="hidden" id="h" name="h" />

				<?php endif; ?>

				<p><?php _e( 'To skip the avatar upload process, hit the "Next Step" button.', 'bp-events' ) ?></p>


				<?php do_action( 'bp_after_event_avatar_creation_step' ); ?>

				<?php wp_nonce_field( 'events_create_save_event-avatar' ) ?>

			<?php endif; ?>

			<!-- Event creation step 5: Invite friends to event -->
			<?php if ( bp_is_event_creation_step( 'event-invites' ) ) : ?>

				<?php do_action( 'bp_before_event_invites_creation_step' ); ?>

				<div class="left-menu">

					<h4><?php _e( 'Select Friends', 'bp-events' ) ?> <img id="ajax-loader" src="<?php echo $bp->events->image_base ?>/ajax-loader.gif" height="7" alt="Loading" style="display: none;" /></h4>

					<div class="info-event">
						<div id="invite-list">
							<ul>
								<?php bp_new_event_invite_friend_list() ?>
							</ul>

							<?php wp_nonce_field( 'events_invite_uninvite_user', '_wpnonce_invite_uninvite_user' ) ?>
						</div>
					</div>

				</div>

				<div class="main-column">

					<div id="message" class="info">
						<p><?php _e('Select people to invite from your friends list.', 'bp-events'); ?></p>
					</div>

					<?php /* The ID 'friend-list' is important for AJAX support. */ ?>
					<ul id="friend-list" class="item-list">
					<?php if ( bp_event_has_invites() ) : ?>

						<?php while ( bp_event_invites() ) : bp_event_the_invite(); ?>

							<li id="<?php bp_event_invite_item_id() ?>">
								<?php bp_event_invite_user_avatar() ?>

								<h4><?php bp_event_invite_user_link() ?></h4>
								<span class="activity"><?php bp_event_invite_user_last_active() ?></span>

								<div class="action">
									<a class="remove" href="<?php bp_event_invite_user_remove_invite_url() ?>" id="<?php bp_event_invite_item_id() ?>"><?php _e( 'Remove Invite', 'bp-events' ) ?></a>
								</div>
							</li>

						<?php endwhile; ?>

						<?php wp_nonce_field( 'events_send_invites', '_wpnonce_send_invites' ) ?>
					<?php endif; ?>
					</ul>

				</div>

				<?php wp_nonce_field( 'events_create_save_event-invites' ) ?>

				<?php do_action( 'bp_after_event_invites_creation_step' ); ?>

			<?php endif; ?>

			<?php do_action( 'events_custom_create_steps' ) // Allow plugins to add custom event creation steps ?>

			<?php do_action( 'bp_before_event_creation_step_buttons' ); ?>

			<div id="previous-next">
				<!-- Previous Button -->
				<?php if ( !bp_is_first_event_creation_step() ) : ?>
					<input type="button" value="&larr; <?php _e('Previous Step', 'bp-events') ?>" id="event-creation-previous" name="previous" onclick="location.href='<?php bp_event_creation_previous_link() ?>'" />
				<?php endif; ?>

				<!-- Next Button -->
				<?php if ( !bp_is_last_event_creation_step() && !bp_is_first_event_creation_step() ) : ?>
					<input type="submit" value="<?php _e('Next Step', 'bp-events') ?> &rarr;" id="event-creation-next" name="save" />
				<?php endif;?>

				<!-- Create Button -->
				<?php if ( bp_is_first_event_creation_step() ) : ?>
					<input type="submit" value="<?php _e('Create Event and Continue', 'bp-events') ?> &rarr;" id="event-creation-create" name="save" />
				<?php endif; ?>

				<!-- Finish Button -->
				<?php if ( bp_is_last_event_creation_step() ) : ?>
					<input type="submit" value="<?php _e('Finish', 'bp-events') ?> &rarr;" id="event-creation-finish" name="save" />
				<?php endif; ?>
			</div>

			<?php do_action( 'bp_after_event_creation_step_buttons' ); ?>

			<!-- Don't leave out this hidden field -->
			<input type="hidden" name="event_id" id="event_id" value="<?php bp_new_event_id() ?>" />
		</form>

		<?php do_action( 'bp_after_event_creation_content' ) ?>

	</div>

<?php get_footer() ?>