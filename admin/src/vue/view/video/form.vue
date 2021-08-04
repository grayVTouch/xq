<template>
    <my-form-drawer :title="title" v-model="myValue.show">

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
            <form @submit.prevent="submitEvent" class="form">
                <div class="menu">
                    <div class="menu-item" v-ripple :class="{cur: myValue.tab === 'base'}" @click="myValue.tab = 'base'">基本信息</div>
                    <div class="menu-item" v-ripple :class="{cur: myValue.tab === 'image'}" @click="myValue.tab = 'image'">视频信息</div>
                </div>
                <div class="menu-mapping-content">
                    <div class="" v-show="myValue.tab === 'base'">
                        <table class="input-table">
                            <tbody>

                            <tr :class="{error: myValue.error.name}">
                                <td>名称：</td>
                                <td>
                                    <input type="text" class="form-text" v-model="form.name" @input="myValue.error.name = ''">
                                    <span class="need"><template v-if="form.type === 'misc'">*</template></span>
                                    <div class="msg"></div>
                                    <div class="e-msg">{{ myValue.error.name }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.user_id}">
                                <td>所属用户：</td>
                                <td>
                                    <input type="text" readonly="readonly" :value="`${owner.username}【${owner.id}】`" class="form-text w-180 run-cursor-not-allow">
                                    如需重新搜索，请点击
                                    <i-button @click="showUserSelector">搜索</i-button>
                                    <span class="need">*</span>
                                    <div class="msg"></div>
                                    <div class="e-msg">{{ myValue.error.user_id }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.type}">
                                <td>类型：</td>
                                <td>
                                    <i-select v-model="form.type" class="w-400" :disabled="true">
                                        <i-option v-for="(v,k) in TopContext.business.video.type" :key="k" :value="k">{{ v }}</i-option>
                                    </i-select>
                                    <span class="need">*</span>
                                    <div class="msg">默认：杂类</div>
                                    <div class="e-msg">{{ myValue.error.type }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.module_id}">
                                <td>所属模块：</td>
                                <td>
                                    <my-select :data="modules" :disabled="mode === 'add'" v-model="form.module_id" @change="moduleChangedEvent" :width="TopContext.style.inputItemW"></my-select>
                                    <i-button type="primary" :loading="myValue.pending.getModules" @click="getModules">刷新</i-button>
                                    <span class="need">*</span>
                                    <div class="msg"></div>
                                    <div class="e-msg">{{ myValue.error.module_id }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.category_id}" v-show="form.type === 'misc'">
                                <td>所属分类：</td>
                                <td>
                                    <my-deep-select :data="categories" v-model="form.category_id" @change="myValue.error.category_id = ''" :has="false" :width="TopContext.style.inputItemW"></my-deep-select>
                                    <i-button type="primary" :loading="myValue.pending.getCategories" @click="getCategories">刷新</i-button>
                                    <span class="need">*</span>
                                    <div class="msg">请务必在选择模块后操作</div>
                                    <div class="e-msg">{{ myValue.error.category_id }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.video_project_id}" v-show="form.type === 'pro'">
                                <td>视频专题：</td>
                                <td>
                                    <input type="text" readonly="readonly" :value="`${videoProject.name}【${videoProject.id}】`" class="form-text w-180 run-cursor-not-allow">
                                    如需重新搜索，请点击
                                    <i-button @click="showVideoProjectSelector">搜索</i-button>
                                    <span class="need">*</span>
                                    <div class="msg">请务必在选择模块后操作；输入关联主体id、名称可查询</div>
                                    <div class="e-msg">{{ myValue.error.video_project_id }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.index}" v-show="form.type === 'pro'">
                                <td>视频索引</td>
                                <td>
                                    <input type="number" v-model="form.index" @input="myValue.error.index = ''" class="form-text">
                                    <span class="need">*</span>
                                    <div class="msg">仅允许整数</div>
                                    <div class="e-msg">{{ myValue.error.index }}</div>
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

                            <tr :class="{error: myValue.error.tags}" v-show="form.type === 'misc'">
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

                            <tr :class="{error: myValue.error.weight}">
                                <td>权重</td>
                                <td>
                                    <input type="number" v-model="form.weight" @input="myValue.error.weight = ''" class="form-text">
                                    <span class="need"></span>
                                    <div class="msg">仅允许整数</div>
                                    <div class="e-msg">{{ myValue.error.thumb }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.view_count}">
                                <td>浏览次数</td>
                                <td>
                                    <input type="number" v-model="form.view_count" @input="myValue.error.view_count = ''" class="form-text">
                                    <span class="need"></span>
                                    <div class="msg">仅允许整数</div>
                                    <div class="e-msg">{{ myValue.error.view_count }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.play_count}">
                                <td>获赞次数</td>
                                <td>
                                    <input type="number" v-model="form.play_count" @input="myValue.error.play_count = ''" class="form-text">
                                    <span class="need"></span>
                                    <div class="msg">仅允许整数</div>
                                    <div class="e-msg">{{ myValue.error.play_count }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.praise_count}">
                                <td>获赞次数</td>
                                <td>
                                    <input type="number" v-model="form.praise_count" @input="myValue.error.praise_count = ''" class="form-text">
                                    <span class="need"></span>
                                    <div class="msg">仅允许整数</div>
                                    <div class="e-msg">{{ myValue.error.praise_count }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.against_count}">
                                <td>反对次数</td>
                                <td>
                                    <input type="number" v-model="form.against_count" @input="myValue.error.against_count = ''" class="form-text">
                                    <span class="need"></span>
                                    <div class="msg">仅允许整数</div>
                                    <div class="e-msg">{{ myValue.error.against_count }}</div>
                                </td>
                            </tr>

                            <tr :class="{error: myValue.error.status}">
                                <td>状态</td>
                                <td>
                                    <i-radio-group v-model="form.status">
                                        <i-radio v-for="(v,k) in TopContext.business.video.status" :key="k" :label="parseInt(k)">{{ v }}</i-radio>
                                    </i-radio-group>
                                    <span class="need">*</span>
                                    <div class="msg">默认：审核中</div>
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

                            <tr :class="{error: myValue.error.description}">
                                <td>描述</td>
                                <td>
                                    <textarea v-model="form.description" class="form-textarea"></textarea>
                                    <span class="need"></span>
                                    <div class="msg"></div>
                                    <div class="e-msg">{{ myValue.error.description }}</div>
                                </td>
                            </tr>

