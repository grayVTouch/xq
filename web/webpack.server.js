const merge = require('webpack-merge');
const common = require('./webpack.common.js');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

module.exports = merge(common, {
    mode: 'development',
    devtool: 'inline-source-map',
    devServer: {
        // web 网站根目录
        contentBase: './dist' ,
        // 初始访问的文件
        index: 'index.html' ,
        // Ip
        host: '127.0.0.1' ,
        // 端口
        port: 30002 ,
        // 当模块热更新失败的时候，刷新页面。
        // 和 hotOnly 不同弄（失败仅在控制台输出错误）
        hot: true ,
        // 当热加载失败的时候刷新页面，请务必开启
        // 否则当修改js文件后，页面会提示要求实例重新加载
        // 实际上就是要求刷新页面
        // 故而，为了让他自动刷新页面
        // 所以这边需要开启
        // inline: true ,
        // 不显示错误消息
        noInfo: false ,
        // 自动打开默认浏览器
        open: true ,
        // 全屏错误
        overlay: true ,
        // 如果开启了 vue 的 history 模式
        // 那么请务必开启下面这个选项
        historyApiFallback: true ,
        proxy: {
            '/api': {
                target: 'http://api.xq.test' ,
                changeOrigin: true ,
            } ,
        } ,
    } ,
    module: {
        rules: [
            {
                test: /\.css$/ ,
                use: [
                    'vue-style-loader' ,
                    // 热模块加载 和 提取 css 不兼容
                    // 具体请看该链接文章： https://blog.csdn.net/weixin_45615791/article/details/104294458
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
