var gulp       = require( 'gulp' );
var watch      = require( 'gulp-watch' );
var sass       = require( 'gulp-sass' );
var browserify = require( 'browserify' );
var watchify   = require( 'watchify' );
var uglify     = require( 'gulp-uglify' );
var source     = require( 'vinyl-source-stream' );
var rename     = require( 'gulp-rename' );
var cssmin     = require( 'gulp-minify-css' );
var uglifyjs   = require( 'gulp-uglifyjs' );

var cssSrcPath        = './src/scss';
var cssDestPath       = './css';
var jsSrcPath         = './src/js';
var jsDestPath        = './js';

/**************************************************
 * tasks
 *************************************************/
gulp.task( 'sass', function() {
	return gulp.src( cssSrcPath + '/*.scss' )
		.pipe( sass( {
			outputStyle : 'expanded',
			includePaths: [
 				cssSrcPath
 			]
		} ) )
		.pipe( gulp.dest( cssDestPath ) )
		.on( 'end', function() {
			gulp.src( [cssDestPath + '/*.css', '!' + cssDestPath + '/*.min.css'] )
				.pipe( cssmin( {
					keepSpecialComments: 0
				} ) )
				.pipe( rename( { suffix: '.min' } ) )
				.pipe( gulp.dest( cssDestPath ) );
		} );
} );

gulp.task( 'browserify', function() {
	return jscompile( false );
} );

gulp.task( 'watchify', function() {
	return jscompile( true );
} );

function jscompile( is_watch ) {
	var bundler;
	if ( is_watch ) {
		bundler = watchify( browserify( jsSrcPath + '/app.js' ) );
	} else {
		bundler = browserify( jsSrcPath + '/app.js' );
	}

	function rebundle() {
		return bundler
			.bundle()
			.pipe( source( 'app.js' ) )
			.pipe( gulp.dest( jsDestPath ) )
			.on( 'end', function() {
				gulp.src( [jsDestPath + '/app.js'] )
					.pipe( uglifyjs( 'app.min.js' ) )
					.pipe( gulp.dest( jsDestPath ) );
			} );
	}
	bundler.on( 'update', function() {
		rebundle();
	} );
	bundler.on( 'log', function( message ) {
		console.log( message );
	} );
	return rebundle();
}

/**************************************************
 * exec tasks
 *************************************************/
gulp.task( 'watch', ['sass', 'watchify'], function() {
	gulp.watch( cssSrcPath + '/*.scss', ['sass'] );
} );
