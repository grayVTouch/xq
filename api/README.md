# 项目概述

`api` 接口项目，包含：

- 后台接口
- `web` 端接口

# 技术栈

 - `Laravel v7.30.4` 版本

# 环境依赖
- `php 7.4+`，低于 `8.0` 版本，正式部署建议开启 `opcache`，能大幅度提升性能
    - `phpredis` 扩展
- [ffmpeg](http://ffmpeg.org/)，下载 `gpl` 版本，具体版本介绍请看 `github` 仓库
- `java 1.8+（或 java 8 +）`
- `redis 3.0+`

# 资源保存

当前项目的资源保存支持任意位置保存！

- `windows` 系统，您可以选择 `c`盘、`d`盘等任意磁盘；
- `linux` 系统，您可以选择任意目录

# 项目部署

### 数据库迁移

```text
// 安装依赖
composer install
// 生成配置文件
cp .env.example .env
# 清理并缓存配置文件
php artisan config:cache
# 数据表迁移和数据填充
php artisan migrate:refresh --seed
```

填充后会自动创建一个后台管理员，默认账号密码：

```text
username: admin
password: admin
```

### 开启队列

```text
php artisan queue:work --tries=1
```

建议设置成开机自启就自动启动。

###### windows 设置方式

1. 控制面板 -> 管理工具 -> 任务计划程序
2. 添加任务，选择开机自启，并且选择执行用户为 `SYSTEM`

添加成功后，可以右键 运行该任务进行测试。

### 添加资源定时清理任务

```text
php artisan resource:clear
```

###### windows 设置方式

1. 控制面板 -> 管理工具 -> 任务计划程序
2. 添加任务，选择每天凌晨 3点半运行

添加成功后，可以右键 运行该任务进行测试。


# 额外

### nginx 伪静态

```text
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```
