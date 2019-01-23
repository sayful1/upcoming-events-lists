const path = require('path');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const BrowserSyncPlugin = require('browser-sync-webpack-plugin');

const config = require('./config.json');

let plugins = [];
let entryPoints = {
    admin: './assets/src/admin/main.js',
    style: './assets/src/scss/public/style.scss',
    'admin-style': './assets/src/scss/admin/admin.scss',
};

plugins.push(new MiniCssExtractPlugin({
    filename: "../css/[name].css"
}));

plugins.push(new BrowserSyncPlugin({
    proxy: config.proxyURL
}));

module.exports = (env, argv) => ({
    "entry": entryPoints,
    "output": {
        "path": path.resolve(__dirname, 'assets/js'),
        "filename": argv.mode === 'production' ? '[name].min.js' : '[name].js'
    },
    "devtool": argv.mode === 'production' ? false : 'source-map',
    "module": {
        "rules": [
            {
                "test": /\.js$/,
                "exclude": /node_modules/,
                "use": {
                    "loader": "babel-loader",
                    "options": {
                        presets: ['@babel/preset-env']
                    }
                }
            },
            {
                "test": /\.scss$/,
                "use": [
                    "style-loader",
                    MiniCssExtractPlugin.loader,
                    "css-loader",
                    "postcss-loader",
                    "sass-loader"
                ]
            }
        ]
    },
    optimization: {
        minimizer: [
            new UglifyJsPlugin({cache: true, parallel: true, sourceMap: false}),
            new OptimizeCSSAssetsPlugin({})
        ]
    },
    "plugins": plugins
});
