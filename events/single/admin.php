<?php get_header() ?>

	<?php if ( bp_has_events() ) : while ( bp_events() ) : bp_the_event(); ?>

		<div class="content-header">
			<ul class="content-header-nav">
				<?php bp_event_admin_tabs(); ?>
			</ul>
		</div>

		<div id="content">

				<?php do_action( 'template_notices' ) // (error/success feedback) ?>

				<?php do_action( 'bp_before_event_admin_content' ) ?>

				<form action="<?php bp_event_admin_form_action() ?>" name="event-settings-form" id="event-settings-form" class="standard-form" method="post" enctype="multipart/form-data">

					<?php /* Edit Event Details */ ?>
					<?php if ( bp_is_event_admin_screen( 'edit-details' ) ) : ?>

						<h2><?php _e( 'Edit Details', 'bp-events' ); ?></h2>

						<?php do_action( 'bp_before_event_details_admin' ); ?>

						<label for="event-name">* <?php _e( 'Event Name', 'bp-events' ) ?></label>
						<input type="text" name="event-name" id="event-name" value="<?php bp_event_name() ?>" />

						<label for="event-desc">* <?php _e( 'Event Description', 'bp-events' ) ?></label>
						<textarea name="event-desc" id="event-desc"><?php bp_event_description_editable() ?></textarea>

						<label for="event-news"><?php _e( 'Recent News', 'bp-events' ) ?></label>
						<textarea name="event-news" id="event-news"><?php bp_event_news_editable() ?></textarea>

						<label for="event-tagline"><?php _e('Tagline', 'bp-events') ?></label>
						<input type="text" name="event-tagline" id="event-tagline" value="<?php bp_new_event_tagline() ?>" />

						<label for="event-location"><?php _e('Location', 'bp-events') ?></label>
						<input type="text" name="event-location" id="event-location" value="<?php bp_new_event_location() ?>" />

						<?php bp_event_groups_dropdown( bp_event_get_group() ) ?>

						<?php do_action( 'events_custom_event_fields_editable' ) ?>

						<p>
							<label for="event-notifiy-members"><?php _e( 'Notify event members of changes via email', 'bp-events' ); ?></label>
							<input type="radio" name="event-notify-members" value="1" /> <?php _e( 'Yes', 'bp-events' ); ?>&nbsp;
							<input type="radio" name="event-notify-members" value="0" checked="checked" /> <?php _e( 'No', 'bp-events' ); ?>&nbsp;
						</p>

						<?php do_action( 'bp_after_event_details_admin' ); ?>

						<p><input type="submit" value="<?php _e( 'Save Changes', 'bp-events' ) ?> &raquo;" id="save" name="save" /></p>
						<?php wp_nonce_field( 'events_edit_event_details' ) ?>

					<?php endif; ?>

					<?php /* Manage Event Dates and Times */ ?>
					<?php if ( bp_is_event_admin_screen( 'event-dates' ) ) : ?>

						<h2><?php _e( 'Event Dates', 'bp-events' ); ?></h2>

						<?php do_action( 'bp_before_event_dates_admin' ); ?>

		       	<p class="checkbox">
							<label><input type="checkbox" name="event-allday" id="event-allday" value="1"<?php if ( bp_get_new_event_allday() ) { ?> checked="checked"<?php } ?> /> <?php _e('This is an all day event', 'bp-events') ?></label>
						</p>

						<p>
							<label for="startdate"><?php _e( 'Event start date and time', 'bp-events' ) ?></label>
						  <?php bp_event_print_datepicker( bp_event_get_startdate(), 'event-startdate' ) ?>
						</p>

						<p>
			        <label for="enddate"><?php _e( 'Event end date and time', 'bp-events' ) ?></label>
			        <?php bp_event_print_datepicker( bp_event_get_enddate(), 'event-enddate' ) ?>
            </p>

						<?php do_action( 'bp_after_event_dates_admin' ); ?>

						<p><input type="submit" value="<?php _e( 'Save Changes', 'bp-events' ) ?> &raquo;" id="save" name="save" /></p>
						<?php wp_nonce_field( 'events_edit_event_dates' ) ?>

					<?php endif; ?>

					<?php /* Manage Event Settings */ ?>
					<?php if ( bp_is_event_admin_screen( 'event-settings' ) ) : ?>

						<h2><?php _e( 'Event Settings', 'bp-events' ); ?></h2>

						<?php do_action( 'bp_before_event_settings_admin' ); ?>

						<?php if ( function_exists('bp_wire_install') ) : ?>

					    <?php if ( get_option('events_user_wire') ) : ?>

								<div class="checkbox">
									<label><input type="checkbox" name="event-show-wire" id="event-show-wire" value="1"<?php bp_event_show_wire_setting() ?>/> <?php _e( 'Enable comment wire', 'bp-events' ) ?></label>
								</div>

					    <?php endif; ?>

						<?php endif; ?>

						<?php if ( function_exists('bp_forums_setup') ) : ?>

							<?php if ( bp_forums_is_installed_correctly() ) : ?>

						    <?php if ( get_option('events_user_forum') ) : ?>

									<div class="checkbox">
										<label><input type="checkbox" name="event-show-forum" id="event-show-forum" value="1"<?php bp_event_show_forum_setting() ?> /> <?php _e( 'Enable discussion forum', 'bp-events' ) ?></label>
									</div>

						    <?php endif; ?>

							<?php endif; ?>

						<?php endif; ?>

						<h3><?php _e( 'Privacy Options', 'bp-events' ); ?></h3>

						<div class="radio">
							<label>
								<input type="radio" name="event-status" value="public"<?php bp_event_show_status_setting('public') ?> />
								<strong><?php _e( 'This is a public event', 'bp-events' ) ?></strong>
								<ul>
									<li><?php _e( 'Any site member can join this event.', 'bp-events' ) ?></li>
									<li><?php _e( 'This event will be listed in the events directory and in search results.', 'bp-events' ) ?></li>
									<li><?php _e( 'Event content and activity will be visible to any site member.', 'bp-events' ) ?></li>
								</ul>
							</label>

							<label>
								<input type="radio" name="event-status" value="private"<?php bp_event_show_status_setting('private') ?> />
								<strong><?php _e( 'This is a private event', 'bp-events' ) ?></strong>
								<ul>
									<li><?php _e( 'Only users who request membership and are accepted can join the event.', 'bp-events' ) ?></li>
									<li><?php _e( 'This event will be listed in the events directory and in search results.', 'bp-events' ) ?></li>
									<li><?php _e( 'Event content and activity will only be visible to members of the event.', 'bp-events' ) ?></li>
								</ul>
							</label>

							<label>
								<input type="radio" name="event-status" value="hidden"<?php bp_event_show_status_setting('hidden') ?> />
								<?php _e( 'This is a hidden event', 'bp-events' ) ?></strong>
								<ul>
									<li><?php _e( 'Only users who are invited can join the event.', 'bp-events' ) ?></li>
									<li><?php _e( 'This event will not be listed in the events directory or search results.', 'bp-events' ) ?></li>
									<li><?php _e( 'Event content and activity will only be visible to members of the event.', 'bp-events' ) ?></li>
								</ul>
							</label>
						</div>

						<?php do_action( 'bp_after_event_settings_admin' ); ?>

						<p><input type="submit" value="<?php _e( 'Save Changes', 'bp-events' ) ?> &raquo;" id="save" name="save" /></p>
						<?php wp_nonce_field( 'events_edit_event_settings' ) ?>

					<?php endif; ?>

					<?php /* Event Avatar Settings */ ?>
					<?php if ( bp_is_event_admin_screen( 'event-avatar' ) ) : ?>

						<h2><?php _e( 'Event Avatar', 'bp-events' ); ?></h2>

                      	<?php if ( 'upload-image' == bp_get_avatar_admin_step() ) : ?>

							<div class="left-menu">
								<?php bp_event_avatar( 'type=full' ) ?>

								<?php if ( bp_get_event_has_avatar() ) : ?>
									<div class="generic-button" id="delete-event-avatar-button">
										<a class="edit" href="<?php bp_event_avatar_delete_link() ?>" title="<?php _e( 'Delete Avatar', 'bp-events' ) ?>"><?php _e( 'Delete Avatar', 'bp-events' ) ?></a>
									</div>
								<?php endif; ?>
							</div>

							<div class="main-column">

								<p><?php _e("Upload an image to use as an avatar for this event. The image will be shown on the main event page, and in search results.", 'bp-events') ?></p>

								<p>
									<input type="file" name="file" id="file" />
									<input type="submit" name="upload" id="upload" value="<?php _e( 'Upload Image', 'bp-events' ) ?>" />
									<input type="hidden" name="action" id="action" value="bp_avatar_upload" />
								</p>

								<?php wp_nonce_field( 'bp_avatar_upload' ) ?>

							</div>

						<?php endif; ?>

						<?php if ( 'crop-image' == bp_get_avatar_admin_step() ) : ?>

							<h3><?php _e( 'Crop Avatar', 'bp-events' ) ?></h3>

							<img src="<?php bp_avatar_to_crop() ?>" id="avatar-to-crop" class="avatar" alt="<?php _e( 'Avatar to crop', 'v' ) ?>" />

							<div id="avatar-crop-pane">
								<img src="<?php bp_avatar_to_crop() ?>" id="avatar-crop-preview" class="avatar" alt="<?php _e( 'Avatar preview', 'bp-events' ) ?>" />
							</div>

							<input type="submit" name="avatar-crop-submit" id="avatar-crop-submit" value="<?php _e( 'Crop Image', 'bp-events' ) ?>" />

							<input type="hidden" name="image_src" id="image_src" value="<?php bp_avatar_to_crop_src() ?>" />
							<input type="hidden" id="x" name="x" />
							<input type="hidden" id="y" name="y" />
							<input type="hidden" id="w" name="w" />
							<input type="hidden" id="h" name="h" />

							<?php wp_nonce_field( 'bp_avatar_cropstore' ) ?>

						<?php endif; ?>

						</div>

					<?php endif; ?>

					<?php /* Manage Event Members */ ?>
					<?php if ( bp_is_event_admin_screen( 'manage-members' ) ) : ?>

						<h2><?php _e( 'Manage Members', 'bp-events' ); ?></h2>

						<?php do_action( 'bp_before_event_manage_members_admin' ); ?>

						<div class="bp-widget">
							<h4><?php _e( 'Administrators', 'bp-events' ); ?></h4>
							<?php bp_event_admin_memberlist( true ) ?>
						</div>

						<?php if ( bp_event_has_moderators() ) : ?>

							<div class="bp-widget">
								<h4><?php _e( 'Moderators', 'bp-events' ) ?></h4>
								<?php bp_event_mod_memberlist( true ) ?>
							</div>

						<?php endif; ?>

						<div class="bp-widget">
							<h4><?php _e('Members', 'bp-events'); ?></h4>

							<?php if ( bp_event_has_members( 'per_page=15&exclude_banned=false' ) ) : ?>

								<?php if ( bp_event_member_needs_pagination() ) : ?>

									<div class="pagination">

										<div id="member-count" class="pag-count">
											<?php bp_event_member_pagination_count() ?>
										</div>

										<div id="member-admin-pagination" class="pagination-links">
											<?php bp_event_member_admin_pagination() ?>
										</div>

									</div>

								<?php endif; ?>

								<ul id="members-list" class="item-list single-line">
									<?php while ( bp_event_members() ) : bp_event_the_member(); ?>

										<?php if ( bp_get_event_member_is_banned() ) : ?>

											<li class="banned-user">
												<?php bp_event_member_avatar_mini() ?>

												<h5><?php bp_event_member_link() ?> <?php _e( '(banned)', 'bp-events') ?> <span class="small"> &mdash; <a href="<?php bp_event_member_unban_link() ?>" class="confirm" title="<?php _e( 'Kick and ban this attendee', 'bp-events' ) ?>"><?php _e( 'Remove Ban', 'bp-events' ); ?></a> </h5>

										<?php else : ?>

											<li>
												<?php bp_event_member_avatar_mini() ?>
												<h5><?php bp_event_member_link() ?>  <span class="small"> &mdash; <a href="<?php bp_event_member_ban_link() ?>" class="confirm" title="<?php _e( 'Kick and ban this attendee', 'bp-events' ); ?>"><?php _e( 'Kick &amp; Ban', 'bp-events' ); ?></a> | <a href="<?php bp_event_member_promote_mod_link() ?>" class="confirm" title="<?php _e( 'Promote to Mod', 'bp-events' ); ?>"><?php _e( 'Promote to Mod', 'bp-events' ); ?></a> | <a href="<?php bp_event_member_promote_admin_link() ?>" class="confirm" title="<?php _e( 'Promote to Admin', 'bp-events' ); ?>"><?php _e( 'Promote to Admin', 'bp-events' ); ?></a></span></h5>

										<?php endif; ?>

												<?php do_action( 'bp_event_manage_members_admin_item' ); ?>
											</li>

									<?php endwhile; ?>
								</ul>

							<?php else: ?>

								<div id="message" class="info">
									<p><?php _e( 'This event has no members.', 'bp-events' ); ?></p>
								</div>

							<?php endif; ?>

						</div>

						<?php do_action( 'bp_after_event_manage_members_admin' ); ?>

					<?php endif; ?>

					<?php /* Manage Membership Requests */ ?>
					<?php if ( bp_is_event_admin_screen( 'membership-requests' ) ) : ?>

						<h2><?php _e( 'Membership Requests', 'bp-events' ); ?></h2>

						<?php do_action( 'bp_before_event_membership_requests_admin' ); ?>

						<?php if ( bp_event_has_membership_requests() ) : ?>

							<ul id="request-list" class="item-list">
								<?php while ( bp_event_membership_requests() ) : bp_event_the_membership_request(); ?>

									<li>
										<?php bp_event_request_user_avatar_thumb() ?>
										<h4><?php bp_event_request_user_link() ?> <span class="comments"><?php bp_event_request_comment() ?></span></h4>
										<span class="activity"><?php bp_event_request_time_since_requested() ?></span>

										<?php do_action( 'bp_event_membership_requests_admin_item' ); ?>

										<div class="action">

											<div class="generic-button accept">
												<a href="<?php bp_event_request_accept_link() ?>"><?php _e( 'Accept', 'bp-events' ); ?></a>
											</div>

										 &nbsp;

											<div class="generic-button reject">
												<a href="<?php bp_event_request_reject_link() ?>"><?php _e( 'Reject', 'bp-events' ); ?></a>
											</div>

											<?php do_action( 'bp_event_membership_requests_admin_item_action' ); ?>

										</div>
									</li>

								<?php endwhile; ?>
							</ul>

						<?php else: ?>

							<div id="message" class="info">
								<p><?php _e( 'There are no pending membership requests.', 'bp-events' ); ?></p>
							</div>

						<?php endif; ?>

						<?php do_action( 'bp_after_event_membership_requests_admin' ); ?>

					<?php endif; ?>

					<?php do_action( 'events_custom_edit_steps' ) // Allow plugins to add custom event edit screens ?>

					<?php /* Delete Event Option */ ?>
					<?php if ( bp_is_event_admin_screen( 'delete-event' ) ) : ?>

						<h2><?php _e( 'Delete Event', 'bp-events' ); ?></h2>

						<?php do_action( 'bp_before_event_delete_admin' ); ?>

						<div id="message" class="info">
							<p><?php _e( 'WARNING: Deleting this event will completely remove ALL content associated with it. There is no way back, please be careful with this option.', 'bp-events' ); ?></p>
						</div>

						<input type="checkbox" name="delete-event-understand" id="delete-event-understand" value="1" onclick="if(this.checked) { document.getElementById('delete-event-button').disabled = ''; } else { document.getElementById('delete-event-button').disabled = 'disabled'; }" /> <?php _e( 'I understand the consequences of deleting this event.', 'bp-events' ); ?>

						<?php do_action( 'bp_after_event_delete_admin' ); ?>

						<p><input type="submit" disabled="disabled" value="<?php _e( 'Delete Event', 'bp-events' ) ?> &raquo;" id="delete-event-button" name="delete-event-button" /></p>

						<input type="hidden" name="event-id" id="event-id" value="<?php bp_event_id() ?>" />

						<?php wp_nonce_field( 'events_delete_event' ) ?>

					<?php endif; ?>

					<?php /* This is important, don't forget it */ ?>
					<input type="hidden" name="event-id" id="event-id" value="<?php bp_event_id() ?>" />

				</form>

				<?php do_action( 'bp_after_event_admin_content' ) ?>
		</div>

	<?php endwhile; endif; ?>

<?php get_footer() ?>
