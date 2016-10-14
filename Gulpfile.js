var gulp   = require('gulp');
var pump   = require('pump');
var util   = require('gulp-util');
var uglify = require('gulp-uglify');


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
        'web/assets/css/**/*.scss',
        '!web/assets/css/vendor/**/*.scss'
    ], ['sass:dev']);

    gulp.watch([
        'web/assets/js/partials/*.js'
    ], ['js:concat']);

    gulp.watch([
        'web/assets/js/**/*.js',
        'web/assets/css/styles.css',
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

gulp.task('js:concat', function (cb) {
    var concat = require('gulp-concat');

    pump([
        gulp.src('web/assets/js/partials/*.js'),
        concat('utilities.js'),
        gulp.dest('web/assets/js/')
    ], cb);
});

gulp.task('js:hint', function (cb) {
    var jshint = require('gulp-jshint');

    pump([
        gulp.src([
            'Gruntfile.js',
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

gulp.task('js:modernizr', function (cb) {
    var modernizr = require('gulp-modernizr');

    pump([
        gulp.src('web/assets/js/*.js'),
        modernizr({
            "crawl": true,
            "customTests": [],
            "tests": [
                "canvas",
                "canvastext",
                "svg",
                "cssanimations",
                "csscalc",
                "csstransforms",
                "csstransitions",
                "cssvhunit",
                "inlinesvg"
            ],
            "options": [
                "setClasses"
            ]
        }),
        uglify(),
        gulp.dest('web/assets/js/min/')
    ], cb);
});

gulp.task('js:uglify', function (cb) {
    pump([
        gulp.src([
            'web/assets/js/*.js',
            '!web/assets/js/min/*.js'
        ]),
        uglify(),
        gulp.dest('web/assets/js/min/')
    ], cb);
});


///
// Sass Functionality
///

gulp.task('sass:dev', function (cb) {
    var sass = require('gulp-sass');
    var combineMq = require('gulp-combine-mq');
    var sourcemaps = require('gulp-sourcemaps');

    pump([
        gulp.src('web/assets/css/styles.scss'),
        sourcemaps.init(),
        sass({
            outputStyle: 'expanded'
        }),
        combineMq({
            beautify: true
        }),
        sourcemaps.write('.'),
        gulp.dest('web/assets/css')
    ], cb);
});

gulp.task('sass:dist', function (cb) {
    var sass = require('gulp-sass');
    var cssmin = require('gulp-cssmin');
    var combineMq = require('gulp-combine-mq');

    pump([
        gulp.src('web/assets/css/styles.scss'),
        sass({
            outputStyle: 'compressed'
        }),
        combineMq({
            beautify: false
        }),
        cssmin({
            processImport: false
        }),
        gulp.dest('web/assets/css')
    ], cb);
});

gulp.task('sass:docs', function (cb) {
    var sassdoc = require('sassdoc');

    pump([
        gulp.src('web/assets/css/modules/*.scss'),
        sassdoc()
    ], cb);
});

gulp.task("sass:lint", function(cb) {
    var syntax_scss = require('postcss-scss');
    var stylelint  = require('stylelint');
    var reporter   = require('postcss-reporter');
    var postcss    = require('gulp-postcss');
    var processors = [
        stylelint(),
        reporter({
            clearMessages: true,
            throwError: true
        })
    ];

    pump([
        gulp.src([
            'web/assets/css/**/*.scss',
            '!web/assets/css/vendor/**/*.scss'
        ]),
        postcss(processors, {
            syntax: syntax_scss
        })
    ], cb);
});


///
// Gulp Tasks
///

gulp.task('dev', ['sass:dev', 'dev:livereload', 'dev:watch']);
gulp.task('dist', ['assets:sprites', 'assets:responsive', 'sass:lint', 'sass:dist', 'js:hint', 'js:concat', 'js:uglify', 'js:modernizer']);

gulp.task('default', ['dev']);