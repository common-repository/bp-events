<?php do_action( 'bp_before_event_menu_content' ) ?>

<?php bp_event_avatar() ?>

<?php do_action( 'bp_after_event_menu_avatar' ) ?>
<?php do_action( 'bp_before_event_menu_buttons' ) ?>

<div class="button-block">
	<?php bp_event_join_button() ?>

	<?php do_action( 'bp_event_menu_buttons' ) ?>
</div>

<?php do_action( 'bp_after_event_menu_buttons' ) ?>
<?php do_action( 'bp_before_event_menu_admins' ) ?>

<div class="bp-widget">
	<h4><?php _e( 'Admins', 'bp-events' ) ?></h4>
	<?php bp_event_list_admins() ?>
</div>

<?php do_action( 'bp_after_event_menu_admins' ) ?>
<?php do_action( 'bp_before_event_menu_mods' ) ?>

<?php if ( bp_event_has_moderators() ) : ?>
	<div class="bp-widget">
		<h4><?php _e( 'Mods' , 'bp-events' ) ?></h4>
		<?php bp_event_list_mods() ?>
	</div>
<?php endif; ?>

<?php do_action( 'bp_after_event_menu_mods' ) ?>
<?php do_action( 'bp_after_event_menu_content' ); /* Deprecated -> */ do_action( 'events_sidebar_after' ); ?>