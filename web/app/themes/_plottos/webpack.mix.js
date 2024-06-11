const mix = require("laravel-mix");
const sassGlobImporter = require('node-sass-glob-importer');
const ImageminPlugin = require('imagemin-webpack-plugin').default;
require('laravel-mix-purgecss');
const path = require('path');

const websiteURL = 'http://plottuypppd.test';

mix.webpackConfig({
	devtool: 'inline-source-map',
	module: {
		rules: [
			{
				test: /.scss/,
				enforce: 'pre',
				loader: 'import-glob-loader'
			},
		]
	},
	plugins: [
		new ImageminPlugin({
			pngquant: {
				quality: '95-100',
			},
			test: /\.(jpe?g|png|gif|svg)$/i
		}),
	],
}).sass("assets/src/scss/style.scss", "assets/dist/css", {
	sassOptions: {
		importer: sassGlobImporter(),
	}
}).options({
	processCssUrls: false,
}).js("assets/src/js/app.js", "assets/dist/js").copy('assets/src/img', 'assets/dist/img', false).sourceMaps();

mix.minify(['assets/dist/js/app.js', 'assets/dist/css/style.css']);


mix.browserSync({
	proxy: websiteURL,
	files: ["./**/*.php", "./assets/dist/**/*.*", "./assets/src/**/*.*"],
});
