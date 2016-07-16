module.exports = function(grunt) {
    grunt.task.loadNpmTasks("grunt-sass");
    grunt.renameTask("sass", "libsass");

    require('load-grunt-tasks')(grunt, {
        pattern: [ 'grunt-*', '!grunt-sass' ]
    });

    grunt.initConfig({
        'check-gems': {
            dist: {
                files: [{
                    src: '.'
                }]
            }
        },
        libsass: {
            options: {
                style: 'expanded',
                sourceMap: true,
                lineNumbers: true
            },
            debug: {
                files: {
                    'web/assets/css/styles.css': 'web/assets/css/styles.scss'
                }
            }
        },
        phpunit: {
            classes: {
                dir: 'tests/ModelTests/'
            },
            options: {
                bin: 'vendor/bin/phpunit',
                colors: true
            }
        },
        responsive_images: {
            landing: {
                options: {
                    engine: 'im',
                    sizes: [
                        {
                            name: 'phone',
                            width: 500
                        },
                        {
                            name: 'phablet',
                            width: 790
                        },
                        {
                            name: 'tablet',
                            width: 1024
                        }
                    ]
                },
                files: [
                    {
                        expand: true,
                        src: [ 'web/assets/imgs/cover_photo.jpg' ]
                    }
                ]
            }
        },
        sass: {
            dist: {
                options: {
                    style: 'compressed',
                    sourcemap: 'none',
                    require: 'sass-media_query_combiner'
                },
                files: {
                    'web/assets/css/styles.css': 'web/assets/css/styles.scss'
                }
            }
        },
        sassdoc: {
            default: {
                src: [ 'web/assets/css/modules' ]
            }
        },
        scsslint: {
            allFiles: [
                'web/assets/css/**/*.scss'
            ],
            options: {
                config: '.scss-lint.yml'
            }
        },
        sprite: {
            rankings: {
                src: 'assets/ranks/sprites/*.png',
                dest: 'web/assets/imgs/ranks.png',
                destCss: 'web/assets/css/vendor/_ranks.scss',
                cssTemplate: 'assets/ranks/ranks.scss.handlebars'
            }
        },
        jshint: {
            options: {
                eqnull: true,
                browser: true,
                globals: {
                    jQuery: true
                },
                reporter: require('jshint-stylish'),
                reporterOutput: ''
            },
            all: ['Gruntfile.js', 'web/assets/js/*.js', 'web/assets/js/partials/*.js']
        },
        uglify: {
            options: {
                mangle: true,
                screwIE8: true
            },
            dist: {
                files: {
                    'web/assets/js/min/animations.js': [ 'web/assets/js/animations.js' ],
                    'web/assets/js/min/utilities.js': [ 'web/assets/js/partials/*.js' ],
                    'web/assets/js/min/teams.js': [ 'web/assets/js/teams.js' ],
                    'web/assets/js/min/main.js' : [ 'web/assets/js/main.js' ]
                }
            }
        },
        watch: {
            options: {
                livereload: true,
                spawn: false
            },
            docs: {
                files: [ 'web/assets/css/modules/**/*.scss' ],
                tasks: [ 'sassdoc' ],
                options: {
                    livereload: false
                }
            },
            scripts: {
                files: [
                    'web/assets/js/**/*.js',
                    '!web/assets/js/min/*.js'
                ],
                tasks: [ 'js' ],
                options: {
                    spawn: true
                }
            },
            css: {
                files: [ 'web/assets/css/styles.css' ]
            },
            styles: {
                files: [
                    'web/assets/css/**/*.scss',
                    '!web/assets/css/vendor/**/*.scss'
                ],
                tasks: [ 'libsass' ],
                options: {
                    livereload: false,
                    spawn: true
                }
            },
            views: {
                files: [ 'views/**/*.html.twig' ],
                options: {
                    livereload: true
                }
            },
            tests: {
                files: [ 'models/*.php', 'tests/ModelTests/*.php' ],
                tasks: [ 'phpunit' ]
            }
        }
    });

    grunt.registerTask('css', [ 'sass:dist' ]);
    grunt.registerTask('js', [ 'jshint', 'uglify' ]);
    grunt.registerTask('check', [ 'check-gems' ]);

    grunt.registerTask('install-hook', function () {
        var fs = require('fs');

        // my precommit hook is inside the repo as /hooks/pre-commit
        // copy the hook file to the correct place in the .git directory
        grunt.file.copy('hooks/pre-commit', '.git/hooks/pre-commit');

        // chmod the file to readable and executable by all
        fs.chmodSync('.git/hooks/pre-commit', '755');
    });

    grunt.registerTask('default', [ 'css', 'js', 'responsive_images' ]);
};
