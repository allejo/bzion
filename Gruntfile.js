module.exports = function(grunt) {
    grunt.initConfig({
        autoprefixer: {
            dist: {
                files: {
                    'assets/css/styles.css': 'assets/css/styles.css'
                }
            }
        },
        sass: {
            dist: {
                options: {
                    style: 'compressed'
                },
                files: {
                    'assets/css/styles.css': 'assets/css/styles.scss',
                    'assets/css/reset.css' : 'assets/css/reset.scss'
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
            all: ['Gruntfile.js', 'assets/js/*.js']
        },
        uglify: {
            options: {
                mangle: true
            },
            dist: {
                files: {
                    'assets/js/min/main-ck.js' :  [ 'assets/js/main.js' ],
                    'assets/js/min/teams-ck.js':  [ 'assets/js/teams.js' ]
                }
            }
        },
        watch: {
            styles: {
                files: [ 'assets/css/**/*.scss' ],
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
