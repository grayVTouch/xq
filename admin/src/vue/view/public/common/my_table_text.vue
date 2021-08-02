<template>
    <span
            class="table-column-nowrap"
            :title="text"
            :style="`width: ${TopContext.table[name] - 40}px`"
    >{{ text }}</span>
</template>

<script>
    export default {
        name: "my-table-text" ,

        props: {
            text: {
                default: '' ,
            } ,
            name: {
                default: 'name' ,
            }
        }
    }
</script>

<style scoped>

    /**
     * *************
     * 换行
     * *************
     */
    span.table-column-nowrap {
        display: inline-block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

</style>
