import mixin from './mixin.js';

const collections = {
    page: 1 ,
    total: 0 ,
    size: TopContext.size ,
    sizes: TopContext.sizes ,
    data: [] ,
};

const collectionGroupForm = {
    name: '' ,
};

const updateCollectionGroupForm = {
    collection_group_id: 0 ,
    name: '' ,
};

export default {
    name: "favorites" ,

    data () {
        return {
            // 表单搜索
            search: {
                relation_type: '',
                value: '',
            },

            relationType: {
                image_project: '图片专题',
                // video_subject: '视图专题',
                // article_subject: '文章专题',
                // bbs_subject: '论坛帖子',
            },

            favorites: [],

            dom: {},
            val: {
                pending: {},
                fixed: false,
            },

            collections: G.copy(collections, true),
            currentCollectionGroup: {} ,
            collectionGroupForm: {...collectionGroupForm} ,
            updateCollectionGroupForm: {...updateCollectionGroupForm} ,
        };
    } ,

    mounted () {
        this.$emit('focus-menu' , 'favorites');
        this.initDom();
        this.initEvent();
        this.getCollectionGroup()
            .then(() => {
                if (this.favorites.length > 0) {
                    this.switchCollectionGroup(this.favorites[0]);
                }
            });
    } ,

    mixins: [
        mixin
    ] ,

    methods: {
        initDom () {
            this.dom.win = G(window);
            this.dom.filter = G(this.$refs.filter);
            this.dom.createForm = G(this.$refs['create-form']);
        } ,

        scrollEvent () {
            const scrollTop = this.dom.filter.getWindowOffsetVal('top');
            this.val.fixed = scrollTop < TopContext.val.fixedTop;
        } ,

        initEvent () {
            this.dom.win.on('scroll' , this.scrollEvent.bind(this));
        } ,

        getCollectionGroup (callback) {
            return new Promise((resolve) => {
                this.pending('getCollectionGroup' , true);
                Api.collectionGroup
                    .index()
                    .then((res) => {
                        this.pending('getCollectionGroup' , false);
                        if (res.code !== TopContext.code.Success) {
                            this.errorHandleAtUserChildren(res.message , res.code , () => {
                                this.getCollectionGroup();
                            });
                            reject();
                            return ;
                        }
                        this.favorites = res.data;
                        resolve();
                    })
                    .finally(() => {

                    });
            });
        } ,

        destroyCollectionGroup (collectionGroup , callback) {
            const pending = 'destroyCollectionGroup_' + collectionGroup.id;
            if (this.pending(pending)) {
                G.invoke(callback , null , false);
                return ;
            }
            this.pending(pending , true);
            Api.collectionGroup
                .destroyAll(null , {
                    ids: G.jsonEncode([collectionGroup.id])
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        G.invoke(callback , null , false);
                        this.pending(pending , false);
                        this.errorHandleAtUserChildren(res.message , res.code , () => {
                            this.destroyHistory(collectionGroup);
                        });
                        return ;
                    }
                    this.getCollectionGroup();
                    G.invoke(callback , null , true);
                })
                .finally(() => {

                });
        } ,

        destroyCollectionGroupEvent (collectionGroup) {
            this.hideCollectionGroupAction(collectionGroup);
            this.destroyCollectionGroup(collectionGroup , (keep) => {
                if (!keep) {
                    return ;
                }
                if (this.currentCollectionGroup.id === collectionGroup.id) {
                    this.collections = G.copy(collections);
                }
            });
        } ,

        // 获取指定收藏夹下收藏的内容
        getCollections (collectionGroupId) {
            this.pending('getCollections' , true);
            Api.collectionGroup
                .collections(collectionGroupId , {
                    size: this.collections.size ,
                    page: this.collections.page ,
                    ...this.search ,
                })
                .then((res) => {
                    this.pending('getCollections' , false);
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtUserChildren(res.message , res.code , () => {
                            this.getCollections(collectionGroupId);
                        });
                        return ;
                    }
                    const data = res.data;
                    data.data.forEach((v) => {
                        v.relation = v.relation ? v.relation : {};
                        v.relation.user = v.relation.user ? v.relation.user : {};
                        switch (v.relation_type)
                        {
                            case 'image_project':
                                break;
                            case 'image':
                                break;
                            case 'video':
                            case 'video_project':
                                v.relation.user_play_record = v.relation.user_play_record ? v.relation.user_play_record : {};
                                v.relation.user_play_record.video = v.relation.user_play_record.video ? v.relation.user_play_record.video : {};
                                break;
                            default:
                        }

                    });
                    this.collections.size = data.per_page;
                    this.collections.page = data.current_page;
                    this.collections.total = data.total;
                    this.collections.data = data.data;
                })
                .finally(() => {

                });
        } ,

        pageEvent (page , size) {
            this.collections.page = page;
            this.collections.size = size;
            this.getCollections();
        } ,

        sizeEvent (size , page) {
            this.collections.size = size;
            this.collections.page = page;
            this.getCollections(this.currentCollectionGroup.id);
        } ,

        switchCollectionGroup (collectionGroup) {
            this.currentCollectionGroup = collectionGroup;
            this.getCollections(collectionGroup.id);
        } ,

        showCollectionGroupAction (collectionGroup) {
            const actions = G(this.$refs['collection_group_actions_' + collectionGroup.id]);
            actions.removeClass('hide');
            actions.animate({
                opacity: 1 ,
                bottom: '0px' ,
            });
        } ,

        hideCollectionGroupAction (collectionGroup) {
            const actions = G(this.$refs['collection_group_actions_' + collectionGroup.id]);
            actions.animate({
                opacity: 0 ,
                bottom: '20px' ,
            } , () => {
                actions.addClass('hide');
            });
        } ,

        // 创建收藏夹
        createCollectionGroup () {
            if (this.pending('createCollectionGroup')) {
                return ;
            }
            this.pending('createCollectionGroup' , true);
            Api.collectionGroup
                .store(null , this.collectionGroupForm)
                .then((res) => {
                    this.pending('createCollectionGroup' , false);
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtUserChildren(res.message , res.code , () => {
                            this.createCollectionGroup();
                        });
                        return ;
                    }
                    this.getCollectionGroup();
                    this.hideCreateFavoritesForm();
                })
                .finally(() => {

                });
        } ,

        // 删除收藏的内容
        destroyCollection (collection) {
            const pendingKey = 'destroyCollection_' + collection.id;
            if (this.pending(pendingKey)) {
                return ;
            }
            this.pending(pendingKey , true);
            Api.collection
                .destroy(collection.id)
                .then((res) => {
                    this.pending(pendingKey , false);
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtUserChildren(res.message , res.code , () => {
                            this.createCollectionGroup();
                        });
                        return ;
                    }
                    this.getCollectionGroup();
                    this.getCollections(this.currentCollectionGroup.id);
                })
                .finally(() => {

                });
        } ,

        showCreateFavoritesForm () {
            this.dom.createForm.removeClass('hide');
            this.dom.createForm.startTransition('show');
        } ,

        hideCreateFavoritesForm () {
            this.collectionGroupForm = {...collectionGroupForm};
            this.dom.createForm.endTransition('show' , () => {
                this.dom.createForm.addClass('hide');
            });
        } ,

        showUpdateFavoritesForm (collectionGroup) {
            this.updateCollectionGroupForm.id = collectionGroup.id;
            this.updateCollectionGroupForm.name = collectionGroup.name;
            this.hideCollectionGroupAction(collectionGroup);
            const updateForm = G(this.$refs['update-form-' + collectionGroup.id]);
            updateForm.removeClass('hide');
            updateForm.startTransition('show');
        } ,

        hideUpdateFavoritesForm (collectionGroup) {
            this.updateCollectionGroupForm = {...updateCollectionGroupForm};
            const updateForm = G(this.$refs['update-form-' + collectionGroup.id]);
            updateForm.endTransition('show' , () => {
                updateForm.addClass('hide');
            });
        } ,

        updateCollectionGroup () {
            const pendingKey = 'updateCollectionGroup_' + this.updateCollectionGroupForm.id;
            if (this.pending(pendingKey)) {
                return ;
            }
            this.pending(pendingKey , true);
            Api.collectionGroup
                .update(this.updateCollectionGroupForm.id , null , this.updateCollectionGroupForm)
                .then((res) => {
                    this.pending(pendingKey , false);
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtUserChildren(res.message , res.code , () => {
                            this.createCollectionGroup();
                        });
                        return ;
                    }
                    const data = res.data;
                    this.hideUpdateFavoritesForm(data);
                    for (let i = 0; i < this.favorites.length; ++i)
                    {
                        const cur = this.favorites[i];
                        if (cur.id === data.id) {
                            this.favorites.splice(i , 1 , data);
                        }
                    }
                })
                .finally(() => {

                });
        } ,
    } ,
}
