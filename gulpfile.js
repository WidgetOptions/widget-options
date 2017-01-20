var gulp        = require('gulp');
var sass        = require('gulp-sass');
var browserSync = require('browser-sync').create();
var runSequence = require('run-sequence');
var uglify      = require('gulp-uglify');
var rename      = require('gulp-rename');
var notify      = require('gulp-notify');
var plumber     = require('gulp-plumber');

var onError = function(err) {
    notify.onError({
                title:    "Gulp",
                subtitle: "Failure!",
                message:  "Error: <%= error.message %>",
                sound:    "Beep"
            })(err);

    this.emit('end');
};

gulp.task('browser-sync', function() {
    browserSync.init({
        proxy: 'test-site.dev',
        // browser: 'safari'
    });
});

gulp.task('sass', function() {
  return gulp.src('assets/scss/*.scss')
    .pipe( plumber({ errorHandler: onError }) )
    .pipe(sass({
    		outputStyle: 'compressed',
    		includePaths: [
		   		'./node_modules/compass-mixins/lib'
		 	]
		 })) // Converts Sass to CSS with gulp-sass
    .pipe( gulp.dest('./assets/css/') ); // output to theme root
});

gulp.task('uglify', function () {
  gulp.src(['assets/js/*.js', '!assets/js/*.min.js'])
    .pipe( plumber({ errorHandler: onError }) )
    .pipe(uglify())
    .pipe(rename({
      suffix: '.min'
    }))
    .pipe(gulp.dest('./assets/js/'));
});

gulp.task('watch', ['browser-sync', 'sass', 'uglify'], function(){
	gulp.watch('assets/scss/*.scss', ['sass']);
	gulp.watch('*/*.php', browserSync.reload);
	gulp.watch('assets/css/*.css', browserSync.reload);
	gulp.watch('assets/js/*.js', browserSync.reload);
});

gulp.task('default', function (callback) {
  runSequence(['sass', 'browser-sync', 'uglify', 'watch'],
    callback
  )
})
