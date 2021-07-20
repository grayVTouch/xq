<template>
    <my-form-drawer
            :title="title"
            v-model="myValue.show"
            :width="950"
    >

        <template slot="header">

            <div class="run-action-title">
                <div class="left">{{ title }}</div>
                <div class="right">
                    <i-button v-ripple type="primary" :loading="myValue.pending.submitEvent || myValue.pending.findById" @click="submitEvent"><my-icon icon="tijiao" />提交</i-button>
                    <i-button v-ripple type="error" @click="closeFormModal"><my-icon icon="guanbi" />关闭</i-button>
                </div>
            </div>

        </template>

        <template slot="default">
            <form @submit.prevent="submitEvent" class="form">
                <div class="menu">
                    <div class="menu-item" v-ripple :class="{cur: myValue.tab === 'base'}" @click="myValue.tab = 'base'">基本信息</div>
                    <div class="menu-item" v-ripple :class="{cur: myValue.tab === 'image'}" @click="myValue.tab = 'image'">图片信息</div>
                </div>
                <div class="menu-mapping-content">
                    <div class="" v-show="myValue.tab === 'base'">
                        <table class="input-table">
                            <tbody>

                            <tr :class="{error: myValue.error.name}">
                                <td>名称：</td>
                                <td>
                                    <input type="text" class="form-text" v-model="form.name" @input="myValue.error.name = ''">
                                    <span class="need">*</span>
                                    <div class="msg"></div>
                                    <div class="e-msg">{{ myValue.error.name }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.user_id}">
                                <td>所属用户：</td>
                                <td>
                                    <input type="text" readonly="readonly" :value="`${owner.username}【${owner.id}】`" class="form-text w-150 run-cursor-not-allow">
                                    如需重新搜索，请点击
                                    <i-button @click="showUserSelector">搜索</i-button>
                                    <span class="need">*</span>
                                    <div class="msg"></div>
                                    <div class="e-msg">{{ myValue.error.user_id }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.type}">
                                <td>类型：</td>
                                <td>
                                    <i-select v-model="form.type" class="w-400" :disabled="true">
                                        <i-option v-for="(v,k) in TopContext.business.imageProject.type" :key="k" :value="k">{{ v }}</i-option>
                                    </i-select>
                                    <span class="need">*</span>
                                    <div class="msg"></div>
                                    <div class="e-msg">{{ myValue.error.type }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.module_id}">
                                <td>所属模块：</td>
                                <td>

                                    <my-select :data="modules" :disabled="mode === 'add'" v-model="form.module_id" @change="moduleChangedEvent" :width="TopContext.style.inputItemW"></my-select>
                                    <i-button type="primary" :loading="myValue.pending.getModules" @click="getModules">刷新</i-button>
                                    <span class="need">*</span>
                                    <div class="msg"></div>
                                    <div class="e-msg">{{ myValue.error.module_id }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.category_id}">
                                <td>所属分类：</td>
                                <td>
                                    <my-deep-select :data="categories" v-model="form.category_id" @change="myValue.error.category_id = ''" :has="false" :width="TopContext.style.inputItemW"></my-deep-select>
                                    <i-button type="primary" :loading="myValue.pending.getCategories" @click="getCategories">刷新</i-button>
                                    <span class="need">*</span>
                                    <div class="msg">请务必在选择类型、模块后操作</div>
                                    <div class="e-msg">{{ myValue.error.category_id }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.image_subject_id}" v-show="form.type === 'pro'">
                                <td>图片主体：</td>
                                <td>
                                    <input type="text" readonly="readonly" :value="`${imageSubject.name}【${imageSubject.id}】`" class="form-text w-150 run-cursor-not-allow">
                                    如需重新搜索，请点击
                                    <i-button @click="showImageSubjectSelector">搜索</i-button>
                                    <span class="need">*</span>
                                    <div class="msg">请务必在选择模块后操作</div>
                                    <div class="e-msg">{{ myValue.error.image_subject_id }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.tags}">
                                <td>标签：</td>
                                <td>
                                    <div class="tags">
                                        <div class="line top">

                                            <div class="active-tag" v-for="v in form.tags" @click="destroyTag(v.tag_id , false)">
                                                <div class="text"><my-loading size="18" color="#b1b6bd" v-if="myValue.pending['destroy_tag_' + v.tag_id]" />{{ v.name }}</div>
                                                <div class="close">
                                                    <div class="inner">
                                                        <div class="positive"></div>
                                                        <div class="negative"></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="active-tag" v-for="v in tags" @click="destroyTag(v.id)">
                                                <div class="text">{{ v.name }}</div>
                                                <div class="close">
                                                    <div class="inner">
                                                        <div class="positive"></div>
                                                        <div class="negative"></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="tag-input" ref="tag-input-outer"><span contenteditable="true" ref="tag-input" class="input" @input="myValue.error.tags = ''" @keyup.enter="createOrAppendTag"></span></div>
                                        </div>
                                        <div class="line btm">
                                            <h5 class="title">推荐标签（选择模块后该列表会更新）</h5>
                                            <div class="__tags__">
                                                <span class="tag run-action-feedback" v-for="v in topTags" @click="appendTag(v)">{{ v.name }}</span>
                                                <!--                                                <span class="tag run-action-feedback">发放</span>-->
                                            </div>
                                        </div>
                                    </div>
                                    <span class="need"></span>
                                    <div class="msg">请务必在选择用户、模块后操作；输入框按回车键可搜寻已有标签，如不存在会自动创建</div>
                                    <div class="e-msg">{{ myValue.error.tags }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.thumb}">
                                <td>封面</td>
                                <td>
                                    <div ref="thumb">
                                        <!-- 上传图片组件 -->
                                        <div class='uploader'>
                                            <div class="upload">
                                                <div class="handler">

                                                    <div class="line input hide">
                                                        <input type="file" class="file-input">
                                                    </div>

                                                    <div class="line icon">
                                                        <div class="ico">
                                                            <div class="feedback run-action-feedback-round"><i class="iconfont run-iconfont run-iconfont-shangchuan"></i></div>
                                                            <div class="clear run-action-feedback-round" title="清空"><i class="iconfont run-iconfont run-iconfont-qingkong"></i></div>
                                                        </div>
                                                        <div class="text">请选择要上传的文件</div>
                                                    </div>

                                                </div>

                                                <div class="msg"></div>
                                            </div>
                                            <div class="preview"></div>

                                        </div>
                                    </div>

                                    <span class="need"></span>
                                    <div class="msg"></div>
                                    <div class="e-msg">{{ myValue.error.thumb }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.weight}">
                                <td>权重</td>
                                <td>
                                    <input type="number" v-model="form.weight" @input="myValue.error.weight = ''" class="form-text">
                                    <span class="need"></span>
                                    <div class="msg">仅允许整数</div>
                                    <div class="e-msg">{{ myValue.error.thumb }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.view_count}">
                                <td>浏览次数</td>
                                <td>
                                    <input type="number" v-model="form.view_count" @input="myValue.error.view_count = ''" class="form-text">
                                    <span class="need"></span>
                                    <div class="msg">仅允许整数</div>
                                    <div class="e-msg">{{ myValue.error.view_count }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.praise_count}">
                                <td>获赞次数</td>
                                <td>
                                    <input type="number" v-model="form.praise_count" @input="myValue.error.praise_count = ''" class="form-text">
                                    <span class="need"></span>
                                    <div class="msg">仅允许整数</div>
                                    <div class="e-msg">{{ myValue.error.praise_count }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.status}">
                                <td>状态</td>
                                <td>
                                    <i-radio-group v-model="form.status">
                                        <i-radio v-for="(v,k) in TopContext.business.imageProject.status" :key="k" :label="parseInt(k)">{{ v }}</i-radio>
                                    </i-radio-group>
                                    <span class="need">*</span>
                                    <div class="msg">默认：待审核</div>
                                    <div class="e-msg">{{ myValue.error.status }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.fail_reason}" v-show="form.status === -1">
                                <td>失败原因</td>
                                <td>
                                    <textarea v-model="form.fail_reason" class="form-textarea" @input="myValue.error.fail_reason = ''"></textarea>
                                    <span class="need">*</span>
                                    <div class="msg">当状态为审核失败的时候必填</div>
                                    <div class="e-msg">{{ myValue.error.fail_reason }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.description}">
                                <td>描述</td>
                                <td>
                                    <textarea v-model="form.description" class="form-textarea"></textarea>
                                    <span class="need"></span>
                                    <div class="msg"></div>
                                    <div class="e-msg">{{ myValue.error.description }}</div>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="2">
                                    <button class="hide" type="submit"><my-icon icon="tijiao" />提交</button>
                                    <i-button v-ripple type="primary" :loading="myValue.pending.submitEvent || myValue.pending.findById" @click="submitEvent"><my-icon icon="tijiao" />提交</i-button>
                                </td>
                            </tr>

                            </tbody>
                        </table>
                    </div>
                    <div class="" v-show="myValue.tab === 'image'">
                        <div class="image-info">
                            <div class="line upload">
                                <div class="run-title">
                                    <div class="left">上传图片</div>
                                    <div class="right"></div>
                                </div>
                                <div>
                                    <table class="input-table">
                                        <tbody>
                                        <tr :class="{error: myValue.error.images}">
                                            <td>
                                                <div ref="images">
                                                    <!-- 上传图片组件 -->
                                                    <div class='uploader'>
                                                        <div class="upload">
                                                            <div class="handler">

                                                                <div class="line input hide">
                                                                    <input type="file" class="file-input">
                                                                </div>

                                                                <div class="line icon">
                                                                    <div class="ico">
                                                                        <div class="feedback run-action-feedback-round"><i class="iconfont run-iconfont run-iconfont-shangchuan"></i></div>
                                                                        <div class="clear run-action-feedback-round" title="清空"><i class="iconfont run-iconfont run-iconfont-qingkong"></i></div>
                                                                    </div>
                                                                    <div class="text">请选择要上传的文件</div>
                                                                </div>

                                                            </div>

                                                            <div class="msg"></div>
                                                        </div>
                                                        <div class="preview"></div>

                                                    </div>
                                                </div>

                                                <span class="need"></span>
                                                <div class="msg"></div>
                                                <div class="e-msg">{{ myValue.error.images }}</div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="images" v-if="mode === 'edit'">
                                <div class="line">
                                    <div class="run-title">
                                        <div class="left">图片列表</div>
                                        <div class="right">
                                            <my-table-button type="error" :loading="myValue.pending['destroyAll']" @click="destroyAllEvent">删除选中项 <span v-if="selection.length > 0">（{{ selection.length }}）</span></my-table-button>
                                        </div>
                                    </div>
                                    <div>
                                        <i-table
                                                ref="table"
                                                class="w-r-100"
                                                border
                                                :columns="imagesTableData.field"
                                                :data="imagesTableData.data"
                                                @on-selection-change="selectionChangeEvent"
                                                @on-row-click="rowClickEvent"
                                        >
                                            <template v-slot:src="{row,index}">
                                                <my-table-image-preview :src="row.src"></my-table-image-preview>
                                            </template>
                                            <template v-slot:original_src="{row,index}">
                                                <i-button @click="openWindow(row.original_src , '_blank')">点击查看原图</i-button>
                                            </template>
                                            <template v-slot:action="{row,index}">
                                                <my-table-button :loading="myValue.pending['delete_' + row.id]" @click="destroyEvent(index , row)">删除</my-table-button>
                                            </template>
                                        </i-table>
                                    </div>
                                </div>

                                <div class="line pager">
                                    <my-page
                                        :total="imagesTableData.all.length"
                                        :size="imagesTableData.size"
                                        :sizes="imagesTableData.sizes"
                                        :page="imagesTableData.page"
                                        @on-page-change="pageEventForImages"
                                        @on-size-change="sizeEventForImages"
                                    ></my-page>
                                </div>

                                <div class="line">
                                    <my-table-button type="error" :loading="myValue.pending['destroyAll']" @click="destroyAllEvent">删除选中项 <span v-if="selection.length > 0">（{{ selection.length }}）</span></my-table-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- 请选择图片主体 -->
            <my-image-subject-selector ref="image-subject-selector" :moduleId="form.module_id" @on-change="imageSubjectChangeEvent"></my-image-subject-selector>

            <!-- 用户选择器 -->
            <my-user-selector ref="user-selector" @on-change="userChangeEvent"></my-user-selector>

            <!-- 第一步：模块选择器 -->
            <my-form-modal
                    v-model="myValue.showModuleSelector"
                    title="请选择"
                    width="auto"
                    :mask-closable="true"
                    :closable="true"
            >
                <span class="f-12">所属模块：</span>
                <my-select :width="TopContext.style.inputItemW" :data="modules" v-model="form.module_id" @on-change="moduleChangedEvent"></my-select>
                <span class="need run-red">*</span>

                <template slot="footer">
                    <i-button type="primary" @click="handleStep('type')">确认</i-button>
                </template>
            </my-form-modal>

            <!-- 第二步：类型选择器 -->
            <my-form-modal
                    v-model="myValue.showTypeSelector"
                    title="请选择"
                    width="auto"
                    :mask-closable="true"
                    :closable="true"
            >
                <span class="f-12">所属类型：</span>
                <i-select v-model="form.type" class="w-400" @on-change="typeChangedEvent">
                    <i-option v-for="(v,k) in TopContext.business.imageProject.type" :key="k" :value="k">{{ v }}</i-option>
                </i-select>
                <span class="need run-red">*</span>

                <template slot="footer">
                    <i-button type="primary" @click="handleStep('form')">确认</i-button>
                </template>
            </my-form-modal>

        </template>
    </my-form-drawer>
</template>

<script src="./js/form.js"></script>
<style scoped src="./css/form.css"></style>
