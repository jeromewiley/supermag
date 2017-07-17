/* File: gulpfile.js */

var gulp = require('gulp'),
    gutil = require('gulp-util');
    sass = require('gulp-sass');

// create a default task and just log a message

gulp.task('default', ['build-css',], function() {
  return gutil.log('Gulp is running')
  gulp.watch('web/themes/contrib/zircon/sass/**/*.scss', ['build-css']);
});

gulp.task('build-css', function() {
  return gulp.src('web/themes/contrib/zircon/sass/**/*.scss')
    .pipe(sass())
    .pipe(gulp.dest('web/themes/contrib/zircon/css/'));
});

// gulp.task('watch', function() {
//  gulp.watch('web/themes/contrib/zircon/sass/**/*.scss', ['build-css']);
// }); 
