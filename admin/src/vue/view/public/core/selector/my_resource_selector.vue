<template>
    <my-form-modal
            v-model="visible"
            title="请选择"
            :mask-closable="true"
            :closable="true"
            width="800"
    >
        <template slot="footer">
            <i-button :loading="myValue.pending.getData" v-ripple type="error" @click="hide">取消</i-button>
        </template>
        <template slot="default">
            <div class="search-modal">
                <tree
                        class="my-tree"
                        v-if="visible"
                        :data="data"
                        :load-data="loadData"
                        @on-select-change="selectChangedEvent"
                ></tree>
            </div>
        </template>
    </my-form-modal>
</template>

<script>
    const search = {
        // 上级搜索目录
        parent_path: '' ,
    };

    export default {
        name: "my-user-selector" ,
        data () {
            return {
                visible: false ,
                data: [] ,
                search: G.copy(search) ,
            };
        } ,
        methods: {
            getData () {
                this.pending('getData' , true);
                return new Promise((resolve , reject) => {
                    Api.systemDisk
                        .index({
                            ...this.search ,
                        })
                        .then((res) => {
                            if (res.code !== TopContext.code.Success) {
                                this.errorHandle(res.message);
                                reject();
                                return ;
                            }
                            resolve(res.data);
                        })
                        .finally(() => {
                            this.pending('getData' , false);
                        });
                });
            } ,

            loadData (row , callback) {
                this.search.parent_path = row.path;
                this.getData()
                    .then((res) => {
                        row.isLoaded = true;
                        const data = res.map((v) => {
                            const line = {
                                title: v.name ,
                                path: v.path ,
                                expand: false ,
                                isLoaded: false ,
                                render: this.generateTreeRender() ,
                            };
                            if (!v.is_empty) {
                                line.loading = false;
                                line.children = [];
                            }
                            return line;
                        });
                        // console.log(data);
                        callback(data);
                    });
            } ,

            selectChangedEvent (selections , selection) {
                selection.selected = false;
                if (selection.isLoaded) {
                    selection.expand = !selection.expand;
                } else {
                    selection.loading = true;
                    this.loadData(selection, (children) => {
                        selection.loading = false;
                        selection.expand = true;
                        this.$set(selection, 'children', children);
                    });
                }
            },

            generateTreeRender () {
                return (h, { root, node, data }) => {
                    return h('span', {
                        class: {
                            'my-tree-row': true ,
                        } ,
                        on: {
                            click: (e) => {
                                // e.stopPropagation();
                                console.log('span click !');
                                // data.loading = true;
                                // this.loadData(data , (children) => {
                                //     data.loading = false;
                                //     data.expand = true;
                                //     this.$set(data , 'children' , children);
                                // });
                            }
                        }
                    } , [
                        h('span' , {
                            class: {
                                name: true
                            } ,
                            domProps: {
                                textContent: data.title
                            } ,
                        }) ,
                        h('span' , {
                            class: {
                                actions: true ,
                                'm-r-20': true ,
                            } ,
                        } , [
                            h('i-button' , {
                                props: {
                                    size: 'small'
                                } ,
                                domProps: {
                                    textContent: '选择' ,
                                } ,
                                on: {
                                    click: (e) => {
                                        e.stopPropagation();
                                        this.hide();
                                        this.$emit('on-change' , data.path);
                                    } ,
                                } ,
                            })
                        ]) ,
                    ]);
                };
            } ,

            hide () {
                this.visible   = false;
                this.data = [];
                this.search = G.copy(search);
            } ,

            show () {
                this.getData()
                    .then((res) => {
                        this.data = res.map((v) => {
                            const row = {
                                title: v.name ,
                                path: v.path ,
                                expand: false ,
                                isLoaded: false ,
                                render: this.generateTreeRender() ,
                            };
                            if (!v.is_empty) {
                                row.loading = false;
                                row.children = [];
                            }
                            return row;
                        });
                    });
                this.visible = true;
            } ,

        } ,
    }
</script>

<style>
    .my-tree .ivu-tree-title{
        width: calc(100% - 16px);
    }

    .my-tree-row {
        display: flex;
        justify-content: space-between;
        align-items: stretch;
        width: 100%;
    }

</style>
