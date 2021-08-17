
export default {
    settings (query , data) {
        return Http.get( `${TopContext.api}/settings` , query , data);
    },
};
