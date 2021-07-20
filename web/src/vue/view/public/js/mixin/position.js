
export default {
    methods: {
        initPositionByRouteAndPositions (route , positions) {
            const position = this.findByRouteAndPositions(route , positions);
            this.dispatch('position' , position);
        } ,

        // 找到当前路由所在菜单项
        findByRouteAndPositions (path , positions) {
            let res = false;
            for (let i = 0; i < positions.length; ++i)
            {
                const cur = positions[i];
                let route = cur.route;
                // 搜索1：完整匹配
                if (route === path) {
                    res = cur;
                    break;
                }
                // 搜搜2：正则匹配
                route = route.replace(/\/:\w+(\/?)/g , '/.+?$1');
                route = route.replace(/(\/|\?)/g , '\$1');
                if (new RegExp('^' + route + '$').test(path)) {
                    res = cur;
                    break;
                }
                // 嵌套循环匹配
                res = this.findByRouteAndPositions(path , cur.children);
                if (res !== false) {
                    break;
                }
            }
            return res;
        } ,

        // 获取当前路由中的最大id
        getMaxIdByPositions (positions) {
            const ids = [];
            G.tree.loop(positions , (v) => {
                ids.push(v.id);
            });
            return Math.max(...ids);
        } ,

        findByKeyAndPositions (key , positions) {
            positions = G.tree.flat(positions);
            for (let i = 0; i < positions.length; ++i)
            {
                const cur = positions[i];
                if (cur.key === key) {
                    return cur;
                }
            }
            throw new Error('不支持的 key');
        } ,

        findNavByIdAndNavs (key , positions) {
            positions = G.tree.flat(positions);
            for (let i = 0; i < positions.length; ++i)
            {
                const cur = positions[i];
                if (cur.id === key) {
                    return cur;
                }
            }
            throw new Error('不支持的 key');
        } ,

        getParentPositionsById (id , positions , isSaveSelf = true) {
            positions = G.tree.flat(positions);
            return G.tree.parents(id , positions , {
                id: 'id' ,
                p_id: 'parentId' ,
            } , isSaveSelf , false);
        } ,
    } ,
};
