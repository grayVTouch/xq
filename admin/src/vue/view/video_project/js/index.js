import myForm from '../form.vue';

const current = {id: 0};

const search = {
    size: TopContext.size,
    video_series_id: '',
    video_company_id: '',
    module_id: '',
};

const myVideoCompany = {
    id: 0 ,
    name: 'unknow' ,
};

const myVideoSeries = {
    id: 0 ,
    name: 'unknow' ,
};

export default {
    name: "index",

    components: {
        'my-form': myForm ,
    } ,

    data () {
        return {
            filter: {
                id: '' ,
            } ,
            dom: {} ,
            ins: {} ,
            myValue: {
                pending: {} ,
                error: {} ,
                mode: '' ,
            } ,
            table: {
                field: [
                    {
                        type: 'selection',
                        width: TopContext.table.checkbox ,
                        align: TopContext.table.alignCenter ,
                        fixed: 'left' ,
                    },
                    {
                        title: 'id' ,
                        key: 'id' ,
                        width: TopContext.table.id ,
                        align: TopContext.table.alignCenter ,
                        fixed: 'left' ,
                        sortable: 'custom' ,
                    } ,
                    {
                        title: '名称' ,
                        slot: 'name' ,
                        minWidth: TopContext.table.name ,
                        align: TopContext.table.alignCenter ,
                        fixed: 'left' ,
                    } ,
                    {
                        title: '封面' ,
                        slot: 'thumb' ,
                        minWidth: TopContext.table.image ,
                        align: TopContext.table.alignCenter ,
                        fixed: 'left' ,
                    } ,
                    {
                        title: '评分' ,
                        key: 'score' ,
                        minWidth: TopContext.table.number ,
                        align: TopContext.table.alignCenter
                    } ,
                    {
                        title: '用户',
                        slot: 'user_id',
                        minWidth: TopContext.table.name ,
                        align: TopContext.table.alignCenter,
                    },
                    {
                        title: '模块【id】',
                        slot: 'module_id',
                        minWidth: TopContext.table.name ,
                        align: TopContext.table.alignCenter,
                    },
                    {
                        title: '所属分类' ,
                        slot: 'category_id' ,
                        minWidth: TopContext.table.name ,
                        align: TopContext.table.alignCenter ,
                    } ,
                    {
                        title: '视频系列' ,
                        slot: 'video_series_id' ,
                        minWidth: TopContext.table.name ,
                        align: TopContext.table.alignCenter ,
                    } ,
                    {
                        title: '视频制作公司' ,
                        slot: 'video_company_id' ,
                        minWidth: TopContext.table.name ,
                        align: TopContext.table.alignCenter ,
                    } ,
                    {
                        title: '视频数',
                        key: 'count',
                        minWidth: TopContext.table.number ,
                        align: TopContext.table.alignCenter,
                    },
                    {
                        title: '视频开始索引',
                        key: 'min_index',
                        minWidth: TopContext.table.number ,
                        align: TopContext.table.alignCenter,
                    },
                    {
                        title: '视频结束索引',
                        key: 'max_index',
                        minWidth: TopContext.table.number ,
                        align: TopContext.table.alignCenter,
                    },
                    {
                        title: '播放数',
                        key: 'play_count',
                        minWidth: TopContext.table.number ,
                        align: TopContext.table.alignCenter,
                    },
                    {
                        title: '标签' ,
                        slot: 'tags' ,
                        minWidth: TopContext.table.name ,
                        align: TopContext.table.alignCenter ,
                    } ,
                    {
                        title: '描述' ,
                        key: 'description' ,
                        minWidth: TopContext.table.desc ,
                        align: TopContext.table.alignCenter ,
                    } ,
                    {
                        title: '完结状态' ,
                        slot: 'end_status' ,
                        minWidth: TopContext.table.status ,
                        align: TopContext.table.alignCenter ,
                        fixed: 'right' ,
                    } ,
                    {
                        title: '审核状态',
                        slot: 'status',
                        minWidth: TopContext.table.status ,
                        align: TopContext.table.alignCenter,
                        fixed: 'right' ,
                    },
                    {
                        title: '文件处理状态',
                        slot: 'file_process_status',
                        minWidth: TopContext.table.status + 50 ,
                        align: TopContext.table.alignCenter,
                        fixed: 'right' ,
                    },
                    {
                        title: '失败原因',
                        key: 'fail_reason',
                        minWidth: TopContext.table.name ,
                        align: TopContext.table.alignCenter,
                    },
                    {
                        title: '权重' ,
                        key: 'weight' ,
                        minWidth: TopContext.table.weight ,
                        align: TopContext.table.alignCenter ,
                    } ,
                    {
                        title: '发布年份' ,
                        key: 'release_year' ,
                        minWidth: TopContext.table.time ,
                        align: TopContext.table.alignCenter
                    } ,
                    {
                        title: '发布日期' ,
                        key: 'release_date' ,
                        minWidth: TopContext.table.time ,
                        align: TopContext.table.alignCenter
                    } ,
                    {
                        title: '完结日期' ,
                        key: 'end_date' ,
                        minWidth: TopContext.table.time ,
                        align: TopContext.table.alignCenter
                    } ,
                    {
                        title: '创建时间' ,
                        key: 'created_at' ,
                        minWidth: TopContext.table.time ,
                        align: TopContext.table.alignCenter ,
                    } ,
                    {
                        title: '操作' ,
                        slot: 'action' ,
                        minWidth: TopContext.table.action - 100 ,
                        align: TopContext.table.alignCenter ,
                        fixed: 'right' ,
                    } ,
                ] ,
                total: 0 ,
                page: 1 ,
                sizes: TopContext.sizes ,
                size: 0 ,
                data: [] ,
            } ,

            // 视频系列
            videoSeries: [] ,

            // 视频公司
            videoCompany: [] ,

            // 搜索
            search: G.copy(search) ,

            // 模块
            modules: [] ,

            // 当前项
            current: G.copy(current) ,

            selection: [] ,

            myVideoSeries: G.copy(myVideoSeries) ,

            myVideoCompany: G.copy(myVideoCompany) ,

            // 分类
            categories: [] ,
        };
    } ,

    mounted () {
        this.initDom();
        this.initIns();
        this.getModules()
            .then(() => {
                this.search.module_id = this.moduleId;
                this.getData();
                this.getCategories();
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
            this.search.category_id = '';
            this.categories         = [];
            if (!G.isNumeric(this.search.module_id)) {
                return ;
            }
            this.pending('getCategories' , true);
            Api.category
                .search({
                    module_id: this.search.module_id ,
                    type: 'video_project' ,
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


        initDom () {
        } ,



        initIns () {

        } ,

        getData () {
            this.selection = [];
            this.pending('getData' , true);
            Api.videoProject
                .index(this.search)
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandle(res.message);
                        return ;
                    }
                    const data = res.data;
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
                Api.videoProject
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
                    })
                    .finally(() => {

                    });
            });
        } ,

        destroyEvent (index , record) {
            const pendingKey = 'delete_' + record.id;
            this.pending(pendingKey , true);
            this.destroy(record.id , () => {
                this.pending(pendingKey , false);

            });
        } ,

        destroyAllEvent () {
            this.pending('destroyAll' , true);
            const ids = this.selection.map((v) => {
                return v.id;
            });
            this.destroyAll(ids , (success) => {
                this.pending('destroyAll' , false);
            });
        } ,

        selectionChangeEvent (selection) {
            this.selection = selection;
        } ,

        searchEvent () {
            this.search.page = 1;
            this.getData();
        } ,

        resetEvent () {
            this.search = G.copy(search);
            this.search.module_id = this.moduleId;

            this.myVideoSeries = G.copy(myVideoSeries);
            this.myVideoCompany = G.copy(myVideoCompany);
            this.getData();
            this.getCategories();
        } ,

        pageEvent (page , size) {
            this.search.page = page;
            this.search.size = size;
            this.getData();
        } ,

        sizeEvent (size , page) {
            this.search.size = size;
            this.search.page = page;
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

        openVideoSeriesSelector () {
            if (this.search.module_id <= 0) {
                this.errorHandle('请先选择模块');
                return ;
            }
            this.$refs['video-series-selector'].show();
        } ,

        openVideoCompanySelector () {
            if (this.search.module_id <= 0) {
                this.errorHandle('请先选择模块');
                return ;
            }
            this.$refs['video-company-selector'].show();
        } ,

        videoSeriesChangedEvent (row) {
            this.myVideoSeries = G.copy(row);
            this.search.video_series_id = row.id;
            this.searchEvent();
        } ,

        videoCompanyChangedEvent (row) {
            this.myVideoCompany = G.copy(row);
            this.search.video_company_id = row.id;
            this.searchEvent();
        } ,

        linkToShowAtWeb (row) {
            const settings = this.state().settings;
            let url = settings.web_url + settings.show_for_video_project_at_web;
            url = url.replace('{id}' , row.id);
            this.openWindow(url , '_blank');
        } ,

        updateFileProcessStatusEvent (value) {
            if (!this.hasSelection()) {
                this.errorHandle('请选择处理项');
                return ;
            }
            this.confirmModal('这将直接更改记录状态（将影响处理中的任务）！请确认是否操作？' , (keep) => {
                if (!keep) {
                    return ;
                }
                this.pending('updateFileProcessStatusEvent' , true);
                const ids = this.selection.map((v) => {
                    return v.id;
                });
                Api.videoProject
                    .updateFileProcessStatus(null , {
                        ids: G.jsonEncode(ids) ,
                        status: value
                    })
                    .then((res) => {
                        if (res.code !== TopContext.code.Success) {
                            this.errorHandle(res.message);
                            return ;
                        }
                        this.successHandle('操作成功');
                        this.getData();
                    })
                    .finally(() => {
                        this.pending('updateFileProcessStatusEvent' , false);
                    });
            });
        } ,
    } ,
}
