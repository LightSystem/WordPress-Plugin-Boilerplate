=== RSS Synchronisation ===
Contributors: LightSystem
Tags: RSS, plugin, wordpress
Requires at least: 3.8
Tested up to: 3.8.1
Stable tag: 0.5.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Use this plugin if you wish to read external RSS feeds into WordPress posts.

== Description ==

The plugin is configurable letting you define what feeds to read and how often to read them. It also lets you decide whether you want to store the external images locally, inserting them into the media gallery, or otherwise simply hotlink them (that is the default).

You will find these settings in your administration page on the Settings->RSS Settings sub-menu.

Check out the [project on GitHub](https://github.com/LightSystem/WordPress-Plugin-RSS-Sync).

== Installation ==

This section describes how to install the plugin and get it working.

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'plugin-name'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `rss-sync.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `rss-sync.zip`
2. Extract the `rss-sync` directory to your computer
3. Upload the `rss-sync` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard

== Changelog ==

= 0.5.3 =
* Added option for toggling using thumbnails.

= 0.5.2 =
* Added support to thumbnail/featured images in posts when 'Link to media gallery' is selected in the settings.

= 0.5.1 =
* Improved behaviour by instantly fetching feeds as soon as they are added in the settings.

= 0.5.0 =
* The plugin can now fetch external images and save them locally, inserting them into the media gallery.

= 0.4.0 =
* Added automatic tagging of posts with RSS feed categories.
* Posts categorized by the origin of the feed.

= 0.3.0 =
* Working version.
* Added a configuration panel.

= 0.2.0 =
* Prototype.
