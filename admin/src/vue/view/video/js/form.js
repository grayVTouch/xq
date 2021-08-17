const form = {
    type: '' ,
    user_id: '' ,
    module_id: '' ,
    category_id: '' ,
    video_project_id: '' ,
    view_count: 0  ,
    play_count: 0  ,
    praise_count: 0 ,
    against_count: 0 ,
    weight: 0 ,
    status: 1 ,
    merge_video_subtitle: 0 ,
    index: 1 ,
    tags: [] ,
};

// 所属用户
const owner         = {id: 0 , username: 'unknow'};
// 所属专题
const videoProject  = {id: 0 , name: 'unknow'};
const videoSubject  = {id: 0 , name: 'unknow'};

// 字幕
// const subtitle = {
//     id: 1 ,
//     name: '' ,
//     src: '' ,
//     delete_1: false ,
// };

const videos = {
    field: [
        {
            type: 'selection' ,
            width: TopContext.table.checkbox ,
            center: TopContext.table.alignCenter ,
            fixed: 'left' ,
        } ,
        {
            title: 'id' ,
            key: 'id' ,
            width: TopContext.table.id ,
            center: TopContext.table.alignCenter ,
            fixed: 'left' ,
        } ,
        {
            title: '清晰度' ,
            key: 'definition' ,
            minWidth: TopContext.table.name ,
            center: TopContext.table.alignCenter ,
        } ,
        {
            title: '视频' ,
            slot: 'src' ,
            minWidth: TopContext.table.video ,
        } ,
        {
            title: '操作' ,
            slot: 'action' ,
            minWidth: TopContext.table.action ,
            fixed: 'right' ,
        } ,
    ] ,
    data: [] ,
};

const videoSubtitles = {
    field: [
        {
            type: 'selection' ,
            width: TopContext.table.checkbox ,
            center: TopContext.table.alignCenter ,
            fixed: 'left' ,
        } ,
        {
            title: 'id' ,
            key: 'id' ,
            width: TopContext.table.id ,
            center: TopContext.table.alignCenter ,
            fixed: 'left' ,
        } ,
        {
            title: '名称' ,
            key: 'name' ,
            minWidth: TopContext.table.name ,
            center: TopContext.table.alignCenter ,
        } ,
        {
            title: '字幕源' ,
            slot: 'src' ,
            minWidth: TopContext.table.src ,
        } ,
        {
            title: '操作' ,
            slot: 'action' ,
            minWidth: TopContext.table.action ,
            fixed: 'right' ,
        } ,
    ] ,
    data: [] ,
};

