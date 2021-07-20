<template>

    <my-base>
        <template slot="search">
            <my-search-form @submit="searchEvent">

                <my-search-form-item name="模块">
                    <my-select :data="modules" v-model="search.module_id"></my-select>
                    <my-loading v-if="myValue.pending.getModules"></my-loading>
                </my-search-form-item>

                <my-search-form-item name="类型">
                    <i-select v-model="search.type" class="w-200">
                        <i-option v-for="(v,k) in TopContext.business.category.type" :key="k" :value="k">{{ v }}</i-option>
                    </i-select>
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
        </template>

        <template slot="page">

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
                <template v-slot:name="{row,index}">
                    <template v-if="row.floor > 1">{{ '|' + '_'.repeat((row.floor - 1) * 10) + row.name }}</template>
                    <template v-else>{{ row.name }}</template>
                </template>
                <template v-slot:user_id="{row,index}">{{ row.user ? `${row.user.username}【${row.user.id}】` : `unknow【${row.user_id}】` }}</template>
                <template v-slot:module_id="{row,index}">{{ row.module ? `${row.module.name}【${row.module.id}】` : `unknow【${row.module_id}】` }}</template>
                <template v-slot:is_enabled="{row,index}"><my-switch v-model="row.is_enabled" :loading="myValue.pending['is_enabled_' + row.id]" :extra="{id: row.id , field: 'is_enabled'}" @on-change="updateBoolValEvent" /></template>
                <template v-slot:action="{row , index}">
                    <my-table-button @click="addNextLevelEvent(row)"><my-icon icon="add" />添加下级</my-table-button>
<!--                    <my-table-button type="error" :loading="myValue.pending['delete_' + row.id]" @click="destroyEvent(index , row)"><my-icon icon="shanchu" />删除</my-table-button>-->
                </template>
            </i-table>
        </template>

        <my-form ref="form"
                 :id="current.id"
                 :mode="myValue.mode"
                 :add-mode="myValue.addMode"
                 @on-success="getData"
        ></my-form>
    </my-base>
</template>

<script src="./js/index.js"></script>
<style scoped>

</style>
