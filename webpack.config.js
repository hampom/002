const webpack = require('webpack');
const path = require('path');

require("babel-core/register");
require("babel-polyfill");

module.exports = {
    entry: ['babel-polyfill', './resources/assets/js/index.js'],
    output: {
        path: path.resolve(__dirname, './public/js'),
        filename: 'app.js'
    },
    devtool: 'source-map',
    module: {
        rules: [
            { test: /\.js$/, use: 'babel-loader' },
            { test: /\.css$/, use: ['style-loader', 'css-loader'] },
            { test: /\.scss$/, use: ['style-loader', 'css-loader', 'sass-loader'] },
            { test: /\.(ttf|otf|eot|svg|woff(2)?)(\?[a-z0-9]+)?$/, loader: 'file-loader?name=assets/[name].[ext]' },
        ]
    },
    resolve: {
        modules: [
            "node_modules",
        ]
    }
};
