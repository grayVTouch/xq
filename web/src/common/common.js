window.request = function(url , method , data = {}) {
    const token = G.s.get('token');
    const module = G.s.json('module');
    const moduleId = module ? module.id : 0;
    if (G.type(data) === 'FormData') {
        data.append('module_id' , moduleId);
    } else {
        data = {...data , ...{module_id: moduleId}};
    }
    return G.ajax({
        url ,
        method ,
        header: {
            Authorization: token ,
        } ,
        data ,
        success (data , code) {
            if (code === 401) {
                // 登录认证失败
                G.s.del('token');
                // G.s.del('user');
                // window.history.go(0);
            }
            if (G.isFunction(success)) {
                success(data.message , data.data , code);
            }
        } ,
        // error ,
        netError () {
            // console.log();
        } ,
    });
};

window.genUrl = function(path){
    const api = TopContext.api.replace(/\/$/ , '');
    path = path.replace(/\/$/ , '');
    return TopContext.api + '/' + path;
};
