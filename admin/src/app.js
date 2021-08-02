
import '@asset/css/vars.css';
import '@asset/css/base.css';
import '@asset/css/iview_reset.css';

/**
 * **************************
 * 注意：以下加载有严格顺序
 * **************************
 */
//
import '@bootstrap/my_plugin.js';

import '@config/context.js';
import '@util/common.js';
import '@util/./util/http.js';
import '@util/api.js';
//
import '@bootstrap/vue.js';
import '@vue/mixin/mixin.js';
import '@vue/directive/directive.js';
import '@bootstrap/iview.js';

import '@bootstrap/my_view.js';

import router from '@vue/router/index.js';
import store from '@vue/vuex';

import app from './app.vue';

Vue.config.debug = TopContext.debug;

Vue.config.devtools = TopContext.debug;

Vue.config.productionTip = TopContext.debug;

/**
 * ****************
 * vue 实例
 * ****************
 */
new Vue({
    el: '#app' ,
    store ,
    router ,
    render (h) {
        return h(app);
    }
});
