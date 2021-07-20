<template>
    <div class="run-page" v-if="total > 0">
        <div class="inner">
            <div class="info">共 {{ total }} 条记录</div>
            <div class="sizes" v-show="showSizes">
                <span class="m-r-5">每页</span>
                <select class="selector m-r-5" v-model="sizeCopy" @change="sizeChangeEvent">
                    <option v-for="v in sizes" :key="v" :value="v">{{ v }}</option>
                </select>
                <span>条</span>
            </div>
            <div class="links">
                <a class="link home" v-ripple :class="{'run-cursor-not-allow': page === 1}" @click="toPage(1)">首页</a>
                <a class="link" :class="{'run-cursor-not-allow': page === 1}" v-ripple @click="toPage(pageCopy - 1)">上一页</a>
                <a class="link" v-ripple v-for="v in pages" :class="{cur: pageCopy === v , 'run-cursor-not-allow': page === v}" :key="v" @click="toPage(v)">{{ v }}</a>
                <a class="link" :class="{'run-cursor-not-allow': page === maxPage}" v-ripple @click="toPage(pageCopy + 1)">下一页</a>
                <a class="link end" :class="{'run-cursor-not-allow': page === maxPage}" v-ripple @click="toPage(maxPage)">尾页</a>
            </div>
            <div class="go-to">共 {{ maxPage }} 页 跳至 <input type="text" class="step" ref="input" @keyup.enter="inputEvent"> 页
                <!--            <button type="button" class="confirm" @click="toPage($refs.input.value)">确定</button>-->
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "page" ,

        props: {
            total: {
                type: Number ,
                default: 0 ,
            } ,

            page: {
                type: Number ,
                default: 1 ,
            } ,

            sizes: {
                type: Array ,
                default () {
                    return [
                        20 ,
                        50 ,
                        100 ,
                        200 ,
                    ];
                } ,
            } ,

            size: {
                type: Number ,
                default: 20 ,
            } ,

            onPageChange: {
                type: Function ,
            } ,

            onSizeChange: {
                type: Function ,
            } ,

            showSizes: {
                type: Boolean ,
                default: true ,
            } ,
        } ,

        data () {
            return {
                minPage: 1 ,
                maxPage: 1 ,
                startPage: 1 ,
                endPage: 1 ,
                pages: [] ,
                before: 3 ,
                after: 4 ,
                pageCopy: 1 ,
                sizeCopy: 1 ,
                showPage: 8 ,
            };
        } ,

        mounted () {
            this.initPage();
        } ,

        methods: {
            toPage (page) {
                if (!G.isValid(page)) {
                    this.message('error' , '请提供有效的页数');
                    return ;
                }
                page = parseInt(page);
                page = Math.max(this.minPage , Math.min(page , this.maxPage));
                if (this.pageCopy === page) {
                    return ;
                }
                this.pageCopy = page;
                this.$emit('on-page-change' , this.pageCopy , this.sizeCopy);
            } ,

            initPage () {
                this.maxPage = Math.ceil(this.total / this.sizeCopy);
                // 当前页数
                if (this.page < this.showPage) {
                    this.startPage = 1;
                    if (this.maxPage <= this.showPage) {
                        this.endPage = this.maxPage;
                    } else {
                        this.endPage = this.showPage;
                    }
                } else {
                    let startPage = this.page - this.before;
                    let endPage = this.page + this.after;
                    this.startPage = Math.max(this.minPage , endPage >= this.maxPage ? this.maxPage - this.showPage : startPage);
                    this.endPage = Math.min(this.maxPage , endPage);
                }
                this.pages = [];
                for (let i = this.startPage; i <= this.endPage; ++i)
                {
                    this.pages.push(i);
                }
            } ,

            inputEvent (e) {
                const tar = G(e.currentTarget);
                let value = tar.val();
                const numberReg = /^\d+$/;
                if (!numberReg.test(value)) {
                    this.message('error' , '请提供数字');
                    return ;
                }
                value = parseInt(value);
                if (value < 1) {
                    this.message('error' , '请提供有效数字');
                    return ;
                }
                this.toPage(value);
                tar.origin('focus');
                tar.origin('select');
            } ,

            sizeChangeEvent () {
                this.pageCopy = 1;
                this.initPage();
                this.$emit('on-size-change' , this.sizeCopy , this.pageCopy)
            } ,
        } ,

        watch: {
            page: {
                immediate: true ,
                handler (newVal , oldVal) {
                    this.pageCopy = newVal;
                    this.initPage();
                } ,
            } ,

            size: {
                immediate: true ,
                handler (newVal , oldVal) {
                    this.sizeCopy = newVal;
                    this.initPage();
                } ,
            } ,

            total (newVal , oldVal) {
                this.initPage();
            } ,
        } ,

    }
</script>

<style scoped></style>
