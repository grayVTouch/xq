import mixin from './mixin.js';

const history = {
    page: 1 ,
    size: TopContext.size ,
    sizes: TopContext.sizes ,
    data: [] ,
    total: 0 ,
};

export default {
    name: "history" ,

    data () {
        return {
            // 表单搜索
            search: {
                relation_type: '' ,
                value: '' ,
            } ,

            history: G.copy(history , true),

            dom: {} ,
            val: {
                pending: {} ,
                fixed: false ,
            } ,
        };
    } ,

    mounted () {
        this.$emit('focus-menu' , 'history');
        this.initDom();
        this.initEvent();
        this.getHistory();
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

        getHistory () {
            this.pending('getHistory' , true);
            Api.history
                .index({
                    size: this.history.size ,
                    page: this.history.page ,
                    ...this.search ,
                })
                .then((res) => {
                    this.pending('getHistory' , false);
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtUserChildren(res.message , res.code , () => {
                            this.getHistory();
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
                    this.history.total = data.total;
                    this.history.size = data.per_page;
                    this.history.page = data.current_page;
                    this.history.data = data.data;
                })
                .finally(() => {

                });
        } ,

        destroyHistory (row) {
            const pendingKey = 'destroyHistory_' + row.id;
            if (this.pending(pendingKey)) {
                return ;
            }
            this.pending(pendingKey , true);
            Api.history
                .destroyAll(null , {
                    ids: G.jsonEncode([row.id])
                })
                .then((res) => {
                    this.pending(pendingKey , false);
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtUserChildren(res.message , res.code , () => {
                            this.destroyHistory(row);
                        });
                        return ;
                    }
                    this.getHistory();
                })
                .finally(() => {

                });

        } ,

        searchHistory () {
            this.history.page = 1;
            this.getHistory();
        } ,

        pageEvent (page , size) {
            this.history.page = page;
            this.history.size = size;
            this.getHistory();
        } ,

        sizeEvent (size , page) {
            this.history.size = size;
            this.history.page = page;
            this.getHistory();
        } ,
    } ,
}
