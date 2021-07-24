import MyFormButton from '../my_form_button.vue';

const systemSettings = {
    disk: 'local' ,
    cloud_storage_manufacturer: 'aliyun' ,
};
const webRouteMappings = [];

export default {
    name: "index",


    data () {
        return {
            myValue: {
                tab: 'admin_settings',
                error: {},
            },
            dom: {},
            ins: {},
            systemSettings ,
            webRouteMappings ,
        };
    } ,

    mounted () {
        this.initDom();
        this.initIns();
        this.getData();
    } ,

    methods: {

        initDom () {
        } ,


        initIns () {

        } ,

        getData () {
            this.pending('getData' , true);
            Api.systemSettings
                .data()
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandle(res.message);
                        return ;
                    }
                    const data = res.data;
                    this.systemSettings = data.system_settings;
                    this.webRouteMappings = data.web_route_mappings;
                })
                .finally(() => {
                    this.pending('getData' , false);
                });
        } ,

        filter (form) {
            const error = {};
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

            const form = {
                ...this.systemSettings ,
                web_route_mappings: this.webRouteMappings ,
            };
            const filterRes = this.filter(form);
            if (!filterRes.status) {
                this.error(filterRes.error , true);
                this.errorHandle(G.getObjectFirstKeyMappingValue(filterRes.error));
                return ;
            }
            form.web_route_mappings = G.jsonEncode(this.webRouteMappings);
            this.pending('submitEvent' , true);
            Api.systemSettings
                .update(form)
                .then((res) => {
                    this.error();
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandle(res.message);
                        return ;
                    }
                    this.successHandle('操作成功');
                })
                .finally(() => {
                    this.pending('submitEvent' , false);
                });
        } ,
    } ,
}
