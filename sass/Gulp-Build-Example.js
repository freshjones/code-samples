'use strict';

var gulp                  = require('gulp');
var sass                  = require('gulp-sass');
var sourcemaps            = require('gulp-sourcemaps');
var postcss               = require('gulp-postcss');
var autoprefixer          = require('autoprefixer');
var browserSync           = require('browser-sync').create();
var reload                = browserSync.reload;

var processors = [
        autoprefixer
    ];

gulp.task('sass', function () {
  gulp
    .src('./sass/**/*.scss')
    .pipe(sourcemaps.init())
    .pipe( sass({outputStyle: 'expanded', errLogToConsole: true }).on('error', sass.logError) )
    .pipe(postcss(processors))
    .pipe(sourcemaps.write('./maps'))
    .pipe(gulp.dest('./css'))
    .pipe(browserSync.stream({match: '**/*.css'}));
});

gulp.task('browsersync', function() {
    
    //notify:false,
    browserSync.init({
      open:false,
      proxy: "dev.xxxxxx.local",
    });

});

gulp.task('compile', function () 
{
  gulp.src('./sass/**/*.scss')
    .pipe(sass({outputStyle: 'compressed'}))
    .pipe(postcss(processors))
    .pipe(gulp.dest('./css'));
});

gulp.task('watch', ['sass','browsersync'], function() 
{

  gulp.watch('./sass/**/*.scss', ['sass']);

});

gulp.task('default', ['compile']);
