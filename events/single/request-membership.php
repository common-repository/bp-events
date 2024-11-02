<?php get_header() ?>

	<div class="content-header">

	</div>

	<div id="content">
		<?php if ( bp_has_events() ) : while ( bp_events() ) : bp_the_event(); ?>

		<?php do_action( 'bp_before_event_request_membership_content' ) ?>

		<div class="left-menu">
			<?php load_template( STYLESHEETPATH . '/events/single/menu.php' ) ?>
		</div>

		<div class="main-column">

			<?php do_action( 'bp_before_event_name' ) ?>

			<div id="event-name">
				<h1><a href="<?php bp_event_permalink() ?>"><?php bp_event_name() ?></a></h1>
				<p class="status"><?php bp_event_type() ?></p>
			</div>

			<?php do_action( 'bp_after_event_name' ) ?>

			<div class="bp-widget">
				<h4><?php _e( 'Request Membership', 'bp-events' ); ?></h4>

				<?php do_action( 'template_notices' ) // (error/success feedback) ?>

				<?php if ( !bp_event_has_requested_membership() ) : ?>
					<p><?php printf( __( "You are requesting to become a member of the event '%s'.", "bp-events" ), bp_event_name( false, false ) ); ?></p>

					<form action="<?php bp_event_form_action('request-membership') ?>" method="post" name="request-membership-form" id="request-membership-form" class="standard-form">
						<label for="event-request-membership-comments"><?php _e( 'Comments (optional)', 'bp-events' ); ?></label>
						<textarea name="event-request-membership-comments" id="event-request-membership-comments"></textarea>

						<?php do_action( 'bp_event_request_membership_content' ) ?>

						<p><input type="submit" name="event-request-send" id="event-request-send" value="<?php _e( 'Send Request', 'bp-events' ) ?> &raquo;" />

						<?php wp_nonce_field( 'events_request_membership' ) ?>
					</form>
				<?php endif; ?>

			</div>

		</div>

		<?php do_action( 'bp_after_event_request_membership_content' ) ?>

		<?php endwhile; endif; ?>
	</div>

<?php get_footer() ?>
