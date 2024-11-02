<?php get_header() ?>

<div class="content-header">

</div>

<div id="content">
	<?php do_action( 'template_notices' ) // (error/success feedback) ?>

	<?php if ( bp_has_events() ) : while ( bp_events() ) : bp_the_event(); ?>

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
				<?php if ( bp_has_forum_topic_posts( 'topic_id=' . bp_get_event_forum_topic() ) ) : ?>
				<form action="<?php bp_forum_topic_action() ?>" method="post" id="forum-topic-form">

					<h4><?php _e( 'Forum', 'bp-events' ); ?></h4>

					<ul id="topic-post-list" class="item-list">
						<li id="topic-meta">
							<a href="<?php bp_forum_permalink() ?>"><?php _e( 'Forum', 'bp-events') ?></a> &raquo;
							<strong><?php bp_the_topic_title() ?> (<?php bp_the_topic_total_post_count() ?>)</strong>
						</li>
					</ul>

					<?php if ( bp_event_is_member() ) : ?>

						<?php if ( bp_is_edit_topic() ) : ?>

							<div id="edit-topic">

								<?php do_action( 'events_forum_edit_topic_before' ) ?>

								<p><strong><?php _e( 'Edit Topic:', 'bp-events' ) ?></strong></p>

								<label for="topic_title"><?php _e( 'Title:', 'bp-events' ) ?></label>
								<input type="text" name="topic_title" id="topic_title" value="<?php bp_the_topic_title() ?>" />

								<label for="topic_text"><?php _e( 'Content:', 'bp-events' ) ?></label>
								<textarea name="topic_text" id="topic_text"><?php bp_the_topic_text() ?></textarea>

								<?php do_action( 'events_forum_edit_topic_after' ) ?>

								<p class="submit"><input type="submit" name="save_changes" id="save_changes" value="<?php _e( 'Save Changes', 'bp-events' ) ?>" /></p>

								<?php wp_nonce_field( 'bp_forums_edit_topic' ) ?>

							</div>

						<?php else : ?>

							<div id="edit-post">

								<?php do_action( 'events_forum_edit_post_before' ) ?>

								<p><strong><?php _e( 'Edit Post:', 'bp-events' ) ?></strong></p>

								<textarea name="post_text" id="post_text"><?php bp_the_topic_post_edit_text() ?></textarea>

								<?php do_action( 'events_forum_edit_post_after' ) ?>

								<p class="submit"><input type="submit" name="save_changes" id="save_changes" value="<?php _e( 'Save Changes', 'bp-events' ) ?>" /></p>

								<?php wp_nonce_field( 'bp_forums_edit_post' ) ?>

							</div>

						<?php endif; ?>

					<?php endif; ?>

				</form>
				<?php else: ?>

					<div id="message" class="info">
						<p><?php _e( 'This topic does not exist.', 'bp-events' ) ?></p>
					</div>

				<?php endif;?>

			</div>

		</div>
	</div>

	<?php endwhile; endif; ?>
</div>

<?php get_footer() ?>
