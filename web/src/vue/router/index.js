import routes from "./routes.js";

const router = new VueRouter({
    routes ,
    mode: TopContext.routerMode ,
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
    const module = G.s.get('module');
    if (G.isNull(module)) {
        if (to.name !== 'welcome') {
            // 以默认模块进入页面
            Api.module
                .default().then((res) => {
                if (res.code !== TopContext.code.Success) {
                    Prompt.error(res.message);
                    return ;
                }
                G.s.json('module' , res.data);
                next();
            });
            return ;
        }
    }
    // 切换路由的时候滚动条重置到 0 的位置
    // G.scrollTo(0 , 'y' , 0 , 0);
    // 进入路由对应组件
    next();
});

export default router;
