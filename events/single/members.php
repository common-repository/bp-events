<?php get_header() ?>

	<div class="content-header">

	</div>

	<div id="content">
		<?php if ( bp_has_events() ) : while ( bp_events() ) : bp_the_event(); ?>

			<?php do_action( 'bp_before_event_members_content' ) ?>

			<div class="left-menu">
				<?php load_template( STYLESHEETPATH . '/events/single/menu.php' ) ?>
			</div>

			<div class="main-column">
				<div class="inner-tube">

					<?php do_action( 'bp_before_event_name' ) ?>

					<div id="event-name">
						<h1><a href="<?php bp_event_permalink() ?>"><?php bp_event_name() ?></a></h1>
						<p class="status"><?php bp_event_type() ?></p>
					</div>

					<?php do_action( 'bp_after_event_name' ) ?>
					<?php do_action( 'bp_before_event_administrators_list' ) ?>

					<div class="bp-widget">
						<h4><?php _e( 'Administrators', 'bp-events' ); ?></h4>
						<?php bp_event_admin_memberlist() ?>
					</div>

					<?php do_action( 'bp_after_event_administrators_list' ) ?>

					<?php if ( bp_event_has_moderators() ) : ?>

						<?php do_action( 'bp_before_event_moderators_list' ) ?>

						<div class="bp-widget">
							<h4><?php _e( 'Moderators', 'bp-events' ); ?></h4>
							<?php bp_event_mod_memberlist() ?>
						</div>

						<?php do_action( 'bp_after_event_moderators_list' ) ?>

					<?php endif; ?>

					<div class="bp-widget">
						<h4><?php _e( 'Event Members', 'bp-events' ); ?></h4>

						<form action="<?php bp_event_form_action('members') ?>" method="post" id="event-members-form">
							<?php if ( bp_event_has_members() ) : ?>

								<?php if ( bp_event_member_needs_pagination() ) : ?>

									<div class="pagination">

										<div id="member-count" class="pag-count">
											<?php bp_event_member_pagination_count() ?>
										</div>

										<div id="member-pagination" class="pagination-links">
											<?php bp_event_member_pagination() ?>
										</div>

									</div>

								<?php endif; ?>

								<?php do_action( 'bp_before_event_members_list' ) ?>

								<ul id="member-list" class="item-list">
									<?php while ( bp_event_members() ) : bp_event_the_member(); ?>

										<li>
											<?php bp_event_member_avatar() ?>
											<h5><?php bp_event_member_link() ?></h5>
											<span class="activity"><?php bp_event_member_joined_since() ?></span>

											<?php do_action( 'bp_event_members_list_item' ) ?>

											<?php if ( function_exists( 'friends_install' ) ) : ?>

												<div class="action">
													<?php bp_add_friend_button( bp_get_event_member_id() ) ?>

													<?php do_action( 'bp_event_members_list_item_action' ) ?>
												</div>

											<?php endif; ?>
										</li>

									<?php endwhile; ?>

								</ul>

								<?php do_action( 'bp_after_event_members_list' ) ?>

							<?php else: ?>

								<div id="message" class="info">
									<p><?php _e( 'This event has no members.', 'bp-events' ); ?></p>
								</div>

							<?php endif;?>

						<input type="hidden" name="event_id" id="event_id" value="<?php bp_event_id() ?>" />
						</form>
					</div>

				</div>

				<?php do_action( 'bp_after_event_members_content' ) ?>

			</div>

		<?php endwhile; endif; ?>
	</div>

<?php get_footer() ?>