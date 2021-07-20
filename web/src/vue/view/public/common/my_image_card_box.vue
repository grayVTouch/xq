<template>
    <div class="card-box image-card-box">
        <a class="link" target="_blank" :href="genUrl(`/image/${row.id}/show`)">
            <div class="mask">
                <img class="image judge-img-size" :data-src="row.src ? row.src : TopContext.res.notFound" v-judge-img-size alt="">
            </div>
            <div class="actions">
                <div class="top">
                    <div class="right praise" v-ripple @click.prevent="$emit('on-praise' , row)">
                        <my-loading size="16" v-if="isPraisePending"></my-loading>
                        <my-icon icon="shoucang2" :class="{'run-red': row.is_praised }" /> 喜欢
                    </div>
                </div>
                <div class="btm">
                    <span class="view-count"><my-icon icon="chakan" mode="right" />{{ row.view_count }}</span>
                    <span class="praise-count"><my-icon icon="shoucang2" mode="right" />{{ row.praise_count }}</span>
                    <span class="collect-count" v-if="state().user"><my-icon icon="shoucang6" mode="right" />{{ row.collect_count }}</span>
                </div>
            </div>
        </a>
    </div>
</template>

<script>
    export default {
        name: "my_image_card_box" ,

        props: {
            row: {
                type: Object ,
                default () {
                    return {
                        src: '' ,
                        is_praised: 0 ,
                        view_count: 0 ,
                        praise_count: 0 ,
                        collect_count: 0 ,
                        created_at: '' ,
                        format_time: '' ,
                    };
                } ,
            } ,

            onPraise: {
                type: Function ,
                default: null
            } ,

            isPraisePending: {
                type: Boolean ,
                default: false ,
            } ,
        } ,

        data () {
            return {};
        } ,

        watch: {
            row: {
                immediate: true ,
                handler (newVal , oldVal) {
                    if (newVal) {
                        return ;
                    }
                } ,
            } ,
        } ,
    }
</script>

<style scoped>
    /**
     * *****************************
     * 图片 - 盒子 样式
     * *****************************
     */
    .image-card-box {
        width: 205px;
        height: 280px;
        overflow: hidden;
        position: relative;
        cursor: pointer;
    }

    .image-card-box:hover .link .actions {
        opacity: 1;
    }

    .image-card-box .link {
        display: block;
        width: inherit;
        height: inherit;
    }

    .image-card-box .link .mask {
        width: inherit;
        height: inherit;
        position: relative;
    }

    .image-card-box .link .mask .image {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50% , -50%);
    }

    .image-card-box .link .actions {
        position: absolute;
        left: 0;
        top: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.3);
        opacity: 0;
        transition: all 0.3s;
    }

    .image-card-box .link .actions .top {
        position: relative;
        width: inherit;
        height: inherit;
    }


    .image-card-box .link .actions .top .praise {
        background-color: rgba(93, 93, 93, 0.5);
        height: 30px;
        line-height: 30px;
        padding: 0 20px;
        color: #fff;
        font-size: 12px;
        transition: all 0.3s;
        cursor: pointer;
        position: absolute;
        right: 20px;
        top: 20px;
        opacity: 1;
        /*transition: all 0.3s;*/
    }

    .image-card-box .link .actions .btm {
        color: #eee;
        font-size: 12px;
        padding-bottom: 15px;
    }
</style>
