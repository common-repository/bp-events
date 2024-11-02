<?php get_header() ?>

	<div class="content-header">

	</div>

	<div id="content">
		<?php do_action( 'template_notices' ) // (error/success feedback) ?>

		<?php if ( bp_has_events() ) : while ( bp_events() ) : bp_the_event(); ?>

			<?php do_action( 'bp_before_event_wire_content' ) ?>

			<div class="left-menu">
				<?php load_template( STYLESHEETPATH . '/events/single/menu.php' ) ?>
			</div>

			<div class="main-column">
				<div class="inner-tube">

					<div id="event-name">
						<h1><a href="<?php bp_event_permalink() ?>"><?php bp_event_name() ?></a></h1>
						<p class="status"><?php bp_event_type() ?></p>
					</div>

					<div class="bp-widget">
						<?php if ( function_exists('bp_wire_get_post_list') ) : ?>

							<?php bp_wire_get_post_list( bp_event_id( false, false), __( 'Event Wire', 'bp-events' ), sprintf( __( 'There are no wire posts for %s', 'bp-events' ), bp_event_name(false) ), bp_event_is_member(), true ) ?>

						<?php endif; ?>
					</div>

				</div>
			</div>

		<?php endwhile; endif; ?>

	</div>

<?php get_footer() ?>