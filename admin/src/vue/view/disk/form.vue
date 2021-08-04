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
                        <tr :class="{error: myValue.error.path}">
                            <td>目录真实路径</td>
                            <td>
                                <input type="text" v-model="form.path" @input="myValue.error.path = ''" class="form-text">
                                <span class="need">*</span>
                                <div class="m-t-5"><i-button v-ripple @click="showResourceSelector">资源管理器</i-button></div>
                                <div class="msg">例如：windows: d:/test ；linux: /myself/resource</div>
                                <div class="e-msg">{{ myValue.error.path }}</div>
                            </td>
                        </tr>

                        <tr :class="{error: myValue.error.prefix}">
                            <td>路径前缀</td>
                            <td>
                                <input type="text" v-model="form.prefix" @input="myValue.error.prefix = ''" class="form-text">
                                <span class="need">*</span>
                                <div class="msg">例如：upload；不允许重复</div>
                                <div class="e-msg">{{ myValue.error.prefix }}</div>
                            </td>
                        </tr>

                        <tr :class="{error: myValue.error.os}">
                            <td>操作系统</td>
                            <td>
                                <i-radio-group v-model="form.os"  @input="myValue.error.os = ''">
                                    <i-radio v-for="(v,k) in TopContext.business.disk.os" :key="k" :label="k">{{ v }}</i-radio>
                                </i-radio-group>
                                <span class="need">*</span>
                                <div class="msg">默认：windows</div>
                                <div class="e-msg">{{ myValue.error.os }}</div>
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

                        <tr :class="{error: myValue.error.is_linked}" v-if="myValue.mode === 'edit'">
                            <td>已创建链接？</td>
                            <td>
                                <i-radio-group v-model="form.is_linked"  @input="myValue.error.is_linked = ''">
                                    <i-radio v-for="(v,k) in TopContext.business.bool.integer" :key="k" :label="parseInt(k)">{{ v }}</i-radio>
                                </i-radio-group>
                                <span class="need">*</span>
                                <div class="msg"></div>
                                <div class="e-msg">{{ myValue.error.is_linked }}</div>
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

        <my-resource-selector ref="resource-selector" @on-change="resourceChangedEvent"></my-resource-selector>
    </div>
</template>

<script src="./js/form.js"></script>

<style scoped>

</style>
