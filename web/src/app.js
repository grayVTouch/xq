
import '@asset/css/base.css';
import '@asset/css/vars.css';
import '@asset/css/animation.css';

/**
 * **************************
 * 注意：以下加载有严格顺序
 * **************************
 */
import '@config/context.js';
import '@common/common.js';
import '@bootstrap/api.js';
//
import '@bootstrap/vue.js';
import '@vue/mixin/mixin.js';
import '@vue/directive/directive.js';
import '@vue/filter/filter.js';

// import vuetify from '@asset/js/vuetify.js';
// import '@asset/js/iview.js';

import '@bootstrap/my_plugin.js';
import '@bootstrap/my_view.js';

// 第三方插件
import '@bootstrap/plugin.js';


import '@util/http.js';



import router from '@vue/router/index.js';

import app from './app.vue';

const debug = true;

Vue.config.debug = debug;

Vue.config.devtools = debug;

Vue.config.productionTip = debug;



/**
 * ****************
 * vue 实例
 * ****************
 */
new Vue({
    el: '#app' ,
    // vuetify 框架
    // vuetify ,
    // 路由仅允许在根组件上注册！！
    // 不允许在嵌套组件上注册
    router ,
    render (h) {
        return h(app);
    } ,
});
