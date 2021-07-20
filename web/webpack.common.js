const path = require('path');
// 使用了 clean-webpack-plugin & html-webpack-plugin 插件后
// 结合 webpack-dev-server 进行开发时，编译后文件会常驻内存
// 且 ./dist 目录会被删除！！
// 也就是说会发生 dist 目录消失的现象。
// 注意该插件的官方用法发生变更，如果使用的最新版的
// 请更新成以下这种写法
// 更新这种写法的主要原因是
// 目前猜测是因为没有默认导出，允许自定义接收
// 自己需要的部分
// const CleanWebpackPlugin = require('clean-webpack-plugin');

const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const VueLoaderPlugin = require('vue-loader/lib/plugin');
// const TerserJSPlugin = require('terser-webpack-plugin');
// const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');

module.exports = {
    entry: {
        // babel-polyfill，在 ie 环境下，vuex 需要用到！
        // 相关文档请看 babel 官方文档: https://www.babeljs.cn/docs/babel-polyfill
        app: ['@babel/polyfill' , './src/app.js'] ,
    },
    // optimization: {
    //     minimizer: [new TerserJSPlugin({}), new OptimizeCSSAssetsPlugin({})],
    // },
    plugins: [

        // 这个用法错了
        // new CleanWebpackPlugin(['compiled']),
        new CleanWebpackPlugin({
            // 仅删除陈旧的资源
            cleanStaleWebpackAssets: false ,
        }) ,
        new HtmlWebpackPlugin({
            title: '兴趣部落' ,
            filename: 'index.html' ,
            template: 'template.html' ,

            meta: {
                // 'viewport': 'width=device-width, initial-scale=1' ,
            } ,
            inject: true ,
            favicon: __dirname + '/src/asset/res/logo.png' ,
            templateParameters: {
                // resUrl: 'http://res.xq.test'
            } ,
        }) ,
        new VueLoaderPlugin() ,
        // new MiniCssExtractPlugin({
        //     filename: '[name].css',
        //     chunkFilename: '[id].css',
        // }),
    ],
    output: {
        filename: 'js/[name]-[hash].js',
        path: path.resolve(__dirname, 'dist') ,
        chunkFilename: "js/chunk-[name]-[hash].js" ,
        publicPath: '/' ,
    } ,
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /(node_modules|bower_components)/,
                use: [
                    {
                        loader: 'babel-loader' ,
                        options: {
                            presets: ["@babel/preset-env"] ,
                            plugins: [
                                // 提升运行速度 详情请查看 https://webpack.js.org/loaders/babel-loader/#root
                                '@babel/plugin-transform-runtime' ,
                                // 支持动态导入语法
                                '@babel/plugin-syntax-dynamic-import' ,
                                // iview 组件动态加载
                                [
                                    "import" ,
                                    {
                                        "libraryName": "iview" ,
                                        "libraryDirectory": "src/components"
                                    }
                                ] ,
                            ]
                        }
                    }
                ]
            } ,
            {
                test: /\.(png|svg|jpg|gif|jpeg)$/,
                use: [
                    {
                        // 请使用该文件加载器
                        // 它能够复制在 js 中通过 import 加载的文件
                        // 也能够复制在 css 中通过
                        // background: url('test.jpg') 这种方式引入的文件
                        // 是一种加强型的 文件加载器
                        loader: 'url-loader' ,
                        options: {
                            name: 'asset/[name].[ext]' ,
                            esModule: false
                        }

                    }
                ]
            } ,
            {
                test: /\.(woff|woff2|eot|ttf|otf)$/,
                use: [
                    'file-loader'
                ]
            } ,
            {
                test: /\.(csv|tsv)$/,
                use: [
                    'csv-loader'
                ]
            } ,
            {
                test: /\.xml$/ ,
                use: [
                    'xml-loader'
                ]
            } ,
            {
                test: /\.vue$/ ,
                loader: 'vue-loader'
            }
        ]
    } ,
    // 相关依赖
    // 简化导入
    resolve: {
        alias: {
            'vue': 'vue/dist/vue.js' ,
            'vue-router': 'vue-router/dist/vue-router.js' ,
            'vuex': 'vuex/dist/vuex.js' ,

            // 相关目录
            '@asset': path.resolve(__dirname , './src/asset') ,
            '@api': path.resolve(__dirname , './src/api') ,
            '@plugin': path.resolve(__dirname , './src/plugin') ,
            '@vue': path.resolve(__dirname , './src/vue') ,
            '@bootstrap': path.resolve(__dirname , './src/bootstrap') ,
            '@config': path.resolve(__dirname , './src/config') ,
            '@util': path.resolve(__dirname , './src/util') ,
            '@common': path.resolve(__dirname , './src/common') ,
        }
    }
};
