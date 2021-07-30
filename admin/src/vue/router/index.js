import routes from "./routes.js";

const router = new VueRouter({
    mode: TopContext.routerMode ,
    routes ,
    // 初始化滚动条位置
    scrollBehavior (to, from, savedPosition) {
        return { x: 0 , y: 0 };
    }
});

/**
 * ****************
 * 路由守卫
 * ****************
 */
router.beforeEach((to , from , next) => {
    const token = G.cookie.get('token');
    if (!G.isEmptyString(token)) {
        if (to.name === 'login') {
            // 已经跳转到首页
            next({name: 'home'});
            return ;
        }
    } else {
        // 未登录
        if (to.name !== 'login') {
            next({name: 'login'});
            return ;
        }
    }
    next();
});

export default router;
