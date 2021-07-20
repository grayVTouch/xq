<template>

    <my-base>
        <template slot="search">

            <my-search-form @submit="searchEvent">
                <my-search-form-item name="ID">
                    <input type="text" class="form-text" v-model="search.id" />
                </my-search-form-item>

                <my-search-form-item name="用户名">
                    <input type="text" class="form-text" v-model="search.username" />
                </my-search-form-item>

                <my-search-form-item name="性别">
                    <i-radio-group v-model="search.sex">
                        <i-radio v-for="(v,k) in TopContext.business.user.sex" :key="k" :label="k">{{ v }}</i-radio>
                    </i-radio-group>
                </my-search-form-item>

                <my-search-form-item name="手机">
                    <input type="text" class="form-text" v-model="search.phone" />
                </my-search-form-item>

                <my-search-form-item name="电子邮箱">
                    <input type="text" class="form-text" v-model="search.email" />
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

                    :columns="table.field" :data="table.data"
                    @on-selection-change="selectionChangeEvent"
                    :loading="myValue.pending.getData"
                    @on-row-click="rowClickEvent"
                    @on-row-dblclick="rowDblclickEvent"
                    @on-sort-change="sortChangeEvent"
            >
                <template v-slot:avatar="{row,index}">
                    <my-table-image-preview :src="row.avatar"></my-table-image-preview>
                </template>

                <template v-slot:is_system="{row,index}"><b :class="{'run-green': row.is_system === 1 , 'run-red': row.is_system === 0}">{{ row.__is_system__ }}</b></template>

                <template v-slot:action="{row , index}">
                    <my-table-button @click="editEvent(row)"><my-icon icon="edit" />编辑</my-table-button>
                    <my-table-button type="error" :loading="myValue.pending['delete_' + row.id]" @click="destroyEvent(index , row)"><my-icon icon="shanchu" />删除</my-table-button>
                </template>
            </i-table>
        </template>

        <my-form ref="form" :id="current.id" :mode="myValue.mode" @on-success="getData"></my-form>

    </my-base>
</template>

<script src="./js/index.js"></script>
<style scoped>

</style>
