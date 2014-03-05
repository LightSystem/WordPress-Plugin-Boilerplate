=== WordPress Plugin RSS Synchronization ===
Contributors: LightSystem
Tags: RSS, plugin, wordpress
Requires at least: 3.8
Tested up to: 3.8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Use this plugin if you wish to read external RSS feeds into WordPress posts. The plugin is configurable letting you define what feeds to read and how often to read them. It also lets you decide whether you want to store the external images locally, inserting them into the media gallery, or otherwise simply hotlink them (that is the default).

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
3. Select `plugin-name.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `plugin-name.zip`
2. Extract the `plugin-name` directory to your computer
3. Upload the `plugin-name` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png`
(or jpg, jpeg, gif).
2. This is the second screen shot

== Changelog ==

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

== Updates ==

This plugin supports the [GitHub Updater](https://github.com/afragen/github-updater) plugin, so if you install that, this plugin becomes automatically updateable direct from GitHub. Any submission to WP.org repo will make this redundant.
