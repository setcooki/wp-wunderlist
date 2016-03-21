module.exports = function(grunt)
{
    var js = [
        'static/js/app.js',
        'static/js/lib/action.js',
        'static/js/lib/api.js',
        'static/js/lib/socket.js'
    ];

    grunt.initConfig({
        pkg: grunt.file.readJSON("package.json"),
        concat: {
            options: {
                separator: '\n',
                sourceMap: false
            },
            dist: {
                src: js,
                dest: 'static/js/wp-wunderlist.js'
            }
        },
        uglify: {
            options: {
                sourceMap: false,
                compress: true
            },
            dist: {
                files: {
                    'static/js/wp-wunderlist.min.js': 'static/js/app.js'
                }
            }
        },
        watch: {
            options: {
                spawn: false,
                interrupt: true,
                interval: 5000
            },
            js: {
                files: [
                    'static/js/*.js',
                    'static/js/**/*.js'
                ],
                tasks: ['uglify']
            }
        }
    });

    grunt.loadNpmTasks("grunt-contrib-concat");
    grunt.loadNpmTasks("grunt-contrib-uglify");
    grunt.loadNpmTasks("grunt-contrib-watch");
    grunt.loadNpmTasks("grunt-contrib-less");
    grunt.loadNpmTasks("grunt-contrib-cssmin");
    grunt.loadNpmTasks("grunt-contrib-copy");
    grunt.loadNpmTasks("grunt-contrib-jshint");

    grunt.registerTask("live", ["watch"]);
    grunt.registerTask("build", ["uglify", "watch"]);
};