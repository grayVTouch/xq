<template>
    <my-base ref="base">
        <div class="mask">

            <div class="line search hide"></div>

            <div class="line data">

                <div class="run-title">
                    <div class="left">
                        数据列表&nbsp;&nbsp;&nbsp;

                        <my-table-button v-if="TopContext.debug" @click="addEvent"><my-icon icon="add" />添加</my-table-button>
                        <my-table-button type="error" @click="destroyAllEvent" :loading="myValue.pending.destroyAll" v-if="TopContext.debug" v-show="showBatchBtn"><my-icon icon="shanchu" />删除选中项 （{{ selection.length }}）</my-table-button>
                    </div>
                    <div class="right"></div>
                </div>

                <div class="table">

                    <i-table border  :columns="table.field" :data="table.data" @on-selection-change="selectionChangeEvent" :loading="myValue.pending.getData">
                        <template v-slot:cn="{row,index}">
                            <template v-if="row.floor > 1">{{ '|' + '_'.repeat(row.floor * 2) + row.cn }}</template>
                            <template v-else>{{ row.cn }}</template>
                        </template>
                        <template v-slot:s_ico="{row,index}"><img :src="row.s_ico ? row.__s_ico__ : TopContext.res.notFound" class="image" :height="TopContext.table.imageH" @click.stop="openWindow(row.__s_ico__)"></template>
                        <template v-slot:b_ico="{row,index}"><img :src="row.b_ico ? row.__b_ico__ : TopContext.res.notFound" class="image" :height="TopContext.table.imageH" @click.stop="openWindow(row.__b_ico__)"></template>
                        <template v-slot:is_menu="{row,index}"><my-switch v-model="row.is_menu" :loading="myValue.pending['is_menu_' + row.id]" :extra="{id: row.id , field: 'is_menu'}" @on-change="updateBoolValEvent" /></template>
                        <template v-slot:is_view="{row,index}"><my-switch v-model="row.is_view" :loading="myValue.pending['is_view_' + row.id]" :extra="{id: row.id , field: 'is_view'}" @on-change="updateBoolValEvent" /></template>
                        <template v-slot:enable="{row,index}"><my-switch v-model="row.enable" :loading="myValue.pending['enable_' + row.id]" :extra="{id: row.id , field: 'enable'}" @on-change="updateBoolValEvent" /></template>
                        <template v-slot:action="{row , index}">
                            <my-table-button @click="editEvent(row)"><my-icon icon="edit" />编辑</my-table-button>
                            <my-table-button type="error" :loading="myValue.pending['delete_' + row.id]" @click="destroyEvent(index , row)"><my-icon icon="shanchu" />删除</my-table-button>
                        </template>
                    </i-table>

                </div>


            </div>

            <div class="line operation">
                <my-table-button v-if="TopContext.debug" @click="addEvent"><my-icon icon="add" />添加</my-table-button>
                <my-table-button type="error" @click="destroyAllEvent" :loading="myValue.pending.destroyAll" v-if="TopContext.debug" v-show="showBatchBtn"><my-icon icon="shanchu" />删除选中项 （{{ selection.length }}）</my-table-button>
            </div>

            <div class="line page hide"></div>

            <my-form
                    ref="form"
                    :data="form"
                    :mode="myValue.mode"
                    @on-success="getData">

            </my-form>
        </div>
    </my-base>
</template>

<script src="./js/index.js"></script>

<style scoped>
    .mask {

    }

    .mask > .line {
        margin-bottom: 15px;
    }

    .mask > .line:nth-last-of-type(1) {
        margin-bottom: 0;
    }

    .mask > .data > .table {
        overflow: hidden;
        overflow-x: auto;
        width: 100%;
    }
</style>
