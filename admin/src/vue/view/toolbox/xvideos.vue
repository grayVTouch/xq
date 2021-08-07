<template>
    <i-tab-pane name="xvideos" label="Xvideos 网站视频下载辅助工具">

        <div class="my-tab-content">
            <div class="left" id="anchor-tab-content">
                <form @submit.prevent="downloadEvent">
                    <table class="input-table">
                        <tbody>
                        <tr id="save_dir">
                            <td>保存目录</td>
                            <td>
                                <input type="text" v-model="form.save_dir" class="form-text">
                                <i-button @click="$refs['my-resource-selector'].show()">资源管理器</i-button>
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
                        <tr id="filename">
                            <td>文件名</td>
                            <td>
                                <input type="text" v-model="form.filename" class="form-text">
                                <span class="need"></span>
                                <div class="msg">如果提供了文件名称则会以此名称保存，否则会自动生成文件名称。</div>
                                <div class="e-msg">{{ myValue.error.filename }}</div>
                            </td>
                        </tr>
                        <tr id="proxy">
                            <td>http(s)代理</td>
                            <td>
                                <input type="text" v-model="form.proxy_pass" class="form-text">
                                <span class="need"></span>
                                <div class="msg">如不想用代理，则留空;范例：http://127.0.0.1:10009</div>
                                <div class="e-msg">{{ myValue.error.proxy_pass }}</div>
                            </td>
                        </tr>
                        <tr id="video_source">
                            <td>视频源</td>
                            <td>
                                <input type="text" v-model="form.src" class="form-text">
                                <i-button :loading="myValue.pending.parseEvent" @click="parseEvent">解析</i-button>
                                <span class="need">*</span>
                                <div class="msg">
<pre>
获取方式：通过 fiddler 等代理工具捕获；这边提供 fiddler 获取方式：
1. 启动 fiddler ，设置全局代理
2. 安装 https 证书
3. filters 选项卡 -> hosts -> show only the following hosts，增加以下域名
    - hls2-l3.xvideos-cdn.com
    - hls-hw.xvideos-cdn.com
    - ... 其他待新增
4. filters 选项卡 -> request headers -> show only if url contains，输入以下内容
    - m3u8
5. 打开 https://www.xvideos.com，找到想要下载的视频，切换到给定的画质
6. ctrl + x，清空 fiddlers 的请求列表，刷新 xvideos 页面
7. 找到 fiddler 请求列表中首个 m3u8 请求地址，ctrl + u 复制
9. 在上述输入框中粘贴地址，继续下述流程
</pre>
                                </div>
                                <div class="e-msg">{{ myValue.error.src }}</div>
                            </td>
                        </tr>
                        <tr id="url">
                            <td>URL</td>
                            <td>
                                <input type="text" v-model="form.url" class="form-text">
                                <span class="need">*</span>
                                <div class="msg">下载切片列表 或 分片文件时需要用到；该 url 地址会在点击 解析按钮后自动分析填入；如果发现自动分析的结果不对，请手动矫正</div>
                                <div class="e-msg">{{ myValue.error.url }}</div>
                            </td>
                        </tr>
                        <tr id="definition">
                            <td>清晰度</td>
                            <td>
                                <i-select v-model="form.definition" :disabled="definitions.length === 0" class="w-400">
                                    <i-option v-for="v in definitions" :key="v" :value="v">{{ v }}</i-option>
                                </i-select>
                                <span class="need" v-if="definitions.length > 0">*</span>
                                <div class="msg"><span class="run-red" v-if="definitions.length > 0">存在多个清晰度！</span>请务必在输入视频源后点击解析按钮，根据视频源的不同，或许可以选择视频的画质！</div>
                                <div class="e-msg">{{ myValue.error.definition }}</div>
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
                    <i-anchor-link href="#save_dir" title="第一步：选择保存目录" />
                    <i-anchor-link href="#filename" title="第二步：可选输入文件名称" />
                    <i-anchor-link href="#video_source" title="第三步：输入视频源，点击解析按钮" />
                    <i-anchor-link href="#url" title="第四步：输入 url" />
                    <i-anchor-link href="#definition" title="第五步：如果存在多画质，则选择画质；否则跳过" />
                    <i-anchor-link href="#proxy" title="第六步：可选输入http(s)代理" />
                    <i-anchor-link href="#download" title="第七步：点击下载开始下载" />
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
        data () {
            return {
                myValue: {
                    pending: {} ,
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
                this.pending('parseEvent' , true);
                Api.xvideos
                    .parse(null , {
                       src: this.form.src ,
                    })
                    .then((res) => {
                        if (res.code !== TopContext.code.Success) {
                            this.errorHandle(res.message);
                            return ;
                        }
                        const data = res.data;
                        this.form.url = data.url;
                        this.definitions = data.definitions;
                    })
                    .finally(() => {
                        this.pending('parseEvent' , false);
                    });
            } ,

            downloadEvent () {
                this.pending('downloadEvent' , true);
                const form = G.copy(this.form);
                Api.xvideos
                    .download(null , form)
                    .then((res) => {
                        if (res.code !== TopContext.code.Success) {
                            this.errorHandle(res.message);
                            return ;
                        }
                        this.modal('success' , '下载任务添加成功！请到保存目录查看下载内容');
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
