export default {
    name: "show" ,
    props: ['id'] ,
    data () {
        return {
            data: {
                user: {} ,
                module: {} ,
                image_subject: {} ,
                images: [] ,
                tags: [] ,
            } ,
            images: {
                data: [] ,
                size: 5 ,
            } ,

            dom: {} ,
            ins: {} ,
            val: {
                fixed: false ,
            } ,
            // 收藏夹列表
            favorites: [] ,

            // 收藏夹表单
            collectionGroup: {
                relation_type: 'image_project' ,
                relation_id: this.id ,
                name: '' ,
            } ,

            // 推荐数据
            recommend: {
                size: 5 ,
                data: [] ,
                type: 'pro' ,
            } ,

            newest: {
                size: 5 ,
                data: [] ,
                type: 'pro' ,
            } ,
        };
    } ,

    created () {

    } ,

    beforeRouteUpdate (to , from , next) {
        this.reload();
    } ,

    mounted () {
        this.initDom();
        this.initEvent();
        this.getData();
        this.incrementViewCount();
        this.record();
        this.getNewestData();
        this.getRecommendData();
    } ,

    methods: {

        // 图片点赞
        praiseHandle () {
            if (this.pending('praiseHandle')) {
                return ;
            }
            const self = this;
            const praised = this.data.is_praised ? 0 : 1;
            this.pending('praiseHandle' , true);
            Api.praise
                .praiseHandle(null , {
                    relation_type: 'image_project' ,
                    relation_id: this.data.id ,
                    action: praised ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message , res.code , () => {
                            this.praiseHandle();
                        });
                        return ;
                    }
                    this.data.is_praised = praised;
                    praised ? this.data.praise_count++ : this.data.praise_count--;
                })
                .finally(() => {
                    this.pending('praiseHandle' , false);
                });
        } ,

        showFavorites () {
            this.$refs['my-collection-group'].show();
        } ,

        collectionHandle (action) {
            action = Number(action);
            action === 1 ? this.data.collect_count++ : this.data.collect_count--;
            this.data.is_collected = this.data.collect_count > 0;
        } ,

        getData () {
            this.pending('getData' , true);
            Api.imageProject
                .show(this.id)
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message , res.code);
                        return ;
                    }
                    const data = res.data;
                    this.handleData(data);
                    this.data = data;
                    // 加载首页图片
                    this.images.data = data.images.slice(0 , this.images.size);
                    this.$nextTick(() => {
                        this.initPicPreview();
                    });
                })
                .finally(() => {
                    this.pending('getData' , false);
                });
        } ,

        handleData (data) {
            data.user    = data.user ? data.user : {};
            data.image_subject = data.image_subject ? data.image_subject : {};
            data.images  = data.images ? data.images : [];
            data.tags    = data.tags ? data.tags : [];
            data.module  = data.module ? data.module : [];
        } ,

        incrementViewCount () {
            this.pending('incrementViewCount' , true);
            Api.imageProject
                .incrementViewCount(this.id)
                .finally(() => {
                    this.pending('incrementViewCount' , false);
                });
        } ,

        record () {
            this.pending('record' , true);
            Api.history
                .store(null , {
                    relation_type: 'image_project' ,
                    relation_id: this.id ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        console.warn(res.message);
                        return ;
                    }
                })
                .finally(() => {
                    this.pending('record' , false);
                });
        } ,

        initPicPreview () {
            const images = this.data.images.map((v) => {
                return {
                    src: v.src ,
                    originalSrc: v.original_src ,
                };
            });
            this.ins.picPreviewAsync = new PicPreview_Async(this.dom.picPreviewAsyncContainer.get(0) , {
                index: 1 ,
                images ,
            });
        },

        imageClick (index) {
            this.ins.picPreviewAsync.show(parseInt(index));
        } ,

        initDom () {
            this.dom.win = G(window);
            this.dom.html = G(document.documentElement);
            this.dom.images = G(this.$refs.images);
            this.dom.picPreviewAsyncContainer = G(this.$refs['pic-preview'].$el);
            this.dom.myFavorites = G(this.$refs['my-favorites']);
            this.dom.misc = G(this.$refs['misc']);
            this.dom.newest = G(this.$refs.newest);
        },

        // 图片分段加载
        scrollLoadImageEvent (e) {
            // const y = window.pageYOffset;
            const scrollTop = this.dom.images.getWindowOffsetVal('top');
            const imagesH = this.dom.images.height();
            const clientH = this.dom.html.clientHeight();
            const extraH = clientH - TopContext.val.footerH;

            if (Math.abs(scrollTop) + extraH < imagesH) {
                return ;
            }
            const imagesDataLen = this.images.data.length;
            const dataImagesLen = this.data.images.length;
            if (imagesDataLen >= dataImagesLen) {
                return ;
            }
            this.images.data = this.images.data.concat(this.data.images.slice(imagesDataLen , imagesDataLen + this.images.size));
        } ,

        scrollWithMiscEvent () {
            const scrollH = document.documentElement.scrollHeight;
            const clientH = document.documentElement.clientHeight;
            const scrollTop = document.documentElement.scrollTop;
            const fixedTopJudgeH = this.dom.newest.getDocOffsetVal('top');
            const fixedBtmJudgeH = scrollH - TopContext.val.footerH;
            const fixedTopScrollH = scrollTop + TopContext.val.fixedTop;
            const fixedBtmScrollH = clientH + scrollTop;

            if (fixedBtmScrollH >= fixedBtmJudgeH) {
                this._val('fixedTop' , false);
                this._val('fixedBtm' , true);
            } else if (fixedTopScrollH >= fixedTopJudgeH) {
                this._val('fixedTop' , true);
                this._val('fixedBtm' , false);
            } else {
                this._val('fixedTop' , false);
                this._val('fixedBtm' , false);
            }
        } ,

        initEvent () {
            this.dom.win.on('scroll' , this.scrollLoadImageEvent.bind(this));
            this.dom.win.on('scroll' , this.scrollWithMiscEvent.bind(this));
        } ,

        // 获取推荐数据
        getNewestData (){
            this.pending('getNewestData' , true);
            Api.imageProject
                .newest({
                    size: this.newest.size ,
                    type: this.newest.type ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message);
                        return ;
                    }
                    this.newest.data = res.data;
                })
                .finally(() => {
                    this.pending('getNewestData' , false);
                });
        } ,

        // 获取推荐数据
        getRecommendData (){
            this.pending('getRecommendData' , true);
            Api.imageProject
                .recommend(this.id , {
                    size: this.recommend.size ,
                    type: this.recommend.type ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.message('error' , res.message);
                        return ;
                    }
                    this.recommend.data = res.data;
                })
                .finally(() => {
                    this.pending('getRecommendData' , false);
                });
        } ,

        linkToImageProject (imageSubject) {
            const link = this.genUrl(`/image_project/${imageSubject.id}/show`);
            this.openWindow(link , '_self');
            this.reload();
        } ,

        focusHandle () {
            if (this.pending('focusHandle')) {
                return ;
            }
            this.pending('focusHandle' , true);
            const action = this.data.user.focused ? 0 : 1;
            Api.user
                .focusHandle(null , {
                    user_id: this.data.user_id ,
                    action ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message , res.code , () => {
                            this.focusHandle();
                        });
                        return ;
                    }
                    // this.getData();

                    this.data.user.focused = action;
                    if (action) {
                        this.data.user.focus_me_user_count++;
                    } else {
                        this.data.user.focus_me_user_count--;
                    }
                })
                .finally(() => {
                    this.pending('focusHandle' , false);
                });

        } ,
    } ,
}
