const path = require('node:path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = (_, { mode }) => ({
    entry: './assets/entry.js',
    output: {
        path: path.resolve(__dirname, 'assets/dist'),
        filename: 'app.js',
        assetModuleFilename: '[name][ext]',
        clean: true,
    },
    module: {
        rules: [
            ...(mode === 'production' ? [{
                include: path.resolve(__dirname, 'assets/app.js'),
                use: path.resolve(__dirname, 'build/obfuscate-app.js'),
            }] : []),
            { test: /\.css$/i, use: [MiniCssExtractPlugin.loader, 'css-loader'] },
            { test: /\.jpg$/i, type: 'asset/resource' },
        ],
    },
    plugins: [new MiniCssExtractPlugin({ filename: 'app.css' })],
    devtool: false,
});
