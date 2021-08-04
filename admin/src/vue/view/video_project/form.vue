<template>
    <my-form-drawer v-model="myValue.show">
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
                            <my-select :width="TopContext.style.inputItemW" :disabled="mode === 'add'" :data="modules" v-model="form.module_id" @change="moduleChanged"></my-select>
                            <i-button type="primary" :loading="myValue.pending.getModules" @click="getModules">刷新</i-button>
                            <span class="need">*</span>
                            <div class="msg"></div>
                            <div class="e-msg">{{ myValue.error.module_id }}</div>
                        </td>
                    </tr>

                    <tr :class="{error: myValue.error.category_id}">
                        <td>所属分类：</td>
                        <td>
                            <my-deep-select :width="TopContext.style.inputItemW" :data="categories" v-model="form.category_id" @change="myValue.error.category_id = ''" :has="false"></my-deep-select>
                            <i-button type="primary" :loading="myValue.pending.getCategories" @click="refreshCategories">刷新</i-button>
                            <span class="need">*</span>
                            <div class="msg">请务必在选择模块后操作</div>
                            <div class="e-msg">{{ myValue.error.category_id }}</div>
                        </td>
                    </tr>


                    <tr :class="{error: myValue.error.video_series_id}">
                        <td>视频系列：</td>
                        <td>
                            <input type="text" readonly="readonly" :value="`${videoSeries.name}【${videoSeries.id}】`" class="form-text w-180 run-cursor-not-allow">
                            如需重新搜索，请点击
                            <i-button @click="showVideoSeriesSelector">搜索</i-button>
                            <span class="need"></span>
                            <div class="msg">请务必在选择模块后操作</div>
                            <div class="e-msg">{{ myValue.error.video_series_id }}</div>
                        </td>
                    </tr>

                    <tr :class="{error: myValue.error.video_company_id}">
                        <td>视频制作公司：</td>
                        <td>
                            <input type="text" readonly="readonly" :value="`${videoCompany.name}【${videoCompany.id}】`" class="form-text w-180 run-cursor-not-allow">
                            如需重新搜索，请点击
                            <i-button @click="showVideoCompanySelector">搜索</i-button>
                            <span class="need"></span>
                            <div class="msg">请务必在选择模块后操作</div>
                            <div class="e-msg">{{ myValue.error.video_company_id }}</div>
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

                    <tr :class="{error: myValue.error.release_date}">
                        <td>发布日期</td>
                        <td>
                            <i-date-picker
                                    type="date"
                                    v-model="releaseDate"
                                    format="yyyy-MM-dd"
                                    @on-change="setReleaseDateEvent"
                                    class="iview-form-input"
                                    :transfer="true"
                            ></i-date-picker>
                            <span class="need"></span>
                            <div class="msg"></div>
                            <div class="e-msg">{{ myValue.error.release_date }}</div>
                        </td>
                    </tr>

                    <tr :class="{error: myValue.error.end_date}">
                        <td>完结日期</td>
                        <td>
                            <i-date-picker
                                    type="date"
                                    v-model="endDate"
                                    format="yyyy-MM-dd"
                                    @on-change="setEndDateEvent"
                                    class="iview-form-input"
                                    :transfer="true"
                            ></i-date-picker>
                            <span class="need"></span>
                            <div class="msg"></div>
                            <div class="e-msg">{{ myValue.error.end_date }}</div>
                        </td>
                    </tr>

<!--                    <tr :class="{error: myValue.error.count}">-->
<!--                        <td>视频数</td>-->
<!--                        <td>-->
<!--                            <input type="number" v-model="form.count" @input="myValue.error.count = ''" class="form-text">-->
<!--                            <span class="need"></span>-->
<!--                            <div class="msg">仅允许整数</div>-->
<!--                            <div class="e-msg"></div>-->
<!--                        </td>-->
<!--                    </tr>-->

                    <tr :class="{error: myValue.error.min_index}">
                        <td>视频开始索引</td>
                        <td>
                            <input type="number" v-model="form.min_index" @input="myValue.error.min_index = ''" class="form-text">
                            <span class="need"></span>
                            <div class="msg">仅允许整数</div>
                            <div class="e-msg"></div>
                        </td>
                    </tr>

                    <tr :class="{error: myValue.error.max_index}">
                        <td>视频结束索引</td>
                        <td>
                            <input type="number" v-model="form.max_index" @input="myValue.error.max_index = ''" class="form-text">
                            <span class="need"></span>
                            <div class="msg">仅允许整数</div>
                            <div class="e-msg"></div>
                        </td>
                    </tr>

<!--                    <tr :class="{error: myValue.error.play_count}">-->
<!--                        <td>播放数</td>-->
<!--                        <td>-->
<!--                            <input type="number" v-model="form.play_count" @input="myValue.error.play_count = ''" class="form-text">-->
<!--                            <span class="msg">仅允许整数</span>-->
<!--                            <span class="need"></span>-->
<!--                            <span class="e-msg"></span>-->
<!--                        </td>-->
<!--                    </tr>-->

                    <tr :class="{error: myValue.error.score}">
                        <td>评分</td>
                        <td>
<!--                            <input type="number" step="0.01" v-model="form.score" @input="myValue.error.score = ''" class="form-text">-->
                            <Rate allow-half v-model="form.score" @change="myValue.error.score = ''" />
<!--                            <span class="msg">精度：0.01 {{ form.score }}</span>-->
                            <span class="need"></span>
                            <div class="msg"></div>
                            <div class="e-msg"></div>
                        </td>
                    </tr>

                    <tr :class="{error: myValue.error.end_status}">
                        <td>完结状态</td>
                        <td>
                            <i-radio-group v-model="form.end_status">
                                <i-radio v-for="(v,k) in TopContext.business.videoProject.endStatus" :key="k" :label="k">{{ v }}</i-radio>
                            </i-radio-group>
                            <span class="need">*</span>
                            <div class="msg">默认：已完结</div>
                            <div class="e-msg">{{ myValue.error.end_status }}</div>
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
                                <i-radio v-for="(v,k) in TopContext.business.videoProject.status" :key="k" :label="parseInt(k)">{{ v }}</i-radio>
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

                    <tr>
                        <td colspan="2">
                            <button type="submit" v-show="false"></button>
                            <i-button v-ripple type="primary" :loading="myValue.pending.submitEvent || myValue.pending.findById" @click="submitEvent"><my-icon icon="tijiao" />提交</i-button>
                            <i-button v-ripple type="error" @click="closeFormModal"><my-icon icon="guanbi" />关闭</i-button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
        </template>

        <my-user-selector ref="user-selector" @on-change="userChangeEvent"></my-user-selector>
        <my-video-series-selector ref="video-series-selector" :moduleId="form.module_id" @on-change="videoSeriesChangeEvent"></my-video-series-selector>
        <my-video-company-selector ref="video-company-selector" :moduleId="form.module_id" @on-change="videoCompanyChangeEvent"></my-video-company-selector>

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
    </my-form-drawer>
</template>

<script src="./js/form.js"></script>

<style scoped src="./css/form.css"></style>