<!--                            <tr :class="{error: myValue.error.created_at}">-->
<!--                                <td>创建时间</td>-->
<!--                                <td>-->
<!--                                    <i-date-picker type="datetime" v-model="createTime" format="yyyy-MM-dd HH:mm:ss" @on-change="setDatetimeEvent" class="iview-form-input"></i-date-picker>-->
<!--                                    <span class="need"></span>-->
<!--                                    <div class="msg">如不提供，则默认使用当前时间</div>-->
<!--                                    <div class="e-msg">{{ myValue.error.created_at }}</div>-->
<!--                                </td>-->
<!--                            </tr>-->

                            <tr>
                                <td colspan="2">
                                    <button class="hide" type="submit"><my-icon icon="tijiao" />提交</button>
                                    <i-button v-ripple type="primary" :loading="myValue.pending.submitEvent || myValue.pending.findById" @click="submitEvent"><my-icon icon="tijiao" />提交</i-button>
                                </td>
                            </tr>

                            </tbody>
                        </table>
                    </div>
                    <div class="" v-show="myValue.tab === 'image'">
                        <div class="video-info">
                            <div class="line upload-video">
                                <div class="run-title">
                                    <div class="left">上传视频</div>
                                    <div class="right"></div>
                                </div>
                                <div>
                                    <table class="input-table">
                                        <tbody>
                                        <tr :class="{error: myValue.error.src}">
                                            <td>
                                                <div ref="video">
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
                                                <div class="msg">
                                                    支持的视频格式有：mov,mp4,mkv,rm,rmvb,avi,flv
                                                    <template v-if="mode === 'edit'"><br><b>当视频处理状态不是 处理完成 或 处理失败 的时候禁止更改</b></template>
                                                </div>
                                                <div class="e-msg">{{ myValue.error.src }}</div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="line upload-subtitle">
                                <div class="run-title">
                                    <div class="left">上传字幕</div>
                                    <div class="right"></div>
                                </div>
                                <div>
                                    <table class="input-table">
                                        <tbody>
                                        <tr>
                                            <td>合并字幕？</td>
                                            <td>
                                                <i-radio-group v-model="form.merge_video_subtitle">
                                                    <i-radio v-for="(v,k) in TopContext.business.bool.integer" :key="k" :label="parseInt(k)">{{ v }}</i-radio>
                                                </i-radio-group>
                                                <span class="need">*</span>
                                                <div class="msg">默认：否；
                                                    <br>
                                                    当选择合并字幕的时候服务端会将字幕列表中的首个字幕合并到视频并删除其他字幕，合并完成也会删除合并字幕仅保留合并后的视频文件
                                                    <br>
                                                    <br>
                                                    当字幕文件编码非utf8格式的时候，服务端会尝试转码后合成，但仍有概率失败！强烈建议手动将字幕文件转成utf8格式编码
                                                </div>
                                                <div class="e-msg">{{ form.merge_video_subtitle }}</div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>字幕</td>
                                            <td>
                                                <div class="subtitles">
                                                    <div class="item" v-for="(v,k) in uVideoSubtitles">
                                                        <div class="name"><input type="text" placeholder="字幕名称" class="form-text" v-model="v.name" @input="v.error = ''"></div>
                                                        <div class="src"><input type="file" class="form-file" @change="videoSubtitleChangeEvent($event , v)"></div>

                                                        <div class="actions">
                                                            <my-table-button @click="uVideoSubtitles.splice(k,1)">删除</my-table-button>
                                                        </div>
                                                        <div class="loading" v-if="v.uploading"><my-loading v-if="v.uploading"></my-loading></div>
                                                        <div class="flag" v-if="v.uploaded"><my-icon icon="604xinxi_chenggong" class="run-green"></my-icon></div>
                                                        <div class="e-msg run-red">{{ v.error }}</div>
                                                    </div>
                                                </div>

                                                <div class="action">
                                                    <i-button v-ripple @click="addVideoSubtitleEvent">新增</i-button>
