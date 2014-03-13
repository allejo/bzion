module.exports = function(grunt) {
    grunt.initConfig({
        autoprefixer : {
            dist : {
                files : {
                    'assets/css/main.css' : 'assets/css/main.css'
                }
            }
        },
        sass : {
            dist : {
                options : {
                    style : 'compressed'
                },
                files : {
                    'assets/css/main.css' : 'assets/css/main.scss'
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
