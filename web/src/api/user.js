
export default {
    login(query , data) {
        return Http.post(`${TopContext.api}/login`, query , data);
    },

    register (query , data) {
        return Http.post(`${TopContext.api}/register`, query , data);
    },

    info () {
        return Http.get(`${TopContext.api}/user_info`);
    },

    updatePassword (query , data) {
        return Http.patch( `${TopContext.api}/user/update_password` , query , data);
    },

    update (query , data) {
        return Http.put(`${TopContext.api}/user` , query , data);
    },

    updatePasswordInLogged (query , data) {
        return Http.patch(`${TopContext.api}/user/update_password_in_logged` , query , data);
    },

    focusHandle (query , data) {
        return Http.post(`${TopContext.api}/user/focus_handle` , query , data);
    } ,

    show (id) {
        return Http.get( `${TopContext.api}/user/${id}/show`);
    } ,

    focusMeUser (userId , query) {
        return Http.get(`${TopContext.api}/user/${userId}/focus_me_user` , query);
    } ,

    myFocusUser (id , query) {
        return Http.get(`${TopContext.api}/user/${id}/my_focus_user` , query);
    } ,

    localUpdate (query , data) {
        return Http.patch(`${TopContext.api}/user` , query , data);
    } ,
};
