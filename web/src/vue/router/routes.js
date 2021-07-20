
export default [
    {
        name: '404' ,
        path: '*' ,
        component: () => import('@vue/view/error/404.vue')
    } ,
    {
        name: 'welcome' ,
        path: '/welcome' ,
        component: () => import('@vue/view/welcome/welcome.vue') ,
    } ,
    {
        name: 'home' ,
        path: '/' ,
        component: () => import('@vue/view/public/home.vue') ,
        redirect: '/index' ,
        children: [
            {
                name: 'index' ,
                path: 'index' ,
                component: () => import('@vue/view/index/index.vue')
            } ,
            {
                path: 'image_project' ,
                component: () => import('@vue/view/image_project/index.vue') ,
            } ,
            {
                path: 'image_project/:id/show' ,
                component: () => import('@vue/view/image_project/show.vue') ,
                props: true ,
            } ,
            {
                path: 'image_project/search' ,
                component: () => import('@vue/view/image_project/search.vue') ,
                props: true ,
            } ,
            {
                path: 'collection_group/:id' ,
                component: () => import('@vue/view/collection_group/index.vue') ,
                props: true ,
                children: [
                    {
                        path: 'image_project' ,
                        component: () => import('@vue/view/collection_group/image_project.vue')
                    }
                ] ,
            } ,
            {
                name: 'channel' ,
                path: 'channel/:id' ,
                redirect: 'channel/:id/image_project' ,
                component: () => import('@vue/view/channel/index.vue') ,
                props: true ,
                children: [
                    {
                        path: 'image_project' ,
                        component:  () => import('@vue/view/channel/image_project.vue') ,
                    } ,
                    {
                        path: 'my_focus_user' ,
                        component: () => import('@vue/view/channel/my_focus_user.vue')
                    } ,
                    {
                        path: 'focus_me_user' ,
                        component: () => import('@vue/view/channel/focus_me_user.vue') ,
                    } ,
                ] ,
            } ,
            {
                path: 'user' ,
                component: () => import('@vue/view/user/index.vue') ,
                redirect: '/user/info' ,
                children: [
                    {
                        path: 'info' ,
                        component: () => import('@vue/view/user/info.vue')
                    } ,
                    {
                        path: 'password' ,
                        component: () => import('@vue/view/user/password.vue')
                    } ,
                    {
                        path: 'history' ,
                        component: () => import('@vue/view/user/history.vue')
                    } ,
                    {
                        path: 'favorites' ,
                        component: () => import('@vue/view/user/favorites.vue')
                    } ,
                    {
                        path: 'praise' ,
                        component: () => import('@vue/view/user/praise.vue')
                    } ,
                ] ,
            } ,
            {
                path: 'video_project' ,
                alias: 'video_project/search' ,
                component: () => import('@vue/view/video_project/search.vue')
            } ,
            {
                path: 'video_project/:id/show' ,
                component: () => import('@vue/view/video_project/show.vue') ,
                props: true ,
            } ,
            {
                path: 'image/:id/show' ,
                component: () => import('@vue/view/image/show.vue') ,
                props: true ,
            } ,
            {
                path: 'image/search' ,
                component: () => import('@vue/view/image/search.vue') ,
                props: true ,
            } ,
            {
                path: 'video/:id/show' ,
                component: () => import('@vue/view/video/show.vue') ,
                props: true ,
            } ,
            {
                path: 'video/search' ,
                component: () => import('@vue/view/video/search.vue') ,
                props: true ,
            } ,
        ] ,
    } ,
]
