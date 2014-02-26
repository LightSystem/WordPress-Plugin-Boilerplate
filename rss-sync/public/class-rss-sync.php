<?php
/**
 * RSS Sync.
 *
 * @package   RSS-Sync
 * @author    João Horta Alves <joao.alves@log.pt>
 * @license   GPL-2.0+
 * @copyright 2014 João Horta Alves
 */

include_once( ABSPATH . WPINC . '/feed.php' );

/**
 * Plugin class. This class is used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-rss-sync-admin.php`
 *
 *
 * @package RSS-Sync
 * @author  João Horta Alves <joao.alves@log.pt>
 */
class RSS_Sync {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   0.2.0
	 *
	 * @var     string
	 */
	const VERSION = '0.3.0';

	const RSS_ID_CUSTOM_FIELD = 'rss_id';

	/**
	 * @TODO - Rename "plugin-name" to the name your your plugin
	 *
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    0.2.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'rss-sync';

	/**
	 * Instance of this class.
	 *
	 * @since    0.2.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     0.2.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		/* Define custom functionality.
		 * Refer To http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		add_action( 'rss_sync_event', array( $this, 'rss_sync_fetch' ) );
		//add_filter( '@TODO', array( $this, 'filter_method_name' ) );

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    0.2.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     0.2.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    0.2.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    0.2.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    0.2.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    0.2.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    0.2.0
	 */
	private static function single_activate() {
		
		$options = get_option( 'rss_sync' );

		if($options)
			$chosen_recurrence = $options['refresh'];

		if($chosen_recurrence)
			wp_schedule_event( time(), $chosen_recurrence, 'rss_sync_event' );
		else
			wp_schedule_event( time(), 'daily', 'rss_sync_event' );
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    0.2.0
	 */
	private static function single_deactivate() {
		
		wp_clear_scheduled_hook( 'rss_sync_event' );

	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	}

	/**
	 * Does all the work of fetching specified RSS feeds, as well as create the associated posts.
	 *
	 *        Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    0.2.0
	 */
	public function rss_sync_fetch() {

		$options = get_option( 'rss_sync' );

		$rss_feeds_to_fetch = explode("\n", $options['rss_feeds']);

		if($rss_feeds_to_fetch){
			foreach ($rss_feeds_to_fetch as $rss_feed) {
				$this->handle_RSS_feed($rss_feed);
			}
		}
	}

	/**
	* Fetch and process a single RSS feed.
	*
	* @since    0.3.0
	*/
	private function handle_RSS_feed($rss_feed){

		// Get a SimplePie feed object from the specified feed source.
		$rss = fetch_feed( $rss_feed );

		if ( ! is_wp_error( $rss ) ) : // Checks that the object is created correctly
			$maxitems = $rss->get_item_quantity( 0 );

			// Build an array of all the items, starting with element 0 (first element).
			$rss_items = $rss->get_items( 0, $maxitems );
		endif;

		//Loop through each feed item and create a post with the associated information
		foreach ( $rss_items as $item ) :

			$item_id 		= $item->get_id(false);
			$item_pub_date 	= date($item->get_date('Y-m-d H:i:s'));

			$item_categories = $item->get_categories();

			$custom_field_query = new WP_Query(array( 'meta_key' => RSS_ID_CUSTOM_FIELD, 'meta_value' => $item_id ));

			if($custom_field_query->have_posts()){
				$post = $custom_field_query->next_post();

				if (strtotime( $post->post_modified ) < strtotime( $item_pub_date )) {
					$post->post_content 	= $item->get_description(false);
					$post->post_title 		= $item->get_title();
					$post->post_modified 	= $item_pub_date;

					wp_update_post( $post );
				}

			} else {

				/*$post_category_IDs = array();

				foreach ($item_categories as $category) {
					
					$cat_id = get_cat_ID($category->get_term());

					if($cat_id != 0){
						array_push($post_category_IDs, $cat_id);
					} else {
						$cat_id = wp_insert_term( $category->get_term(), 'category' );

						array_push($post_category_IDs, $cat_id);
					}

				}*/

				$post_category_tags = array();

				foreach ($item_categories as $category) {
					
					$raw_tag = $category->get_term();

					array_push($post_category_tags, str_replace(' ', '-', $raw_tag));

				}

				$post = array(
				  'post_content'   => $item->get_description(false), // The full text of the post.
				  'post_title'     => $item->get_title(), // The title of the post.
				  'post_status'    => 'publish',
				  'post_date'      => $item_pub_date, // The time the post was made.
				  'tags_input'	   => $post_category_tags
				);

				$inserted_post_id = wp_insert_post( $post );

				if($inserted_post_id != 0){
					update_post_meta($inserted_post_id, RSS_ID_CUSTOM_FIELD, $item_id);
				}
			}

		endforeach;
	}

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *        Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// @TODO: Define your filter hook callback here
	}

}