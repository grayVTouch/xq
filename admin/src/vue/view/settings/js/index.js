
const systemSettings = {
    disk: 'local' ,
    cloud_storage_manufacturer: 'aliyun' ,
};
const webRouteMappings = [];

const friendLinks = [
    // {
    //     name: '' ,
    //     link: '' ,
    // } ,
];

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
            friendLinks ,
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
                    this.friendLinks = G.jsonDecode(this.systemSettings.friend_links);

                    console.log(this.friendLinks , JSON.stringify(this.friendLinks));
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
                friend_links: G.jsonEncode(this.friendLinks) ,
            };
            const filterRes = this.filter(form);
            if (!filterRes.status) {
                this.error(filterRes.error , true);
                this.errorHandle(G.getObjectFirstKeyMappingValue(filterRes.error));
                return ;
            }
            for (let i = 0; i < this.friendLinks.length; ++i)
            {
                const cur = this.friendLinks[i];
                if (G.isEmptyString(cur.name)) {
                    this.errorHandle('友情链接：名称不能为空');
                    return ;
                }
                if (G.isEmptyString(cur.link)) {
                    this.errorHandle('友情链接：链接不能为空');
                    return ;
                }
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
