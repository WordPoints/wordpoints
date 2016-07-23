/**
 * Grunt configuration file.
 *
 * @package WordPoints
 * @since 2.1.0
 */

/* jshint node:true */
module.exports = function( grunt ) {
	var SOURCE_DIR = 'src/',
		UNBUILT_DIR = 'unbuilt/',
		browserifyConfig = {},
		DEV_LIB_DIR = 'dev-lib-wordpoints/',
		jsManifests = grunt.file.expand( { cwd: UNBUILT_DIR }, ['**/*.manifest.js'] );

	// Load tasks.
	require( 'matchdep' ).filterDev( 'grunt-*' ).forEach( grunt.loadNpmTasks );
	grunt.loadTasks( DEV_LIB_DIR + 'grunt/tasks/' );

	jsManifests.forEach( function ( manifest ) {

		var build;

		// The compiled files are called *.manifested.js.
		build = manifest.substring( 0, manifest.length - 12 /* .manifest.js */ );

		browserifyConfig[ build ] = { files : {} };
		browserifyConfig[ build ].files[ SOURCE_DIR + build + '.manifested.js' ] = [ UNBUILT_DIR + manifest ];
	} );

	// Project configuration.
	grunt.initConfig({
		autoloader: {
			all: {
				src_dir: SOURCE_DIR,
				prefix: function ( class_dir ) {

					var prefix = 'wordpoints_';

					switch ( class_dir ) {

						case 'src/admin/includes/classes/':
							prefix += 'admin_';
						break;

						case 'src/components/points/includes/classes/':
							prefix += 'points_';
						break;

						case 'src/components/points/admin/includes/classes/':
							prefix += 'points_admin_';
						break;
					}

					return prefix;
				},
				filter:  function ( class_files, class_dir ) {

					if ( 'src/includes/classes/' !== class_dir ) {
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

					return class_files;
				}
			}
		},
		browserify: browserifyConfig,
		sass: {
			all: {
				expand: true,
				cwd: UNBUILT_DIR,
				dest: SOURCE_DIR,
				ext: '.css',
				src: ['**/*.scss'],
				options: {
					outputStyle: 'expanded'
				}
			}
		},
		watch: {
			autoloader: {
				files: [
					SOURCE_DIR + 'includes/classes/**/*.php',
					'!' + SOURCE_DIR + 'includes/classes/index.php'
				],
				tasks: ['autoloader'],
				options: {
					event: [ 'added', 'deleted' ]
				}
			},
			browserify: {
				files: [ UNBUILT_DIR + '**/*.js' ],
				tasks: ['browserify'],
				spawn: false
			},
			// This triggers an automatic reload of the `watch` task.
			config: {
				files: 'Gruntfile.js',
				tasks: ['build']
			},
			css: {
				files: [ UNBUILT_DIR + '**/*.scss' ],
				tasks: ['sass:all']
			},
			livereload: {
				options: { livereload: true },
				files: [ SOURCE_DIR + '/**/*' ]
			}
		}
	});

	grunt.registerTask( 'build', ['autoloader', 'browserify', 'sass:all'] );
	grunt.registerTask( 'default', 'build' );
};

// EOF
