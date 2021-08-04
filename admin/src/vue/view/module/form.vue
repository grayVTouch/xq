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
                        <tr :class="{error: myValue.error.name}">
                            <td>名称</td>
                            <td>
                                <input
                                        type="text"
                                        v-model="form.name"
                                        @input="myValue.error.name=''"
                                        :class="{'form-text': true , disabled: mode === 'edit'}"
                                        :readonly="mode === 'edit'"
                                >
                                <span class="need">*</span>
                                <div class="msg run-red">
                                    <template v-if="mode === 'edit'">禁止修改</template>
                                    <template v-if="mode === 'add'">一旦添加则禁止修改名称！清谨慎操作</template>
                                </div>
                                <div class="e-msg">{{ myValue.error.name }}</div>
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
                        <tr :class="{error: myValue.error.is_enabled}">
                            <td>启用？</td>
                            <td>
                                <i-radio-group v-model="form.is_enabled"  @input="myValue.error.is_enabled = ''">
                                    <i-radio v-for="(v,k) in TopContext.business.bool.integer" :key="k" :label="parseInt(k)">{{ v }}</i-radio>
                                </i-radio-group>
                                <span class="need">*</span>
                                <div class="msg">默认：开启</div>
                                <div class="e-msg">{{ myValue.error.is_enabled }}</div>
                            </td>
                        </tr>
                        <tr :class="{error: myValue.error.is_default}">
                            <td>默认？</td>
                            <td>
                                <i-radio-group v-model="form.is_default"  @input="myValue.error.is_default = ''">
                                    <i-radio v-for="(v,k) in TopContext.business.bool.integer" :key="k" :label="parseInt(k)">{{ v }}</i-radio>
                                </i-radio-group>
                                <span class="need">*</span>
                                <div class="msg">默认：否</div>
                                <div class="e-msg">{{ myValue.error.is_default }}</div>
                            </td>
                        </tr>
                        <tr :class="{error: myValue.error.is_auth}">
                            <td>认证？</td>
                            <td>
                                <i-radio-group v-model="form.is_auth"  @input="myValue.error.is_auth = ''">
                                    <i-radio v-for="(v,k) in TopContext.business.bool.integer" :key="k" :label="parseInt(k)">{{ v }}</i-radio>
                                </i-radio-group>
                                <span class="need">*</span>
                                <div class="msg">默认：否</div>
                                <div class="e-msg">{{ myValue.error.auth }}</div>
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
    </div>
</template>

<script src="./js/form.js"></script>

<style scoped>

</style>
