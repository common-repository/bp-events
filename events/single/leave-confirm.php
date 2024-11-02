<?php get_header() ?>

	<div class="content-header">

	</div>

	<div id="content">

		<?php if ( bp_has_events() ) : while ( bp_events() ) : bp_the_event(); ?>

			<?php do_action( 'bp_before_event_leave_confirm_content' ) ?>

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

					<div class="bp-widget">
						<h4><?php _e( 'Confirm Leave Event', 'bp-events' ); ?></h4>
						<h3><?php _e( 'Are you sure you want to leave this event?', 'bp-events' ); ?></h3>

						<p>
							<a href="<?php bp_event_leave_confirm_link() ?>"><?php _e( "Yes, I'd like to leave this event.", 'bp-events' ) ?></a> |
							<a href="<?php bp_event_leave_reject_link() ?>"><?php _e( "No, I'll stay!", 'bp-events' ) ?></a>
						</p>

						<?php do_action( 'bp_event_leave_confirm_content' ) ?>
					</div>

				</div>
			</div>

			<?php do_action( 'bp_after_event_leave_confirm_content' ) ?>

		<?php endwhile; endif; ?>

	</div>

<?php get_footer() ?>