import mixin from './mixin.js';
export default {
    name: "info" ,
    data () {
        return {
            val: {
                pending: {} ,
                error: {} ,
            } ,
            form: {} ,
        };
    } ,

    mixins: [
        mixin
    ] ,

    mounted () {
        this.$emit('focus-menu' , 'password');
    } ,

    methods: {

        submitEvent () {
            if (this.pending('submitEvent')) {
                return ;
            }
            const self = this;
            this.error();
            this.pending('submitEvent' , true);
            Api.user
                .updatePasswordInLogged(null , this.form)
                .then((res) => {
                    this.pending('submitEvent' , false);
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtUserChildren(res.message , res.code , () => {
                            this.submitEvent();
                        });
                        return ;
                    }
                    this.message('success' , '操作成功');
                })
                .finally(() => {

                });
        } ,
    } ,


}
