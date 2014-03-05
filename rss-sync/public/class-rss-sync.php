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
include_once( ABSPATH . 'wp-admin/includes/image.php' );

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
	const VERSION = '0.5.0';

	const RSS_ID_CUSTOM_FIELD = 'rss_id';

	/**
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
		// Set 1 hour caching for feeds
		add_filter( 'wp_feed_cache_transient_lifetime', function ($seconds){ return 3600; } );

		add_action( 'rss_sync_event', array( $this, 'rss_sync_fetch' ) );
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

		if($options){
			$chosen_recurrence = $options['refresh'];
                }

		if($chosen_recurrence){
			wp_schedule_event( time(), $chosen_recurrence, 'rss_sync_event' );
                } else {
			wp_schedule_event( time(), 'daily', 'rss_sync_event' );
                }
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
			$channel_title = $rss->get_title();
			$post_cat_id   = $this->cat_id_by_name($channel_title);

			$maxitems = $rss->get_item_quantity( 5 );

			// Build an array of all the items, starting with element 0 (first element).
			$rss_items = $rss->get_items( 0, $maxitems );

			//Loop through each feed item and create a post with the associated information
			foreach ( $rss_items as $item ) :

				$item_id 	   = $item->get_id(false);
				$item_pub_date = date($item->get_date('Y-m-d H:i:s'));

				$item_categories = $item->get_categories();
				$post_tags 		 = $this->extract_tags($item_categories);

				$custom_field_query = new WP_Query(array( 'meta_key' => RSS_ID_CUSTOM_FIELD, 'meta_value' => $item_id ));

				if($custom_field_query->have_posts()){
					$post = $custom_field_query->next_post();

					if (strtotime( $post->post_modified ) < strtotime( $item_pub_date )) {
						$post->post_content  = $item->get_description(false);
						$post->post_title 	 = $item->get_title();
						$post->post_modified = $item_pub_date;

						$updated_post_id = wp_update_post( $post );

						if($updated_post_id != 0){
							wp_set_object_terms( $updated_post_id, $post_cat_id, 'category', false );
							wp_set_post_tags( $updated_post_id, $post_tags, false );

							if($this->is_image_import()){
								//Image importing routines
								$post_data = array(
									'post_content' => $post->post_content,
									'post_date' => $post->post_modified
								);

								$processed_post_content = $this->process_image_tags($post_data, $updated_post_id);

								//Update post content
								if(!is_wp_error( $processed_post_content )){
									$this->update_post_content($processed_post_content, $updated_post_id);
								}
							}
						}
					}

				} else {

					$post = array(
					  'post_content' => $item->get_description(false), // The full text of the post.
					  'post_title'   => $item->get_title(), // The title of the post.
					  'post_status'  => 'publish',
					  'post_date'    => $item_pub_date, // The time the post was made.
					  'tags_input'	 => $post_tags
					);

					$inserted_post_id = wp_insert_post( $post );

					if($inserted_post_id != 0){
						wp_set_object_terms( $inserted_post_id, $post_cat_id, 'category', false );
						update_post_meta($inserted_post_id, RSS_ID_CUSTOM_FIELD, $item_id);

						if($this->is_image_import()){
							//Import images to media library
							$processed_post_content = $this->process_image_tags($post, $inserted_post_id);

							//Update post content
							if( !is_wp_error( $processed_post_content ) ){
								$this->update_post_content($processed_post_content, $inserted_post_id);
							}
						}
					}
				}

			endforeach;
		endif;

	}

	private function is_image_import(){

		$options = get_option( 'rss_sync' );

		return $options['img_storage'] == 'local_storage';
	}

	private function update_post_content($post_content, $post_id){

		$post = get_post( $post_id );
		$post->post_content = $post_content;

		return wp_update_post($post) != 0;
	}

	/**
	* Handles creation and/or resolution of a category ID.	
	*	
	* @since    0.4.0
	*/
	private function cat_id_by_name($cat_name){

		$cat_id = get_cat_ID($cat_name);

		if($cat_id == 0){
			$cat_id = wp_insert_term( $cat_name, 'category' );
		}

		return $cat_id;
	}

	/**
	* Handles extraction of post tags from a list of RSS item categories. 
	*
	* @since    0.4.0
	*/
	private function extract_tags($rss_item_cats){

		$post_tags = array();

		foreach ($rss_item_cats as $category) {

			$raw_tag = $category->get_term();

			array_push($post_tags, str_replace(' ', '-', $raw_tag));

		}

		return $post_tags;
	}

	/**
	* Parses text content, looking for image tags. Handles fetching external image if needed.
	* Returns processed text with image tags now pointing to images locally stored.
	*/
	private function process_image_tags($post, $post_id){

		if(preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post['post_content'], $matches)){
			$images_array = $matches [1];

			foreach ($images_array as $image) {
				$upload = $this->get_img_attachment($post_id, $image);

				if(!$upload){
					$upload = $this->fetch_remote_image($image, $post, $post_id);
				}

				if ( is_wp_error( $upload ) ){
					write_log('UPLOAD');
					write_log($upload);

					return $upload;
				}

				$post_content = str_replace($image, $upload['url'], $post['post_content']);

				return $post_content;
			}
		}

		return $post['post_content'];
	}

	/**
	* Checks if image already exists in media library. Returns its URL if it does, returns false if it does not.
	*/
	function get_img_attachment($post_id, $external_img_url){

		$attachments = new WP_Query( array( 'post_status' => 'any', 'post_type' => 'attachment', 'post_parent' => $post_id ) );

		while($attachments->have_posts()){
			$attachment = $attachments->next_post();

			$metadata = wp_get_attachment_metadata($attachment->ID);

			if($metadata['file'] == $external_img_url){
				$upload = array(
					'url' => wp_get_attachment_url( $attachment->ID )
				);

				return $upload;
			}
		}

		return false;
	}

	/**
	 * Attempt to download a remote image attachment
	 *
	 * @param string $url URL of image to fetch
	 * @param array $postdata Data about the post te image belongs to
	 * @param string ID of the post
	 * @return array|WP_Error Local file location details on success, WP_Error otherwise
	 */
	function fetch_remote_image( $url, $postdata, $post_id ) {

		// extract the file name and extension from the url
		$file_name = rawurldecode(basename( $url ));

		// get placeholder file in the upload dir with a unique, sanitized filename
		$upload = wp_upload_bits( $file_name, 0, '', $postdata['post_date'] );

		//Append jpeg extension to file if Invalid file type error detected
		if($upload['error'] == 'Invalid file type'){
			//There must be some better way to do this
			$file_name = $file_name . '.jpeg';

			$upload = wp_upload_bits( $file_name, 0, '', $postdata['post_date'] );
		}

		if ( $upload['error'] ){
			return new WP_Error( 'upload_dir_error', $upload['error'] );
		}

		// fetch the remote url and write it to the placeholder file
		$headers = wp_get_http( $url, $upload['file'] );

		// request failed
		if ( ! $headers ) {
			@unlink( $upload['file'] );
			return new WP_Error( 'import_file_error', __('Remote server did not respond', 'wordpress-importer') );
		}

		// make sure the fetch was successful
		if ( $headers['response'] != '200' ) {
			@unlink( $upload['file'] );
			return new WP_Error( 'import_file_error', sprintf( __('Remote server returned error response %1$d %2$s', 'wordpress-importer'), esc_html($headers['response']), get_status_header_desc($headers['response']) ) );
		}

		$filesize = filesize( $upload['file'] );

		if ( isset( $headers['content-length'] ) && $filesize != $headers['content-length'] ) {
			@unlink( $upload['file'] );
			return new WP_Error( 'import_file_error', __('Remote file is incorrect size', 'wordpress-importer') );
		}

		if ( 0 == $filesize ) {
			@unlink( $upload['file'] );
			return new WP_Error( 'import_file_error', __('Zero size file downloaded', 'wordpress-importer') );
		}

		$max_size = (int) $this->max_attachment_size();
		if ( ! empty( $max_size ) && $filesize > $max_size ) {
			@unlink( $upload['file'] );
			return new WP_Error( 'import_file_error', sprintf(__('Remote file is too large, limit is %s', 'wordpress-importer'), size_format($max_size) ) );
		}

		// keep track of the old and new urls so we can substitute them later
		$this->url_remap[$url] = $upload['url'];
		//$this->url_remap[$post['guid']] = $upload['url'];
		// keep track of the destination if the remote url is redirected somewhere else
		if ( isset($headers['x-final-location']) && $headers['x-final-location'] != $url ){
			$this->url_remap[$headers['x-final-location']] = $upload['url'];
                }
                        
		//add to media library
		//Attachment options
		$attachment = array(
			'post_title'=> $file_name,
			'post_mime_type' => $headers['content-type']
		);

		$attach_id = wp_insert_attachment( $attachment, $upload['file'], $post_id );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $url );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		return $upload;
	}

	/**
	 * Decide what the maximum file size for downloaded attachments is.
	 * Default is 0 (unlimited), can be filtered via import_attachment_size_limit
	 *
	 * @return int Maximum attachment file size to import
	 */
	function max_attachment_size() {
		return apply_filters( 'import_attachment_size_limit', 0 );
	}

}
