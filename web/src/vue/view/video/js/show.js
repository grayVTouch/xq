const video = {
    user: {} ,
    videos: [] ,
};
const current = {};

const newest = {
    size: 5 ,
    data: [] ,
};

const recommendData = {
    size: 16 ,
    page: 1 ,
    maxPage: 1 ,
    total: 0 ,
    data: [] ,
};

export default {
    name: "show" ,

    props: ["id"] ,

    data () {
        return {
            dom: {} ,

            val: {} ,

            ins: {} ,

            // 当前视频专题
            video: G.copy(video) ,

            // 是否首次加载视频（索引）
            onceLoadVideosInIndex: true ,

            // 视频专题
            videosInSeries: [] ,

            current: G.copy(current) ,

            newest: G.copy(newest) ,

            recommendData: G.copy(recommendData) ,
        };
    } ,

    computed: {

    } ,

    mounted () {
        this.initDom();
        this.initIns();
        this.getVideo()
            .then(() => {
                // 初始化视频播放器
                this.initVideoPlayer();
            });
        this.getNewestData();
        this.getRecommendData();
        this.recordAccessHistory();
        this.initEvent();
    } ,

    beforeRouteUpdate (to , from , next) {
        this.reload();
    } ,

    methods: {

        getNewestData () {
            this.pending('getNewestData' , true);
            Api.video
                .newest({
                    size: this.newest.size ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandle(res.message);
                        return ;
                    }
                    this.handleAllData(res.data);
                    this.newest.data = res.data;
                })
                .finally(() => {
                    this.pending('getNewestData' , false);
                });
        } ,

        loadMoreRecommendEvent () {
            this.recommendData.page = Math.min(this.recommendData.page + 1 , this.recommendData.maxPage);
            this.getRecommendData();
        } ,

        getRecommendData () {
            if (this.pending('getRecommendData')) {
                return ;
            }
            this.pending('getRecommendData' , true);
            Api.video
                .recommend(this.id , {
                    size: this.recommendData.size ,
                    page: this.recommendData.page ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandle(res.message);
                        return ;
                    }
                    const data = res.data;
                    this.handleAllData(data.data);

                    this.recommendData.size = data.per_page;
                    this.recommendData.maxPage = data.last_page;
                    this.recommendData.page = data.current_page;
                    this.recommendData.total = data.total;

                    // 拼接数据
                    this.recommendData.data = this.recommendData.data.concat(data.data);
                })
                .finally(() => {
                    this.pending('getRecommendData' , false);
                });
        } ,

        recordAccessHistory () {
            this.pending('recordAccessHistory' , true);
            Api.user
                .record(null , {
                    relation_type: 'video' ,
                    relation_id: this.id ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        console.warn(res.message);
                        return ;
                    }
                })
                .finally(() => {
                    this.pending('recordAccessHistory' , false);
                });
        } ,

        incrementViewCount (videoId) {
            this.pending('incrementViewCount' , true);
            Api.video
                .incrementViewCount(videoId)
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        console.log('更新视频观看次数失败');
                        return ;
                    }
                })
                .finally(() => {
                    this.pending('incrementViewCount' , false);
                });
        } ,

        record (videoId , playedDuration , volume , definition , subtitle) {
            this.pending('record' , true);
            Api.video
                .record(videoId , null , {
                    played_duration: playedDuration ,
                    definition: definition ? definition : '' ,
                    subtitle: subtitle ? subtitle : '' ,
                    volume: volume ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        console.log('更新视频观看信息失败');
                        return ;
                    }
                })
                .finally(() => {
                    this.pending('record' , false);
                });
        } ,

        initDom () {
            this.dom.win = G(window);
            this.dom.newest = G(this.$refs.newest);
            this.dom.videoContainer = G(this.$refs['video-container']);

        } ,

        initIns () {

        } ,

        collectionHandle (action) {
            action = Number(action);
            action === 1 ? this.video.collect_count++ : this.video.collect_count--;
            this.video.is_collected = this.video.collect_count > 0;
        } ,

        praiseHandle () {
            if (this.pending('praiseHandle')) {
                return ;
            }
            const self = this;
            const praised = this.video.is_praised ? 0 : 1;
            this.pending('praiseHandle' , true);
            Api.video
                .praiseHandle(this.id , null , {
                    action: praised ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message , res.code , () => {
                            this.praiseHandle();
                        });
                        return ;
                    }
                    this.video.is_praised = praised;
                    praised ? this.video.praise_count++ : this.video.praise_count--;
                })
                .finally(() => {
                    this.pending('praiseHandle' , false);
                });
        } ,

        initVideoPlayer () {
            const self = this;
            const playlist = [];

            const definition = [];
            const subtitle = [];

            this.video.videos.forEach((v1) => {
                definition.push({
                    id: v1.id ,
                    name: v1.definition ,
                    src: v1.src ,
                });
            });

            this.video.video_subtitles.forEach((v1) => {
                subtitle.push({
                    id: v1.id ,
                    name: v1.name ,
                    src: v1.src ,
                });
            });

            playlist.push({
                id: this.video.id ,
                name: this.video.__name__ ,
                index: this.video.index ,
                thumb: this.video.thumb ? this.video.thumb : this.video.thumb_for_program ,
                preview: {
                    src: this.video.preview ,
                    width: this.video.preview_width ,
                    height: this.video.preview_height ,
                    duration: this.video.preview_duration ,
                    count: this.video.preview_line_count ,
                } ,
                definition ,
                subtitle ,
            });

            // 当前播放视频
            const userPlayRecord = this.video.user_play_record;
            let once = true;
            const recordCallback = (context) => {
                const currentVideo      = context.getCurrentVideo();
                const currentDefinition = context.getCurrentDefinition();
                const currentSubtitle   = context.getCurrentSubtitle();
                const currentVolume     = context.getCurrentVolume();
                const currentTime       = context.getCurrentTime();
                this.record(currentVideo.id , currentTime , currentVolume , currentDefinition?.name , currentSubtitle?.name);
            };
            const videoPlayer = new VideoPlayer(this.dom.videoContainer.get(0) , {
                // 海报
                // poster: './res/poster.jpg' ,
                poster: '' ,
                // 单次步进时间，单位：s
                // step: 30 ,
                // 音频步进：0-1
                // soundStep: 0.2 ,
                // 视频源
                playlist ,
                // 当前播放索引
                index: 1 ,
                // 画质
                definition: userPlayRecord?.definition ,
                // 字幕
                subtitle: userPlayRecord?.subtitle ,
                // 当前播放时间点
                currentTime: userPlayRecord?.played_duration ,
                // 静音
                muted: false ,
                // 音量大小
                volume: userPlayRecord?.volume ,
                // 开启字幕
                enableSubtitle: true ,
                // 间隔多长时间执行下述回调
                timeUpdateInterval: 5 ,

                // 时间更新时触发
                onTimeUpdate (index , playedDuration) {
                    recordCallback(this);
                } ,
                // 时间更新时触发
                onDefinitionChange (index , definition) {
                    recordCallback(this);
                } ,
                // 时间更新时触发
                onSubtitleChange (index , subtitle) {
                    recordCallback(this);
                } ,
            });

            const currentVideo = videoPlayer.getCurrentVideo();
            if (currentVideo) {
                // 仅在 video 存在的情况下
                if (!userPlayRecord) {
                    // 如果不存在，则记录
                    const currentDefinition = videoPlayer.getCurrentDefinition();
                    const currentSubtitle   = videoPlayer.getCurrentSubtitle();
                    const volume            = videoPlayer.getCurrentVolume();
                    this.record(currentVideo.id , currentVideo.index , 0 , volume , currentDefinition?.name , currentSubtitle?.name);
                }
                this.incrementViewCount(currentVideo.id);
            }
            this.ins.videoPlayer = videoPlayer;
        } ,

        getVideo () {
            return new Promise((resolve , reject) => {
                this.pending('getVideo' , true);
                Api.video
                    .show(this.id)
                    .then((res) => {
                        if (res.code !== TopContext.code.Success) {
                            this.errorHandleAtHomeChildren(res.message);
                            return ;
                        }
                        const data = res.data;
                        // 数据处理
                        this.handleData(data);
                        this.video = data;

                        this.$nextTick(() => {
                            resolve();
                        });
                    })
                    .finally(() => {
                        this.pending('getVideo' , false);
                    });
            });
        } ,

        handleData (data) {
            data.is_show_preview_video = false;
            data.video_is_loaded = false;
            data.user = data.user ? data.user : {};
            data.videos = data.videos ? data.videos : [];
            data.videos.forEach((v) => {
                v.videos           = v.videos ? v.videos : [];
                v.video_subtitles  = v.video_subtitles ? v.video_subtitles : [];
            });
        } ,

        handleAllData (rows) {
            rows.forEach((row) => {
                this.handleData(row);
            });
        } ,

        initEvent () {
            this.dom.win.on('scroll' , this.scrollWithMiscEvent.bind(this));
        } ,

        showVideo (record) {
            const video = G(this.$refs['video-' + record.id]);
            record.show_type = 'video';
            if (record.video_loaded) {
                video.native('currentTime' , 0);
                video.origin('play');
            } else {
                if (!record.init_video_preview) {
                    record.init_video_preview = true;
                    G.ajax({
                        url: record.simple_preview ,
                        method: 'get' ,
                        // 下载事件
                        progress (e) {
                            if (!e.lengthComputable) {
                                return ;
                            }
                            record.video_loaded_ratio = e.loaded / e.total;
                        } ,
                        success () {
                            video.on('loadeddata' , () => {
                                record.video_loaded = true;
                                record.video_loaded_ratio = 1;
                            });
                            video.native('src' , record.simple_preview);
                        } ,
                    });
                }
            }
        } ,

        hideVideo (record) {
            record.show_type = 'image';
            if (record.video_loaded) {
                const video = G(this.$refs['video-' + record.id]);
                video.origin('pause');
            }
        } ,

        scrollWithMiscEvent () {
            const scrollH = document.documentElement.scrollHeight;
            const clientH = document.documentElement.clientHeight;
            const scrollTop = document.documentElement.scrollTop;
            const fixedTopJudgeH = this.dom.newest.getDocOffsetVal('top');
            const fixedBtmJudgeH = scrollH - TopContext.val.footerH;
            const fixedTopScrollH = scrollTop + TopContext.val.fixedTop;
            const fixedBtmScrollH = clientH + scrollTop;

            if (fixedBtmScrollH >= fixedBtmJudgeH) {
                this._val('fixedTop' , false);
                this._val('fixedBtm' , true);
            } else if (fixedTopScrollH >= fixedTopJudgeH) {
                this._val('fixedTop' , true);
                this._val('fixedBtm' , false);
            } else {
                this._val('fixedTop' , false);
                this._val('fixedBtm' , false);
            }
        } ,

        /**
         * 视频相关
         */
        playPreviewVideo (row) {
            row.is_show_preview_video = true;
            const dom = {
                video: G(this.$refs['video-' + row.id]) ,
                progressBar: G(this.$refs['progress-bar-' + row.id]) ,
                progressBarInner: G(this.$refs['progress-bar-inner-' + row.id]) ,
            };
            const isInit = Number(dom.video.data('is-init'));
            if (isInit === 0) {
                dom.video.data('is-init' , 1);
                G.ajax({
                    url: row.simple_preview ,
                    method: 'get' ,
                    // 下载事件
                    progress (e) {
                        if (!e.lengthComputable) {
                            return ;
                        }
                        row.video_loaded_ratio = e.loaded / e.total;
                    } ,
                    success () {
                        dom.video.on('loadeddata' , () => {
                            row.video_is_loaded = true;
                            row.video_loaded_ratio = 1;
                            if (row.is_show_preview_video) {
                                dom.video.origin('play');
                            }
                        });
                        dom.video.native('src' , row.simple_preview);
                    } ,
                });
            } else {
                // 播放
                if (row.video_is_loaded) {
                    dom.video.native('currentTime' , 0);
                    dom.video.origin('play');
                }
            }
        } ,

        pausePreviewVideo (row) {
            row.is_show_preview_video = false;
            const video = G(this.$refs['video-' + row.id]);
            const isInit = Number(video.data('is-init'));
            if (isInit !== 1 || !row.video_is_loaded) {
                return ;
            }
            video.native('currentTime' , 0);
            video.origin('pause');
        } ,

        linkToVideo (row) {
            const url = this.genUrl('/video/' + row.id + '/show');
            this.openWindow(url , '_self');
        } ,

        focusHandle () {
            if (this.pending('focusHandle')) {
                return ;
            }
            this.pending('focusHandle' , true);
            const action = this.video.user.focused ? 0 : 1;
            Api.user
                .focusHandle(null , {
                    user_id: this.video.user_id ,
                    action ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message , res.code , () => {
                            this.focusHandle();
                        });
                        return ;
                    }
                    // this.getData();

                    this.video.user.focused = action;
                    if (action) {
                        this.video.user.focus_me_user_count++;
                    } else {
                        this.video.user.focus_me_user_count--;
                    }
                })
                .finally(() => {
                    this.pending('focusHandle' , false);
                });

        } ,
    } ,
}
