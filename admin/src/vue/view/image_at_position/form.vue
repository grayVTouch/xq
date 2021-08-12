<template>
    <div>
        <my-form-modal
                v-model="myValue.show"
                :title="title"
                :loading="myValue.pending.submitEvent || myValue.pending.findById"
                @on-ok="submitEvent"
                @on-cancel="closeFormModal"
        >
            <template slot="default">
                <form class="form" @submit.prevent="submitEvent">
                    <table class="input-table">
                        <tbody>

                        <tr :class="{error: myValue.error.module_id}">
                            <td>所属模块</td>
                            <td>
                                <my-select :data="modules" :disabled="mode === 'add'" v-model="form.module_id" @change="myValue.error.module_id = ''" :width="TopContext.style.inputItemW"></my-select>
                                <my-loading v-if="myValue.pending.getModules"></my-loading>
                                <span class="need">*</span>
                                <div class="msg"></div>
                                <div class="e-msg">{{ myValue.error.module_id }}</div>
                            </td>
                        </tr>

                        <tr :class="{error: myValue.error.position_id}">
                            <td>位置</td>
                            <td>
                                <i-select v-model="form.position_id" @on-change="myValue.error.position_id = ''" class="w-400">
                                    <i-option v-for="v in positions" :value="v.id" :key="v.id">{{ v.name + `【${v.platform}】` }}</i-option>
                                </i-select>
                                <my-loading v-if="myValue.pending.getPositions"></my-loading>
                                <span class="need">*</span>
                                <div class="msg"></div>
                                <div class="e-msg">{{ myValue.error.position_id }}</div>
                            </td>
                        </tr>

                        <tr :class="{error: myValue.error.src}">
                            <td>图片</td>
                            <td>
                                <div ref="src">
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
                                                    <div class="text">请选择要上传的文件或拖拽文件到此</div>
                                                </div>

                                            </div>

                                            <div class="msg"></div>
                                        </div>
                                        <div class="preview"></div>

                                    </div>
                                </div>

                                <span class="need"></span>
                                <div class="msg"></div>
                                <div class="e-msg">{{ myValue.error.src }}</div>
                            </td>
                        </tr>

                        <tr :class="{error: myValue.error.link}">
                            <td>链接</td>
                            <td>
                                <input type="text" v-model="form.link" @input="myValue.error.link=''" class="form-text">
                                <span class="need"></span>
                                <div class="msg"></div>
                                <div class="e-msg">{{ myValue.error.link }}</div>
                            </td>
                        </tr>

                        <tr v-show="false">
                            <td colspan="2">
                                <button type="submit"></button>
                            </td>
                        </tr>

                        </tbody>
                    </table>
                </form>
            </template>
            <template slot="footer">
                <i-button v-ripple type="primary" :loading="myValue.pending.submitEvent || myValue.pending.findById" @click="submitEvent">确认</i-button>
                <i-button v-ripple type="error" @click="closeFormModal">关闭</i-button>
            </template>
        </my-form-modal>

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
            <my-select :width="TopContext.style.inputItemW" :data="modules" v-model="form.module_id" @change="myValue.error.module_id = ''"></my-select>
            <span class="need run-red">*</span>

            <template slot="footer">
                <i-button type="primary" @click="nextStepAtForm">确认</i-button>
            </template>
        </my-form-modal>
    </div>
    </div>
</template>

<script src="./js/form.js"></script>

<style scoped>

</style>
