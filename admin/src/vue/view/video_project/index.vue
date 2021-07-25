<template>

    <my-base>
        <template slot="search">
            <my-search-form @submit="searchEvent">
                <my-search-form-item name="模块">
                    <my-select :clear="true" :data="modules" empty="" v-model="search.module_id"></my-select>
                    <my-loading v-if="myValue.pending.getModules"></my-loading>
                </my-search-form-item>

                <my-search-form-item name="分类">
                    <my-deep-select :data="categories" v-model="search.category_id" :has="false" empty=""></my-deep-select>
                    <my-loading v-if="myValue.pending.getCategories"></my-loading>
                    <span class="msg">请选择模块后操作</span>
                </my-search-form-item>

                <my-search-form-item name="ID">
                    <input type="text" class="form-text" v-model="search.id" />
                </my-search-form-item>

                <my-search-form-item name="名称">
                    <input type="text" class="form-text" v-model="search.name" />
                </my-search-form-item>

                <my-search-form-item name="视频系列">
                    <i-input
                            :value="myVideoSeries.id > 0 ? `${myVideoSeries.name}【${myVideoSeries.id}】` : ''"
                            class="w-200 run-cursor"
                            suffix="ios-search"
                            placeholder="请选择"
                            :readonly="true"
                            @click.native="openVideoSeriesSelector"
                    ></i-input>
                </my-search-form-item>

                <my-search-form-item name="视频制作公司">
                    <i-input
                            :value="myVideoCompany.id > 0 ? `${myVideoCompany.name}【${myVideoCompany.id}】` : ''"
                            class="w-200 run-cursor"
                            suffix="ios-search"
                            placeholder="请选择"
                            :readonly="true"
                            @click.native="openVideoCompanySelector"
                    ></i-input>
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
                    :loading="myValue.pending.getData"
                    @on-selection-change="selectionChangeEvent"
                    @on-row-click="rowClickEvent"
                    @on-row-dblclick="rowDblclickEvent"
                    @on-sort-change="sortChangeEvent"
            >
                <template v-slot:name="{row,index}">{{ row.name }}</template>
                <template v-slot:thumb="{row,index}"><my-table-image-preview :src="row.thumb"></my-table-image-preview></template>
                <template v-slot:module_id="{row,index}">{{ row.module ? `${row.module.name}【${row.module.id}】` : `unknow【${row.module_id}】` }}</template>
                <template v-slot:category_id="{row,index}">{{ row.category ? `${row.category.name}【${row.category.id}】` : `unknow【${row.category_id}】` }}</template>
                <template v-slot:video_series_id="{row,index}">{{ row.video_series ? `${row.video_series.name}【${row.video_series.id}】` : `unknow【${row.video_series_id}】` }}</template>
                <template v-slot:video_company_id="{row,index}">{{ row.video_company ? `${row.video_company.name}【${row.video_company.id}】` : `unknow【${row.video_company_id}】` }}</template>
                <template v-slot:user_id="{row,index}">{{ row.user ? `${row.user.username}【${row.user.id}】` : `unknow【${row.user_id}】` }}</template>
                <template v-slot:status="{row,index}"><b :class="{'run-red': row.status === -1 , 'run-gray': row.status === 0 , 'run-green': row.status === 1}">{{ row.__status__ }}</b></template>
                <template v-slot:end_status="{row,index}"><b :class="{'run-red': row.end_status === 'making' , 'run-gray': row.end_status === 'terminated' , 'run-green': row.end_status === 'completed'}">{{ row.__end_status__ }}</b></template>
                <template v-slot:file_process_status="{row,index}">
                    <i-tooltip max-width="200" :transfer="true" placement="top" :content="row.file_process_status ? row.file_process_status : '暂无处理信息'">
                        <b :class="{'run-gray': row.file_process_status === -1 , 'run-red': row.file_process_status === 0 , 'run-green': row.file_process_status >= 1}">{{ row.__file_process_status__ }}</b>
                    </i-tooltip>
                </template>
                <template v-slot:tags="{row,index}">
                    <i-poptip placement="right" width="400" title="标签" :transfer="true" trigger="hover">
                        <i-button>悬浮可查看详情</i-button>
                        <div slot="content">
                            <table class="line-table">
                                <tbody>
                                <tr v-for="v in row.tags" :key="v.id">
                                    <td>{{ v.name }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </i-poptip>
                </template>
                <template v-slot:status="{row,index}">
                    <span class="run-weight" :class="{'run-red': row.status == 'making' , 'run-green': row.status === 'completed' , 'run-gray': row.status === 'terminated'}">{{ row.__status__ }}</span>
                </template>
                <template v-slot:action="{row,index}">
<!--                    <my-table-button @click="editEvent(row)"><my-icon icon="edit" />编辑</my-table-button>-->
<!--                    <my-table-button type="error" :loading="myValue.pending['delete_' + row.id]" @click="destroyEvent(index , row)"><my-icon icon="shanchu" />删除</my-table-button>-->

                    <my-tooltip content="点击查看web端详情">
                        <my-table-button @click="linkToShowAtWeb(row)"><my-icon icon="web"></my-icon></my-table-button>
                    </my-tooltip>
                </template>
            </i-table>
        </template>

        <my-form ref="form" :mode="myValue.mode" :id="current.id" @on-success="getData"></my-form>

        <my-video-series-selector
                ref="video-series-selector"
                :module-id="search.module_id"
                @on-change="videoSeriesChangedEvent"
        ></my-video-series-selector>

        <my-video-company-selector
                ref="video-company-selector"
                :module-id="search.module_id"
                @on-change="videoCompanyChangedEvent"
        ></my-video-company-selector>
    </my-base>
</template>

<script src="./js/index.js"></script>
<style scoped>

</style>
