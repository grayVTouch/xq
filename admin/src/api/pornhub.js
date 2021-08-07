
export default {

    parse (query , data) {
        return Http.post(`${TopContext.api}/parse_pornhub_video` , query , data);
    } ,

    download (query , data) {
        return Http.post(`${TopContext.api}/download_pornhub_video` , null , data);
    } ,
};
