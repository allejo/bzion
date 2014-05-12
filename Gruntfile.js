module.exports = function(grunt) {
    grunt.initConfig({
        autoprefixer : {
            dist : {
                files : {
                    'assets/css/styles.css' : 'assets/css/styles.css'
                }
            }
        },
        sass : {
            dist : {
                options : {
                    style : 'compressed'
                },
                files : {
                    'assets/css/styles.css' : 'assets/css/styles.scss',
                    'assets/css/reset.css'  : 'assets/css/reset.scss'
                }
            }
        },
        watch : {
            styles : {
                files : [ 'assets/css/**/*.scss' ],
                tasks : [ 'css' ]
            }
        }
    });

    grunt.registerTask('css', [ 'sass', 'autoprefixer' ]);
    grunt.registerTask('default', [ 'css' ]);

    grunt.loadNpmTasks('grunt-autoprefixer');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-sass');
};
