<template>
    <div class="card-box image-project-card-box">
        <!-- 封面 -->
        <div class="thumb">
            <a class="link" target="_blank" :href="genUrl(`/image_project/${row.id}/show`)">
                <img
                        :data-src="row.thumb ? row.thumb : TopContext.res.notFound"
                        v-judge-img-size
                        class="image judge-img-size"
                        alt="封面"
                >
                <div class="mask">
                    <div class="top">
                        <div class="type"><my-icon icon="zhuanyerenzheng" size="35" /></div>
                        <div class="praise" v-ripple @click.prevent="$emit('on-praise' , row)">
                            <my-loading size="16" v-if="isPraisePending"></my-loading>
                            <my-icon icon="shoucang2" :class="{'run-red': row.is_praised }" /> 喜欢
                        </div>
                    </div>
                    <div class="btm">
                        <div class="count">{{ row.image_count }}P</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="introduction">
            <!-- 标签 -->
            <div class="tags">
                <span class="ico"><my-icon icon="icontag" size="18" /></span>
                <a class="tag"
                   target="_blank"
                   v-for="tag in row.tags"
                   :href="genUrl(`#/image_project/search?tag_id=${tag.tag_id}`)"
                >{{ tag.name }}</a>
            </div>
            <!-- 标题 -->
            <div class="title"><a target="_blank" :href="genUrl(`/image_project/${row.id}/show`)">{{ row.name }}</a></div>
            <!-- 发布者 -->
            <div class="user">
                <div class="sender">
                    <span class="avatar-outer"><img :src="row.user.avatar ? row.user.avatar : TopContext.res.avatar" alt="" class="image avatar"></span>
                    <a class="name">{{ row.user.nickname }}</a>
                </div>
                <div class="action"></div>
            </div>
            <!-- 统计信息 -->
            <div class="info">
                <div class="left"><my-icon icon="shijian" class="ico" mode="right" /> {{ row.format_time }}</div>
                <div class="right">
                    <span class="view-count"><my-icon icon="chakan" mode="right" />{{ row.view_count }}</span>
                    <span class="praise-count"><my-icon icon="shoucang2" mode="right" />{{ row.praise_count }}</span>
                    <span class="collect-count" v-if="state().user"><my-icon icon="shoucang6" mode="right" />{{ row.collect_count }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "my_image_project_card_box" ,

        props: {
            row: {
                type: Object ,
                default () {
                    return {
                        thumb: '' ,
                        is_praised: 0 ,
                        view_count: 0 ,
                        praise_count: 0 ,
                        collect_count: 0 ,
                        image_count: 0 ,
                        user: {} ,
                        tags: [] ,
                        created_at: '' ,
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
                    newVal.tags = newVal.tags ? newVal.tags : [];
                    newVal.user = newVal.user ? newVal.user : {};
                } ,
            } ,
        } ,
    }
</script>

<style scoped>

    /**
     * **********************
     * 弹性盒子
     * **********************
     */
    .image-project-card-box {
        transition: all 0.3s;
        transform: translateY(0);
        background-color: #383838;
        width: 305px;
        box-sizing: border-box;
    }

    .image-project-card-box:hover {
        background-color: #424242;
        transform: translateY(-5px);
    }

    .image-project-card-box:hover .thumb .link .mask {
        /*.image-project-card-box .thumb .link .mask {*/
        /*opacity: 1;*/
        background-color: rgba(0,0,0,0.5);
    }

    .image-project-card-box:hover .thumb .link .mask .top .praise {
        /*.image-project-card-box .thumb .link .mask .top .praise {*/
        opacity: 1;
    }

    .image-project-card-box .thumb {
        height: 380px;
        overflow: hidden;
    }

    .image-project-card-box .thumb .link {
        position: relative;
        display: block;
        height: inherit;

    }

    .image-project-card-box .thumb .link > .image {
        position: absolute;
        left: 50%;
        top: 0;
        z-index: 1;
        transform: translateX(-50%);
    }

    .image-project-card-box .thumb .link .mask {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        transition: all 0.3s;
        /*background-color: rgba(0,0,0,0.5);*/
        opacity: 1;
        cursor: pointer;
        z-index: 1;
    }

    .image-project-card-box .thumb .link .mask > * {
        position: absolute;
        left: 0;
        width: 100%;
        box-sizing: border-box;
    }

    .image-project-card-box .thumb .link .mask .run-iconfont {
        font-size: 35px;
    }

    .image-project-card-box .thumb .link .mask .top {
        top: 0;
    }

    .image-project-card-box .thumb .link .mask .top .type {
        position: absolute;
        left: 20px;
        top: 20px;
    }

    .image-project-card-box .thumb .link .mask .top .praise {
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
        opacity: 0;
        /*transition: all 0.3s;*/
    }

    .image-project-card-box .thumb .link .mask .top .praise:hover {
        background-color: rgba(93, 93, 93, 0.8);
    }

    .image-project-card-box .thumb .link .mask .btm {
        bottom: 0;
    }

    .image-project-card-box .thumb .link .mask .btm .count {
        background-color: rgba(62, 62, 62, 0.5);
        padding: 0 15px;
        height: 24px;
        line-height: 24px;
        font-size: 12px;
        position: absolute;
        right: 20px;
        bottom: 20px;
    }

    .image-project-card-box .introduction {
        padding: 0 15px 15px;
        box-sizing: border-box;
    }

    .image-project-card-box .introduction .tags {
        height: 40px;
        line-height: 40px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .image-project-card-box .introduction .tags .ico {
        padding-right: 5px;
    }

    .image-project-card-box .introduction .tags .tag {
        padding: 3px 10px;
        background-color: #5d5d5d;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.3s;
        margin-left: 5px;
        line-height: normal;
        display: inline-block;
    }

    .image-project-card-box .introduction .tags .tag:nth-of-type(1) {
        margin-left: 0;
    }

    .image-project-card-box .introduction .tags .tag:hover {
        background-color: #828282;
    }

    .image-project-card-box .introduction .title {
        height: 40px;
        line-height: 40px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 14px;
        /*cursor: pointer;*/
    }

    .image-project-card-box .introduction .title:hover {
        text-decoration: underline;
    }

    .image-project-card-box .introduction .user {
        height: 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .image-project-card-box .introduction .user > * {
        margin: 0;
    }

    .image-project-card-box .introduction .user .sender {
        height: auto;
        display: flex;
        align-items: center;
        justify-content: flex-start;
    }

    .image-project-card-box .introduction .user .sender .avatar-outer {
        width: 30px;
        height: 30px;
        position: relative;
        overflow: hidden;
        border-radius: 50%;
        display: inline-block;
        vertical-align: middle;
    }

    .image-project-card-box .introduction .user .sender .avatar-outer .avatar {
        position: absolute;
        left: 50%;
        top: 50%;
        min-width: 100%;
        height: 100%;
        vertical-align: top;
        transform: translate(-50% , -50%);
    }

    .image-project-card-box .introduction .user .sender .name {
        font-size: 12px;
        display: inline-block;
        max-width: 100px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-left: 10px;
    }

    .image-project-card-box .introduction .user .action {

    }

    .image-project-card-box .introduction .user .action .button {
        color: #fff;
    }

    .image-project-card-box .introduction .info {
        display: flex;
        justify-content: space-between;
        height: 40px;
        line-height: 40px;
        font-size: 12px;
        color: #c5c5c5;
    }

    .image-project-card-box .introduction .info > * {
        margin: 0;
    }

    .image-project-card-box .introduction .info .right span {
        margin-right: 8px;
    }

    .image-project-card-box .introduction .info .right span:nth-last-of-type(1) {
        margin-right: 0;
    }

    .image-project-card-box .introduction .info .right .collect-count .run-iconfont {
        margin-top: -2px;
    }

    .image-project-card-box .actions {
        display: flex;
        justify-content: flex-start;
        align-items: flex-start;
        padding: 0 15px 15px 15px;
    }

    .image-project-card-box .actions .button {
        width: 25%;
        height: 30px;
        color: #fff;
        box-sizing: border-box;
    }
</style>
