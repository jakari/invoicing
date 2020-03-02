const webpack = require('webpack');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const path = require('path');
const CopyPlugin = require('copy-webpack-plugin');

const nodeModulesPath = path.resolve(__dirname, 'node_modules');

const isProduction = process.env.NODE_ENV === "production";
const plugins = [
  // Injects the JS files into the template HTML file
  new HtmlWebpackPlugin({
    template: './client/index.html',
    inject: 'body',
    filename: 'index.html',
  }),
  new webpack.DefinePlugin({
    'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV),
  })
];

if (isProduction) {
  plugins.push(
    new CopyPlugin([
      { from: 'client/robots.txt' },
    ])
  );
}

module.exports = {
  entry: {
    app: [
      './client/index.js',
    ],
  },

  output: {
    path: path.resolve(__dirname, 'build/'),
    filename: '[name].[hash].js',
    publicPath: '/'
  },

  resolve: {
    extensions: ['.js', '.jsx', '.sass', '.scss'],
    modules: ['node_modules', path.resolve(__dirname, 'client')],
  },

  plugins,
  devtool: 'none',

  devServer: {
    hot: true,
    contentBase: path.resolve(__dirname, 'build/'),
    historyApiFallback: true,
    publicPath: '/',
    proxy: {
      '/api': {
        target: 'http://invoicing.tunk.io',
        changeOrigin: true,
        secure: false
      },
      '/bundles': {
        target: 'http://invoicing.tunk.io',
        changeOrigin: true,
        secure: false
      }
    },
  },

  module: {
    rules: [
      {
        test: /\.jsx?$/,
        use: {
          loader: "babel-loader",
          options: {
            cacheDirectory: true
          }
        },
        exclude: nodeModulesPath,
      },
      {
        test: /\.s[ac]ss$/,
        loaders: [
          'style-loader',
          'css-loader',
          'sass-loader',
        ],
        exclude: nodeModulesPath,
      },
      {
        test: /\.css$/,
        use: [
          { loader: "style-loader" },
          { loader: "css-loader" }
        ]
      },
      // "file" loader makes sure assets end up in the `build` folder.
      // When you `import` an asset, you get its filename.
      {
        test: [/\.eot$/, /\.ttf$/, /\.svg$/, /\.woff$/, /\.woff2$/, /\.png/],
        loader: 'file-loader',
        options: {
          name: 'static/media/[name].[hash:8].[ext]',
        },
      },
      { test: /\.txt$/, use: 'raw-loader' },
      {
        test: /\.(png|jpe?g|gif|)$/,
        loader: 'url-loader',
        exclude: nodeModulesPath,
      },
      {
        test: /\.json$/,
        loader: 'json-loader',
        exclude: nodeModulesPath,
      },
    ],
  },
};
