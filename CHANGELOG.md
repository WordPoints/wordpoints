# Change Log for WordPoints

All notable changes to this plugin will be documented in this file.

This plugin adheres to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com/).

This is the developer changelog for WordPoints. For a user-centric changelog, see [src/readme.txt](src/readme.txt).

## [Unreleased]

Nothing documented at present.

## [2.3.0] - 2017-03-14

### Security

- Hardening: Module files are now validated before deletion. Previously a user with the capabilities to manage modules could list and delete any directory via the Delete Module admin screen.

### Added

- `yarn.lock` file. #583
- Support for events with multiple signature args in the Hooks API. #594
- Minified versions of CSS and JS files, which are served by default. The unminified versions are only used when `SCRIPT_DEBUG` is enabled. #58
- RTL versions of CSS files, which are automatically served on sites using RTL locales. #601
- Translator comments to all gettext calls with placeholders. #603
- Support for handling deprecated query args to `WordPoints_DB_Query`. #546
- `'wordpoints_points_hook_reactor_points_to_award'` filter to filter the number of points to award in the points reactor. #469
- Inline docs for the base JS hook extension object. #469
- `WordPoints_Entity_Attr_Stored_DB_Table_Meta` class to provide a bootstrap for entity attributes that are stored in a meta-style table in the database. #616
- `'wordpoints_htgp_shortcode_reaction_points'` filter to the How To Get Points shortcode to filter the value of the points column for a reaction.
- The ability to disable hook reactions via the Disable extension, which uses the Blocker extension under the hood. #470
- The Title, Excerpt, Date Published, Date Modified, and Comment Count attributes to the post type entities. #422
- The Post Parent relationship for the hierarchical post type entities. #422
- The Date and Content attributes and the Parent Comment relationship for the comment entities. #529
- The `WordPoints_Entity_Relationship_Dynamic_Stored_Field` class for dynamic entity relationships that are stored in an entity field. #529
- Helper functions to prevent updates if WordPoints changes its PHP version requirements in the future. #623
 - Added a function to check the description of each WordPoints update to see if a particular PHP version requirement is specified.
 - If an update of the plugin does have a requirement, we also provide a function that will check if it met by the current install.
 - If an update's required PHP version isn't met, then we remove the update row in the plugins list table and replace it with an error notice explaining the situation and a link to more information.
 - We also hide the update on the Dashboard » Updates screen.
 - And we prevent the user from being able to update via the "View Details" modal.
- `games` tag to `readme.txt`. #612
- Decimal Number data type. #615
- Equals hook condition for the Decimal Number and Integer data types. #615
- Greater Than and Less Than hook conditions for the Integer and Decimal Number data types. #626
- Shortcodes meta box to the Points Types admin screen to display shortcode examples for the points type. #610
- `SECURITY.md` file.

### Changed

- PHPUnit bootstrap to be a part of the dev-lib instead of WordPoints itself. #587
- The name of the `primary_arg_guid` column in the hook hits database table to `signature_arg_guids`. #594
- All CSS to be auto-prefixed. #601
- All images to be minified, without altering them visibly. #58
- Configuration bootstrap for Grunt to be a part of the dev-lib. #58
- The `WordPoints_Points_Logs_Query` class to extend `WordPoints_DB_Query`. #546
- All non-deprecated classes to be isolated in their own file. #498
- Many classes to be in the `classes` directories instead of in `includes`. #372
- Base hook extension class to better support extensions that don't store their settings per-action type. #469
- Base hook extension class to discard settings when `null` is returned from `validate_extension_settings()` and `validate_action_type_settings()`. #469
- Hooks JS code to pass message to `wp.a11y.speak()` when conditions are added/removed and reactions are saved/deleted/editing is cancelled. #291
- Modules list table to also display the network-active modules on the per-site module screens on multisite. #448
- PHPUnit tests to use `assertSame()` instead of `assertEquals()`, and avoid use of `assertEmpty()` and `assertContains()`. #567
- Points Logs widget to improve display on narrow sidebars. Added the option to hide some columns of the table, enable horizontal scrolling, hide the usernames (but still display the avatars), and made sure that fixed layout was not used. #333
- Top Users widget to improve display on narrow sidebars. The user rank is no longer displayed beside thier username, the Position column just uses # as the heading title, and avoids using fixed layout for the table. #333
- User Points widget to extend the points logs widget, to take advantage of new changes to improve display on narrow sidebars. #333
- Modules list table `single_row()` method, refactoring the code to display each column into separate methods. #341
- Modules list table to specify primary column. #354
- `WordPoints_Hook_Extension` to accept the extension slug via the constructor. #619
- The Blocker hook extension to support cross-action type extension settings. #470
- The `WordPoints_Entity_Relationship_Dynamic` class to use the parent slug passed to the constructor rather than requiring the child slug to be dynamic. #529
- The `WordPoints_Entity_Post_Parent` and `WordPoints_Entity_Comment_Post` classes to extend the new `WordPoints_Entity_Relationship_Dynamic_Stored_Field` class. #529
- The Comment Leave event to only fire for comments, not pingbacks, trackbacks, or custom comment types. #622
- Old change log entries to be in `changelog.txt` instead of `readme.txt`. #613
- The Equals hook condition class to check the validity of an attribute value before comparing it with the setting value. #615
- The `DataTypes` collection used in the hooks UI API to be publicly accessible. #624
- "Configure" admin screen title to "Settings". #633

