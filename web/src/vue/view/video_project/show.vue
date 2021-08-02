<template>
    <div class="view">

        <div class="left info">
            <div class="video-container" ref="video-container">
                <my-video ref="my-video"></my-video>
            </div>

            <div class="play-info">
                <div class="info">
                    <div class="name">{{ videoProject.current.name }}</div>
                    <div class="statistics">
                        {{ videoProject.current.view_count }}观看 · {{ videoProject.current.play_count }}播放 · {{ videoProject.current.created_at }}
                    </div>
                </div>
                <div class="actions">

                    <my-button class="button praise m-r-12" @click.stop="praiseHandle">
                        <my-loading size="16" v-if="val.pending.praiseHandle"></my-loading>
                        <!--                                <my-icon :class="{'run-red': data.is_praised }" icon="shoucang2" /> 喜欢 {{ data.praise_count }}-->
                        <my-icon :class="{'run-red': videoProject.is_praised }" icon="shoucang2" /> 喜欢
                    </my-button>
                    <my-button class="button collect" @click.stop="$refs['my-collection-group'].show()"><my-icon icon="shoucang5" :class="{'run-red': videoProject.is_collected}" /> 收藏</my-button>

<!--                    <div class="action praise run-red" v-ripple><my-icon icon="shoucang2" size="24"></my-icon>{{ videoProject.current.praise_count }}</div>-->
<!--                    <div class="action hate"><my-icon icon="shoucang2" size="24"></my-icon>{{ videoProject.current.against_count }}</div>-->
<!--                    <div class="action collect"><my-icon icon="shoucang5" size="24"></my-icon>{{ videoProject.current.collect_count }}</div>-->
                </div>
            </div>

            <div class="video-project">
                <div class="thumb"><img :data-src="videoProject.thumb ? videoProject.thumb : TopContext.res.notFound" v-judge-img-size class="image judge-img-size"></div>
                <div class="info">

                    <div class="two-column-layout core">
                        <div class="title">
                            <div class="name">{{ videoProject.name }}</div>
                            <div class="info">
                                <div class="statistics-1">{{ videoProject.view_count }}观看 · {{ videoProject.play_count }}播放 · {{ videoProject.collect_count }}收藏 · {{ videoProject.praise_count }}点赞· {{ videoProject.against_count }}反对</div>
                                <div class="statistics-2">{{ videoProject.__status__ }}, 全{{ videoProject.max_index }}话</div>
                            </div>
                        </div>
                        <div class="item company" v-if="videoProject.video_company">
                            <div class="field">视频制作公司</div>
                            <div class="value"><a :href="genUrl(`/video_project/search?video_company_id=${videoProject.video_company_id}`)" target="_blank">{{ videoProject.video_company.name }}</a></div>
                        </div>
                    </div>

                    <div class="two-column-layout time">
                        <div class="item release-time">
                            <div class="field">发布时间</div>
                            <div class="value">{{ videoProject.release_time }}</div>
                        </div>
                        <div class="item end-time">
                            <div class="field">完结时间</div>
                            <div class="value">{{ videoProject.end_time }}</div>
                        </div>
                        <div class="item upload-time">
                            <div class="field">上传时间</div>
                            <div class="value">{{ videoProject.created_at }}</div>
                        </div>
                    </div>

                    <div class="desc">{{ videoProject.description }}</div>
                    <div class="item run-tags">
                        <my-link class="tag border-tag" target="_blank" v-for="v in videoProject.tags" :key="v.id" :href="genUrl(`/video_project/search?tag_id=${v.tag_id}`)">{{ v.name }}</my-link>
                    </div>
                </div>
                <div class="mark">
                    <div class="flag hide">UNCENSORED</div>
                    <div class="score">{{ videoProject.score }}</div>
                </div>
            </div>

            <div class="comments"></div>

        </div>

        <div class="right recommend">
            <div class="video-index">

                <!-- 索引标题 -->
                <div class="title">选集</div>

                <!-- 索引范围 -->
                <div class="index-range run-space-between">
                    <div class="left">
                        <div class="item" v-ripple v-for="(v,k) in indexRange.group.index" :key="k" :class="{cur: indexRange.current.value === `${v.min}-${v.max}`}" @click="switchIndexRangeByMinAndMax(v.min , v.max)">{{ v.min + '-' + v.max }}</div>
