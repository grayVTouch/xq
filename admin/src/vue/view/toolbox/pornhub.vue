<template>
    <i-tab-pane name="pornhub" label="Pornhub 网站视频下载辅助工具">

        <div class="my-tab-content">
            <div class="left" id="anchor-tab-content">
                <form @submit.prevent="downloadEvent">
                    <table class="input-table">
                        <tbody>
                        <tr id="video_source" :class="{error: myValue.error.src}">
                            <td>视频源</td>
                            <td>
                                <input type="text" v-model="form.src" class="form-text" @input="myValue.error.src = ''">
                                <i-button type="info" v-if="recentSrc" @click="form.src = recentSrc">使用最近一次提供视频源</i-button>
                                <i-button type="info" :loading="myValue.pending.parseEvent" @click="parseEvent">解析</i-button>
                                <span class="need">*</span>
                                <div class="msg">
<pre>
获取方式1：浏览器F12打开控制台，切换到 Network 面板，过滤 m3u8
获取方式2：通过 fiddler 等代理工具捕获；这边提供 fiddler 获取方式：
1. 启动 fiddler ，设置全局代理
2. 安装 https 证书
3. filters 选项卡 -> hosts -> show only the following hosts，增加以下域名
    - e1v-h.phncdn.com
    - cn.pornhub.com
    - d1v-h.phncdn.com
    - ... 其他待新增
4. filters 选项卡 -> request headers -> show only if url contains，输入以下内容
    - m3u8
5. 打开 https://www.pornhub.com，找到想要下载的视频，切换到给定的画质
6. ctrl + x，清空 fiddlers 的请求列表，刷新 pornhub 页面
7. 找到 fiddler 请求列表中首个 m3u8 请求地址，ctrl + u 复制
9. 在上述输入框中粘贴地址，继续下述流程
</pre>
                                </div>
                                <div class="e-msg">{{ myValue.error.src }}</div>
                            </td>
                        </tr>
                        <tr id="definition" :class="{error: myValue.error.definition}">
                            <td>清晰度</td>
                            <td >
                                <i-select v-model="form.definition" :disabled="definitions.length === 0" class="w-400" @change="myValue.error.definition = ''">
                                    <i-option v-for="v in definitions" :key="v" :value="v">{{ v }}</i-option>
                                </i-select>
                                <span class="need" v-if="definitions.length > 0">*</span>
                                <div class="msg"><span class="run-red" v-if="definitions.length > 0">存在多个清晰度！</span>请务必在输入视频源后点击解析按钮，根据视频源的不同，或许可以选择视频的画质！</div>
                                <div class="e-msg">{{ myValue.error.definition }}</div>
                            </td>
                        </tr>
                        <tr id="proxy" :class="{error: myValue.error.proxy_pass}">
                            <td>http(s)代理</td>
                            <td>
                                <input type="text" v-model="form.proxy_pass" class="form-text" @input="myValue.error.proxy_pass = ''">
                                <i-button type="info" v-if="recentProxyPass" @click="form.proxy_pass = recentProxyPass">使用最近一次设置代理</i-button>
                                <i-button type="info" @click="form.proxy_pass = state().settings.proxy_pass">使用系统设置默认代理</i-button>
                                <span class="need"></span>
                                <div class="msg">如不想用代理，则留空;范例：http://127.0.0.1:10009</div>
                                <div class="e-msg">{{ myValue.error.proxy_pass }}</div>
                            </td>
                        </tr>
                        <tr id="save_dir" :class="{error: myValue.error.save_dir}">
                            <td>保存目录</td>
                            <td>
                                <input type="text" v-model="form.save_dir" class="form-text" @input="myValue.error.save_dir = ''">
                                <i-button type="info" v-if="recentSaveDir" @click="form.save_dir = recentSaveDir">使用最近一次保存目录</i-button>
                                <i-button type="info" @click="$refs['my-resource-selector'].show()">资源管理器</i-button>
                                <span class="need">*</span>
                                <div class="msg">
                                    - 处理过程中，会在该目录下自动生成以下两个临时目录<br>
                                    &nbsp;&nbsp;&nbsp;-temp_fsrtsf<br>
                                    &nbsp;&nbsp;&nbsp;-chunk_fsrtsf<br>
                                    - 处理完后会自动删除
                                </div>
                                <div class="e-msg">{{ myValue.error.save_dir }}</div>
                            </td>
                        </tr>
                        <tr id="filename" :class="{error: myValue.error.filename}">
                            <td>文件名</td>
                            <td>
                                <input type="text" v-model="form.filename" class="form-text" @input="myValue.error.filename = ''">
                                <span class="need"></span>
                                <div class="msg">如果提供了文件名称则会以此名称保存，否则会自动生成文件名称。</div>
                                <div class="e-msg">{{ myValue.error.filename }}</div>
                            </td>
                        </tr>
                        <tr id="download">
                            <td colspan="2">
                                <i-button
                                        @click="downloadEvent"
                                        type="primary"
                                        :loading="myValue.pending.downloadEvent"
                                >提交</i-button>
                                <div class="msg">
                                    <b class="run-red">特别注意：如果下载成功，在保存目录中最终生成的文件名形如 xxx-merged.mp4 </b>请注意查看
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </form>

            </div>
            <div class="right my-anchor">
                <i-anchor show-ink container="#anchor-tab-content">
                    <i-anchor-link href="#video_source" title="第一步：输入视频源，点击解析按钮" />
                    <i-anchor-link href="#definition" title="第二步：如果存在多画质，则选择画质；否则跳过" />
                    <i-anchor-link href="#proxy" title="第三步：可选输入http(s)代理" />
                    <i-anchor-link href="#save_dir" title="第四步：选择保存目录" />
                    <i-anchor-link href="#filename" title="第五步：可选输入文件名称" />
                    <i-anchor-link href="#download" title="第六步：点击下载开始下载" />
                </i-anchor>
            </div>
        </div>

        <my-resource-selector
            ref="my-resource-selector"
            @on-change="resourceChangedEvent"
        ></my-resource-selector>
    </i-tab-pane>
