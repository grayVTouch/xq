const merge = require('webpack-merge');
const common = require('./webpack.common.js');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const TerserPlugin = require("terser-webpack-plugin");
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const CompressionPlugin = require("compression-webpack-plugin");
// 使用了 clean-webpack-plugin & html-webpack-plugin 插件后
// 结合 webpack-dev-server 进行开发时，编译后文件会常驻内存
// 且 ./dist 目录会被删除！！
// 也就是说会发生 dist 目录消失的现象。
// 注意该插件的官方用法发生变更，如果使用的最新版的
// 请更新成以下这种写法
// 更新这种写法的主要原因是
// 目前猜测是因为没有默认导出，允许自定义接收
// 自己需要的部分
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

module.exports = merge(common, {
    mode: 'production' ,
    optimization: {
        minimize: true,
        minimizer: [new TerserPlugin()],
    },
    plugins: [
        new CleanWebpackPlugin({
            // 仅删除陈旧的资源
            cleanStaleWebpackAssets: false ,
            // 排除
            exclude: ['*.htaccess'] ,
        }) ,
        new MiniCssExtractPlugin({
            filename: "css/[name]-[hash].css",
            chunkFilename: "css/chunk-[name]-[hash].css"
        }) ,
        // 包体积分析工具
        new BundleAnalyzerPlugin({
            analyzerMode: 'static',
            reportFilename: 'BundleReport.html',
            logLevel: 'info'
        }) ,
        // 压缩包体积
        new CompressionPlugin({
            test: /\.js$/ ,
            filename: "js/[name].gz" ,
        }),
        new CompressionPlugin({
            test: /\.css$/ ,
            filename: "css/[name].gz" ,
        }),
    ] ,
    module: {
        rules: [
            {
                // test: /\.s?[ac]ss$/,
                test: /\.css$/ ,
                use: [
                    {
                        loader: MiniCssExtractPlugin.loader,
                        options: {
                            /**
                             * 如果没加 publicPath 的情况下，css 中通过 @import 或 url() 等引入的文件
                             * 加载的目录会默认是 css 文件所在目录
                             * 而实际上字体文件的定位是 dist 目录所在目录！
                             * 所以需要给出 publicPath 指定 dist 编译的根目录
                             */
                            // publicPath: '../',
                        },
                    },
                    {
                        loader: 'css-loader' ,
                        options: {
                            esModule: false ,
                        }
                    } ,
                ],
            } ,
        ] ,
    } ,
});
