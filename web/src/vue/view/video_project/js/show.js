const videoProject = {
    // 当前播放的视频
    current: {} ,
    videos: [] ,
};

const indexRange = {
    // 当前索引类型
    current: {
        min: 0 ,
        max: 0 ,
        value: ''
    } ,
    // 分割的集数
    split: 30 ,
    // 正常显示的剧集分组数量
    indexGroupCount: 3 ,
    // 剧集分组
    group: {
        index: [
            // {
            //     min: 1 ,
            //     max: 30 ,
            // } ,
        ] ,
        other: [] ,
    } ,

    videos: [] ,
};

export default {
    name: "show" ,

    props: ["id"] ,

    data () {
        return {
            dom: {} ,

            val: {
                // 加载更多剧集
                loadMoreIndex: false ,
            } ,

            ins: {} ,

            // 当前视频专题
            videoProject: G.copy(videoProject) ,

            // 当前索引范围
            indexRange: G.copy(indexRange) ,

            // 是否首次加载视频（索引）
            onceLoadVideosInIndex: true ,

            // 视频专题
            videoProjectsInSeries: [] ,
        };
    } ,

    computed: {

    } ,

    mounted () {
        this.initDom();
        this.initIns();
        this.getVideoProject()
            .then(() => {
                // 生成剧集信息
                this.generateIndexRange(this.videoProject.min_index , this.videoProject.max_index);
                // 初始化视频播放器
                this.initVideoPlayer();
                // 获取系列下的视频专题
                this.getVideoProjectsInSeries();
            });
        this.recordAccessHistory();
        this.initEvent();
    } ,

    beforeRouteUpdate (to , from , next) {
        this.reload();
    } ,

    methods: {

        recordAccessHistory () {
            this.pending('recordAccessHistory' , true);
            Api.user
                .record(null , {
                    relation_type: 'video_project' ,
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

        record (videoId , index , playedDuration , volume , definition , subtitle) {
            this.pending('record' , true);
            Api.video
                .record(videoId , null , {
                    index ,
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
            this.dom.videoContainer = G(this.$refs['video-container']);

        } ,

        initIns () {

        } ,

        collectionHandle (action) {
            action = Number(action);
            this.videoProject.is_collected = action;
            action === 1 ? this.videoProject.collect_count++ : this.videoProject.collect_count--;
            this.videoProject.is_collected = this.videoProject.collect_count > 0;
        } ,

        praiseHandle () {
            if (this.pending('praiseHandle')) {
                return ;
            }
            const self = this;
            const praised = this.videoProject.is_praised ? 0 : 1;
            this.pending('praiseHandle' , true);
            Api.videoProject
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
                    this.videoProject.is_praised = praised;
                    praised ? this.videoProject.praise_count++ : this.videoProject.praise_count--;
                })
                .finally(() => {
                    this.pending('praiseHandle' , false);
                });
        } ,

        initVideoPlayer () {
            const self = this;
            const playlist = [];
            this.videoProject.videos.forEach((v) => {
                const definition = [];
                const subtitle = [];

                v.videos.forEach((v1) => {
                    definition.push({
                        id: v1.id ,
                        name: v1.definition ,
                        src: v1.src ,
                    });
                });

                v.video_subtitles.forEach((v1) => {
                    subtitle.push({
                        id: v1.id ,
                        name: v1.name ,
                        src: v1.src ,
                    });
                });

                playlist.push({
                    id: v.id ,
                    name: v.__name__ ,
                    index: v.index ,
                    thumb: v.thumb ? v.thumb : v.thumb_for_program ,
                    preview: {
                        src: v.preview ,
                        width: v.preview_width ,
                        height: v.preview_height ,
                        duration: v.preview_duration ,
                        count: v.preview_line_count ,
                    } ,
                    definition ,
                    subtitle ,
                });
            });
            const findVideoProjectByIndex = (index) => {
                for (let i = 0; i < playlist.length; ++i)
                {
                    const cur = playlist[i];
                    if (cur.index == index) {
                        return cur;
                    }
                }
                throw new Error(`未找到当前索引【${index}】对应的视频记录`);
            };
            const findVideoSrcByName = (name) => {
                for (let i = 0; i < playlist.length; ++i)
                {
                    const cur = playlist[i];
                    if (cur.index == index) {
                        return cur;
                    }
                }
                throw new Error(`未找到当前索引【${index}】对应的视频记录`);
            };
            // 当前播放视频
            const userPlayRecord = this.videoProject.user_play_record;
            const index = playlist.length > 0 ? playlist[0].index : 1;
            let once = true;
            const recordCallback = (context) => {
                const currentVideo      = context.getCurrentVideo();
                const currentDefinition = context.getCurrentDefinition();
                const currentSubtitle   = context.getCurrentSubtitle();
                const currentVolume     = context.getCurrentVolume();
                const currentTime       = context.getCurrentTime();
                this.record(currentVideo.id , currentVideo.index , currentTime , currentVolume , currentDefinition?.name , currentSubtitle?.name);
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
                index: userPlayRecord?.index ,
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
                minIndex: this.videoProject.min_index ,
                maxIndex: this.videoProject.max_index ,
                // definition: '480P' ,
                // 当视频播放结束时的回调
                ended () {
                    // 自动播放下一集
                    this.next();
                } ,

                switch (index) {
                    if (userPlayRecord && userPlayRecord.index !== index) {
                        // 检查当前记录的内容是否是当前的索引
                        // 如果不是当前记录的视频索引，那么应该切换成给定的索引
                        const volume = this.getCurrentVolume();
                        const currentVideo = this.getCurrentVideo();
                        const currentDefinition = this.getCurrentDefinition();
                        const currentSubtitle   = this.getCurrentSubtitle();
                        self.record(currentVideo.id , currentVideo.index , 0 , volume , currentDefinition?.name , currentSubtitle?.name);
                    }
                    once = false;
                    for (let i = 0; i < self.videoProject.videos.length; ++i)
                    {
                        const cur = self.videoProject.videos[i];
                        if (index === cur.index) {
                            self.videoProject.current = cur;
                            break;
                        }
                    }
                    // 选中范围
                    self.selectedIndexRange(index);
                    if (self.onceLoadVideosInIndex) {
                        self.onceLoadVideosInIndex = false;
                        self.videosInRange(self.indexRange.current.min , self.indexRange.current.max);
                    }
                } ,
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

        getVideoProject () {
            return new Promise((resolve , reject) => {
                this.pending('getVideoProject' , true);
                Api.videoProject
                    .show(this.id)
                    .then((res) => {
                        if (res.code !== TopContext.code.Success) {
                            this.errorHandleAtHomeChildren(res.message);
                            return ;
                        }
                        const data = res.data;
                        // 数据处理
                        this.handleData(data);
                        this.videoProject = data;

                        this.$nextTick(() => {
                            resolve();
                        });
                    })
                    .finally(() => {
                        this.pending('getVideoProject' , false);
                    });
            });
        } ,

        handleData (data) {
            data.current = {};
            data.videos = data.videos ? data.videos : [];
            data.videos.forEach((v) => {
                v.videos           = v.videos ? v.videos : [];
                v.video_subtitles  = v.video_subtitles ? v.video_subtitles : [];
            });
        } ,

        generateIndexRange (min , max) {
            let i = min;
            let obj;
            let groupCount = 1;
            while (i <= max)
            {
                if (!obj) {
                    obj = {
                        min: i ,
                        max: i ,
                    };
                }
                if (i >= min + groupCount * this.indexRange.split - 1 || i === max) {
                    obj.max = i;
                    if (groupCount <= this.indexRange.indexGroupCount) {
                        this.indexRange.group.index.push(obj);
                    } else {
                        this.indexRange.group.other.push(obj);
                    }
                    groupCount++;
                    obj = null;
                }
                i++;
            }
        } ,

        // 选中其中一个索引范围
        selectedIndexRange (index) {
            const indexRange = this.indexRange.group.index.concat(this.indexRange.group.other);
            for (let i = 0; i < indexRange.length; ++i)
            {
                const cur = indexRange[i];
                if (index >= cur.min && index <= cur.max) {
                    this.indexRange.current = {
                        min: cur.min ,
                        max: cur.max ,
                        value: cur.min + '-' + cur.max ,
                        more: this.isIndexRangeInMore(cur.min , cur.max) ,
                    };
                    break;
                }
            }
        } ,

        showMoreIndex () {
            this._val('loadMoreIndex' , true);
        } ,

        hideMoreIndex () {
            this._val('loadMoreIndex' , false);
        } ,

        initEvent () {
            this.dom.win.on('click' , this.hideMoreIndex.bind(this));
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

        videosInRange (min , max) {
            this.pending('videosInRange' , true);
            // 请求数据
            Api.videoProject
                .videosInRange(this.videoProject.id , {
                    min ,
                    max ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandle(res.message)
                            .then((keep) => {
                                if (!keep) {
                                    return ;
                                }
                                this.videosInRange(min , max);
                            });
                        return ;
                    }
                    const data = res.data;
                    data.forEach((v) => {
                        // 当前显示的元素类型
                        v.show_type = 'image';
                        // 视频是否加载完成
                        v.video_loaded = false;
                        // 视频是否已经初始化（避免重复初始化）
                        v.init_video_preview = false;
                        // 视频加载进度
                        v.video_loaded_ratio = 0;
                    });
                    this.indexRange.videos = data;
                })
                .finally(() => {
                    this.pending('videosInRange' , false);
                });
        } ,

        isIndexRangeInMore (min , max) {
            for (let i = 0; i < this.indexRange.group.index.length; ++i)
            {
                const cur = this.indexRange.group.index[i];
                if (cur.min == min && cur.max == max) {
                    return false;
                }
            }
            return true;
        } ,

        switchIndexRangeByMinAndMax (min , max) {
            this.indexRange.current = {
                min ,
                max ,
                value:  min + '-' + max ,
                more: this.isIndexRangeInMore(min , max) ,
            };
            this.videosInRange(min , max);
            this.hideMoreIndex();
        } ,

        // 获取视频系列
        getVideoProjectsInSeries () {
            if (this.videoProject.video_series_id <= 0) {
                return ;
            }
            this.pending('getVideoProjectsInSeries' , true);
            Api.videoProject
                .vdieoProjectFilterByVideoSeriesId({
                    video_project_id: this.videoProject.id ,
                    video_series_id: this.videoProject.video_series_id ,
                })
                .then((res) => {
                    this.pending('getVideoProjectsInSeries' , false);
                    if (res.code !== TopContext.code.Success) {
                        return this.errorHandle(res.message)
                            .then((keep) => {
                                if (!keep) {
                                    return ;
                                }
                                this.getVideoProjectsInSeries();
                            });
                    }
                    this.videoProjectsInSeries = res.data;
                });
        } ,

    } ,
}
