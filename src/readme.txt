=== WordPoints ===
Contributors: jdgrimes
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=TPXS6B98HURLJ&lc=US&item_name=WordPoints&item_number=wordpressorg&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted
Tags: points, awards, rewards, cubepoints, credits, gamify, multisite
Requires at least: 3.7
Tested up to: 4.0
Stable tag: 1.6.0
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
using the [`[wordpoints_points_top]`](http://wordpoints.org/user-guide/points-shortcodes/wordpoints_points_logs/)
shortcode.

The plugin also provides [several widgets](http://wordpoints.org/user-guide/widgets/)
that you can use.

Many more features are planned in the near future, and you can check out the roadmap
on the plugin website, [WordPoints.org](http://wordpoints.org/roadmap/).

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

= How can I manually change a user's points? =

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

= 1.6.0 =
* New: The value of the main setting for a hook is displayed in its title bar.
* New: Translation into Spanish thanks to Andrew Kurtis of WebHostingHub.
* New: Support for symlinked modules (that's devspeak you don't need to understand, for those of you non-techie users :-)
* New: The HTML classes of the table elements may be filtered by developers.
* Updated: Improved performance when the logs are regenerated after a post or comment is deleted.
* Updated: Better pagination for the points logs tables.
* Updated: Better accessibility for users with screen readers.

= 1.5.1 =
* Fixed: Cyrillic and other non-English characters not displaying correctly in the points logs.
* Fixed: Only post types that support comments are shown as options in the Comment and Comment Removed points hooks.

= 1.5.0 =
* New: The Comment and Comment Removed points hooks now have a post type setting, like the Post points hook.
* New: Translation into simplified Chinese, provided by Jack Lee.
* Updated: The points hooks API for developers has received several improvements.
* * It is now optional to implement the `form()` and `update()` methods when extending `WordPoints_Points_Hook`.
* * The `WordPoints_Post_Type_Points_Hook_Base` class was introduced as a bootstrap for points hooks implementing a post type setting.
* * Other internal improvements, to be continued.
* Updated: The points logs are now cached, offering a performance benefit on sites with persistent caching.
* Updated: The points types are shown in two columns on the Points Hooks administration panel on devices with wide screens.
* Updated: By request, it is now possible to enable the use of HTML in the "WordPoints" widget using this code: `remove_filter( 'wordpoints_points_widget_text', 'esc_html', 20 );`
* Fixed: Module caching was broken because of a code typo.
* Fixed: When network active on multisite, the plugin did not install itself on new sites when they were added to the network.

= 1.4.0 =
* New: Added [`[wordpoints_how_to_get_points]`](http://wordpoints.org/user-guide/shortcodes/wordpoints_how_to_get_points/) shortcode to display a list of active points hooks.
* New: Override hook descriptions shown by the new how to get points shortcode on the hooks admin screen.
* Updated: The current number of points a user has is displayed on their admin profile page to administrators in addition to the inputs.
* Updated: The post points hook has been split in to the Post and Post Delete points hooks.
* Updated: The comments points hook has been split into the Comment and Comment Removed points hooks.
* Updated: Calculate the periods for the periodic points hook relative to the calendar instead of the user's last visit.
* Fixed: Clean the points logs for comment approvals when a post is deleted, removing the dead link to the post.

= 1.3.0 =
* New: User avatars are displayed in the points logs table.
* New: Added [`[wordpoints_points]`](http://wordpoints.org/user-guide/shortcodes/wordpoints_points/) shortcode to display a user's points.
* New: Users' points total may be [stored in a custom meta key](http://wordpoints.org/?p=153), allowing integration with other plugins.
* Fixed: Logs for posts that have become private or protected are hidden from users who can't access them.

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

= 1.6.0 =
* This is a feature release that makes it so you can see the value of the main
setting of a points hook without having to "open" it. Also includes Spanish
translation, accessibility improvements, developer enhancements, and other goodies.

= 1.5.1 =
* This is a bugfix release which fixes an issue where non-English characters aren't
displayed properly in the points logs.

= 1.5.0 =
* This is a feature release adding a post type setting to the comment points hook,
and performance improvements on sites which use persistent caching.

= 1.4.0 =
* This is a feature release, which add the [wordpoints_how_to_get_points] shortcode,
and other improvements.

= 1.3.0 =
* This is a feature release which adds the [wordpoints_points] shortcode, among
other things.

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
