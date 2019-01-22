<?php
/**
 * Plugin Name: Upcoming Events Lists
 * Plugin URI: http://wordpress.org/plugins/upcoming-events-lists
 * Description: A plugin to show a list of upcoming events on the front-end.
 * Version: 1.3.2
 * Author: Sayful Islam
 * Author URI: http://sayful.net
 * Text Domain: upcoming-events
 * Domain Path: /languages/
 * License: GPL2
 */

if( !class_exists('Upcoming_Events_Lists') ):

class Upcoming_Events_Lists {

	private $plugin_name = 'upcoming-events-lists';
	private $plugin_version = '1.3.2';
	private $plugin_url;
	private $plugin_path;

	public function __construct(){

		add_action( 'plugins_loaded', array( $this, 'load_textdomain') );
		add_action( 'wp_enqueue_scripts', array( $this, 'widget_style' ));
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_script_style' ) );
		
		
		add_action('add_meta_boxes', array( $this, 'event_info_metabox') );
		add_action( 'save_post', array( $this, 'save_event_info' ) );

		add_filter( 'the_content', array( $this, 'upcoming_events_single_content') );

		// Include required files
		$this->includes();
	}

	// Including the widget
	public function includes(){
		include_once $this->plugin_path() . '/includes/Upcoming_Events_Lists_Admin.php';
		include_once $this->plugin_path() . '/widget-upcoming-events.php';

		new Upcoming_Events_Lists_Admin();
	}

