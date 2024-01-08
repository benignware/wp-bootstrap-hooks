const path = require('path');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const pkgDir = path.resolve(__dirname, '../../..');

console.log('pkgDir: ', pkgDir);

module.exports = {
  mode: process.env.NODE_ENV || 'development',
  entry: {
    'bootstrap': [
      path.resolve(__dirname, 'assets/scss/bootstrap.scss'),
      path.resolve(__dirname, 'assets/js/bootstrap.js'),
    ]
  },
  output: {
    path: path.join(__dirname, 'public')
  },
  plugins: [
    new MiniCssExtractPlugin(),
  ],
  module: {
    rules: [{
      test: /\.scss$/,
      use: [
        MiniCssExtractPlugin.loader,
        'css-loader',
        {
          loader: 'sass-loader',
          options: {
            sassOptions: {
              includePaths: [
                path.resolve(pkgDir, 'node_modules')
              ]
            }
          }
        }
      ]
    }]
  }
};