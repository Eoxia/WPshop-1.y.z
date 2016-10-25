var gulp = require('gulp');
var please = require('gulp-pleeease');
var less = require('gulp-less');
var stripCssComments = require('gulp-strip-css-comments');
var watch = require('gulp-watch');
var plumber = require('gulp-plumber');
var concat = require('gulp-continuous-concat');
var rename = require("gulp-rename");
var minifyCss = require('gulp-minify-css');

var paths = {
  styles: ['less/wps_style.less'],
  images: 'client/img/**/*'
};

gulp.task('default', function() {
  return gulp.src(paths.styles)
    .pipe(watch(paths.styles))
    .pipe(stripCssComments())
    .pipe(plumber())
  	.pipe(less())
    .pipe(stripCssComments())
  	.pipe(please({
        minifier: false,
        autoprefixer: {"browsers": ["last 40 versions", "ios 6"]},
        rem: true,
        pseudoElements: true,
        mqpacker: false,
        opacity : true,
        filters : true
      }))
    .pipe(concat('wps_style.css'))
    .pipe(gulp.dest('css'))
    .pipe(minifyCss())
    .pipe(rename("wps_style.min.css"))
    .pipe(gulp.dest('css'));
});