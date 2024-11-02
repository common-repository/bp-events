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
				<h4><?php _e( 'Forum', 'bp-events' ); ?> <?php if ( is_user_logged_in() ) : ?><span><a href="#post-new-topic" title="<?php _e( 'Post New', 'bp-events' ) ?>"><?php _e( 'Post New &rarr;', 'bp-events' ) ?></a></span><?php endif; ?></h4>

				<form action="<?php bp_forum_action() ?>" method="post" id="forum-topic-form" class="standard-form">
					<?php if ( bp_has_forum_topics( 'forum_id=' . bp_get_event_forum() ) ) : ?>

						<div class="pagination">

							<div id="post-count" class="pag-count">
								<?php bp_forum_pagination_count() ?>
							</div>

							<div class="pagination-links" id="topic-pag">
								<?php bp_forum_pagination() ?>
							</div>

						</div>

						<ul id="forum-topic-list" class="item-list">
						<?php while ( bp_topics() ) : bp_the_topic(); ?>
							<li<?php if ( bp_get_the_topic_css_class() ) : ?> class="<?php bp_the_topic_css_class() ?>"<?php endif; ?>>

								<a class="topic-avatar" href="<?php bp_the_topic_permalink() ?>" title="<?php bp_the_topic_title() ?> - <?php _e( 'Permalink', 'bp-events' ) ?>"><?php bp_the_topic_last_poster_avatar( 'width=30&height=30') ?></a>
								<a class="topic-title" href="<?php bp_the_topic_permalink() ?>" title="<?php bp_the_topic_title() ?> - <?php _e( 'Permalink', 'bp-events' ) ?>"><?php bp_the_topic_title() ?></a>
								<span class="small topic-meta">(<?php bp_the_topic_total_post_count() ?> &rarr; <?php printf( __( '%s ago', 'bp-events' ), bp_get_the_topic_time_since_last_post() ) ?>)</span>
								<span class="small latest topic-excerpt"><?php bp_the_topic_latest_post_excerpt() ?></span>

								<?php if ( bp_event_is_admin() || bp_event_is_mod() ) : ?>
									<div class="admin-links"><?php bp_the_topic_admin_links() ?></div>
								<?php endif; ?>
							</li>
						<?php endwhile; ?>
						</ul>
					<?php else: ?>

						<div id="message" class="info">
							<p><?php _e( 'There are no topics for this event forum.', 'bp-events' ) ?></p>
						</div>

					<?php endif;?>

					<?php if ( ( is_user_logged_in() && 'public' == bp_get_event_status() ) || bp_event_is_member() ) : ?>

						<div id="post-new-topic">

							<?php do_action( 'events_forum_new_topic_before' ) ?>

							<a name="post-new"></a>
							<p><strong><?php _e( 'Post a New Topic:', 'bp-events' ) ?></strong></p>

							<label><?php _e( 'Title:', 'bp-events' ) ?></label>
							<input type="text" name="topic_title" id="topic_title" value="" />

							<label><?php _e( 'Content:', 'bp-events' ) ?></label>
							<textarea name="topic_text" id="topic_text"></textarea>

							<label><?php _e( 'Tags (comma separated):', 'bp-events' ) ?></label>
							<input type="text" name="topic_tags" id="topic_tags" value="" />

							<?php do_action( 'events_forum_new_topic_after' ) ?>

							<p class="submit"><input type="submit" name="submit_topic" id="submit" value="<?php _e( 'Post Topic', 'bp-events' ) ?>" /></p>

							<?php wp_nonce_field( 'bp_forums_new_topic' ) ?>
						</div>

					<?php endif; ?>
				</form>

			</div>

		</div>
	</div>

	<?php endwhile; endif; ?>
</div>

<?php get_footer() ?>