export default {

    computed: {
        title () {
            return this.mode === 'edit' ? '编辑' : '添加';
        } ,
    } ,

    data () {
        return {
            myValue: {
                tab: 'base' ,
                pending: {} ,
                error: {} ,
                show: false ,
                showModuleSelector: false ,
                showTypeSelector: false ,
            } ,

            owner: G.copy(owner) ,

            // 关联主体
            videoProject: G.copy(videoProject , true),

            videoSubject: G.copy(videoSubject , true),

            // 标签
            tags: [] ,

            // 标签
            dom: {} ,

            // 实例
            ins: {} ,

            uVideoSubtitles: [] ,

            videos: G.copy(videos) ,

            videoSubtitles: G.copy(videoSubtitles) ,

            images: [] ,

            categories: [] ,

            modules: [] ,

            createTime: '' ,

            form: G.copy(form) ,

            videoSelection: [] ,

            videoSubtitleSelection: [] ,

            topTags: [] ,
        };
    } ,
    props: {
        id: {
            type: Number ,
            default: 0 ,
        } ,
        mode: {
            type: String ,
            default: '' ,
        } ,
    } ,

    mounted () {
        this.initDom();
        this.initIns();
    } ,

    methods: {

        genVideoSubtitle () {
            const id = G.randomArray(6 , 'letter' , true);
            return {
                id ,
                name: '' ,
                src: '' ,
                delete: false ,
                error: '' ,
                uploaded: false ,
                uploading: false ,
            };
        } ,

        getCategories (callback) {
            if (this.form.module_id <= 0) {
                return ;
            }
            if (G.isEmptyString(this.form.type)) {
                return ;
            }
            this.pending('getCategories' , true);
            Api.category
                .search({
                    module_id: this.form.module_id ,
                    type: this.form.type === 'pro' ? 'video_project' : 'video' ,
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

        getModules (callback) {
            this.pending('getModules' , true);
            Api.module
                .all()
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandle(res.message);
                        G.invoke(callback , null , false);
                        return ;
                    }
                    this.modules = res.data;
                    this.$nextTick(() => {
                        G.invoke(callback , null , true);
                    });
                })
                .finally(() => {
                    this.pending('getModules' , false);
                });
        } ,


        moduleChangedEvent () {
            this.error({module_id: ''} , false);
            this.form.category_id = '';
            this.form.video_project_id = '';
            this.videoProject = G.copy(videoProject);
            if (this.form.type === 'misc') {
                this.getCategories();
                // 获取标签
                this.getTopTags();
            }
        } ,

        typeChangedEvent (type) {
            if (type === 'misc') {
                // 杂项
                this.videoProject  = G.copy(videoProject);
                this.form.video_project_id  = '';
                this.getTopTags();
            } else {
                // 专题
                this.form.category_id = 0;
            }
        } ,

        initDom () {
            this.dom.tagInput = G(this.$refs['tag-input']);
            this.dom.tagInputOuter = G(this.$refs['tag-input-outer']);
            this.dom.thumb = G(this.$refs.thumb);
            this.dom.video = G(this.$refs.video);
        } ,

        initIns () {
            const self = this;
            this.ins.thumb = new Uploader(this.dom.thumb.get(0) , {
                api: this.thumbApi(),
                mode: 'override' ,
                clear: true ,
                mimeLimit: 'image' ,
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

            this.ins.video = new Uploader(this.dom.video.get(0) , {
                api: this.videoApi() ,
                mode: 'override' ,
                multiple: false ,
                clear: true ,
                mimeLimit: 'video' ,
                // 单位：MB
                blockSize: 10 ,
                // 启用分块上传
                isEnabledBlock: true ,
                // 检查单个块上传是否成功
                isBlockUploadOk (res , code) {
                    return code === TopContext.code.Success;
                } ,
                uploaded (file , data , code) {
                    self.form.src = data.data;
                } ,
                cleared () {
                    self.form.src = '';
                } ,
            });
        } ,

        // 获取当前编辑记录详情
        findById (id) {
            this.pending('findById' , true);
            return new Promise((resolve , reject) => {
                Api.video
                    .show(id)
                    .then((res) => {
                        if (res.code !== TopContext.code.Success) {
                            this.errorHandle(res.message);
                            reject();
                            return ;
                        }
                        this.form = res.data;
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
                this.handleStep('module');
            } else {
                this.handleForm();
            }
        } ,

        // 表单处理
        handleForm () {
            this.myValue.show = true;
            if (this.mode === 'edit') {
                this.findById(this.id)
                    .then(() => {
                        // 做一些额外处理
                        this.ins.thumb.render(this.form.__thumb__);

                        this.owner                  = this.form.user ? this.form.user : G.copy(owner);
                        this.videoProject           = this.form.video_project ? this.form.video_project : G.copy(videoProject);
                        this.videoSubject           = this.form.video_subject ? this.form.video_subject : G.copy(videoSubject);
                        this.videos.data            = this.form.videos;
                        this.videoSubtitles.data    = this.form.video_subtitles;

                        if (this.form.type === 'misc') {
                            this.getCategories();
                            this.getTopTags();
                        }
                    });
            } else {
                if (this.form.type === 'misc') {
                    this.getCategories();
                    this.getTopTags();
                }
            }
        } ,

        handleStep (step) {
            if (step === 'module') {
                this.myValue.showModuleSelector = true;
            } else if (step === 'type') {
                if (this.form.module_id  < 1) {
                    this.errorHandle('请选择模块');
                    return ;
                }
                this.myValue.showModuleSelector = false;
                this.myValue.showTypeSelector = true;
            } else if (step === 'form') {
                if (this.form.type  === '') {
                    this.errorHandle('请选择类型');
                    return ;
                }
                this.myValue.showTypeSelector = false;
                this.handleForm();
            } else {
                // 其他相关操作
            }
        } ,

        closeFormModal () {
            if (this.pending('submitEvent')) {
                this.message('warning' , '请求中...请耐心等待');
                return ;
            }
            this.myValue.step = 'module';
            this.myValue.showModuleSelector = false;

            this.myValue.show = false;
            this.myValue.tab = 'base';

            this.ins.thumb.clearAll();
            this.ins.video.clearAll();

            this.images             = [];
            this.tags               = [];
            this.topTags            = [];
            this.categories         = [];
            this.uVideoSubtitles    = [];
            this.owner              = G.copy(owner);
            this.videoProject       = G.copy(videoProject);
            this.videos             = G.copy(videos);
            this.videoSubtitles     = G.copy(videoSubtitles);
            this.videoSubject     = G.copy(videoSubject);
            this.form               = G.copy(form);
            this.error();
        } ,


        videoSelectionChangeEvent (selection) {
            this.videoSelection = selection;
        } ,

        destroyVideoEvent (index , record) {
            const pendingKey = 'delete_' + record.id;
            this.pending(pendingKey , true);
            this.destroyVideo(record.id , () => {
                this.pending(pendingKey , false);

            });
        } ,

        destroyVideosEvent () {
            this.pending('destroyVideos' , true);
            const ids = this.videoSelection.map((v) => {
                return v.id;
            });
            this.destroyVideos(ids , (success) => {
                this.pending('destroyVideos' , false);
            });
        } ,

        destroyVideo (id , callback) {
            this.destroyVideos([id] , callback);
        } ,

        destroyVideos (ids , callback) {
            if (ids.length < 1) {
                this.message('warning' ,'请选择待删除的项');
                G.invoke(callback , this , false);
                return ;
            }
            const self = this;
            this.confirmModal('你确定删除吗？'  , (res) => {
                if (res) {
                    Api.video
                        .destroyVideos(ids)
                        .then((res) => {
                            if (res.code !== TopContext.code.Success) {
                                G.invoke(callback , this , false);
                                this.errorHandle(res.message);
                                return ;
                            }
                            G.invoke(callback , this , true);
                            this.message('success' , '操作成功');
                            for (let i = 0; i < this.videos.data.length; ++i)
                            {
                                const cur = this.videos.data[i];
                                if (G.contain(cur.id , ids)) {
                                    this.videos.data.splice(i , 1);
                                    i--;
                                }
                            }
                        })
                        .finally(() => {

                        });
                    return ;
                }
                G.invoke(callback , this , false);
            });
        } ,


        videoSubtitleSelectionChangeEvent (selection) {
            this.videoSubtitleSelection = selection;
        } ,

        destroyVideoSubtitle (id , callback) {
            this.destroyVideoSubtitles([id] , callback);
        } ,

        destroyVideoSubtitles (ids , callback) {
            if (ids.length < 1) {
                this.message('warning' ,'请选择待删除的项');
                G.invoke(callback , this , false);
                return ;
            }
            const self = this;
            this.confirmModal('你确定删除吗？'  , (res) => {
                if (res) {
                    Api.videoSubtitle
                        .destroyAll(ids)
                        .then((res) => {
                            if (res.code !== TopContext.code.Success) {
                                G.invoke(callback , this , false);
                                this.errorHandle(res.message);
                                return ;
                            }
                            G.invoke(callback , this , true);
                            this.message('success' , '操作成功');
                            for (let i = 0; i < this.videoSubtitles.data.length; ++i)
                            {
                                const cur = this.videoSubtitles.data[i];
                                if (G.contain(cur.id , ids)) {
                                    this.videoSubtitles.data.splice(i , 1);
                                    i--;
                                }
                            }
                        });
                    return ;
                }
                G.invoke(callback , this , false);
            });
        } ,

        destroyVideoSubtitleEvent (index , record) {
            const pendingKey = 'delete_' + record.id;
            this.pending(pendingKey , true);
            this.destroyVideoSubtitle(record.id , () => {
                this.pending(pendingKey , false);
            });
        } ,

        destroyVideoSubtitlesEvent () {
            this.pending('destroyVideoSubtitles' , true);
            const ids = this.videoSubtitleSelection.map((v) => {
                return v.id;
            });
            this.destroyVideoSubtitles(ids , () => {
                this.pending('destroyVideoSubtitles' , false);
            });
        } ,

        addVideoSubtitleEvent () {
            if (this.form.merge_video_subtitle) {
                if (this.uVideoSubtitles.length >= 1) {
                    this.errorHandle('合并字幕只能合成单个字幕');
                    return ;
                }
            }
            const videoSubtitle = this.genVideoSubtitle();
            this.uVideoSubtitles.push(videoSubtitle);
        } ,

        videoSubtitleChangeEvent (e , record) {
            const tar   = e.currentTarget;
            const files = tar.files;
            if (files.length < 1) {
                return ;
            }
            record.error = '';
            record.file = files[0];
            this.uploadVideoSubtitle(record , () => {
                record.uploading = false;
            });
        } ,

        uploadVideoSubtitle (videoSubtitle , callback) {
            if (videoSubtitle.uploaded) {
                // 过滤掉那些上传成功的数据
                return ;
            }
            videoSubtitle.uploaded = false;
            videoSubtitle.uploading = true;
            videoSubtitle.error = '';
            Api.file
                .uploadSubtitle(videoSubtitle.file)
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        videoSubtitle.error = res.message;
                        videoSubtitle.uploaded = false;
                        G.invoke(callback , null , false);
                        return ;
                    }
                    videoSubtitle.uploaded = true;
                    videoSubtitle.src = res.data;
                    G.invoke(callback , null , true);
                })
                .finally(() => {

                });
        } ,

        filter (form) {
            const error = {};
            // if (G.isEmptyString(form.name)) {
            //     error.name = '请填写名称';
            // }
            if (!G.isNumeric(form.user_id)) {
                error.user_id = '请选择用户';
            }
            if (!G.isNumeric(form.module_id)) {
                error.module_id = '请选择模块';
            }
            if (form.type === 'pro') {
                if (!G.isNumeric(form.video_project_id) || form.video_project_id < 1) {
                    error.video_project_id = '请选择视频专题';
                }
                if (!G.isNumeric(form.min_index) && form.min_index < 1) {
                    error.min_index = '索引最小为1';
                }
                if (!G.isNumeric(form.max_index) && form.max_index < 1) {
                    error.max_index = '索引最小为1';
                }
            }
            if (form.type === 'misc' && !G.isNumeric(form.category_id)) {
                error.category_id = '请选择分类';
            }
            // 检查是否有未提供名称的字幕
            if (!form.merge_video_subtitle) {
                for (let i = 0; i < form.video_subtitles.length; ++i)
                {
                    const cur = form.video_subtitles[i];
                    if (G.isEmptyString(cur.name)) {
                        error.none = '存在未提供名称的字幕';
                        break;
                    }
                }
            }
            return {
                status: G.isEmptyObject(error) ,
                error ,
            };
        } ,

        submitEvent () {
            if (this.pending('submitEvent')) {
                this.message('warning' , '请求中...请耐心等待');
                return ;
            }
            const form = G.copy(this.form);
            // 字幕上传
            form.video_subtitles = this.uVideoSubtitles.map((v) => {
                return {
                    name: v.name ,
                    src: v.src ,
                };
            });
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
            form.video_subtitles = G.jsonEncode(form.video_subtitles);
            const thenCallback = (res) => {
                this.error();
                if (res.code !== TopContext.code.Success) {
                    this.errorHandle(res.message);
                    return ;
                }
                this.successModal((keep) => {
                    this.$emit('on-success');
                    if (!keep) {
                        this.closeFormModal();
                    }
                });
            };
            const finalCallback = () => {
                this.pending('submitEvent' , false);
            };
            this.pending('submitEvent' , true);
            if (this.mode === 'edit') {
                Api.video.update(form.id , form).then(thenCallback).finally(finalCallback);
                return ;
            }
            Api.video.store(form).then(thenCallback).finally(finalCallback);
        } ,

        changeEventTest (value) {
            console.log('i-select changed' , value , JSON.stringify(this.modules));
        } ,

        userChangeEvent (res) {
            this.error({user_id: ''} , false);
            this.form.user_id   = res.id;
            this.owner          = res;
        } ,

        showUserSelector () {
            this.$refs['user-selector'].show();
        } ,

        videoProjectChangeEvent (res) {
            this.error({video_project_id: ''} , false);
            this.form.video_project_id      = res.id;
            this.videoProject               = res;
        } ,

        showVideoProjectSelector () {
            this.$refs['video-project-selector'].show();
        } ,

        videoSubjectChangeEvent (res) {
            this.error({video_subject_id: ''} , false);
            this.form.video_subject_id      = res.id;
            this.videoSubject               = res;
        } ,

        showVideoSubjectSelector () {
            this.$refs['video-subject-selector'].show();
        } ,

        clearVideoSubjectEvent () {
            this.videoSubject = G.copy(videoSubject);
            this.form.video_subject_id = 0;
            this.error({video_subject_id: ''} , false);
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
            Api.video
                .destroyTag({
                    video_id: this.form.id ,
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
                    type: 'video' ,
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

        getTopTags () {
            if (this.form.module_id <= 0) {
                return ;
            }
            this.pending('getTopTags' , true);
            Api.tag
                .top({
                    module_id: this.form.module_id ,
                    type: 'video' ,
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
    } ,

    watch: {

    } ,
};

