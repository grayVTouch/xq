<template>
    <my-form-modal
            v-model="visible"
            title="请选择视频系列"
            width="75%"
            :mask-closable="true"
            :closable="true"
    >
        <template slot="footer">
            <i-button v-ripple type="error" @click="hide">取消</i-button>
        </template>
        <template slot="default">
            <div class="search-modal">
                <div class="input">
                    <div class="input-mask"><input type="text" v-model="table.search.value" @keyup.enter="searchEvent" placeholder="请输入搜索值"></div>
                    <div class="msg">输入id、名称可查询</div>
                </div>
                <div class="list">
                    <i-table
                            ref="table"
                            class="w-r-100"
                            border
                            :loading="myValue.pending.getData"
                            :data="table.data"
                            :columns="table.field"
                            @on-row-click="rowClickEvent">
                        <template v-slot:thumb="{row,index}">
                            <my-table-image-preview :src="row.thumb"></my-table-image-preview>
                        </template>
                        <template v-slot:module_id="{row,index}">{{ row.module ? `${row.module.name}【${row.module.id}】` : `unknow【${row.module_id}】` }}</template>
                        <template v-slot:action="{row,index}">
                            <my-table-button @click="rowClickEvent(row,index)">选择</my-table-button>
                        </template>
                    </i-table>
                </div>
                <div class="pager">
                    <my-page
                            :total="table.total"
                            :size="table.size"
                            :sizes="table.sizes"
                            :page="table.page"
                            @on-page-change="pageEvent"
                            @on-size-change="sizeEvent"
                    ></my-page>
                </div>
            </div>
        </template>
    </my-form-modal>
</template>

<script>
    const table = {
        data: [],
        field: [
            {
                title: 'id' ,
                key: 'id' ,
            } ,
            {
                title: '名称' ,
                key: 'name' ,
            } ,
            {
                title: '封面' ,
                slot: 'thumb' ,
            } ,
            {
                title: '模块' ,
                slot: 'module_id' ,
            } ,
            {
                title: '创建时间' ,
                key: 'created_at' ,
            } ,
            {
                title: '操作' ,
                slot: 'action' ,
                minWidth: TopContext.table.action ,
                align: TopContext.table.alignCenter ,
            } ,
        ] ,
        size: TopContext.size ,
        sizes: TopContext.sizes ,
        search: {
            value: '' ,
        } ,
        page: 1 ,
        total: 0 ,
    };

    export default {

        name: "my-video-series-selector" ,

        props: {
            moduleId: {
                default: 0 ,
            } ,
        } ,

        data () {
            return {
                visible: false ,
                table: G.copy(table) ,
            };
        } ,

        methods: {
            getData () {
                this.pending('getData' , true);
                Api.videoSeries
                    .search({
                        limit: this.table.limit ,
                        page: this.table.page ,
                        ...this.table.search ,
                        module_id: this.moduleId ,
                    })
                    .then((res) => {
                        if (res.code !== TopContext.code.Success) {
                            this.errorHandle(res.message);
                            return ;
                        }
                        const data = res.data;
                        this.table.total = data.total;
                        this.table.size = data.per_page;
                        this.table.page = data.current_page;
                        this.table.data = data.data;
                    })
                    .finally(() => {
                        this.pending('getData' , false);
                    });
            } ,

            pageEvent (page , size) {
                this.table.page = page;
                this.table.size = size;
                this.getData();
            } ,

            sizeEvent (size , page) {
                this.table.page = page;
                this.table.size = size;
                this.getData();
            } ,
            searchEvent (e) {
                this.table.page = 1;
                this.getData();
            } ,

            rowClickEvent (row , index) {
                this.$emit('on-change' , G.copy(row));
                this.hide();
            },

            hide () {
                this.table  = G.copy(table);
                this.visible   = false;
            } ,

            show () {
                this.getData();
                this.visible = true;
            } ,

        } ,
    }
</script>

<style scoped>

</style>
