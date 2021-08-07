<template>

    <my-base

        :show-search="false"
        :show-data="false"
        :show-actions="false"
    >
        <div class="line">
            <i-tabs value="pornhub">
                <my-pornhub></my-pornhub>
                <my-xvideos></my-xvideos>
            </i-tabs>
        </div>
    </my-base>

</template>

<script>
    import MyPornhub from './pornhub.vue';
    import MyXvideos from './xvideos.vue';

    export default {
        name: "index",

        data () {
            return {
                tab: '' ,
            };
        } ,

        components: {
            'my-pornhub': MyPornhub ,
            'my-xvideos': MyXvideos ,
        } ,
    }
</script>
<style scoped>

</style>
