
export default {

    show (id) {
        return Http.get(`${TopContext.api}/image/${id}`);
    } ,

    index (query) {
        return Http.get(`${TopContext.api}/image` , query);
    } ,

    hotTags (query) {
        return Http.get(`${TopContext.api}/image/hot_tags` , query);
    } ,

    hotTagsWithPager (query) {
        return Http.get(`${TopContext.api}/image/hot_tags_with_pager`, query);
    } ,

    categories () {
        return Http.get(`${TopContext.api}/image/category`);
    } ,

    incrementViewCount (id) {
        return Http.post( `${TopContext.api}/image/${id}/increment_view_count`);
    } ,

    recommend (id , query) {
        return Http.get(`${TopContext.api}/image/${id}/recommend` , query);
    } ,

    // 最新的图片专题
    newest (query) {
        return Http.get(`${TopContext.api}/image/newest` , query);
    } ,

    // 最热的图片专题
    hot (query) {
        return Http.get(`${TopContext.api}/image/hot` , query);
    } ,

    // 图片专题：根据标签返回
    getByTagId (query) {
        return Http.get(`${TopContext.api}/image/get_by_tag_id` , query);
    } ,

    praiseHandle (id , query , data) {
        return Http.post(`${TopContext.api}/image/${id}/praise_handle` , query , data);
    } ,
};
