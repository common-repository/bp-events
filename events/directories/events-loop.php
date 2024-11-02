<?php if ( bp_has_site_events( 'type=upcoming&per_page=10' ) ) : ?>

	<div class="pagination">

		<div class="pag-count" id="event-dir-count">
			<?php bp_site_events_pagination_count() ?>
		</div>

		<div class="pagination-links" id="event-dir-pag">
			<?php bp_site_events_pagination_links() ?>
		</div>

	</div>

	<?php do_action( 'bp_before_directory_events_list' ) ?>

	<ul id="events-list" class="item-list">
	<?php while ( bp_site_events() ) : bp_the_site_event(); ?>

		<li>
			<div class="item-avatar">
				<a href="<?php bp_the_site_event_link() ?>"><?php bp_the_site_event_avatar_thumb() ?></a>
			</div>

			<div class="item">
				<div class="item-title"><a href="<?php bp_the_site_event_link() ?>"><?php bp_the_site_event_name() ?></a></div>
				<div class="item-meta"><span class="activity"><?php bp_the_site_event_upcoming() ?></span></div>

				<div class="item-meta desc"><?php bp_the_site_event_description_excerpt() ?></div>

				<?php do_action( 'bp_directory_events_item' ) ?>
			</div>

			<div class="action">
				<?php bp_the_site_event_join_button() ?>

				<div class="meta">
					<?php bp_the_site_event_type() ?> / <?php bp_the_site_event_member_count() ?>
				</div>

				<?php do_action( 'bp_directory_events_actions' ) ?>
			</div>

			<div class="clear"></div>
		</li>

	<?php endwhile; ?>
	</ul>

	<?php do_action( 'bp_after_directory_events_list' ) ?>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'There were no events found.', 'bp-events' ) ?></p>
	</div>

<?php endif; ?>

<?php bp_the_site_event_hidden_fields() ?>