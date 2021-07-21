import Form from '../form.vue';

const current = {id: 0};

const search = {
    size: TopContext.size ,
    category_id: '' ,
    image_subject_id: '' ,
};

const imageSubject = {
    id: 0 ,
    name: 'unknow' ,
};

const myUser = {
    id: 0 ,
    name: 'unknow' ,
};

export default {
    name: "index",

    components: {
        'my-form': Form ,
    } ,

    data () {
        return {
            filter: {
                id: '',
            },
            dom: {},
            ins: {},
            myValue: {
                pending: {},
                error: {},
                // edit-编辑 add-添加
                mode: '',
                selectedIds: [],
                // 抽屉
                drawer: false,
                showImagePreview: false,
            },
            table: {
                field: [
                    {
                        type: 'selection',
                        width: TopContext.table.checkbox,
                        align: TopContext.table.alignCenter,
                        fixed: 'left',
                    },
                    {
                        title: 'id',
                        key: 'id',
                        minWidth: TopContext.table.id,
                        align: TopContext.table.alignCenter,
                        fixed: 'left',
                        sortable: 'custom',
                    },
                    {
                        title: '名称',
                        key: 'name',
                        width: TopContext.table.name,
                        align: TopContext.table.alignCenter,
                        fixed: 'left',
                        resizable: true,
                        ellipsis: true,
                        // tooltip: true ,
                    },
                    {
                        title: '封面',
                        slot: 'thumb',
                        minWidth: TopContext.table.image,
                        align: TopContext.table.alignCenter,
                        fixed: 'left',
                    },
                    {
                        title: '用户【id】',
                        slot: 'user_id',
                        minWidth: TopContext.table.name,
                        align: TopContext.table.alignCenter
                    },
                    {
                        title: '模块【id】',
                        slot: 'module_id',
                        minWidth: TopContext.table.name,
                        align: TopContext.table.alignCenter
                    },
                    {
                        title: '分类【id】',
                        slot: 'category_id',
                        minWidth: TopContext.table.name,
                        align: TopContext.table.alignCenter
                    },
                    {
                        title: '类型',
                        key: '__type__',
                        minWidth: TopContext.table.type,
                        align: TopContext.table.alignCenter
                    },
                    {
                        title: '关联主体【id】',
                        slot: 'image_subject_id',
                        minWidth: TopContext.table.name,
                        align: TopContext.table.alignCenter
                    },
                    // {
                    //     title: '图片列表' ,
                    //     slot: 'images' ,
                    //     minWidth: TopContext.table.name ,
                    //     align: TopContext.table.alignCenter ,
                    //     fixed: 'right' ,
                    // } ,
                    {
                        title: '标签',
                        slot: 'tags',
                        minWidth: TopContext.table.name,
                        align: TopContext.table.alignCenter
                    },
                    {
                        title: '处理状态',
                        key: '__process_status__',
                        // slot: 'status' ,
                        minWidth: TopContext.table.status,
                        align: TopContext.table.alignCenter,
                        fixed: 'right',
                    },
                    {
                        title: '审核状态',
                        key: '__status__',
                        slot: 'status',
                        minWidth: TopContext.table.status,
                        align: TopContext.table.alignCenter,
                        fixed: 'right',
                    },
                    {
                        title: '失败原因',
                        key: 'fail_reason',
                        minWidth: TopContext.table.desc,
                        align: TopContext.table.alignCenter
                    },

                    {
                        title: '浏览次数',
                        key: 'view_count',
                        minWidth: TopContext.table.number,
                        align: TopContext.table.alignCenter
                    },
                    {
                        title: '获赞次数',
                        key: 'praise_count',
                        minWidth: TopContext.table.number,
                        align: TopContext.table.alignCenter
                    },

                    {
                        title: '描述',
                        key: 'description',
                        minWidth: TopContext.table.desc,
                        align: TopContext.table.alignCenter,
                        resizable: true,
                        ellipsis: true,
                        tooltip: true,
                    },

                    {
                        title: '权重',
                        key: 'weight',
                        minWidth: TopContext.table.weight,
                        align: TopContext.table.alignCenter,
                    },
                    {
                        title: '创建时间',
                        key: 'created_at',
                        minWidth: TopContext.table.time,
                        align: TopContext.table.alignCenter,
                    },
                    {
                        title: '操作',
                        slot: 'action',
                        minWidth: TopContext.table.action - 40,
                        align: TopContext.table.alignCenter,
                        fixed: 'right',
                    },
                ],
                total: 0,
                page: 1,
                data: [],
            },

            search: G.copy(search),

            modules: [],

            categories: [],

            form: {},

            current: G.copy(current),

            selection: [],

            imageSubject: G.copy(imageSubject),

            myUser: G.copy(myUser),
        };
    } ,

    mounted () {
        this.initDom();
        this.initIns();
        this.getModules()
            .then(() => {
                this.search.module_id = this.moduleId;
                this.getCategories();
                this.getData();
            });
    } ,

    computed: {
        showBatchBtn () {
            return this.selection.length > 0;
        } ,

        moduleId () {
            return this.modules.length > 0 ? this.modules[0].id : '';
        } ,
    } ,

    methods: {

        initDom () {

        } ,



        initIns () {

        } ,

        // 显示图片资源浏览器
        showImagePreview (row) {
            this.current = row;
            this.$nextTick(() => {
                this.myValue.showImagePreview = true;
            });
        } ,

        getModules () {
            return new Promise((resolve , reject) => {
                this.pending('getModules' , true);
                Api.module.all()
                    .then((res) => {
                        if (res.code !== TopContext.code.Success) {
                            this.errorHandle(res.message);
                            reject();
                            return ;
                        }
                        this.modules = res.data;
                        resolve();
                    })
                    .finally(() => {
                        this.pending('getModules' , false);
                    });
            });
        } ,

        getCategories () {
            this.categories = [];
            this.search.category_id = '';
            if (!G.isNumeric(this.search.module_id)) {
                return ;
            }
            this.pending('getCategories' , true);
            Api.category
                .search({
                    module_id: this.search.module_id ,
                    type: G.isEmptyString(this.search.type) ? 'image,image_project' : (this.search.type === 'pro' ? 'image_project' : 'image') ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandle(res.message);
                        return ;
                    }
                    this.categories = res.data;
                })
                .finally(() => {
                    this.pending('getCategories' , false);
                });
        } ,

        typeChangedEvent () {
            this.getCategories();
        } ,

        getData () {
            this.pending('getData' , true);
            Api.imageProject
                .index(this.search)
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandle(res.message);
                        return ;
                    }
                    const data = res.data;
                    data.data.forEach((v) => {
                        this.pending(`delete_${v.id}` , false);

                        v.images = v.images ? v.images : [];
                        v.tags = v.tags ? v.tags : [];
                    });
                    this.table.total = data.total;
                    this.table.size = data.per_page;
                    this.table.page = data.current_page;
                    this.table.data = data.data;
                })
                .finally(() => {
                    this.pending('getData' , false);
                });
        } ,

        destroy (id , callback) {
            this.destroyAll([id] , callback);
        } ,

        destroyAll (ids , callback) {
            if (ids.length < 1) {
                this.message('warning' ,'请选择待删除的项');
                G.invoke(callback , this , false);
                return ;
            }
            const self = this;
            this.confirmModal('你确定删除吗？'  , (res) => {
                if (!res) {
                    G.invoke(callback , this , false);
                    return ;
                }
                Api.imageProject
                    .destroyAll(ids)
                    .then((res) => {
                        if (res.code !== TopContext.code.Success) {
                            G.invoke(callback , this , false);
                            this.errorHandle(res.message);
                            return ;
                        }
                        G.invoke(callback , this , true);
                        this.message('success' , '操作成功');
                        this.getData();
                    });
            });
        } ,

        selectionChangeEvent (selection) {
            this.selection = selection;
        } ,

        destroyEvent (index , record) {
            const pendingKey = 'delete_' + record.id;
            this.pending(pendingKey , true);
            this.destroy(record.id , () => {
                this.pending(pendingKey , false);

            });
        } ,

        destroyAllEvent () {
            console.log('destroyAll');
            this.pending('destroyAll' , true);
            const ids = this.selection.map((v) => {
                return v.id;
            });
            this.destroyAll(ids , (success) => {
                console.log('destroyAll 111');
                this.pending('destroyAll' , false);
                if (success) {
                    this.selection = [];
                }
            });
        } ,

        searchEvent () {
            this.search.page = 1;
            this.getData();
        } ,

        resetEvent () {
            this.search = G.copy(search);
            this.search.module_id = this.moduleId;

            this.imageSubject = G.copy(imageSubject);
            this.myUser = G.copy(myUser);

            this.getData();
            this.getCategories();
        } ,

        pageEvent (page , size) {
            this.search.page = page;
            this.search.size = size;
            this.getData();
        } ,

        sizeEvent (size , page) {
            this.search.page = page;
            this.search.size = size;
            this.getData();
        } ,

        sortChangeEvent (data) {
            if (data.order === TopContext.sort.none) {
                this.search.order = '';
            } else {
                this.search.order = this.generateOrderString(data.key , data.order);
            }
            this.table.page = 1;
            this.getData();
        } ,

        isOnlyOneSelection () {
            return this.selection.length === 1;
        } ,

        isEmptySelection () {
            return this.selection.length === 0;
        } ,

        hasSelection () {
            return this.selection.length > 0;
        } ,

        getFirstSelection () {
            return this.selection[0];
        } ,

        checkOneSelection () {
            if (!this.hasSelection()) {
                this.errorHandle('请选择项');
                return false;
            }
            if (!this.isOnlyOneSelection()) {
                this.errorHandle('请仅选择一项');
                return false;
            }
            return true;
        } ,

        edit (record) {
            this.current = record;
            this.setValue('mode' , 'edit');
            this.$nextTick(() => {
                this.$refs.form.openFormModal();
            });
        } ,

        editEvent (record) {
            this.edit(record);
        } ,

        editEventByButton () {
            if (!this.checkOneSelection()) {
                return ;
            }
            const current = this.getFirstSelection();
            this.edit(current);
        } ,

        addEvent () {
            this.setValue('mode' , 'add');
            this.$nextTick(() => {
                this.$refs.form.openFormModal();
            });
        } ,

        rowClickEvent (row , index) {
            this.$refs.table.toggleSelect(index);
        } ,

        rowDblclickEvent (row , index) {
            this.editEvent(row);
        } ,

        linkToShowForImageProjectAtWeb (row) {
            const settings = this.state().settings;
            let url = settings.web_url + settings.show_for_image_project_at_web;
            url = url.replace('{id}' , row.id);
            this.openWindow(url , '_blank');
        } ,

        showImageSubjectSelector() {
            if (this.search.module_id < 1) {
                this.errorHandle('请选择模块');
                return ;
            }
            this.$refs['image-subject-selector'].show();
        } ,

        imageSubjectChangedEvent (row) {
            this.imageSubject = G.copy(row);
            this.search.image_subject_id = row.id;
            this.searchEvent();
        } ,

        showUserSelector() {
            this.$refs['user-selector'].show();
        } ,

        userChangedEvent (row) {
            this.myUser = {
                id: row.id ,
                name: this.getUsername(row.username , row.nickname) ,
            };
            this.search.user_id = row.id;
            this.searchEvent();
        } ,

        // 批量：事件：重新处理视频
        retryProcessEvent () {
            const ids = this.selection.map((v) => {
                return v.id;
            });
            this.retryProcess(ids);
        } ,

        // 批量：重新处理视频
        retryProcess (ids , callback) {
            this.pending('retryProcess' , true);
            Api.imageProject
                .retryProcess(ids)
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        G.invoke(callback , null , false);
                        this.errorHandle(res.message);
                        return ;
                    }
                    this.getData();
                    G.invoke(callback , null , true);
                })
                .finally(() => {
                    this.pending('retryProcess' , false);
                });
        } ,
    } ,
}
