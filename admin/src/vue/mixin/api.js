export default {
    fileApi (resize = false) {
        return TopContext.fileApi;
    } ,

    imageApi (resize = false , isUploadToCloud = true) {
        let url = TopContext.uploadImageApi  + (resize ? '?w=' + TopContext.val.imageW : '');
        if (resize) {
            url += '&';
        } else {
            url += '?';
        }
        url += 'is_upload_to_cloud=' + (isUploadToCloud ? 1 : 0);
        return url;
    } ,

    thumbApi (resize = true) {
        return TopContext.uploadImageApi + (resize ? '?w=' + TopContext.val.thumbW : '');
    } ,

    videoApi () {
        return TopContext.uploadVideoApi;
    } ,

    subtitleApi () {
        return TopContext.uploadSubtitleApi;
    } ,

    officeApi () {
        return TopContext.uploadOfficeApi;
    } ,
};
