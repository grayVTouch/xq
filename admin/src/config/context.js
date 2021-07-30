// import logo from "../res/logo.jpg";
// import notFound from "../res/404.png";
import business from './business.js';
import table from './table.js';
import config from './config.js';
import style from './style.js';

/**
 * ******************
 * 全局上下文环境
 * ******************
 */

// 接口 host
const api = config.apiUrl;
// 资源 host
const resUrl = config.resUrl;

window.TopContext = {
    ...config ,
    api: api + '/api/admin' ,
    resUrl ,
    // 图片上传 api
    fileApi: api + '/api/admin/upload' ,
    uploadApi: api + '/api/admin/upload' ,
    uploadImageApi: api + '/api/admin/upload_image' ,
    uploadVideoApi: api + '/api/admin/upload_video' ,
    uploadSubtitleApi: api + '/api/admin/upload_subtitle' ,
    uploadOfficeApi: api + '/api/admin/upload_office' ,
    code: {
        Success: 200 ,
        AuthFailed: 401 ,
        FormValidateFail: 400 ,
    } ,
    res: {
        logo: resUrl + '/resource/preset/ico/logo.png' ,
        notFound: resUrl + '/resource/preset/image/404.jpg' ,
        avatar: resUrl + '/resource/preset/ico/logo.png' ,
    } ,
    business ,
    table ,
    // 表单信息
    form: {
        itemW: 400 ,
    } ,
    style ,
    // 每页显示记录数
    size: 8 ,
    sizes: [8 , 20 , 50 , 100 , 200] ,
    // 系统信息
    os: {
        name: '兴趣部落' ,
    } ,
    val: {
        thumbW: 960 ,
        imageW: 2160 ,
    } ,
    sort: {
        none: 'normal' ,
        asc: 'asc' ,
        desc: 'desc' ,
    } ,
};
