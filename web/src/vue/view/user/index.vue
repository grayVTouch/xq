<template>
    <div class="view">
        <div class="menu" :class="{fixed: fixedMenu}" ref="menu">
            <div class="inner">
                <a v-ripple :href="genUrl('/user/info')" class="link" :class="{cur: current === 'info'}"><my-icon icon="yonghu" mode="right" />我的信息</a>
                <a v-ripple :href="genUrl('/user/password')" class="link" :class="{cur: current === 'password'}"><my-icon icon="privilege" mode="right" />修改密码</a>
                <a v-ripple :href="genUrl('/user/history')" class="link" :class="{cur: current === 'history'}"><my-icon icon="lishijilu" mode="right" />历史记录</a>
                <a v-ripple :href="genUrl('/user/favorites')" class="link" :class="{cur: current === 'favorites'}"><my-icon icon="shoucang6" mode="right" />我的收藏</a>
                <a v-ripple :href="genUrl('/user/praise')" class="link" :class="{cur: current === 'praise'}"><my-icon icon="shoucang2" mode="right" />我的点赞</a>
                <a v-ripple v-if="state().user" :href="genUrl(`/channel/${state().user.id}`)" class="link" :class="{cur: current === 'channel'}"><my-icon icon="ronghepindao" mode="right" />我的频道</a>
            </div>
        </div>

        <div class="content">
            <div class="inner">
                <router-view @focus-menu="focusMenu"></router-view>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "index" ,
        data () {
            return {
                current: '' ,
                fixedMenu: false ,
                dom: {} ,
            };
        } ,

        beforeRouteEnter (to , from , next) {
            next((vm) => {
                // vm.scrollEvent();
            });
        },

        beforeRouteUpdate (to , from , next) {
            next();
        } ,

        mounted () {
            this.initDom();
            this.initEvent();
        } ,

        methods: {
            initDom () {
                this.dom.menu = G(this.$refs.menu);
                this.dom.win = G(window);
            } ,
            focusMenu (menu) {
                this.current = menu;
            } ,

            scrollEvent () {
                const scrollTop = this.dom.menu.getWindowOffsetVal('top');
                this.fixedMenu = scrollTop < TopContext.val.fixedTop;
            } ,

            initEvent () {
                this.dom.win.on('scroll' , this.scrollEvent.bind(this));
            } ,

        } ,
    }
</script>

<style scoped src="../public/css/base.css"></style>
<style scoped>
    .view {
        display: flex;
        justify-content: flex-start;
        align-items: stretch;
        padding-top: 20px;
        position: relative;
    }

    /*.view:before {*/
    /*    position: absolute;*/
    /*    left: 0;*/
    /*    top: 20px;*/
    /*    width: 150px;*/
    /*    height: calc(100% - 20px);*/
    /*    background-color: #5d5d5d;*/
    /*    content: '';*/
    /*    z-index: 0;*/
    /*}*/

    .view > * {
        margin: 0;
        flex: 0 0 auto;
        box-sizing: border-box;
    }

    .view > .menu {
        width: 150px;
        position: relative;
        z-index: 1;
        background-color: var(--button-background-color);
    }

    .view > .menu > .inner {
        width: inherit;
    }

    .view > .menu.fixed .inner {
        position: fixed;
        top: var(--fixed-top);
    }

    .view > .menu > .inner .link {
        display: flex;
        justify-content: flex-start;
        align-items: center;
        height: 40px;
        /*background-color: var(--button-background-color);*/
        transition: all 0.3s;
        padding: 0 20px;
        font-size: 14px;
    }

    .view > .menu > .inner > .link:hover {
        background-color: var(--button-background-color-hover);
    }

    .view > .menu > .inner > .link.cur {
        background-color: var(--button-background-color-hover);
    }

    .view > .content {
        width: calc(100% - 150px);
        background-color: #424242;
        /*min-height: 600px;*/
        /*padding: 0 20px 20px 20px;*/

    }

    .view > .content > .inner {
        position: relative;
    }

</style>
