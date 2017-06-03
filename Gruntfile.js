/**
 * Grunt configuration file.
 *
 * @package WordPoints
 * @since 2.1.0
 */

/* jshint node:true */
module.exports = function( grunt ) {

	// Load the default configuration from the dev-lib.
	require( './dev-lib/grunt/modules/configure.js' )( grunt, __dirname );

	// Project configuration.
	grunt.config(
		[ 'autoloader', 'all', 'filter' ]
		, function ( class_files, class_dir ) {

			if ( 'src/components/points/classes/' === class_dir ) {

				// This class needs to come before other entity restriction classes.
				class_files.splice(
					class_files.indexOf( 'logs/viewing/restrictioni.php' ) + 1
					, 0
					, class_files.splice(
						class_files.indexOf( 'logs/viewing/restriction/read/post.php' )
						, 1
					)[0]
				);

				return class_files;

			} else if ( 'src/classes/' !== class_dir ) {
				return class_files;
			}

			var entity_parents = [],
				action_parents = [];

			// Extract the parent class files. To do that we need to loop
			// backwards so that we can remove elements from the array as we go.
			for ( var i = class_files.length - 1; i >= 0; i-- ) {

				if ( class_files[ i ].substr( 0, 14 ) === 'entity/stored/' ) {

					entity_parents.push( class_files[ i ] );
					class_files.splice( i, 1 );

				} else if ( class_files[ i ].substr( 0, 19 ) === 'entity/relationship' ) {

					entity_parents.push( class_files[ i ] );
					class_files.splice( i, 1 );

				} else if ( class_files[ i ].substr( 0, 21 ) === 'hook/action/post/type' ) {

					action_parents.push( class_files[ i ] );
					class_files.splice( i, 1 );
				}
			}

			// Entity these entity classes need to come before other entity classes.
			Array.prototype.splice.apply(
				class_files
				, [ class_files.indexOf( 'entity.php' ) + 1, 0 ]
					.concat( entity_parents.reverse() )
			);

			// Entityish class needs to come before the entity classes.
			class_files.splice(
				class_files.indexOf( 'entity.php' )
				, 0
				, class_files.splice(
					class_files.indexOf( 'entityish.php' )
					, 1
				)[0]
			);

			// This class needs to come before other entity restriction classes.
			class_files.splice(
				class_files.indexOf( 'entity/restrictioni.php' ) + 1
				, 0
				, class_files.splice(
					class_files.indexOf( 'entity/restriction/post/status/nonpublic.php' )
					, 1
				)[0]
			);

			// Action classes that need to come before other action classes.
			Array.prototype.splice.apply(
				class_files
				, [ class_files.indexOf( 'hook/action.php' ) + 1, 0 ]
					.concat( action_parents.reverse() )
			);

			// This class needs to come before other event classes.
			class_files.splice(
				class_files.indexOf( 'hook/event.php' ) + 1
				, 0
				, class_files.splice(
					class_files.indexOf( 'hook/event/dynamic.php' )
					, 1
				)[0]
			);

			// The breaking updater extends the un/installer class.
			class_files.splice(
				class_files.indexOf( 'un/installer/base.php' )
				, 0
				, class_files.splice(
					class_files.indexOf( 'breaking/updater.php' )
					, 1
				)[0]
			);

			class_files.splice(
				class_files.indexOf( 'extension/server/api/extension/license/renewablei.php' ) + 1
				, 0
				, class_files.splice(
					class_files.indexOf( 'extension/server/api/extension/license/renewable/urli.php' )
					, 1
				)[0]
			);

			// Move the module installer before the upgrader (which extends it).
			class_files.splice(
				class_files.indexOf( 'extension/upgrader.php' )
				, 0
				, class_files.splice(
					class_files.indexOf( 'module/installer.php' )
					, 1
				)[0]
			);

			return class_files;
		}
	);
};

// EOF
