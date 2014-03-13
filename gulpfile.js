var gulp = require('gulp');
var gutil = require('gulp-util');
var sass = require('gulp-sass');
var autoprefixer = require('gulp-autoprefixer');
var cssminify = require('gulp-minify-css');

var paths = {
  styleDirectory: 'assets/css',
  styleWatch: 'assets/css/**/*.scss',
  styles: 'assets/css/*.scss'
};

gulp.task('styles', function() {
  return gulp.src(paths.styles)
    .pipe(sass({ outputStyle: "compressed" }))
    .pipe(autoprefixer())
    .pipe(cssminify())
    .pipe(gulp.dest(paths.styleDirectory));
});

// Rerun the task when a file changes
gulp.task('watch', function () {
  gulp.watch(paths.styleWatch, ['styles']);
});

// The default task (called when you run `gulp` from cli)
gulp.task('default', ['styles']);