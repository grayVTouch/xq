

export default {

    getWithCollection (query) {
        return Http.get(`${TopContext.api}/collection_group_with_collection`, query);
    },

    collections (id , query) {
        return Http.get(`${TopContext.api}/collection_group/${id}/collection`, query);
    },

    index (query) {
        return Http.get( `${TopContext.api}/collection_group`, query);
    },

    store (query , data) {
        return Http.post(`${TopContext.api}/collection_group` , query , data);
    },

    update (id , query , data) {
        return Http.put(`${TopContext.api}/collection_group/${id}` , query , data);
    },

    destroyAll (query , data) {
        return Http.delete(`${TopContext.api}/destroy_all_collection_group`, query , data);
    },

    collectOrCancel (id , query , data) {
        return Http.post( `${TopContext.api}/collection_group/${id}/collect_or_cancel` , query , data);
    },

    show (id) {
        return Http.get(`${TopContext.api}/collection_group/${id}`);
    } ,

    join (id , query , data) {
        return Http.post(`${TopContext.api}/collection_group/${id}/join`, 'post', data);
    },

    createAndJoin (query , data) {
        return Http.post(`${TopContext.api}/create_and_join_collection_group`, query , data);
    },

    getWithJudge (query) {
        return Http.get(`${TopContext.api}/collection_group_with_judge`, query);
    },

};
