import mixin from './mixin.js';

const data = {
    page: 1 ,
    size: TopContext.size ,
    sizes: TopContext.sizes ,
    data: [] ,
    total: 0 ,
};

export default {
    name: "my-praise" ,

    data () {
        return {
            // 表单搜索
            search: {
                relation_type: '' ,
                value: '' ,
            } ,

            data: G.copy(data , true),

            dom: {} ,
            val: {
                pending: {} ,
                fixed: false ,
            } ,
        };
    } ,

    mounted () {
        this.$emit('focus-menu' , 'praise');
        this.initDom();
        this.initEvent();
        this.getData();
    } ,

    mixins: [
        mixin
    ] ,

    methods: {
        initDom () {
            this.dom.win = G(window);
            this.dom.filter = G(this.$refs.filter);
        } ,

        scrollEvent () {
            const scrollTop = this.dom.filter.getWindowOffsetVal('top');
            this.val.fixed = scrollTop < TopContext.val.fixedTop;
        } ,

        initEvent () {
            this.dom.win.on('scroll' , this.scrollEvent.bind(this));
        } ,

        getData () {
            this.pending('getData' , true);
            Api.praise
                .index({
                    size: this.data.size ,
                    page: this.data.page ,
                    ...this.search ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtUserChildren(res.message , res.code , () => {
                            this.getData();
                        });
                        return ;
                    }
                    const data = res.data;
                    data.data.forEach((group) => {
                        group.data.forEach((v) => {
                            v.relation = v.relation ? v.relation : {};
                            v.relation.user = v.relation.user ? v.relation.user : {};

                            switch (v.relation_type)
                            {
                                case 'image_project':
                                    break;
                                case 'image':
                                    break;
                                case 'video':
                                case 'video_project':
                                    v.relation.user_play_record = v.relation.user_play_record ? v.relation.user_play_record : {};
                                    v.relation.user_play_record.video = v.relation.user_play_record.video ? v.relation.user_play_record.video : {};
                                    break;
                                default:
                            }
                        });
                    });
                    this.data.total = data.total;
                    this.data.size = data.per_page;
                    this.data.page = data.current_page;
                    this.data.data = data.data;
                })
                .finally(() => {
                    this.pending('getData' , false);
                });
        } ,

        destroyMyPraise (row) {
            const pendingKey = 'destroyMyPraise_' + row.id;
            if (this.pending(pendingKey)) {
                return ;
            }
            this.pending(pendingKey , true);
            Api.praise
                .destroyAll(null , {
                    ids: G.jsonEncode([row.id])
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtUserChildren(res.message , res.code , () => {
                            this.destroyMyPraise(row);
                        });
                        return ;
                    }
                    this.getData();
                })
                .finally(() => {
                    this.pending(pendingKey , false);
                });

        } ,

        searchHistory () {
            this.data.page = 1;
            this.getData();
        } ,

        pageEvent (page , size) {
            this.data.page = page;
            this.data.size = size;
            this.getData();
        } ,

        sizeEvent (size , page) {
            this.data.size = size;
            this.data.page = page;
            this.getData();
        } ,
    } ,
}
