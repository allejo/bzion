module.exports = function(grunt) {
    grunt.initConfig({
        autoprefixer: {
            dist: {
                files: {
                    'web/assets/css/styles.css': 'web/assets/css/styles.css'
                }
            }
        },
        sass: {
            dist: {
                options: {
                    style: 'compressed',
                    sourcemap: 'auto'
                },
                files: {
                    'web/assets/css/styles.css': 'web/assets/css/styles.scss',
                    'web/assets/css/reset.css' : 'web/assets/css/reset.scss'
                }
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
            styles: {
                files: [ 'web/assets/css/**/*.scss' ],
                tasks: [ 'css' ]
            }
        }
    });

    grunt.registerTask('css', [ 'sass' ]);
    grunt.registerTask('js', [ 'jshint', 'uglify' ]);
    grunt.registerTask('default', [ 'css', 'js' ]);

    grunt.loadNpmTasks('grunt-autoprefixer');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-uglify');
};
