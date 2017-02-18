=== WordPoints ===
Contributors: jdgrimes
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=TPXS6B98HURLJ&lc=US&item_name=WordPoints&item_number=wordpressorg&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted
Tags: points, awards, rewards, cubepoints, credits, gamify, multisite, ranks
Requires at least: 4.6
Tested up to: 4.8-alpha-39357
Stable tag: 2.2.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Gamify your site, track user rep, or run a rewards program. WordPoints has a powerful core, infinitely extendable via add-on modules.

== Description ==

= Features =

This plugin lets you create one or multiple types of points which you can use to
reward your users when certain events occur on your site. It also includes
a Ranks component, which lets you create ranks for your users based on how many
points they have.

You can currently award points to users for:

* Registration
* Posts - You can be selective in which post types get awarded points, and award different amounts for different types. Points will automatically be removed when a post is removed.
* Comments - You can award points to a user when they leave a comment, and also to post authors when they receive a comment. As with posts, you can award different amounts for comments on different post types, and points will automatically be removed if you delete a user's comment or mark it as spam.
* Visiting your site - You can award points to a user when they visit your site at least once in a time period; once per day, for example.

You can also conditionally award points based on a post's contents, a user's role,
and more!

All points transactions are logged and can be reviewed by administrators from the
WordPoints » Points Logs admin screen. The logs can be displayed on the front end of
your site using the [`[wordpoints_points_logs]`](https://wordpoints.org/user-guide/points-shortcodes/wordpoints_points_logs/)
shortcode.

You can display how many points a user has using the [`[wordpoints_points]`](https://wordpoints.org/user-guide/points-shortcodes/wordpoints_points/)
shortcode, and you can also display a list of the top users with the most points using the
[`[wordpoints_points_top]`](https://wordpoints.org/user-guide/points-shortcodes/wordpoints_points_logs/)
shortcode.

You can display a list of ways that your users can earn points using the
[`[wordpoints_how_to_get_points]`](https://wordpoints.org/user-guide/shortcodes/wordpoints_how_to_get_points/)
shortcode.

The plugin also provides [several widgets](https://wordpoints.org/user-guide/widgets/).

More features are always being planned, and you can check out the roadmap on the
plugin website, [WordPoints.org](https://wordpoints.org/roadmap/).

Also on the plugin's website, you can [browse the available extensions](https://wordpoints.org/modules/),
called "modules". There's [a module that imports from CubePoints to WordPoints](https://wordpoints.org/modules/importer/),
one that [integrates with WooCommerce](https://wordpoints.org/modules/woocommerce/),
one that [integrates with BuddyPress](https://wordpoints.org/modules/buddypress/),
and another that let's you [reset your users' points](https://wordpoints.org/modules/reset-points/).
More are being added regularly, so take a look to see what is new.

= Developers =

If you are a developer, designer, or accessibility expert, and you'd like to give
back to this plugin, you should visit the [plugin's repo on GitHub](https://github.com/WordPoints/wordpoints/),
where active development takes place.

If you are interested in integrating or extending the plugin, you'll want to read the
[developer docs](https://wordpoints.org/developer-guide/).

If you are a security researcher you can report vulnerabilities through our
[bug bounty program on HackerOne](https://hackerone.com/wordpoints).

== Installation ==

Before installing on multisite, it is recommended that you read this
[explanation of how WordPoints works on multisite](https://wordpoints.org/user-guide/multisite/).

1. Download and unzip the plugin file
1. Upload the resulting `/wordpoints/` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. You can set up the points hooks to your liking by clicking on the Points Hooks submenu item
1. If you want to use ranks, you can activate the Ranks component on the WordPoints » Configure screen on the Components tab.

== Frequently Asked Questions ==

= How can I manually change a user's points? =

You can [manually adjust a user's points](https://wordpoints.org/user-guide/manually-editing-a-users-points/) from their profile page in the admin.

= Does WordPoints support Multisite? =

Yes, WordPoints fully supports multisite. It is recommended that you [read up on it here](https://wordpoints.org/user-guide/multisite/)
before you install it.

= When will WordPoints have (some feature)? =

You can see what we're currently planning on our [roadmap]((https://wordpoints.org/roadmap/),
and find out how to request new features.

== Screenshots ==

1. An example of a table of points log entries.

2. The Points Types administration screen. This is where you create your points
types and configure when points are awarded in reaction to various events.

3. A rank group on the Ranks administration screen. This is where you create and
manage the ranks used on your site.

4. An example of the `[wordpoints_points_top]` shortcode.

5. An example of the `[wordpoints_how_to_get_points]` shortcode.

6. You can manually edit a user's points on their profile in the administration
screens.

== Changelog ==

This plugin adheres to [Semantic Versioning](http://semver.org/).

= 2.2.2 — 2017-01-14 =

##### Fixed

- Event reactions for custom post types not awarding points. Plugins like bbPress
were effected by this, because they register their post types later in the code
than WordPoints expected. This is now fixed so that WordPoints will work correctly
for post types no matter how late they are registered.

= 2.2.1 — 2017-01-03 =

##### Fixed

- The Points Types screen locking up when creating a new reaction for some events.
This only affected events where conditions could be created for items that could
relate to another item of the same type (like how a comment could have a parent
comment), causing an infinite loop.
- Points values not being formatted with the prefix if the suffix wasn't set, and
vice versa. This would only happen when the value wasn't set at all, not just when it
was empty, and so only applies to points types that were created programmatically.
Points types created through the UI were still formatted as expected.

= 2.2.0 — 2016-12-08 =

**Requires: WordPress 4.6+**

##### Changed

- Rate Limits for event reactions to now support setting the number of
minutes/hours/etc. Previously it was only possible to have rate limits of "once per
minute" or "once per day", now a rate limit can be "once every 5 minutes" or "once
every 2 days" or any other amount that you want.
- Points log entries that are hidden from some users to now be marked as such when a
user who is allowed to see them is viewing them. For example, if a post is not
public, only users who can view that post can view any points logs that relate to it.
Such log entries will now be displayed with a note below them explaining to the
current user that not all other users will be able to view them.
- Points types slugs to be generated from the name of the points type with any
spaces replaced with dashes. Previously when a points type was created, the slug
would be generated from the name, but any spaces would be removed, so if there were
multiple words they would be run together in the slug. Now if you create a points
type named "An Example", its slug would be "an-example", instead of "anexample". This
will not change the slugs of existing points types.

= Older Versions =

If you'd like to view the changelog for older versions, see the
[changelog.txt](https://plugins.svn.wordpress.org/wordpoints/trunk/changelog.txt)
file included with the plugin.

== Upgrade Notice ==
= 2.2.2 =
* Fixes a bug that caused event reactions not to award points for some custom post
types, like bbPress forum topics.

= 2.2.1 =
* Fixes a bug that could cause the Points Types screen to lock up in some
circumstances.

= 2.2.0 =
* Introduces greater flexibility for Rate Limits for event reactions, and now
differentiates points log entries that are hidden from some users.

= 2.1.5 =
* Fixes a bug on multisite that caused network-activated modules to not always be
loaded on all sites on the network.

= 2.1.4 =
* Fixes an issue with points being removed when a published post was updated. Also
includes some minor security hardening.

= 2.1.3 =
* Fixes issues when deleting a module, when deleting a reaction condition, and when
cancelling editing a reaction (on the Points Types screen).

= 2.1.2 =
* Fixes a bug that caused two Rate Limit forms to be displayed for the Visit event
reactions.

= 2.1.1 =
* Fixes the Rate Limits for the Visit event.

= 2.1.0 =
* Introduces a new admin screen for managing points types and how points are awarded.
The old Points Hooks screen remains for now on legacy sites, and continues to work.

= 2.0.2 =
* Fixes a bug in version 2.0.0 that has caused the plugin not to run its installation
script when it was activated. Updating will cause the installation script to be run
automatically if it hasn't been run yet.

= 2.0.1 =
* This is a security hardening release, which fixes a small bug and strengthens two
very minor security weaknesses.

= 2.0.0 =
* This is a breaking update that includes emoji support, some bug fixes, and many
internal code improvements in preparation for new features. Some old code has been
removed, so you should test before upgrading if you are using your own custom
modules.

= 1.10.4 =
* Fixes a bug that caused the number of points a non-admin user has to always be
displayed to them as 0 on the profile screen.

= 1.10.3 =
* This is a security-fix release that addresses three security-related issues. One
vulnerability is not exploitable by default, and the other two issues only occur on
poorly configured servers. All users are still encouraged to upgrade, just in case.

= 1.10.2 =
* Fixes some bugs in the top users table, and also one causing users to be promoted
to the wrong rank.

= 1.10.1 =
* Fixes a bug that caused the My Points widget to display points logs even when the
user was not logged in.

= 1.10.0 =
* This is a minor release which contains a few small improvements and several fixed
bugs.

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
