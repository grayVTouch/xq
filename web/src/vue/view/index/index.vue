<template>
    <div class="view">

        <!-- 焦点栏目 -->
        <div class="focus-bar">

            <div class="background">
                <!-- 背景轮播图 -->
                <div class="bg-image" :style="'background-image: url(\'' + background.image + '\')'"></div>
                <!-- 背景轮播图遮罩层 -->
                <div class="mask"></div>
            </div>

            <div class="content">
                <div class="slidebar" ref="slidebar">

                    <div class="pic-play-transform">
                        <div class="images">
                            <a class="link" v-for="v in homeSlideshow" :key="v.id" :href='v.link'>
                                <img :src="v.src"
                                     class="image"
                                     alt=""
                                >
                            </a>
                        </div>
                        <div class="index"></div>
                        <div class="action prev"><i class="run-iconfont run-iconfont-prev01"></i></div>
                        <div class="action next"><i class="run-iconfont run-iconfont-next01"></i></div>
                    </div>

                </div>

                <div class="box">
                    <div class="inner">
                        <a class="item" v-for="(v,k) in hotImages" :key="v.id" v-if="k < 6" target="_blank" :href="genUrl(`/image_project/${v.id}/show`)">
                            <img :data-src="v.thumb ? v.thumb : TopContext.res.notFound" alt="" v-judge-img-size class="image judge-img-size">
                            <div class="info">
                                <h5 class="title">{{ v.name  }}</h5>
                                <p class="desc">{{ v.description }}</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <!-- 内容分组 -->
        <div class="content-group">

            <!-- 图片 -->
            <div class="group group-for-imageProject">
                <!-- 导航 -->
                <div class="run-action-title">
                    <div class="left">图片专题</div>
                    <div class="right">
                        <div class="tags">
                            <my-button class="tag" :class="{cur: group.imageProject.curTag === 'newest'}" @click="newestInImageProject">最新</my-button>
                            <my-button class="tag" :class="{cur: group.imageProject.curTag === 'hot'}" @click="getHotImageProject">热门</my-button>
                            <my-button class="tag" v-for="v in group.imageProject.tag.data" :key="v.id" :class="{cur: group.imageProject.curTag === 'tag_' + v.tag_id}" @click="getImageProjectsByTagId(v.tag_id)">{{ v.name }}</my-button>
                            <my-link class="tag" :href="genUrl('/image_project')">更多</my-link>
                        </div>
                        <div class="operation">
                            <my-button class="prev" :class="{disabled: group.imageProject.action.translateX === group.imageProject.action.maxTranslateX}" @click="prevByGroup('imageProject')"><my-icon icon="prev01" /></my-button>
                            <my-button class="next" :class="{disabled: group.imageProject.action.translateX === group.imageProject.action.minTranslateX}" @click="nextByGroup('imageProject')"><my-icon icon="next01" /></my-button>
                        </div>
                    </div>
                </div>

                <div class="list" ref="list-for-imageProject">
                    <div class="loading" v-if="val.pending.imageProject"><my-loading width="50" height="50"></my-loading></div>
                    <div class="empty" v-if="!val.pending.imageProject && imageProjects.data.length <= 0">
                        <my-icon icon="empty" size="40"></my-icon>
                    </div>
                    <div class="inner" ref="inner-for-imageProject">

                        <my-image-project-card-box
                            class="item"
                            v-for="v in imageProjects.data"
                            :key="v.id"
                            :row="v"
                            @on-praise="praiseImageProject"
                            :is-praise-pending="val.pending.praiseImageProject"
                        ></my-image-project-card-box>

                    </div>

                </div>
            </div>

            <!-- 图片 -->
            <div class="group group-for-image" v-if="true">
                <!-- 导航 -->
                <div class="run-action-title">
                    <div class="left">图片</div>
                    <div class="right">
                        <div class="tags">
                            <my-button class="tag" :class="{cur: group.image.curTag === 'newest'}" @click="newestInImage">最新</my-button>
                            <my-button class="tag" :class="{cur: group.image.curTag === 'hot'}" @click="hotInImage">热门</my-button>
                            <my-button class="tag" v-for="v in group.image.tag.data" :key="v.id" :class="{cur: group.image.curTag === 'tag_' + v.tag_id}" @click="getImagesByTagId(v.tag_id)">{{ v.name }}</my-button>
                            <my-link class="tag" :href="genUrl('/image/search')">更多</my-link>
                        </div>
                        <div class="operation"></div>
                    </div>
                </div>

                <div class="list" ref="list-for-image">
                    <div class="loading" v-if="val.pending.image"><my-loading width="50" height="50"></my-loading></div>
                    <div class="empty" v-if="!val.pending.image && images.data.length <= 0">
                        <my-icon icon="empty" size="40"></my-icon>
                    </div>

                    <div class="list-inner">

                        <my-image-card-box
                                class="item"
                                v-for="v in images.data"
                                :key="v.id"
                                :row="v"
                                @on-praise="praiseImage"
                                :is-praise-pending="val.pending.praiseImage"
                        ></my-image-card-box>

                    </div>

                </div>
            </div>

            <!-- 视频专题 -->
            <div class="group group-for-video-project">
                <!-- 导航 -->
                <div class="run-action-title">
                    <div class="left">视频专题</div>
                    <div class="right">
                        <div class="tags">
                            <my-button class="tag" :class="{cur: group.videoProject.curTag === 'newest'}" @click="newestInVideoProject">最新</my-button>
                            <my-button class="tag" :class="{cur: group.videoProject.curTag === 'hot'}" @click="hotInVideoProject">热门</my-button>
                            <my-button class="tag" v-for="v in group.videoProject.tag.data" :key="v.id" :class="{cur: group.videoProject.curTag === 'tag_' + v.tag_id}" @click="getVideoProjectsByTagId(v.tag_id)">{{ v.name }}</my-button>
                            <my-link class="tag" :href="genUrl('/video_project/search')">更多</my-link>
                        </div>
                        <div class="operation">
                            <my-button class="prev" :class="{disabled: group.videoProject.action.translateX === group.videoProject.action.maxTranslateX}" @click="prevByGroup('videoProject')"><my-icon icon="prev01" /></my-button>
                            <my-button class="next" :class="{disabled: group.videoProject.action.translateX === group.videoProject.action.minTranslateX}" @click="nextByGroup('videoProject')"><my-icon icon="next01" /></my-button>
                        </div>
                    </div>
                </div>

                <div class="list" ref="list-for-videoProject">
                    <div class="loading" v-if="val.pending.videoProject"><my-loading width="50" height="50"></my-loading></div>
                    <div class="empty" v-if="!val.pending.videoProject && videoProjects.data.length <= 0">
                        <my-icon icon="empty" size="40"></my-icon>
                    </div>
                    <div class="inner" ref="inner-for-videoProject">

                        <my-video-project-card-box
                                class="item"
                                v-for="v in videoProjects.data"
                                :key="v.id"
                                :row="v"
                                @on-praise="praiseVideoProject"
                                :is-praise-pending="val.pending.praiseVideoProject"
                        ></my-video-project-card-box>

                    </div>

                </div>
            </div>

            <!-- 视频 -->
            <div class="group group-for-video">
                <!-- 导航 -->
                <div class="run-action-title">
                    <div class="left">视频</div>
                    <div class="right">
                        <div class="tags">
                            <my-button class="tag" :class="{cur: group.video.curTag === 'newest'}" @click="newestInVideo">最新</my-button>
                            <my-button class="tag" :class="{cur: group.video.curTag === 'hot'}" @click="hotInVideo">热门</my-button>
                            <my-button class="tag" v-for="v in group.video.tag.data" :key="v.id" :class="{cur: group.video.curTag === 'tag_' + v.tag_id}" @click="getVideosByTagId(v.tag_id)">{{ v.name }}</my-button>
                            <my-link class="tag" :href="genUrl('/video/search')">更多</my-link>
                        </div>
                        <div class="operation">

                        </div>
                    </div>
                </div>

                <div class="list" ref="list-for-video">
                    <div class="loading" v-if="val.pending.video"><my-loading width="50" height="50"></my-loading></div>
                    <div class="empty" v-if="!val.pending.video && videos.data.length <= 0">
                        <my-icon icon="empty" size="40"></my-icon>
                    </div>
                    <div class="list-inner items" ref="inner-for-video">

                        <my-video-card-box
                                class="item"
                                v-for="v in videos.data"
                                :key="v.id"
                                :row="v"
                        ></my-video-card-box>

                    </div>

                </div>
            </div>

        </div>


    </div>
</template>

<script src="./js/index.js"></script>
<style scoped src="../public/css/base.css"></style>
<style scoped src="./css/index.css"></style>
