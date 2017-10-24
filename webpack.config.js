const path = require('path');
const debug = false;
const env = debug ? 'dev' : 'production';
const NODE_ENV = JSON.stringify(env);
const webpack = require('webpack');
const ExtractTextPlugin = require("extract-text-webpack-plugin");
const CleanWebpackPlugin = require('clean-webpack-plugin');
const fs = require('fs');
// process.traceDeprecation = true
var WebpackBuildNotifierPlugin = require('webpack-build-notifier');


//
const extract = {
	www: new ExtractTextPlugin("../bundles/bundle.[name].[hash].css")
}

//
const loadersConfig = {
	babel: {
		loader: 'babel-loader',
		options: {
			presets: ['es2015', 'stage-2']
		}
	},
	babel_react: {
		loader: 'babel-loader',
		options: {
			presets: ['es2015', 'stage-2', 'react']
		}
	},
	css: {
		loader: 'css-loader',
		options: {
			importLoaders: 1,
			url: false,
			sourceMap: debug
		}
	},
	less: 'less-loader',
	postcss: {
		loader: 'postcss-loader',
		options: {
			plugins: function () {
				return [
					require('autoprefixer')
				];
			},
			sourceMap: debug
		}
	},
	sass: {
		loader: "sass-loader",
		options: {
			sourceMap: debug
		}
	}
};


//
function extractLoaders(config, loaders) {
	return extract[config].extract({
		use: loaders.map((loader) => loadersConfig[loader])
	})
}



const common_config = {
	context: __dirname,
	devtool: debug ? "inline-sourcemap" : false,
	resolve: {
		alias: {
			'[modules]': path.join(__dirname, 'js/modules'),
			'[components]': path.join(__dirname, 'js/components')
		}
	},
}


//
module.exports = [
	Object.assign({}, common_config, {
		name: 'www',
		entry: {
			 main: './js/app.js'
			// ,questions: './js/app.1.js'
		},
		output: {
			path: path.join(__dirname, "assets/bundles/"),
			filename: 'bundle.[name].[hash].js'
		},
		module: {
			rules: [
				// { test: /\.png$/, loader: "file-loader?name=[path][name].[ext]" },
				{
					test: /\.jsx?$/,
					exclude: /(node_modules|bower_components)/,
					use: loadersConfig.babel
				},
				{
					test: /\.css$/,
					use: extractLoaders('www', ['css', 'postcss'])
				},
				{
					test: /\.less$/,
					use: extractLoaders('www', ['css', 'postcss', 'less'])
				},
				{
					test: /\.scss$/,
					use: extractLoaders('www', ['css', 'postcss', 'sass'])
				}
			]
		},

		plugins: debug ?
			[
				new CleanWebpackPlugin(['bundles/*.*'], {
					root: path.join(__dirname, "../web/assets/"),
					verbose: true,
					dry: false,
					exclude: ['.htaccess']
				}),
				extract.www,
				new webpack.ProvidePlugin({
					$: 'jquery',
					jQuery: 'jquery',
					'window.jQuery': 'jquery'
				}),
				function() {
					this.plugin("done", function(stats) {

						fs.writeFileSync(
							path.join(__dirname, 'webpack.php'),
							"<?php return array('hash' => '" + stats.hash + "') ?>"
						);

					});
				},
				new WebpackBuildNotifierPlugin({
				  title: "Build"
				})
			] : [
				new CleanWebpackPlugin(['bundles/*.*'], {
					root: path.join(__dirname, "../web/assets/"),
					verbose: true,
					dry: false,
					exclude: ['.htaccess']
				}),
				new webpack.DefinePlugin({
					'process.env': {
						'NODE_ENV': NODE_ENV
					}
				}),
				new webpack.ProvidePlugin({
					$: 'jquery',
					jQuery: 'jquery',
					'window.jQuery': 'jquery'
				}),
				new webpack.LoaderOptionsPlugin({
					minimize: true,
					debug: false
				}),
				new webpack.optimize.UglifyJsPlugin({
					beautify: false,
					mangle: {
						screw_ie8: true,
						keep_fnames: true
					},
					compress: {
					screw_ie8: true
					},
					comments: false
				}),
				extract.www,
				function() {
					this.plugin("done", function(stats) {

						fs.writeFileSync(
							path.join(__dirname, 'webpack.php'),
							"<?php return array('hash' => '" + stats.hash + "') ?>"
						);

					});
				},
				new WebpackBuildNotifierPlugin({
				  title: "Build"
				})
			]
	})
]