</template>

<script>
    export default {
        name: "pornhub" ,

        computed: {
            recentSaveDir () {
                const recentSaveDir = G.storage.local.get(this.myValue.localStorageKeys.recentSaveDir);
                return recentSaveDir ? recentSaveDir : '';
            } ,

            recentProxyPass () {
                const recentProxyPass = G.storage.local.get(this.myValue.localStorageKeys.recentProxyPass);
                return recentProxyPass ? recentProxyPass : '';
            } ,

            recentSrc () {
                const recentSrc = G.storage.local.get(this.myValue.localStorageKeys.recentSrc);
                return recentSrc ? recentSrc : '';
            } ,
        } ,

        data () {
            return {
                myValue: {
                    pending: {} ,
                    error: {} ,
                    localStorageKeys: {
                        recentSaveDir: 'toolbox:pornhub:save_dir' ,
                        recentProxyPass: 'toolbox:pornhub:proxy_pass' ,
                        recentSrc: 'toolbox:pornhub:src' ,
                    } ,
                } ,
                form: {
                    save_dir: '' ,
                    src: '' ,
                    url: '' ,
                    definition: '' ,
                    proxy_pass: '' ,
                } ,
                // 画质
                definitions: [] ,
            };
        } ,

        methods: {
            resourceChangedEvent (dir) {
                this.form.save_dir = dir;
            } ,

            parseEvent () {
                if (this.pending('parseEvent')) {
                    this.errorHandle('请求中 ... 请耐心等待');
                    return ;
                }
                if (G.isEmptyString(this.form.src)) {
                    this.errorHandle('视频源不能为空');
                    return ;
                }
                this.pending('parseEvent' , true);
                Api.pornhub
                    .parse(null , {
                        src: this.form.src ,
                        proxy_pass: this.form.proxy_pass ,
                    })
                    .then((res) => {
                        if (res.code !== TopContext.code.Success) {
                            this.errorHandle(res.message);
                            return ;
                        }
                        this.definitions = res.data;
                        this.successHandle('解析成功！' + (this.definitions.length > 0 ? '存在多画质！' : '无多画质'));
                    })
                    .finally(() => {
                        this.pending('parseEvent' , false);
                    });
            } ,

            filter (form) {
                const error = {};
                if (G.isEmptyString(form.src)) {
                    error.src = '请填写视频源';
                }
                if (this.definitions.length > 0 && G.isEmptyString(form.definition)) {
                    error.definition = '请选择画质';
                }
                if (G.isEmptyString(form.save_dir)) {
                    error.save_dir = '请填写保存目录';
                }
                return {
                    status: G.isEmptyObject(error) ,
                    error ,
                };
            } ,

            downloadEvent () {
                if (this.pending('downloadEvent')) {
                    this.errorHandle('请求中...请耐心等待');
                    return ;
                }
                const form = G.copy(this.form);
                const filterRes = this.filter(form);
                if (!filterRes.status) {
                    this.error(filterRes.error , true);
                    this.errorHandle(G.getObjectFirstKeyMappingValue(filterRes.error));
                    return ;
                }
                if (!G.isEmptyString(form.save_dir)) {
                    G.storage.local.set(this.myValue.localStorageKeys.recentSaveDir , form.save_dir);
                }
                if (!G.isEmptyString(form.proxy_pass)) {
                    G.storage.local.set(this.myValue.localStorageKeys.recentProxyPass , form.proxy_pass);
                }
                if (!G.isEmptyString(form.src)) {
                    G.storage.local.set(this.myValue.localStorageKeys.recentSrc , form.src);
                }
                this.pending('downloadEvent' , true);
                Api.pornhub
                    .download(null , form)
                    .then((res) => {
                        if (res.code !== TopContext.code.Success) {
                            this.errorHandle(res.message);
                            return ;
                        }
                        // 重置内容
                        this.form.src = '';
                        this.form.definition = '';
                        this.form.filename = '';
                        this.definitions = [];
                        this.modal('success' , '下载任务添加成功！请到保存目录下查看');
                    })
                    .finally(() => {
                        this.pending('downloadEvent' , false);
                    });
            } ,
        } ,
    }
</script>

<style scoped>
    .my-tab-content {
        padding: 20px;
        display: flex;
        justify-content: flex-start;
        align-items: stretch;
    }

    .my-tab-content > .left {
        width: 70%;
    }

    .my-tab-content > .right {
        width: 30%;
        padding-left: 20px;
        box-sizing: border-box;
    }
</style>
