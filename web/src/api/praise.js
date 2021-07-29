

export default {

    index (query) {
        return Http.get( `${TopContext.api}/praise`, query);
    },

    destroyAll (query , data) {
        return Http.delete(`${TopContext.api}/destroy_all_praise`, query , data);
    },

    praiseHandle (query , data) {
        return Http.post(`${TopContext.api}/praise_handle`, query , data);
    },

};
