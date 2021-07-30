# 项目概述

`web` 端

# 技术栈

```text
vue + vue-router + vuex
```

# 环境依赖

- `nodejs v12.16.2+`
- `npm 6.14.4+`

# 项目部署

### 开发

```text
npm run server
或
npm run watch

推荐：

npm run server
```

### 编译

```text
npm run build
```

# 额外

### nginx 配置

```text
server {
    listen 80;
    index index.html;
    root /xinqu/admin;
    autoindex on;
    
    # 伪静态 - 路由开启了 history 模式则必须
    location / {
        try_files $uri $uri/ /index.html?$query_string;
    }
    
    # 接口代理 - 必须
    location /api {
        proxy_pass http://api.xq.test;
    }
    
    # 资源代理 - 必须
    location /resource {
        # 特别注意 要去掉 /resource 前缀
        # 所以末尾要加个 / ，或者使用 rewrite 也可以
        # rewrite ^/resource/(.*)$ /$1 break;
        # proxy_pass http://res.xq.test;
        # 重写末尾不用加 / ！！
        proxy_pass http://res.xq.test/;
    }
}
```
