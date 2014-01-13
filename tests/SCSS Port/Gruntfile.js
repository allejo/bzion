module.exports = function(grunt) {
    grunt.initConfig({
        autoprefixer : {
            dist : {
                files : {
                    'styles.css' : 'styles.css'
                }
            }
        },
        sass : {
            dist : {
                options : {
                    style : 'compressed'
                },
                files : {
                    'styles.css' : 'styles.scss'
                }
            }
        },
        watch : {
            styles : {
                files : [ 'styles.scss' ],
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
