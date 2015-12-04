module.exports = function(grunt) {

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        sass: {
            build: {
                options: {
                    sourceMap: false,
                    outputStyle: 'compressed'
                },
                files: {
                    '../public/style/screen.css': 'style/screen.scss'
                }
            },
            dev: {
                options: {
                    sourceMap: false,
                    outputStyle: 'expanded'
                },
                files: {
                    '../public/style/screen.css': 'style/screen.scss'
                }
            }
        },
        watch: {
            sass: {
                files: ['style/*.scss'],
                tasks: ['sass:dev'],
                options: {
                    spawn: false
                }
            }
        }
    });

    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('default', ['sass:build']);
    grunt.registerTask('dev', ['sass:dev']);
};