<?php get_header() ?>

	<div class="content-header">

	</div>

	<div id="content">

		<?php do_action( 'template_notices' ) // (error/success feedback) ?>

		<?php if ( bp_has_events() ) : while ( bp_events() ) : bp_the_event(); ?>

			<?php do_action( 'bp_before_event_content' ) ?>

			<div class="left-menu">
				<?php load_template( STYLESHEETPATH . '/events/single/menu.php' ) ?>
			</div>

			<div class="main-column">
				<div class="inner-tube">

					<?php do_action( 'bp_before_event_name' ) ?>

					<div id="event-name">
						<h1><a href="<?php bp_event_permalink() ?>" title="<?php bp_event_name() ?>"><?php bp_event_name() ?></a></h1>
						<h2><?php bp_event_tagline() ?></h2>
						<p class="status">
						  <?php bp_event_type() ?>
						  <?php if ( bp_get_event_group_name() ) : ?> &middot; <?php _e( 'Organized by', 'bp-events' ); ?> <a href="<?php bp_event_group_permalink() ?>"><?php bp_event_group_name() ?></a><?php endif; ?>
						</p>
					</div>

					<?php do_action( 'bp_after_event_name' ) ?>

					<?php if ( !bp_event_is_visible() ) : ?>

						<?php do_action( 'bp_before_event_status_message' ) ?>

						<div id="message" class="info">
							<p><?php bp_event_status_message() ?></p>
						</div>

						<?php do_action( 'bp_after_event_status_message' ) ?>

					<?php endif; ?>

		      <div class="bp-widget">
		        <h4><?php _e( 'Date and Time', 'bp-events' ); ?></h4>
				    <p><?php bp_event_date() ?></p>
			    </div>

					<?php do_action( 'bp_before_event_description' ) ?>

					<div class="bp-widget">
						<h4><?php _e( 'Description', 'bp-events' ); ?></h4>
						<p><?php bp_event_description() ?></p>
					</div>

					<?php do_action( 'bp_after_event_description' ) ?>

					<?php if ( bp_event_is_visible() && bp_event_has_location() ) : ?>

					  <div class="bp-widget">
					   	<h4><?php _e( 'Location', 'bp-events' ); ?></h4>
						  <p><?php bp_event_location(); ?></p>
					  </div>

					<?php endif; ?>

					<?php if ( bp_event_is_visible() && bp_event_has_news() ) : ?>

						<?php do_action( 'bp_before_event_news' ) ?>

						<div class="bp-widget">
							<h4><?php _e( 'News', 'bp-events' ); ?></h4>
							<p><?php bp_event_news() ?></p>
						</div>

						<?php do_action( 'bp_after_event_news' ) ?>

					<?php endif; ?>

					<?php if ( bp_event_is_visible() ) : ?>

						<?php if ( bp_has_activities( 'object=events&primary_id=' . bp_get_event_id() . '&max=150&per_page=5' ) ) : ?>

							<?php do_action( 'bp_before_event_activity' ) ?>

							<div class="bp-widget">
								<h4><?php _e( 'Event Activity', 'bp-events' ); ?></h4>

								<div class="pagination">
									<div class="pag-count" id="activity-count">
										<?php bp_activity_pagination_count() ?>
									</div>

									<div class="pagination-links" id="activity-pag">
										&nbsp; <?php bp_activity_pagination_links() ?>
									</div>
								</div>

								<ul id="activity-list" class="activity-list item-list">
								<?php while ( bp_activities() ) : bp_the_activity(); ?>
									<li class="<?php bp_activity_css_class() ?>">
										<div class="activity-avatar">
											<?php bp_activity_avatar() ?>
										</div>

										<?php bp_activity_content() ?>
									</li>
								<?php endwhile; ?>
								</ul>

							</div>

							<?php do_action( 'bp_after_event_activity' ) ?>

						<?php endif; ?>

					<?php endif; ?>

					<?php if ( bp_event_is_visible() && bp_event_is_forum_enabled() && function_exists( 'bp_forums_setup') ) : ?>

						<?php do_action( 'bp_before_event_active_topics' ) ?>

						<div class="bp-widget">
							<h4><?php _e( 'Recently Active Topics', 'bp-events' ); ?> <span><a href="<?php bp_event_forum_permalink() ?>"><?php _e( 'See All', 'bp-events' ) ?> &rarr;</a></span></h4>

							<?php if ( bp_has_forum_topics( 'no_stickies=true&max=5&per_page=5&forum_id=' . bp_get_event_forum() ) ) : ?>

								<ul id="forum-topic-list" class="item-list">
									<?php while ( bp_topics() ) : bp_the_topic(); ?>

										<li>
											<a class="topic-avatar" href="<?php bp_the_topic_permalink() ?>" title="<?php bp_the_topic_title() ?> - <?php _e( 'Permalink', 'bp-events' ) ?>"><?php bp_the_topic_last_poster_avatar( 'width=30&height=30') ?></a>
											<a class="topic-title" href="<?php bp_the_topic_permalink() ?>" title="<?php bp_the_topic_title() ?> - <?php _e( 'Permalink', 'bp-events' ) ?>"><?php bp_the_topic_title() ?></a>
											<span class="small topic-meta">(<?php bp_the_topic_total_post_count() ?> &rarr; <?php bp_the_topic_time_since_last_post() ?> ago)</span>
											<span class="small latest topic-excerpt"><?php bp_the_topic_latest_post_excerpt() ?></span>

											<?php do_action( 'bp_event_active_topics_item' ) ?>
										</li>

									<?php endwhile; ?>
								</ul>

							<?php else: ?>

								<div id="message" class="info">
									<p><?php _e( 'There are no active forum topics for this event', 'bp-events' ) ?></p>
								</div>

							<?php endif;?>

						</div>

						<?php do_action( 'bp_after_event_active_topics' ) ?>

					<?php endif; ?>

					<?php if ( bp_event_is_visible() ) : ?>

						<?php do_action( 'bp_before_event_member_widget' ) ?>

						<div class="bp-widget">
							<h4><?php printf( __( 'Attendees (%d)', 'bp-events' ), bp_get_event_total_members() ); ?> <span><a href="<?php bp_event_all_members_permalink() ?>"><?php _e( 'See All', 'bp-events' ) ?> &rarr;</a></span></h4>

							<?php if ( bp_event_has_members( 'max=5&exclude_admins_mods=0' ) ) : ?>

								<ul class="horiz-gallery">
									<?php while ( bp_event_members() ) : bp_event_the_member(); ?>

										<li>
											<a href="<?php bp_event_member_url() ?>"><?php bp_event_member_avatar_thumb() ?></a>
											<h5><?php bp_event_member_link() ?></h5>
										</li>
									<?php endwhile; ?>
								</ul>

							<?php endif; ?>

						</div>

						<?php do_action( 'bp_after_event_member_widget' ) ?>

					<?php endif; ?>

					<?php do_action( 'events_custom_event_boxes' ) ?>

					<?php if ( bp_event_is_visible() && bp_event_is_wire_enabled() ) : ?>

						<?php if ( function_exists('bp_wire_get_post_list') ) : ?>

							<?php do_action( 'bp_before_event_wire_widget' ) ?>

							<?php bp_wire_get_post_list( bp_get_event_id(), __( 'Event Wire', 'bp-events' ), sprintf( __( 'There are no wire posts for %s', 'bp-events' ), bp_get_event_name() ), bp_event_is_member(), true ) ?>

							<?php do_action( 'bp_after_event_wire_widget' ) ?>

						<?php endif; ?>

					<?php endif; ?>

				</div>

			</div>

			<?php do_action( 'bp_after_event_content' ) ?>

		<?php endwhile; else: ?>

			<div id="message" class="error">
				<p><?php _e('Sorry, the event does not exist.', 'bp-events'); ?></p>
			</div>

		<?php endif;?>

	</div>

<?php get_footer() ?>