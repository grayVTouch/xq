
import store from '../vuex';

/**
 * ****************
 * 全局混入
 * ****************
 */
Vue.mixin({

    store ,

    data () {
        return {
            TopContext ,
            val: {
                pending: {} ,
                error: {} ,
                select: {} ,
            } ,
        };
    } ,

    methods: {

        isValid (val) {
            return G.isValid(val) && val !== '';
        } ,

        push (location , onComplete , onAbort) {
            if (G.isString(location)) {
                if (location === this.$route.path) {
                    return ;
                }
            } else {
                if (
                    (location.name && location.name === this.$route.name) ||
                    (location.path && location.path === this.$route.path)
                ) {
                    return ;
                }
            }
            this.$router.push.apply(this.$router , [location , onComplete , onAbort]);
        } ,

        state (key) {
            if (key) {
                return this.$store.state[key];
            }
            return this.$store.state;
        } ,

        message (action , message , option = {}) {
            return Prompt[action](message , {
                closeBtn: true ,
                ...option
            });
        } ,

        openWindow (url , type = '_blank') {
            window.open(url , type);
        } ,

        linkAndRefresh (url) {
            url = this.genUrl(url);
            this.openWindow(url , '_self');
            // window.history.go(0);
        } ,

        pending (key , val) {
            if (!G.isValid(val)) {
                return this.val.pending[key];
            }
            this.val.pending[key] = val;
            this.val.pending = {...this.val.pending , ...{[key]: val}};
        } ,

        error (error = {} , clear = true) {
            if (clear) {
                this.val.error = {...error};
            }
            this.val.error = {...this.val.error , ...error};
        } ,

        _val (key , val) {
            if (!G.isValid(val)) {
                return this.val[key];
            }
            this.val = {...this.val , ...{[key]: val}};
        } ,

        errorHandle (message , code = 400) {
            return new Promise((resolve , reject) => {
                if (code === TopContext.code.AuthFailed) {
                    this.message('error' , '您尚未登录，请登录后操作？' , {
                        // closeBtn: false ,
                        action: [
                            {
                                name: '确定' ,
                                callback () {
                                    this.hide();
                                    resolve(true);
                                } ,
                            } ,
                            {
                                name: '取消' ,
                                callback () {
                                    this.hide();
                                    reject();
                                } ,
                            } ,
                        ] ,
                    });
                    return ;
                }
                this.message('error' , message);
                resolve(false);
            });
        } ,

        // 错误处理
        errorHandleAtUserChildren (message , code , loggedCallback) {
            this.errorHandle(message , code)
                .then((keep) => {
                    if (!keep) {
                        return ;
                    }
                    this.$parent.$parent.showUserForm('login' , loggedCallback);
                });
        } ,

        errorHandleAtHomeChildren (message , code , loggedCallback) {
            this.errorHandle(message , code)
                .then((keep) => {
                    if (!keep) {
                        return ;
                    }
                    this.$parent.showUserForm('login' , loggedCallback);
                });
        } ,

        successHandle () {
            return new Promise((resolve) => {
                this.message('success' , '操作成功' , {
                    // 点击确认按钮回调
                    confirm () {
                        resolve(true);
                    } ,
                    // 点击取消按钮回调
                    cancel () {
                        resolve(false);
                    } ,
                });
            });
        } ,

        dispatch (action , payload) {
            this.$store.dispatch(action , payload);
        } ,

        handleImageProject (data) {
            data.user    = data.user ? data.user : {};
            data.image_subject = data.image_subject ? data.image_subject : {};
            data.images  = data.images ? data.images : [];
            data.tags    = data.tags ? data.tags : [];
            data.module  = data.module ? data.module : [];
        } ,

        key () {
            return G.randomArr('64' , 'mixed' , true);
        } ,

        getUsername (username , nickname) {
            return nickname ? nickname : username;
        } ,

        genUrl (route) {
            if (!route) {
                return '';
            }
            if (TopContext.routerMode === 'hash') {
                route = route.replace(/^(\/?#)?\/?/ , '');
                return '#/' + route;
            }
            return route;
        } ,

        reload () {
            window.history.go(0);
        } ,


        imageApi (resize = false) {
            return TopContext.uploadImageApi  + (resize ? '?w=' + TopContext.val.imageW : '');
        } ,

        thumbApi () {
            return TopContext.uploadImageApi + '?w=' + TopContext.val.thumbW;
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

        module () {
            return G.s.json('module');
        } ,

    } ,
});
