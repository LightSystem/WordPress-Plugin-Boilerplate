<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   RSS-Sync
 * @author    João Horta Alves <joao.alves@log.pt>
 * @license   GPL-2.0+
 * @copyright 2014 João Horta Alves
 */

class RSS_Sync_AdminSettings
{
    static function admin_init ()
    {

        register_setting( 'rss_sync', 'rss_sync', array( 'RSS_Sync_AdminSettings', 'options_validator' ) );
        
        add_settings_section( 'rss_sync_options',
            __( 'General', 'rss-sync' ),
            array( 'RSS_Sync_AdminSettings', 'rss_options' ),
            'rss_sync' );
            
        add_settings_field( 'rss_sync_rss_feeds',
            __( 'RSS Feeds', 'rss-sync' ),
            array( 'RSS_Sync_AdminSettings', 'rss_feeds' ),
            'rss_sync',
            'rss_sync_options' );
       
       	add_settings_field( 'rss-sync-refresh',
       		__('Refresh Feed', 'rss-sync'),
       		array('RSS_Sync_AdminSettings', 'rss_sync_refresh'),
       		'rss_sync',
            'rss_sync_options' );

        add_settings_field( 'rss_sync_img_storage', 
            __('Image Storage', 'rss-sync'), 
            array('RSS_Sync_AdminSettings', 'image_storage_options'), 
            'rss_sync', 
            'rss_sync_options' );
    }

    /* BEGIN APP SETTINGS FORM CALLBACKS */    
    static function rss_sync_app_page () {
        ?>
        <div class="wrap">
            <?php screen_icon( 'options-general' ); ?>
            <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
            <form action="options.php" method="POST">
            <?php
                settings_fields( 'rss_sync' );
                do_settings_sections( 'rss_sync' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    function rss_options ()
    {
        echo '<p>'; _e( 'General settings for the RSS Sync Plugin.', 'rss-sync' ); echo '</p>';
    }
    
    function rss_feeds ()
    {
        $options = get_option( 'rss_sync' );
        
        ?><fieldset>
            <legend class="screen-reader-text"><span><?php _e( 'RSS Feeds', 'rss-sync' ); ?></span></legend>
            <textarea id="rss_sync_rss_feeds" rows="10" cols="50" name="rss_sync[rss_feeds]"/><?php echo $options['rss_feeds'] ?></textarea><br/>
            <label for="rss_sync_rss_feeds"><?php _e( 'Describe here what feeds are to be used. One per line.', 'rss-sync' );?></label>
        </fieldset>
        <?php
    }

    function rss_sync_refresh()
    {
    	$options = get_option( 'rss_sync' );
    	$prev_chosen_recurrence = $options['refresh'];

    	$recurrences = wp_get_schedules();

    	?><fieldset>
            <legend class="screen-reader-text"><span><?php _e( 'RSS Refresh', 'rss-sync' ); ?></span></legend>
            <select id="rss_sync_refresh" name="rss_sync[refresh]">
            	<?php foreach ($recurrences as $key => $a_recurrence) { ?>
            	<option value="<?php echo $key ?>" <?php if($prev_chosen_recurrence == $key) echo 'selected' ?> >
            		<?php echo $a_recurrence['display'] ?>
            	</option>
            	<?php } ?>
            </select>
            <br/>
            <label for="rss_sync_refresh"><?php _e( 'Default: Once Daily.', 'rss-sync' );?></label>
        </fieldset>
        <?php
    }

    function image_storage_options()
    {
        $options = get_option( 'rss_sync' );
        $stored_option = $options['img_storage'];

        ?><fieldset>
            <legend class="screen-reader-text"><span><?php _e( 'Image Storage', 'rss-sync' ); ?></span></legend>
            <select id="image_storage_options" name="rss_sync[img_storage]">
                <option value="hotlinking" <?php if($stored_option == 'hotlinking') echo 'selected' ?> > <?php _e('Use hotlinking', 'rss-sync') ?> </option>
                <option value="local_storage" <?php if($stored_option == 'local_storage') echo 'selected' ?> > <?php _e('Link to media gallery', 'rss-sync') ?> </option>
            </select>
            <br/>
            <label for="image_storage_options">
                <?php _e("Note: For featured/thumbnail images to work in posts, choose 'Link to media gallery'"); ?>
            </label>
        </fieldset>
        <?php
    }
    
    static function options_validator ( $options )
    {
        $existing = get_option( 'rss_sync' );

        if (!is_array( $existing ) || !is_array( $options ))
            return $options;

        if($existing['rss_feeds'] != $options['rss_feeds']){
            include_once( ABSPATH . 'wp-content/plugins/rss-sync/includes/class-rss-sync-tools.php' );

            $tools = RSS_Sync_Tools::get_instance();
            $tools->rss_sync_fetch($options['rss_feeds']);
        }

        if($existing['refresh'] != $options['refresh']){

        	wp_clear_scheduled_hook( 'rss_sync_event' );

        	wp_schedule_event( time(), $options['refresh'], 'rss_sync_event' );
        }

        return array_merge( $existing, $options );
    }
    
    /* END APP SETTINGS FORM CALLBACKS */
}
