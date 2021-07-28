

export default {

    less (query) {
        return Http.get(`${TopContext.api}/less_history`, query);
    },

    index (query) {
        return Http.get( `${TopContext.api}/history`, query);
    },

    store (query , data) {
        return Http.post(`${TopContext.api}/history` , query , data);
    },

    destroyAll (query , data) {
        return Http.delete(`${TopContext.api}//destroy_all_history`, query , data);
    },
};
