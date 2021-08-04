<template>
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

                    <tr :class="{error: myValue.error.username}">
                        <td>名称</td>
                        <td>
                            <input type="text" v-model="form.username" @input="myValue.error.username=''" class="form-text">
                            <span class="need">*</span>
                            <div class="msg">长度最少6位</div>
                            <div class="e-msg">{{ myValue.error.username }}</div>
                        </td>
                    </tr>

                    <tr :class="{error: myValue.error.password}">
                        <td>密码</td>
                        <td>
                            <input type="text" v-model="form.password" @input="myValue.error.password=''" class="form-text">
                            <span class="need"></span>
                            <div class="msg">长度最少6位；<template v-if="mode === 'edit'">为空，则使用原密码</template></div>
                            <div class="e-msg">{{ myValue.error.password }}</div>
                        </td>
                    </tr>

                    <tr :class="{error: myValue.error.nickname}">
                        <td>昵称</td>
                        <td>
                            <input type="text" v-model="form.nickname" @input="myValue.error.username=''" class="form-text">
                            <span class="need">*</span>
                            <div class="msg"></div>
                            <div class="e-msg">{{ myValue.error.nickname }}</div>
                        </td>
                    </tr>

                    <tr :class="{error: myValue.error.avatar}">
                        <td>封面</td>
                        <td>
                            <div ref="avatar">
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

                    <tr :class="{error: myValue.error.sex}">
                        <td>性别</td>
                        <td>
                            <i-radio-group v-model="form.sex">
                                <i-radio v-for="(v,k) in TopContext.business.user.sex" :key="k" :label="k">{{ v }}</i-radio>
                            </i-radio-group>
                            <span class="need">*</span>
                            <div class="msg"></div>
                            <div class="e-msg">{{ myValue.error.sex }}</div>
                        </td>
                    </tr>

                    <tr :class="{error: myValue.error.birthday}">
                        <td>生日</td>
                        <td>
                            <i-date-picker
                                    type="date"
                                    v-model="myValue.birthday"
                                    format="yyyy-MM-dd"
                                    @on-change="birthdayChangeEvent"
                                    class="iview-form-input"
                                    :transfer="true"
                            ></i-date-picker>
                            <span class="need"></span>
                            <div class="msg"></div>
                            <div class="e-msg">{{ myValue.error.birthday }}</div>
                        </td>
                    </tr>

                    <tr :class="{error: myValue.error.phone}">
                        <td>手机号码</td>
                        <td>
                            <input type="text" v-model="form.phone" @input="myValue.error.phone=''" class="form-text">
                            <span class="need"></span>
                            <div class="msg"></div>
                            <div class="e-msg">{{ myValue.error.phone }}</div>
                        </td>
                    </tr>

                    <tr :class="{error: myValue.error.email}">
                        <td>电子邮件</td>
                        <td>
                            <input type="text" v-model="form.email" @input="myValue.error.email=''" class="form-text">
                            <span class="need"></span>
                            <div class="msg"></div>
                            <div class="e-msg">{{ myValue.error.email }}</div>
                        </td>
                    </tr>

                    <tr :class="{error: myValue.error.description}">
                        <td>描述</td>
                        <td>
                            <textarea v-model="form.description" @input="myValue.error.description=''" class="form-textarea"></textarea>
                            <span class="need"></span>
                            <div class="msg"></div>
                            <div class="e-msg">{{ myValue.error.description }}</div>
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
</template>

<script src="./js/form.js"></script>

<style scoped>

</style>
