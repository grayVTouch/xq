<template>

    <my-base>
        <template slot="search">
            <my-search-form @submit="searchEvent">

                <my-search-form-item name="ID">
                    <input type="text" class="form-text" v-model="search.id" />
                </my-search-form-item>

                <my-search-form-item :show-separator="false">
                    <my-table-button @click="searchEvent"><my-icon icon="search" mode="right" />搜索</my-table-button>
                    <my-table-button @click="resetEvent" class="m-l-10"><my-icon icon="reset" mode="right" />重置</my-table-button>
                </my-search-form-item>
            </my-search-form>
        </template>

        <template slot="action">
            <my-table-button class="m-r-10" @click="addEvent"><my-icon icon="add" />添加</my-table-button>
            <my-table-button class="m-r-10" @click="editEventByButton"><my-icon icon="edit" />编辑</my-table-button>
            <my-table-button class="m-r-10" type="error" @click="destroyAllEvent" :loading="myValue.pending.destroyAll"><my-icon icon="shanchu" />删除选中项 <span v-if="selection.length > 0">（{{ selection.length }}）</span></my-table-button>
            <my-table-button class="m-r-10" @click="linkDiskEvent" :loading="myValue.pending.linkDisk"><my-icon icon="" />创建链接 <span v-if="selection.length > 0">（{{ selection.length }}）</span></my-table-button>

        </template>

        <template slot="page">
            <my-page
                    :total="table.total"
                    :sizes="table.sizes"
                    :size="table.size"
                    :page="table.page"
                    @on-page-change="pageEvent"
                    @on-size-change="sizeEvent"
            ></my-page>
        </template>

        <template slot="table">
            <i-table
                    ref="table"
                    class="w-r-100"
                    border

                    :columns="table.field"
                    :data="table.data"
                    :loading="myValue.pending.getData"
                    @on-selection-change="selectionChangeEvent"
                    @on-row-click="rowClickEvent"
                    @on-row-dblclick="rowDblclickEvent"
                    @on-sort-change="sortChangeEvent"
            >
                <template v-slot:is_default="{row,index}"><my-switch v-model="row.is_default" :loading="myValue.pending['is_default_' + row.id]" :extra="{id: row.id , field: 'is_default'}" @on-change="updateBoolValEvent" /></template>
                <template v-slot:is_linked="{row,index}"><my-switch v-model="row.is_linked" :loading="myValue.pending['is_linked_' + row.id]" :extra="{id: row.id , field: 'is_linked'}" @on-change="updateBoolValEvent" /></template>
                <template v-slot:action="{row , index}">
                    <my-table-button @click="editEvent(row)"><my-icon icon="edit" />编辑 {{ row.is_linked }}</my-table-button>
                    <my-table-button type="error" :loading="myValue.pending['delete_' + row.id]" @click="destroyEvent(index , row)"><my-icon icon="shanchu" />删除</my-table-button>
                    <my-table-button v-if="!row.is_linked" :loading="myValue.pending['link_disk_' + row.id]" @click="linkDiskEvent(index , row)"><my-icon icon="shanchu" />创建链接</my-table-button>
                </template>
            </i-table>
        </template>

        <my-form ref="form" :id="current.id" :mode="myValue.mode" @on-success="getData"></my-form>
    </my-base>

</template>

<script src="./js/index.js"></script>
<style scoped>

</style>
