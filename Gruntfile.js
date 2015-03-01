module.exports = function(grunt) {
    grunt.task.loadNpmTasks("grunt-sass");
    grunt.renameTask("sass", "libsass");

    require('load-grunt-tasks')(grunt, {
        pattern: [ 'grunt-*', '!grunt-sass' ]
    });

    grunt.initConfig({
        autoprefixer: {
            dist: {
                files: {
                    'web/assets/css/styles.css': 'web/assets/css/styles.css'
                }
            }
        },
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
        jshint: {
            options: {
                eqnull: true,
                browser: true,
                globals: {
                    jQuery: true
                },
                reporter: require('jshint-stylish')
            },
            all: ['Gruntfile.js', 'web/assets/js/*.js']
        },
        uglify: {
            options: {
                mangle: true
            },
            dist: {
                files: {
                    'web/assets/js/min/main-ck.js' :  [ 'web/assets/js/main.js' ],
                    'web/assets/js/min/teams-ck.js':  [ 'web/assets/js/teams.js' ]
                }
            }
        },
        watch: {
            docs: {
                files: [ 'web/assets/css/modules/**/*.scss' ],
                tasks: [ 'sassdoc' ]
            },
            scripts: {
                files: [ 'web/assets/js/main.js', 'web/assets/js/teams.js'],
                tasks: [ 'js' ],
                options: {
                    livereload: true
                }
            },
            css: {
                files: [ 'web/assets/css/styles.css' ]
            },
            styles: {
                files: [ 'web/assets/css/**/*.scss' ],
                tasks: [ 'libsass' ]
            },
            views: {
                files: [ 'views/**/*.html.twig' ],
                options: {
                    livereload: true
                }
            }
        }
    });

    grunt.registerTask('css', [ 'sass:dist' ]);
    grunt.registerTask('js', [ 'jshint', 'uglify' ]);
    grunt.registerTask('check', [ 'check-gems' ]);
    grunt.registerTask('default', [ 'css', 'js' ]);
    grunt.registerTask('install-hook', function () {
        var fs = require('fs');

        // my precommit hook is inside the repo as /hooks/pre-commit
        // copy the hook file to the correct place in the .git directory
        grunt.file.copy('hooks/pre-commit', '.git/hooks/pre-commit');

        // chmod the file to readable and executable by all
        fs.chmodSync('.git/hooks/pre-commit', '755');
    });
};