	public function load_textdomain(){
  		load_plugin_textdomain( 'upcoming-events', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Enqueueing styles for the front-end widget
	 */
	public function widget_style() {
		if ( is_active_widget( '', '', 'sis_upcoming_events', true ) ) {
			wp_enqueue_style( $this->plugin_name, $this->plugin_url() . '/assets/css/style.css' );
		}
	}

	/**
	 * Enqueueing scripts and styles in the admin
	 * @param  int $hook Current page hook
	 */
	public function admin_script_style( $hook ) {
		global $post;

		if ( ( 'post.php' == $hook || 'post-new.php' == $hook ) && ( 'event' == $post->post_type ) ) {

			wp_enqueue_script( $this->plugin_name . '-admin', $this->plugin_url() . '/assets/js/script.js', array( 'jquery', 'jquery-ui-datepicker' ), false, true );
			wp_enqueue_style( $this->plugin_name . '-admin', $this->plugin_url() . '/assets/css/admin.css' );
		}
	}

	//Adding metabox for event information
	public function event_info_metabox() {
		add_meta_box( 'sis-event-info-metabox', __( 'Event Info', 'upcoming-events' ), array( $this, 'render_event_info_metabox'), 'event','side', 'core' );
	}

	/**
	 * Rendering the metabox for event information
	 * @param  object $post The post object
	 */
	public function render_event_info_metabox( $post ) {
		//generate a nonce field
		wp_nonce_field( basename( __FILE__ ), 'sis-event-info-nonce' );

		//get previously saved meta values (if any)
		$event_start_date = get_post_meta( $post->ID, 'event-start-date', true );
		$event_end_date = get_post_meta( $post->ID, 'event-end-date', true );
		$event_venue = get_post_meta( $post->ID, 'event-venue', true );

		//if there is previously saved value then retrieve it, else set it to the current time
		$event_start_date = ! empty( $event_start_date ) ? $event_start_date : time();

		//we assume that if the end date is not present, event ends on the same day
		$event_end_date = ! empty( $event_end_date ) ? $event_end_date : $event_start_date;

		?>
		<p> 
			<label for="sis-event-start-date"><?php _e( 'Event Start Date:', 'upcoming-events' ); ?></label>
			<input type="text" id="sis-event-start-date" name="sis-event-start-date" class="widefat sis-event-date-input" value="<?php echo date( 'F d, Y', $event_start_date ); ?>" placeholder="Format: February 18, 2014">
		</p>
		<p>
			<label for="sis-event-end-date"><?php _e( 'Event End Date:', 'upcoming-events' ); ?></label>
			<input type="text" id="sis-event-end-date" name="sis-event-end-date" class="widefat sis-event-date-input" value="<?php echo date( 'F d, Y', $event_end_date ); ?>" placeholder="Format: February 18, 2014">
		</p>
		<p>
			<label for="sis-event-venue"><?php _e( 'Event Venue:', 'upcoming-events' ); ?></label>
			<input type="text" id="sis-event-venue" name="sis-event-venue" class="widefat" value="<?php echo $event_venue; ?>" placeholder="eg. Times Square">
		</p>
		<?php
	}

	/**
	 * Saving the event along with its meta values
	 * @param  int $post_id The id of the current post
	 */
	function save_event_info( $post_id ) {
		//checking if the post being saved is an 'event',
		//if not, then return
		if ( isset($_POST['post_type']) && 'event' != $_POST['post_type'] ) {
			return;
		}

		//checking for the 'save' status
		$is_autosave = wp_is_post_autosave( $post_id );
		$is_revision = wp_is_post_revision( $post_id );
		$is_valid_nonce = ( isset( $_POST['sis-event-info-nonce'] ) && ( wp_verify_nonce( $_POST['sis-event-info-nonce'], basename( __FILE__ ) ) ) ) ? true : false;

		//exit depending on the save status or if the nonce is not valid
		if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
			return;
		}

		//checking for the values and performing necessary actions
		if ( isset( $_POST['sis-event-start-date'] ) ) {
			update_post_meta( $post_id, 'event-start-date', strtotime( $_POST['sis-event-start-date'] ) );
		}

		if ( isset( $_POST['sis-event-end-date'] ) ) {
			update_post_meta( $post_id, 'event-end-date', strtotime( $_POST['sis-event-end-date'] ) );
		}

		if ( isset( $_POST['sis-event-venue'] ) ) {
			update_post_meta( $post_id, 'event-venue', sanitize_text_field( $_POST['sis-event-venue'] ) );
		}
	}


	function upcoming_events_single_content( $content ){
		if ( is_singular('event') || is_post_type_archive('event') ) {

			$event_start_date = get_post_meta( get_the_ID(), 'event-start-date', true );
			$event_end_date = get_post_meta( get_the_ID(), 'event-end-date', true );
			$event_venue = get_post_meta( get_the_ID(), 'event-venue', true );

			$event  = '<table>';
			$event .= '<tr>';
			$event .= '<td><strong>'.__('Event Start Date:', 'upcoming-events').'</strong><br>'.date_i18n( get_option( 'date_format' ), $event_start_date ).'</td>';
			$event .= '<td><strong>'.__('Event End Date:', 'upcoming-events').'</strong><br>'.date_i18n( get_option( 'date_format' ), $event_end_date ).'</td>';
			$event .= '<td><strong>'.__('Event Venue:', 'upcoming-events').'</strong><br>'.$event_venue.'</td>';
			$event .= '</tr>';
			$event .= '</table>';

			$content = $event . $content;
		}
		return $content;
	}

	/**
	 * Plugin path.
	 *
	 * @return string Plugin path
	 */
	private function plugin_path() {
		if ( $this->plugin_path ) return $this->plugin_path;

		return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Plugin url.
	 *
	 * @return string Plugin url
	 */
	private function plugin_url() {
		if ( $this->plugin_url ) return $this->plugin_url;
		return $this->plugin_url = untrailingslashit( plugins_url( '/', __FILE__ ) );
	}
}

endif;

new Upcoming_Events_Lists();

/**
 * Flushing rewrite rules on plugin activation/deactivation
 * for better working of permalink structure
 */
function upcoming_events_lists_activation_deactivation() {
	$events = new Upcoming_Events_Lists_Admin();
	$events->post_type();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'upcoming_events_lists_activation_deactivation' );
register_deactivation_hook( __FILE__, 'upcoming_events_lists_activation_deactivation' );