=== WordPoints ===
Contributors: jdgrimes
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=TPXS6B98HURLJ&lc=US&item_name=WordPoints&item_number=wordpressorg&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted
Tags: points, awards, rewards, credits, gamify, ranks, games
Requires at least: 4.7
Tested up to: 5.0-alpha-42970
Stable tag: 2.4.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Gamify your site, track user rep, or run a rewards program. WordPoints has a powerful core, infinitely extendable via add-ons.

== Description ==

= Features =

This plugin lets you create one or multiple types of points which you can use to reward your users when certain events occur on your site. It also includes a Ranks component, which lets you create ranks for your users based on how many points they have.

You can currently award points to users for:

* **Registration**
* **Posts** - You can be selective in which post types get awarded points, and award different amounts for different types. Points will automatically be removed when a post is removed.
* **Comments** - You can award points to a user when they leave a comment, and also to post authors when they receive a comment. As with posts, you can award different amounts for comments on different post types, and points will automatically be removed if you delete a user's comment or mark it as spam.
* **Visiting your site** - You can award points to a user when they visit your site at least once in a time period; once per day, for example.

You can also conditionally award points based on a post's tags, a comment's text, a user's role, and more!

All points transactions are logged and can be reviewed by administrators from the *WordPoints » Points Logs* admin screen. The logs can be displayed on the front end of your site using the [`[wordpoints_points_logs]`](https://wordpoints.org/user-guide/shortcodes/wordpoints_points_logs/) shortcode.

You can show how many points a user has using the [`[wordpoints_points]`](https://wordpoints.org/user-guide/shortcodes/wordpoints_points/) shortcode. You can also display a list of the top users with the most points using the [`[wordpoints_points_top]`](https://wordpoints.org/user-guide/shortcodes/wordpoints_points_top/) shortcode.

You can display a list of ways that your users can earn points using the [`[wordpoints_how_to_get_points]`](https://wordpoints.org/user-guide/shortcodes/wordpoints_how_to_get_points/) shortcode.

The plugin also provides [several widgets](https://wordpoints.org/user-guide/widgets/) you can use for the same things as the shortcodes.

More features are always being planned, and you can check out the roadmap on the plugin website, [WordPoints.org](https://wordpoints.org/roadmap/).

Also on the plugin's website, you can [browse the available extensions](https://wordpoints.org/extensions/). There's [an extension that imports from CubePoints to WordPoints](https://wordpoints.org/extensions/importer/), one that [integrates with WooCommerce](https://wordpoints.org/extensions/woocommerce/), one that [integrates with BuddyPress](https://wordpoints.org/extensions/buddypress/), and another that let's you [reset your users' points](https://wordpoints.org/extensions/reset-points/). More are being added regularly, so take a look to see what is new.

= Developers =

If you are a developer, designer, or accessibility expert, and you'd like to give back to this plugin, you should visit the [plugin's repo on GitHub](https://github.com/WordPoints/wordpoints/), where active development takes place.

If you are interested in integrating or extending the plugin, you'll want to read the [developer docs](https://wordpoints.org/developer-guide/).

If you are a security researcher you can report vulnerabilities through our [bug bounty program on HackerOne](https://hackerone.com/wordpoints).

= Privacy Policy =

WordPoints does not communicate with any remote services by default. However, when you install extensions from WordPoints.org or other servers, WordPoints may communicate with those services in order to provide you updates for the extensions. Check the privacy policy of the extension server in question to learn more about what information is shared with it, though generally only the ID and version of the extension will be sent to the update service.

== Installation ==

Before installing on multisite, it is recommended that you read this [explanation of how WordPoints works on multisite](https://wordpoints.org/user-guide/multisite/).

1. Download and unzip the plugin file
1. Upload the resulting `/wordpoints/` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. You can set up the points types to your liking by clicking on the *WordPoints » Points Types* menu item
1. If you want to use ranks, you can activate the Ranks component on the *WordPoints » Settings* screen on the Components tab.

== Frequently Asked Questions ==

= How can I manually change a user's points? =

You can [manually adjust a user's points](https://wordpoints.org/user-guide/manually-editing-a-users-points/) from their profile page in the admin.

= Does WordPoints support Multisite? =

Yes, WordPoints fully supports multisite. It is recommended that you read this [explanation of how WordPoints works on multisite](https://wordpoints.org/user-guide/multisite/) before you install it.

= When will WordPoints have (some feature)? =

You can see what we're currently planning on our [roadmap](https://wordpoints.org/roadmap/), and find out how to request new features.

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

= 2.4.2 — 2018-05-08 =

##### Fixed

- Conflicts with some plugins (PayPal for WooCommerce, WCMp) causing fatal errors
  on the Points Types admin screen.
- Performance improvements of the code that powers the Rate Limits for the Visit
  event.

= 2.4.1 — 2017-11-13 =

##### Fixed

- Fatal errors when a rank type was not recognized. This should usually never happen.
- More fatal errors when viewing the points logs after deactivating BuddyPress.
- "Update package not found" errors when attempting to update an extension.

= 2.4.0 — 2017-10-26 =

**Requires: WordPress 4.7+**

##### Added

- Extensions can now be updated from within the admin screens, just like plugins can.
  If you have installed any extensions from WordPoints.org, you may be prompted to
  enter your license key after updating. You can get your license key for an
  extension from the [My Account](https://wordpoints.org/my-account/) page.
- New `[wordpoints_rank_list]` shortcode to display a list of the ranks that users
  can have on the site.
- Reaction settings can now include conditions on a post's terms. This means that
  points can now be awarded for posts based on tags, categories, or custom
  taxonomies.
- When no Reactions have been created for a points type yet, WordPoints will offer to
  create some demo reactions to help you get started.

##### Changed

- "Modules" are now called "extensions". The term "extension" is more familiar and
  obvious in its meaning than "module".
- When deleting a points type, you are now required to type the name of the points
  type in the confirmation dialog. This helps to ensure that points types aren't
  deleted accidentally. In addition, the confirmation dialogs for when a Reaction or
  Rank is being deleted have been updated to so the reaction description or rank
  name, respectively, so that it is clear which rank or reaction is being deleted.

##### Fixed

- The Top Users points table will always order results for users with same number of
  points consistently, rather than it being more-or-less random. If two users have
  the same number of points, the one with the lowest ID will come before the one with
  the higher ID number. Thanks to Gspin96 for the patch!
- Improved performance of the Ranks component. Creating ranks should be much quicker
  on sites with large numbers of users now.
- The Max setting of the Contains condition for Reactions now interprets an empty max
  to mean no maximum, rather than a maximum of 0.
- Corrected the invalid HTML that was breaking the user profile admin screen.
- Fatal errors when viewing points logs after deactivating the BuddyPress plugin.

= 2.3.0 — 2017-03-14 =

##### Security

- Hardening: Module files are now validated before deletion. Previously a user with
 the capabilities to manage modules could list and delete any directory via the
 Delete Module admin screen.

##### Added

- The ability to disable an event reaction without deleting it.
- The ability to set Conditions for a reaction on the post title, excerpt, and
 comment count.
- Support for setting Conditions for a reaction on numeric attributes of an entity
 involved in an event (for example, post comment count). Available conditions are
 currently Equals, Greater Than, and Less Than.
- The ability to set Conditions for a reaction or award points based on the parent of
 a page or other hierarchical post type.
- The ability to set Conditions for a reaction on the comment content, or attributes
 of the parent comment if the comment is a reply.
- Support for events that involve multiple entities (like when a user is added to a
 group, for example).
- Better support for right-to-left locales.
- More comments to aid in translation of the plugin.
- Meta box with shortcode examples to the Points Types screen.

##### Changed

- CSS and JS files to be minified.
- Reactions UI to improve accessibility for unsighted users by allowing success and
 error messages to be spoken aloud by assistive technology.
- Network-active modules to also be displayed on the per-site Modules screens on
 multisite.
- Widgets to improve display in small sidebars. The settings now allow for some of
 the columns in the points logs tables to be hidden, horizontal scrolling to be
 enabled, and the user names to be hidden so that just the avatars are displayed.
 Also makes it so that the user rank is not displayed next to the user name within
 the widgets.
- The Comment Leave event to only be fired for comments, not pingbacks or trackbacks.
- The name of the "Configure" screen to "Settings" instead.

##### Fixed

- Error messages being displayed when first adding a widget to the site via the
 Customizer.

= Older Versions =

If you'd like to view the changelog for older versions, see the [changelog.txt](https://plugins.svn.wordpress.org/wordpoints/trunk/changelog.txt) file included with the plugin.

== Upgrade Notice ==

= 2.4.2 =
Fixes a few conflicts and improves performance of the code for the Rate Limits used
by the Visit event.

= 2.4.1 =
Fixes a few errors that occurred under rare circumstances.

= 2.4.0 =
Adds support for extension updates, a shortcode to display available ranks, the
ability to award points based on post terms, and also improves performance when
creating ranks.

= 2.3.0 =
**Includes minor security hardening.** Adds the ability to disable reactions,
provides more possibilities for reaction conditions, and improves the display of the
widgets on narrow sidebars.
