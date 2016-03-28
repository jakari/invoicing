const gulp = require('gulp');
const del = require('del');
const typescript = require('gulp-typescript');
const tscConfig = require('./tsconfig.json');
const sourcemaps = require('gulp-sourcemaps');
const tslint = require('gulp-tslint');
const browserSync = require('browser-sync');
const reload = browserSync.reload;
const tsconfig = require('tsconfig-glob');
const watch = require('gulp-watch');

// clean the contents of the distribution directory
gulp.task('clean:lib', function () {
    return del('web/lib/**/*');
});

gulp.task('clean:app', function () {
    return del('web/app/**/*');
});

// copy static assets - i.e. non TypeScript compiled source
gulp.task('copy:assets', ['clean:app'], function() {
    return gulp.src(['frontend/**/*', 'index.html', 'styles.css', '!frontend/**/*.ts'], { base : './frontend' })
        .pipe(gulp.dest('web/app'))
});

// copy dependencies
gulp.task('copy:libs', ['clean:lib'], function() {
    return gulp.src([
            'node_modules/intl/dist/Intl.complete.js',
            'node_modules/intl/dist/Intl.min.js',
            'node_modules/angular2/bundles/angular2-polyfills.js',
            'node_modules/systemjs/dist/system.src.js',
            'node_modules/rxjs/bundles/Rx.js',
            'node_modules/angular2/bundles/angular2.dev.js',
            'node_modules/angular2/bundles/http.min.js',
            'node_modules/angular2/bundles/router.dev.js',
            'node_modules/node-uuid/uuid.js',
            'node_modules/immutable/dist/immutable.js',
            'node_modules/semantic-ui/dist/semantic.min.js',
            'node_modules/semantic-ui/dist/semantic.min.css',
            'node_modules/semantic-ui/node_modules/jquery/dist/jquery.min.js',
            'node_modules/pickadate/lib/compressed/themes/classic.date.css',
            'node_modules/pickadate/lib/compressed/picker.js',
            'node_modules/pickadate/lib/compressed/picker.date.js'
        ])
        .pipe(gulp.dest('web/lib'))
});

// linting
gulp.task('tslint', function() {
    return gulp.src('frontend/**/*.ts')
        .pipe(tslint())
        .pipe(tslint.report('verbose'));
});


// TypeScript compile
gulp.task('compile', ['clean:app'], function () {
    return gulp
        .src(tscConfig.files)
        .pipe(sourcemaps.init())
        .pipe(typescript(tscConfig.compilerOptions))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('web/app'));
});

gulp.task('watch', function () {
    gulp.start('default');

    watch(tscConfig.files, function () {
        gulp.start('compile');
        gulp.start('copy:assets');
    });

    watch(['frontend/**/*', 'index.html', 'styles.css', '!frontend/**/*.ts'], function () {
        gulp.start('compile');
        gulp.start('copy:assets');
    });
});

// update the tsconfig files based on the glob pattern
gulp.task('tsconfig-glob', function () {
    return tsconfig({
        configPath: '.',
        indent: 2
    });
});

// Run browsersync for development
gulp.task('serve', ['build'], function() {
    browserSync({
        server: {
            baseDir: 'web'
        }
    });

    gulp.watch(['app/**/*', '/web/index.html', 'styles.css'], ['buildAndReload']);
});

gulp.task('build', ['tslint', 'compile', 'copy:libs', 'copy:assets']);
gulp.task('buildAndReload', ['build'], reload);
gulp.task('default', ['build']);
