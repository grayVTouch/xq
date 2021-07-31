const merge = require('webpack-merge');
const common = require('./webpack.common.js');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

module.exports = merge(common, {
    mode: 'production' ,
    plugins: [
        new MiniCssExtractPlugin({
            filename: "css/[name]-[hash].css",
            chunkFilename: "css/chunk-[name]-[hash].css"
        }) ,
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
