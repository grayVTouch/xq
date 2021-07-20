<template>
    <i-table
            class="w-r-100"
            border

            :columns="table.field"
            :data="table.data"
            :loading="myValue.pending.getData"
            @on-selection-change="selectionChangeEvent"
            @on-row-dblclick="editEvent"
            @on-row-click="rowClickEvent"
            @on-sort-change="sortChangeEvent"
    >
        <slot></slot>
    </i-table>
</template>

<script>
    export default {
        name: "my_table" ,

        props: {

        } ,

        methods: {

        } ,
    }
</script>

<style scoped>

</style>
