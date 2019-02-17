<?php

//laravel-admin 的配置文件

return [

    /*
     * 站点标题
    */
    'name' => 'Laravel Shop',

    /*
     * 页面顶部Logo
    */
    'logo' => '<b>Laravel</b> admin',

    /*
     * 页面顶部小Logo
    */
    'logo-mini' => '<b>La</b>',

    /*
     * 路由配置
    */
    'route' => [
        //路由前缀
        'prefix' => 'admin',

        //控制器命名空间前缀
        'namespace' => 'App\\Admin\\Controllers',

        //默认中间件列表
        'middleware' => ['web', 'admin'],
    ],

    /*
     * Laravel-Admin 的安装目录
    */
    'directory' => app_path('Admin'),

    /*
     * Laravel-Admin 页面标题
    */
    'title' => 'Laravel Shop 管理后台',

    /*
     * 是否使用 https
    */
    'https' => env('ADMIN_HTTPS', false),

    /*
     * Laravel-Admin 用户认证设置
    */
    'auth' => [

        'controller' => App\Admin\Controllers\AuthController::class,

        'guards' => [
            'admin' => [
                'driver'   => 'session',
                'provider' => 'admin',
            ],
        ],

        'providers' => [
            'admin' => [
                'driver' => 'eloquent',
                'model'  => Encore\Admin\Auth\Database\Administrator::class,
            ],
        ],
    ],

    /*
     * Laravel-Admin 文件上传设置
    */
    'upload' => [

        // Disk in `config/filesystem.php`.对应 filesystem.php 中的 disks
        'disk' => 'public',

        // Image and file upload path under the disk above.
        'directory' => [
            'image' => 'images',
            'file'  => 'files',
        ],
    ],

    /*
     * Laravel-Admin 数据库设置
    */
    'database' => [

        // 数据库连接名称，留空即可.
        'connection' => '',

        // User tables and model. 管理员用户表及模型
        'users_table' => 'admin_users',
        'users_model' => Encore\Admin\Auth\Database\Administrator::class,

        // Role table and model. 角色表及模型
        'roles_table' => 'admin_roles',
        'roles_model' => Encore\Admin\Auth\Database\Role::class,

        // Permission table and model. 权限表及模型
        'permissions_table' => 'admin_permissions',
        'permissions_model' => Encore\Admin\Auth\Database\Permission::class,

        // Menu table and model. 菜单表及模型
        'menu_table' => 'admin_menu',
        'menu_model' => Encore\Admin\Auth\Database\Menu::class,

        // Pivot table for table above. 多对多关联中间表
        'operation_log_table'    => 'admin_operation_log',
        'user_permissions_table' => 'admin_user_permissions',
        'role_users_table'       => 'admin_role_users',
        'role_permissions_table' => 'admin_role_permissions',
        'role_menu_table'        => 'admin_role_menu',
    ],

    /*
     * Laravel-Admin 操作日志设置
    */
    'operation_log' => [

        'enable' => true,

        /*
         * Only logging allowed methods in th
         */
        //'allowed_methods' => ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'TRACE', 'PATCH'],

        /*
         * 不记操作日志的路由
         */
        'except' => [
            'admin/auth/logs*',
        ],
    ],

    /*
    
     * 地图组件提供商

    */
    'map_provider' => 'google',

    /*
     * 页面风格
    */
    'skin' => 'skin-blue-light',

    /*
    |--------------------------------------------------------------------------
    | Application layout
    |--------------------------------------------------------------------------
    |
    | This value is the layout of admin pages.
    | @see https://adminlte.io/docs/2.4/layout
    |
    | Supported: "fixed", "layout-boxed", "layout-top-nav", "sidebar-collapse",
    | "sidebar-mini".
    |
    */
    'layout' => ['sidebar-mini', 'sidebar-collapse'],

    /*
     * 登录页背景图
    */
    'login_background_image' => '',

    /*
    * 显示版本
    */
    'show_version' => true,

    /*
    * 显示环境
    */
    'show_environment' => true,

    /*
    * 菜单绑定权限
    */
    'menu_bind_permission' => true,

    /*
    * 默认启用面包屑
    */
    'enable_default_breadcrumb' => true,

    /*
    * 扩展所在的目录.
    */
    'extension_dir' => app_path('Admin/Extensions'),

    /*
    * 扩展设置.
    */
    'extensions' => [

    ],
];