<!--                        <div class="item cur">151-180</div>-->
                    </div>
                    <div class="right" >
<!--                        <div class="item more" v-if="videoProject.videos.length > 120" @click="val.loadMoreIndex = true">加载更多<my-icon icon="arrow"></my-icon>-->
                        <div class="item more" :class="{cur: indexRange.current.more}" v-ripple @click.stop="showMoreIndex" v-if="videoProject.max_index > indexRange.split * indexRange.indexGroupCount">
                            <template v-if="indexRange.current.more">{{ indexRange.current.min + '-' + indexRange.current.max }}</template>
                            <template v-else>加载更多</template>
                            <my-icon icon="arrow" :class="{spread: !val.loadMoreIndex}"></my-icon>
                        </div>
                    </div>
                    <div class="more-index" v-if="val.loadMoreIndex" @click.stop>
                        <div class="item" v-ripple v-for="(v,k) in indexRange.group.other" :key="k" :class="{cur: indexRange.current.value === `${v.min}-${v.max}`}" @click="switchIndexRangeByMinAndMax(v.min , v.max)">{{ v.min + '-' + v.max }}</div>
<!--                        <div class="item">151-180</div>-->
                    </div>
                </div>

                <!-- 索引 -->
                <div class="indexs" :class="{pending: val.pending.videosInRange , empty: !val.pending.videosInRange && indexRange.videos.length === 0}">

                    <div class="item" v-for="v in indexRange.videos" :class="{cur: v.id === videoProject.current.id}" :key="v.id" @mouseenter="showVideo(v)" @mouseleave="hideVideo(v)" @click="ins.videoPlayer.index(v.index)">
                        <div class="thumb">
                            <div class="image-mask" v-show="v.show_type === 'image' || !v.video_loaded"><img :data-src="v.__thumb__" v-judge-img-size class="image judge-img-size" alt=""></div>
                            <div class="video-mask" :ref="'video-mask-' + v.id" v-show="v.show_type === 'video' && v.video_loaded">
                                <video :ref="'video-' + v.id" loop="loop" autoplay="autoplay" muted="muted"></video>
                            </div>
                            <div class="info"><template v-if="v.is_hd">HD</template> {{ v.__duration__ }}</div>
                            <div class="progress" v-if="!v.video_loaded && v.show_type === 'video'">
                                <div class="ratio" :style="'transform: scaleX(' + v.video_loaded_ratio + ')'"></div>
                            </div>
                        </div>
                        <div class="info">
                            <div class="name">{{ v.__name__ }}</div>
<!--                            <div class="subject">Pink Pineapple</div>-->
                            <div class="statistics">{{ v.view_count }}次观看</div>
                        </div>
                    </div>

                    <div class="pending" v-if="val.pending.videosInRange"><my-loading></my-loading></div>
                    <div class="empty" v-if="!val.pending.videosInRange && indexRange.videos.length === 0"><my-icon icon="empty" size="40"></my-icon></div>
                </div>

            </div>

            <div class="video-series">
                <div class="title">系列视频</div>
                <div class="list">

                    <div class="video-project" v-for="v in videoProjectsInSeries" :key="v.id" @click="linkAndRefresh(`/video_project/${v.id}/show`)">
                        <div class="thumb"><img :data-src="v.thumb ? v.thumb : TopContext.res.notFound" class="judge-img-size" v-judge-img-size alt=""></div>
                        <div class="info">
                            <div class="name">{{ v.name }}</div>
                            <div class="statistics">
                                <div class="index-count">全{{ v.max_index }}话</div>
                                <div class="statistics">{{ v.play_count }}播放</div>
                            </div>
                        </div>
                    </div>

                    <div class="empty" v-if="videoProjectsInSeries.length === 0"><my-icon icon="empty" size="40"></my-icon></div>

                </div>
            </div>

            <div class="relation"></div>
        </div>

        <my-collection-group
                ref="my-collection-group"
                :relation-id="id"
                relation-type="video_project"
                @on-change="collectionHandle"
        ></my-collection-group>
    </div>
</template>

<script src="./js/show.js"></script>

<style src="../public/css/base.css"></style>
<style scoped src="./css/show.css"></style>
