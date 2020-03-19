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
Route ::get ('/' , 'Index/index/index');//前台主页
Route ::rule ('Index/Login/login' , 'Index/Login/login' , 'GET|POST');//前台登录
Route ::rule ('index/login/register' , 'Index/Login/register' , 'GET|POST') -> ext ('html');//前台注册
Route ::rule ('Login/register/[:id]' , 'Index/Login/register') -> ext ('html');//前台注册
Route ::post ('Index/Login/send_sms' , 'Index/Login/send_sms');//前台短信
Route ::rule ('Index/Login/getpsw' , 'Index/Login/getpsw' , 'GET|POST');//前台忘记密码

Route ::get ('admin_hui' , 'admin/login/index');//后台主页
Route ::get ('user/index/address_add/:id' , 'Index/User/address_add');
Route ::get ('user/index/bank_add/:id' , 'Index/User/bank_add');

//我的
Route ::group ('user' , function () {
    Route ::get ('index' , 'Index/User/index');
    Route ::get ('loginout' , 'Index/User/loginout');
    Route ::get ('user_set' , 'Index/User/user_set');
    Route ::rule ('edit_pwd' , 'Index/User/edit_pwd' , 'GET|POST');
    Route ::get ('user_address' , 'Index/User/user_address');
    Route ::rule ('address_add' , 'Index/User/address_add' , 'GET|POST');
    Route ::post ('user_address_def' , 'Index/User/user_address_def');
    Route ::post ('user_address_del' , 'Index/User/user_address_del');
    Route ::get ('user_bank' , 'Index/User/user_bank');
    Route ::rule ('bank_add' , 'Index/User/bank_add' , 'GET|POST');
    Route ::post ('user_bank_def' , 'Index/User/user_bank_def');
    Route ::post ('user_bank_del' , 'Index/User/user_bank_del');
    Route ::get ('user_money_info' , 'Index/User/user_money_info');
    Route ::get ('user_share' , 'Index/User/user_share');
    Route ::get ('user_team' , 'Index/User/user_team');
    Route ::rule ('user_info' , 'Index/User/user_info' , 'GET|POST');
});

//充值提现
Route ::group ('wealth' , function () {
    Route ::get ('index' , 'Index/wealth/index');
    Route ::get ('wealth_card/[:id]' , 'Index/wealth/wealth_card') -> ext ('html');
    Route ::get ('wealth_log' , 'Index/wealth/wealth_log');
    Route ::post ('yhkrandom' , 'Index/wealth/yhkrandom');
    Route ::post ('bank_rc' , 'Index/wealth/bank_rc');
    Route ::rule ('send_withdrawal' , 'Index/wealth/send_withdrawal' , 'GET|POST');
    Route ::get ('withdrawal' , 'Index/wealth/withdrawal');
    Route ::get ('wealth_info' , 'Index/wealth/wealth_info');

});

//余额宝
Route ::group ('investment' , function () {
    Route ::get ('index' , 'Index/investment/index');
    Route ::post ('send_investment' , 'Index/investment/send_investment');
    Route ::get ('investment_info' , 'Index/investment/investment_info');
    Route ::post ('investment_get' , 'Index/investment/investment_get');

});

//抢单
Route ::group ('single' , function () {
    Route ::get ('index' , 'Index/Single/index');
    Route ::post ('send' , 'Index/Single/send');
    Route ::get ('single_info' , 'Index/Single/single_info');
    Route ::get ('order_info/:order_id' , 'Index/Single/order_info');
    Route ::get ('customer' , 'Index/Single/customer');
    Route ::post ('order_send' , 'Index/Single/order_send');
    Route ::post ('order_thaw' , 'Index/Single/order_thaw');

});

//后台主页
Route ::group ('admin/index' , function () {
    Route ::get ('end_admin' , 'admin/index/end_admin');
    Route ::get ('index' , 'admin/index/index');
    Route ::post ('get_menu' , 'admin/index/get_menu');
    Route ::post ('get_info_money' , 'admin/index/get_info_money');
});

//后台登录路由
Route ::group ('admin/login' , function () {
    Route ::get ('admin_hui' , 'admin/login/index');
    Route ::post ('verification_code' , 'admin/login/verification_code');
    Route ::post ('login' , 'admin/login/login');
});

//后台用户管理
Route ::group ('admin/user' , function () {
    Route ::get ('index' , 'admin/user/index');
    Route ::post ('user_status' , 'admin/user/user_status');
    Route ::post ('user_del' , 'admin/user/user_del');
    Route ::get ('user_edit' , 'admin/user/user_edit');
    Route ::post ('user_edit_s' , 'admin/user/user_edit_s');
    Route ::get ('user_add' , 'admin/user/user_add');
    Route ::post ('user_add_s' , 'admin/user/user_add_s');

    Route ::get ('user_order' , 'admin/user/user_order');
    Route ::post ('user_order_status' , 'admin/user/user_order_status');
    Route ::post ('user_order_del' , 'admin/user/user_order_del');

    Route ::get ('investment' , 'admin/user/investment');
    Route ::post ('user_investment_del' , 'admin/user/user_investment_del');

    Route ::get ('money_info' , 'admin/user/money_info');
    Route ::post ('user_money_info_del' , 'admin/user/user_money_info_del');

    Route ::get ('user_bank' , 'admin/user/user_bank');
    Route ::post ('del_bank' , 'admin/user/del_bank');

});

