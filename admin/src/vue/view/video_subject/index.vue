<template>
    <my-base>
        <template slot="search">
            <my-search-form @submit="searchEvent">

                <my-search-form-item name="模块">
                    <my-select :data="modules" v-model="search.module_id" empty=""></my-select>
                    <my-loading v-if="myValue.pending.getModules"></my-loading>
                </my-search-form-item>

                <my-search-form-item name="ID">
                    <input type="text" class="form-text" v-model="search.id" />
                </my-search-form-item>

                <my-search-form-item name="名称">
                    <input type="text" class="form-text" v-model="search.name" />
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
                    @on-selection-change="selectionChangeEvent"
                    :loading="myValue.pending.getData"
                    @on-row-click="rowClickEvent"
                    @on-row-dblclick="rowDblclickEvent"
                    @on-sort-change="sortChangeEvent"
            >
<!--                <template v-slot:name="{row,index}">{{ row.name + `【${row.module ? row.module.name : 'unknow'}】` }}</template>-->
                <template v-slot:name="{row,index}">{{ row.name }}</template>
                <template v-slot:module_id="{row,index}">{{ row.module ? `${row.module.name}【${row.module.id}】` : `unknow【${row.module_id}】` }}</template>
                <template v-slot:thumb="{row,index}">
                    <my-table-image-preview :src="row.thumb"></my-table-image-preview>
                </template>

                <template v-slot:attr="{row,index}">
                    <i-poptip placement="right" width="400" title="关联主体属性" :transfer="true" trigger="hover">
                        <i-button>悬浮可查看详情</i-button>
                        <div slot="content">
                            <table class="line-table">
                                <tbody>
                                <tr v-for="(v,k) in row.__attr__" :key="k">
                                    <td>{{ v.field }}</td>
                                    <td>{{ v.value }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </i-poptip>
                </template>
                <template v-slot:user_id="{row,index}">{{ row.user ? `${row.user.username}【${row.user.id}】` : `unknow【${row.user_id}】` }}</template>
                <template v-slot:status="{row,index}"><b :class="{'run-red': row.status === -1 , 'run-gray': row.status === 0 , 'run-green': row.status === 1}">{{ row.__status__ }}</b></template>
            </i-table>
        </template>

        <my-form ref="form" :id="current.id" :mode="myValue.mode" @on-success="getData"></my-form>

    </my-base>
</template>

<script src="./js/index.js"></script>
<style scoped>

</style>
