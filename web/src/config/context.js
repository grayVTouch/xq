// import logo from "../res/logo.jpg";
// import avatar from "../res/avatar.jpg";
// import notFound from "../res/404.png";
import config from './config.js';
import business from './business.js';
import table from './table.js';

/**
 * ******************
 * 全局上下文环境
 * ******************
 */
// 接口 host
const apiUrl = config.apiUrl;
// 资源 host
const resUrl = config.resUrl;

window.TopContext = {
    ...config ,

    host: apiUrl ,
    api: apiUrl + '/api/web' ,
    // // 图片上传 api
    fileApi: apiUrl + '/api/web/upload' ,

    // 图片上传 api
    uploadApi: apiUrl + '/api/web/upload' ,
    uploadImageApi: apiUrl + '/api/web/upload_image' ,
    uploadVideoApi: apiUrl + '/api/web/upload_video' ,
    uploadSubtitleApi: apiUrl + '/api/web/upload_subtitle' ,
    uploadOfficeApi: apiUrl + '/api/web/upload_office' ,

    code: {
        Success: 200 ,
        AuthFailed: 401 ,
        FormValidateFail: 400 ,
    } ,
    res: {
        logo: resUrl + '/resource/preset/ico/logo.png' ,
        notFound: resUrl + '/resource/preset/image/404.jpg' ,
        avatar: resUrl + '/resource/preset/ico/logo.png' ,

        user: {
            password: '' ,

        } ,
    } ,
    business ,
    table ,
    // 每页显示记录数
    size: 20 ,
    sizes: [
        20 ,
        50 ,
        100 ,
        200 ,
    ] ,
    val: {
        fixedTop: 105 ,
        headerH: 105 ,
        footerH: 132 ,
        // 封面图 裁剪尺寸
        thumbW: 960 ,
        // 大图裁剪尺寸
        imageW: 2160 ,
    } ,

    os: {
        name: '兴趣部落' ,
        version: '1.0.0' ,
        author: 'running'
    } ,
};
