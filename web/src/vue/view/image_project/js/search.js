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

const imageSubjects = {
    selected: [] ,
    selectedIds: [] ,
    data: [] ,
    page: 1 ,
    size: TopContext.size ,
    sizes: TopContext.sizes ,
    total: 0 ,
    value: '' ,
};

const images = {
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
            images: G.copy(images) ,

            // 分类
            categories: G.copy(categories) ,

            // 关联主体
            imageSubjects: G.copy(imageSubjects) ,

            // 标签
            tags: G.copy(tags) ,

            val: {
                categories: false ,
                imageSubjects: true ,
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
                    name: '点赞数量' ,
                    key: 'praise_count|desc' ,
                } ,
                {
                    name: '查看次数' ,
                    key: 'view_count|desc' ,
                } ,
            ] ,
        };
    } ,

    mounted () {
        this.initDom();
        this.initEvent();
        this.getWithPager();
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

        // 图片点赞 | 取消点赞
        praiseHandle (row) {
            if (this.pending('praiseHandle')) {
                return ;
            }
            const praised = row.is_praised ? 0 : 1;
            this.pending('praiseHandle' , true);
            Api.praise
                .praiseHandle(null , {
                    relation_type: 'image_project' ,
                    relation_id: row.id ,
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
            if (query.category_id) {
                // 选中该分类
                this.focusCategoryByCategoryId(query.category_id);
            }
            if (query.image_subject_id) {
                // 选中该主体
                this.focusImageSubjectByImageSubjectId(query.image_subject_id);
            }
            if (query.tag_id) {
                // 选中该主体
                this.focusTagByTagId(query.tag_id);
            }
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
                    this.images.page = 1;
                    this.getWithPager();
                })
                .finally(() => {
                    this.pending('focusCategoryByCategoryId' , false);
                });
        } ,

        focusImageSubjectByImageSubjectId (imageSubjectId) {
            imageSubjectId = parseInt(imageSubjectId);
            Api.imageSubject
                .show(imageSubjectId)
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message);
                        return ;
                    }
                    if (this.imageSubjects.selectedIds.indexOf(imageSubjectId) >= 0) {
                        return ;
                    }
                    const data = res.data;
                    this.imageSubjects = G.copy(imageSubjects);
                    this.imageSubjects.selected.push(data);
                    this.imageSubjects.selectedIds.push(data.id);
                    this.images.page = 1;
                    this.getWithPager();
                })
                .finally(() => {

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
                    this.images.page = 1;
                    this.getWithPager();
                })
                .finally(() => {

                });
        } ,

        resetFilter () {
            this.categories = G.copy(categories);
            this.imageSubjects = G.copy(imageSubjects);
            this.tags = G.copy(tags);
            this.images.order = '';
            this.images.page = 1;
            this.getWithPager();
        } ,

        orderInImageProject (v) {
            this.closeOrderSelectorInHorizontal();
            this.closeOrderSelectorInVertical();
            this.images.order = v.key;
            this.images.page = 1;
            this.getWithPager();
        } ,

        pageEventInImageProject (page , size) {
            this.images.page = page;
            this.images.size = size;
            this.getWithPager();
        } ,

        sizeEventInImageProject (size , page) {
            this.images.size = size;
            this.images.page = page;
            this.getWithPager();
        } ,

        pageEventInImageSubject (page , size) {
            this.imageSubjects.page = page;
            this.imageSubjects.size = size;
            this.getImageSubjects();
        } ,

        sizeEventInImageSubject (size , page) {
            this.imageSubjects.size = size;
            this.imageSubjects.page = page;
            this.getImageSubjects();
        } ,

        filterModeChangeEvent () {
            this.images.page = 1;
            this.getWithPager();
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
            this.images.page = 1;
            this.getWithPager();
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
            this.images.page = 1;
            this.getWithPager();
        } ,

        delTag (tag) {
            const index = this.findIndexInTagsByTagId(tag.tag_id);
            this.tags.selected.splice(index , 1);
            this.tags.selectedIds.splice(index , 1);
            this.images.page = 1;
            this.getWithPager();
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
            this.images.page = 1;
            this.getWithPager();
        } ,

        delCategory (category) {
            const index = this.findIndexInCategoriesByCategoryId(category.id);
            this.categories.selected.splice(index , 1);
            this.categories.selectedIds.splice(index , 1);
            this.images.page = 1;
            this.getWithPager();
        } ,

        resetCategoryFilter () {
            this.categories = G.copy(categories);
            this.getCategories();
            this.images.page = 1;
            this.getWithPager();
        } ,

        // 检查分类是否存在
        findIndexInImageSubjectsByImageSubjectId (imageSubjectId) {
            for (let i = 0; i < this.imageSubjects.selected.length; ++i)
            {
                const cur = this.imageSubjects.selected[i];
                if (cur.id === imageSubjectId) {
                    return i;
                }
            }
            return false;
        } ,


        // 根据分类对内容进行过滤
        filterByImageSubject (imageSubject) {
            const index = this.findIndexInImageSubjectsByImageSubjectId(imageSubject.id);
            if (index === false) {
                this.addImageSubject(imageSubject);
            } else {
                this.delImageSubject(imageSubject);
            }
        } ,

        addImageSubject (imageSubject) {
            const index = this.findIndexInCategoriesByCategoryId(imageSubject.id);
            this.imageSubjects.selected.push(imageSubject);
            this.imageSubjects.selectedIds.push(imageSubject.id);
            this.images.page = 1;
            this.getWithPager();
        } ,

        delImageSubject (imageSubject) {
            const index = this.findIndexInCategoriesByCategoryId(imageSubject.id);
            this.imageSubjects.selected.splice(index , 1);
            this.imageSubjects.selectedIds.splice(index , 1);
            this.images.page = 1;
            this.getWithPager();
        } ,

        searchImageSubject () {
            this.imageSubjects.page = 1;
            this.getImageSubjects();
        } ,

        resetImageSubjectFilter () {
            this.imageSubjects = G.copy(imageSubjects);
            this.getImageSubjects();
            this.images.page = 1;
            this.getWithPager();
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

        showSubjectSelector () {
            this.dom.imageSubjectSelector.removeClass('hide');
            this.dom.imageSubjectSelector.startTransition('show');
            this.imageSubjects.page = 1;
            this.getImageSubjects();
        } ,

        closeSubjectSelector () {
            this.imageSubjects.data = [];
            this.dom.imageSubjectSelector.endTransition('show' , () => {
                this.dom.imageSubjectSelector.addClass('hide');
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
            Api.imageProject
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
            Api.imageProject
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

        // 获取关联主体
        getImageSubjects () {
            this.pending('getImageSubjects' , true);
            Api.imageProject
                .imageSubjects({
                    page: this.imageSubjects.page ,
                    size: this.imageSubjects.size ,
                    value: this.imageSubjects.value ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message);
                        return ;
                    }
                    const data = res.data;
                    this.imageSubjects.total = data.total;
                    this.imageSubjects.page = data.current_page;
                    this.imageSubjects.maxPage = data.last_page;
                    this.imageSubjects.data = data.data;
                })
                .finally(() => {
                    this.pending('getImageSubjects' , false);
                });
        } ,

        initDom () {
            this.dom.win = G(window);
            this.dom.categorySelector = G(this.$refs['category-selector']);
            this.dom.tagSelector = G(this.$refs['tag-selector']);
            this.dom.imageSubjectSelector = G(this.$refs['image-subject-selector']);
            this.dom.orderSelector = G(this.$refs['order-selector']);
            this.dom.orderSelectorInHorizontal = G(this.$refs['order-selector-in-horizontal']);
            this.dom.orderSelectorInVertical = G(this.$refs['order-selector-in-vertical']);
            this.dom.filterSelectorInHorizontal = G(this.$refs['filter-selector-in-horizontal']);
            this.dom.filterFixedInSlidebar = G(this.$refs['filter-fiexed-in-slidebar']);


            this.dom.search = G(this.$parent.$refs['search']);
        } ,

        getWithPager () {
            this.pending('getWithPager' , true);
            Api.imageProject
                .index({
                    page: this.images.page ,
                    mode: this.tags.mode ,
                    size: this.images.size ,
                    category_ids: G.jsonEncode(this.categories.selectedIds) ,
                    image_subject_ids: G.jsonEncode(this.imageSubjects.selectedIds) ,
                    tag_ids: G.jsonEncode(this.tags.selectedIds) ,
                    value: this.images.value ,
                    order: this.images.order ,
                    type: this.images.type ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message);
                    }
                    const data = res.data;
                    this.images.page = data.current_page;
                    this.images.total = data.total;
                    this.images.data = data.data;
                })
                .finally(() => {
                    this.pending('getWithPager' , false);
                });
        } ,

        searchEvent (e) {
            if (e.keyCode !== 13) {
                return ;
            }
            const tar = G(e.currentTarget);
            this.images.value = tar.val();
            this.images.page = 1;
            this.getWithPager();
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
