<template>

    <my-base>
        <template slot="search">
            <my-search-form @submit="searchEvent">

                <my-search-form-item name="模块">
                    <my-select :data="modules" v-model="search.module_id" empty="" @change="getCategories"></my-select>
                    <my-loading v-if="myValue.pending.getModules"></my-loading>
                </my-search-form-item>

                <my-search-form-item name="类型">
                    <i-select v-model="search.type" class="w-200">
                        <i-option v-for="(v,k) in TopContext.business.video.type" :key="k" :value="k">{{ v }}</i-option>
                    </i-select>
                </my-search-form-item>

                <my-search-form-item name="分类" v-show="search.type === 'misc'">
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

                <my-search-form-item name="用户">
                    <i-input
                            :value="myUser.id > 0 ? `${myUser.name}【${myUser.id}】` : ''"
                            class="w-200 run-cursor"
                            suffix="ios-search"
                            placeholder="请选择"
                            :readonly="true"
                            @click.native="showUserSelector"
                    ></i-input>
                </my-search-form-item>

                <my-search-form-item name="视频专题">
                    <i-input
                            :value="videoProject.id > 0 ? `${videoProject.name}【${videoProject.id}】` : ''"
                            class="w-200 run-cursor"
                            suffix="ios-search"
                            placeholder="请选择"
                            :readonly="true"
                            @click.native="showVideoProjectSelector"
                    ></i-input>
                </my-search-form-item>

                <my-search-form-item name="审核状态">
                    <i-select v-model="search.status" class="w-200">
                        <i-option v-for="(v,k) in TopContext.business.video.status" :key="k" :value="parseInt(k)">{{ v }}</i-option>
                    </i-select>
                </my-search-form-item>

                <my-search-form-item name="视频处理状态">
                    <i-select v-model="search.video_process_status" class="w-200">
                        <i-option v-for="(v,k) in TopContext.business.video.videoProcessStatus" :key="k" :value="parseInt(k)">{{ v }}</i-option>
                    </i-select>
                </my-search-form-item>

                <my-search-form-item name="文件处理状态">
                    <i-select v-model="search.file_process_status" class="w-200">
                        <i-option v-for="(v,k) in TopContext.business.video.fileProcessStatus" :key="k" :value="parseInt(k)">{{ v }}</i-option>
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
<!--            <my-table-button class="m-r-10" @click="editEventByButton"><my-icon icon="edit" />编辑</my-table-button>-->
<!--            <my-table-button class="m-r-10" type="error" @click="destroyAllEvent" :loading="myValue.pending.destroyAll"><my-icon icon="shanchu" />删除选中项 <span v-if="selection.length > 0">（{{ selection.length }}）</span></my-table-button>-->
<!--            <my-table-button class="m-r-10" @click="retryProcessVideosEvent" :loading="myValue.pending.retryProcessVideos"><my-icon icon="reset" />重新处理 <span v-if="selection.length > 0">（{{ selection.length }}）</span></my-table-button>-->
            <i-dropdown @on-click="updateVideoProcessStatusEvent">
                <my-table-button class="m-r-10" type="primary" :loading="myValue.pending.updateVideoProcessStatusEvent">
                    视频处理状态
                    <span v-if="selection.length > 0">（{{ selection.length }}）</span>
                    <i-icon type="ios-arrow-down"></i-icon>
                </my-table-button>
                <i-dropdown-menu slot="list">
                    <i-dropdown-item name="-1">处理失败</i-dropdown-item>
                    <i-dropdown-item name="0">待处理</i-dropdown-item>
                    <i-dropdown-item name="1">处理中</i-dropdown-item>
                    <i-dropdown-item name="2">转码中</i-dropdown-item>
                    <i-dropdown-item name="3">处理成功</i-dropdown-item>
                </i-dropdown-menu>
            </i-dropdown>

            <i-dropdown @on-click="updateFileProcessStatusEvent">
                <my-table-button class="m-r-10" type="primary" :loading="myValue.pending.updateFileProcessStatusEvent">
                    文件处理状态
                    <span v-if="selection.length > 0">（{{ selection.length }}）</span>
                    <i-icon type="ios-arrow-down"></i-icon>
                </my-table-button>
                <i-dropdown-menu slot="list">
                    <i-dropdown-item name="-1">处理失败</i-dropdown-item>
                    <i-dropdown-item name="0">待处理</i-dropdown-item>
                    <i-dropdown-item name="1">处理中</i-dropdown-item>
                    <i-dropdown-item name="2">处理成功</i-dropdown-item>
                </i-dropdown-menu>
            </i-dropdown>


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
                <template v-slot:thumb="{row,index}"><my-table-image-preview :src="row.__thumb__"></my-table-image-preview></template>
