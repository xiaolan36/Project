<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 模板设置
// +----------------------------------------------------------------------

return [
    // 模板引擎类型 支持 php think 支持扩展
    'type'         => 'Think',
    // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写 3 保持操作方法
    'auto_rule'    => 1,
    // 模板路径
    'view_path'    => '',
    // 模板后缀
    'view_suffix'  => 'html',
    // 模板文件名分隔符
    'view_depr'    => DIRECTORY_SEPARATOR,
    // 模板引擎普通标签开始标记
    'tpl_begin'    => '{',
    // 模板引擎普通标签结束标记
    'tpl_end'      => '}',
    // 标签库标签开始标记
    'taglib_begin' => '{',
    // 标签库标签结束标记
    'taglib_end'   => '}',
    //模板输出替换
    'tpl_replace_string'  =>  [
        //后台路劲
        '__PUBLIC__'=>'/static',
        '__ADMINPUBLIC__'=>'/static/admin',
        '__ADMINIMG__'=>'/uploads',

        //前台路劲
        '__WCSS__'       => '/static/home/wap/css',
        '__WJS__'        =>  '/static/home/wap/js',
        '__WIMG__'       =>  '/static/home/wap/images',
        '__WAP__'        =>  '/static/home/wap',
        '__COM__'        =>  '/static/home/common',
        '__MUSIC__'		 =>  '/static/music',
        //上传头像
        '__IMGHEAD__'        =>  '/static/home/wap/heads',
        //头像上传JD
        '__IMGJS__'        =>  '/static/home/wap/jsa',
        '__LAYERUI__'       =>  '/static/home/wap/layui',

    ]
];
