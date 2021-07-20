<template>
    <div class="view">

        <div class="left info">
            <div class="video-container" ref="video-container">
                <my-video ref="my-video"></my-video>
            </div>

            <div class="play-info">
                <div class="info">
                    <div class="name"></div>
                    <div class="statistics">
                        {{ video.view_count }}观看 · {{ video.play_count }}播放 · {{ video.created_at }}
                    </div>
                </div>
                <div class="actions">

                    <my-button class="button praise m-r-12" @click.stop="praiseHandle">
                        <my-loading size="16" v-if="val.pending.praiseHandle"></my-loading>
                        <!--                                <my-icon :class="{'run-red': data.is_praised }" icon="shoucang2" /> 喜欢 {{ data.praise_count }}-->
                        <my-icon :class="{'run-red': video.is_praised }" icon="shoucang2" /> 喜欢
                    </my-button>
                    <my-button class="button collect" @click.stop="$refs['my-collection-group'].show()"><my-icon icon="shoucang5" :class="{'run-red': video.is_collected}" /> 收藏</my-button>

                    <!--                    <div class="action praise run-red" v-ripple><my-icon icon="shoucang2" size="24"></my-icon>{{ video.praise_count }}</div>-->
                    <!--                    <div class="action hate"><my-icon icon="shoucang2" size="24"></my-icon>{{ video.against_count }}</div>-->
                    <!--                    <div class="action collect"><my-icon icon="shoucang5" size="24"></my-icon>{{ video.collect_count }}</div>-->
                </div>
            </div>

            <div class="video">
                <div class="thumb">
                    <img
                            data-id="video-test-thumb"
                        :data-src="video.__thumb__ ? video.__thumb__ : TopContext.res.notFound"
                        v-judge-img-size
                        class="image judge-img-size"
                        alt=""
                    >
                </div>
                <div class="info">

                    <div class="top">
                        <div class="line core">
                            <div class="title">
                                <div class="name">{{ video.name }}</div>
                                <div class="info">
                                    <div class="statistics-1">{{ video.view_count }}观看 · {{ video.play_count }}播放 · {{ video.collect_count }}收藏 · {{ video.praise_count }}点赞· {{ video.against_count }}反对</div>
                                </div>
                            </div>
                        </div>

                        <div class="line time">
                            <div class="item upload-time">
                                <div class="field">上传时间</div>
                                <div class="value">{{ video.created_at }}</div>
                            </div>
                        </div>

                        <div class="line desc">{{ video.description }}</div>
                    </div>
                    <div class="btm">
                        <div class="item run-tags">
                            <my-link class="tag border-tag" target="_blank" v-for="v in video.tags" :key="v.id" :href="genUrl(`/video/search?tag_id=${v.tag_id}`)">{{ v.name }}</my-link>
                        </div>
                    </div>

                </div>

                <div class="line mark">
                    <div class="flag hide">UNCENSORED</div>
                    <div class="score">{{ video.score }}</div>
                </div>
            </div>

            <div class="comments"></div>

            <!-- 同类型推荐 -->
            <div class="recommend">

                <div class="list">

                    <my-video-card-box
                            class="item"
                            v-for="v in recommendData.data"
                            :key="v.id"
                            :row="v"
                    ></my-video-card-box>

                </div>

                <div class="actions" v-if="val.pending.getRecommendData || recommendData.page < recommendData.maxPage">
                    <div class="load-more" v-ripple @click="loadMoreRecommendEvent()">
                        加载更多
                        <my-loading class="m-l-5" size="16" v-if="val.pending.getRecommendData"></my-loading>
                    </div>
                </div>

                <div class="end-message" v-if="!val.pending.getRecommendData && recommendData.total > 0 && recommendData.page >= recommendData.maxPage">
                    <span class="text">已经到底了</span>
                </div>

                <div class="empty" v-if="!val.pending.getNewestData && recommendData.total <= 0">
                    <my-icon icon="empty" size="40"></my-icon>
                </div>
            </div>

        </div>

        <!-- 其他 -->
        <div class="right misc" ref="misc">
                <div class="inner" :class="{'fixed-top': val.fixedTop , 'fixed-btm': val.fixedBtm}">

                    <!-- 发布者 -->
                    <a class="user m-b-20" target="_blank" :href="genUrl(`/channel/${video.user_id}`)">
                        <div class="inner">
                            <div class="avatar">
                                <div class="mask">
                                    <img :data-src="video.user ? video.user.avatar : TopContext.res.notFound" v-judge-img-size class="image judge-img-size" alt="">
                                </div>
                            </div>
                            <div class="name">{{ video.user ? video.user.username : '' }}</div>
                            <div class="data">
                                <a class="left" target="_blank" :href="genUrl(`/channel/${video.user_id}/my_focus_user`)">关注 {{ video.user.my_focus_user_count }}</a>
                                <a class="right" target="_blank" :href="genUrl(`/channel/${video.user_id}/focus_me_user`)">粉丝 {{ video.user.focus_me_user_count }}</a>
                            </div>
                            <div class="desc">{{ video.user.description }}</div>
                            <div class="action">
                                <my-button class="focus" @click.prevent="focusHandle">

                                    <template v-if="!video.user.focused"><my-icon icon="add" v-if="!video.user.focused" class="run-position-relative run-t--2" /> 关注</template>
                                    <template v-else>取消关注</template>
                                    <my-loading size="16" v-if="val.pending.focusHandle"></my-loading>
                                </my-button>
                                <my-button class="message" v-if="false" @click.prevent>私信</my-button>
                            </div>
                        </div>
                    </a>

                    <!-- 最新发布 -->
                    <div class="newest" ref="newest">
                        <div class="inner" :class="{fixed: val.fixed}">
                            <div class="title">最新发布</div>
                            <div class="list">

                                <a
                                        class="item"
                                        v-for="v in newest.data"
                                        :Key="v.id"
                                        @click.prevent="linkToVideo(v)"
                                >
                                    <div class="inner">
                                        <div
                                                class="preview"
                                                @mouseenter="playPreviewVideo(v)"
                                                @mouseleave="pausePreviewVideo(v)"
                                        >
                                            <div class="preview-image">
                                                <img :data-src="v.__thumb__ ? v.__thumb__ : TopContext.res.notFound" v-judge-img-size class="image judge-img-size">
                                            </div>
                                            <div
                                                    class="preview-video"
                                                    v-show="v.is_show_preview_video"
                                            >
                                                <div class="mask"></div>
                                                <video
                                                        :ref="`video-${v.id}`"
                                                        :data-is-init="0"
                                                        class="video"
                                                        muted
                                                        loop
                                                ></video>
                                                <div class="progress-bar" :ref="`progress-bar-${v.id}`" v-if="!v.video_is_loaded">
                                                    <div
                                                            class="inner"
                                                            :ref="`progress-bar-inner-${v.id}`"
                                                            :style="`width: ${v.video_loaded_ratio * 100}%`"
                                                    ></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="info">
                                            <div class="top">
                                                <div class="line name">{{ v.name }}</div>
                                                <div class="line statistics">{{ v.view_count }}次观看 111</div>
                                            </div>
                                            <div class="btm">
                                                <div class="line time"><my-icon icon="shijian" size="12"></my-icon>&nbsp;{{ v.format_time }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </a>

                                <div class="empty" v-if="!val.pending.getNewestData && newest.data.length <= 0">
                                    <my-icon icon="empty" size="40"></my-icon>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- app 下载 -->
                    <div class="mobile"></div>
                </div>
            </div>

        <my-collection-group
                ref="my-collection-group"
                :relation-id="id"
                relation-type="video"
                @on-change="collectionHandle"
        ></my-collection-group>
    </div>
</template>

<script src="./js/show.js"></script>

<style src="../public/css/base.css"></style>
<style scoped src="./css/show.css"></style>
