
export default {

    show (id) {
        return Http.get(`${TopContext.api}/video/${id}`);
    } ,

    index (query) {
        return Http.get(`${TopContext.api}/video` , query);
    } ,

    incrementViewCount (id) {
        return Http.post(`${TopContext.api}/video/${id}/increment_view_count`);
    } ,

    incrementPlayCount (id) {
        return Http.post(`${TopContext.api}/video/${id}/increment_play_count`);
    } ,

    praiseHandle (id , query , data) {
        return Http.post(`${TopContext.api}/video/${id}/praise_handle` , query , data);
    } ,

    record (id , query , data) {
        return Http.post(`${TopContext.api}/video/${id}/record` , query , data);
    } ,

    newest (query) {
        return Http.get(`${TopContext.api}/video/newest` , query);
    } ,

    hotTags (query) {
        return Http.get(`${TopContext.api}/video/hot_tags` , query);
    } ,

    hotTagsWithPager (query) {
        return Http.get(`${TopContext.api}/video/hot_tags_with_pager`, query);
    } ,

    getByTagId (query) {
        return  Http.get(`${TopContext.api}/video/get_by_tag_id` , query);
    } ,

    getByTagIds (query) {
        return Http.get(`${TopContext.api}/video/get_by_tag_ids` , query);
    } ,

    categories () {
        return Http.get(`${TopContext.api}/video/category`);
    } ,

    recommend (id , query) {
        return Http.get(`${TopContext.api}/video/${id}/recommend` , query);
    } ,

    hot (query) {
        return Http.get(`${TopContext.api}/video/hot`, query);
    } ,
};
