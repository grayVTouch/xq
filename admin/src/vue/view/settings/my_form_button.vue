<template>
    <i-button
            type="primary"
            :loading="loading"
            @click="$emit('on-click')"
    >提交</i-button>
</template>

<script>
    export default {
        name: "my_button" ,
        props: {
            loading: {
                default: false ,
            } ,

            onClick: {
                type: Function ,
            } ,
        } ,
    }
</script>

<style scoped>

</style>
