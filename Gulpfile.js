var gulp   = require('gulp');
var pump   = require('pump');
var util   = require('gulp-util');


///
// Asset Management
///

gulp.task('assets:responsive', function (cb) {
    var responsive = require('gulp-responsive');

    pump([
        gulp.src('web/assets/imgs/cover_photo.jpg'),
        responsive({
            'cover_photo.jpg': [
                {
                    rename: { suffix: '-phone' },
                    width: 500
                },
                {
                    rename: { suffix: '-phablet' },
                    width: 790
                },
                {
                    rename: { suffix: '-tablet' },
                    width: 1024
                }
            ]
        }),
        gulp.dest('web/assets/imgs')
    ], cb);
});

gulp.task('assets:sprites', function (cb) {
    var spritesmith = require('gulp.spritesmith');

    pump([
        gulp.src('assets/ranks/sprites/*.png'),
        spritesmith({
            cssTemplate: 'assets/ranks/ranks.scss.handlebars',
            cssName: 'css/vendor/_ranks.scss',
            imgName: 'imgs/ranks.png'
        }),
        gulp.dest('web/assets/')
    ], cb);
});


///
// Dev Functionality
///

gulp.task('dev:watch', function() {
    var livereload = require('gulp-livereload');

    livereload.listen({
        quiet: true
    });

    gulp.watch([
        'assets/css/themes/*.yml',
        'assets/css/**/*.scss',
        '!assets/css/vendor/**/*.scss'
    ], ['sass:dev']);

    gulp.watch([
        'web/assets/js/**/*.js',
        'web/assets/css/styles.css',
        'web/build/*.js',
        'views/**/*.html.twig',
        'controllers/*.php',
        'models/*.php'
    ]).on('change', function (file) {
        livereload.changed(file);

        util.log(util.colors.yellow('File changed: ' + file.path));
    });
});

gulp.task('dev:watch:sassdocs', function () {
    gulp.watch([
        'web/assets/css/modules/**/*.scss'
    ], ['sass:docs']);
});


///
// JS Functionality
///

gulp.task('js:hint', function (cb) {
    var jshint = require('gulp-jshint');

    pump([
        gulp.src([
            'assets/js/src/*.js',
            'assets/js/*.js',
            'Gulpfile.js',
            'web/assets/js/*.js',
            'web/assets/js/partials/*.js'
        ]),
        jshint({
            eqnull: true,
            browser: true,
            globals: {
                jQuery: true
            }
        }),
        jshint.reporter('jshint-stylish')
    ], cb);
});

///
// Sass Functionality
///

var sass = require('gulp-sass');
var eyeglass = require('eyeglass');
var combineMq = require('gulp-combine-mq');

gulp.task('sass:dev', function (cb) {
    var sourcemaps = require('gulp-sourcemaps');

    pump([
        gulp.src('assets/css/styles.scss'),
        sourcemaps.init(),
        sass(eyeglass({
            outputStyle: 'compact'
        })),
        combineMq({
            beautify: true
        }),
        sourcemaps.write('.'),
        gulp.dest('web/assets/css')
    ], cb);
});

gulp.task('sass:dist', function (cb) {
    var cssmin = require('gulp-cssmin');
    var postcss = require('gulp-postcss');
    var unprefix = require('postcss-unprefix');
    var removePrefixes = require('postcss-remove-prefixes');

    pump([
        gulp.src('assets/css/styles.scss'),
        sass(eyeglass({
            outputStyle: 'compressed'
        })),
        combineMq({
            beautify: false
        }),
        postcss([
            unprefix(),
            removePrefixes()
        ]),
        cssmin({
            processImport: false,
            mediaMerging: false
        }),
        gulp.dest('web/assets/css')
    ], cb);
});

gulp.task('sass:docs', function (cb) {
    var sassdoc = require('sassdoc');

    pump([
        gulp.src('assets/css/abstracts/*.scss'),
        sassdoc()
    ], cb);
});

gulp.task('sass:test', function(cb) {
    var mocha = require('gulp-mocha');

    pump([
        gulp.src('assets/css/tests/test.js', { read: false }),
        mocha()
    ], cb);
});


///
// Gulp Tasks
///

gulp.task('dev', ['sass:dev', 'dev:watch']);
gulp.task('dist', ['assets:sprites', 'assets:responsive', 'sass:dist']);

gulp.task('default', ['dev']);
