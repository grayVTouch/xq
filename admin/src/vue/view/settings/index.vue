<template>

    <my-base
        :show-search="false"
        :show-data="false"
        :show-actions="false"
    >
        <form
                class="my-form"
                @submit.prevent="submitEvent"
        >
            <div class="line">

                <Tabs v-model="myValue.tab">

                    <TabPane name="admin_settings" label="通用设置">

                        <div class="block">
                            <div class="run-title">
                                <div class="left">登录设置</div>
                                <div class="right"></div>
                            </div>
                            <table class="input-table">
                                <tbody>

                                <tr :class="{error: myValue.error.web_url}">
                                    <td>启用验证码？</td>
                                    <td>
                                        <radio-group v-model="systemSettings.is_enable_grapha_verify_code_for_login">
                                            <radio v-for="(v,k) in TopContext.business.bool.integer" :key="k" :label="parseInt(k)">{{ v }}</radio>
                                        </radio-group>
                                        <span class="need"></span>
                                        <div class="msg">例：https://www.test.com</div>
                                        <div class="e-msg">{{ myValue.error.web_url }}</div>
                                    </td>
                                </tr>

                                </tbody>
                            </table>
                        </div>

                        <div class="block m-t-10">
                            <div class="run-title">
                                <div class="left">存储设置</div>
                                <div class="right"></div>
                            </div>
                            <table class="input-table">
                                <tbody>

                                <tr :class="{error: myValue.error.disk}">
                                    <td>存储介质</td>
                                    <td>
                                        <radio-group v-model="systemSettings.disk">
                                            <radio v-for="(v,k) in TopContext.business.settings.disk" :key="k" :label="k">{{ v }}</radio>
                                        </radio-group>
                                        <span class="need">*</span>
                                        <div class="msg">默认：本地存储</div>
                                        <div class="e-msg">{{ myValue.error.disk }}</div>
                                    </td>
                                </tr>

                                </tbody>
                            </table>
                        </div>

                        <div class="block m-t-10">
                            <div class="run-title">
                                <div class="left">代理设置</div>
                                <div class="right"></div>
                            </div>
                            <table class="input-table">
                                <tbody>

                                <tr :class="{error: myValue.error.proxy_pass}">
                                    <td>http(s)代理设置</td>
                                    <td>
                                        <input type="text" class="form-text" v-model="systemSettings.proxy_pass">
                                        <span class="need"></span>
                                        <div class="msg">范例：http://127.0.0.1:10009</div>
                                        <div class="e-msg">{{ myValue.error.proxy_pass }}</div>
                                    </td>
                                </tr>

                                </tbody>
                            </table>
                        </div>

                        <div class="actions m-t-10">
                            <i-button
                                    type="primary"
                                    :loading="myValue.pending.getData || myValue.pending.submitEvent"
                                    @click="submitEvent"
                            >提交</i-button>
                        </div>

                    </TabPane>

                    <TabPane name="web_settings" label="web 端设置">

                        <table class="input-table">
                            <tbody>

                            <tr :class="{error: myValue.error.web_url}">
                                <td>web 端url</td>
                                <td>
                                    <input
                                            type="text"
                                            v-model="systemSettings.web_url"
                                            @input="myValue.error.web_url = ''"
                                            class="form-text"
                                            placeholder="web 端url"
                                    >
                                    <span class="need"></span>
                                    <div class="msg">例：https://www.test.com</div>
                                    <div class="e-msg">{{ myValue.error.web_url }}</div>
                                </td>
                            </tr>

                            <tr v-for="v in webRouteMappings">
                                <td>{{ v.name }}</td>
                                <td>
                                    <input
                                            type="text"
                                            v-model="v.url"
                                            @input="myValue.error.web_url = ''"
                                            class="form-text"
                                            :placeholder="v.name"
                                    >
                                    <span class="need"></span>
                                    <div class="msg">例：/video/{id}/show；{id} - 动态参数</div>
                                    <div class="e-msg"></div>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="2">
                                    <i-button
                                            type="primary"
                                            :loading="myValue.pending.getData || myValue.pending.submitEvent"
                                            @click="submitEvent"
                                    >提交</i-button>
                                </td>
                            </tr>

                            </tbody>
                        </table>

                    </TabPane>

                    <TabPane name="aliyun_settings" label="阿里云存储">
                        <table class="input-table">
                            <tbody>

                            <tr :class="{error: myValue.error.aliyun_key}">
                                <td>阿里云 key</td>
                                <td>
                                    <input
                                            type="text"
                                            v-model="systemSettings.aliyun_key"
                                            @input="myValue.error.aliyun_key = ''"
                                            class="form-text"
                                            placeholder="阿里云 key"
                                    >
                                    <span class="need"></span>
                                    <div class="msg"></div>
                                    <div class="e-msg">{{ myValue.error.aliyun_key }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.aliyun_secret}">
                                <td>阿里云 secret</td>
                                <td>
                                    <input
                                            type="text"
                                            v-model="systemSettings.aliyun_secret"
                                            @input="myValue.error.aliyun_secret = ''"
                                            class="form-text"
                                            placeholder="阿里云 secret"
                                    >
                                    <span class="need"></span>
                                    <div class="msg"></div>
                                    <div class="e-msg">{{ myValue.error.aliyun_secret }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.aliyun_endpoint}">
                                <td>阿里云 endpoint</td>
                                <td>
                                    <input
                                            type="text"
                                            v-model="systemSettings.aliyun_endpoint"
                                            @input="myValue.error.aliyun_endpoint = ''"
                                            class="form-text"
                                            placeholder="阿里云 endpoint"
                                    >
                                    <span class="need"></span>
                                    <div class="msg"></div>
                                    <div class="e-msg">{{ myValue.error.aliyun_endpoint }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.aliyun_bucket}">
                                <td>阿里云 bucket</td>
                                <td>
                                    <input
                                            type="text"
                                            v-model="systemSettings.aliyun_bucket"
                                            @input="myValue.error.aliyun_bucket = ''"
                                            class="form-text"
                                            placeholder="阿里云 bucket"
                                    >
                                    <span class="need"></span>
                                    <div class="msg"></div>
                                    <div class="e-msg">{{ myValue.error.aliyun_bucket }}</div>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="2">
                                    <i-button
                                            type="primary"
                                            :loading="myValue.pending.getData || myValue.pending.submitEvent"
                                            @click="submitEvent"
                                    >提交</i-button>
                                </td>
                            </tr>

                            </tbody>
                        </table>

                    </TabPane>


                </Tabs>

            </div>


            <div class="line actions">
                <button type="submit" v-show="false"></button>
            </div>
        </form>
    </my-base>

</template>

<script src="./js/index.js"></script>
<style scoped>
    /**
     * ****************
     * 表单样式控制
     * ****************
     */
    .input-table tbody tr td:nth-of-type(1) {
        width: 130px;
    }

    .my-form > .line {
        margin-bottom: 15px;
    }

    .my-form > .line:nth-last-of-type(1) {
        margin-bottom: 0;
    }
</style>
