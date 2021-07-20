<template>
    <i-poptip trigger="hover" placement="right" :transfer="true">
        <video class="my-video" autoplay muted :src="src" type="video/mp4" :height="TopContext.table.imageH" @click="openWindow(src , '_blank')"></video>
        <div slot="content" class="my-table-video-preview">
            <video class="my-video" :src="src" type="video/mp4" loop autoplay controls muted></video>
        </div>
    </i-poptip>
</template>

<script>
    export default {
        name: "my-table-video-preview" ,

        props: {
            src: {
                default: '' ,
            }
        }
    }
</script>

<style scoped>
    /**
     * ***************
     * 表格大图预览样式
     * ***************
     */
    .my-table-video-preview {

    }

    .my-table-video-preview .my-video {
        max-width: 400px;
    }

</style>
