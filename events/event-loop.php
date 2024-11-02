<?php do_action( 'bp_before_my_events_loop' ) ?>

<div id="event-loop">

	<?php if ( bp_has_events() ) : ?>

		<div class="pagination">

			<div class="pag-count">
				<?php bp_event_pagination_count() ?>
			</div>

			<div class="pagination-links" id="<?php bp_event_pag_id() ?>">
				<?php bp_event_pagination() ?>
			</div>

		</div>

		<?php do_action( 'bp_before_my_events_list' ) ?>

		<ul id="event-list" class="item-list">
			<?php while ( bp_events() ) : bp_the_event(); ?>

				<li>
					<?php bp_event_avatar_thumb() ?>
					<h4><a href="<?php bp_event_permalink() ?>"><?php bp_event_name() ?></a><span class="small"> - <?php bp_event_tagline()?></span></h4>

					<div class="event-meta">

						<p class="desc">
						  <?php bp_event_date() ?>
						</p>

					  <p class="desc">
					    <?php printf( __( '%s Invites', 'bp-events' ), bp_event_total_members( false ) ) ?> &middot; <?php bp_event_date_togo() ?>
					  </p>

						<?php if ( bp_event_has_requested_membership() ) : ?>
							<p class="request-pending"><?php _e( 'Attendance Pending Approval', 'bp-events' ); ?></p>
						<?php endif; ?>

	        </div>

					<?php do_action( 'bp_before_my_events_list_item' ) ?>
				</li>

			<?php endwhile; ?>
		</ul>

		<?php do_action( 'bp_after_my_events_list' ) ?>

	<?php else: ?>

		<?php if ( bp_event_show_no_events_message() ) : ?>

			<div id="message" class="info">
				<p><?php bp_word_or_name( __( "You haven't joined any events yet.", 'bp-events' ), __( "%s hasn't joined any events yet.", 'bp-events' ) ) ?></p>
			</div>

		<?php else: ?>

			<div id="message" class="error">
				<p><?php _e( "No matching events found.", 'bp-events' ) ?></p>
			</div>

		<?php endif; ?>

	<?php endif;?>

</div>

<?php do_action( 'bp_after_my_events_loop' ) ?>