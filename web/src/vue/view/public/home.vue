<template>
    <main class="main">

        <!-- 头部 -->
        <header class="header">

            <!-- 导航栏 -->
            <div class="navigation" :class="{fixed: val.fixed}">
                <nav class="nav nav-top">
                    <div class="inner">
                        <div class="c-inner">
                            <a class="logo" :href="genUrl('/')">
                                <div class="__logo__"><img :src="TopContext.res.logo" class="image"></div>
                                <div class="site"><a class="link">{{ TopContext.os.name }}</a></div>
<!--                                <div class="module"><a class="link" v-ripple :href="genUrl('/welcome')">{{ module().name }}</a></div>-->
                            </a>

                            <div class="search search-in-logged">
                                <div class="inner">
                                    <div class="ico"><i class="run-iconfont run-iconfont-search"></i></div>
                                    <div class="input"><input type="text" placeholder="搜索" class="form-input" ref="search" @focus="searchEvent"></div>
                                    <div class="type" :class="{show: val.navTypeList}"  @mouseenter="showNavTypeList" @mouseleave="hideNavTypeList">
                                        <div class="current">{{ val.mime.value }}<i class="iconfont run-iconfont run-iconfont-arrow"></i></div>
                                        <ul class="list hide" ref="nav-type-list" @click.stop>
                                            <li v-for="(v,k) in TopContext.business.contentType" :key="v" :ref="'mime-item-' + k" :data-mime="k" @click="switchSearchType(k,v)">{{ v }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="action">

                                <!-- 登录状态 -->
                                <div class="logged-layer" v-if="state().user">

                                    <div class="item history" @click.stop @mouseenter="showHistoryCtrl" @mouseleave="hideHistoryCtrl">
                                        <div class="action"><button v-ripple class="button history"><my-icon icon="lishijilu" mode="right" />记录</button></div>
                                        <div class="groups hide" ref="groups-for-history">

                                            <div class="inner">
                                                <div class="transparent-block"></div>
                                                <div class="loading" v-if="val.pending.getHistories">
                                                    <my-loading></my-loading>
                                                </div>

                                                <div class="empty" v-if="!val.pending.getHistories && histories.length <= 0">
                                                    暂无数据
                                                </div>

                                                <div class="group" v-for="v in histories">
                                                    <div class="title f-12">{{ v.name }}</div>
                                                    <div class="list">
                                                        <template v-for="v1 in v.data">

                                                            <a class="item image" v-if="v1.relation_type === 'image_project'" target="_blank" :href="`#/image_project/${v1.relation_id}/show`">
                                                                <div class="thumb"><img :data-src="v1.relation.thumb ? v1.relation.thumb : TopContext.res.notFound" v-judge-img-size class="image judge-img-size" alt=""></div>
                                                                <div class="info">
                                                                    <div class="name f-14">【{{ v1.__relation_type__ }}】{{ v1.relation.name ?  v1.relation.name : '' }}</div>
                                                                    <div class="time f-12">
                                                                        <my-icon icon="shijian" mode="right" />{{ v1.created_at }}&nbsp;&nbsp;{{ v1.relation.user ? v1.relation.user.nickname : 'unknow' }}
                                                                    </div>
                                                                </div>
                                                            </a>

                                                            <a class="item video-project" v-if="v1.relation_type === 'video_project'" target="_blank" :href="`#/video_project/${v1.relation_id}/show`">
                                                                <div class="thumb">
                                                                    <img
                                                                        :data-src="v1.relation.user_play_record.video.__thumb__ ? v1.relation.user_play_record.video.__thumb__ : TopContext.res.notFound"
                                                                        v-judge-img-size
                                                                        class="image judge-img-size"
                                                                        alt="">
                                                                    <div class="progress-bar" :style="`width: ${v1.relation.user_play_record.ratio * 100}%`"></div>
                                                                </div>
                                                                <div class="info">
                                                                    <div class="name f-14">【{{ v1.__relation_type__ }}】{{ v1.relation.name ?  v1.relation.name : '' }}</div>
                                                                    <div class="sub-name f-12">{{ v1.relation.user_play_record.video.name ? v1.relation.user_play_record.video.name : '' }}</div>
                                                                    <div class="time f-12">
                                                                        <my-icon icon="shijian" mode="right" />{{ v1.created_at }}&nbsp;&nbsp;{{ v1.relation.user ? v1.relation.user.nickname : 'unknow' }}
                                                                    </div>
                                                                </div>
                                                            </a>

                                                            <a class="item image" v-if="v1.relation_type === 'image'" target="_blank" :href="`#/image/${v1.relation_id}/show`">
                                                                <div class="thumb"><img :data-src="v1.relation.src ? v1.relation.src : TopContext.res.notFound" v-judge-img-size class="image judge-img-size" alt=""></div>
                                                                <div class="info">
                                                                    <div class="name f-14">【{{ v1.__relation_type__ }}】{{ v1.relation.name ?  v1.relation.name : '' }}</div>
                                                                    <div class="time f-12">
                                                                        <my-icon icon="shijian" mode="right" />{{ v1.created_at }}&nbsp;&nbsp;{{ v1.relation.user ? v1.relation.user.nickname : 'unknow' }}
                                                                    </div>
                                                                </div>
                                                            </a>

                                                            <a class="item video" v-if="v1.relation_type === 'video'" target="_blank" :href="`#/video/${v1.relation_id}/show`">
                                                                <div class="thumb">
                                                                    <img
                                                                            :data-src="v1.relation.user_play_record.video.__thumb__ ? v1.relation.user_play_record.video.__thumb__ : TopContext.res.notFound"
                                                                            v-judge-img-size
                                                                            class="image judge-img-size"
                                                                            alt="">
                                                                    <div class="progress-bar" :style="`width: ${v1.relation.user_play_record.ratio * 100}%`"></div>
                                                                </div>
                                                                <div class="info">
                                                                    <div class="name f-14">【{{ v1.__relation_type__ }}】{{ v1.relation.name ?  v1.relation.name : '' }}</div>
                                                                    <div class="time f-12">
                                                                        <my-icon icon="shijian" mode="right" />{{ v1.created_at }}&nbsp;&nbsp;{{ v1.relation.user ? v1.relation.user.nickname : 'unknow' }}
                                                                    </div>
                                                                </div>
                                                            </a>

                                                        </template>
                                                    </div>
                                                </div>

                                                <div class="load-more" v-if="histories.length > 0">
                                                    <my-link class="more" href="#/user/history" @click="hideHistoryCtrl">加载更多</my-link>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="item favorites" @click.stop @mouseenter="showFavoritesCtrl" @mouseleave="hideFavoritesCtrl">
                                        <div class="action"><button v-ripple class="button favorites"><my-icon icon="shoucang6" mode="right" />收藏</button></div>
                                        <div class="collection hide" ref="collection">
                                            <div class="transparent-block"></div>
                                            <div class="nav">
                                                <div class="item" v-for="v in favorites.collectionGroups" :key="v.id" :class="{cur: v.id === favorites.collection_group.id}" v-ripple @click="favorites.collection_group = v">
                                                    <div class="name">{{ v.name }}</div>
                                                    <div class="number">{{ v.count }}</div>
                                                </div>
                                            </div>
                                            <div class="list">
                                                <template v-for="v in favorites.collection_group.collections">

                                                    <a v-if="v.relation_type === 'image_project'" class="item image-project" target="_blank" :href="`#/image_project/${v.relation_id}/show`">
                                                        <div class="thumb"><img :data-src="v.relation.thumb ? v.relation.thumb : TopContext.res.notFound" v-judge-img-size class="image judge-img-size" alt=""></div>
                                                        <div class="info">
                                                            <div class="name f-14">【{{ v.__relation_type__}}】{{ v.relation.name }}</div>
                                                            <div class="time f-12">
                                                                <my-icon icon="shijian" mode="right" />{{ v.created_at }} {{ v.relation.user.nickname }}
                                                            </div>
                                                        </div>
                                                    </a>

                                                    <a v-if="v.relation_type === 'video_project'" class="item video-project" target="_blank" :href="`#/video_project/${v.relation_id}/show`">
                                                        <div class="thumb">
                                                            <img
                                                                    :data-src="v.relation.user_play_record.video.__thumb__ ? v.relation.user_play_record.video.__thumb__ : TopContext.res.notFound"
                                                                    v-judge-img-size
                                                                    class="image judge-img-size"
                                                                    alt="">
                                                            <div class="progress-bar" :style="`width: ${v.relation.user_play_record.ratio * 100}%`"></div>
                                                        </div>
                                                        <div class="info">
                                                            <div class="name f-14">【{{ v.__relation_type__}}】{{ v.relation.name }}</div>
                                                            <div class="time f-12">
                                                                <my-icon icon="shijian" mode="right" />{{ v.created_at }} {{ v.relation.user.nickname }}
                                                            </div>
                                                        </div>
                                                    </a>

                                                    <a v-if="v.relation_type === 'video'" class="item video" target="_blank" :href="`#/video/${v.relation_id}/show`">
                                                        <div class="thumb">
                                                            <img
                                                                    :data-src="v.relation.__thumb__ ? v.relation.__thumb__ : TopContext.res.notFound"
                                                                    v-judge-img-size
                                                                    class="image judge-img-size"
                                                                    alt="">
                                                            <div class="progress-bar" :style="`width: ${v.relation.user_play_record.ratio * 100}%`"></div>
                                                        </div>
                                                        <div class="info">
                                                            <div class="name f-14">【{{ v.__relation_type__}}】{{ v.relation.name }}</div>
                                                            <div class="time f-12">
                                                                <my-icon icon="shijian" mode="right" />{{ v.created_at }} {{ v.relation.user.nickname }}
                                                            </div>
                                                        </div>
                                                    </a>

                                                    <a v-if="v.relation_type === 'image'" class="item image-project" target="_blank" :href="`#/image/${v.relation_id}/show`">
                                                        <div class="thumb"><img :data-src="v.relation.src ? v.relation.src : TopContext.res.notFound" v-judge-img-size class="image judge-img-size" alt=""></div>
                                                        <div class="info">
                                                            <div class="name f-14">【{{ v.__relation_type__}}】{{ v.relation.name }}</div>
                                                            <div class="time f-12">
                                                                <my-icon icon="shijian" mode="right" />{{ v.created_at }} {{ v.relation.user.nickname }}
                                                            </div>
                                                        </div>
                                                    </a>
                                                </template>

                                                <div class="empty" v-if="favorites.collection_group.collections.length === 0">
                                                    <my-icon icon="empty" size="40"></my-icon>
                                                </div>

                                                <div class="loaded" v-if="favorites.collection_group.count !== 0 && favorites.collection_group.count === favorites.collection_group.collections.length"><span>到底了</span></div>

                                                <div class="load-more" v-if="!val.pending.getCollectionGroupWithCollection && favorites.collection_group.count > 0 && favorites.collection_group.count !== favorites.collection_group.collections.length">
                                                    <my-link class="more" href="#/user/favorites" @click="hideFavoritesCtrl">加载更多</my-link>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="item user" @click.stop @mouseenter="showUserCtrl" @mouseleave="hideUserCtrl">
                                        <div class="action">
                                            <a class="link"
                                               :href="genUrl('/user')"
                                               v-ripple>
                                                <img :data-src="state().user.avatar ? state().user.avatar : TopContext.res.avatar" v-judge-img-size class="image judge-img-size">
                                            </a>
                                        </div>
                                        <div class="info hide" ref="info-for-user">
                                            <div class="transparent-block"></div>
                                            <div class="user m-b-10">
                                                <a :href="genUrl('/user/info')" v-ripple target="_self" class="link" @click="hideUserCtrl">
                                                    <div class="avatar">
                                                        <div class="mask">
                                                            <img :data-src="state().user.avatar ? state().user.avatar : TopContext.res.avatar" v-judge-img-size class="image judge-img-size">
                                                        </div>
                                                    </div>
                                                    <div class="info">
                                                        <div class="name">{{ getUsername(state().user.username , state().user.nickname) }}</div>
                                                        <div class="desc">{{ state().user.description }}</div>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="actions">
                                                <a class="action" v-ripple :href="genUrl(`/channel/${state().user.id}`)" @click="hideUserCtrl">
                                                    <div class="ico"><my-icon icon="ronghepindao" size="16" /></div>
                                                    <div class="name">我的频道</div>
                                                </a>

                                                <a class="action" v-ripple :href="genUrl('/user/password')" @click="hideUserCtrl">
                                                    <div class="ico"><my-icon icon="privilege" size="16" /></div>
                                                    <div class="name">修改密码</div>
                                                </a>

                                                <a class="action" v-ripple :href="genUrl('/user/history')" @click="hideUserCtrl">
                                                    <div class="ico"><my-icon icon="lishijilu" size="16" /></div>
                                                    <div class="name">历史记录</div>
                                                </a>

                                                <a class="action" v-ripple :href="genUrl('/user/favorites')" @click="hideUserCtrl">
                                                    <div class="ico"><my-icon icon="shoucang6" size="16" /></div>
                                                    <div class="name">我的收藏</div>
                                                </a>

                                                <a class="action" v-ripple :href="genUrl('/user/praise')" @click="hideUserCtrl">
                                                    <div class="ico"><my-icon icon="shoucang2" size="16" /></div>
                                                    <div class="name">我的点赞</div>
                                                </a>

                                                <a class="action" v-ripple @click.prevent="logout">
                                                    <div class="ico"><my-icon icon="084tuichu" size="16" /></div>
                                                    <div class="name">退出登录</div>
                                                </a>
                                            </div>
                                        </div>

                                    </div>

                                </div>

                                <!-- 未登录状态 -->
                                <div class="login-layer" v-else>
                                    <my-button class="login" @click="showUserForm('login')">登录</my-button>
                                    <my-button class="register" @click="showUserForm('register')">注册</my-button>
                                </div>

                            </div>
                        </div>
                    </div>
                </nav>
                <nav class="nav nav-btm">
                    <div class="inner">
                        <div class="item left menu" ref="nav-menu">
                            <my-nav-menu :data="positions"></my-nav-menu>
                        </div>
                        <div class="item right actions">
                            <div class="module"><a class="link" v-ripple :href="genUrl('/welcome')" title="点击选择模块">{{ module().name }}</a></div>
                            <div class="module-switch" @mouseenter="showListForModuleSwitch" @mouseleave="hideListForModuleSwitch">
                                <div class="action"><my-icon icon="mokuai"></my-icon></div>
                                <div class="list hide" ref="list-for-module-switch">
                                    <div class="item" v-for="v in modules" :key="v.id" @click="switchModule(v)">{{ v.name }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>

        </header>

        <!-- 公告 -->
        <section class="announcement"></section>

        <!-- 内容 -->
        <section class="body" ref="body">
            <keep-alive>
                <router-view></router-view>
            </keep-alive>
        </section>

        <!-- 尾部 -->
        <footer class="footer">
            <div class="inner">
                <p class="website">兴趣部落</p>
                <p class="links">
                    友情链接：
                    <a class="m-r-5" target="_blank" href="https://awm.moe/">ACG萌图</a>
                    <a class="m-r-5" target="_blank" href="https://hanime.tv/">hanime</a>
                    <a class="m-r-5" target="_blank" href="https://95mm.net/">mm</a>
                </p>
                <p class="copyright">{{ TopContext.os.name }} 版权所有</p>
            </div>
        </footer>

        <!-- 登录/注册 -->
        <div class="user-form hide" ref="userForm" @click="hideUserForm">

            <!-- 登录 -->
            <div class="login user-form-item" v-if="val.userFormType === 'login'" @click.stop>
                <div class="logo">
                    <div class="outer">
                        <img :src="TopContext.res.logo" class="image" alt="">
                    </div>
                </div>
                <div class="title">用户登录</div>
                <div class="message" :class="val.loginMessage.class">{{ val.loginMessage.text }}</div>
                <div class="form">
                    <form class="form-inner" @submit.prevent>
                        <div class="line" :class="{error: isValid(val.loginError.username) , focus: val.focus.usernameForLogin}" ref="line-username">
                            <div class="top">
                                <div class="left"><input type="text" class="form-input" v-model="loginForm.username" @input="val.loginError.username = ''" data-name="usernameForLogin" placeholder="用户名/邮箱/手机号" @focus="focusEvent" @blur="blurEvent"></div>
                                <div class="right"><my-icon icon="yonghu2" size="24"></my-icon></div>
                            </div>
                            <div class="btm">
                                <template v-if="!isValid(val.loginError.username)"></template>
                                <template v-else>{{ val.loginError.username }}</template>
                            </div>
                        </div>

                        <div class="line password" :class="{error: isValid(val.loginError.password) , focus: val.focus.passwordForLogin}" ref="line-password">
                            <div class="top">
                                <div class="left">
                                    <input :type="val.showPasswordForLogin ? 'text' : 'password'" class="form-input" ref="input-password" v-model="loginForm.password" @input="val.loginError.password = ''" placeholder="密码" data-name="passwordForLogin" @focus="focusEvent" @blur="blurEvent">
                                    <div class="preview-password">
                                        <div class="outer" @click="val.showPasswordForLogin = !val.showPasswordForLogin">
                                            <my-icon icon="xianshi" size="18" v-if="val.showPasswordForLogin"></my-icon>
                                            <my-icon icon="yincang1" size="18" v-else></my-icon>
                                        </div>
                                    </div>
                                </div>
                                <div class="right"><my-icon size="24" icon="mima"></my-icon></div>
                            </div>
                            <div class="btm">
                                <template v-if="!isValid(val.loginError.password)"></template>
                                <template v-else>{{ val.loginError.password }}</template>
                            </div>
                        </div>

                        <div class="line operation submit">
                            <my-button type="submit" class="button" v-if="!val.pending.userLogin" @click="userLogin">登录</my-button>
                            <button class="button disabled" v-else>
                                <my-loading></my-loading>
                            </button>
                        </div>

                        <div class="line operation forget">
                            <my-button class="button" @click="showUserForm('forget')">不能登录？</my-button>
                        </div>

                        <div class="line operation register">
                            <div class="title m-b-20">没有账号？现在注册</div>
                            <div class="action">
                                <my-button class="button" @click="switchUserForm('register')">注册</my-button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

            <!-- 忘记密码 -->
            <div class="forget user-form-item" v-if="val.userFormType === 'forget'" @click.stop>
                <div class="logo">
                    <div class="outer">
                        <img src="./res/logo.png" class="image" alt="">
                    </div>
                </div>
                <div class="title">忘记密码</div>
                <div class="message" :class="val.forgetMessage.class">{{ val.forgetMessage.text }}</div>
                <div class="form">
                    <form class="form-inner" @submit.prevent>
                        <div class="line" :class="{error: isValid(val.forgetError.email) , focus: val.focus.emailForForget , 'hide-ico': val.timer.password > 0 || val.pending.sendEmailCodeForPassword}" ref="line-username">
                            <div class="top">
                                <div class="left">
                                    <input type="text" class="form-input" v-model="forgetForm.email" @input="val.forgetError.email = ''" data-name="emailForForget" placeholder="请输入邮箱" @focus="focusEvent" @blur="blurEvent">
                                    <button type="button" v-ripple class="button-in-line" v-if="val.step.password === 'password'" :class="{disabled: val.timer.password > 0}" @click="sendEmailCodeForPassword">
                                        <my-loading size="14" v-if="val.pending.sendEmailCodeForPassword"></my-loading>
                                        发送验证码<span v-if="val.timer.password > 0">（{{ val.timer.password }}）</span>
                                    </button>
                                </div>
                                <div class="right"><my-icon icon="youxiang" size="24"></my-icon></div>
                            </div>
                            <div class="btm">
                                <template v-if="!isValid(val.forgetError.email)"></template>
                                <template v-else>{{ val.forgetError.email }}</template>
                            </div>
                        </div>

                        <template v-if="val.step.password === 'password'">
                            <div class="line password" :class="{error: isValid(val.forgetError.password) , focus: val.focus.passwordForForget}" ref="line-password">
                                <div class="top">
                                    <div class="left">
                                        <input :type="val.showPasswordForForget ? 'text' : 'password'" class="form-input" ref="input-password" v-model="forgetForm.password" @input="val.forgetError.password = ''" placeholder="新密码" data-name="passwordForForget" @focus="focusEvent" @blur="blurEvent">
                                        <div class="preview-password">
                                            <div class="outer" @click="val.showPasswordForForget = !val.showPasswordForForget">
                                                <my-icon icon="xianshi" size="18" v-if="val.showPasswordForForget"></my-icon>
                                                <my-icon icon="yincang1" size="18" v-else></my-icon>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="right">
                                        <my-icon icon="mima" size="24"></my-icon>
                                    </div>
                                </div>
                                <div class="btm">
                                    <template v-if="!isValid(val.forgetError.password)"></template>
                                    <template v-else>{{ val.forgetError.password }}</template>
                                </div>
                            </div>

                            <div class="line password" :class="{error: isValid(val.forgetError.confirm_password) , focus: val.focus.confirmPasswordForForget}" ref="line-password">
                                <div class="top">
                                    <div class="left">
                                        <input :type="val.showConfirmPasswordForForget ? 'text' : 'password'" class="form-input" ref="input-password" v-model="forgetForm.confirm_password" @input="val.forgetError.confirm_password = ''" placeholder="确认新密码" data-name="confirmPasswordForForget" @focus="focusEvent" @blur="blurEvent">
                                        <div class="preview-password">
                                            <div class="outer" @click="val.showConfirmPasswordForForget = !val.showConfirmPasswordForForget">
                                                <my-icon icon="xianshi" size="18" v-if="val.showConfirmPasswordForForget"></my-icon>
                                                <my-icon icon="yincang1" size="18" v-else></my-icon>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="right">
                                        <my-icon icon="mima" size="24"></my-icon>
                                    </div>
                                </div>
                                <div class="btm">
                                    <template v-if="!isValid(val.forgetError.confirm_password)"></template>
                                    <template v-else>{{ val.forgetError.confirm_password }}</template>
                                </div>
                            </div>

                            <div class="line email-code" :class="{error: isValid(val.forgetError.email_code) , focus: val.focus.emailCodeForForget}" ref="line-email-code">
                                <div class="top">
                                    <div class="left"><input type="text" class="form-input" v-model="forgetForm.email_code" @input="val.forgetError.email_code = ''" placeholder="邮箱验证码" data-name="emailCodeForForget" @focus="focusEvent" @blur="blurEvent"></div>
                                    <div class="right"><my-icon icon="yanzhengma1" size="24"></my-icon></div>
                                </div>
                                <div class="btm">
                                    <template v-if="!isValid(val.forgetError.email_code)"></template>
                                    <template v-else>{{ val.forgetError.email_code }}</template>
                                </div>
                            </div>

                        </template>

                        <div class="line operation submit">
                            <my-button type="submit" class="button" :class="{disabled: val.timer.password > 0}" v-if="val.step.password === 'email' && !val.pending.sendEmailCodeForPassword" @click="sendEmailCodeForPassword">发送验证码 <span v-if="val.timer.password > 0">（{{ val.timer.password }}）</span></my-button>
                            <my-button type="submit" class="button" v-if="val.step.password === 'password' && !val.pending.updateUserPassword" @click="updateUserPassword">修改</my-button>
                            <button class="button disabled" v-if="val.pending.updateUserPassword || (val.pending.sendEmailCodeForPassword && val.step.password === 'email')">
                                <my-loading></my-loading>
                            </button>
                        </div>

                        <div class="line operation register">
                            <div class="title m-b-20">已经有账号？</div>
                            <div class="action">
                                <my-button class="button" @click="switchUserForm('login')">登录</my-button>
                            </div>
                        </div>

                        <div class="line operation register">
                            <div class="title m-b-20">还没有账号？</div>
                            <div class="action">
                                <my-button class="button" @click="switchUserForm('register')">注册</my-button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

            <!-- 注册 -->
            <div class="register user-form-item" v-if="val.userFormType === 'register'" @click.stop>
                <div class="logo">
                    <div class="outer">
                        <img :src="TopContext.res.logo" class="image" alt="">
                    </div>
                </div>
                <div class="title">用户注册</div>
                <div class="message" :class="val.registerMessage.class">{{ val.registerMessage.text }}</div>
                <div class="form">
                    <form class="form-inner" @submit.prevent>

                        <div class="line" :class="{error: isValid(val.registerError.email) , focus: val.focus.emailForRegister , 'hide-ico': val.timer.register > 0 || val.pending.sendEmailCodeForRegister}" ref="line-username">
                            <div class="top">
                                <div class="left">
                                    <input type="text" class="form-input" v-model="registerForm.email" @input="val.registerError.email = ''" data-name="emailForRegister" placeholder="请输入邮箱" @focus="focusEvent" @blur="blurEvent">
                                    <button type="button" v-ripple class="button-in-line" :class="{disabled: val.timer.register > 0}" @click="sendEmailCodeForRegister">
                                        <my-loading size="14" v-if="val.pending.sendEmailCodeForRegister"></my-loading>
                                        发送验证码<span v-if="val.timer.register > 0">（{{ val.timer.register }}）</span>
                                    </button>
                                </div>
                                <div class="right"><my-icon icon="youxiang" size="24"></my-icon></div>
                            </div>
                            <div class="btm">
                                <template v-if="!isValid(val.registerError.email)"></template>
                                <template v-else>{{ val.registerError.email }}</template>
                            </div>
                        </div>

                        <div class="line password" :class="{error: isValid(val.registerError.password) , focus: val.focus.passwordForRegister}" ref="line-password">
                            <div class="top">
                                <div class="left">
                                    <input :type="val.showPasswordForRegister ? 'text' : 'password'" class="form-input" ref="input-password" v-model="registerForm.password" @input="val.registerError.password = ''" placeholder="密码" data-name="passwordForRegister" @focus="focusEvent" @blur="blurEvent">
                                    <div class="preview-password">
                                        <div class="outer" @click="val.showPasswordForRegister = !val.showPasswordForRegister">
                                            <my-icon icon="xianshi" size="18" v-if="val.showPasswordForRegister"></my-icon>
                                            <my-icon icon="yincang1" size="18" v-else></my-icon>
                                        </div>
                                    </div>
                                </div>
                                <div class="right">
                                    <my-icon icon="mima" size="24"></my-icon>
                                </div>
                            </div>
                            <div class="btm">
                                <template v-if="!isValid(val.registerError.password)"></template>
                                <template v-else>{{ val.registerError.password }}</template>
                            </div>
                        </div>

                        <div class="line password" :class="{error: isValid(val.registerError.confirm_password) , focus: val.focus.confirmPasswordForRegister}" ref="line-password">
                            <div class="top">
                                <div class="left">
                                    <input :type="val.showConfirmPasswordForRegister ? 'text' : 'password'" class="form-input" ref="input-password" v-model="registerForm.confirm_password" @input="val.registerError.confirm_password = ''" placeholder="确认密码" data-name="confirmPasswordForRegister" @focus="focusEvent" @blur="blurEvent">
                                    <div class="preview-password">
                                        <div class="outer" @click="val.showConfirmPasswordForRegister = !val.showConfirmPasswordForRegister">
                                            <my-icon icon="xianshi" size="18" v-if="val.showConfirmPasswordForRegister"></my-icon>
                                            <my-icon icon="yincang1" size="18" v-else></my-icon>
                                        </div>
                                    </div>
                                </div>
                                <div class="right">
                                    <my-icon icon="mima" size="24"></my-icon>
                                </div>
                            </div>
                            <div class="btm">
                                <template v-if="!isValid(val.registerError.confirm_password)"></template>
                                <template v-else>{{ val.registerError.confirm_password }}</template>
                            </div>
                        </div>


                        <div class="line email-code" :class="{error: isValid(val.registerError.email_code) , focus: val.focus.emailCodeForRegister}" ref="line-email-code">
                            <div class="top">
                                <div class="left"><input type="text" class="form-input" v-model="registerForm.email_code" @input="val.registerError.email_code = ''" placeholder="邮箱验证码" data-name="emailCodeForRegister" @focus="focusEvent" @blur="blurEvent"></div>
                                <div class="right"><my-icon icon="yanzhengma1" size="24"></my-icon></div>
                            </div>
                            <div class="btm">
                                <template v-if="!isValid(val.registerError.email_code)"></template>
                                <template v-else>{{ val.registerError.email_code }}</template>
                            </div>
                        </div>

<!--                        <div class="line captcha" :class="{error: isValid(val.registerError.captcha_code) , focus: val.focus.captchaCodeForRegister}" ref="line-captcha">-->
<!--                            <div class="top">-->
<!--                                <div class="left"><input type="text" class="form-input"  v-model="registerForm.captcha_code" @input="val.registerError.captcha_code = ''" data-name="captchaCodeForRegister" placeholder="图形验证码" @focus="focusEvent" @blur="blurEvent"></div>-->
<!--                                <div class="right">-->
<!--                                    <img :src="val.captchaForRegister.img" class="image" @click="captchaForRegister">-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="btm">-->
<!--                                <template v-if="!isValid(val.registerError.captcha_code)"></template>-->
<!--                                <template v-else>{{ val.registerError.captcha_code }}</template>-->
<!--                            </div>-->
<!--                        </div>-->

                        <div class="line operation submit">
                            <my-button type="submit" class="button" v-if="!val.pending.userRegister" @click="userRegister">注册</my-button>
                            <button class="button disabled" v-else>
                                <my-loading></my-loading>
                            </button>
                        </div>

                        <div class="line operation register">
                            <div class="title m-b-20">已经有账号？</div>
                            <div class="action">
                                <my-button class="button" @click="switchUserForm('login')">登录</my-button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>


        </div>

        <!-- 滚动到顶部 -->
        <div class="to-top" ref="to-top"><my-icon icon="jiantou" size="20" /></div>


    </main>
</template>

<script src="./js/home.js"></script>
<style scoped src="./css/home.css"></style>
