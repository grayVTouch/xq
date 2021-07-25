<template>

    <my-base>
        <template slot="search">
            <my-search-form @submit="searchEvent">

                <my-search-form-item name="模块">
                    <my-select :data="modules" v-model="search.module_id" empty="" @change="getCategories"></my-select>
                    <my-loading v-if="myValue.pending.getModules"></my-loading>
                </my-search-form-item>

                <my-search-form-item name="类型">
                    <i-radio-group v-model="search.type" @on-change="typeChangedEvent">
                        <i-radio v-for="(v,k) in TopContext.business.imageProject.type" :key="k" :label="k">{{ v }}</i-radio>
                    </i-radio-group>
                </my-search-form-item>

                <my-search-form-item name="分类">
                    <my-deep-select :data="categories" v-model="search.category_id" :has="false" empty="">
                        <template v-slot:extra="{row , index}" v-if="!search.type">【{{ row.__type__ }}】</template>
                    </my-deep-select>
                    <my-loading v-if="myValue.pending.getCategories"></my-loading>
                    <span class="msg">请选择模块后操作</span>
                </my-search-form-item>

                <my-search-form-item name="图片主体">
                    <i-input
                            :value="imageSubject.id > 0 ? `${imageSubject.name}【${imageSubject.id}】` : ''"
                            class="w-200 run-cursor"
                            suffix="ios-search"
                            placeholder="请选择"
                            :readonly="true"
                            @click.native="showImageSubjectSelector"
                    ></i-input>
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

                <my-search-form-item name="状态">
                    <i-radio-group v-model="search.status">
                        <i-radio v-for="(v,k) in TopContext.business.imageProject.status" :key="k" :label="parseInt(k)">{{ v }}</i-radio>
                    </i-radio-group>
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
            <my-table-button @click="retryProcessEvent" :loading="myValue.pending.retryProcess"><my-icon icon="reset" />重新处理 <span v-if="selection.length > 0">（{{ selection.length }}）</span></my-table-button>
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
                    @on-row-dblclick="editEvent"
                    @on-row-click="rowClickEvent"
                    @on-sort-change="sortChangeEvent"
            >
                <template v-slot:thumb="{row,index}">
                    <my-table-image-preview :src="row.thumb"></my-table-image-preview>
                </template>
                <template v-slot:user_id="{row,index}">
                    {{ row.user ? `${row.user.username}【${row.user.id}】` : `unknow【${row.user_id}】` }}
                </template>
                <template v-slot:module_id="{row,index}">
                    {{ row.module ? `${row.module.name}【${row.module.id}】` : `unknow【${row.module_id}】` }}
                </template>
                <template v-slot:category_id="{row,index}">
                    {{ row.category ? `${row.category.name}【${row.category.id}】` : `unknow【${row.category_id}】` }}
                </template>
                <template v-slot:image_subject_id="{row,index}">
                    {{ row.type === 'pro' ? (row.image_subject ? `${row.image_subject.name}【${row.image_subject.id}】` : `unknow【${row.image_subject_id}】`) : null }}
                </template>

                <template v-slot:status="{row,index}">
                    <b :class="{'run-red': row.status === -1 , 'run-gray': row.status === 0 , 'run-green': row.status === 1}">{{ row.__status__ }}</b>
                </template>

                <template v-slot:images="{row,index}">

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

                <template v-slot:process_status="{row,index}">
                    <i-tooltip max-width="200" :transfer="true" placement="top" :content="row.process_message ? row.process_message : '暂无处理信息'">
                        <b :class="{'run-gray': row.process_status === -1 , 'run-red': row.process_status === 0 , 'run-green': row.process_status >= 1}">{{ row.__process_status__ }}</b>
                    </i-tooltip>
                </template>

                <template v-slot:action="{row,index}">
                    <my-tooltip content="点击查看web端详情">
                        <my-table-button v-if="row.type === 'pro'" @click="linkToShowForImageProjectAtWeb(row)"><my-icon icon="web"></my-icon></my-table-button>
                    </my-tooltip>
                    <my-tooltip content="点击查看图片列表">
                        <my-table-button @click="showImagePreview(row)"><my-icon icon="shangchuantupian"></my-icon>【{{ row.images.length }}P】</my-table-button>
                    </my-tooltip>
                </template>

            </i-table>

        </template>

        <my-form
                ref="form"
                :id="current.id"
                :mode="myValue.mode"
                @on-success="getData"
        ></my-form>

        <my-image-preview
            :visible.sync="myValue.showImagePreview"
            :images="current.images"
        ></my-image-preview>

        <my-image-subject-selector
                ref="image-subject-selector"
                :module-id="search.module_id"
                @on-change="imageSubjectChangedEvent"
        ></my-image-subject-selector>

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
