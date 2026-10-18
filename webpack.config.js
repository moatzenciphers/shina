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
      'Polygon',
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
    entry: {
      main: path.resolve(__dirname, 'src/js/index.js'),
      calculator: path.resolve(__dirname, 'src/js/calculator-entry.js'),
    },
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
        chunks: ['main'],
        templateParameters: {
          yandexMapsUrl: getYandexMapsUrl(),
          hasYandexSuggestApiKey: hasYandexSuggestApiKey(),
          openRouteServiceApiKey: getOpenRouteServiceApiKey(),
        },
      }),
      new HtmlWebpackPlugin({ template: path.resolve(__dirname, 'src/pages/calculator.pug'), filename: 'calculator.html', chunks: ['main', 'calculator'], inject: 'body', scriptLoading: 'defer', templateParameters: { yandexMapsUrl: getYandexMapsUrl(), hasYandexSuggestApiKey: hasYandexSuggestApiKey(), openRouteServiceApiKey: getOpenRouteServiceApiKey() } }),
      new MiniCssExtractPlugin({
        filename: 'css/[name].[contenthash:8].css',
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src/pages/articles.pug'),
        filename: 'articles.html',
        chunks: ['main'],
        inject: 'body',
        scriptLoading: 'defer',
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src/pages/articles.pug'),
        filename: 'articles-page-2.html',
        templateParameters: { articlesPage: 2 },
        chunks: ['main'],
        inject: 'body',
        scriptLoading: 'defer',
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src/pages/articles.pug'),
        filename: 'articles-empty.html',
        templateParameters: { articlesEmpty: true },
        chunks: ['main'],
        inject: 'body',
        scriptLoading: 'defer',
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src/pages/services.pug'),
        filename: 'services.html',
        chunks: ['main'],
        inject: 'body',
        scriptLoading: 'defer',
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src/pages/fuel.pug'),
        filename: 'fuel.html',
        templateParameters: {
          yandexMapsUrl: getYandexMapsUrl(),
          hasYandexSuggestApiKey: hasYandexSuggestApiKey(),
          openRouteServiceApiKey: getOpenRouteServiceApiKey(),
        },
        chunks: ['main'],
        inject: 'body',
        scriptLoading: 'defer',
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src/pages/tire-work.pug'),
        filename: 'tire-work.html',
        templateParameters: {
          yandexMapsUrl: getYandexMapsUrl(),
          hasYandexSuggestApiKey: hasYandexSuggestApiKey(),
          openRouteServiceApiKey: getOpenRouteServiceApiKey(),
        },
        chunks: ['main'],
        inject: 'body',
        scriptLoading: 'defer',
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src/pages/locknuts.pug'),
        filename: 'locknuts.html',
        templateParameters: {
          yandexMapsUrl: getYandexMapsUrl(),
          hasYandexSuggestApiKey: hasYandexSuggestApiKey(),
          openRouteServiceApiKey: getOpenRouteServiceApiKey(),
        },
        chunks: ['main'],
        inject: 'body',
        scriptLoading: 'defer',
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src/pages/storage.pug'),
        filename: 'storage.html',
        templateParameters: {
          yandexMapsUrl: getYandexMapsUrl(),
          hasYandexSuggestApiKey: hasYandexSuggestApiKey(),
          openRouteServiceApiKey: getOpenRouteServiceApiKey(),
        },
        chunks: ['main'],
        inject: 'body',
        scriptLoading: 'defer',
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src/pages/wheels.pug'),
        filename: 'wheels.html',
        templateParameters: {
          yandexMapsUrl: getYandexMapsUrl(),
          hasYandexSuggestApiKey: hasYandexSuggestApiKey(),
          openRouteServiceApiKey: getOpenRouteServiceApiKey(),
        },
        chunks: ['main'],
        inject: 'body',
        scriptLoading: 'defer',
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src/pages/warming.pug'),
        filename: 'warming.html',
        templateParameters: {
          yandexMapsUrl: getYandexMapsUrl(),
          hasYandexSuggestApiKey: hasYandexSuggestApiKey(),
          openRouteServiceApiKey: getOpenRouteServiceApiKey(),
        },
        chunks: ['main'],
        inject: 'body',
        scriptLoading: 'defer',
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src/pages/jumpstart.pug'),
        filename: 'jumpstart.html',
        templateParameters: {
          yandexMapsUrl: getYandexMapsUrl(),
          hasYandexSuggestApiKey: hasYandexSuggestApiKey(),
          openRouteServiceApiKey: getOpenRouteServiceApiKey(),
        },
        chunks: ['main'],
        inject: 'body',
        scriptLoading: 'defer',
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src/pages/battery.pug'),
        filename: 'battery.html',
        templateParameters: {
          yandexMapsUrl: getYandexMapsUrl(),
          hasYandexSuggestApiKey: hasYandexSuggestApiKey(),
          openRouteServiceApiKey: getOpenRouteServiceApiKey(),
        },
        chunks: ['main'],
        inject: 'body',
        scriptLoading: 'defer',
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src/pages/sidewall.pug'),
        filename: 'sidewall.html',
        templateParameters: {
          yandexMapsUrl: getYandexMapsUrl(),
          hasYandexSuggestApiKey: hasYandexSuggestApiKey(),
          openRouteServiceApiKey: getOpenRouteServiceApiKey(),
        },
        chunks: ['main'],
        inject: 'body',
        scriptLoading: 'defer',
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src/pages/puncture.pug'),
        filename: 'puncture.html',
        templateParameters: {
          yandexMapsUrl: getYandexMapsUrl(),
          hasYandexSuggestApiKey: hasYandexSuggestApiKey(),
          openRouteServiceApiKey: getOpenRouteServiceApiKey(),
        },
        chunks: ['main'],
        inject: 'body',
        scriptLoading: 'defer',
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src/pages/conditioner.pug'),
        filename: 'conditioner.html',
        templateParameters: {
          yandexMapsUrl: getYandexMapsUrl(),
          hasYandexSuggestApiKey: hasYandexSuggestApiKey(),
          openRouteServiceApiKey: getOpenRouteServiceApiKey(),
        },
        chunks: ['main'],
        inject: 'body',
        scriptLoading: 'defer',
      }),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'src/pages/seasonal.pug'),
        filename: 'seasonal.html',
        templateParameters: {
          yandexMapsUrl: getYandexMapsUrl(),
          hasYandexSuggestApiKey: hasYandexSuggestApiKey(),
          openRouteServiceApiKey: getOpenRouteServiceApiKey(),
        },
        chunks: ['main'],
        inject: 'body',
        scriptLoading: 'defer',
      }),
    ],
  };
};
