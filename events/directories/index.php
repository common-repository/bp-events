<?php get_header() ?>

	<?php do_action( 'bp_before_directory_events_content' ) ?>

	<div id="content">

		<div class="page" id="events-directory-page">

			<form action="<?php echo site_url() . '/' ?>" method="post" id="events-directory-form">
				<h3><?php _e( 'Events Directory', 'bp-events' ) ?></h3>

				<ul id="letter-list">
					<li><a href="#a" id="letter-a">A</a></li>
					<li><a href="#b" id="letter-b">B</a></li>
					<li><a href="#c" id="letter-c">C</a></li>
					<li><a href="#d" id="letter-d">D</a></li>
					<li><a href="#e" id="letter-e">E</a></li>
					<li><a href="#f" id="letter-f">F</a></li>
					<li><a href="#g" id="letter-g">G</a></li>
					<li><a href="#h" id="letter-h">H</a></li>
					<li><a href="#i" id="letter-i">I</a></li>
					<li><a href="#j" id="letter-j">J</a></li>
					<li><a href="#k" id="letter-k">K</a></li>
					<li><a href="#l" id="letter-l">L</a></li>
					<li><a href="#m" id="letter-m">M</a></li>
					<li><a href="#n" id="letter-n">N</a></li>
					<li><a href="#o" id="letter-o">O</a></li>
					<li><a href="#p" id="letter-p">P</a></li>
					<li><a href="#q" id="letter-q">Q</a></li>
					<li><a href="#r" id="letter-r">R</a></li>
					<li><a href="#s" id="letter-s">S</a></li>
					<li><a href="#t" id="letter-t">T</a></li>
					<li><a href="#u" id="letter-u">U</a></li>
					<li><a href="#v" id="letter-v">V</a></li>
					<li><a href="#w" id="letter-w">W</a></li>
					<li><a href="#x" id="letter-x">X</a></li>
					<li><a href="#y" id="letter-y">Y</a></li>
					<li><a href="#z" id="letter-z">Z</a></li>
				</ul>

				<div id="events-directory-listing" class="directory-listing">
					<h3><?php _e( 'Events Listing', 'bp-events' ) ?></h3>

					<div id="event-dir-list">
						<?php locate_template( array( 'events/directories/events-loop.php' ), true ) ?>
					</div>

				</div>

				<?php do_action( 'bp_directory_events_content' ) ?>

				<?php wp_nonce_field( 'directory_events', '_wpnonce-event-filter' ) ?>

			</form>

		</div>

	</div>

	<?php do_action( 'bp_after_directory_events_content' ) ?>
	<?php do_action( 'bp_before_directory_events_sidebar' ) ?>

	<div id="sidebar" class="directory-sidebar">

		<?php do_action( 'bp_before_directory_events_search' ) ?>

		<div id="events-directory-search" class="directory-widget">

			<h3><?php _e( 'Find Events', 'bp-events' ) ?></h3>

			<?php bp_directory_events_search_form() ?>

			<?php do_action( 'bp_directory_events_search' ) ?>

		</div>

		<?php do_action( 'bp_after_directory_events_search' ) ?>
		<?php do_action( 'bp_before_directory_events_featured' ) ?>

		<div id="events-directory-featured" class="directory-widget">

			<h3><?php _e( 'Random Events', 'bp-events' ) ?></h3>

			<?php if ( bp_has_site_events( 'type=random&max=3' ) ) : ?>

				<ul id="events-list" class="item-list">
					<?php while ( bp_site_events() ) : bp_the_site_event(); ?>

						<li>
							<div class="item-avatar">
								<a href="<?php bp_the_site_event_link() ?>"><?php bp_the_site_event_avatar_thumb() ?></a>
							</div>

							<div class="item">

								<div class="item-title"><a href="<?php bp_the_site_event_link() ?>"><?php bp_the_site_event_name() ?></a></div>
								<div class="item-meta"><span class="activity"><?php bp_the_site_event_last_active() ?></span></div>

								<div class="field-data">
									<div class="field-name">
										<strong><?php _e( 'Members:', 'bp-events' ) ?></strong>
										<?php bp_the_site_event_member_count() ?>
									</div>

									<div class="field-name">
										<strong><?php _e( 'Description:', 'bp-events' ) ?></strong>
										<?php bp_the_site_event_description_excerpt() ?>
									</div>
								</div>

								<?php do_action( 'bp_directory_events_featured_item' ) ?>

							</div>

						</li>

					<?php endwhile; ?>
				</ul>

				<?php do_action( 'bp_directory_events_featured' ) ?>

			<?php else: ?>

				<div id="message" class="info">
					<p><?php _e( 'No events found.', 'buddypress' ) ?></p>
				</div>

			<?php endif; ?>

		</div>

		<?php do_action( 'bp_after_directory_events_featured' ) ?>

	</div>

	<?php do_action( 'bp_after_directory_events_sidebar' ) ?>

<?php get_footer() ?>