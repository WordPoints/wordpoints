=== WordPoints ===
Contributors: jdgrimes
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=TPXS6B98HURLJ&lc=US&item_name=WordPoints&item_number=wordpressorg&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted
Tags: points, awards, rewards, cubepoints, credits, gamify, multisite, ranks
Requires at least: 3.8
Tested up to: 4.2-alpha-31007
Stable tag: 1.9.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Gamify your site with points.

== Description ==

= Features =

This plugin lets you create one or multiple types of points which you can use to
reward your users by "hooking into" different user actions. WordPoints also includes
a Ranks component, which lets you create ranks for your users based on how many
points they have.

You can currently award points to users for:

* Registration
* Posts - You can be selective in which post types get awarded points, and award different amounts for different types. Points will automatically be removed when a post is deleted.
* Comments - You can award points to a user when they leave a comment, and also to post authors when they receive a comment. As with posts, you can award different amounts for comments on different post types, and points will automatically be removed if you delete a user's comment or mark it as spam.
* Visiting your site - You can award points to a user when they visit your site at least once in a time period; once per day, for example.

All points transactions are logged and can be reviewed by administrators from the
WordPoints » Points Logs admin screen. The logs can be displayed on the front end of
your site using the [`[wordpoints_points_logs]`](http://wordpoints.org/user-guide/points-shortcodes/wordpoints_points_logs/)
shortcode.

You can also display a list of the top users with the most points using the
[`[wordpoints_points_top]`](http://wordpoints.org/user-guide/points-shortcodes/wordpoints_points_logs/)
shortcode.

You can display a list of ways that your users can earn points using the
[`[wordpoints_how_to_get_points]`](https://wordpoints.org/user-guide/shortcodes/wordpoints_how_to_get_points/)
shortcode.

The plugin also provides [several widgets](http://wordpoints.org/user-guide/widgets/).

More features are always being planned, and you can check out the roadmap on the
plugin website, [WordPoints.org](http://wordpoints.org/roadmap/).

= Localization =

WordPoints is fully localizable, and translations are already available in several
languages:

* **(es) Spanish** — Thanks to Andrew Kurtis of WebHostingHub.
* **(ja) Japanese** — Thanks to Raymond Calla.
* **(pt_BR) Brazilian Portuguese** — Thanks goes to Gabriel Galvão ([@threadsgeneration](https://profiles.wordpress.org/threadsgeneration)).
* **(zh_CN) Simplified Chinese** — Thanks to Jack Lee ([@suifengtec](https://profiles.wordpress.org/suifengtec)).

Not all of these translations are complete, and if you'd like to help maintain and
improve them, or you'd like to translate WordPoints into another language, you can
join the [translation project on Weblate](https://hosted.weblate.org/engage/wordpoints/).

If you have a completed translation you'd like to share, you can also send it to us
using the [contact form on WordPoints.org](http://wordpoints.org/contact/).

= Developers =

If you are a developer, designer, or accessibility expert, and you'd like to give
back to this plugin, you should visit the [plugin's repo on GitHub](https://github.com/WordPoints/wordpoints/),
where active development takes place.

If you are interested in integrating or extending the plugin, you'll want to read the
[developer docs](http://wordpoints.org/developer-guide/).

If you are a security researcher you can report vulnerabilities through our
[bug bounty program on HackerOne](https://hackerone.com/wordpoints).

== Installation ==

Before installing on multisite, it is recommended that you read this
[explanation of how WordPoints works on multisite](http://wordpoints.org/user-guide/multisite/).

1. Download and unzip the plugin file
1. Upload the resulting `/wordpoints/` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. You can set up the points hooks to your liking by clicking on the Points Hooks submenu item
1. If you want to use ranks, you can activate the Ranks component on the WordPoints » Configure screen on the Components tab.

== Frequently Asked Questions ==

= Why doesn't WordPoints have (some feature)? =

Maybe it will soon - just ask for it!

= How can I manually change a user's points? =

You can [manually adjust a user's points](http://wordpoints.org/user-guide/manually-editing-a-users-points/) from their profile page in the admin.

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

3. A rank group on the Ranks administration screen. This is where you create and
manage the ranks used on your site.

4. An example of the `[wordpoints_points_top]` shortcode.

5. An example of the `[wordpoints_how_to_get_points]` shortcode.

== Changelog ==

= 1.9.2 — (~2 hrs) =
* Fixed: A bug preventing updating a user's points from their admin profile when
they don't have any points yet.

= 1.9.1 — (~3 hrs) =
* Fixed: A bug preventing ranks from being saved or updated.

= 1.9.0 - (~65 hrs) =
* Updated: The title for the "Points" column in the points logs and top users tables
will be replaced with the name of the points type being displayed.
* Updated: Display the number of points a rank is for in the rank's title bar (on
the Ranks admin screen).
* Updated: Cache user ranks. May improve performance.
* Updated: Combine the Comment and Comment Removed points hooks once again. The
Comment hook will automatically remove the points if the comment gets removed, and
the Comment Removed hook is hidden on new sites. The old behavior is retained as
needed for current installs, but the Comment Removed hook will likely be removed in
2.0, so you are recommended to stop using it now.
* Updated: Combine the Post and Post Delete points hooks also. The Post hook now
automatically removes points when a post is deleted. Also, the old behavior is
retained on existing installs, but the Post Delete hook will probably be removed in
2.0 as well.
* Fixed: Better caching for the points logs. This could really improve performance
when viewing the logs.
* Fixed: Warn the user when they attempt to upload a module on the plugins screen.
* Fixed: On multisite, only load network-active modules on the network admin screens.

= 1.8.0 - (~55 hrs) =
* New: You can display the points of the current post's author using the
[`[wordpoints_points]`](http://wordpoints.org/user-guide/shortcodes/wordpoints_points/)
shortcode by supplying `post_author` as the value of the `user_id` attribute, like
this: `[wordpoints_points user_id="post_author"]`.
* New: Award points to post authors for comments they receive with the
[Comment Received](http://wordpoints.org/user-guide/points-hooks/comment-received/)
points hook.
* New: Display a user's rank with the [`[wordpoints_user_rank]`](http://wordpoints.org/user-guide/shortcodes/wordpoints_user_rank/) shortcode.
* Updated: Part of the install and update process is skipped when the plugin is
network activated on a very large multisite network (>10,000 sites).

= 1.7.1 — (~5 hrs) =
* Fixed: Ranks not saving in some cases. Thanks, @atomtheman10, for the report!
* Fixed: XSS vulnerability from the points logs admin screen. It was only exploitable by Administrators and Editors.
* Fixed: CSRF vulnerability for toggling accessibility mode on the Points Hooks screen. It would only have been an annoyance.

= 1.7.0 - (~80 hrs) =
* New: Create ranks for your users by activating [the Ranks component](http://wordpoints.org/user-guide/#ranks).
* * You can manage the ranks on the *WordPoints » Ranks* administration screen.
* * A user's rank is displayed along with their name in the Top Users table.
* * You can display a user's rank using the `%rank` placeholder in the WordPoints widget.
* Updated: Now requires WordPress 3.8+.
* Fixed: Display users who have never been awarded points in the Top Users table when appropriate.
* Fixed: CSRF vulnerability for adding a points type.
* Fixed: CSRF vulnerability for deleting a points type.
* Fixed: Many other small internal improvements.

= 1.6.1 =
* Fixed: Honor the Excluded Users setting in the Top Users widget and shortcode.

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

= 1.9.2 =
* Fixes a bug preventing a user's points from being updated on their admin profile
if they have 0 points.

= 1.9.1 =
* Fixes a bug preventing ranks from being saved or updated.

= 1.9.0 =
* This release deprecates the Comment Removed and Post Delete points hooks. Their
functionality is now combined with the Comment and Post hooks, which now automatically
remove points when a comment or post is deleted, respectively. It includes other
improvements as well, like better caching.

= 1.8.0 =
* This is a feature release that adds a new shortcode to display a user's rank,
lets you display the points of the author of the current post, and makes it possible
to award a post author points when they get a comment on one of their posts.

= 1.7.1 =
* This is a security and bugfix release. It fixes an issue on the Ranks admin screen
that would cause the spinner to continue indefinitely while trying to save a rank.
It also includes patches for two minor security issues.

= 1.7.0 =
* This is the biggest update since 1.0.0! It introduces the Ranks component, and also
includes many other small fixes, including two minor security issues. It is highly
recommended that all users upgrade immediately.

= 1.6.1 =
* This is a maintenance release which fixes the bug of the Excluded Users settings
not being applied to the Top Users widget and shortcode.

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
