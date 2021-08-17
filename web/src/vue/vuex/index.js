/**
 * @author running
 */

export default new Vuex.Store({
    state: {
        // 当前登录用户
        user: {} ,

        settings: {
            friend_links: [] ,
        } ,

        context: TopContext ,

        business: TopContext.business ,

        position: {} ,

        // 当前路径
        positions: [] ,

        // 用户登录后需要处理的相关回调函数
        loggedCallback: [] ,

    } ,
    mutations: {
        user (state , payload) {
            state.user = payload;
        } ,

        settings (state , payload) {
            state.settings = payload;
        } ,

        position (state , payload) {
            state.position = payload;
        } ,

        positions (state , payload) {
            state.positions = payload;
        } ,
    } ,
    actions: {
        user (context , payload) {
            context.commit('user' , payload);
        } ,

        settings (context , payload) {
            context.commit('settings' , payload);
        } ,

        position (state , payload) {
            state.commit('position' , payload);
        } ,

        positions (state , payload) {
            state.commit('positions' , payload);
        } ,
    } ,
});
