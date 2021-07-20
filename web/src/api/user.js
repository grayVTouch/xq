const login = `${TopContext.api}/login`;
const joinCollectionGroup = `${TopContext.api}/user/join_collection_group`;
const destroyCollectionGroup = `${TopContext.api}/user/destroy_collection_group`;
const focusMeUser = `${TopContext.api}/user/{user_id}/focus_me_user`;

export default {
    login(query , data) {
        return Http.post(login, query , data);
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

    lessHistory (query) {
        return Http.get(`${TopContext.api}/less_history`, query);
    },

    history (query) {
        return Http.get( `${TopContext.api}/history`, query);
    },

    praiseHandle(query , data) {
        return Http.post(`${TopContext.api}/user/praise_handle`, query , data);
    },

    collectionHandle(query , data) {
        return Http.post( `${TopContext.api}/user/collection_handle` , query , data);
    },

    // 记录用户行为
    record (query , data) {
        return Http.post(`${TopContext.api}/user/record` , query , data);
    },

    createAndJoinCollectionGroup(query , data) {
        return Http.post(`${TopContext.api}/user/create_and_join_collection_group`, query , data);
    },

    joinCollectionGroup(data) {
        return request(joinCollectionGroup, 'post', data);
    },

    collectionGroup (query) {
        return Http.get(`${TopContext.api}/user/collection_group` , query);
    },

    collectionGroupWithJudge (query) {
        return Http.get(`${TopContext.api}/user/collection_group_with_judge`, query);
    },

    destroyAllCollectionGroup(query , data) {
        return Http.delete(`${TopContext.api}/user/destroy_all_collection_group`, query , data);
    },

    lessCollectionGroupWithCollection(query) {
        return Http.get(`${TopContext.api}/less_collection_group_with_collection` , query);
    },

    update (query , data) {
        return Http.put(`${TopContext.api}/user` , query , data);
    },

    updatePasswordInLogged (query , data) {
        return Http.patch(`${TopContext.api}/user/update_password_in_logged` , query , data);
    },

    destroyHistory (query , data) {
        return Http.delete(`${TopContext.api}/user/destroy_history`, query , data);
    },

    collections (query) {
        return Http.get(`${TopContext.api}/user/collections`, query);
    },

    destroyCollections(data) {
        return request(collections, 'delete', data);
    },

    createCollectionGroup (query , data) {
        return Http.post(`${TopContext.api}/user/create_collection_group` , query , data);
    },

    destroyCollection (query , data) {
        return Http.delete( `${TopContext.api}/user/destroy_collection` , query , data);
    },

    updateCollectionGroup (query , data) {
        return Http.patch(`${TopContext.api}/user/update_collection_group` , query , data);
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

    collectionGroupByUserId (id , query) {
        return Http.get(`${TopContext.api}/user/${id}/collection_group` , query);
    } ,

    localUpdate (query , data) {
        return Http.patch(`${TopContext.api}/user` , query , data);
    } ,

    collectionGroupInfo (collectionGroupId) {
        return Http.get(`${TopContext.api}/user/${collectionGroupId}/collection_group_info`);
    } ,

    myPraise (query) {
        return Http.get( `${TopContext.api}/my_praise`, query);
    } ,

    destroyMyPraise (query , data) {
        return Http.delete(`${TopContext.api}/user/destroy_my_praise`, query , data);
    } ,
};
