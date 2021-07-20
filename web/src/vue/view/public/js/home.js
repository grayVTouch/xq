import positions from './position.js';
import positionMixin from './mixin/position.js';

const loginForm = {
    username: '' ,
    password: '' ,
};
const forgetForm = {
    email: '' ,
    email_code: '' ,
    password: '' ,
    confirm_password: '' ,
};

const registerForm = {
    email: '' ,
    nickname: '' ,
    password: '' ,
    confirm_password: '' ,
    email_code: '' ,
    captcha_code: '' ,
    captcha_key: '' ,
};

const loginError = {
    username: '' ,
    password: '' ,
};

const forgetError = {
    email: '' ,
    password: '' ,
    email_code: '' ,
    confirm_password: '' ,
    captcha_code: '' ,
    captcha_key: '' ,
};

const registerError = {
    username: '' ,
    password: '' ,
    confirm_password: '' ,
    captcha_code: '' ,
    captcha_key: '' ,
};

const loginMessage = {
    class: '' ,
    text: '' ,
};

const registerMessage = {...loginMessage};
const forgetMessage = {...loginMessage};

const userFormCallback = {
    login: [] ,
    register: [] ,
    forget: [] ,
};

const collectionGroup = {
    count: 0 ,
    collections: []

};

export default {
    name: "home" ,

    mixins: [
        positionMixin ,
    ] ,

    data () {
        return {
            val: {
                isGroupsBindAnimationendListener: false ,
                fixed: false,
                navTypeList: false,
                mime: {
                    key: 'image_project',
                    value: '图片专题',
                },
                toTop: false,

                // 用户登录、注册表单相关字段
                loginError: {...loginError},
                registerError: {...registerError},
                forgetError: {...forgetError},
                loginMessage: {...loginMessage},
                registerMessage: {...registerMessage},
                forgetMessage: {...forgetMessage},

                showPasswordForLogin: false,

                showPasswordForRegister: false,
                showConfirmPasswordForRegister: false,

                showPasswordForForget: false,
                showConfirmPasswordForForget: false,

                pending: {
                    userLogin: false,
                    userRegister: false,
                    updateUserPassword: false,
                    sendEmailCodeForPassword: false,
                    sendEmailCodeForRegister: false,
                },
                focus: {
                    usernameForLogin: false,
                    passwordForLogin: false,

                    emailForRegister: false,
                    passwordForRegister: false,
                    confirmPasswordForRegister: false,
                    captchaCodeForRegister: false,
                    emailCodeForRegister: false,

                    emailForForget: false,
                    passwordForForget: false,
                    confirmPasswordForForget: false,
                    emailCodeForForget: false,
                },
                captchaForRegister: {},
                /**
                 * login
                 * forget
                 * register
                 */
                userFormType: 'login',
                // 相关步骤
                step: {
                    // 1. email - 发送电子邮箱
                    // 2. password - 修改密码
                    password: 'email',
                },
                timer: {
                    password: 0,
                    register: 0,
                },
            },
            dom: {},
            ins: {},
            search: {
                /**
                 * image
                 * video
                 * article
                 */
                mime: 'image',
            },
            // 导航菜单
            positions ,
            // 是否缓存路由
            keepalive: true,
            count: 0,
            loginForm: {...loginForm},
            registerForm: {...registerForm},
            forgetForm: {...forgetForm},
            histories: [],
            favorites: {
                total_collection_group: 0,
                collectionGroups: [],
                collection_group: {...collectionGroup} ,
            },
            // 用户回调
            userFormCallback: {...userFormCallback},

            // 模块列表
            modules: [],
        };
    } ,

    // beforeRouteEnter (to , from , next) {
    //     if (from.path === '/welcome') {
    //         next(() => {
    //             window.history.go(0);
    //         });
    //         return ;
    //     }
    //     next();
    // } ,

    beforeRouteUpdate (to , from , next) {
        if (!G.isObject(this.ins.nav)) {
            next();
            return ;
        }
        const route = this.genUrl(to.path);
        this.initPositionByRouteAndPositions(route , this.positions);
        const position = this.state().position;
        const positionTree = this.getParentPositionsById(position.id , this.positions , true);
        this.dispatch('positions' , positionTree);
        // 菜单失去焦点
        this.ins.nav.blur();
        // 菜单获取焦点
        if (positionTree.length > 0) {
            const first = positionTree[0];
            const last  = positionTree[positionTree.length - 1];
            const ids   = first.route === last.route ?  [first.route] : [first.route , last.route];
            this.ins.nav.focusByIds(ids);
        }
        next();
    } ,

    mounted () {
        this.initDom();
        this.initEvent();
        this.initStyle();
        this.initToTop();

        // 数据获取
        this.getPositions();
        this.getModules();

        if (G.s.exists('token')) {
            // 用户如果已经登录，则获取用户信息
            this.userInfo();
        }
    } ,

    computed: {

    } ,

    methods: {

        getModules () {
            this.pending('getModules' , true);
            Api.module
                .all()
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandle(res.message , res.code);
                        return ;
                    }
                    this.modules = res.data;
                })
                .finally(() => {
                    this.pending('getModules' , false);
                });
        } ,

        switchModule (module) {
            this.hideListForModuleSwitch();
            G.s.json('module' , module);
            window.history.go(0);
        } ,

        userInfo () {
            return new Promise((resolve) => {
                this.pending('userInfo' , true);
                Api.user
                    .info()
                    .then((res) => {
                        if (res.code !== TopContext.code.Success) {
                            resolve(false);
                            if (res.code === TopContext.code.AuthFailed) {
                                return ;
                            }
                            this.errorHandle(res.message , res.code);
                            return ;
                        }
                        const user = res.data;
                        G.s.json('user' , user);
                        this.dispatch('user' , user);
                        resolve(true);
                    })
                    .finally(() => {
                        this.pending('userInfo' , false);
                    });
            });

        } ,

        /**
         * *****************
         * 搜索事件
         * *****************
         */
        searchEvent () {
            let url = '';
            switch (this.val.mime.key)
            {
                case 'image_project':
                    url = '/image_project/search';
                    break;
                case 'video_project':
                    url = '/video_project/search';
                    break;
                case 'image':
                    url = '/image/search';
                    break;
                case 'video':
                    url = '/video/search';
                    break;
            }
            // url = this.genUrl(url);
            console.log('url' , url);
            // debugger
            this.push(url);
        } ,

        initDom () {
            this.dom.win = G(window);
            this.dom.body = G(document.body);
            this.dom.root = G(this.$el);
            this.dom.navTypeList = G(this.$refs['nav-type-list']);
            this.dom.navMenu = G(this.$refs['nav-menu']);
            this.dom.body = G(this.$refs.body);
            this.dom.toTop = G(this.$refs['to-top']);
            this.dom.userForm = G(this.$refs.userForm);
            this.dom.listForModuleSwitch = G(this.$refs['list-for-module-switch']);
        } ,

        initNav () {
            const route = this.genUrl(this.$route.path);
            this.initPositionByRouteAndPositions(route , this.positions);

            const self      = this;
            const position  = this.state().position;
            const positionTree = this.getParentPositionsById(position.id , this.positions , true);
            this.dispatch('positions' , positionTree);

            const first     = positionTree[0];
            const last      = positionTree[positionTree.length - 1];
            const ids       = positionTree.length > 0 ?
                (
                    first.route === last.route ?
                    [first.route] :
                    [first.route , last.route]
                ) :
                [];
            this.ins.nav    = new Nav(this.dom.navMenu.get(0) , {
                // click (id) {
                //     self.openWindow(self.genUrl(id) , '_self');
                // } ,
                // 是否选中项
                focus: true ,
                // 是否选中顶级项
                topFocus: true ,
                // 初始选中的项
                ids ,
            });

            // 分类需要修改（区分不同的事物主体，而不再是一套分类适用于所有事物）
            // 比如，视频、图片、视频专题、图片专题等，他们不允许共用一套分类！
            // 需要为他们分别设计单独的分类体系
        } ,

        initStyle () {
            // this.dom.body.startTransition('show');
        } ,

        switchSearchType (key , value) {
            this.search.mime = key;
            this._val('mime' , {
                key ,
                value ,
            });
            this.hideNavTypeList();
        } ,

        getPositions () {
            this.pending('getPositions' , true);
            Api.home
                .nav()
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandle(res.message , res.code);
                        return ;
                    }
                    const data = res.data;
                    let appendImageProjectPosition = [];
                    let appendVideoProjectPosition = [];
                    let maxId = this.getMaxIdByPositions(this.positions);

                    // console.log('maxId' , maxId);

                    const imageProjectPosition = this.findByKeyAndPositions('image_project' , this.positions);
                    const videoProjectPosition = this.findByKeyAndPositions('video_project' , this.positions);
                    // id / p_id 转换成客户端体系
                    const mappings = [];
                    const findInMappingsByOldId = (oldId) => {
                        for (let i = 0; i < mappings.length; ++i)
                        {
                            const cur = mappings[i];
                            if (cur.oldId === oldId) {
                                return cur;
                            }
                        }
                        throw new Error('未找到 id = ' + oldId + '的映射项');
                    };

                    data.forEach((v) => {
                        const mapping = {
                            oldId: v.id ,
                            newId: ++maxId ,
                            oldParentId: v.p_id ,
                            newParentId: undefined ,
                        };
                        switch (v.type)
                        {
                            case 'image_project':
                                mapping.newParentId = v.p_id === 0 ? imageProjectPosition.id : findInMappingsByOldId(v.p_id).newId;
                                const r = {
                                    id: mapping.newId ,
                                    name: v.name ,
                                    parentId: mapping.newParentId ,
                                    route: '/image_project/search?category_id=' + v.value ,
                                    hidden: false ,
                                };
                                appendImageProjectPosition.push(r);
                                break;
                            case 'video_project':
                                mapping.newParentId = v.p_id === 0 ? videoProjectPosition.id : findInMappingsByOldId(v.p_id).newId;
                                appendVideoProjectPosition.push({
                                    id: mapping.newId ,
                                    name: v.name ,
                                    parentId: mapping.newParentId ,
                                    route: '/video_project/search?category_id=' + v.value ,
                                    hidden: false ,
                                });
                                break;
                            default:
                                throw new Error('不支持的动态菜单类型');
                        }
                        mappings.push(mapping);
                    });
                    const field = {id: 'id' , p_id: 'parentId'};

                    appendImageProjectPosition = G.tree.childrens(imageProjectPosition.id , appendImageProjectPosition , field , false , true);
                    appendVideoProjectPosition = G.tree.childrens(videoProjectPosition.id , appendVideoProjectPosition , field , false , true);

                    // 请使用下面这种方式来触发节点更新
                    appendImageProjectPosition.forEach((v) => {
                        imageProjectPosition.children.push(v);
                    });

                    appendVideoProjectPosition.forEach((v) => {
                        videoProjectPosition.children.push(v);
                    });

                    // 路由模式修正（根据路由模式自动修正）
                    G.tree.loop(this.positions , (v) => {
                        v.route = this.genUrl(v.route);
                    });

                    // 初始化获取获取当前路由所在具体位置
                    this.$nextTick(() => {
                        this.initNav();
                    });
                })
                .finally(() => {
                    this.pending('getPositions' , false);
                });

        } ,

        mimeTypeEvent () {
            if (this.val.navTypeList) {
                this.hideNavTypeList();
            } else {
                this.showNavTypeList();
            }
        } ,

        showNavTypeList () {
            this.val.navTypeList = true;
            this.dom.navTypeList.removeClass(['hide' , 'animate-out']);
            this.dom.navTypeList.addClass('animate-in');
        } ,

        hideNavTypeList () {
            this.val.navTypeList = false;
            const listener = 'animationend';
            this.dom.navTypeList
                .off(listener)
                .on(listener , () => {
                    if (!this.val.navTypeList) {
                        this.dom.navTypeList.addClass('hide');
                    }
                    this.dom.navTypeList.off(listener);
                    this.dom.navTypeList.removeClass('animate-out');
                });
            this.dom.navTypeList.removeClass('animate-in');
            this.dom.navTypeList.addClass('animate-out');


            // this.dom.navTypeList.endTransition('show');
            // this.val.navTypeListTimer = window.setTimeout(() => {
            //     this.dom.navTypeList.addClass('hide');
            // } , 300);
        } ,

        // 标题栏置顶
        fixedHeader () {
            const scrollTop = window.pageYOffset;
            this.val.fixed = scrollTop >= 60;
        } ,

        initToTop () {
            const y = window.pageYOffset;
            if (y === 0) {
                this.dom.toTop.endTransition('show' , () => {
                    this.dom.toTop.removeClass('hide');
                });
            } else {
                this.dom.toTop.removeClass('hide');
                this.dom.toTop.startTransition('show');
            }
        } ,

        toTopEvent () {
            G.scrollTo(300 , 'y' , 0 , 0);
        } ,

        initEvent () {
            // this.dom.win.on('click' , this.hideNavTypeList.bind(this));

            // this.dom.win.on('click' , this.hideHistoryCtrl.bind(this));
            // this.dom.win.on('click' , this.hideUserCtrl.bind(this));
            // this.dom.win.on('click' , this.hideFavoritesCtrl.bind(this));

            this.dom.root.on('scroll' , this.fixedHeader.bind(this));

            this.dom.win.on('scroll' , this.fixedHeader.bind(this));
            this.dom.toTop.on('click' , this.toTopEvent.bind(this));
            this.dom.win.on('scroll' , this.initToTop.bind(this));
        } ,

        showListForModuleSwitch () {
            this.dom.listForModuleSwitch.removeClass('hide');
            this.dom.listForModuleSwitch.startTransition('show');
        } ,

        hideListForModuleSwitch () {
            this.dom.listForModuleSwitch.addClass('hide');
            this.dom.listForModuleSwitch.removeClass('show');
        } ,

        // 获取历史记录
        getHistories () {
            this.pending('getHistories' , true);
            Api.user
                .lessHistory({
                    size: TopContext.size
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message , res.code);
                        return ;
                    }
                    const data = res.data;
                    data.forEach((v) => {
                        v.data = v.data ? v.data : [];
                        v.data.forEach((v1) => {
                            v1.relation = v1.relation ? v1.relation : {};
                            v1.relation.user = v1.relation.user ? v1.relation.user : {};

                            if (['video_project' , 'video'].includes(v1.relation_type)) {
                                v1.relation.user_play_record = v1.relation.user_play_record ? v1.relation.user_play_record : {};
                                v1.relation.user_play_record.video = v1.relation.user_play_record.video ? v1.relation.user_play_record.video : {};
                            }
                        });
                    });
                    this.histories = data;
                })
                .finally(() => {
                    this.pending('getHistories' , false);
                });
        } ,


        showHistoryCtrl () {
            this.hideFavoritesCtrl();
            this.hideUserCtrl();

            this.val.showHistoryCtrl = true;

            this.dom.groupsForHistory = G(this.$refs['groups-for-history']);
            this.dom.groupsForHistory.removeClass(['hide' , 'animate-out']);
            this.dom.groupsForHistory.addClass('animate-in');
            if (this.histories.length <= 0) {
                this.getHistories();
            }
        } ,

        hideHistoryCtrl () {
            this.val.showHistoryCtrl = false;
            this.dom.groupsForHistory = G(this.$refs['groups-for-history']);

            if (!this.val.isGroupsBindAnimationendListener) {
                this.val.isGroupsBindAnimationendListener = true;
                this.dom.groupsForHistory
                    .on('animationend' , () => {
                        if (!this.val.showHistoryCtrl) {
                            this.dom.groupsForHistory.addClass('hide');
                            this.dom.groupsForHistory.removeClass('animate-out');
                        }
                    });
            }

            this.dom.groupsForHistory = G(this.$refs['groups-for-history']);
            this.dom.groupsForHistory.removeClass('animate-in');
            this.dom.groupsForHistory.addClass('animate-out');
        } ,

        showUserCtrl () {
            this.hideFavoritesCtrl();
            this.hideHistoryCtrl();
            this.dom.infoForUser = G(this.$refs['info-for-user']);

            this.val.showUserCtrl = true;
            this.dom.infoForUser.removeClass(['hide' , 'animate-out']);
            this.dom.infoForUser.addClass('animate-in');

        } ,

        hideUserCtrl () {
            this.val.showUserCtrl = false;
            this.dom.infoForUser = G(this.$refs['info-for-user']);

            if (!this.val.isInfoBindAnimationendListener) {
                this.val.isInfoBindAnimationendListener = true;
                this.dom.infoForUser
                    .on('animationend' , () => {
                        if (!this.val.showUserCtrl) {
                            this.dom.infoForUser.addClass('hide');
                            this.dom.infoForUser.removeClass('animate-out');
                        }
                    });
            }

            this.dom.infoForUser.removeClass('animate-in');
            this.dom.infoForUser.addClass('animate-out');

        } ,

        // 获取收藏夹内容
        showFavoritesCtrl () {
            this.hideUserCtrl();
            this.hideHistoryCtrl();
            this.dom.collection = G(this.$refs['collection']);

            this.dom.collection.removeClass(['hide' , 'animate-out']);
            this.dom.collection.addClass('animate-in');

            this.val.showFavoritesCtrl = true;
            if (this.favorites.collectionGroups.length <= 0) {
                this.getCollectionGroupWithCollection();
            }
        } ,

        hideFavoritesCtrl () {
            this.val.showFavoritesCtrl = false;
            this.dom.collection = G(this.$refs['collection']);


            if (!this.val.isCollectionBindAnimationendListener) {
                this.val.isCollectionBindAnimationendListener = true;
                this.dom.collection
                    .on('animationend' , () => {
                        if (!this.val.showFavoritesCtrl) {
                            this.dom.collection.addClass('hide');
                            this.dom.collection.removeClass('animate-out');
                        }
                    });
            }

            this.dom.collection = G(this.$refs['collection']);
            this.dom.collection.removeClass('animate-in');
            this.dom.collection.addClass('animate-out');
        } ,

        getCollectionGroupWithCollection () {
            this.pending('getCollectionGroupWithCollection' , true);
            Api.user
                .lessCollectionGroupWithCollection({
                    // size: TopContext.size
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message , res.code);
                        return ;
                    }
                    const data = res.data;
                    data.collection_groups = data.collection_groups ? data.collection_groups : [];
                    data.collection_groups.forEach((v) => {

                        v.collections = v.collections ? v.collections : [];
                        v.collections.forEach((v1) => {
                            v1.relation = v1.relation ? v1.relation : {};
                            v1.relation.user = v1.relation.user ? v1.relation.user : {};

                            if (['video_project' , 'video'].includes(v1.relation_type)) {
                                v1.relation.user_play_record = v1.relation.user_play_record ? v1.relation.user_play_record : {};
                                v1.relation.user_play_record.video = v1.relation.user_play_record.video ? v1.relation.user_play_record.video : {};
                            }
                        });
                    });

                    this.favorites.total_collection_group = data.total_collection_group;
                    this.favorites.collectionGroups = data.collection_groups;
                    this.favorites.collection_group = data.collection_groups.length > 0 ? {...data.collection_groups[0]} : {...collectionGroup};
                })
                .finally(() => {
                    this.pending('getCollectionGroupWithCollection' , false);
                });
        } ,

        logout () {
            G.s.del('token');
            G.s.del('user');
            window.history.go(0);
        } ,

        showUserForm (type , callback) {
            this._val('userFormType' ,type);
            switch (type)
            {
                case 'login':
                    break;
                case 'register':
                    this.captchaForRegister();
                    break;
                case 'forget':
                    break;
                default:
                    this.message('不支持的类型');
            }
            this.dom.userForm.removeClass('hide');
            this.dom.userForm.startTransition('show');
            if (G.isFunction(callback)) {
                this.userFormCallback[type].push(callback);
            }
        } ,

        switchUserForm (type) {
            this._val('userFormType' ,type);
            switch (type)
            {
                case 'login':
                    break;
                case 'register':
                    this.captchaForRegister();
                    break;
                case 'forget':
                    break;
                default:
                    this.message('不支持的类型');
            }
        } ,

        hideUserForm () {
            if (this.pending('userLogin') || this.pending('userRegister') || this.pending('updateUserPassword')) {
                this.message('info' , '服务正在处理...请耐心等待');
                return ;
            }
            this.dom.userForm.endTransition('show' , () => {
                this.dom.userForm.addClass('hide');
            });
            this.userFormCallback = {...userFormCallback};
            this.val.step.password = 'email';
            this.resetForgetMessage();
            this.resetForgetForm();
            this.resetRegisterForm();
            this.resetRegisterMessage();
            this.resetRegisterError();
            this.resetLoginForm();
            this.resetLoginError();
            this.resetLoginMessage();
        } ,

        loginMessage (text = '' , classname = '') {
            this.val.loginMessage.text = text;
            this.val.loginMessage.class = classname;
        } ,

        registerMessage (text = '' , classname = '') {
            this.val.registerMessage.text = text;
            this.val.registerMessage.class = classname;
        } ,

        forgetMessage (text = '' , classname = '') {
            this.val.forgetMessage.text = text;
            this.val.forgetMessage.class = classname;
        } ,

        captchaForRegister () {
            Api.misc.captcha((msg , data , code) => {
                if (res.code !== TopContext.code.Success) {
                    this.val.registerError.captcha_code = data;
                    return ;
                }
                this.val.captchaForRegister = data;
                this.registerForm.captcha_key = data.key;
            })
        } ,

        focusEvent (e) {
            const tar = G(e.currentTarget);
            const name = tar.data('name');
            this.val.focus[name] = true;
        } ,

        blurEvent (e) {
            const tar = G(e.currentTarget);
            const name = tar.data('name');
            this.val.focus[name] = false;
        } ,

        resetLoginForm () {
            this.loginForm = {...loginForm};
        } ,

        resetForgetForm () {
            this.forgetForm = {...forgetForm};
        } ,

        resetRegisterForm () {
            this.registerForm = {...registerForm};
        } ,



        resetLoginError () {
            this.val.loginError = {...loginError};
        } ,

        resetForgetError () {
            this.val.forgetError = {...forgetError};
        } ,

        resetRegisterError () {
            this.val.registerError = {...registerError};
        } ,

        resetLoginMessage () {
            this.val.loginMessage = {...loginMessage};
        } ,

        resetRegisterMessage () {
            this.val.registerMessage = {...registerMessage};
        } ,

        resetForgetMessage () {
            this.val.forgetMessage = {...forgetMessage};
        } ,

        userLoginFilter () {
            const error = {};
            if (G.isEmptyString(this.loginForm.username)) {
                error.username = '请填写用户名';
            } else {
                if (this.loginForm.username.length < 6) {
                    error.username = '用户名的最小长度为 6 字符';
                }
            }
            if (G.isEmptyString(this.loginForm.password)) {
                error.password = '请填写密码';
            } else {
                if (this.loginForm.password.length < 6) {
                    error.password = '密码的最小长度为 6 字符';
                }
            }
            return {
                status: G.isEmptyObject(error) ,
                error ,
            };
        } ,

        forgetFilter () {
            const error = {};
            if (G.isEmptyString(this.forgetForm.email)) {
                error.email = '请填写邮箱';
            }
            return {
                status: G.isEmptyObject(error) ,
                error ,
            };
        } ,

        userRegisterFilter () {
            const error = {};
            if (G.isEmptyString(this.registerForm.email)) {
                error.email = '请填写邮箱';
            }
            if (G.isEmptyString(this.registerForm.password)) {
                error.password = '请填写密码';
            } else {
                if (this.registerForm.password.length < 6) {
                    error.password = '密码的最小长度为 6 字符';
                }
            }
            if (G.isEmptyString(this.registerForm.confirm_password)) {
                error.confirm_password = '请填写确认密码';
            } else {
                if (this.registerForm.confirm_password.length < 6) {
                    error.confirm_password = '密码的最小长度为 6 字符';
                }
            }
            if (G.isEmptyString(this.registerForm.email_code)) {
                error.email_code = '请填写验证码';
            }
            return {
                status: G.isEmptyObject(error) ,
                error ,
            };
        } ,

        userPasswordFilter () {
            const error = {};
            if (G.isEmptyString(this.forgetForm.email)) {
                error.email = '请填写邮箱';
            }
            if (G.isEmptyString(this.forgetForm.password)) {
                error.password = '请填写密码';
            } else {
                if (this.forgetForm.password.length < 6) {
                    error.password = '密码的最小长度为 6 字符';
                }
            }
            if (G.isEmptyString(this.forgetForm.confirm_password)) {
                error.confirm_password = '请填写确认密码';
            } else {
                if (this.forgetForm.confirm_password.length < 6) {
                    error.confirm_password = '密码的最小长度为 6 字符';
                }
            }
            if (G.isEmptyString(this.forgetForm.email_code)) {
                error.email_code = '请填写验证码';
            }
            return {
                status: G.isEmptyObject(error) ,
                error ,
            };
        } ,

        // 用户登录
        userLogin () {
            if (this.pending('userLogin')) {
                this.message('info' , '请求中...请耐心等待');
                return ;
            }
            const filterRes = this.userLoginFilter();
            if (!filterRes.status) {
                this.val.loginError = filterRes.error;
                return ;
            }
            this.resetLoginError();
            this.pending('userLogin' , true);
            Api.user
                .login(null , this.loginForm)
                .then((res) => {
                    this.resetLoginMessage();
                    if (res.code !== TopContext.code.Success) {
                        this.pending('userLogin' , false);
                        this.errorHandle(res.message);
                        return ;
                    }
                    const data = res.data;
                    G.s.set('token' , data);
                    // console.log(G.jsonEncode(this.userFormCallback.login) , this.userFormCallback.login.length);
                    if (this.userFormCallback.login.length < 1) {
                        this.loginMessage('登录成功，页面刷新中...' , 'run-green');
                        window.setTimeout(this.reload.bind(this) , 1000);
                        return ;
                    }
                    this.loginMessage('登录成功，获取用户信息中...' , 'run-green');
                    // 获取用户信息
                    this.userInfo()
                        .then((keep) => {
                            this.pending('userLogin' , false);
                            if (!keep) {
                                this.loginMessage('获取用户信息失败，请稍后重试' , 'run-red');
                                return ;
                            }
                            while (this.userFormCallback.login.length > 0)
                            {
                                const callback = this.userFormCallback.login.pop();
                                G.invoke(callback);
                            }
                            this.hideUserForm();
                        })
                        .finally(() => {
                            this.resetLoginForm();
                            this.resetLoginMessage();
                        });
                })
                .finally(() => {

                });
        } ,

        userRegister () {
            if (this.pending('userRegister')) {
                this.message('info' , '请求中...请耐心等待');
                return ;
            }
            const filterRes = this.userRegisterFilter();
            if (!filterRes.status) {
                this.val.registerError = filterRes.error;
                return ;
            }
            this.resetRegisterError();
            this.pending('userRegister' , true);
            Api.user
                .register(null , this.registerForm)
                .then((res) => {
                    this.resetRegisterMessage();
                    if (res.code !== TopContext.code.Success) {
                        this.pending('userRegister' , false);
                        this.captchaForRegister();
                        this.errorHandle(res.message);
                        return ;
                    }
                    const data = res.data;
                    this.registerMessage('注册成功，获取用户信息中...' , 'run-green');
                    G.s.set('token' , data);
                    // 获取用户信息
                    this.userInfo()
                        .then((keep) => {
                            this.pending('userRegister' , false);
                            if (!keep) {
                                this.registerMessage('获取用户信息失败，请稍后重试' , 'run-red');
                                return ;
                            }
                            this.hideUserForm();
                        })
                        .finally(() => {
                            this.resetRegisterForm();
                            this.resetRegisterMessage();
                        });
                })
                .finally(() => {

                });
        } ,

        sendEmailCodeForPassword () {
            if (this.val.timer.password > 0) {
                return ;
            }
            const filterRes = this.forgetFilter();
            if (!filterRes.status) {
                this.val.forgetError = filterRes.error;
                return ;
            }
            // 发送验证码
            this.pending('sendEmailCodeForPassword' , true);
            Api.misc
                .sendEmailCodeForPassword(null , this.forgetForm)
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandle(res.message);
                        return ;
                    }
                    // 密码
                    this.val.step.password = 'password';
                    this.forgetMessage('邮箱验证码发送成功' , 'run-green');
                    window.setTimeout(() => {
                        this.resetForgetMessage();
                    } , 3000);
                    G.timeCount(60 , 1 , (v) => {
                        this.val.timer.password = v;
                    });
                })
                .finally(() => {
                    this.pending('sendEmailCodeForPassword' , false);
                });
        } ,

        sendEmailCodeForRegister () {
            if (this.val.timer.register > 0) {
                return ;
            }
            // 发送验证码
            this.pending('sendEmailCodeForRegister' , true);
            Api.misc
                .sendEmailCodeForRegister(null , this.registerForm)
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandle(res.message);
                        return ;
                    }
                    // 密码
                    this.forgetMessage('邮箱验证码发送成功' , 'run-green');
                    window.setTimeout(() => {
                        this.resetRegisterMessage();
                    } , 3000);
                    G.timeCount(60 , 1 , (v) => {
                        this.val.timer.register = v;
                    });
                })
                .finally(() => {
                    this.pending('sendEmailCodeForRegister' , false);
                });
        } ,

        updateUserPassword () {
            if (this.pending('updateUserPassword')) {
                this.message('info' , '请求中...请耐心等待');
                return ;
            }
            const filterRes = this.userPasswordFilter();
            if (!filterRes.status) {
                this.val.forgetError = filterRes.error;
                return ;
            }
            this.resetForgetError();
            this.pending('updateUserPassword' , true);
            Api.user
                .updatePassword(null , this.forgetForm)
                .then((res) => {
                    this.resetForgetMessage();
                    if (res.code !== TopContext.code.Success) {
                        this.pending('updateUserPassword' , false);
                        this.errorHandle(res.message);
                        return ;
                    }
                    G.s.del('token');
                    G.s.del('user');
                    this.dispatch('user' , null);
                    this.forgetMessage('修改成功！' , 'run-green');
                    window.setTimeout(() => {
                        this.val.password = 'email';
                        this.pending('updateUserPassword' , false);
                        this.resetForgetMessage();
                        this.resetForgetForm();
                        this.showUserForm('login');
                    } , 1000);
                })
                .finally(() => {

                });
        } ,
    } ,
}
