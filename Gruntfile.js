module.exports = function(grunt) {
    require('load-grunt-tasks')(grunt);

    grunt.initConfig({
        combine_mq: {
            dist: {
                expand: true,
                cwd: 'web/assets/css',
                src: '*.css',
                dest: 'web/assets/css/'
            }
        },
        cssmin: {
            options: {
                processImport: false
            },
            dist: {
                files: {
                    'web/assets/css/styles.css': [ 'web/assets/css/styles.css' ]
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
            debug: {
                options: {
                    outputStyle: 'expanded',
                    sourceMap: true,
                    lineNumbers: true
                },
                files: {
                    'web/assets/css/styles.css': 'web/assets/css/styles.scss'
                }
            },
            dist: {
                options: {
                    outputStyle: 'compressed'
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
                tasks: [ 'sass' ],
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

    grunt.registerTask('css',  [ 'sass:dist', 'combine_mq', 'cssmin' ]);
    grunt.registerTask('js',   [ 'jshint', 'uglify' ]);
    grunt.registerTask('dist', [ 'css', 'js', 'responsive_images' ]);

    grunt.registerTask('default', ['dist']);
};
