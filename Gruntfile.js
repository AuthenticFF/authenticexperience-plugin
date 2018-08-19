// var mozjpeg = require('imagemin-mozjpeg');

module.exports = function(grunt) {

  require('load-grunt-tasks')(grunt);

  // Project configuration.
  grunt.initConfig({

    pkg: grunt.file.readJSON('package.json'),

    //
    // Style Tasks
    //
    sass: {
      options: {
        sourceMap: true,
        outputStyle: 'nested'
      },
      dist: {
        files: {
          'src/assetbundles/smartmodelfield/dist/css/SmartModel.css': 'src/assetbundles/smartmodelfield/src/scss/SmartModel.scss',
          'src/assetbundles/smartphotospherefield/dist/css/SmartPhotosphere.css': 'src/assetbundles/smartphotospherefield/src/scss/SmartPhotosphere.scss',
        },
      }
    },

    autoprefixer: {
      options: {
        browsers: ['last 2 versions']
      },
      dist: {
        files:{
          'src/assetbundles/smartmodelfield/dist/css/SmartModel.css':'src/assetbundles/smartmodelfield/dist/css/SmartModel.css',
          'src/assetbundles/smartphotospherefield/dist/css/SmartPhotosphere.css':'src/assetbundles/smartphotospherefield/dist/css/SmartPhotosphere.css'
        }
      }
    },

    //
    // Javascript Tasks
    //

    // Compile javascript into a single file`
    browserify: {
      dist:{
        files:{
          "src/assetbundles/smartmodelfield/dist/js/SmartModel.js": "src/assetbundles/smartmodelfield/src/js/SmartModel.js",
          "src/assetbundles/smartphotospherefield/dist/js/SmartPhotosphere.js": "src/assetbundles/smartphotospherefield/src/js/SmartPhotosphere.js"
        },
        options: {
          watch : true,
          browserifyOptions : {
            debug : true
          }
        }
      }
    },

    //
    // Setting up our watch events
    //
    chokidar: {
      js: {
        files: [
          'src/assetbundles/smartmodelfield/src/js/**/*.js',
          'src/assetbundles/smartphotospherefield/src/js/**/*.js',
        ],
        tasks: [ 'browserify']
      },
      sass: {
        files: [
          'src/assetbundles/smartmodelfield/src/scss/**/*.scss',
          'src/assetbundles/smartphotospherefield/src/scss/**/*.scss',
        ],
        tasks: [ 'sass', 'autoprefixer' ]
      }
    },

  });

  // TASKS
  grunt.loadNpmTasks('grunt-autoprefixer');
  grunt.loadNpmTasks('grunt-browserify');
  grunt.loadNpmTasks('grunt-chokidar');
  grunt.registerTask('dev', ['chokidar']);

};
