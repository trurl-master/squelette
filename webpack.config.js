const path = require('path');
const fs = require('fs');

//
const webpack = require('webpack');
const CleanWebpackPlugin = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const WebpackBuildNotifierPlugin = require('webpack-build-notifier');

//
const NODE_ENV = process.env.NODE_ENV;
const debug = NODE_ENV !== 'production';


//
const loadersConfig = {
	babel: {
		loader: 'babel-loader',
		options: {
			presets: ['env'/*, 'stage-2'*/]
		}
	},
	babel_react: {
		loader: 'babel-loader',
		options: {
			presets: ['env'/*, 'stage-2'*/, 'react']
		}
	},
    extract: MiniCssExtractPlugin.loader,
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
function extractLoaders(loaders) {
    return loaders.map((loader) => loadersConfig[loader]);
}


//
module.exports = {
    context: __dirname,
	resolve: {
		alias: {
			'[modules]': path.resolve(__dirname, 'js/modules/'),
			'[common]':  path.resolve(__dirname, 'js/modules/common/'),
			'[components]': path.resolve(__dirname, 'js/components/'),
			'[models]': path.resolve(__dirname, 'js/models/'),
            '[libs]': path.resolve(__dirname, 'js/libs/'),
            '[db]': path.resolve(__dirname, 'inc/db/generated-js/')
		}
	},
    entry: {
        main: './js/app.js',
        // admin: './js/app.admin.js'
    },
    output: {
        path: path.resolve(__dirname, "assets/bundles/"),
        filename: 'bundle.[name].[hash].js'
    },
    module: {
        rules: [
            // {
            //     test: /\.jsx?$/,
            //     exclude: /(node_modules|bower_components)/,
            //     use: loadersConfig.babel
            // },
            {
                test: /\.jsx$/,
                exclude: /(node_modules|bower_components)/,
                use: loadersConfig.babel_react
            },
            {
                test: /\.js$/,
                exclude: /(node_modules|bower_components)/,
                use: loadersConfig.babel
            },
            {
                test: /\.css$/,
                use: extractLoaders(['extract', 'css', 'postcss'])
            },
            {
                test: /\.less$/,
                use: extractLoaders(['extract', 'css', 'postcss', 'less'])
            },
            {
                test: /\.scss$/,
                use: extractLoaders(['extract', 'css', 'postcss', 'sass'])
            }
        ]
    },
    plugins: [
        new CleanWebpackPlugin(['bundles/*.*'], {
            root: path.resolve(__dirname, 'assets/'),
            verbose: true,
            dry: false,
            exclude: ['.htaccess']
        }),
        new MiniCssExtractPlugin({
            filename: '../bundles/bundle.[name].[hash].css',
            chunkFilename: '../bundles/[id].css'
            // filename: "[name].css",
            // chunkFilename: "[id].css"
        }),
        new webpack.ProvidePlugin({
            $: 'jquery',
            jQuery: 'jquery',
            'window.jQuery': 'jquery'
        }),
        function() {
            this.plugin("done", function(stats) {

                fs.writeFileSync(
                    path.resolve(__dirname, 'webpack.php'),
                    "<?php return array('hash' => '" + stats.hash + "') ?>"
                );

            });
        },
        new WebpackBuildNotifierPlugin({
          title: "Build"
        })
    ]
};
