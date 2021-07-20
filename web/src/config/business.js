export default {
    bool_for_int: {
        0: '否',
        1: '是'
    } ,
    admin_permission: {
        type: {
            view: 'view' ,
            api: 'api'
        }
    } ,
    image_project: {
        type: {
            pro: '专题' ,
            misc: '杂项' ,
        } ,

        status: {
            '-1' : '审核失败' ,
            0: '待审核' ,
            1: '审核通过'
        }
    } ,

    user: {
        sex: {
            male: '男' ,
            female: '女' ,
            secret: '保密' ,
            both: '两性' ,
            shemale: '人妖' ,
        } ,
    } ,

    // 关联事物类型
    relationType: {
        image_project: '图片专题' ,
        video_project: '视频专题' ,
        image: '图片' ,
        video: '视频' ,
    } ,

    contentType: {
        image_project: '图片专题' ,
        video_project: '视频专题' ,
        image: '图片' ,
        video: '视频' ,
    } ,
};
