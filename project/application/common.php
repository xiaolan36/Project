<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * 正则检测参数函数
 * @param String 	$param 待检测数据
 * @param String 	$type 检测类型
 * @return Boolean
 */
function CheckParam($param,$type)
{
    switch ($type) {
        case 'number':
            return preg_match('/^\\d+$/',$param);#整型数字
            break;
        case 'float':
            return preg_match('/^[\d]+.[0]?[1-9]+$/',$param);#浮点数字
            break;
        case 'phone'://手机
            return preg_match('/^(13[0-9]|14[579]|15[0-3,5-9]|16[6]|17[0135678]|18[0-9]|19[89])\d{8}$/',$param);
            break;
        case 'eamil'://邮箱
            return preg_match('/^[a-z]([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?$/i',$param);
            break;
        case 'url'://网址
            return preg_match('/^((http:\/\/)|(https:\/\/))?([\w-]+\.)+[\w-]+(\/[\w-.\/?%&=]*)?$/',$param);
            break;
        case 'idcard'://身份证
            return preg_match('/^([1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3})|([1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}[\d|X|x]){1}$/',$param);
            break;
        case 'username':
            return preg_match('/^[0-9a-zA-Z_]{4,6}+$/',$param);#用户名，2-12位
            break;
        case 'password':
            return preg_match('/^[a-zA-Z0-9]{5,16}+$/',$param);#弱密码，字母、数字，5-16位
            break;
        case 'password_high':
            return preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])[a-zA-Z]{1}[A-Za-z0-9]{8,20}$/',$param);#强密码，字母开头，必须包含大写字母、小写字母、数字，5-16位
            break;
        case 'chinese':
            return preg_match('/^\u4e00-\u9fa5$/',$param);#中文
            break;
        case 'numfolat':
            return preg_match('/^[0-9]+([.]{1}[0-9]+){0,1}$/',$param);#必须为数字，但是允许小数点'
            break;
        case 'bankcard':
            return preg_match ('/^\d{16}|\d{19}$/',$param);//银行卡，16-19位
        default:
            return FALSE;
            break;
    }
}