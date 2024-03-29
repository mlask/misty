var gulp = require('gulp');
var sass = require('gulp-sass')(require('sass'));
var pump = require('pump');
var concat = require('gulp-concat');
var rename = require('gulp-rename');
var terser = require('gulp-terser');
var replace = require('gulp-replace');
var clean_css = require('gulp-clean-css');
var sourcemaps = require('gulp-sourcemaps');

gulp.task('js', (done) => {
	pump([
		gulp.src(['./frontend/js/source/*.js']),
		terser(),
		rename({ suffix: '.min' }),
		gulp.dest('./frontend/js'),
	], done);
});
gulp.task('sass', (done) => {
	pump([
		gulp.src(['./frontend/styles/sass/app.scss']),
		sourcemaps.init(),
		sass({
			outputStyle: 'compressed'
		}),
		clean_css({
			keepSpecialComments: 0
		}),
		sourcemaps.write('.'),
		gulp.dest('./frontend/styles')
	], done);
});
gulp.task('fonts', (done) => {
	pump([
		gulp.src(['./node_modules/@mdi/font/fonts/*']),
		gulp.dest('./frontend/styles/fonts/mdi')
	], done);
});
gulp.task('default', gulp.parallel('js', 'sass', 'fonts'));
gulp.task('all', gulp.series('js', 'sass', 'fonts'));
gulp.task('watch', gulp.parallel('js', 'sass', (done) => {
	gulp.watch(['./frontend/styles/sass/**/*.scss'], gulp.parallel('sass'));
	gulp.watch(['./frontend/js/source/*.js'], gulp.parallel('js'));
	done();
}));