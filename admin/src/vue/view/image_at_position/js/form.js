const form = {
    p_id: 0 ,
    module_id: '' ,
    enable: 1 ,
    weight: 0 ,
    status: 1 ,
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
                showModuleSelector: false ,
                /**
                 * module - 选择模块
                 * form - 输入表单
                 */
                step: 'module' ,
            } ,

            dom: {} ,

            ins: {} ,

            modules: [] ,

            positions: [] ,

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

        getPositions () {
            this.pending('getPositions' , true);
            Api.position
                .all()
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandle(res.message);
                        return ;
                    }
                    this.positions = res.data;
                })
                .finally(() => {
                    this.pending('getPositions' , false);
                });
        } ,

        initDom () {
            this.dom.src = G(this.$refs.src);
        } ,

        initIns () {
            const self = this;
            this.ins.src = new Uploader(this.dom.src.get(0) , {
                api: this.imageApi(true) ,
                mode: 'override' ,
                clear: true ,
                mimeLimit: 'image' ,
                uploaded (file , data , code) {
                    if (code !== TopContext.code.Success) {
                        this.status(file.id , false);
                        return ;
                    }
                    this.status(file.id , true);
                    self.form.src = data.data;
                } ,
                cleared () {
                    self.form.src = '';
                } ,
            });
        } ,

        findById (id) {
            this.pending('findById' , true);
            return new Promise((resolve , reject) => {
               Api.imageAtPosition
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
            this.getPositions();
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
                        // 做一些额外处理
                        this.ins.src.render(this.form.src);
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
            this.positions  = [];
            this.form       = G.copy(form);
            this.ins.src.clearAll();
            this.error();
        } ,

        filter (form) {
            const error = {};

            if (!G.isNumeric(form.position_id)) {
                error.position_id = '请选择位置';
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
                Api.imageAtPosition.update(form.id , form).then(thenCallback).finally(finalCallback);
                return ;
            }
            Api.imageAtPosition.store(form).then(thenCallback).finally(finalCallback);
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
