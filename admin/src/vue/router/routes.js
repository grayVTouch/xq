/**
 * 同步加载
 */

const index = () => import('@vue/view/index/index.vue');

export default [

    {
        name: '404' ,
        path: '*' ,
        component: () => import('@vue/view/error/404.vue') ,
        async: false ,
    } ,
    {
        name: 'login' ,
        path: '/login' ,
        component: () => import('@vue/view/login/login.vue') ,
        async: false ,
    } ,
    {
        name: 'home' ,
        path: '/' ,
        component: index ,
        redirect: '/pannel' ,
        async: false ,
        children: [
            {
                path: 'pannel' ,
                component: () => import('@vue/view/pannel/pannel.vue') ,
            } ,
        ]
    } ,
    {
        path: '/admin' ,
        component: index ,
        children: [
            {
                path: 'index' ,
                component: () => import('@vue/view/admin/index.vue') ,
            }
        ] ,
    } ,
    {
        path: '/user' ,
        component: index ,
        children: [
            {
                path: 'index' ,
                component: () => import('@vue/view/user/index.vue') ,
            }
        ] ,
    } ,
    {
        path: '/image' ,
        component: index ,
        children: [
            {
                path: 'subject' ,
                component: () => import('@vue/view/image_subject/index.vue') ,
            } ,
            {
                path: 'project' ,
                component: () => import('@vue/view/image_project/index.vue') ,
            } ,
        ] ,
    } ,
    {
        path: '/video' ,
        component: index ,
        children: [
            {
                path: 'series' ,
                component: () => import('@vue/view/video_series/index.vue') ,
            } ,
            {
                path: 'company' ,
                component: () => import('@vue/view/video_company/index.vue') ,
            } ,
            {
                path: 'subject' ,
                component: () => import('@vue/view/video_subject/index.vue') ,
            } ,
            {
                path: 'project' ,
                component: () => import('@vue/view/video_project/index.vue') ,
            } ,
            {
                path: 'index' ,
                component: () => import('@vue/view/video/index.vue') ,
            } ,
        ] ,
    } ,
    {
        path: '/tag' ,
        component: index ,
        children: [
            {
                path: 'index' ,
                component: () => import('@vue/view/tag/index.vue') ,
            } ,
        ] ,
    } ,
    {
        path: '/category' ,
        component: index ,
        children: [
            {
                path: 'index' ,
                component: () => import('@vue/view/category/index.vue') ,
            } ,
        ] ,
    } ,
    {
        path: '/module' ,
        component: index ,
        children: [
            {
                path: 'index' ,
                component: () => import('@vue/view/module/index.vue') ,
            } ,
        ] ,
    } ,
    {
        path: '/system' ,
        component: index ,
        children: [
            {
                path: 'settings' ,
                component: () => import('@vue/view/settings/index.vue') ,
            } ,
            {
                path: 'disk' ,
                component: () => import('@vue/view/disk/index.vue') ,
            } ,
            {
                path: 'navigation' ,
                component: () => import('@vue/view/nav/index.vue') ,
            } ,
            {
                path: 'position' ,
                component: () => import('@vue/view/position/index.vue') ,
            } ,
            {
                path: 'imageAtPosition' ,
                component: () => import('@vue/view/image_at_position/index.vue') ,
            } ,
        ] ,
    } ,
    {
        path: '/toolbox' ,
        component: index ,
        children: [
            {
                path: 'index' ,
                component: () => import('@vue/view/toolbox/index.vue') ,
            } ,
        ] ,
    } ,
];
