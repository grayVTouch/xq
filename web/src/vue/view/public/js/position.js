
let id              = 1;
const indexId         = id++;
const imageId         = id++;
const videoId         = id++;
const videoProjectId  = id++;
const imageProjectId  = id++;
const userId          = id++;
const channelId       = id++;

export default [
    {
        id: indexId ,
        key: 'index' ,
        name: '首页' ,
        route: '/index' ,
        hidden: false ,

        parentId: 0 ,
        children: [] ,
    } ,
    {
        id: imageId,
        key: 'image',
        name: '图片',
        route: '/image/search',
        hidden: false,
        parentId: 0,
        children: [
            {
                id: id++ ,
                key: 'image_detail' ,
                name: '图片详情' ,
                route: '/image/:id/show' ,
                hidden: true ,
                parentId: imageId ,
                children: [] ,
            } ,
            {
                id: id++ ,
                key: 'image_search' ,
                name: '搜索' ,
                route: '/image/search' ,
                hidden: true ,
                parentId: imageId ,
                children: [] ,
            } ,
        ]
    } ,
    {
        id: videoId,
        key: 'image',
        name: '视频',
        route: '/video/search',
        hidden: false,
        parentId: 0,
        children: [
            {
                id: id++ ,
                key: 'video_detail' ,
                name: '视频详情' ,
                route: '/video/:id/show' ,
                hidden: true ,
                parentId: videoId ,
                children: [] ,
            } ,
            {
                id: id++ ,
                key: 'video_search' ,
                name: '搜索' ,
                route: '/video/search' ,
                hidden: true ,
                parentId: videoId ,
                children: [] ,
            } ,
        ]
    } ,
    {
        id: videoProjectId ,
        key: 'video_project' ,
        name: '视频专题' ,
        route: '/video_project' ,
        hidden: false ,

        parentId: 0 ,
        children: [
            {
                id: id++ ,
                key: 'video_project_detail' ,
                name: '视频专题详情' ,
                route: '/video_project/:id/show' ,
                hidden: true ,
                parentId: videoProjectId ,
                children: [] ,
            } ,
            {
                id: id++ ,
                key: 'video_project_search' ,
                name: '搜索' ,
                route: '/video_project/search' ,
                hidden: true ,

                parentId: videoProjectId ,
                children: [] ,
            } ,
        ] ,
    } ,
    {
        id: imageProjectId ,
        key: 'image_project' ,
        name: '图片专题' ,
        route: '/image_project' ,
        hidden: false ,

        parentId: 0 ,
        children: [
            {
                id: id++ ,
                key: 'image_project_detail' ,
                name: '图片专题详情' ,
                route: '/image_project/:id/show' ,
                hidden: true ,

                parentId: imageProjectId ,
                children: [] ,
            } ,
            {
                id: id++ ,
                key: 'image_project_search' ,
                name: '搜索' ,
                route: '/image_project/search' ,
                hidden: true ,

                parentId: imageProjectId ,
                children: [] ,
            } ,
        ] ,
    } ,
    {
        id: userId ,
        key: 'user_center' ,
        name: '用户中心' ,
        route: '/user' ,
        hidden: false ,

        parentId: 0 ,
        children: [
            {
                id: id++ ,
                key: 'user_info' ,
                name: '用户信息' ,
                route: '/user/info' ,
                hidden: true ,

                parentId: userId ,
                children: [] ,
            } ,
            {
                id: id++ ,
                key: 'update_password' ,
                name: '修改密码' ,
                route: '/user/password' ,
                hidden: true ,

                parentId: userId ,
                children: [] ,
            } ,
            {
                id: id++ ,
                key: 'my_history' ,
                name: '历史记录' ,
                route: '/user/history' ,
                hidden: true ,

                parentId: userId ,
                children: [] ,
            } ,
            {
                id: id++ ,
                key: 'my_favorite' ,
                name: '我的收藏' ,
                route: '/user/favorites' ,
                hidden: true ,
                parentId: userId ,
                children: [] ,
            } ,
            {
                id: id++ ,
                key: 'my_praise' ,
                name: '我的点赞' ,
                route: '/user/praise' ,
                hidden: true ,
                parentId: userId ,
                children: [] ,
            } ,
        ] ,
    } ,
    {
        id: channelId ,
        key: 'channel' ,
        name: '频道' ,
        route: '/channel' ,
        hidden: true ,
        parentId: 0 ,
        children: [
            {
                id: id++ ,
                key: 'channel_image_project' ,
                name: '图片专题' ,
                route: '/channel/:id/image_project' ,
                hidden: true ,
                parentId: channelId ,
                children: [
                    {
                        id: id++ ,
                        key: 'collection_group_image_project' ,
                        name: '图片专题' ,
                        route: '/collection_group/:id/image_project' ,
                        hidden: true ,
                        parentId: id - 2 ,
                        children: [] ,
                    } ,
                ] ,
            } ,
            {
                id: id++ ,
                key: 'focus_my_user' ,
                name: '关注我的人' ,
                route: '/channel/:id/focus_me_user' ,
                hidden: true ,
                parentId: channelId ,
                children: [] ,
            } ,
            {
                id: id++ ,
                key: 'my_focus_user' ,
                name: '我关注的人' ,
                route: '/channel/:id/my_focus_user' ,
                hidden: true ,

                parentId: channelId ,
                children: [] ,
            } ,
        ] ,
    } ,
];
