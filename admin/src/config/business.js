export default {

    contentType: {
        image: '图片' ,
        video: '视频' ,
        image_project: '图片专题' ,
        video_project: '视频专题' ,
    } ,

    bool: {
        integer: {
            0: '否',
            1: '是'
        } ,
        string: {
            'y': '是' ,
            'n': '否' ,
        } ,
    } ,

    adminPermission: {
        type: {
            view: 'view' ,
            api: 'api'
        }
    } ,
    imageProject: {
        type: {
            pro: '专题' ,
            misc: '杂项' ,
        } ,

        status: {
            '-1' : '审核失败' ,
            0: '待审核' ,
            1: '审核通过'
        } ,

        processStatus: {
            '-1': '处理失败' ,
            '0': '待处理' ,
            '1': '处理中' ,
            '2': '处理完成' ,
        } ,
    } ,

    videoProject: {

        endStatus: {
            making : '连载中' ,
            completed: '已完结' ,
            terminated: '已终止'
        } ,
        status: {
            '-1' : '审核失败' ,
            0: '待审核' ,
            1: '审核通过'
        } ,
        fileProcessStatus: {
            '-1': '处理失败' ,
            '0': '待处理' ,
            '1': '处理中' ,
            '2': '处理完成' ,
        } ,
    } ,

    video: {
        type: {
            pro: '专题' ,
            misc: '杂项' ,
        } ,

        status: {
            '-1' : '审核失败' ,
            0: '审核中' ,
            1: '审核通过'
        } ,
        videoProcessStatus: {
            '-1': '处理失败' ,
            '0': '待处理' ,
            '1': '处理中' ,
            '2': '转码中' ,
            '3': '处理完成' ,
        } ,
        fileProcessStatus: {
            '-1': '处理失败' ,
            '0': '待处理' ,
            '1': '处理中' ,
            '2': '处理完成' ,
        } ,
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

    platform: {
        web: 'web端' ,
        app: 'app' ,
        android: 'android' ,
        ios: 'ios' ,
        mobile: '移动端' ,
    } ,

    disk: {
        os: {
            windows: 'windows' ,
            linux: 'linux' ,
            mac: 'mac' ,
        } ,
    } ,

    imageSubject: {
        status: {
            '-1' : '审核失败' ,
            0: '待审核' ,
            1: '审核通过'
        }
    } ,

    videoSubject: {
        status: {
            '-1' : '审核失败' ,
            0: '待审核' ,
            1: '审核通过'
        }
    } ,

    videoSeries: {
        status: {
            '-1' : '审核失败' ,
            0: '待审核' ,
            1: '审核通过'
        }
    } ,

    videoCompany: {
        status: {
            '-1' : '审核失败' ,
            0: '待审核' ,
            1: '审核通过'
        }
    } ,

    tag: {
        status: {
            '-1' : '审核失败' ,
            0: '待审核' ,
            1: '审核通过'
        }
    } ,

    category: {
        status: {
            '-1' : '审核失败' ,
            0: '待审核' ,
            1: '审核通过'
        } ,
        type: {
            image: '图片' ,
            video: '视频' ,
            image_project: '图片专题' ,
            video_project: '视频专题' ,
        } ,
    } ,

    nav: {
        type: {
            image_project: '图片专题' ,
            video_project: '视频专题' ,
        } ,
    } ,

    settings: {
        disk: {
            local: '本地存储' ,
            aliyun: '阿里云' ,
        } ,
    } ,
};
