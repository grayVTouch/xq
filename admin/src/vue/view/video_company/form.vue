<template>
    <div>
        <my-form-modal
                v-model="myValue.show"
                :title="title"
                :width="700"
                :loading="myValue.pending.submitEvent || myValue.pending.findById"
                @on-ok="submitEvent"
                @on-cancel="closeFormModal"
        >
            <template slot="default">
                <form class="form" @submit.prevent="submitEvent">
                    <table class="input-table">
                        <tbody>
                        <tr :class="{error: myValue.error.name}">
                            <td>名称</td>
                            <td>
                                <input type="text" v-model="form.name" @input="myValue.error.name=''" class="form-text">
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

                        <tr :class="{error: myValue.error.module_id}">
                            <td>所属模块</td>
                            <td>
                                <my-select :data="modules" :disabled="mode === 'add'" v-model="form.module_id" @change="myValue.error.module_id = ''" :width="TopContext.style.inputItemW"></my-select>
                                <i-button type="primary" :loading="myValue.pending.getModules" @click="getModules">刷新</i-button>
                                <span class="need">*</span>
                                <div class="msg"></div>
                                <div class="e-msg">{{ myValue.error.module_id }}</div>
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
                                <div class="e-msg">{{ myValue.error.thumb }}</div>
                            </td>
                        </tr>

                        <tr :class="{error: myValue.error.country_id}">
                            <td>所属国家：</td>
                            <td>
                                <input type="text" readonly="readonly" :value="`${country.name}【${country.id}】`" class="form-text w-150 run-cursor-not-allow">
                                如需重新搜索，请点击
                                <i-button @click="showCountrySelector">搜索</i-button>
                                <span class="need">*</span>
                                <div class="msg">请务必在选择模块后操作</div>
                                <div class="e-msg">{{ myValue.error.country_id }}</div>
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

                        <tr :class="{error: myValue.error.status}">
                            <td>状态</td>
                            <td>
                                <i-radio-group v-model="form.status">
                                    <i-radio v-for="(v,k) in TopContext.business.imageSubject.status" :key="k" :label="parseInt(k)">{{ v }}</i-radio>
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

                        <tr :class="{error: myValue.error.weight}">
                            <td>权重</td>
                            <td>
                                <input type="number" v-model="form.weight" @input="myValue.error.weight = ''" class="form-text">
                                <span class="msg">仅允许整数</span>
                                <span class="need"></span>
                                <span class="e-msg"></span>
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
        <my-country-selector ref="country-selector" @on-change="countryChangeEvent"></my-country-selector>

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
</template>

<script src="./js/form.js"></script>

<style scoped>

</style>
