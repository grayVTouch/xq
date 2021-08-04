<template>
    <div>
        <my-form-modal
                v-model="myValue.show"
                :title="title"
                :loading="myValue.pending.submitEvent || myValue.pending.findById"
                width="700"
                @on-ok="submitEvent"
                @on-cancel="closeFormModal"
        >
            <template slot="default">
                <form class="form" @submit.prevent="submitEvent" ref="form">
                    <table class="input-table">
                        <tbody>
                        <tr :class="{error: myValue.error.name}">
                            <td>名称</td>
                            <td>
                                <input type="text" v-model="form.name"  @input="myValue.error.name = ''" class="form-text">
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
                            <td>所属类型：</td>
                            <td>
                                <i-select v-model="form.type" class="w-400" @on-change="typeChangedEvent" :disabled="mode === 'add' || addMode === 'add_next'">
                                    <i-option v-for="(v,k) in TopContext.business.category.type" :key="k" :value="k">{{ v }}</i-option>
                                </i-select>
                                <span class="need">*</span>
                                <div class="msg"></div>
                                <div class="e-msg">{{ myValue.error.type }}</div>
                            </td>
                        </tr>

                        <tr :class="{error: myValue.error.module_id}">
                            <td>所属模块</td>
                            <td>
                                <my-select :data="modules" :disabled="mode === 'add' || addMode === 'add_next'" v-model="form.module_id" @change="moduleChangedEvent" :width="TopContext.style.inputItemW"></my-select>
                                <i-button type="primary" :loading="myValue.pending.getModules" @click="getModules">刷新</i-button>
                                <span class="need">*</span>
                                <div class="msg"></div>
                                <div class="e-msg">{{ myValue.error.module_id }}</div>
                            </td>
                        </tr>

                        <tr :class="{error: myValue.error.p_id}">
                            <td>上级分类</td>
                            <td>
                                <my-deep-select
                                        :data="categories"
                                        v-model="form.p_id"
                                        :has="true"
                                        :attr="myValue.attr"
                                        @change="myValue.error.p_id = ''"
                                        :width="TopContext.style.inputItemW"
                                        :disabled="mode === 'add' && addMode === 'add_next'"
                                ></my-deep-select>
                                <i-button type="primary" :loading="myValue.pending.getCategories" @click="getCategories(form.module_id)">刷新</i-button>
                                <span class="need">*</span>
                                <div class="msg">请务必选择模块、类型后操作</div>
                                <div class="e-msg">{{ myValue.error.p_id }}</div>
                            </td>
                        </tr>

                        <tr :class="{error: myValue.error.description}">
                            <td>描述</td>
                            <td>
                                <textarea v-model="form.description" class="form-textarea" @input="myValue.error.description = ''"></textarea>
                                <span class="msg"></span>
                                <span class="need"></span>
                                <span class="e-msg">{{ myValue.error.description }}</span>
                            </td>
                        </tr>

                        <tr :class="{error: myValue.error.enable}">
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
                                <input type="number" class="form-text"  @input="myValue.error.weight = ''" v-model="form.weight">
                                <span class="need"></span>
                                <div class="msg">请提供整数</div>
                                <div class="e-msg">{{ myValue.error.weight }}</div>
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
            <my-select class-name="w-400" :data="modules" v-model="form.module_id" @on-change="moduleChangedEvent"></my-select>
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
                <i-option v-for="(v,k) in TopContext.business.category.type" :key="k" :value="k">{{ v }}</i-option>
            </i-select>
            <span class="need run-red">*</span>

            <template slot="footer">
                <i-button type="primary" @click="handleStep('form')">确认</i-button>
            </template>
        </my-form-modal>
    </div>
</template>

<script src="./js/form.js"></script>

<style scoped>

</style>
