const path = require('path')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const VueLoaderPlugin = require('vue-loader/lib/plugin')
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer')
  .BundleAnalyzerPlugin
const WebpackManifestGeneratorPlugin = require('webpack-manifest-generator-plugin')
const CleanWebpackPlugin = require('clean-webpack-plugin').CleanWebpackPlugin
const webpack = require('webpack')
const WebpackBar = require('webpackbar')
const url = require('url')
const zlib = require('zlib')
const _ = require('lodash')
const fs = require('fs')
const paxConfig = require('./pax.config')
require('dotenv').config()

const polyfill =  !_.isUndefined(paxConfig.polyfill) ? paxConfig.polyfill : true

const isWin = process.platform === "win32";

const host = process.env.HOST || 'localhost'
const port = process.env.PORT || '3000'
const analyzer = process.env.ANALYZER || false

module.exports = (env, argv) => {
  const isDev = argv.mode === 'development'
  const config = loadConfig()

  const currentUrl = getCurrentUrl(config)

  const distRelativePath = getDistRelativePath()

  const rewriteUrl = url.parse(currentUrl)
  const proxyUrl = 'http://' + host + ':' + port + rewriteUrl.path
  const publicPath = isDev
    ? 'http://' +
      host +
      ':' +
      port +
      rewriteUrl.path +
      distRelativePath.replace(/\\/g, '/')
    : '/' + distRelativePath.replace(/\\/g, '/')

  const publicRelativePath = distRelativePath.replace(/\\/g, '/')
  let rewriteUrls = {}
  rewriteUrls[currentUrl] = ''
  rewriteUrls['^' + rewriteUrl.path] = ''

  const plugins = []

  if (isDev) {
    if (analyzer) {
      plugins.push(
        new BundleAnalyzerPlugin({
          analyzerMode: 'static',
          openAnalyzer: false,
          reportFilename: 'stat/index.html',
          logLevel: 'silent'
        })
      )
    }
  }

  let entry = []

  if(polyfill) {
    entry = [...entry, '@babel/polyfill']
  }
  return {
    entry: [...entry, path.resolve(__dirname, './core/main.js')],
    output: {
      path: path.resolve(__dirname, `../${paxConfig.codename}/`),
      filename: '[hash].bundle.js',
      publicPath: publicPath
    },
    resolve: {
      extensions: ['*', '.js', '.vue', '.json', '.gql'],
      alias: {
        '~': path.resolve(__dirname, './'),
        assets: path.resolve(__dirname, './assets'),
        vue$: 'vue/dist/vue.esm.js'
      }
    },
    performance: {
      hints: false
    },
    devServer: {
      index: '',
      open: true,
      host: host,
      port: port,
      overlay: {
        errors: true,
        warnings: false
      },
      open: false,
      hotOnly: true,
      historyApiFallback: true,
      compress: false,
      proxy: {
        '/': {
          target: currentUrl,
          secure: false,
          changeOrigin: true,
          autoRewrite: true,
          pathRewrite: rewriteUrls,
          headers: {
            'X-ProxiedBy-Webpack': true
          },
          onProxyRes(proxyRes, req, res) {
            var _write = res.write
            if(proxyRes.headers['set-cookie']) {
              const cookie = proxyRes.headers['set-cookie'][0]
              proxyRes.headers['set-cookie'] = [cookie.replace(/domain=(.*);/, 'domain=localhost;')]
            }

            if (
              /^text\/html/.test(proxyRes.headers['content-type'])
            ) {
              res.write = buffer => {
                if(proxyRes.headers['set-cookie']) {
                  const cookie = proxyRes.headers['set-cookie'][0]
                  res.setHeader('set-cookie', cookie.replace(/domain=(.*);/, 'domain=localhost;'));
                }
                try {
                  const isZipped =
                    proxyRes.headers['content-encoding'] === 'gzip'
                  let body = (isZipped
                    ? zlib.gunzipSync(buffer)
                    : buffer
                  ).toString('utf8')
                  body = _.replace(body, new RegExp(currentUrl, 'g'), proxyUrl)
                  body = body
                    .split(currentUrl.replace(/\//g, '\\/'))
                    .join(proxyUrl.replace(/\//g, '\\/'))
                  body = body
                    .split(currentUrl.replace(/http[s]?:/g, ''))
                    .join(proxyUrl.replace(/http[s]?:/g, ''))

                  let newBuffer = new Buffer.from(body)

                  const gzipRes =  isZipped ? zlib.gzipSync(newBuffer) : newBuffer
                  if(isZipped) {
                    res.setHeader('content-length', gzipRes.length);
                  }
                  _write.call(res, gzipRes)
                } catch (e) {
                  _write.call(res, buffer)
                }
              }
            }
          }
        }
      }
    },
    stats: {
      colors: true,
      hash: false,
      version: true,
      timings: true,
      assets: true,
      chunks: false,
      modules: false,
      reasons: false,
      children: false,
      source: false,
      errors: true,
      errorDetails: false,
      warnings: true,
      publicPath: false
    },
    watch: argv.mode === 'development',
    module: {
      rules: [
        {
          resourceQuery: /blockType=i18n/,
          type: 'javascript/auto',
          loader: '@kazupon/vue-i18n-loader',
        },
        {
          test: /\.(graphql|gql)$/,
          use: 'graphql-tag/loader'
        },
        {
          test: /\.vue$/,
          loader: 'vue-loader',
          options: {
            extractCSS: true
          }
        },
        {
          test: /\.js$/,
          exclude: /node_modules/,
          use: ['babel-loader']
        },
        {
          test: /\.css$/,
          use: [
            argv.mode === 'development'
              ? 'vue-style-loader'
              : MiniCssExtractPlugin.loader,
            'css-loader'
          ]
        },
        {
          test: /\.(pcss|postcss)$/,
          use: [
            argv.mode === 'development'
              ? 'vue-style-loader'
              : MiniCssExtractPlugin.loader,
            'css-loader',
            'postcss-loader'
          ]
        },
        {
          test: /\.scss$/,
          use: [
            argv.mode === 'development'
              ? 'vue-style-loader'
              : MiniCssExtractPlugin.loader,
            'css-loader',
            'postcss-loader',
            'sass-loader',
            {
              loader: 'sass-resources-loader',
              options: {
                resources: [
                  path.resolve(__dirname, './assets/scss/_colors.scss'),
                  path.resolve(__dirname, './assets/scss/_variables.scss')
                ]
              }
            }
          ]
        },
        {
          test: /\.(png|jpe?g|gif|svg|webp)$/,
          use: [
            {
              loader: 'url-loader',
              options: {
                limit: 8192,
                esModule: false,
                fallback: 'file-loader',
                context: path.resolve(__dirname, './assets'),
                outputPath: './',
                publicPath: '../'+publicRelativePath,
                name: '[path][name].[ext]'
              }
            }
          ]
        },
        // {
        //   enforce: 'pre',
        //   test: /\.(js|vue)$/,
        //   loader: 'eslint-loader',
        //   include: __dirname,
        //   exclude: /node_modules/,
        //   options: {
        //     fix: true,
        //   }
        // }
      ]
    },
    plugins: [
      new MiniCssExtractPlugin({
        filename: '[name].[hash].css',
        chunkFilename: '[id].[hash].css'
      }),
      new VueLoaderPlugin(),
      new WebpackManifestGeneratorPlugin({
        filename: 'manifest.json'
      }),
      new WebpackBar(),
      new CleanWebpackPlugin({
        root: path.resolve(__dirname, '../'),
        verbose: true,
        dry: false,
        watch: false
      }),
      new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/),
      ...plugins
    ],
    optimization: {
      runtimeChunk: true,
      splitChunks: {
        cacheGroups: {
          vendor: {
            test: /node_modules/, // you may add "vendor.js" here if you want to
            maxSize: 200000,
            name: 'vendor',
            chunks: 'initial',
            enforce: true
          }
        }
      }
    }
  }
}
const loadConfig = () => {
  let configFile = false
  let currentDir = false
  do {
    currentDir = currentDir
      ? path.resolve(currentDir, '../')
      : path.resolve(__dirname, '../')
    configFile = fs.existsSync(path.resolve(currentDir, './app'))
  } while (!configFile)

  return fs
    .readFileSync(path.resolve(currentDir, './.htaccess'))
    .toString('UTF-8')
}

const getRootDir = () => {
  let configFile = false
  let currentDir = false
  do {
    currentDir = currentDir
      ? path.resolve(currentDir, '../')
      : path.resolve(__dirname, '../')
    configFile = fs.existsSync(path.resolve(currentDir, './app'))
  } while (!configFile)

  return currentDir
}

const getCurrentUrl = config => {
  return `http://${/^#Domain:\s+(.*)/gm.exec(config)[1]}/`
}
const getDistRelativePath = () => {
  const cDir = path.resolve(getRootDir(), './')
  const nDir = path.resolve(__dirname, `../${paxConfig.codename}/`)

  if(isWin) {
    return _.replace(nDir, cDir + '\\', '')
  } else {
    return _.replace(nDir, cDir + '/', '')
  }
}
