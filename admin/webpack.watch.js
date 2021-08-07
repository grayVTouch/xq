const merge = require('webpack-merge');
const common = require('./webpack.common.js');
const VueLoaderPlugin = require('vue-loader/lib/plugin');

module.exports = merge(common, {
    mode: 'development',
    devtool: 'inline-source-map' ,
    plugins: [
        new VueLoaderPlugin() ,
    ] ,
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
});
