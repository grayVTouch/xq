<template>
    <a
            class="card-box video-card-box"
            target="_blank"
            :href="genUrl(`/video/${row.id}/show`)"
    >
        <div class="preview"
             @mouseenter="playPreviewVideo"
             @mouseleave="pausePreviewVideo"
        >
            <div class="preview-image">
                <img
                        :data-src="row.__thumb__ ? row.__thumb__ : TopContext.res.notFound"
                        v-judge-img-size
                        class="image judge-img-size"
                        alt=""
                >
            </div>
            <div
                    class="preview-video"
                    :ref="`preview-video-${row.id}`"
                    v-show="myValue.isShowPreviewVideo"
            >
                <div class="mask"></div>
                <video
                        :ref="`video-${row.id}`"
                        class="video"
                        loop
                        muted
                ></video>
                <!-- 加载进度条 -->
                <div :ref="`progress-bar-${row.id}`" class="progress-bar" v-if="!myValue.videoIsLoaded">
                    <div :ref="`progress-bar-inner-${row.id}`"
                         :style="`width: ${myValue.videoLoadedRatio}%`"
                         class="inner"></div>
                </div>
            </div>
        </div>
        <div class="info">
            <!-- 标签 -->
            <div class="tags m-b-5">
                <span class="ico"><my-icon icon="icontag" size="18" /></span>
                <a class="tag" target="_blank" v-for="tag in row.tags" :href="genUrl(`/video/search?tag_id=${tag.tag_id}`)">{{ tag.name }}</a>
            </div>
            <div class="name m-b-10">{{ row.name }}</div>
            <div class="desc">
                <div class="left"><my-icon icon="shijian" class="ico" mode="right" /> {{ row.format_time }}</div>
                <div class="right">
                    <span class="view-count"><my-icon icon="chakan" mode="right" />{{ row.view_count }}</span>
                    <span class="praise-count"><my-icon icon="shoucang2" mode="right" />{{ row.praise_count }}</span>
                    <span class="collect-count" v-if="state().user"><my-icon icon="shoucang6" mode="right" />{{ row.collect_count }}</span>
                </div>
            </div>
        </div>
    </a>
</template>

<script>
    export default {
        name: "my_video_card_box" ,

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
                        user: {} ,
                        tags: [] ,
                        format_time: '' ,
                        created_at: '' ,
                    };
                } ,
            } ,
        } ,

        data () {
            return {
                myValue: {
                    isShowPreviewVideo: false ,
                    videoIsLoaded: false ,
                    videoLoadedRatio: 0 ,
                    once: true ,
                } ,
            };
        } ,

        methods: {
            /**
             * 视频相关
             */
            playPreviewVideo () {
                const self = this;
                const row = this.row;

                this.myValue.isShowPreviewVideo = true;

                const dom = {
                    video: G(this.$refs['video-' + row.id]) ,
                    progressBar: G(this.$refs['progress-bar-' + row.id]) ,
                    progressBarInner: G(this.$refs['progress-bar-inner-' + row.id]) ,
                };
                if (this.myValue.once) {
                    this.myValue.once = false;
                    G.ajax({
                        url: row.simple_preview ,
                        method: 'get' ,
                        // 下载事件
                        progress (e) {
                            if (!e.lengthComputable) {
                                return ;
                            }
                            self.myValue.videoLoadedRatio = e.loaded / e.total * 100;
                        } ,
                        success () {
                            dom.video.on('loadeddata' , () => {
                                self.myValue.videoIsLoaded = true;
                                self.myValue.videoLoadedRatio = 1;
                                if (self.myValue.isShowPreviewVideo) {
                                    dom.video.origin('play');
                                }
                            });
                            dom.video.native('src' , row.simple_preview);
                        } ,
                    });
                } else {
                    // 播放
                    if (this.myValue.videoIsLoaded) {
                        dom.video.native('currentTime' , 0);
                        dom.video.origin('play');
                    }
                }
            } ,

            pausePreviewVideo () {
                const row = this.row;

                this.myValue.isShowPreviewVideo = false;
                if (this.myValue.once || !this.myValue.videoIsLoaded) {
                    return ;
                }
                const video = G(this.$refs['video-' + row.id]);
                video.native('currentTime' , 0);
                video.origin('pause');
            } ,

        } ,

        watch: {
            row: {
                immediate: true ,
                handler (newVal , oldVal) {
                    if (!newVal) {
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
     * ****************
     * 视频盒子
     * ****************
     */

    .video-card-box {
        width: 248px;
        cursor: pointer;
        background-color: var(--card-box-background-color);
        transition: all 0.3s ease;
        display: block;
    }

    .video-card-box:hover {
        background-color: var(--card-box-background-color-hover);
        /*transform: translateY(-10px);*/
        /*box-shadow: 0 0 5px 0 #fff;*/
    }

    .video-card-box .preview {
        height: 140px;
        overflow: hidden;
        position: relative;
    }

    .video-card-box .preview .preview-image {
        position: relative;
        height: inherit;
    }

    .video-card-box .preview .preview-video {
        position: absolute;
        width: 100%;
        height: 100%;
        left: 0;
        top: 0;
    }

    .video-card-box .preview .preview-video .mask {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: transparent;
        z-index: 4;
    }

    .video-card-box .preview .preview-video .video {
        width: inherit;
        height: inherit;
    }

    .video-card-box .preview .preview-video .progress-bar {
        position: absolute;
        left: 0;
        top: 0;
        z-index: 2;
        width: 100%;
        height: 4px;
        /*background-color: blue;*/
    }

    .video-card-box .preview .preview-video .progress-bar .inner {
        background-color: red;
        /*width: 30%;*/
        height: inherit;
        transition: all 0.3s ease;
    }

    .video-card-box .info {
        padding: 10px 15px;
    }

    .video-card-box .info .tags {
        height: 40px;
        line-height: 40px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .video-card-box .info .tags .ico {
        padding-right: 5px;
    }

    .video-card-box .info .tags .tag {
        padding: 3px 10px;
        background-color: #5d5d5d;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.3s;
        margin-left: 5px;
        line-height: normal;
        display: inline-block;
    }

    .video-card-box .info .tags .tag:nth-of-type(1) {
        margin-left: 0;
    }

    .video-card-box .info .tags .tag:hover {
        background-color: #828282;
    }

    .video-card-box .info .name {
        overflow: hidden;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        text-overflow: ellipsis;
        font-size: 14px;
        height: 38px;
    }

    .video-card-box .info .desc {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
        color: #c5c5c5;
        height: 20px;
    }

    .video-card-box .info .desc > * {
        margin: 0;
    }

    .video-card-box .info .desc .right span {
        margin-right: 8px;
    }

    .video-card-box .info .desc .right span:nth-last-of-type(1) {
        margin-right: 0;
    }

    .video-card-box .info .desc .right .collect-count .run-iconfont {
        margin-top: -2px;
    }

</style>
