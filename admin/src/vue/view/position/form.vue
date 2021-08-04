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
                        <tr :class="{error: myValue.error.value}">
                            <td>位置</td>
                            <td>
                                <input type="text" v-model="form.value" @input="myValue.error.value=''" class="form-text">
                                <span class="need">*</span>
                                <div class="msg"></div>
                                <div class="e-msg">{{ myValue.error.value }}</div>
                            </td>
                        </tr>
                        <tr :class="{error: myValue.error.name}">
                            <td>名称</td>
                            <td>
                                <input type="text" v-model="form.name" @input="myValue.error.name=''" class="form-text">
                                <span class="need">*</span>
                                <div class="msg"></div>
                                <div class="e-msg">{{ myValue.error.name }}</div>
                            </td>
                        </tr>
                        <tr :class="{error: myValue.error.platform}">
                            <td>所属平台</td>
                            <td>
                                <i-select v-model="form.platform" class="w-400" @on-change="myValue.error.platform = ''">
                                    <i-option v-for="(v,k) in TopContext.business.platform" :key="k" :value="k">{{ v }}</i-option>
                                </i-select>
                                <span class="need">*</span>
                                <div class="msg"></div>
                                <div class="e-msg">{{ myValue.error.platform }}</div>
                            </td>
                        </tr>
                        <tr :class="{error: myValue.error.description}">
                            <td>描述</td>
                            <td>
                                <textarea v-model="form.description" class="form-textarea" @input="myValue.error.description = ''"></textarea>
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

        <my-user-selector ref="user-selector" @on-change="userChangeEvent"></my-user-selector>
    </div>
</template>

<script src="./js/form.js"></script>

<style scoped>

</style>
