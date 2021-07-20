const form = {
    module_id: '' ,
    weight: 0 ,
    category_id: '' ,
    video_series_id: '' ,
    video_company_id: '' ,
    end_status: 'completed' ,
    tags: [] ,
    score: 0 ,
    count: 0 ,
    status: 1 ,
};

const owner = {id: 0 , username: 'unknow'};
const videoSeries = {id: 0 , name: 'unknow'};
const videoCompany = {id: 0 , name: 'unknow'};

export default {
    name: "index",

    props: {
        id: {
            type: Number ,
            default: 0 ,
        } ,
        mode: {
            type: String ,
            required: true
        } ,
    } ,

    data () {
        return {
            dom: {} ,
            ins: {} ,
            myValue: {
                pending: {} ,
                error: {} ,
                show: false ,
                showModuleSelector: false ,
                step: 'module' ,
            } ,

            // 发布时间
            releaseDate: '' ,

            // 完结时间
            endDate: '' ,

            // 视频系列
            videoSeries: G.copy(videoSeries) ,

            // 视频制作公司
            videoCompany: G.copy(videoCompany) ,

            // 用户
            owner: G.copy(owner) ,

            // 搜索条件
            search: {
                size: TopContext.size
            } ,

            // 模块
            modules: [] ,

            // 当前修改事物
            form: G.copy(form) ,

            // 标签
            tags: [] ,

            // 标签排行榜
            topTags: [] ,

            // 分类
            categories: [] ,
        };
    } ,

    mounted () {
        this.initDom();
        this.initIns();
    } ,

    computed: {
        title () {
            return this.mode === 'edit' ? '编辑' : '添加';
        } ,

    } ,

    methods: {

        getModules () {
            this.pending('getModules' , true);
            Api.module
                .all()
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandle(res.message);
                        return ;
                    }
                    this.modules = res.data;
                })
                .finally(() => {
                    this.pending('getModules' , false);
                });
        } ,

        getTopTags () {
            if (this.form.module_id <= 0) {
                return ;
            }
            this.pending('getTopTags' , true);
            Api.tag
                .top({
                    module_id: this.form.module_id ,
                    type: 'video_project' ,
                    size: 10 ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandle(res.message);
                        return ;
                    }
                    this.topTags = res.data;
                })
                .finally(() => {
                    this.pending('getTopTags' , false);
                });
        } ,

        refreshCategories () {
            if (this.form.module_id < 1) {
                this.errorHandle('请选择模块');
                return ;
            }
            this.getCategories();
        } ,

        getCategories (callback) {
            if (this.form.module_id < 1) {
                return ;
            }
            this.pending('getCategories' , true);
            Api.category
                .search({
                    module_id: this.form.module_id ,
                    type: 'video_project' ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandle(res.message);
                        G.invoke(callback , null , false);
                        return ;
                    }
                    this.categories = res.data;
                    this.$nextTick(() => {
                        G.invoke(callback , null , true);
                    });
                })
                .finally(() => {
                    this.pending('getCategories' , false);
                });
        } ,

        // 模块发生变化的时候
        moduleChanged () {
            this.myValue.error.module_id = '';
            this.form.video_series_id = '';
            this.form.video_company_id = '';
            this.form.video_series = G.copy(videoSeries);
            this.form.video_company = G.copy(videoCompany);
            this.topTags = [];
            this.getTopTags();
            this.getCategories();
        },

        initDom () {
            this.dom.tagInput = G(this.$refs['tag-input']);
            this.dom.tagInputOuter = G(this.$refs['tag-input-outer']);
            this.dom.thumb = G(this.$refs.thumb);
        } ,

        initIns () {
            var self = this;
            this.ins.thumb = new Uploader(this.dom.thumb.get(0) , {
                api: this.thumbApi() ,
                mode: 'override' ,
                clear: true ,
                uploaded (file , data , code) {
                    if (code !== TopContext.code.Success) {
                        this.status(file.id , false);
                        return ;
                    }
                    this.status(file.id , true);
                    self.form.thumb = data.data;
                } ,
                cleared () {
                    self.form.thumb = '';
                } ,
            });
        } ,

        filter (form) {
            const error = {};
            if (G.isEmptyString(form.name)) {
                error.name = '请填写名称';
            }
            if (!G.isNumeric(form.user_id)) {
                error.user_id = '请选择用户';
            }
            if (!G.isNumeric(form.module_id)) {
                error.module_id = '请选择模块';
            }
            if (!G.isNumeric(form.category_id)) {
                error.category_id = '请选择分类';
            }
            if (form.type === 'pro' && !G.isNumeric(form.image_subject_id)) {
                error.image_subject_id = '请选择图片主体';
            }
            return {
                status: G.isEmptyObject(error) ,
                error ,
            };
        } ,

        submitEvent () {
            const form = G.copy(this.form);
            form.images = G.jsonEncode(this.images);
            form.tags = this.tags.map((v) => {
                return v.id;
            });
            form.tags = G.jsonEncode(form.tags);
            const filterRes = this.filter(form);
            if (!filterRes.status) {
                this.error(filterRes.error , true);
                this.errorHandle(G.getObjectFirstKeyMappingValue(filterRes.error));
                return ;
            }
            form.release_date = form.release_data ? form.release_date : '';
            form.end_date = form.end_date ? form.end_date : '';
            const thenCallback = (res) => {
                this.error();
                if (res.code !== TopContext.code.Success) {
                    this.errorHandle(res.message);
                    return ;
                }
                this.successModal((keep) => {
                    this.$emit('on-success');
                    if (keep) {
                        return ;
                    }
                    this.closeFormModal();
                });
            };
            const finalCallback = () => {
                this.pending('submitEvent' , false);
            };
            this.pending('submitEvent' , true);
            if (this.mode === 'edit') {
                Api.videoProject.update(form.id , form).then(thenCallback).finally(finalCallback);
                return ;
            }
            Api.videoProject.store(form).then(thenCallback).finally(finalCallback);
        } ,

        // 获取当前编辑记录详情
        findById (id) {
            return new Promise((resolve , reject) => {
                this.pending('findById' , true);
                Api.videoProject
                    .show(id)
                    .then((res) => {
                        if (res.code !== TopContext.code.Success) {
                            this.errorHandle(res.message);
                            reject();
                            return ;
                        }
                        const data = res.data;
                        data.score = parseFloat(data.score);
                        this.form = data;
                        resolve();
                    })
                    .finally(() => {
                        this.pending('findById' , false);
                    });
            });
        } ,

        openFormModal () {
            this.getModules();
            if (this.mode === 'add') {
                // 添加
                this.handleModuleStep();
            } else {
                this.handleFormStep();
            }
        } ,

        // 模块处理
        handleModuleStep () {
            this.step = 'module';
            this.myValue.showModuleSelector = true;
        } ,

        // 表单处理
        handleFormStep () {
            this.step = 'form';
            this.myValue.show = true;
            if (this.mode === 'edit') {
                this.findById(this.id)
                    .then((res) => {
                        this.ins.thumb.render(this.form.thumb);
                        this.videoSeries    = this.form.video_series ? this.form.video_series : G.copy(videoSeries);
                        this.owner          = this.form.user ? this.form.user : G.copy(owner);
                        this.videoCompany   = this.form.video_company ? this.form.video_company : G.copy(videoCompany);
                        this.releaseDate            = this.form.release_date ? this.form.release_date : '';
                        this.endDate                = this.form.end_date ? this.form.end_date : '';
                        this.getTopTags();
                        this.getCategories();
                    });
            }
        } ,

        // 下一步
        nextStepAtForm () {
            if (this.form.module_id  < 1) {
                this.errorHandle('请选择模块');
                return ;
            }
            this.myValue.showModuleSelector = false;
            // 获取分类
            this.getCategories();
            // 获取标签
            this.getTopTags();
            // 表单处理
            this.handleFormStep();
        } ,

        closeFormModal () {
            if (this.pending('submitEvent')) {
                this.message('warning' , '请求中...请耐心等待');
                return;
            }

            this.myValue.step = 'module';
            this.myValue.showUserSelector = false;
            this.myValue.showModuleSelector = false;

            this.myValue.show = false;
            // 清除所有错误信息
            this.error();
            this.ins.thumb.clearAll();
            this.form           = G.copy(form);
            this.tags           = [];
            this.topTags        = [];
            this.categories     = [];
            this.videoCompany   = G.copy(videoCompany);
            this.videoSeries    = G.copy(videoSeries);
            this.owner          = G.copy(owner);
            this.releaseDate    = '';
            this.endDate        = '';
        } ,

        setReleaseDateEvent (date) {
            this.form.release_date = date;
        } ,

        setEndDateEvent (date) {
            this.form.end_date = date;
        } ,

        isExistTagByTagId (tagId) {
            for (let i = 0; i < this.form.tags.length; ++i)
            {
                const cur = this.form.tags[i];
                if (tagId === cur.tag_id) {
                    return true;
                }
            }
            for (let i = 0; i < this.tags.length; ++i)
            {
                const cur = this.tags[i];
                if (tagId === cur.id) {
                    return true;
                }
            }
            return false;
        } ,

        isExistTagByName (name) {
            const tags = this.form.tags.concat(this.tags);
            for (let i = 0; i < tags.length; ++i)
            {
                const cur = tags[i];
                if (name === cur.name) {
                    return true;
                }
            }
            return false;
        } ,

        appendTag (v) {
            if (this.isExistTagByTagId(v.id)) {
                this.message('error' , '标签已经存在');
                return ;
            }
            this.tags.push(v);
        } ,

        destroyTag (tagId , direct = true) {
            if (direct) {
                for (let i = 0; i < this.tags.length; ++i)
                {
                    const tag = this.tags[i];
                    if (tag.id === tagId) {
                        this.tags.splice(i , 1);
                        i--;
                    }
                }
                return ;
            }
            const pendingKey = 'destroy_tag_' + tagId;
            // 编辑模式
            this.pending(pendingKey , true);
            Api.videoProject
                .destroyTag({
                    video_project_id: this.form.id ,
                    tag_id: tagId ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.error({tags: res.data});
                        return ;
                    }
                    for (let i = 0; i < this.form.tags.length; ++i)
                    {
                        const tag = this.form.tags[i];
                        if (tag.tag_id === tagId) {
                            this.form.tags.splice(i , 1);
                            i--;
                        }
                    }
                })
                .finally(() => {
                    this.pending(pendingKey , false);
                });
        } ,

        createOrAppendTag () {
            this.myValue.error.tags = '';
            const name = this.dom.tagInput.text().replace(/\s/g , '');
            this.dom.tagInput.html(name);
            if (!G.isValid(name)) {
                this.message('error' , '请提供标签名称');
                return ;
            }
            if (this.form.user_id <= 0) {
                this.errorHandle('请先选择用户');
                return ;
            }
            if (this.isExistTagByName(name)) {
                this.message('error' , '标签已经存在');
                return ;
            }
            this.dom.tagInput.origin('blur');
            this.dom.tagInputOuter.addClass('disabled');
            Api.tag
                .findOrCreateTag({
                    name ,
                    module_id: this.form.module_id ,
                    type: 'video_project' ,
                    user_id: this.form.user_id ,
                })
                .then((res) => {
                    this.dom.tagInputOuter.removeClass('disabled');
                    if (res.code !== TopContext.code.Success) {
                        this.error({tags: res.message} , false);
                        return ;
                    }
                    this.tags.push(res.data);
                    this.dom.tagInput.html('');
                    this.dom.tagInput.origin('focus');
                })
                .finally(() => {

                });
        } ,

        userChangeEvent (res) {
            this.error({user_id: ''} , false);
            this.form.user_id   = res.id;
            this.owner          = res;
        } ,

        showUserSelector () {
            this.$refs['user-selector'].show();
        } ,

        videoSeriesChangeEvent (res) {
            this.error({video_series_id: ''} , false);
            this.form.video_series_id   = res.id;
            this.videoSeries            = res;
        } ,

        showVideoSeriesSelector () {
            if (!G.isNumeric(this.form.module_id)) {
                this.errorHandle('请先选择模块');
                return ;
            }
            this.$refs['video-series-selector'].show();
        } ,

        videoCompanyChangeEvent (res) {
            this.error({video_company_id: ''} , false);
            this.form.video_company_id   = res.id;
            this.videoCompany            = res;
        } ,

        showVideoCompanySelector () {
            if (!G.isNumeric(this.form.module_id)) {
                this.errorHandle('请先选择模块');
                return ;
            }
            this.$refs['video-company-selector'].show();
        } ,

    } ,

}