### Deprecated

- Some query args of the `WordPoints_Points_Logs_Query` class (#546): 
 - `orderby` in favor of `order_by`.
 - `user__in` and `user__not_in` in favor of `user_id__in` and `user_id__not_in`, respectively.
 - `blog__in` and `blog__not_in` in favor of `blog_id__in` and `blog_id__not_in`, respectively.
 - Passing `'all'` as the value for `fields`, in favor of just leaving it empty.
 - Passing `'none'` as the value for `order_by`, in favor of just leaving it empty.
- `WordPoints_How_To_Get_Points_Shortcode` class in favor of `WordPoints_Points_Shortcode_HTGP`. #498
- `WordPoints_User_Points_Shortcode` class in favor of `WordPoints_Points_Shortcode_User_Points`. #498
- `WordPoints_Points_Logs_Shortcode` class in favor of `WordPoints_Points_Shortcode_Logs`. #498
- `WordPoints_Points_Top_Shortcode` class in favor of `WordPoints_Points_Shortcode_Top_Users`. #498
- `WordPoints_User_Rank_Shortcode` class in favor of `WordPoints_Rank_Shortcode_User_Rank`. #498
- `WordPoints_My_Points_Widget` class in favor of `WordPoints_Points_Widget_User_Points`. #498
- `WordPoints_Top_Users_Points_Widget` class in favor of `WordPoints_Points_Widget_Top_Users`. #498
- `WordPoints_Points_Logs_Widget` class in favor of `WordPoints_Points_Widget_Logs`. #498

### Removed

- `WordPoints_Entity_Relationship_Stored_Field`'s `$related_ids_field` property and `get_related_entity_ids()` method, as they were unnecessarily overriding the same members in its parent class.
- Old update notices from `readme.txt`. #613
- `cubepoints` and `multisite` tags from `readme.txt`. #612

### Fixed

- Un/installer `get_db_version()` method basing the value on the context instead of the installation status, resulting in the incorrect version being returned when in network context. #604
- An error message regarding the points type not being set being displayed when first adding a widget via the Customizer. #618
- `Fields.create()` not properly converting the data type to the input type due to the `DataTypes` collection not recognizing the IDs of the models it contained. #625

## [2.2.2] - 2017-01-14

### Fixed

- Events for post types registered after the hook actions API is initialized not firing. #491
- PHPCS errors being flagged for git tags, by pinning the WPCS version. #598

## [2.2.1] - 2017-01-03

### Fixed

- Infinite loop on the Points Types screen when an event had args that have relationships with other entities of the same type. #593
- Points values not being formatted with prefix and suffix if one of these was not set. #588
- "Table exists" errors from the uninstaller tests when running against WordPress 4.7+. #595
- PHPCS errors being flagged when working on stable, by pinning the WPCS version.

## [2.2.0] - 2016-12-08

### Added

- This changelog.
- Screen reader content for the modules list table. #447
- `update_module()` method to the base unit test case. #430
- `WordPoints_PHPUnit_TestCase_Ajax_Points`, `WordPoints_PHPUnit_TestCase_Points`, and `WordPoints_PHPUnit_TestCase_Ranks` test case classes. #474
- `WordPoints_PHPUnit_Factory_For_Points_Log` and `WordPoints_PHPUnit_Factory_For_Rank` factory classes. #474
- `WordPoints_PHPUnit_Mock_Filter`, `WordPoints_PHPUnit_Mock_Breaking_Updater`, `WordPoints_PHPUnit_Mock_Module_Installer_Skin`, `WordPoints_PHPUnit_Mock_Points_Hook`, `WordPoints_PHPUnit_Mock_Points_Hook_Post_Type`, `WordPoints_PHPUnit_Mock_Rank_Type`, and `WordPoints_PHPUnit_Mock_Un_Installer` mock classes. #474
- `assertEventRegistered()` and `assertEventNotRegistered()` assertions to the `WordPoints_PHPUnit_TestCase_Hooks` testcase. #503
- Tests for the post-related events using custom post types. #491
- Entity context switching, via `switch_to()` and `switch_back()` methods on entity context classes, and methods of the same names on the new contexts app (`WordPoints_Entity_Contexts`). #479
- Support for passed entity GUIDs to `Entity::set_the_value()`. #535
- `wordpoints_get_post_types_for_entities()`, `wordpoints_get_post_types_for_hook_events()`, `wordpoints_get_post_types_for_auto_integration()`, and the `'wordpoints_post_types_for_auto_integration'` filter. #542
- `WordPoints_Class_Registry_DeepI` interface and `WordPoints_Class_Registry_Deep_Multilevel` and `WordPoints_Class_Registry_Deep_Multilevel_Slugless` classes implimenting it, and support for it in the apps API. #541
- `WordPoints_Multisite_Switched_State` class for switching between sites on multisite.
- Components and modules apps, and `wordpoints_component()` and `wordpoints_module()`. #537
- Points logs views API, and table view. #544
- Entity restrictions API. #541
 - `WordPoints_Entity_RestirctionI` interface.
 - Post Status Nonpublic, Comment Post Status Nonpublic, Legacy, Unregsitered, and View Post Content Password Protected restrictions.
 - Restriction wrapper class.
 - Entity restrictions app.
 - Regular, applicable, and not applicable mocks for the PHPUnit tests.
- `pass_slugs` setting to the children class registry.
- Points Logs Viewing Restrictions API. #536
 - `WordPoints_Points_Logs_Viewing_RestrictionI` interface.
 - Hooks, Read Post, and Read Comment Post restrictions.
 - Restriction wrapper.
 - Points logs viewing restrictions app.
 - Regular, applicable, and not applicable mocks for the PHPUnit tests.
- Support for specifying a list of fixtures to create for a test case in the `$shared_fixtures` property.
- Support for `Namespace` module header. #540
- A method to the PHPUnit test case to throw an exception. #557
- `aria-label` attributes for action links in modules list table. #351
- `assertNotWordPointsAdminNotice()` assertion to the main test case. #427
- Link to the points logs on the points types screen. #452
- Link to the ranks on the points types screen. #570
- `'wordpoints_points_logs_table_username'` filter on the username displayed in the points logs. #574

### Changed

- All permissions related error messages to begin "Sorry, you are not allowed to...". #449
- All links to admin screens to use `self_admin_url()` rather than relative links. #456
- All URLs to be escaped with just `esc_url()` instead of also wrapping with `esc_attr()`. #459
- Un/install/update to disable server execution time limits. #444
- JS code to use the `input` event instead of `keyup` where appropriate. #446
- Module search to ignore HTML tags. #488
- `WordPoints_PHPUnit_TestCase_Entities` to support mutiple `can(t)_view` entities in entity tests. #489
- Docs for `Entity::$context` to state explicitly that `Entity::get_context()` can be overridden. #495
- `WordPoints_PHPUnit_TestCase_Entities` to not require the tested entities have the human ID provided via an entity attribute. #496
- All CSS to use `#fff` instead of `white`. #493
- `wordpoints_modules_dir()` to call the `'wordpoints_modules_dir'` filter every time it is called, not just once. #497
- PHPUnit tests to use WPPPB.
- `WordPoints_PHPUnit_TestCase_Hook_Event` to support testing events with multiple args. #502
- Reaction Rate Limits UI to support setting the number of minutes/hours/etc. #438
- PHP inline docs to use `object` instead of `stdClass`. #501
- `wordpoints_get_points_logs_query_args()` to default registered logs queries to returning all fields, rather than ommitting the `site_id` and `blog_id` fields. #547
- Points logs that are hidden from some users to be marked as such on display. #424
- Points reaction to save entity GUIDs as hook metadata.
- All uses of `global $wp_roles` to use `wp_roles()` instead. #532
- Points types slug generation to replace spaces with hyphens, instead of stripping them out compeltely. #359
- Shortcodes API to construct the shortcode classes with the shortcode slugs. #512
- Direct queries on the `$wpdb->blogs` table to use `get_sites()` instead. #533
- Use of `get_current_site()->id` to `get_current_network_id()`. #534
- `ArgHierarchySelector` namespace from `'arg-selector2'` to `'arg-hierarchy-selector'`. #525
- Modules list table to sort by module name by default. #350
- Styling of activate module link on module install screen to be a button. #511
- Styling of the module upload form to better match the plugin upload form. #396
- Reaction creation to automatically focus on the first reaction field. #443
- Rate Limit settings to be wrapped in a `div`. #528
- Summary of the plugin for WordPress.org to be more descriptive.
- FAQ for WordPress.org, removing an outdated Q&A and improving the one regarding new features. #561
- WordPress.org plugin feature description to mention the conspicuously absent `[wordpoints_points]` shortcode. #561
- Rank creation UI to automatically focus the title of the new rank. #569
- Points Types screen meta boxes to pass the points type slug along with other points types data. #571
- Legacy `WordPoints_Points_UnitTestCase` to be `abstract`. #572
- Plugin header image for WordPress.org. #476

### Deprecated

- `WordPoints_Points_AJAX_UnitTestCase`, `WordPoints_Points_UnitTestCase`, `WordPoints_Ranks_UnitTestCase`, `WordPoints_Ajax_UnitTestCase`, `WordPoints_Ranks_Ajax_UnitTestCase`, and `WordPoints_UnitTestCase` test case classes. #474
- `WordPoints_UnitTest_Factory_For_Points_Log` and `WordPoints_UnitTest_Factory_For_Rank` factory classes. #474
- `WordPoints_Mock_Filter`, `WordPoints_Breaking_Updater_Mock`, `WordPoints_Module_Installer_Skin_TestDouble`, `WordPoints_Post_Type_Points_Hook_TestDouble`, `WordPoints_Points_Hook_TestDouble`, `WordPoints_Test_Rank_Type`, and `WordPoints_Un_Installer_Mock` mock classes. #474
- `'wordpoints_register_hook_actions_for_post_types'` filter. #542
- `wordpoints_hooks_user_can_view_points_log()`. #536

### Removed

- The WordPress plugin dev-lib from the development package of the plugin. #508
- `target="_blank"` from all links. #558

### Fixed

- `wordpoints_get_module_data()` reloading module text domains that were already loaded. #454
- Un/installer not restoring switched state, instead assuming that the context was unswitched to start with. #458
- `Fields.create()` modifying field name arrays. #504
- "Undefined index `merged_filters`" errors when runnning the PHPUnit tests on WordPress 4.7. #510
- Inline docs for the dynamic hook event class mentioning the hyphen as the namespace separator instead of the backslash.
- `glob()` always being assumed to return an array. #353
- "UTC" to be translatable in the points logs table. #543
- Randomly failing Periods hook extension tests. #481

## [2.1.5] - 2016-11-15

### Fixed

- Network activated modules not being loaded on sites with no active modules. #559
- `is_plugin_active_for_network()` being used in several places instead of `is_wordpoints_network_active()`. #560

## [2.1.4] - 2016-11-08

### Security

- Use a cryptographically secure PRNG to generate the security token used to check module compatibility during breaking updates.
- Use HTTPS URLs everywhere, as long as the domain they point to supports them.

### Added

- Support for comparators for the arg requirements for a hook action. Currently only `!=` is supported in addition to `=`. #551

### Fixed

- Post publish hook event firing when a post was updated, via both `toggle_on` and `toggle_off` actions. #550

## [2.1.3] - 2016-10-01

### Fixed

- Module info not being displayed on the Delete module screen. #509
- Deleting a module showing success without actually deleting it. #509
- Reaction conditions not being deleted form the database when deleted within the UI. #519
- Cancelling editing a reaction causing a second Rate Limits section to be added to the reaction form. #516
- Cancelling editing a reaction not restoring removed conditions or removing restored conditions. #517
- Cancelling editing a reaction removing the add new condition selectors. #520
- Cancelling editing a reaction removing newly saved conditions. #521
- JS error `TypeError: this.$c is not a function` when cancelling editing a reaction. #523
- `wordpoints_user_supplied_shortcode_atts` filter filtered value not being used correctly. #530

## [2.1.2] - 2016-09-13

### Fixed

- The legacy periods extension being shown in the UI for all reactions, instead of just those that use it. #507
- The `Fields.create()` function in the JS Hooks API modifying the passed `name` if it was an array. #504

## [2.1.1] - 2016-09-12

### Fixed

- Periods being sorted in ascending order when being queried, and thus not working after the first period ended. #505
- The legacy periods extension not being shown in the UI for reactions that used it, and the regular periods form being shown instead. #480

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

[unreleased]: https://github.com/WordPoints/wordpoints/compare/stable...HEAD
[2.3.0]: https://github.com/WordPoints/wordpoints/compare/2.2.2...2.3.0
[2.2.2]: https://github.com/WordPoints/wordpoints/compare/2.2.1...2.2.2
[2.2.1]: https://github.com/WordPoints/wordpoints/compare/2.2.0...2.2.1
[2.2.0]: https://github.com/WordPoints/wordpoints/compare/2.1.5...2.2.0
[2.1.5]: https://github.com/WordPoints/wordpoints/compare/2.1.4...2.1.5
[2.1.4]: https://github.com/WordPoints/wordpoints/compare/2.1.3...2.1.4
[2.1.3]: https://github.com/WordPoints/wordpoints/compare/2.1.2...2.1.3
[2.1.2]: https://github.com/WordPoints/wordpoints/compare/2.1.1...2.1.2
[2.1.1]: https://github.com/WordPoints/wordpoints/compare/2.1.0...2.1.1
[2.1.0]: https://github.com/WordPoints/wordpoints/compare/2.0.2...2.1.0
