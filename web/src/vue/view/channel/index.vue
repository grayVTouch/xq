<template>
    <div class="view">

        <div class="thumb">
            <div class="image-mask"><img :data-src="user.channel_thumb ? user.channel_thumb : TopContext.res.notFound" alt="" class="image judge-img-size" v-judge-img-size></div>
            <div class="name">
                <!-- 仅在编辑模式下展示 -->
                <div class="image-editor" v-if="val.mode === 'edit'">
                    <my-uploader :loading="val.pending.changeThumb" @success="changeThumb"></my-uploader>
                </div>
                <div class="introduction">{{ getUsername(user.username , user.nickname) }} 的频道</div>
            </div>

            <div class="action" v-if="state().user ? user.id === state().user.id : false">
                <a class="link my-button" v-if="val.mode !== 'preview'" v-ripple @click="val.mode = 'preview'">预览</a>
                <a class="link my-button" v-if="val.mode !== 'edit'" v-ripple @click="val.mode = 'edit'">编辑</a>
            </div>

        </div>

        <div class="user">
            <div class="avatar">
                <div class="image-mask">
                    <img :data-src="user.avatar ? user.avatar : TopContext.res.notFound" alt="" class="image judge-img-size" v-judge-img-size>
                    <!-- 仅在编辑模式下展示 -->
                    <div class="image-editor" v-if="val.mode === 'edit'">
<!--                    <div class="image-editor">-->
                        <my-uploader :loading="val.pending.changeAvatar" @success="changeAvatar"></my-uploader>
                    </div>
                </div>
                <div class="info">
                    <div class="name">
                        <div v-if="val.mode === 'edit'">
                            <input type="text" v-model="form.nickname" class="show-and-editor-input" @keyup.enter="changeNickname">
                            <my-loading size="16" v-if="val.pending.changeNickname"></my-loading>
                        </div>
                        <span class="readonly" v-else>{{ getUsername(user.username , user.nickname) }}</span>
                    </div>
                    <div class="desc">
                        <div v-if="val.mode === 'edit'">
                            <input type="text"  v-model="form.description" @keyup.enter="changeDescription" class="show-and-editor-input">
                            <my-loading size="16" v-if="val.pending.changeDescription"></my-loading>
                        </div>
                        <span class="readonly" v-else>{{ user.description }}</span>
                    </div>
                </div>
            </div>
            <div class="other"></div>
        </div>

        <div class="content">

            <div class="statistics">
                <div class="title" @click="onCopy" v-ripple v-clipboard :data-clipboard-text="`${TopContext.host}/channel/${user.id}`">
                    <my-icon icon="lianjie"></my-icon>
                    <span class="m-l-10 run-weight">{{ `${TopContext.host}/channel/${user.id}` }}</span>
                </div>
                <div class="count">
                    <div class="item">关注数 {{ user.my_focus_user_count }}</div>
                    <div class="item">粉丝数 {{ user.focus_me_user_count }}</div>
                    <div class="item">点赞数 {{ user.praise_count }}</div>
                    <div class="item">收藏数 {{ user.collect_count }}</div>
                </div>
                <div class="desc">{{ user.description }}</div>
            </div>

            <div class="resource">
                <!-- 播放列表 -->
                <nav class="navs m-b-20" ref="navs">
                    <div class="line" ref="line-in-nav"></div>
                    <div class="nav-menu">
<!--                        <a class="nav" ref="nav-image" v-ripple :class="{cur: val.nav === 'image'}" @click="switchNavMappingItemById('image')">图片</a>-->
                        <a class="nav" ref="nav-image_project" v-ripple :class="{cur: val.nav === 'image_project'}" @click="switchNavMappingItemById('image_project')">图片专题</a>
                        <a class="nav" ref="nav-my_focus_user" v-ripple :class="{cur: val.nav === 'my_focus_user'}" @click="switchNavMappingItemById('my_focus_user')">我关注的人</a>
                        <a class="nav"  ref="nav-focus_me_user"v-ripple :class="{cur: val.nav === 'focus_me_user'}" @click="switchNavMappingItemById('focus_me_user')">关注我的人</a>
                    </div>
                </nav>

                <div class="nav-mapping-items" ref="nav-mapping-item">
                    <keep-alive>
                        <router-view></router-view>
                    </keep-alive>
                </div>

            </div>
        </div>

    </div>
</template>

<script src="./js/index.js"></script>

<style scoped src="../public/css/base.css"></style>
<style scoped src="./css/index.css"></style>