<!--                <template v-slot:thumb_for_program="{row,index}"><my-table-image-preview :src="row.thumb_for_program"></my-table-image-preview></template>-->
                <template v-slot:simple_preview="{row,index}">
                    <my-table-video-preview :src="row.simple_preview"></my-table-video-preview>
                </template>
                <template v-slot:preview="{row,index}">
                    <i-button @click.stop="openWindow(row.preview)">点击查看</i-button>
                </template>
                <template v-slot:user_id="{row,index}">
                    <my-table-text
                            :text="row.user ? `${row.user.username}【${row.user.id}】` : `unknow【${row.user_id}】`"
                            name="name"
                    ></my-table-text>
                </template>
                <template v-slot:module_id="{row,index}">
                    <my-table-text
                            :text="row.module ? `${row.module.name}【${row.module.id}】` : `unknow【${row.module_id}】`"
                            name="name"
                    ></my-table-text>
                </template>
                <template v-slot:category_id="{row,index}">
                    <my-table-text
                            :text="row.category ? `${row.category.name}【${row.category.id}】` : `unknow【${row.category_id}】`"
                            name="name"
                    ></my-table-text>
                </template>
                <template v-slot:video_project_id="{row,index}">
                    <my-table-text
                            :text="row.type === 'pro' ? (row.video_project ? `${row.video_project.name}【${row.video_project.id}】` : `unknow【${row.video_project_id}】`) : null "
                            name="name"
                    ></my-table-text>
                </template>
                <template v-slot:status="{row,index}">
                    <b :class="{'run-red': row.status === -1 , 'run-gray': row.status === 0 , 'run-green': row.status === 1}">{{ row.__status__ }}</b>
                </template>
                <template v-slot:video_process_status="{row,index}">
                    <i-tooltip max-width="200" :transfer="true" placement="top" :content="row.video_process_message ? row.video_process_message : '暂无处理信息'">
                        <b :class="{'run-gray': row.video_process_status === -1 , 'run-red': row.video_process_status === 0 , 'run-green': row.video_process_status >= 1}">{{ row.__video_process_status__ }}</b>
                    </i-tooltip>
                </template>
                <template v-slot:file_process_status="{row,index}">
                    <i-tooltip max-width="200" placement="top" :content="row.file_process_message ? row.file_process_message : '暂无处理信息'" :transfer="true">
                        <b :class="{'run-gray': row.file_process_status === -1 , 'run-red': row.file_process_status === 0 , 'run-green': row.file_process_status >= 1}">{{ row.__file_process_status__ }}</b>
                    </i-tooltip>
                </template>
                <template v-slot:action="{row , index}">
                    <my-tooltip content="点击查看web端详情">
                        <my-table-button
                                v-if="row.type === 'pro'"
                                @click="linkToShowForVideoProjectAtWeb(row)"
                        ><my-icon icon="web"></my-icon></my-table-button>
                        <my-table-button
                                v-if="row.type === 'misc'"
                                @click="linkToShowForVideoAtWeb(row)"
                        ><my-icon icon="web"></my-icon></my-table-button>
                    </my-tooltip>

                    <my-table-button @click="editEvent(row)"><my-icon icon="edit" /></my-table-button>

                    <my-table-button
                            @click="retryProcessVideoEvent(row)"
                            :loading="myValue.pending['retry_' + row.id]"
                            v-if="row.video_process_status !== 3 || row.file_process_status !== 2"
                    >
                        <my-icon icon="reset"></my-icon>
                    </my-table-button>

                    <my-table-button type="error" :loading="myValue.pending['delete_' + row.id]" @click="destroyEvent(index , row)">
                        <my-icon icon="shanchu"></my-icon>
                    </my-table-button>

                </template>
            </i-table>
        </template>

        <my-form ref="form" :id="current.id" :mode="myValue.mode" @on-success="getData"></my-form>

        <!-- 视频专题选择器 -->
        <my-video-project-selector
                ref="video-project-selector"
                :module-id="search.module_id"
                @on-change="videoProjectChangedEvent"
        ></my-video-project-selector>

        <my-user-selector
                ref="user-selector"
                @on-change="userChangedEvent"
        ></my-user-selector>
    </my-base>
</template>

<script src="./js/index.js"></script>
<style src="../public/css/base.css"></style>
<style scoped>

</style>
