const allHotTags = {
    page: 1 ,
    size: TopContext.size ,
    sizes: TopContext.sizes ,
    data: [] ,
    total: 0 ,
    value: '' ,
    type: 'pro' ,
};

const partHotTags = {
    size: 5 ,
    data: [] ,
    type: 'pro' ,
};

const images = {
    page: 1 ,
    maxPage: 1 ,
    size: TopContext.size ,
    data: [] ,
    total: 0 ,
    end: false ,
    type: 'pro' ,
};

const search = {
    mode: 'loose' ,
    tags: [] ,
    tagIds: [] ,
    type: 'pro' ,
};

export default {
    name: "image_project" ,
    data () {
        return {

            // 热门标签（部分）
            partHotTags: G.copy(partHotTags) ,

            // 所有标签
            allHotTags: G.copy(allHotTags) ,

            // 图片
            images: {...images} ,

            // 焦点图片
            imageSubject: [] ,
            curTag: '' ,
            dom: {} ,
            val: {
                pending: {} ,
                tagSelectorInSlidebar: false ,
            } ,
            search: G.copy(search) ,
        };
    } ,

    mounted () {
        this.initDom();
        this.initEvent();

        this.getImageProject();
        this.hotTags();
        this.newestInImageProjects();

        // 调试阶段打开
        // this.showTagSelector();
    } ,

    methods: {

        // 图片点赞
        praiseHandle (row) {
            if (this.pending('praiseHandle')) {
                return ;
            }
            const self = this;
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

        findInSelectedTagsByTagId (tagId) {
            for (let i = 0; i < this.search.tags.length; ++i)
            {
                const cur = this.search.tags[i];
                if (cur.tag_id === tagId) {
                    return cur;
                }
            }
            return false;
        } ,

        findIndexInSelectedTagsByTagId (tagId) {
            for (let i = 0; i < this.search.tags.length; ++i)
            {
                const cur = this.search.tags[i];
                if (cur.tag_id === tagId) {
                    return i;
                }
            }
            return false;
        } ,

        findIndexInSelectedTagIdsByTagId (tagId) {
            for (let i = 0; i < this.search.tagIds.length; ++i)
            {
                const id = this.search.tagIds[i];
                if (id === tagId) {
                    return i;
                }
            }
            return false;
        } ,

        filterByTag (tag) {
            const index = this.findIndexInSelectedTagsByTagId(tag.tag_id);
            if (index === false) {
                this.search.tags.push(tag);
                this.search.tagIds.push(tag.tag_id);
                this._val('selected_tag_' + tag.tag_id , true);
            } else {
                this.search.tags.splice(index , 1);
                this.search.tagIds.splice(index , 1);
                this._val('selected_tag_' + tag.tag_id , false);
            }
            if (this.search.tags.length === 0) {
                this.newestInImageProjects(true);
                return ;
            }
            this.getWithPagerInImageProject(this.search.tagIds , this.search.mode , true);
        } ,

        resetTagFilter () {
            this.search.tags = [];
            this.search.tagIds = [];
            this.newestInImageProjects(true);
        } ,

        unselectedTagByTagId (tagId) {
            const index = this.findIndexInSelectedTagsByTagId(tagId);
            if (index === false) {
                return ;
            }
            this._val('selected_tag_' + tagId , false);
            this.search.tags.splice(index , 1);
            this.search.tagIds.splice(index , 1);
            if (this.search.tags.length === 0) {
                this.newestInImageProjects(true);
                return ;
            }
            this.getWithPagerInImageProject(this.search.tagIds , this.search.mode , true);
        } ,

        filterModeChangeEvent (mode) {
            this.search.mode = mode;
            if (this.search.tags.length === 0) {
                return ;
            }
            this.getWithPagerInImageProject(this.search.tagIds , this.search.mode , true);
        } ,

        tagPageEvent (page , size) {
            this.allHotTags.page = page;
            this.allHotTags.size = size;
            this.hotTagsWithPager();
        } ,

        tagSizeEvent (size , page) {
            this.allHotTags.page = page;
            this.allHotTags.size = size;
            this.hotTagsWithPager();
        } ,

        // 显示标签选择器
        showTagSelector () {
            this.dom.tagSelector.removeClass('hide');
            this.dom.tagSelector.startTransition('show');
            this.allHotTags.page = 1;
            this.allHotTags = {...allHotTags};
            this.hotTagsWithPager();
        } ,

        closeTagSelector () {
            this.dom.tagSelector.endTransition('show' , () => {
                this.dom.tagSelector.addClass('hide');
            });
        } ,

        getImageProject () {
            this.pending('getImageProject' , true);
            Api.slideshow
                .imageProject()
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message);
                        return ;
                    }
                    this.imageSubject = res.data;
                })
                .finally(() => {
                    this.pending('getImageProject' , false);
                });
        } ,

        // 图片-最新图片
        newestInImageProjects (override = true) {
            if (override) {
                this.pending('switchImages' , true);
                this.images.page = 1;
            }
            this.pending('images' , true);
            this.curTag = 'newest';
            this.images.end = false;
            Api.imageProject
                .newestWithPager({
                    size: this.images.size ,
                    page:  this.images.page ,
                    type: this.images.type ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.pending('images' , false);
                        this.errorHandleAtHomeChildren(res.message);
                        return ;
                    }
                    const data = res.data;
                    this.images.page = data.current_page;
                    this.images.maxPage = data.last_page;
                    this.images.total = data.total;
                    this.images.end = this.images.page === this.images.maxPage;

                    if (override) {
                        this.images.data = data.data;
                    } else {
                        this.images.data = this.images.data.concat(data.data);
                    }
                    this.$nextTick(() => {
                        this.pending('images' , false);
                    });
                })
                .finally(() => {
                    this.pending('switchImages' , false);
                });
        } ,

        hotInImageProjects (override = true) {
            if (override) {
                this.pending('switchImages' , true);
                this.images.page = 1;
            }
            this.curTag = 'hot';
            this.pending('images' , true);
            this.images.end = false;
            Api.imageProject
                .hotWithPager({
                    size: this.images.size ,
                    page:  this.images.page ,
                    type: this.images.type ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.pending('images' , false);
                        this.errorHandleAtHomeChildren(res.message);
                        return ;
                    }
                    const data = res.data;
                    this.images.page = data.current_page;
                    this.images.maxPage = data.last_page;
                    this.images.total = data.total;
                    this.images.end = this.images.page === this.images.maxPage;
                    if (override) {
                        this.images.data = data.data;
                    } else {
                        this.images.data = this.images.data.concat(data.data);
                    }
                    this.$nextTick(() => {
                        this.pending('images' , false);
                    });
                })
                .finally(() => {
                    this.pending('switchImages' , false);
                });
        } ,

        // 图片-热门标签
        hotTags () {
            Api.imageProject
                .hotTags({
                    size: partHotTags.size ,
                    type: this.partHotTags.type ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message);
                        return ;
                    }
                    this.partHotTags.data = res.data;
                })
                .finally(() => {

                });
        } ,

        /**
         * 图片-按标签分类获取的图片
         * @param mode strict-严格的 |loose-宽松的
         * @param tagIds
         */
        getWithPagerInImageProject (tagIds , mode , override = true) {
            if (override) {
                this.pending('switchImages' , true);
                this.images.page = 1;
            }
            this.pending('images' , true);
            this.images.end = false;
            Api.imageProject
                .getWithPagerByTagIds({
                    mode ,
                    tag_ids: G.jsonEncode(tagIds) ,
                    size: this.images.size ,
                    page: this.images.page ,
                    type: this.images.type ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.pending('images' , false);
                        this.message('error' , res.message);
                        return ;
                    }
                    const data = res.data;
                    this.images.page = data.current_page;
                    this.images.maxPage = data.last_page;
                    this.images.total = data.total;
                    this.images.end = this.images.page === this.images.maxPage;
                    if (override) {
                        this.images.data = data.data;
                    } else {
                        this.images.data = this.images.data.concat(data.data);
                    }
                    this.$nextTick(() => {
                        this.pending('images' , false);
                    });
                })
                .finally(() => {
                    this.pending('switchImages' , false);
                });
        } ,

        /**
         * *********************
         * 图片标签
         * *********************
         */
        getWithPagerByTagIdInImageProject (tagId , override = true) {
            this.search = G.copy(search);
            this.curTag = 'tag_' + tagId;

            this.getWithPagerInImageProject([tagId] , 'strict' , override);
        } ,

        // 图片-按标签分类获取的图片
        hotTagsWithPager () {
            this.pending('hotTagsWithPager' , true);
            Api.imageProject
                .hotTagsWithPager({
                    size: this.allHotTags.size ,
                    page:  this.allHotTags.page ,
                    value: this.allHotTags.value ,
                    type: this.allHotTags.type ,
                })
                .then((res) => {
                    if (res.code !== TopContext.code.Success) {
                        this.errorHandleAtHomeChildren(res.message);
                        return ;
                    }
                    const data = res.data;
                    this.allHotTags.page = data.current_page;
                    this.allHotTags.total = data.total;
                    this.allHotTags.data = data.data;
                })
                .finally(() => {
                    this.pending('hotTagsWithPager' , false);
                });
        } ,

        initDom () {
            this.dom.win = G(window);
            this.dom.html = G(document.documentElement);
            this.dom.body = G(document.body);
            this.dom.tagSelector = G(this.$refs['tag-selector']);
            this.dom.tagSelectorInDocs = G(this.$refs['tags-selector-in-docs']);
            this.dom.tagSelectorInSlidebar = G(this.$refs['tags-selector-in-slidebar']);
        } ,

        initEvent () {
            this.dom.win.on('scroll' , this.winScrollEvent.bind(this));
            this.dom.win.on('scroll' , this.tagSelectorVisibleEvent.bind(this));
        } ,

        tagSelectorVisibleEvent (e) {
            const docY = this.dom.tagSelectorInDocs.getWindowOffsetVal('top');
            this._val('tagSelectorInSlidebar' , docY < 0);
            if (docY < 0) {
                this.dom.tagSelectorInSlidebar.removeClass('hide');
                this.dom.tagSelectorInSlidebar.startTransition('show');
            } else {
                this.dom.tagSelectorInSlidebar.endTransition('show' , () => {
                    this.dom.tagSelectorInSlidebar.addClass('hide');
                });
            }
        } ,

        winScrollEvent () {
            const y = window.pageYOffset;
            const scrollHeight = this.dom.html.scrollHeight();
            const clientHeight = this.dom.html.clientHeight();
            const extraY = TopContext.val.footerH;
            if (this.images.page >= this.images.maxPage) {
                return ;
            }
            if (clientHeight + y + extraY < scrollHeight) {
                return ;
            }
            if (this.pending('images')) {
                return ;
            }
            this.images.page++;
            switch (this.curTag)
            {
                case 'newest':
                    this.newestInImageProjects(false);
                    break;
                case 'hot':
                    this.hotInImageProjects(false);
                    break;
                case 'more':
                    this.getWithPagerInImageProject(this.search.tagIds , this.search.mode , false);
                    break;
                default:
                    let tagId = parseInt(this.curTag.replace('tag_' , ''));
                    this.getWithPagerByTagIdInImageProject(tagId , false);
                    break;
            }
        } ,
    } ,
}
