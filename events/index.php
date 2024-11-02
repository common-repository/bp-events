<?php get_header() ?>

	<div id="event-nav" class="content-header">
		<ul class="content-header-nav">
			<?php bp_events_header_tabs() ?>
		</ul>
	</div>

	<div id="content">

		<h2><?php bp_word_or_name( __( "My Events", 'bp-events' ), __( "%s's Events", 'bp-events' ) ) ?> &raquo; <?php bp_events_filter_title() ?></h2>

		<?php do_action( 'bp_before_my_events_content' ) ?>

		<div class="left-menu">
			<?php bp_event_search_form() ?>
		</div>

		<div class="main-column">
			<?php do_action( 'template_notices' ) // (error/success feedback) ?>

			<?php load_template( STYLESHEETPATH . '/events/event-loop.php' )?>
		</div>

		<?php do_action( 'bp_after_my_events_content' ) ?>

	</div>

<?php get_footer() ?>