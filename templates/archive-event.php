<?php
/**
 * The Template for displaying event archives
 *
 * This template can be overridden by copying it to yourtheme/archive-event.php.
 *
 * HOWEVER, on occasion we will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 */

// If this file is called directly, abort.
defined( 'ABSPATH' ) || exit;

get_header();
?>
    <section id="primary" class="content-area">
        <main id="main" class="site-main">
			<?php
			if ( have_posts() ) {

				do_action( 'upcoming_events_lists/event_loop_before' );

				while ( have_posts() ) {
					the_post();
					do_action( 'upcoming_events_lists/event_loop' );
				}

				do_action( 'upcoming_events_lists/event_loop_after' );

			} else {
				do_action( 'upcoming_events_lists/no_events_found' );
			}
			?>
        </main><!-- #main -->
    </section><!-- #primary -->
<?php
get_footer();
