const form = {
    p_id: 0 ,
    module_id: '' ,
    enable: 1 ,
    weight: 0 ,
    status: 1 ,
};

const owner = {
    id: 0 ,
    username: 'unknow' ,
};

export default {
    name: "my-form" ,

    props: {
        id: {
            type: [Number , String] ,
            required: true ,
        } ,

        mode: {
            type: String ,
            default: 'add' ,
        } ,
    } ,

    computed: {
        title () {
            return this.mode === 'edit' ? '编辑' : '添加';
        } ,
    } ,

    data () {
        return {
            form: G.copy(form) ,

            myValue: {
                show: false ,
                showUserSelector: false ,
                showModuleSelector: false ,
                /**
                 * module - 选择模块
                 * form - 输入表单
                 */
                step: 'module' ,
            } ,

            dom: {} ,

            ins: {} ,

            owner: G.copy(owner) ,

            modules: [] ,

        };
    } ,

    mounted () {
        this.initDom();
        this.initIns();
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

        initDom () {
            this.dom.thumb = G(this.$refs.thumb);
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
        } ,

        findById (id) {
            this.pending('findById' , true);
            return new Promise((resolve , reject) => {
               Api.videoSeries
                   .show(id)
                   .then((res) => {
                       if (res.code !== TopContext.code.Success) {
                           this.errorHandle(res.message);
                           reject();
                           return ;
                       }
                       this.form = res.data;
                       resolve();
                   }).finally(() => {
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
            this.myValue.step = 'module';
            this.myValue.showModuleSelector = true;
        } ,

        // 表单处理
        handleFormStep () {
            this.myValue.step = 'form';
            this.myValue.show = true;
            if (this.mode === 'edit') {
                this.findById(this.id)
                    .then(() => {
                        this.owner = this.form.user ? this.form.user : G.copy(owner);

                        this.ins.thumb.render(this.form.thumb);
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
            this.handleFormStep();
        } ,

        closeFormModal () {
            if (this.pending('submitEvent')) {
                this.message('warning' , '请求中...请耐心等待');
                return;
            }
            this.myValue.step = 'module';
            this.myValue.showModuleSelector = false;
            this.myValue.show   = false;
            this.modules    = [];
            this.form       = G.copy(form);
            this.owner      = G.copy(owner);
            this.error();
            this.ins.thumb.clearAll();
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
            return {
                status: G.isEmptyObject(error) ,
                error ,
            };
        } ,

        submitEvent () {
            const self = this;
            const form = G.copy(this.form);
            const filterRes = this.filter(form);
            if (!filterRes.status) {
                this.error(filterRes.error , true);
                this.errorHandle(G.getObjectFirstKeyMappingValue(filterRes.error));
                return ;
            }
            const thenCallback = (res) => {
                if (res.code !== TopContext.code.Success) {
                    this.errorHandle(res.message);
                    return ;
                }
                this.successModal((keep) => {
                    this.$emit('on-success');
                    if (keep) {
                        return ;
                    }
                    self.closeFormModal();
                });
            };
            const finalCallback = () => {
                this.pending('submitEvent' , false);
                this.error();
            };
            this.pending('submitEvent' , true);
            if (this.mode === 'edit') {
                Api.videoSeries.update(this.form.id , this.form).then(thenCallback).finally(finalCallback);
                return ;
            }
            Api.videoSeries.store(this.form).then(thenCallback).finally(finalCallback);
        } ,

        userChangeEvent (res) {
            this.error({user_id: ''} , false);
            this.form.user_id   = res.id;
            this.owner          = res;
        } ,

        showUserSelector () {
            this.$refs['user-selector'].show();
        } ,
    } ,
}
