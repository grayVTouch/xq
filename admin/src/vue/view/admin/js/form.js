const form = {
    sex: 'secret' ,
    // role_id: '' ,
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
                birthday: '' ,
            } ,

            dom: {} ,

            ins: {} ,

            // 角色列表
            roles: [] ,
        };
    } ,

    mounted () {
        this.initDom();
        this.initIns();
    } ,

    methods: {


        initDom () {
            this.dom.avatar = G(this.$refs.avatar);
        } ,

        initIns () {
            const self = this;
            this.ins.avatar = new Uploader(this.dom.avatar.get(0) , {
                api: this.thumbApi() ,
                mode: 'override' ,
                clear: true ,
                mimeLimit: 'image' ,
                uploaded (file , data , code) {
                    if (code !== TopContext.code.Success) {
                        this.status(file.id , false);
                        return ;
                    }
                    this.status(file.id , true);
                    self.form.avatar = data.data;
                } ,
                cleared () {
                    self.form.avatar = '';
                } ,
            });
        } ,

        findById (id) {
            return new Promise((resolve , reject) => {
                this.pending('findById' , true);
               Api.admin
                   .show(id)
                   .then((res) => {
                       if (res.code !== TopContext.code.Success) {
                           this.errorHandle(res.message);
                           reject();
                           return ;
                       }
                       const data = res.data;
                       delete data.password;
                       this.form = data;
                       resolve();
                 }).finally(() => {
                   this.pending('findById' , false);
               });
            });
        } ,

        openFormModal () {
            this.setValue('show' , true);
            this.getRoles();

            if (this.mode === 'add') {
                // 添加
                return ;
            }
            // 编辑
            this.findById(this.id).then(() => {
                // 做一些额外处理
                this.ins.avatar.render(this.form.avatar);
                this.setValue('birthday' , this.form.birthday ? this.form.birthday : '');
            });
        } ,

        closeFormModal () {
            if (this.pending('submitEvent')) {
                this.message('warning' , '请求中...请耐心等待');
                return;
            }
            this.setValue('show' , false);
            this.setValue('birthday' , '');
            this.form = G.copy(form);
            this.error();
            this.ins.avatar.clearAll();
        } ,

        getRoles () {
            this.pending('getRoles' , true);
            Api.role
                .all()
                .then((res) => {
                    this.pending('getRoles' , false);
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandle(res.message);
                        return ;
                    }
                    this.roles = res.data;
                });
        } ,

        birthdayChangeEvent (date) {
            this.form.birthday = date;
        } ,

        filter (form) {
            const error = {};
            if (G.isEmptyString(form.username)) {
                error.username = '请填写用户名';
            }
            if (this.mode === 'add' && G.isEmptyString(form.password)) {
                error.password = '请填写密码';
            }
            if (!G.isNumeric(form.role_id)) {
                error.role_id = '请选择角色';
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
                this.error();
                this.pending('submitEvent' , false);
            };
            this.error();
            this.pending('submitEvent' , true);
            if (this.mode === 'edit') {
                Api.admin.update(form.id , form).then(thenCallback).finally(finalCallback);
                return ;
            }
            Api.admin.store(form).then(thenCallback).finally(finalCallback);
        } ,
    } ,
}
