=== WordPoints ===
Contributors: jdgrimes
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=TPXS6B98HURLJ&lc=US&item_name=WordPoints&item_number=wordpressorg&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted
Tags: points, awards, rewards, cubepoints, credits, gamify
Requires at least: 3.7
Tested up to: 3.9-alpha-27092
Stable tag: 1.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Gamify your site with points.

== Description ==

This plugin lets you create one or multiple types of points which you can use to
reward your users by "hooking into" different user actions.

You can currently award points to users for...

* Registration
* Comments - You can also have points removed if you delete a user's comment or mark it as spam.
* Posts - You can be selective in which post types get awarded points, and award different amounts for different types. As with comments, you can have points removed when a post is deleted.
* Visiting your site - You can award points to a user when they visit your site at least once in a time period, once per day, for example.

All points transactions are logged and can be reviewed by administrators and
displayed on the front end of your site using the [`[wordpoints_points_logs]`](http://wordpoints.org/user-guide/points-shortcodes/wordpoints_points_logs/)
shortcode.

You can also display a list of the top users based on the number of points they have
using the [`[wordpoints_points_top]`](http://wordpoints.org/user-guide/points-shortcodes/wordpoints_points_logs/) shortcode.

The plugin also provides [several widgets](http://wordpoints.org/user-guide/widgets/) that you can use.

Many more features a planned in the near future, and you can check out the roadmap on
the plugin website, [WordPoints.org](http://wordpoints.org/roadmap/).

It is also possible to extend the default functionality of the plugin using modules.
For more information on that, see the [developer docs](http://wordpoints.org/developer-guide/).

== Installation ==

Before installing on multisite, it is recommended that you [read this](http://wordpoints.org/user-guide/multisite/).

1. Download and unzip the plugin file
1. Upload the resulting `/wordpoints/` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. You can set up the points hooks to your liking by clicking on the Points Hooks submenu item

== Frequently Asked Questions ==

= Why doesn't WordPoints have (some feature)? =

Maybe it will soon - just ask for it!

= How can I manually change a users points? =

You can manually adjust a user's points from their profile page in the admin.

= Why does WordPoints have only one component? =

I plan to add more components in future, but right now these are still under
development. Find out more [here](http://wordpoints.org/roadmap/).

= Does WordPoints support Multisite? =

Yes, WordPoints fully supports multisite. It is recommended that you [read up on it here](http://wordpoints.org/user-guide/multisite/)
before you install it.

= Why doesn't WordPoints support my old outdated WordPress version? =

Precisely because it is old, outdated, and most importantly, insecure. Backup and
upgrade now before it's too late. Seriously!

== Screenshots ==

1. An example of a table of points log entries.

2. The Points Hooks administration screen. This is where you configure when and where
points are awarded.

== Changelog ==

= 1.2.0 =
* New: Support for WordPress multisite See [here](http://wordpoints.org/user-guide/multisite/) for full details.
* New: Network-wide points hooks for multisite.
* Fixed: Delete the points logs for a user when they are deleted.
* Fixed: Clean up the points logs for a post when it is deleted, removing the link.
* Fixed: Clean up the points logs for a comment when it is deleted, removing the link and comment text.

= 1.1.2 =
* Fixed: The post points hook was awarding points for auto-drafts and post revisions.
* Fixed: The periodic points hook wasn’t working in some cases.
* Fixed: There was a fatal error in the uninstall script, causing a blank screen when uninstalling the plugin.

= 1.1.1 =
* New: The Brazilian Portugese language file was added to the plugin, thanks to Gabriel Galvao.
* Fixed: The language files are being loaded properly now.
* Fixed: The points component is activated by default. (We tried and failed to do that back in 1.0.1).

= 1.1.0 =
* New: You can now add points hooks to a points type by clicking on the hook and
choosing the points type to add it to.
* New: The plugin is fully translatable, and pot file is now included in `/languages`.
* New: For developers, this version introduces an [improved modules API](http://wordpoints.org/developer-guide/modules/).
* New: Also for developers, the logs query class now implements `WP_Meta_Query` and `WP_Date_Query`.
* Fixed: Use the correct post type name in the points logs instead of the generic "Post".

= 1.0.1 =
* Fixed: The points component is now activated upon installation.
* Fixed: Module/component activation user experience improved slightly.

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.2.0 =
* This is a major update which adds full support for WordPress multisite.

= 1.1.2 =
* This update is a maintenance release with some fixes for bugs in the periodic and post points hooks and the uninstall routine.

= 1.1.1 =
* This update is a maintenance release with some localization and installation
improvements. It is recommended that all users upgrade.

= 1.1.0 =
* This update includes improvements to the points hooks UI, and also many improvements
to the plugin's core code, paving the way for more new features in the near future.

= 1.0.1 =
* This is a minor release with a few small fixes/improvements mainly aimed at initial
installation - but it's recommended that you upgrade.

= 1.0.0 =
* This is the initial release
