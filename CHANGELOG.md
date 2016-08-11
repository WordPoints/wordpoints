# Change Log for WordPoints

All notable changes to this plugin will be documented in this file.

This plugin adheres to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com/).

This is the developer changelog for WordPoints. For a user-centric changelog, see [src/readme.txt](src/readme.txt).

## [Unreleased]

### Added

- This changelog.

## [2.1.0] - 2016-08-10

### Requires

- WordPress 4.4+

### Added

- New Apps API consisiting of Apps and Class Registries. #321
 - Hooks, Entities, and Data Types apps.
 - Regular, Children, and Persistent class registries.
- New Hooks API consisting of Actions, Args, Events, Router, Fires, Extensions, Reactors, Reaction Stores, and Reactions. #321
 - Post Type, Comment, New Comment, and Post Depubish Delete hook action objects.
 - Registration of User Delete (User arg), User Regsiter (User arg), and User Visit (Current User arg) hook actions.
 - Registration of Post Publish (Post arg), Post Depublish (Post arg), and Post Depublish Delete (Post arg) hook actions for each public post type except for attachments.
 - Registration of Add Attachment (Post arg) action for attachments.
 - Registration of Post Delete (Post arg) action for all public post types.
 - Registration of the New Comment (Comment arg), Comment Approve (Comment arg), and Comment Disapprove (Comment arg) actions for each public post type supporting comments.
 - Current User hook arg object.
 - Comment Leave, Media Upload, Post Publish, User Register, and User Visit hook events.
 - Registration of the Post Publish event for every public post type.
 - Registration of the Comment Leave event for every post type that supports comments.
 - Blocker, Repeat Blocker, Points Legacy Repeat Blocker, Reversals, Points Legacy Reversals, Conditions, Periods, and Points Legacy Periods hook extensions.
 - Entity Array Contains, String Contains, and Equals hook conditions.
 - Points and Points Legacy hook reactors.
 - Options and Network Options hook reaction stores.
 - Options hook reaction.
 - Reaction validator.
- New Entities API consisiting of Entities, Entity Attributes, Entity Relationships, Entity Arrays, Entity Hierarchies, and Entity Contexts. #321
 - Comment (Post and Author relationships), Post (Author relationship, Content attribute), User (Role relationship), and User Role entities.
 - Site and Network entity contexts.
- New Data Type API consisiting of Data Types. 
 - Integer and Text data types.
- PHP class Autoloader. #321
- Admin Screen API, consisting of Screens. #321
- New Hooks UI API, consisting of Backbone templates and objects for Events, Reactors, Reactions, Extensions, and Fields. #321
- Support for registering Backbone templates for any script via `wordpoints-templates` data key for that script handle. #321
- Points Types administration screen, with support for creating, editing, and deleting points types, and for creating, editing, and deleting Reactions to award users points when Events occur. #321
- `WordPoints_Points_Legacy_Hook_To_Reaction_Importer` to import old points hooks to the new Hooks API. #321
- New DB Query bootstrap class. #321
- Autoloader for the PHPUnit tests' helper classes and testcases.
- Automatic catching of database errors during the PHPUnit tests.
- PHPUnit factories for Post Types, User Roles, Entities, and Hook Actions, Conditions, Events, Extensions, Reactions, Reaction Stores, and Reactors.
- PHPUnit mocks for Silent Apps, Data Types, Entities, Entity Attributes, Entity Children, Entity Contexts, Out-of-state Entity Contexts, Contexted Entities, Entity Relationships, Array Entity Relationships, Dynamic Entity Relationships, Dynamic Array Entity Relationships, Restircted Visibility Entities, Unsettable Entities, Entityishs, Hook Actions, Post Type Hook Actions, Hook Args, Hook Conditions, Unmet Hook Conditions, Hook Extensions, Hook Events, Dynamic Hook Events, Miss Listener Hook Extensions, Hook Reactions, Hook Reaction Stores, Contexted Hook Reaction Stores, Hook Reactors, the Hooks App, and two separate Object mocks.
- PHPUnit testcases for Hooks-related code, Entities, Events, Dynamic Events, and Class Registries.
- Grunt task runner configuration for automatic building of PHP autoload files, JS files from node modules, and CSS from SASS files.
- Codeception tests.
- Support for wildcards in meta keys in the un/installer. #395
- Support for wildcards in network options in the un/installer. #379
- Support for uninstalling meta boxes to the un/installer. #379
- `$unique` parameter to `wordpoints_add_points_log_meta()`. #347
- `'wordpoints_user_supplied_shortcode_atts'` filter for shortcode attributes in `WordPoints_Shortcode::expand()`. #367
- An error to be displayed when shortcodes are supplied an unrecognized non-integer value for the `user_id` attribute. #368
- `'wordpoints_delete_points_type'` action to the `wordpoints_delete_points_type()` function. #361
- `wordpoints_user_can_view_points_log()` function with the `'wordpoints_user_can_view_points_log'` filter. #423
- `$user_id` parameter to the `"wordpoints_user_can_view_points_log-{$log->log_type}"` filter. #423

