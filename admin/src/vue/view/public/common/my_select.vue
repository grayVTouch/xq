<template>
<!--    <select v-model="valueCopy" @change="changeEvent" class="form-select">-->
<!--        <option :value="empty">请选择...</option>-->
<!--        <option v-for="v in data" :value="v[attr.id]" :key="v[attr.id]">{{ v[attr.name] }}</option>-->
<!--    </select>-->

    <i-select v-model="valueCopy" :class="className" filterable @on-change="changeEvent" :style="`width: ${width}px`" :disabled="disabled" :clearable="clearable">
        <i-option v-for="v in data" :value="v[attr.id]" :key="v[attr.id]">{{ v[attr.name] }}</i-option>
    </i-select>
</template>

<script>
    export default {
        name: "my-select" ,
        data () {
            return {
                valueCopy: '' ,
            };
        } ,
        model: {
            name: 'value' ,
            event: 'change' ,
        } ,
        props: {
            value: {
                default: '' ,
            },

            data: {
                type: Array ,
                required: true ,
            } ,

            // 未选择时的默认值
            empty: {
                default: '' ,
            } ,

            attr: {
                type: Object ,
                default () {
                    return {
                        id: 'id',
                        name: 'name',
                    }
                }
            } ,
            width: {
                type: [String , Number] ,
                default: 200 ,
            } ,

            className: {
                default: '' ,
            } ,

            disabled: {
                type: [Boolean] ,
                default: false ,
            } ,

            clearable : {
                type: Boolean ,
                default: false ,
            } ,
        } ,

        methods: {
            changeEvent (value) {
                this.$emit('change' , G.isValid(value) ? value : '');
                this.$emit('on-change' , G.isValid(value) ? value : '');
            } ,
        } ,

        watch: {
            value: {
                // 为了避免初始化的时候在出现重复值导致不应用
                // 所以，必须要加上 immediate
                immediate: true ,
                handler (newVal , oldVal) {
                    this.valueCopy = newVal;
                }
            } ,
        } ,
    }
</script>

<style scoped>

</style>
