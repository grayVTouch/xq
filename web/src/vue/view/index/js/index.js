const imageProjects = {
    size: 16 ,
    data: [] ,
    type: 'pro' ,
};

const videoProjects = {
    size: 16 ,
    data: [] ,
    type: 'pro' ,
};

const videos = {
    size: 10 ,
    data: [] ,
};

const images = {
    size: 12 ,
    data: [] ,
};

export default {
    name: "index" ,
    data () {
        return {
            dom: {} ,
            ins: {} ,
            val: {
                size: 16 ,
                pending: {} ,
            } ,

            imageProjects: G.copy(imageProjects) ,

            videoProjects: G.copy(videoProjects) ,

            images: G.copy(images) ,

            videos: G.copy(videos) ,

            homeSlideshow: [] ,

            background: {
                duration: 5 * 1000 ,
                image: '' ,
                index: 0 ,
            } ,

            // 最热门图片
            hotImages: [] ,

            // 最新图片
            newestImages: [] ,

            group: {
                imageProject: {
                    action: {
                        scrollWidth: 0 ,
                        clientWidth: 0 ,
                        translateX: 0 ,
                        maxTranslateX: 0 ,
                        minTranslateX: 0 ,
                    } ,
                    curTag: 'newest' ,
                    tag: {
                        size: 5 ,
                        type: 'pro' ,
                        data: [] ,
                    } ,
                } ,

                videoProject: {
                    action: {
                        scrollWidth: 0 ,
                        clientWidth: 0 ,
                        translateX: 0 ,
                        maxTranslateX: 0 ,
                        minTranslateX: 0 ,
                    } ,
                    curTag: 'newest' ,
                    tag: {
                        data: [] ,
                        size: 5 ,
                    } ,
                } ,

                image: {
                    curTag: 'newest' ,
                    tag: {
                        data: [] ,
                        size: 5 ,
                    } ,
                } ,

                video: {
                    curTag: 'newest' ,
                    tag: {
                        data: [] ,
                        size: 5 ,
                    } ,
                } ,
            } ,

        };
    } ,

    // beforeRouteLeave (to , from , next) {
    //     if (this.ins.picPlayTransform instanceof PicPlay_Transform) {
    //         this.ins.picPlayTransform.clearTimer();
    //     }
    //     next();
    // } ,



    mounted () {
        this.initDom();
        // 首页轮播图
        this.getHomeSlideshow();
        // 热点图片专题
        this.hotInImageProject()
            .then((data) => {
                this.hotImages = data;
            });

        // 最新图片专题
        this.newestInImageProject();
        this.hotTagsInImageProject();

        // 视频专题
        this.newestInVideoProject();
        this.hotTagsInVideoProject();

        // 杂项图片
        this.newestInImage();
        this.hotTagsInImage();

        // 杂项视频
        this.newestInVideo();
        this.hotTagsInVideo();
    } ,

    methods: {

        // 图片点赞
        praiseImageProject (row) {
            if (this.pending('praiseImageProject')) {
                return ;
            }
            const self = this;
            const praised = row.is_praised ? 0 : 1;
            this.pending('praiseImageProject' , true);
            Api.user
                .praiseHandle(null , {
                    relation_type: 'image_project' ,
                    relation_id: row.id ,
                    action: praised ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message , res.code , () => {
                            this.praiseImageProject(row)
                        });
                        return ;
                    }
                    row.is_praised = praised;
                    praised ? row.praise_count++ : row.praise_count--;
                })
                .finally(() => {
                    this.pending('praiseImageProject' , false);
                });
        } ,

        // 图片点赞
        praiseImage (row) {
            if (this.pending('praiseImage')) {
                return ;
            }
            const self = this;
            const praised = row.is_praised ? 0 : 1;
            this.pending('praiseImage' , true);
            Api.image
                .praiseHandle(row.id , null , {
                    action: praised ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message , res.code , () => {
                            this.praiseImage(row)
                        });
                        return ;
                    }
                    row.is_praised = praised;
                    praised ? row.praise_count++ : row.praise_count--;
                })
                .finally(() => {
                    this.pending('praiseImage' , false);
                });
        } ,

        // 视频点赞
        praiseVideoProject (row) {
            if (this.pending('praiseImageProject')) {
                return ;
            }
            const praised = row.is_praised ? 0 : 1;
            this.pending('praiseVideoProject' , true);
            Api.videoProject
                .praiseHandle(row.id , null , {
                    action: praised ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message , res.code , () => {
                            this.praiseVideoProject(row)
                        });
                        return ;
                    }
                    row.is_praised = praised;
                    praised ? row.praise_count++ : row.praise_count--;
                })
                .finally(() => {
                    this.pending('praiseVideoProject' , false);
                });
        } ,

        findImageProjectByImageProjectId (imageProjectId , callback) {
            this.pending('findImageProjectByImageProjectId' , true);
            Api.imageProject.show(imageProjectId.then((res) => {
                this.pending('findImageProjectByImageProjectId' , false);
                if (res.code !== TopContext.code.Success) {
                    this.message('error' , res.message);
                    G.invoke(callback , null , false);
                    return ;
                }
                data.user = data.user ? data.user : {};
                data.subject = data.subject ? data.subject : {};
                data.images = data.images ? data.images : [];
                data.tags = data.tags ? data.tags : [];
                data.module = data.module ? data.module : [];

                this.$nextTick(() => {
                    G.invoke(callback , null , true);
                });
            }));
        } ,

        // 图片专题-最新图片
        newestInImage () {
            this.pending('image' , true);
            this.group.image.curTag = 'newest';
            Api.image
                .newest({
                    size: this.images.size ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message , res.code);
                        return ;
                    }
                    this.images.data = res.data;

                })
                .finally(() => {
                    this.pending('image' , false);
                });
        } ,

        newestInVideo () {
            this.pending('video' , true);
            this.group.video.curTag = 'newest';
            Api.video
                .newest({
                    size: this.videos.size ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message , res.code);
                        return ;
                    }
                    this.videos.data = res.data;
                })
                .finally(() => {
                    this.pending('video' , false);
                });
        } ,

        // 图片专题-最新图片
        newestInImageProject () {
            return new Promise((resolve , reject) => {
                this.pending('imageProject' , true);
                this.group.imageProject.curTag = 'newest';
                Api.imageProject
                    .newest({
                        size: this.imageProjects.size ,
                        type: this.imageProjects.type ,
                    })
                    .then((res) => {
                        if (res.code !== TopContext.code.Success) {
                            this.errorHandleAtHomeChildren(res.message , res.code);
                            reject();
                            return ;
                        }
                        this.newestImages = res.data;
                        this.imageProjects.data = res.data;
                        this.$nextTick(() => {
                            this.initContentGroupContainerWidthByGroup('imageProject');
                        });
                        resolve();
                    })
                    .finally(() => {
                        this.pending('imageProject' , false);
                    });
            });
        } ,


        // 图片-最热门的图片
        hotInImageProject () {
            return new Promise((resolve) => {
                this.pending('imageProject' , true);
                Api.imageProject
                    .hot({
                        size: this.imageProjects.size ,
                        type: this.imageProjects.type ,
                    })
                    .then((res) => {
                        if (res.code !== TopContext.code.Success) {
                            this.errorHandleAtHomeChildren(res.message , res.code);
                            return ;
                        }
                        resolve(res.data);
                    })
                    .finally(() => {
                        this.pending('imageProject' , false);
                    });
            });
        } ,

        hotInImage () {
            this.group.image.curTag = 'hot';
            this.pending('image' , true);
            Api.image
                .hot({
                    size: this.images.size ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message , res.code);
                        return ;
                    }
                    this.images.data = res.data;
                })
                .finally(() => {
                    this.pending('image' , false);
                });
        } ,

        getHotImageProject () {
            this.group.imageProject.curTag = 'hot';
            this.hotInImageProject()
                .then((data) => {
                    this.imageProjects.data = data;
                    this.$nextTick(() => {
                        this.initContentGroupContainerWidthByGroup('imageProject');
                    });
                });
        } ,

        // 图片-按标签分类获取的图片
        getImageProjectsByTagId (tagId) {
            this.pending('imageProject' , true);
            this.group.imageProject.curTag = 'tag_' + tagId;
            Api.imageProject
                .getByTagId({
                    tag_id: tagId ,
                    size: this.imageProjects.size ,
                    type:  this.imageProjects.type ,
                }).then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message , res.code);
                        return ;
                    }
                    this.imageProjects.data = res.data;
                    this.$nextTick(() => {
                        this.initContentGroupContainerWidthByGroup('imageProject');
                    });
                })
                .finally(() => {
                    this.pending('imageProject' , false);
                });
        } ,

        // 图片-按标签分类获取的图片
        hotTagsInImageProject () {
            return new Promise((resolve , reject) => {
                this.pending('hotTagsInImageProject' , true);
                Api.imageProject
                    .hotTags({
                        type: this.group.imageProject.tag.type ,
                        size: this.group.imageProject.tag.size ,
                    })
                    .then((res) => {
                        if (res.code !== TopContext.code.Success) {
                            this.errorHandleAtHomeChildren(res.message , res.code);
                            reject();
                            return ;
                        }
                        this.group.imageProject.tag.data = res.data;
                        resolve();
                    })
                    .finally(() => {
                        this.pending('hotTagsInImageProject' , false);
                    });
            });
        } ,

        // 图片-按标签分类获取的图片
        hotTagsInImage () {
            this.pending('hotTagsInImage' , true);
            Api.image
                .hotTags({
                    type: this.group.image.tag.type ,
                    size: this.group.image.tag.size ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message , res.code);
                        return ;
                    }
                    this.group.image.tag.data = res.data;
                })
                .finally(() => {
                    this.pending('hotTagsInImage' , false);
                });
        } ,

        hotTagsInVideo () {
            this.pending('hotTagsInVideo' , true);
            Api.video
                .hotTags({
                    type: this.group.video.tag.type ,
                    size: this.group.video.tag.size ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message , res.code);
                        return ;
                    }
                    this.group.video.tag.data = res.data;
                })
                .finally(() => {
                    this.pending('hotTagsInVideo' , false);
                });
        } ,

        newestInVideoProject () {
            this.pending('videoProject' , true);
            this.group.videoProject.curTag = 'newest';
            Api.videoProject
                .newest({
                    size: this.videoProjects.size ,
                    type: this.videoProjects.type ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message);
                        return ;
                    }
                    this.videoProjects.data = res.data;
                    this.$nextTick(() => {
                        this.initContentGroupContainerWidthByGroup('videoProject');
                    });
                })
                .finally(() => {
                    this.pending('videoProject' , false);
                });
        } ,

        hotInVideoProject () {
            this.group.videoProject.curTag = 'hot';
            this.pending('videoProject' , true);
            Api.videoProject
                .hot({
                    size: this.videoProjects.size ,
                })
                .then((res) => {
                    this.pending('videoProject' , false);
                    if (res.code !== TopContext.code.Success) {
                        this.message('error' , res.message);
                        return ;
                    }
                    this.videoProjects.data = res.data;
                    this.$nextTick(() => {
                        this.initContentGroupContainerWidthByGroup('videoProject');
                    });
                });
        } ,

        hotInVideo () {
            this.group.video.curTag = 'hot';
            this.pending('video' , true);
            Api.video
                .hot({
                    size: this.videos.size ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandle(res.message);
                        return ;
                    }
                    this.handleVideosData(res.data);
                    this.videos.data = res.data;
                })
                .finally(() => {
                    this.pending('video' , false);
                })
        } ,

        getVideoProjectsByTagId (tagId) {
            this.group.videoProject.curTag = 'tag_' + tagId;
            this.pending('videoProject' , true);
            Api.videoProject
                .getByTagId({
                    size: this.videoProjects.size ,
                    tag_id: tagId ,
                })
                .then((res) => {
                    this.pending('videoProject' , false);
                    if (res.code !== TopContext.code.Success) {
                        this.message('error' , res.message);
                        return ;
                    }
                    this.videoProjects.data = res.data;
                    this.$nextTick(() => {
                        this.initContentGroupContainerWidthByGroup('videoProject');
                    });
                });
        } ,

        getVideosByTagId (tagId) {
            this.group.video.curTag = 'tag_' + tagId;
            this.pending('video' , true);
            Api.video
                .getByTagId({
                    size: this.videos.size ,
                    tag_id: tagId ,
                })
                .then((res) => {
                    this.pending('video' , false);
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandle(res.message);
                        return ;
                    }
                    this.handleVideosData(res.data);
                    this.videos.data = res.data;
                });
        } ,

        getImagesByTagId (tagId) {
            this.group.image.curTag = 'tag_' + tagId;
            this.pending('image' , true);
            Api.image
                .getByTagId({
                    size: this.images.size ,
                    tag_id: tagId ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.message('error' , res.message);
                        return ;
                    }
                    this.images.data = res.data;
                })
                .finally(() => {
                    this.pending('image' , false);
                })
        } ,

        // 标签-视频专题
        hotTagsInVideoProject () {
            this.pending('hotTagsInVideoProject' , true);
            Api.videoProject
                .hotTags({
                    size: this.group.videoProject.tag.size ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandle(res.message);
                        return ;
                    }
                    this.group.videoProject.tag.data = res.data;
                })
                .finally(() => {
                    this.pending('hotTagsInVideoProject' , false);
                });
        } ,

        // 首页幻灯片
        getHomeSlideshow () {
            Api.slideshow
                .home()
                .then((res) => {
                if (res.code !== TopContext.code.Success) {
                    this.errorHandleAtHomeChildren(res.message , res.code);
                    return ;
                }
                this.homeSlideshow = res.data;
                this.$nextTick(() => {
                    this.initPicPlay_Transform();
                    this.initBackground();
                });
            });
        } ,

        initBackground () {
            const playBackground = () => {
                this.background.image = this.homeSlideshow[this.background.index].__path__;
                this.background.index++;
                if (this.background.index >= this.homeSlideshow.length) {
                    this.background.index = 0;
                }
                // 定时播放
                G.timer.time(playBackground.bind(this) , this.background.duration);
            };
            playBackground();
        } ,

        initDom () {
            this.dom.slidebar = G(this.$refs.slidebar);
        } ,

        // 首页幻灯片
        initPicPlay_Transform () {
            this.ins.slidebar = new PicPlay_Transform(this.dom.slidebar.get(0) , {
                // 动画过度时间
                time: 400,
                // 定时器时间
                duration: this.background.duration ,
                timer: true ,
            })
        } ,

        prevByGroup (group) {
            if (this.group[group].action.translateX >= this.group[group].action.maxTranslateX) {
                return ;
            }
            this.group[group].action.translateX += this.group[group].action.clientWidth;
            const inner = G(this.$refs['inner-for-' + group]);
            inner.css({
                transform: 'translateX(' + this.group[group].action.translateX + 'px)'
            });
        } ,

        nextByGroup (group) {
            if (this.group[group].action.translateX <= this.group[group].action.minTranslateX) {
                return ;
            }
            this.group[group].action.translateX -= this.group[group].action.clientWidth;
            const inner = G(this.$refs['inner-for-' + group]);
            inner.css({
                transform: 'translateX(' + this.group[group].action.translateX + 'px)'
            });
        } ,

        /**
         * 初始化内容分组的容器宽度
         *
         * @param group image-subject | video-subject
         */
        initContentGroupContainerWidthByGroup (group) {
            const list  = G(this.$refs['list-for-' + group]);
            const inner = G(this.$refs['inner-for-' + group]);
            const items = inner.children({
                className: 'item' ,
                tagName: 'div' ,
            });
            const listW = list.width();
            let width = 0;
            items.each((item) => {
                item = G(item);
                width += item.getTW();
            });
            width = width < listW ? listW : width;
            inner.css({
                width: width + 'px' ,
                transform: 'translateX(0px)'
            });
            this.group[group].action.translateX = 0;
            this.group[group].action.scrollWidth = width;
            this.group[group].action.clientWidth = parseInt(list.width('content-box'));
            this.group[group].action.maxTranslateX = 0;
            this.group[group].action.minTranslateX = -(Math.ceil(this.group[group].action.scrollWidth / this.group[group].action.clientWidth) - 1) * this.group[group].action.clientWidth;
        } ,

    } ,
}