### Changed

- All action hooks were moved out of the function defining files and into `filters.php` files in the corresponding directores. #335
- Points hooks are now registered within the new `wordpoints_register_points_hooks()` function, instead of in the hook class files. #50
- Moved the `wordpoints_register_core_ranks()` function from `rank-types.php` to `ranks.php` with the other ranks functions.
- The `.hidden` class is used to hide elements on the administration screens instead of using `dispaly: none;`. #334
- All admin notices to use the new notice classes, including `wordpoints_show_admin_message()`. #222
- Dismissal of admin notices tied to options to use Ajax requests to delete the option from the database. #222
- All functions to expect unslashed data. Affects the points log and rank meta functions. #364
- The uninstall PHPUnit tests to be in a seperate test suite. 
- Heading levels on the administration screens to match the post-4.3 levels in WordPress. #340
- The uninstall PHPUnit tests to activate a module in the usage simulator, for greater realism. #380
- The points component un/installer to use wildcard settings in `$uninstall` to uninstall user points metadata. #395
- The uninstall PHPUnit tests to simulate usage of network points hooks. #400
- The uninstallation of list tables in the un/installer to be more granular. #403
- The legacy Points Hooks screen to only show hook types that are enabled. #386
- Links to the legacy Points Hooks screen to point to the new Points Types screen. #405
- The un/installers to automatically set the correct mode of the Hooks API during install/update/uninstall. #408
- The maximum length of points hook names to 191 characters. #83
- The How To Get Points shortcode to include reactions from the Points Types screen.
- Most admin notices to be dismissible. #222
- The Rank Type dropdown on the Ranks screen to have a label, for better accessibility. #346

### Deprecated

- The Points Hooks administration screen. Still needed for legacy installs and legacy modules that still register Points Hooks.
- `WordPoints_Ajax_UnitTestCase` in favor of `WordPoints_PHPUnit_TestCase_Ajax`.
- The `$uninstall['list_tables']` property of un/installers in favor of `$uninstall[*]['list_tables']`.
- Legacy points hooks in favor of the new Hooks API. They are now disabled on new installs.
- The `wordpoints_*_network_option()` functions in favor of new `wordpoints_*_maybe_network_option()` functions. #305
- The points component's `wordpoints_admin_register_scripts()` function in favor of `wordpoints_points_admin_register_scripts()`. #370
- Bundled translation files in favor of WordPress.org language packs. They will be removed as language packs become available. #355
- Assuming that the current user is the one being shown the log in filter functions hooked to `"wordpoints_user_can_view_points_log-{$log->log_type}"`, in favor of the `$user_id` parameter. #423

### Removed

- The ability to create, update, and delete points types on the legacy Points Hooks screen, in favor of the new Points Types screen.
- The Greek, Japanese, and Simplified Chinese translation files, in favor of WordPoints.org language packs. #355

### Fixed

- All uses of WordPress's meta APIs to slash the data passed to them, as it is expected slashed. #364
- SLQ syntax error from missing closing parenthesis in the `WordPoints_Points_Logs_Query` class when ordering the query by `meta_key`. #369
- Network admin menu being displayed on multisite even when not network active. #362
- "Part of installation skipped" notice being shown for the ranks component when it was network-installed on multisite, even though none of the installation was really skipped.
- Per-site updates are no longer run when there aren't any. #392
- Modules using the new installables API not being uninstalled. #380
- Fatal error on uninstallation of the plugin if any modules were present. #380
- SQL errors when validating site IDs in un/installers, if the list of IDs that the installable is installed on is empty. #394
- Uninstall PHPUnit tests to actually run with WordPoints network-active when `WORDPOINTS_NETWORK_ACTIVE` is true. #395
- Legacy-related points hook options not being uninstalled.
- Breaking update check options not being uninstalled.
- The default value of the Periodic points hook not being set correctly.
- The Post Delete and Comment Removed legacy points hooks not being registered on legacy sites where they are still in use. We also disable deprecated notices for them on legacy sites.
- Symlinked modules using the intallables API not being installed. #429
- Notices for ophaned comments in comment points hooks. #436

[unreleased]: https://github.com/WordPoints/wordpoints/compare/2.1.0...HEAD
[2.1.0]: https://github.com/WordPoints/wordpoints/compare/2.0.2...2.1.0
