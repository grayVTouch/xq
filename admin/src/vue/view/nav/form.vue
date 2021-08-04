<template>
    <div>
        <my-form-modal
                v-model="myValue.show"
                :title="title"
                width="700"
                :loading="myValue.pending.submitEvent || myValue.pending.findById"
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

                        <tr :class="{error: myValue.error.type}">
                            <td>类型：</td>
                            <td>
                                <i-select v-model="form.type" :disabled="mode === 'add'" class="w-400" @on-change="typeChangeEvent">
                                    <i-option v-for="(v,k) in TopContext.business.nav.type" :key="k" :value="k">{{ v }}</i-option>
                                </i-select>
                                <span class="msg"></span>
                                <span class="need">*</span>
                                <span class="e-msg">{{ myValue.error.type }}</span>
                            </td>
                        </tr>

                        <tr :class="{error: myValue.error.module_id}">
                            <td>所属模块</td>
                            <td>
                                <my-select :data="modules" :disabled="mode === 'add'" v-model="form.module_id" @change="moduleChangedEvent" :width="TopContext.style.inputItemW"></my-select>
                                <i-button type="primary" :loading="myValue.pending.getModules" @click="getModules">刷新</i-button>
                                <span class="need">*</span>
                                <div class="msg"></div>
                                <div class="e-msg">{{ myValue.error.module_id }}</div>
                            </td>
                        </tr>

                        <tr :class="{error: myValue.error.p_id}">
                            <td>上级导航菜单</td>
                            <td>
                                <my-deep-select
                                        :data="navs"
                                        v-model="form.p_id"
                                        :has="true"
                                        :attr="myValue.attr"
                                        @change="myValue.error.p_id = ''"
                                        :width="TopContext.style.inputItemW"
                                        :disabled="mode === 'add' && addMode === 'add_next'"
                                ></my-deep-select>
                                <my-loading v-if="myValue.pending.getNavs"></my-loading>
                                <span class="need">*</span>
                                <div class="msg">请务必选择类型、模块后操作</div>
                                <div class="e-msg">{{ myValue.error.p_id }}</div>
                            </td>
                        </tr>

                        <tr :class="{error: myValue.error.value}">
                            <td>分类值</td>
                            <td>
                                <input
                                        type="text"
                                        class="form-text"
                                        readonly
                                        :value="`${category.name}【${category.id}】`"
                                >
                                <i-button @click="openCategorySelector">选择分类</i-button>
                                <span class="need">*</span>
                                <div class="msg">请务必先选择 模块、类型 后在操作！</div>
                                <div class="e-msg">{{ myValue.error.value }}</div>
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

<!--                        <tr :class="{error: myValue.error.platform}">-->
<!--                            <td>所属平台</td>-->
<!--                            <td>-->
<!--                                <i-select v-model="form.platform" @on-change="myValue.error.platform = ''" class="w-400">-->
<!--                                    <i-option v-for="(v,k) in TopContext.business.platform" :key="k" :value="k">{{ v }}</i-option>-->
<!--                                </i-select>-->
<!--                                <span class="need">*</span>-->
<!--                                <div class="msg"></div>-->
<!--                                <div class="e-msg">{{ myValue.error.platform }}</div>-->
<!--                            </td>-->
<!--                        </tr>-->

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
                <i-button type="primary" @click="nextStepAtType">确认</i-button>
            </template>
        </my-form-modal>

        <!-- 第二步：选择类型 -->
        <my-form-modal
                v-model="myValue.showTypeSelector"
                title="请选择"
                width="auto"
                :mask-closable="true"
                :closable="true"
        >
            <span class="f-12">选择类型：</span>
            <i-select v-model="form.type" class="w-400">
                <i-option v-for="(v,k) in TopContext.business.nav.type" :key="k" :value="k">{{ v }}</i-option>
            </i-select>
            <span class="need run-red">*</span>

            <template slot="footer">
                <i-button type="primary" @click="nextStepAtForm">确认</i-button>
            </template>
        </my-form-modal>

        <!-- 分类选择器 -->
        <my-category-selector
            ref="category-selector"
            :module-id="form.module_id"
            :type="form.type"
            @on-change="categoryChangedEvent"
        ></my-category-selector>

    </div>
</template>

<script src="./js/form.js"></script>

<style scoped>

</style>
