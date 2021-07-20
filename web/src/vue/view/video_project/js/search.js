const categories = {
    selected: [] ,
    selectedIds: [] ,
    data: [] ,
};


const tags = {
    selected: [] ,
    selectedIds: [] ,
    data: [] ,
    page: 1 ,
    size: TopContext.size ,
    sizes: TopContext.sizes ,
    total: 1 ,
    mode: 'strict' ,
    value: '' ,
    type: 'pro' ,
};

const videoSeries = {
    selected: [] ,
    selectedIds: [] ,
    data: [] ,
    page: 1 ,
    size: TopContext.size ,
    sizes: TopContext.sizes ,
    total: 0 ,
    value: '' ,
};

const videoCompanies = {
    selected: [] ,
    selectedIds: [] ,
    data: [] ,
    page: 1 ,
    size: TopContext.size ,
    sizes: TopContext.sizes ,
    total: 0 ,
    value: '' ,
};

const videoProjects = {
    data: [] ,
    value: '' ,
    size: TopContext.size ,
    sizes: TopContext.sizes ,
    total: 0 ,
    page: 1 ,
    order: '' ,
    type: 'pro' ,
};

export default {
    name: "search" ,

    data () {
        return {
            // 图片专题
            videoProjects: G.copy(videoProjects) ,

            // 分类
            categories: G.copy(categories) ,

            // 视频系列
            videoSeries: G.copy(videoSeries) ,

            // 视频公司
            videoCompanies: G.copy(videoCompanies) ,

            // 标签
            tags: G.copy(tags) ,

            val: {
                categories: false ,
                videoSeries: true ,
                pending: {} ,
            } ,

            dom: {} ,
            ins: {} ,

            // 排序
            orders: [
                {
                    name: '上传日期' ,
                    key: 'created_at|desc' ,
                } ,
                {
                    name: '播放数量' ,
                    key: 'play_count|desc' ,
                } ,
                {
                    name: '点赞数量' ,
                    key: 'praise_count|desc' ,
                } ,
                {
                    name: '查看次数' ,
                    key: 'view_count|desc' ,
                } ,
                {
                    name: '发布日期' ,
                    key: 'release_date|desc' ,
                } ,
                {
                    name: '完结日期' ,
                    key: 'end_date|desc' ,
                } ,
                {
                    name: '发布年份' ,
                    key: 'release_year|desc' ,
                } ,
            ] ,
        };
    } ,

    mounted () {
        this.initDom();
        this.initEvent();
        this.getData();
    } ,

    beforeRouteEnter (to , from , next) {
        next((vm) => {
           vm.initQuery(to.query);
        });
    } ,

    beforeRouteUpdate  (to , from , next) {
        this.initQuery(to.query);
        next();
    } ,
    methods: {

        praiseHandle (row) {
            if (this.pending('praiseHandle')) {
                return ;
            }
            const self = this;
            const praised = row.is_praised ? 0 : 1;
            this.pending('praiseHandle' , true);
            Api.videoProject
                .praiseHandle(row.id , null , {
                    action: praised ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message , res.code , () => {
                            this.praiseHandle(row);
                        });
                        return ;
                    }
                    row.is_praised = praised;
                    praised ? row.praise_count++ : row.praise_count--;
                })
                .finally(() => {
                    this.pending('praiseHandle' , false);
                });
        } ,

        initQuery (query) {
            if (query.video_series_id) {
                // 视频系列
                this.focusVideoSeriesByVideoSeriesId(query.video_series_id);
            }
            if (query.video_company_id) {
                // 视频系列
                this.focusVideoCompanyByVideoCompanyId(query.video_company_id);
            }
            if (query.category_id) {
                // 选中该分类
                this.focusCategoryByCategoryId(query.category_id);
            }
            if (query.tag_id) {
                // 选中该主体
                this.focusTagByTagId(query.tag_id);
            }
        } ,

        focusVideoSeriesByVideoSeriesId (videoSeriesId) {
            videoSeriesId = parseInt(videoSeriesId);
            this.pending('focusVideoSeriesByVideoSeriesId' , true);
            Api.videoSeries
                .show(videoSeriesId)
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message);
                        return ;
                    }
                    if (this.videoSeries.selectedIds.indexOf(videoSeriesId) >= 0) {
                        return ;
                    }
                    const data = res.data;
                    this.videoSeries = G.copy(videoSeries);
                    this.videoSeries.selected.push(data);
                    this.videoSeries.selectedIds.push(data.id);
                    this.videoProjects.page = 1;
                    this.getData();
                })
                .finally(() => {
                    this.pending('focusVideoSeriesByVideoSeriesId' , false);
                });
        } ,

        focusVideoCompanyByVideoCompanyId (videoCompanyId) {
            videoCompanyId = parseInt(videoCompanyId);
            this.pending('focusVideoCompanyByVideoCompanyId' , true);
            Api.videoCompany
                .show(videoCompanyId)
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message);
                        return ;
                    }
                    if (this.videoCompanies.selectedIds.indexOf(videoCompanyId) >= 0) {
                        return ;
                    }
                    const data = res.data;
                    this.videoCompanies = G.copy(videoCompanies);
                    this.videoCompanies.selected.push(data);
                    this.videoCompanies.selectedIds.push(data.id);
                    this.videoProjects.page = 1;
                    this.getData();
                })
                .finally(() => {
                    this.pending('focusVideoCompanyByVideoCompanyId' , false);
                });
        } ,

        focusCategoryByCategoryId (categoryId) {
            categoryId = parseInt(categoryId);
            this.pending('focusCategoryByCategoryId' , true);
            Api.category
                .show(categoryId)
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message);
                        return ;
                    }
                    if (this.categories.selectedIds.indexOf(categoryId) >= 0) {
                        return ;
                    }
                    const data = res.data;
                    this.categories = G.copy(categories);
                    this.categories.selected.push(data);
                    this.categories.selectedIds.push(data.id);
                    this.videoProjects.page = 1;
                    this.getData();
                })
                .finally(() => {
                    this.pending('focusCategoryByCategoryId' , false);
                });
        } ,

        focusTagByTagId (tagId) {
            tagId = parseInt(tagId);
            Api.tag
                .show(tagId)
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.message('error' , res.message);
                        return ;
                    }
                    if (this.tags.selectedIds.indexOf(tagId) >= 0) {
                        return ;
                    }
                    const data = res.data;
                    data.tag_id = data.id;
                    this.tags = G.copy(tags);
                    this.tags.selected.push(data);
                    this.tags.selectedIds.push(data.id);
                    this.videoProjects.page = 1;
                    this.getData();
                })
                .finally(() => {

                });
        } ,

        resetFilter () {
            this.categories = G.copy(categories);
            this.videoSeries = G.copy(videoSeries);
            this.videoCompanies = G.copy(videoCompanies);
            this.tags = G.copy(tags);
            this.videoProjects.order = '';
            this.videoProjects.page = 1;
            this.getData();
        } ,

        orderInImageProject (v) {
            this.closeOrderSelectorInHorizontal();
            this.closeOrderSelectorInVertical();
            this.videoProjects.order = v.key;
            this.videoProjects.page = 1;
            this.getData();
        } ,

        pageEventInVideoProject (page , size) {
            this.videoProjects.page = page;
            this.videoProjects.size = size;
            this.getData();
        } ,

        sizeEventInVideoProject (size , page) {
            this.videoProjects.size = size;
            this.videoProjects.page = page;
            this.getData();
        } ,

        filterModeChangeEvent () {
            this.videoProjects.page = 1;
            this.getData();
        } ,

        pageEventInTag (page , size) {
            this.tags.page = page;
            this.tags.size = size;
            this.getTags();
        } ,

        sizeEventInTag (size , page) {
            this.tags.size = size;
            this.tags.page = page;
            this.getTags();
        } ,

        resetTagFilter () {
            this.tags = G.copy(tags , true);
            this.getTags();
            this.videoProjects.page = 1;
            this.getData();
        } ,

        // 检查分类是否存在
        findIndexInTagsByTagId (tagId) {
            for (let i = 0; i < this.tags.selected.length; ++i)
            {
                const cur = this.tags.selected[i];
                if (cur.tag_id === tagId) {
                    return i;
                }
            }
            return false;
        } ,

        // 根据分类对内容进行过滤
        filterByTag (tag) {
            const index = this.findIndexInTagsByTagId(tag.tag_id);
            if (index === false) {
                this.addTag(tag);
            } else {
                this.delTag(tag);
            }
        } ,

        addTag (tag) {
            const index = this.findIndexInTagsByTagId(tag.id);
            this.tags.selected.push(tag);
            this.tags.selectedIds.push(tag.tag_id);
            this.videoProjects.page = 1;
            this.getData();
        } ,

        delTag (tag) {
            const index = this.findIndexInTagsByTagId(tag.tag_id);
            this.tags.selected.splice(index , 1);
            this.tags.selectedIds.splice(index , 1);
            this.videoProjects.page = 1;
            this.getData();
        } ,

        searchTag () {
            this.tags.page = 1;
            this.getTags();
        } ,

        // 检查分类是否存在
        findIndexInCategoriesByCategoryId (categoryId) {
            for (let i = 0; i < this.categories.selected.length; ++i)
            {
                const cur = this.categories.selected[i];
                if (cur.id === categoryId) {
                    return i;
                }
            }
            return false;
        } ,

        // 根据分类对内容进行过滤
        filterByCategory (category) {
            const index = this.findIndexInCategoriesByCategoryId(category.id);
            if (index === false) {
                this.addCategory(category);
            } else {
                this.delCategory(category);
            }
        } ,

        addCategory (category) {
            const index = this.findIndexInCategoriesByCategoryId(category.id);
            this.categories.selected.push(category);
            this.categories.selectedIds.push(category.id);
            this.videoProjects.page = 1;
            this.getData();
        } ,

        delCategory (category) {
            const index = this.findIndexInCategoriesByCategoryId(category.id);
            this.categories.selected.splice(index , 1);
            this.categories.selectedIds.splice(index , 1);
            this.videoProjects.page = 1;
            this.getData();
        } ,

        resetCategoryFilter () {
            this.categories = G.copy(categories);
            this.getCategories();
            this.videoProjects.page = 1;
            this.getData();
        } ,

        // 检查分类是否存在
        findIndexInVideoSeriesByVideoSeriesId (subjectId) {
            for (let i = 0; i < this.videoSeries.selected.length; ++i)
            {
                const cur = this.videoSeries.selected[i];
                if (cur.id === subjectId) {
                    return i;
                }
            }
            return false;
        } ,


        // 根据分类对内容进行过滤
        filterByVideoSeries (row) {
            const index = this.findIndexInVideoSeriesByVideoSeriesId(row.id);
            if (index === false) {
                this.addVideoSeries(row);
            } else {
                this.delVideoSeries(row);
            }
        } ,

        addVideoSeries (row) {
            const index = this.findIndexInCategoriesByCategoryId(row.id);
            this.videoSeries.selected.push(row);
            this.videoSeries.selectedIds.push(row.id);
            this.videoProjects.page = 1;
            this.getData();
        } ,

        delVideoSeries (row) {
            const index = this.findIndexInCategoriesByCategoryId(row.id);
            this.videoSeries.selected.splice(index , 1);
            this.videoSeries.selectedIds.splice(index , 1);
            this.videoProjects.page = 1;
            this.getData();
        } ,

        searchVideoSeries () {
            this.videoSeries.page = 1;
            this.getVideoSeries();
        } ,

        resetVideoSeriesFilter () {
            this.videoSeries = G.copy(videoSeries);
            this.getVideoSeries();
            this.videoProjects.page = 1;
            this.getData();
        } ,

        pageEventInVideoSeries (page , size) {
            this.videoSeries.page = page;
            this.videoSeries.size = size;
            this.getVideoSeries();
        } ,

        sizeEventInVideoSeries (size , page) {
            this.videoSeries.size = size;
            this.videoSeries.page = page;
            this.getVideoSeries();
        } ,

        showVideoSeriesSelector () {
            this.dom.videoSeriesSelector.removeClass('hide');
            this.dom.videoSeriesSelector.startTransition('show');
            this.videoSeries.page = 1;
            this.getVideoSeries();
        } ,

        closeVideoSeriesSelector () {
            this.videoSeries.data = [];
            this.dom.videoSeriesSelector.endTransition('show' , () => {
                this.dom.videoSeriesSelector.addClass('hide');
            });
        } ,

        getVideoSeries () {
            this.pending('getVideoSeries' , true);
            Api.videoSeries
                .index({
                    page: this.videoSeries.page ,
                    size: this.videoSeries.size ,
                    value: this.videoSeries.value ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message);
                        return ;
                    }
                    const data = res.data;
                    this.videoSeries.total = data.total;
                    this.videoSeries.page = data.current_page;
                    this.videoSeries.maxPage = data.last_page;
                    this.videoSeries.data = data.data;
                })
                .finally(() => {
                    this.pending('getVideoSeries' , false);
                });
        } ,

        // ------------------------
        // 检查分类是否存在
        findIndexInVideoCompanyByVideoCompanyId (subjectId) {
            for (let i = 0; i < this.videoCompanies.selected.length; ++i)
            {
                const cur = this.videoCompanies.selected[i];
                if (cur.id === subjectId) {
                    return i;
                }
            }
            return false;
        } ,


        // 根据分类对内容进行过滤
        filterByVideoCompany (row) {
            const index = this.findIndexInVideoCompanyByVideoCompanyId(row.id);
            if (index === false) {
                this.addVideoCompany(row);
            } else {
                this.delVideoCompany(row);
            }
        } ,

        addVideoCompany (row) {
            const index = this.findIndexInCategoriesByCategoryId(row.id);
            this.videoCompanies.selected.push(row);
            this.videoCompanies.selectedIds.push(row.id);
            this.videoProjects.page = 1;
            this.getData();
        } ,

        delVideoCompany (row) {
            const index = this.findIndexInCategoriesByCategoryId(row.id);
            this.videoCompanies.selected.splice(index , 1);
            this.videoCompanies.selectedIds.splice(index , 1);
            this.videoProjects.page = 1;
            this.getData();
        } ,

        searchVideoCompany () {
            this.videoCompanies.page = 1;
            this.getVideoCompany();
        } ,

        resetVideoCompanyFilter () {
            this.videoCompanies = G.copy(videoCompanies);
            this.getVideoCompany();
            this.videoProjects.page = 1;
            this.getData();
        } ,

        pageEventInVideoCompany (page , size) {
            this.videoCompanies.page = page;
            this.videoCompanies.size = size;
            this.getVideoCompany();
        } ,

        sizeEventInVideoCompany (size , page) {
            this.videoCompanies.size = size;
            this.videoCompanies.page = page;
            this.getVideoCompany();
        } ,

        showVideoCompanySelector () {
            this.dom.videoCompanySelector.removeClass('hide');
            this.dom.videoCompanySelector.startTransition('show');
            this.videoCompanies.page = 1;
            this.getVideoCompany();
        } ,

        closeVideoCompanySelector () {
            this.videoCompanies.data = [];
            this.dom.videoCompanySelector.endTransition('show' , () => {
                this.dom.videoCompanySelector.addClass('hide');
            });
        } ,

        getVideoCompany () {
            this.pending('getVideoCompany' , true);
            Api.videoCompany
                .index({
                    page: this.videoCompanies.page ,
                    size: this.videoCompanies.size ,
                    value: this.videoCompanies.value ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message);
                        return ;
                    }
                    const data = res.data;
                    this.videoCompanies.total = data.total;
                    this.videoCompanies.page = data.current_page;
                    this.videoCompanies.maxPage = data.last_page;
                    this.videoCompanies.data = data.data;
                })
                .finally(() => {
                    this.pending('getVideoCompany' , false);
                });
        } ,

        showOrderSelectorInHorizontal () {
            this.dom.orderSelectorInHorizontal.removeClass('hide');
            this.dom.orderSelectorInHorizontal.startTransition('show');
        } ,

        closeOrderSelectorInHorizontal () {
            this.dom.orderSelectorInHorizontal.endTransition('show' , () => {
                this.dom.orderSelectorInHorizontal.addClass('hide');
            });
        } ,

        showOrderSelectorInVertical () {
            this.dom.orderSelectorInVertical.removeClass('hide');
            this.dom.orderSelectorInVertical.startTransition('show');
        } ,

        closeOrderSelectorInVertical () {
            this.dom.orderSelectorInVertical.endTransition('show' , () => {
                this.dom.orderSelectorInVertical.addClass('hide');
            });
        } ,

        showCategorySelector () {
            this.getCategories();
            this.dom.categorySelector.removeClass('hide');
            this.dom.categorySelector.startTransition('show');
        } ,

        closeCategorySelector () {
            this.categories.data = [];
            this.dom.categorySelector.endTransition('show' , () => {
                this.dom.categorySelector.addClass('hide');
            });
        } ,

        // 显示标签选择器
        showTagSelector () {
            this.dom.tagSelector.removeClass('hide');
            this.dom.tagSelector.startTransition('show');
            this.tags.page = 1;
            this.getTags();
        } ,

        closeTagSelector () {
            this.dom.tagSelector.endTransition('show' , () => {
                this.dom.tagSelector.addClass('hide');
            });
        } ,

        // 图片-按标签分类获取的图片
        getTags () {
            this.pending('getTags' , true);
            Api.videoProject
                .hotTagsWithPager({
                    size: this.tags.size ,
                    page:  this.tags.page ,
                    value: this.tags.value ,
                    type: this.tags.type ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message);
                        return ;
                    }
                    const data = res.data;
                    this.tags.page = data.current_page;
                    this.tags.total = data.total;
                    this.tags.data = data.data;
                })
                .finally(() => {
                    this.pending('getTags' , false);
                });
        } ,

        // 获取分类数据
        getCategories () {
            this.pending('getCategories' , true);
            Api.videoProject
                .categories()
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message);
                        return ;
                    }
                    this.categories.data = res.data;
                })
                .finally(() => {
                    this.pending('getCategories' , false);
                });
        } ,

        initDom () {
            this.dom.win = G(window);
            this.dom.categorySelector = G(this.$refs['category-selector']);
            this.dom.tagSelector = G(this.$refs['tag-selector']);

            this.dom.videoSeriesSelector = G(this.$refs['video-series-selector']);
            this.dom.videoCompanySelector = G(this.$refs['video-company-selector']);


            this.dom.orderSelector = G(this.$refs['order-selector']);
            this.dom.orderSelectorInHorizontal = G(this.$refs['order-selector-in-horizontal']);
            this.dom.orderSelectorInVertical = G(this.$refs['order-selector-in-vertical']);
            this.dom.filterSelectorInHorizontal = G(this.$refs['filter-selector-in-horizontal']);
            this.dom.filterFixedInSlidebar = G(this.$refs['filter-fiexed-in-slidebar']);

            this.dom.search = G(this.$parent.$refs['search']);
        } ,

        getData () {
            this.pending('getData' , true);
            Api.videoProject
                .index({
                    page: this.videoProjects.page ,
                    mode: this.tags.mode ,
                    size: this.videoProjects.size ,
                    video_series_ids: G.jsonEncode(this.videoSeries.selectedIds) ,
                    video_company_ids: G.jsonEncode(this.videoCompanies.selectedIds) ,
                    category_ids: G.jsonEncode(this.categories.selectedIds) ,
                    tag_ids: G.jsonEncode(this.tags.selectedIds) ,
                    value: this.videoProjects.value ,
                    order: this.videoProjects.order ,
                    type: this.videoProjects.type ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message);
                    }
                    const data = res.data;
                    this.videoProjects.page = data.current_page;
                    this.videoProjects.total = data.total;
                    this.videoProjects.data = data.data;
                })
                .finally(() => {
                    this.pending('getData' , false);
                });
        } ,

        searchEvent (e) {
            if (e.keyCode !== 13) {
                return ;
            }
            const tar = G(e.currentTarget);
            this.videoProjects.value = tar.val();
            this.videoProjects.page = 1;
            this.getData();
        } ,

        scrollEvent () {
            const y = this.dom.filterSelectorInHorizontal.getWindowOffsetVal('top');
            // console.log('y' , y);
            if (y > 0) {
                this.dom.filterFixedInSlidebar.endTransition('show' , () => {
                    this.dom.filterFixedInSlidebar.addClass('hide');
                });
            } else {
                this.dom.filterFixedInSlidebar.removeClass('hide');
                this.dom.filterFixedInSlidebar.startTransition('show');
            }
        } ,

        initEvent () {
            this.dom.win.on('click' , this.closeOrderSelectorInVertical.bind(this));
            this.dom.search.on('keyup' , this.searchEvent.bind(this));
            this.dom.win.on('scroll' , this.scrollEvent.bind(this));
        } ,
    } ,
}
