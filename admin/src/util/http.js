/**
 * 请求拦截器
 */
G.ajax.created = function(){
    // 这边可以做一些什么
    return true;
};

/**
 * 响应拦截器
 */
G.ajax.responded = function(data , code){
    if (code === TopContext.code.AuthFailed) {
        // 登录认证失败
        G.cookie.del('token');
        window.history.go(0);
        return false;
    }
    return true;
};

window.Http = {
    // 请求
    ajax (url , method , query , data) {
        return new Promise((resolve , reject) => {
            const token = G.cookie.get('token');
            return G.ajax({
                url ,
                method ,
                header: {
                    // 授权码
                    Authorization: token ? token : '' ,
                    // 接收的服务端响应的数据类型
                    Accept: 'application/json' ,
                } ,
                query ,
                data ,
                success (data , code) {
                    data.code = code;
                    resolve(data);
                } ,
                error (data) {
                    reject(data);
                } ,
                timeout (data) {
                    reject(data);
                } ,
            });
        });
    } ,

    get (url , query) {
        return this.ajax(url , 'get' , query);
    } ,

    post (url , query , data) {
        return this.ajax(url , 'post' , query , data);
    } ,

    patch (url , query , data) {
        return this.ajax(url , 'patch' , query , data);
    } ,

    put (url , query , data) {
        return this.ajax(url , 'put' , query , data);
    } ,

    delete (url , query , data) {
        return this.ajax(url , 'delete' , query , data);
    } ,

    options (url , query , data) {
        return this.ajax(url , 'options' , query , data);
    } ,
};
