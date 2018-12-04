var gulp = require('gulp');
var less = require('gulp-less');
var pump = require('pump');
var concat = require('gulp-concat');
var rename = require('gulp-rename');
var uglify = require('gulp-uglify');
var through = require('through2');
var replace = require('gulp-replace');
var clean_css = require('gulp-clean-css');
var sourcemaps = require('gulp-sourcemaps');

gulp.task('default', ['less']);
gulp.task('all', ['less']);
gulp.task('less', function (done) {
	pump([
		gulp.src(['./frontend/styles/less/app.less']),
		sourcemaps.init(),
		less({
			javascriptEnabled: true,
			relativeUrls: true
		}),
		clean_css({
			keepSpecialComments: 0
		}),
		sourcemaps.write('.'),
		gulp.dest('./frontend/styles')
	], done);
});
gulp.task('watch', ['less'], function () {
	gulp.watch(['./app/styles/less/**/*.less'], ['less']);
});