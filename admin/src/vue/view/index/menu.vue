
<template>
    <div class='infinite-classification'>
        <div class="list">
            <v-item v-for="v in permissions" :item="v" :key="v.id"></v-item>
        </div>
    </div>
</template>

<script>
    import item from './item.vue';

    export default {
        name: "my-menu" ,

        data () {
            return {
                ins: {} ,
                permissions: [] ,
                // treeField: {
                //     id: 'id' ,
                //     p_id: 'parentId' ,
                // } ,
                once: true ,
            };
        } ,

        mounted () {

        } ,

        components: {
            'v-item': item
        } ,

        methods: {

            initData () {
                const permissions = this.state().permissions;
                // 生成id & parentId
                G.tree.buildTree(permissions , {
                    id:     'id' ,
                    p_id:   'parentId' ,
                });
                G.tree.loop(permissions , (v) => {
                    // 临时性措施
                    const isShowTool = Number(this.state().settings.is_show_tool);
                    if (isShowTool === 0) {
                       if (v.path === '/toolbox/index') {
                           v.hidden = true;
                       }
                    }
                    if (this.TopContext.debug) {
                        return ;
                    }
                    // 权限处理
                    // 仅在非调试模式下启用
                    // 检查当前登录用户是否具备查看权限
                    v.hidden = !this.state().myPermission.some((v1) => {
                        return v.key === v1.value;
                    });
                });

                this.permissions = permissions;
            } ,

            // 初始化菜单
            init () {
                // 初始化数据
                const self = this;
                this.initData();
                const route = this.findRouteByPath(this.$route.path);
                this.initPositions(route.id);
                this.$nextTick(() => {
                    this.initIns();
                    // 选中当前选择项目
                    // this.ins.ic.focus(route.id);
                    // if (this.state().slidebar === 'vertical') {
                    //     this.ins.ic.icon('text');
                    // }
                });
            } ,

            initIns () {
                const self = this;
                this.ins.ic = new InfiniteClassification(this.$el , {
                    // 菜单展开动画过渡时间
                    time: 200 ,
                    // 次要的图标类型，new || number || switch
                    icon: 'switch' ,
                    // 初始状态，spread || shrink
                    status: 'shrink' ,
                    // 层级视觉显示效果
                    amount: 12 ,
                    // 同层级是否互斥
                    exclution: false ,
                    // 是否菜单也可被选中
                    menuFocus: false ,
                    // 点击项后是否选中
                    focus: true ,
                    // 是否选中顶级菜单
                    topFocus: false ,
                    // 是否展开首个项
                    spreadFirst: false ,
                    // 子级项点击后回调
                    child (id) {
                        if (self.once) {
                            self.once = false;
                            return ;
                        }
                        self.$emit('on-focus' , id);
                    } ,
                });

            } ,

        } ,

    }
</script>

<style scoped>
</style>
