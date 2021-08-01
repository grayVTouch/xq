<template>
    <main class="main" data-app="true">
        <section class="center" ref="center">

            <header class="header">
                <div class="logo">
                    <div class="inner"><img :src="TopContext.res.logo" class="image" alt=""></div>
                </div>
                <div class="site">兴趣部落</div>
            </header>

            <!-- 模块选择 -->
            <nav class="nav">
                <h2 class="title">请选择模块</h2>
                <div class="module">
                    <ul class="list" v-if="!val.pending.getModules">
                        <li v-for="v in modules" :key="v.id" class="item"><a class="link" href="javascript:void(0);" @click="toHome(v)">{{ v.name }}</a></li>
                    </ul>
                    <div class="loading" v-else>
                        <my-loading></my-loading>
                    </div>
                </div>
            </nav>

        </section>

    </main>
</template>

<script>
    export default {
        name: "welcome" ,

        data () {
            return {
                dom: {} ,
                modules: [] ,
            };
        } ,

        beforeRouteUpdate () {
            this.getModules();
        } ,

        mounted () {
            this.initDom();
            this.showModule();
            this.getModules();
        } ,

        methods: {
            initDom () {
                this.dom.center = G(this.$refs.center);
            } ,

            showModule () {
                this.dom.center.startTransition('show');
            } ,

            hideModule () {
                this.dom.center.endTransition('show');
            } ,

            getModules () {
                this.pending('getModules' , true);
                Api.module
                    .all()
                    .then((res) => {
                        if (res.code !== TopContext.code.Success) {
                            this.errorHandle(res.message);
                            return ;
                        }
                        this.modules = res.data;
                    })
                    .finally(() => {
                        this.pending('getModules' , false);
                    });
            } ,

            toHome (v) {
                G.s.json('module' , v);
                const url = this.genUrl('/index');
                this.openWindow(url , '_self');
            } ,

        } ,
    }
</script>

<style scoped src="./css/welcome.css"></style>
