<template>
    <div class="main">
        <div class="menu" ref="menu">
            <div class="inner" ref="menu-inner">
                <div class="line logo" ref="logo">
                    <div class="c-left image-outer">
                        <div class="inner"><img :src="TopContext.res.logo" alt="" class="image"></div>
                    </div>
                    <div class="c-right text-outer">{{ TopContext.os.name }}</div>
                </div>

                <div class="line avatar" ref="avatar">
                    <div class="inner">
                        <div class="top">
                            <my-avatar
                                    :src="user().avatar"
                                    :mask="true"
                                    :top-val="user().username"
                                    :btm-val="user().role ? user().role.name : '无'"
                            ></my-avatar>
                        </div>
                        <div class="btm">{{ getUsername(user().username , user().nickname) }}</div>
                    </div>
                </div>

                <div class="line block" ref="block">
                    <!-- 水平 -->
                    <div class="horizontal" ref="horizontal" @click="vertical">
                        <div class="in">
                            <div class="line"></div>
                            <div class="line"></div>
                            <div class="line"></div>
                        </div>
                    </div>

                    <!-- 垂直 -->
                    <div class="vertical hide" ref="vertical" @click="horizontal">
                        <div class="in">
                            <div class="line"></div>
                            <div class="line"></div>
                            <div class="line"></div>
                        </div>
                    </div>
                </div>

                <div class="line menus" ref="menus">
                    <my-menu ref="my-menu" @on-focus="menuFocusEvent"></my-menu>
                </div>

                <div class="line desc" ref="desc">{{ TopContext.os.name }}</div>
            </div>
        </div>

        <div class="content" ref="content">
            <div class="inner">
<!--                <div class="line nav" :style="`padding-right: ${myValue.yScrollbarWidth}px`" ref="nav">-->
                <div class="line nav" ref="top-nav">
                    <div class="top toolbar" ref="toolbar">

                        <div class="left">
                            <!-- 测试范例：翻译 -->
                            <Tooltip content="刷新当前标签页">
                                <div v-ripple @click="reloadLocalView()">
                                    <my-icon icon="reset"></my-icon>
                                </div>
                            </Tooltip>
                            <Tooltip content="清空失败队列">
                                <div v-ripple @click="clearFailedJobs">
                                    <my-icon icon="qingkong"></my-icon>
                                </div>
                            </Tooltip>
                            <Tooltip content="重试失败队列" v-if="false">
                                <div v-ripple @click="retryFailedJobs">
                                    <my-icon icon="lianjie1"></my-icon>
                                </div>
                            </Tooltip>
                            <Tooltip content="执行资源清理任务">
                                <div v-ripple @click="executeResourceClearJob">
                                    <my-icon icon="delete"></my-icon>
                                </div>
                            </Tooltip>
                        </div>

                        <div class="right">

                            <!-- 测试范例：通知栏 -->
                            <div class="notification hide">
                                <div class="in">
                                    <div class="image-container">
                                        <img src="./res/notification.png" class="image">
                                    </div>
                                    <div class="text">10</div>
                                </div>
                            </div>

                            <a target="_blank" :href="state().settings.web_url" class="common-item website">
                                <div class="in">
                                    <i-tooltip :transfer="true" placement="bottom" content="跳转到 web 网站">
                                        <my-icon icon="web" size="24"></my-icon>
                                    </i-tooltip>
                                </div>
                            </a>

                            <!--用户控制-->
                            <div class="user" @mouseenter="showUserCtrl" @mouseleave="hideUserCtrl">
                                <div class="ctrl">
                                    <div class="avatar">
                                        <my-avatar :src="user().avatar"></my-avatar>
                                    </div>
                                    <div class="username">{{ user().username }}</div>
                                </div>
                                <div class="functions hide" ref="functions-for-user">
                                    <div class="function" @click="logout">
                                        <div class="left"><my-icon icon="084tuichu"></my-icon></div>
                                        <div class="right">注销</div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="btm tabs" ref="tabs">
                        <!--多标签-->
                        <div class="multiple-tab">
                            <!-- 项列表 -->
                            <div class="tabs"></div>
                        </div>
                    </div>
                </div>

                <div class="line view" ref="view">
                    <!-- 视图导航 -->
                    <div class="navigation">
                        <div class="nav" v-if="myValue.fixedNavigation"></div>
                        <div ref="btm-nav"
                             class="nav"
                             :class="{fixed: myValue.fixedNavigation , spread: state().slidebar === 'vertical'}"
                        >
                            <div class="inner">
                                <div class="left">
                                    <img :src="state().topRoute.bIco" class="image" alt="">
                                    <span class="cn">{{ state().topRoute.cn }}</span>
                                    <span class="delimiter">/</span>
                                    <span class="en">{{ state().topRoute.en }}</span>
                                </div>
                                <div class="right">
                                    <!-- 面包屑 -->
                                    <template v-for="(v,k) in state().positions">
                                        <span v-ripple class="text" :class="{'run-cursor-not-allow':  !v.view }" @click="navigationTo(v)">{{ v.cn }}</span>
                                        <span class="delimiter" v-if="!(k == state().positions.length - 1)">/</span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 动态视图 -->
                    <div class="dynamic" ref="dynamic">
                        <keep-alive v-if="myValue.showView">
<!--                            <transition name="fade-in-out">-->
                                <router-view class="fade-in-out"></router-view>
<!--                            </transition>-->
                        </keep-alive>
                    </div>

                    <!-- 滚动到顶部 -->
                    <div class="to-top hide">
                        <img src="" alt="" class="image" />
                    </div>
                </div>

                <div class="line info hide" ref="info">{{ TopContext.os.name }}</div>
            </div>
        </div>

        <!-- 磁盘选择器 -->
        <my-disk ref="disk"></my-disk>
    </div>
</template>

<script src="./js/index.js"></script>
<style scoped src="./css/index.css"></style>
