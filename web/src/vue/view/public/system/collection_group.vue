<template>
    <!-- 收藏夹 -->
    <div class="my-collection-group hide" @click="hide">
        <div class="inner" @click.stop>
            <div class="title">
                <span class="text">收藏夹</span>
                <my-button class="action close" @click="hide"><my-icon icon="close" size="12" /></my-button>
            </div>

            <div class="create">
                <div class="title m-b-15 run-weight">创建收藏夹</div>
                <div class="content">
                    收藏夹名称 <input type="text" class="input" placeholder="请输入收藏夹名称" v-model="collectionGroup.name" @keyup.stop.enter="createAndJoinCollectionGroup"> <my-button class="button" @click="createAndJoinCollectionGroup"><my-loading size="16" v-if="val.pending.createAndJoinCollectionGroup" />&nbsp;创建并添加</my-button>
                </div>
            </div>

            <div class="favorites">
                <div class="title m-b-15 run-weight">收藏夹列表</div>
                <div class="list">

                    <div class="item" v-for="v in data">
                        <div class="name">
                            {{ v.name }}
                            <span class="exists" v-if="v.is_inside">/已在此列表</span>
                            <my-button class="button" @click="collectionHandle(v)">
                                <my-loading v-if="val.pending['collectionHandle_' + v.id]" size="16"></my-loading>
                                <template v-if="v.is_inside">移除</template>
                                <template v-else>添加</template>
                            </my-button>
                        </div>
                        <div class="info">
                            <span class="number">{{ v.count }}</span>
                        </div>
                    </div>

                    <div class="loading" v-if="val.pending.getData">
                        <my-loading></my-loading>
                    </div>

                    <div class="empty" v-if="!val.pending.getData && data.length <= 0">暂无数据</div>


                </div>

            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "collection" ,
        data () {
            return {
                val: {
                    pending: {} ,
                } ,
                data: [] ,
                dom: {} ,
                // 收藏夹表单
                collectionGroup: {
                    name: '' ,
                } ,
            };
        } ,

        props: {
            relationType: {
                required: true ,
            } ,

            relationId: {
                required: true ,
            } ,
        } ,

        mounted () {
            this.initDom();
        } ,

        methods: {

            initDom () {
                this.dom.root = G(this.$el);
            } ,

            show () {
                this.dom.root.removeClass('hide');
                this.dom.root.startTransition('show');
                this.getData();
            } ,

            hide () {
                this.dom.root.endTransition('show' , () => {
                    this.dom.root.addClass('hide');
                });
            } ,

            // 获取我的收藏夹
            getData () {
                this.pending('getData' , true);
                Api.collectionGroup
                    .getWithJudge({
                        relation_type: this.relationType ,
                        relation_id: this.relationId ,
                    })
                    .then((res) => {
                        if (res.code !== TopContext.code.Success) {
                            this.errorHandleAtHomeChildren(res.message , res.code , () => {
                                this.getData();
                            });
                            return ;
                        }
                        this.data = res.data;
                    })
                    .finally(() => {
                        this.pending('getData' , false);
                    });
            } ,

            collectionHandle (row) {
                const pendingKey = 'collectionHandle_' + row.id;
                if (this.pending(pendingKey)) {
                    return ;
                }
                this.pending(pendingKey , true);
                const action = row.is_inside ? 0 : 1;
                Api.collectionGroup
                    .collectOrCancel(row.id , null , {
                        relation_type: this.relationType ,
                        relation_id: this.relationId ,
                        action ,
                    })
                    .then((res) => {
                        if (res.code !== TopContext.code.Success) {
                            this.errorHandleAtHomeChildren(res.message , res.code , () => {
                                this.collectionHandle(row);
                            });
                            return ;
                        }
                        this.$emit('on-change' , action);
                        row.is_inside = action;
                        action ? row.count++ : row.count--;
                        this.getData();
                    })
                    .finally(() => {
                        this.pending(pendingKey , false);
                    });
            } ,

            // 创建并添加收藏夹
            createAndJoinCollectionGroup () {
                if (this.pending('createAndJoinCollectionGroup')) {
                    return ;
                }
                this.pending('createAndJoinCollectionGroup' , true);
                Api.collectionGroup
                    .createAndJoin(null , {
                        ...this.collectionGroup ,
                        relation_type: this.relationType ,
                        relation_id: this.relationId ,
                    })
                    .then((res) => {
                        if (res.code !== TopContext.code.Success) {
                            this.errorHandleAtHomeChildren(res.message , res.code , () => {
                                this.createAndJoinCollectionGroup();
                            });
                            return ;
                        }
                        this.$emit('on-change' , 1);
                        // 刷新列表
                        this.getData();
                    })
                    .finally(() => {
                        this.pending('createAndJoinCollectionGroup' , false);
                    });
            } ,
        } ,
    }
