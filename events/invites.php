<?php get_header() ?>

	<div class="content-header">

	</div>

	<div id="content">
		<h2><?php _e( 'Group Invites', 'bp-events' ) ?></h2>

		<?php do_action( 'template_notices' ) // (error/success feedback) ?>

		<?php do_action( 'bp_before_event_invites_content' ) ?>

		<?php if ( bp_has_events() ) : ?>

			<ul id="event-list" class="invites item-list">

				<?php while ( bp_events() ) : bp_the_event(); ?>

					<li>
						<?php bp_event_avatar_thumb() ?>
						<h4><a href="<?php bp_event_permalink() ?>"><?php bp_event_name() ?></a><span class="small"> - <?php printf( __( '%s members', 'bp-events' ), bp_event_total_members( false ) ) ?></span></h4>
						<p class="desc">
							<?php bp_event_description_excerpt() ?>
						</p>

						<?php do_action( 'bp_event_invites_item' ) ?>

						<div class="action">

							<div class="generic-button accept">
								<a href="<?php bp_event_accept_invite_link() ?>"><?php _e( 'Accept', 'bp-events' ) ?></a>
							</div>

							 &nbsp;

							<div class="generic-button reject">
								<a href="<?php bp_event_reject_invite_link() ?>"><?php _e( 'Reject', 'bp-events' ) ?></a>
							</div>

							<?php do_action( 'bp_event_invites_item_action' ) ?>

						</div>
						<hr />
					</li>

				<?php endwhile; ?>
			</ul>

		<?php else: ?>

			<div id="message" class="info">
				<p><?php _e( 'You have no outstanding event invites.', 'bp-events' ) ?></p>
			</div>

		<?php endif;?>

		<?php do_action( 'bp_after_event_invites_content' ) ?>

	</div>

<?php get_footer() ?>