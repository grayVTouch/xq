<template>
    <div class="channel-item-view">

        <div class="list">
            <a class="item" v-for="v in data" target="_blank" :href="genUrl(`/collection_group/${v.id}/image_project`)">
                <div class="thumb">
                    <div class="image-mask">
                        <img :data-src="v.thumb ? v.thumb : TopContext.res.notFound" class="image judge-img-size" v-judge-img-size>
                    </div>
                    <div class="info">
                        <span class="m-r-5">{{ v.count_for_image_project }}</span>
                        <my-icon icon="shijian" class="run-position-relative run-t-1"></my-icon>
                    </div>
                </div>
                <div class="name">{{ v.name }}</div>
                <div class="time">{{ v.created_at }}</div>
            </a>

            <div class="loading" v-if="val.pending.getData">
                <my-loading></my-loading>
            </div>

            <div class="empty" v-if="!val.pending.getData && data.length < 1">
                <my-icon icon="empty" size="40"></my-icon>
            </div>

        </div>

    </div>
</template>

<script>
    export default {
        name: "my-image-project" ,

        data () {
            return {
                data: [] ,
            };
        } ,

        mounted () {
            this.getData();
        } ,

        methods: {
            getData () {
                this.pending('getData' , true);
                Api.user
                    .collectionGroupByUserId(this.$parent.id , {
                        // size: this.data.size ,
                        // page: this.data.page ,
                        relation_type: 'image_project' ,
                    })
                    .then((res) => {
                        if (res.code !== TopContext.code.Success) {
                            this.errorHandleAtHomeChildren(res.message , res.code);
                            return ;
                        }
                        // this.data.total = data.total;
                        // this.data.page = data.page;
                        // this.data.data = data.data;
                        this.data = res.data;
                    })
                    .finally(() => {
                        this.pending('getData' , false);
                    });
            } ,

            // toPage (page) {
            //     this.data.page = page;
            //     this.getData();
            // } ,

        } ,
    }
</script>

<style scoped src="./css/index.css"></style>
<style scoped src="./css/image_project.css"></style>