<!--                                                    <i-button v-ripple :loading="myValue.pending.uploadVideoSubtitle" :disabled="canUploadVideoSubtitle" @click="uploadVideoSubtitleEvent">上传</i-button>-->
                                                </div>

<!--                                                <div class="msg">在提交修改之前，请务必点击上传文件先将字幕文件保存到服务器！</div>-->
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="line files" v-if="mode === 'edit'">
                                <div class="line">
                                    <div class="run-title">
                                        <div class="left">视频列表</div>
                                        <div class="right">
                                            <my-table-button type="error" :loading="myValue.pending['destroyVideos']" @click="destroyVideosEvent">删除选中项 <template v-if="videoSelection.length > 0">（{{ videoSelection.length }}）</template></my-table-button>
                                        </div>
                                    </div>
                                    <div>
                                        <i-table
                                                class="w-r-100"
                                                border
                                                :columns="videos.field"
                                                :data="videos.data"
                                                @on-selection-change="videoSelectionChangeEvent"
                                        >
                                            <template v-slot:src="{row,index}">
                                                <video muted="muted" controls :src="row.src ? row.src : TopContext.res.notFound" :height="TopContext.table.videoH"></video>
                                            </template>
                                            <template v-slot:action="{row,index}">
                                                <my-table-button :loading="myValue.pending['delete_' + row.id]" @click="destroyVideoEvent(index , row)">删除</my-table-button>
                                                <my-table-button @click="openWindow(row.src , '_blank')">预览</my-table-button>
                                            </template>
                                        </i-table>
                                    </div>
                                </div>

                                <div class="line">
                                    <my-table-button type="error" :loading="myValue.pending['destroyVideos']" @click="destroyVideosEvent">删除选中项 <template v-if="videoSelection.length > 0">（{{ videoSelection.length }}）</template></my-table-button>
                                </div>
                            </div>

                            <div class="line files" v-if="mode === 'edit'">
                                <div class="line">
                                    <div class="run-title">
                                        <div class="left">字幕列表</div>
                                        <div class="right">
                                            <my-table-button type="error" :loading="myValue.pending['destroyVideoSubtitles']" @click="destroyVideoSubtitlesEvent">删除选中项 <template v-if="videoSubtitleSelection.length > 0">（{{ videoSubtitleSelection.length }}）</template></my-table-button>
                                        </div>
                                    </div>
                                    <div>
                                        <i-table
                                                class="w-r-100"
                                                border :columns="videoSubtitles.field"
                                                :data="videoSubtitles.data"
                                                @on-selection-change="videoSubtitleSelectionChangeEvent"
                                        >
                                            <template v-slot:src="{row,index}">{{ row.src }}</template>
                                            <template v-slot:action="{row,index}">
                                                <my-table-button :loading="myValue.pending['delete_' + row.id]" @click="destroyVideoSubtitleEvent(index , row)">删除</my-table-button>
                                            </template>
                                        </i-table>
                                    </div>
                                </div>

                                <div class="line">
                                    <my-table-button type="error" :loading="myValue.pending['destroyVideoSubtitles']" @click="destroyVideoSubtitlesEvent">删除选中项 <template v-if="videoSubtitleSelection.length > 0">（{{ videoSubtitleSelection.length }}）</template></my-table-button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </form>

            <!-- 请选择用户 -->
            <my-user-selector ref="user-selector" @on-change="userChangeEvent"></my-user-selector>
            <my-video-project-selector ref="video-project-selector" :moduleId="form.module_id" @on-change="videoProjectChangeEvent"></my-video-project-selector>

            <!-- 第一步：模块选择器 -->
            <my-form-modal
                    v-model="myValue.showModuleSelector"
                    title="请选择"
                    width="auto"
                    :mask-closable="true"
                    :closable="true"
            >
                <span class="f-12">所属模块：</span>
                <my-select :width="TopContext.style.inputItemW" :data="modules" v-model="form.module_id" @on-change="moduleChangedEvent"></my-select>
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
                    <i-option v-for="(v,k) in TopContext.business.video.type" :key="k" :value="k">{{ v }}</i-option>
                </i-select>
                <span class="need run-red">*</span>

                <template slot="footer">
                    <i-button type="primary" @click="handleStep('form')">确认</i-button>
                </template>
            </my-form-modal>

        </template>
    </my-form-drawer>
</template>

<script src="./js/form.js"></script>
<style scoped src="./css/form.css"></style>