//后台充值提现
Route ::group ('admin/wealth' , function () {
    Route ::get ('recharge' , 'admin/wealth/recharge');
    Route ::post ('recharge_status' , 'admin/wealth/recharge_status');
    Route ::post ('recharge_del' , 'admin/wealth/recharge_del');
    Route ::get ('withdrawal' , 'admin/wealth/withdrawal');
    Route ::post ('withdrawal_status' , 'admin/wealth/withdrawal_status');
    Route ::post ('withdrawal_del' , 'admin/wealth/withdrawal_del');
    Route ::get ('manual/:type' , 'admin/wealth/manual') -> ext ('html');
    Route ::post ('manual_edit' , 'admin/wealth/manual_edit');

});

//后台系统设置
Route ::group ('admin/config' , function () {
    Route ::get ('index' , 'admin/config/index');
    Route ::post ('edit_config' , 'admin/config/edit_config'); //修改文章状态
    //轮播图
    Route ::get ('banner' , 'admin/config/banner'); //轮播图列表
    Route ::get ('add_banner' , 'admin/config/add_banner'); //添加轮播图页面
    Route ::get ('edit_banner' , 'admin/config/edit_banner'); //修改轮播图页面
    Route ::post ('banner_edit_s' , 'admin/config/banner_edit_s'); //修改轮播图
    Route ::post ('banner_add_s' , 'admin/config/banner_add_s'); //添加轮播图
    Route ::post ('banner_del' , 'admin/config/banner_del'); //删除轮播图列表
    Route ::post ('banner_status' , 'admin/config/banner_status'); //修改轮播图状态
    //银行卡
    Route ::get ('link' , 'admin/config/link'); //轮播图列表
    Route ::get ('add_link' , 'admin/config/add_link'); //添加轮播图页面
    Route ::get ('edit_link' , 'admin/config/edit_link'); //修改轮播图页面
    Route ::post ('link_edit_s' , 'admin/config/link_edit_s'); //修改轮播图
    Route ::post ('link_add_s' , 'admin/config/link_add_s'); //添加轮播图
    Route ::post ('link_del' , 'admin/config/link_del'); //删除轮播图列表
    Route ::post ('link_status' , 'admin/config/link_status'); //修改轮播图状态
    //银行卡
    Route ::get ('interest' , 'admin/config/interest'); //轮播图列表
    Route ::get ('add_interest' , 'admin/config/add_interest'); //添加轮播图页面
    Route ::get ('edit_interest' , 'admin/config/edit_interest'); //修改轮播图页面
    Route ::post ('interest_edit_s' , 'admin/config/interest_edit_s'); //修改轮播图
    Route ::post ('interest_add_s' , 'admin/config/interest_add_s'); //添加轮播图
    Route ::post ('interest_del' , 'admin/config/interest_del'); //删除轮播图列表
    Route ::post ('interest_status' , 'admin/config/interest_status'); //修改轮播图状态

});

//文章
Route ::get ('admin/Article/index' , 'admin/Article/index'); //文章列表
Route ::get ('admin/Article/article_add' , 'admin/Article/article_add'); //添加文章页面
Route ::rule ('admin/Article/article_add_s' , 'admin/Article/article_add_s'); //上传文章
Route ::get ('admin/Article/article_edit' , 'admin/Article/article_edit'); //修改文章页面
Route ::post ('admin/Article/article_edit_s' , 'admin/Article/article_edit_s'); //修改文章页面
Route ::post ('admin/Article/get_menu' , 'admin/Article/get_menu'); //获取菜单
Route ::post ('admin/Article/article_del' , 'admin/Article/article_del'); //删除文章列表
Route ::post ('admin/Article/article_status' , 'admin/Article/article_status'); //修改文章状态

//后台商品管理
Route ::group ('admin/goods' , function () {
    Route ::get ('goods' , 'admin/goods/goods');
    Route ::post ('edit_goods' , 'admin/goods/edit_goods');
    //轮播图
    Route ::get ('goods' , 'admin/goods/goods');
    Route ::get ('add_goods' , 'admin/goods/add_goods');
    Route ::get ('edit_goods' , 'admin/goods/edit_goods');
    Route ::post ('goods_edit_s' , 'admin/goods/goods_edit_s');
    Route ::post ('goods_add_s' , 'admin/goods/goods_add_s');
    Route ::post ('goods_del' , 'admin/goods/goods_del');
    Route ::post ('goods_status' , 'admin/goods/goods_status');
});

//后台统计管理
Route ::group ('admin/count' , function () {
    Route ::get ('index' , 'admin/count/index');
    Route ::get ('register_count' , 'admin/count/register_count');
    Route ::get ('bank_count' , 'admin/count/bank_count');
    Route ::get ('count_sum' , 'admin/count/count_sum');
    Route ::get ('user' , 'admin/count/user');
    Route ::get ('boss_user' , 'admin/count/boss_user');

});

//管理员
Route ::group ('admin/admin' , function () {
    Route ::get ('index' , 'admin/admin/index');
    Route ::rule ('add_admin' , 'admin/admin/add_admin' , 'GET|POST');
    Route ::get ('edit_admin' , 'admin/admin/edit_admin');
    Route ::post ('edit_admin_s' , 'admin/admin/edit_admin_s');
    Route ::post ('admin_status' , 'admin/admin/admin_status');
    Route ::post ('admin_del' , 'admin/admin/admin_del');

    Route ::get ('role' , 'admin/admin/role');
    Route ::rule ('add_role' , 'admin/admin/add_role' , 'GET|POST');
    Route ::post ('role_del' , 'admin/admin/role_del');
    Route ::get ('edit_role' , 'admin/admin/edit_role');
    Route ::post ('edit_role_s' , 'admin/admin/edit_role_s');

});

Route ::get ('admin/task/update_order' , 'admin/task/update_order');













