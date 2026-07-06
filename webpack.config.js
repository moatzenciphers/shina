const path = require('path');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

require('dotenv').config();

const getYandexMapsUrl = () => {
  const apiKey = process.env.YANDEX_MAPS_API_KEY;
  const suggestApiKey = process.env.YANDEX_SUGGEST_API_KEY;

  if (!apiKey) {
    return '';
  }

  const params = new URLSearchParams({
    lang: 'ru_RU',
    load: [
      'Map',
      'Placemark',
      'GeoObjectCollection',
      'SuggestView',
      'geocode',
      'geolocation',
      'Polyline',
      'templateLayoutFactory',
    ].join(','),
    apikey: apiKey,
  });

  if (suggestApiKey) {
    params.set('suggest_apikey', suggestApiKey);
  }

  return `https://api-maps.yandex.ru/2.1/?${params.toString()}`;
};

const hasYandexSuggestApiKey = () => Boolean(process.env.YANDEX_SUGGEST_API_KEY);
const getOpenRouteServiceApiKey = () => String(process.env.OPENROUTESERVICE_API_KEY || '');

module.exports = (_, argv) => {
  const isProduction = argv.mode === 'production';

  return {
    entry: path.resolve(__dirname, 'src/js/index.js'),
    output: {
      path: path.resolve(__dirname, 'dist'),
      filename: 'js/[name].[contenthash:8].js',
      clean: true,
    },
    devtool: isProduction ? false : 'source-map',
    devServer: {
      static: path.resolve(__dirname, 'dist'),
      allowedHosts: 'all',
      host: '0.0.0.0',
      port: 8080,
      hot: true,
      client: {
        webSocketURL: 'auto://0.0.0.0:0/ws',
      },
      open: false,
    },
    module: {
      rules: [
        {
          test: /\.pug$/,
          use: [
            {
              loader: 'pug-loader',
              options: {
                pretty: !isProduction,
              },
            },
          ],
        },
        {
          test: /\.s[ac]ss$/i,
          use: [
            isProduction ? MiniCssExtractPlugin.loader : 'style-loader',
            'css-loader',
            'sass-loader',
          ],
        },
        {
          test: /\.css$/i,
          use: [
            isProduction ? MiniCssExtractPlugin.loader : 'style-loader',
            'css-loader',
          ],
        },
        {
          test: /\.(woff2?|otf|ttf|eot)$/i,
          type: 'asset/resource',
          generator: {
            filename: 'fonts/[name][ext]',
          },
        },
        {
          test: /\.(png|jpe?g|gif|webp|avif|svg)$/i,
          type: 'asset/resource',
          generator: {
            filename: 'img/[name][ext]',
          },
        },
      ],
    },
    plugins: [
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src/pages/index.pug'),
        filename: 'index.html',
        inject: 'body',
        scriptLoading: 'defer',
        templateParameters: {
          yandexMapsUrl: getYandexMapsUrl(),
          hasYandexSuggestApiKey: hasYandexSuggestApiKey(),
          openRouteServiceApiKey: getOpenRouteServiceApiKey(),
        },
      }),
      new MiniCssExtractPlugin({
        filename: 'css/[name].[contenthash:8].css',
      }),
    ],
  };
};
