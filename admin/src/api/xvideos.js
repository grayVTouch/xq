
export default {

    parse (query , data) {
        return Http.post(`${TopContext.api}/parse_xvideos_video` , query , data);
    } ,

    download (query , data) {
        return Http.post(`${TopContext.api}/download_xvideos_video` , null , data);
    } ,
};
