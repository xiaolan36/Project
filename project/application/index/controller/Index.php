<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\helper\Time;

/**
 * 主页类
 * Class Index
 * @package app\index\controller
 */
class Index extends Common
{

    /**
     *
     * @return mixed
     * User: lanzh
     * Date: 2020/2/14 18:56
     */
    public function index ()
    {

        $info = Db ::name ('money_info') -> where ('type' , 2) -> order ('addtime' , 'desc') -> limit (10) -> select ();
        foreach ( $info as $key => $val ) {
            $info[ $key ][ 'addtime' ] = $this -> format_date ($val[ 'addtime' ]);
            $info[ $key ][ 'phone' ]   = mb_substr ($info[ $key ][ 'phone' ] , 0 , 2) . '***' . substr ($info[ $key ][ 'phone' ] , -2);
            $info[ $key ][ 'money' ]   = round ($info[ $key ][ 'money' ] , 2);
        }

        $banner  = Db ::name ('banner') -> where ('status' , 1) -> order ('start_time' , 'desc') -> select ();
        $tags    = Db ::name ('tags') -> order ('id' , 'asc') -> select ();
        $arcitle = Db ::name ('article') -> where ('statusifyId' , 1) -> order ('id' , 'asc') -> select ();
        $config  = Db ::name ('system') -> field ('app_link') -> find ();
        $this -> assign ('banner' , $banner);
        $this -> assign ('tags' , $tags);
        $this -> assign ('arcitle' , $arcitle);
        $this -> assign ('config' , $config);
        $this -> assign ('info' , $info);
        $this -> assign ('title' , '主页');
        return $this -> fetch ();
    }

    /**
     *转换时间戳
     *
     * @param $the_time
     *
     * @return string
     * User: lanzh
     * Date: 2020/2/20 16:40
     */
    function format_date ( $the_time )
    {
        $now_time = time ();
        $dur      = $now_time - $the_time;

        if ( $dur < 60 ) {
            return $dur . '秒前';
        }
        else {
            if ( $dur < 3600 ) {
                return floor ($dur / 60) . '分钟前';
            }
            else {
                if ( $dur < 86400 ) {
                    return floor ($dur / 3600) . '小时前';
                }
                else {
                    if ( $dur < 259200 ) {//3天内
                        return floor ($dur / 86400) . '天前';
                    }
                    else {
                        return $the_time;
                    }
                }
            }
        }
    }

}
