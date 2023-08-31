var gulp = require('gulp');
   var sass = require('gulp-sass');
   var rename = require('gulp-rename');

   gulp.task('sass', function () {
     return gulp.src('./scss/style.scss')
       .pipe(sass().on('error', sass.logError))
       .pipe(rename('style.css'))
       .pipe(gulp.dest('../css/'));
   });

   gulp.task('sass:watch', function () {
     gulp.watch('./scss/**/*.scss', ['sass']);
   });
