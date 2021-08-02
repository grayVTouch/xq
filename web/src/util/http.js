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
    return true;
};

window.Http = {
    // 请求
    ajax (url , method , query = {} , data = {}) {
        method = method.toLowerCase();
        const module = G.s.json('module');
        const moduleId = module ? module.id : '';
        if (method === 'get') {
            query.module_id = moduleId;
        } else {
            if (G.type(data) === 'FormData') {
                data.append('module_id' , moduleId);
            } else {
                data.module_id = moduleId;
            }
        }
        return new Promise((resolve , reject) => {
            const token = G.s.get('token');
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