</script>

<style scoped>

    /**
     * *********************
     * 收藏夹
     * *********************
     */
    .my-collection-group {
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        opacity: 0;
        transition: all 0.3s;
        z-index: 100;
    }

    .my-collection-group.show {
        opacity: 1;
    }

    .my-collection-group.show > .inner {
        transform: translate(-50% , -50%);
    }

    .my-collection-group > .inner {
        background-color: #424242;
        padding: 0 20px 20px 20px;
        width: 700px;
        border-radius: 2px;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50% , 100%);
        transition: all 0.3s;
    }

    .my-collection-group > .inner > * {
        margin-bottom: 20px;
    }

    .my-collection-group > .inner > *:nth-last-of-type(1) {
        margin-bottom: 0;
    }

    .my-collection-group > .inner > .title {
        height: 50px;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        border-bottom: 1px solid #525252;

    }

    .my-collection-group > .inner > .title .close {
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        color: #fff;
        transition: all 0.3s;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        overflow: hidden;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .my-collection-group > .inner > .title .close:hover {
        background-color: #6b6b6b;
    }

    .my-collection-group > .inner > .create {

    }

    .my-collection-group > .inner > .create .title {
        height: 30px;
        line-height: 30px;
    }

    .my-collection-group > .inner > .create .content {
        font-size: 14px;
    }

    .my-collection-group > .inner > .create .content .input {
        background-color: rgba(255,255,255,0.1);
        border: none;
        width: 200px;
        height: 30px;
        color: #fff;
    }

    .my-collection-group > .inner > .create .content .input::placeholder {
        color: #ccc;
    }

    .my-collection-group > .inner > .create .content .input:focus {
        background-color: rgba(255,255,255,0.2);
    }

    .my-collection-group > .inner > .create .content .button {
        background-color: #5d5d5d;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.3s;
        height: 30px;
        padding: 0 20px;
        color: #fff;
    }

    .my-collection-group > .inner > .create .content .button:hover {
        background-color: #828282;
    }

    .my-collection-group > .inner > .favorites .title {
        height: 30px;
        line-height: 30px;
    }

    .my-collection-group > .inner > .favorites .list {
        display: flex;
        justify-content: flex-start;
        align-items: flex-start;
        align-content: flex-start;
        flex-wrap: wrap;
        min-height: 200px;
        max-height: 400px;
        overflow-y: auto;
        /*padding: 15px 0;*/
        /*border: 1px solid #525252;*/
        position: relative;
    }

    .my-collection-group > .inner > .favorites .list .loading ,
    .my-collection-group > .inner > .favorites .list .empty
    {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .my-collection-group > .inner > .favorites .list .loading {
        background-color: rgba(0,0,0,0.5);
    }

    .my-collection-group > .inner > .favorites .list .empty {
        font-size: 14px;
    }

    .my-collection-group > .inner > .favorites .list .item {
        width: 50%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex: 0 0 auto;
        height: 40px;
        font-size: 14px;
        box-sizing: border-box;
        /*margin: 0 0 15px 0;*/
        margin: 0;
        /*padding: 0 10px;*/
        /*border: 1px solid #525252;*/
    }

    .my-collection-group > .inner > .favorites .list .item:nth-of-type(2n - 1) {
        padding-right: 10px;
    }

    .my-collection-group > .inner > .favorites .list .item:nth-of-type(2n) {
        padding-left: 10px;
    }
    .my-collection-group > .inner > .favorites .item > * {
        margin: 0;
        flex: 0 0 auto;
    }

    .my-collection-group > .inner > .favorites .list .item .name .exists {
        color: darkgrey;
        font-size: 12px;
    }

    .my-collection-group > .inner > .favorites .list .item .name .button {
        background-color: #5d5d5d;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.3s;
        height: 28px;
        padding: 0 20px;
        color: #fff;
        margin-left: 8px;
    }

    .my-collection-group > .inner > .favorites .list .item .name .button:hover {
        background-color: #828282;
    }

    .my-collection-group > .inner > .favorites .list .item .info {

    }

    .my-collection-group > .inner > .favorites .list .item .info .number {
        background-color: #5d5d5d;
        padding: 2px 12px;
        border-radius: 30%;
        font-size: 12px;

    }
</style>
