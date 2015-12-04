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
        },
        copy: {
            fonts: {
                expand: true,
                flatten: true,
                src: ['bower_components/bootstrap-sass/assets/fonts/bootstrap/*'],
                dest: '../public/fonts/bootstrap/'
            }
        },
        uglify: {
            options: {
                mangle: true
            },
            main: {
                files: {
                    '../public/script/main.js': [
                        'bower_components/jquery/dist/jquery.js',
                        'bower_components/bootstrap-sass/assets/javascripts/bootstrap.js',
                    ]
                }
            },
            detail: {
                files: {
                    '../public/script/project_detail.js': [
                        'script/project_detail.js'
                    ]
                }
            }
        }
    });

    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-uglify');

    grunt.registerTask('default', ['sass:build', 'copy:fonts', 'uglify:main', 'uglify:detail']);
    grunt.registerTask('dev', ['sass:dev']);
};