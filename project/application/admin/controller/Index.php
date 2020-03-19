<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Session;
use think\helper\Time;

class Index extends Common
{

    //总表
    public function index ()
    {
//                $a=Db::name ('user')->where ('test',0)->where ('gid',80000)->select ();
//        $str='';
//        foreach ( $a as $key => $val ) {
//            $str.=$val['userid'].',';
//        }
//        exit(var_dump ($str));
        $data = array (
            'today'              => 0 ,
            'today_test'         => 0 ,
            'month'              => 0 ,
            'month_test'         => 0 ,
            'lastMonth'          => 0 ,
            'lastMonth_test'     => 0 ,
            'sum_user'           => 0 ,
            'sum_user_test'      => 0 ,
            'sum_money'          => 0 ,
            'sum_money_test'     => 0 ,
            'one_user'           => 0 ,
            'one_user_test'      => 0 ,
            'user_recharge'      => 0 ,
            'user_recharge_test' => 0 ,
        );

        $new_data = array (
            'today'     => array (
                'info' => '今日统计' ,
                'cz'   => 0 ,
                'tx'   => 0 ,
                'cq'   => 0 ,
                'sd'   => 0 ,
                'yj'   => 0 ,
                'yjjc' => 0 ,
                'sg'   => 0 ,
                'kj'   => 0 ,
                'dj'   => 0 ,
                'lc'   => 0
            ) ,
            'yesterday' => array (
                'info' => '昨日统计' ,
                'cz'   => 0 ,
                'tx'   => 0 ,
                'cq'   => 0 ,
                'sd'   => 0 ,
                'yj'   => 0 ,
                'yjjc' => 0 ,
                'sg'   => 0 ,
                'kj'   => 0 ,
                'dj'   => 0 ,
                'lc'   => 0
            ) ,
            'month'     => array (
                'info' => '本月统计' ,
                'cz'   => 0 ,
                'tx'   => 0 ,
                'cq'   => 0 ,
                'sd'   => 0 ,
                'yj'   => 0 ,
                'yjjc' => 0 ,
                'sg'   => 0 ,
                'kj'   => 0 ,
                'dj'   => 0 ,
                'lc'   => 0
            ) ,
            'lastMonth' => array (
                'info' => '上月统计' ,
                'cz'   => 0 ,
                'tx'   => 0 ,
                'cq'   => 0 ,
                'sd'   => 0 ,
                'yj'   => 0 ,
                'yjjc' => 0 ,
                'sg'   => 0 ,
                'kj'   => 0 ,
                'dj'   => 0 ,
                'lc'   => 0
            ) ,
        );

        $cursor = Db ::name ('user') -> field ('reg_time,test,my_money,pid') -> where ('userid' , 'neq' , 80000) -> cursor ();
        foreach ( $cursor as $val ) {

            // 今日开始和结束的时间戳
            list($start , $end) = Time ::today ();
            if ( $val[ 'reg_time' ] >= $start ) {
                if ( empty($val[ 'test' ]) ) {
                    $data[ 'today' ] += 1;
                }
                else {
                    if ( $val[ 'test' ] == 1 ) {
                        $data[ 'today_test' ] += 1;
                    }
                }
            }

            // 本月开始和结束的时间戳
            list($start , $end) = Time ::month ();;
            if ( $val[ 'reg_time' ] >= $start && $val[ 'reg_time' ] <= $end ) {
                if ( empty($val[ 'test' ]) ) {
                    $data[ 'month' ] += 1;
                }
                else {
                    if ( $val[ 'test' ] == 1 ) {
                        $data[ 'month_test' ] += 1;
                    }
                }
            }

            // 上月开始和结束的时间戳
            list($start , $end) = Time ::lastMonth ();
            if ( $val[ 'reg_time' ] >= $start && $val[ 'reg_time' ] <= $end ) {

                if ( empty($val[ 'test' ]) ) {
                    $data[ 'lastMonth' ] += 1;
                }
                else {
                    if ( $val[ 'test' ] == 1 ) {
                        $data[ 'lastMonth_test' ] += 1;
                    }
                }
            }

            //用户总数和余额
            if ( empty($val[ 'test' ]) ) {
                $data[ 'sum_user' ]  += 1;
                $data[ 'sum_money' ] += $val[ 'my_money' ];
            }
            if ( $val[ 'test' ] == 1 ) {
                $data[ 'sum_user_test' ]  += 1;
                $data[ 'sum_money_test' ] += $val[ 'my_money' ];
            }

            //一级代理
            if ( $val[ 'pid' ] == 80000 ) {

                if ( empty($val[ 'test' ]) ) {
                    $data[ 'one_user' ] += 1;
                }
                else {
                    if ( $val[ 'test' ] == 1 ) {
                        $data[ 'one_user_test' ] += 1;
                    }
                }
            }
        }

        $data[ 'user_recharge' ]      = Db ::name ('recharge') -> alias ('a') -> join ('user b' , 'a.uid = b.userid') -> where ('a.status' , 3) -> where ('b.test' , 0) -> group ('a.uid') -> count ();
//        echo Db::name ('recharge')->getLastSql ();exit();
        $data[ 'user_recharge_test' ] = Db ::name ('recharge') -> alias ('a') -> join ('user b' , 'a.uid = b.userid') -> where ('a.status' , 3) -> where ('b.test' , 1) -> group ('a.uid') -> count ();
        unset($cursor);
        //包含测试号数据
        //$cursor = Db ::name ('money_info') -> field ('type,addtime,money') -> cursor ();

        //不包含测试数据
        $cursor = Db ::table ('api_money_info') -> alias ('a') -> join ('user b' , 'a.uid=b.userid') -> where ('b.test <> 1') -> field ('a.type,a.addtime,a.money') -> cursor ();

        foreach ( $cursor as $val ) {

            // 今日开始和结束的时间戳
            list($start , $end) = Time ::today ();
            if ( $val[ 'addtime' ] >= $start ) {
                switch ( intval ($val[ 'type' ]) ) {
                    case 3://佣金提成
                    case 4:
                    case 5:
                        $new_data[ 'today' ][ 'yjjc' ] += $val[ 'money' ];
                        break;
                    case 6://充值通过
                        $new_data[ 'today' ][ 'cz' ] += $val[ 'money' ];
                        $new_data[ 'today' ][ 'cq' ] += $val[ 'money' ];
                        break;
                    case 7://提现通过
                        $new_data[ 'today' ][ 'tx' ] += $val[ 'money' ];
                        $new_data[ 'today' ][ 'cq' ] -= $val[ 'money' ];
                        break;
                    case 2://佣金加成
                        $new_data[ 'today' ][ 'yj' ] += $val[ 'money' ];
                        break;
                    case 8://手工赠金
                        $new_data[ 'today' ][ 'sg' ] += $val[ 'money' ];
                        break;
                    case 9://手工扣
                        $new_data[ 'today' ][ 'kj' ] += $val[ 'money' ];
                        break;
                    case 1://理财收益
                        $new_data[ 'today' ][ 'lc' ] += $val[ 'money' ];
                        break;
                    default:
                        break;
                }
            }

            // 昨日开始和结束的时间戳yesterday
            list($start , $end) = Time ::yesterday ();

            if ( $val[ 'addtime' ] >= $start && $val[ 'addtime' ] <= $end ) {

                switch ( intval ($val[ 'type' ]) ) {
                    case 3://佣金提成
                    case 4:
                    case 5:
                        $new_data[ 'yesterday' ][ 'yjjc' ] += $val[ 'money' ];
                        break;
                    case 6://充值通过
                        $new_data[ 'yesterday' ][ 'cz' ] += $val[ 'money' ];
                        $new_data[ 'yesterday' ][ 'cq' ] += $val[ 'money' ];
                        break;
                    case 7://提现通过
                        $new_data[ 'yesterday' ][ 'tx' ] += $val[ 'money' ];
                        $new_data[ 'yesterday' ][ 'cq' ] -= $val[ 'money' ];
                        break;
                    case 2://佣金加成
                        $new_data[ 'yesterday' ][ 'yj' ] += $val[ 'money' ];
                        break;
                    case 8://手工赠金
                        $new_data[ 'yesterday' ][ 'sg' ] += $val[ 'money' ];
                        break;
                    case 9://手工扣
                        $new_data[ 'yesterday' ][ 'kj' ] += $val[ 'money' ];
                        break;
                    case 1://理财收益
                        $new_data[ 'yesterday' ][ 'lc' ] += $val[ 'money' ];
                        break;
                    default:
                        break;
                }
            }

            // 本月开始和结束的时间戳
            list($start , $end) = Time ::month ();
            if ( $val[ 'addtime' ] >= $start && $val[ 'addtime' ] <= $end ) {

                switch ( intval ($val[ 'type' ]) ) {
                    case 3://佣金提成
                    case 4:
                    case 5:
                        $new_data[ 'month' ][ 'yjjc' ] += $val[ 'money' ];
                        break;
                    case 6://充值通过
                        $new_data[ 'month' ][ 'cz' ] += $val[ 'money' ];
                        $new_data[ 'month' ][ 'cq' ] += $val[ 'money' ];
                        break;
                    case 7://提现通过
                        $new_data[ 'month' ][ 'tx' ] += $val[ 'money' ];
                        $new_data[ 'month' ][ 'cq' ] -= $val[ 'money' ];
                        break;
                    case 2://佣金加成
                        $new_data[ 'month' ][ 'yj' ] += $val[ 'money' ];
                        break;
                    case 8://手工赠金
                        $new_data[ 'month' ][ 'sg' ] += $val[ 'money' ];
                        break;
                    case 9://手工扣
                        $new_data[ 'month' ][ 'kj' ] += $val[ 'money' ];
                        break;
                    case 1://理财收益
                        $new_data[ 'month' ][ 'lc' ] += $val[ 'money' ];
                        break;
                    default:
                        break;
                }
            }
            // 上月开始和结束的时间戳
            list($start , $end) = Time ::lastMonth ();

            if ( $val[ 'addtime' ] >= $start && $val[ 'addtime' ] <= $end ) {
                switch ( intval ($val[ 'type' ]) ) {
                    case 3://佣金提成
                    case 4:
                    case 5:
                        $new_data[ 'lastMonth' ][ 'yjjc' ] += $val[ 'money' ];
                        break;
                    case 6://充值通过
                        $new_data[ 'lastMonth' ][ 'cz' ] += $val[ 'money' ];
                        $new_data[ 'lastMonth' ][ 'cq' ] += $val[ 'money' ];
                        break;
                    case 7://提现通过
                        $new_data[ 'lastMonth' ][ 'tx' ] += $val[ 'money' ];
                        $new_data[ 'lastMonth' ][ 'cq' ] -= $val[ 'money' ];
                        break;
                    case 2://佣金加成
                        $new_data[ 'lastMonth' ][ 'yj' ] += $val[ 'money' ];
                        break;
                    case 8://手工赠金
                        $new_data[ 'lastMonth' ][ 'sg' ] += $val[ 'money' ];
                        break;
                    case 9://手工扣
                        $new_data[ 'lastMonth' ][ 'kj' ] += $val[ 'money' ];
                        break;
                    case 1://理财收益
                        $new_data[ 'lastMonth' ][ 'lc' ] += $val[ 'money' ];
                        break;
                    default:
                        break;
                }
            }
        }
        unset($cursor);

        //包含测试号
        //$cursor = Db ::name ('user_order') -> field ('price,addtime,status') -> where ('status' , 'neq' , 1) -> cursor ();
        //不包含测试
        $cursor = Db ::table ('api_user_order') -> alias ('a') -> join ('user b' , 'a.uid=b.userid') -> where ('a.status' , 'neq' , 1) -> where ('b.test <> 1') -> field ('a.price,a.addtime,a.status') -> cursor ();
        foreach ( $cursor as $val ) {
            // 今日开始和结束的时间戳
            list($start , $end) = Time ::today ();
            if ( $val[ 'addtime' ] >= $start ) {
                if ( $val[ 'status' ] == 3 ) {
                    $new_data[ 'today' ][ 'sd' ] += $val[ 'price' ];
                }
                else {
                    if ( $val[ 'status' ] == 2 ) {
                        $new_data[ 'today' ][ 'dj' ] += $val[ 'price' ];
                    }
                }
            }

            // 昨日开始和结束的时间戳
            list($start , $end) = Time ::yesterday ();

            if ( $val[ 'addtime' ] >= $start && $val[ 'addtime' ] <= $end ) {
                if ( $val[ 'status' ] == 3 ) {
                    $new_data[ 'yesterday' ][ 'sd' ] += $val[ 'price' ];
                }
                else {
                    if ( $val[ 'status' ] == 2 ) {
                        $new_data[ 'yesterday' ][ 'dj' ] += $val[ 'price' ];
                    }
                }
            }

            // 本月开始和结束的时间戳
            list($start , $end) = Time ::month ();
            if ( $val[ 'addtime' ] >= $start && $val[ 'addtime' ] <= $end ) {
                if ( $val[ 'status' ] == 3 ) {
                    $new_data[ 'month' ][ 'sd' ] += $val[ 'price' ];
                }
                else {
                    if ( $val[ 'status' ] == 2 ) {
                        $new_data[ 'month' ][ 'dj' ] += $val[ 'price' ];
                    }
                }
            }
            // 上月开始和结束的时间戳
            list($start , $end) = Time ::lastMonth ();

            if ( $val[ 'addtime' ] >= $start && $val[ 'addtime' ] <= $end ) {
                if ( $val[ 'status' ] == 3 ) {
                    $new_data[ 'lastMonth' ][ 'sd' ] += $val[ 'price' ];
                }
                else {
                    if ( $val[ 'status' ] == 2 ) {
                        $new_data[ 'lastMonth' ][ 'dj' ] += $val[ 'price' ];
                    }
                }
            }
        }

        foreach ( $new_data as $key => $val ) {
            foreach ( $val as $k => $v ) {
                if ( is_float ($v) ) {
                    $new_data[ $key ][ $k ] = round ($v , 2);
                }
            }
        }
        $this -> assign ('data' , $data);
        $this -> assign ('new_data' , $new_data);

        return $this -> fetch ('count_index' , [ 'title' => '首页' , 'title2' => '首页' ]);
    }

    //后台主页
    public function index_two ()
    {
        $tiee = Time ::today ();

        $version  = Db ::query ('SELECT VERSION() AS ver');
        $mysql_id = Db ::query ('SELECT VERSION() from dual limit 1');
        $info     = array (
            '登录ip/所在地'             => $_SERVER[ "REMOTE_ADDR" ] . '[' . $this -> ip_address ($_SERVER[ "REMOTE_ADDR" ]) . ']' ,
            '操作系统'                 => PHP_OS ,
            '运行环境'                 => $_SERVER[ "SERVER_SOFTWARE" ] ,
            'PHP运行方式'              => php_sapi_name () ,
            '运行PHP版本'              => PHP_VERSION ,
            'MySQL数据库版本'           => $mysql_id[ 0 ][ 'VERSION()' ] ,
            '网站目录'                 => $_SERVER[ 'DOCUMENT_ROOT' ] ,
            '服务器端口'                => $_SERVER[ 'SERVER_PORT' ] ,
            '上传附件限制'               => ini_get ('upload_max_filesize') ,
            '执行时间限制'               => ini_get ('max_execution_time') . '秒' ,
            '服务器时间'                => date ("Y年n月j日 H:i:s") ,
            '北京时间'                 => gmdate ("Y年n月j日 H:i:s" , time () + 8 * 3600) ,
            '服务器域名/IP'             => $_SERVER[ 'SERVER_NAME' ] . ' [ ' . gethostbyname ($_SERVER[ 'SERVER_NAME' ]) . ' ]' ,
            '剩余空间'                 => round (( disk_free_space (".") / ( 1024 * 1024 ) ) , 2) . 'M' ,
            'register_globals'     => get_cfg_var ("register_globals") == "1" ? "ON" : "OFF" ,
            'magic_quotes_gpc'     => ( 1 === get_magic_quotes_gpc () ) ? 'YES' : 'NO' ,
            'magic_quotes_runtime' => ( 1 === get_magic_quotes_runtime () ) ? 'YES' : 'NO' ,
        );

        $this -> assign ('info' , $info);
        return $this -> fetch ('index' , [ 'title' => '首页' , 'title2' => '首页' ]);
    }

    /*
     * 淘宝ip地址查询接口
     */
    public function ip_address ( $ip )
    {

        $durl = 'http://ip.taobao.com/service/getIpInfo.php?ip=' . $ip;

        // 初始化

        $curl = curl_init ();

        // 设置url路径

        curl_setopt ($curl , CURLOPT_URL , $durl);

        // 将 curl_exec()获取的信息以文件流的形式返回，而不是直接输出。

        curl_setopt ($curl , CURLOPT_RETURNTRANSFER , true);

        // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回

        curl_setopt ($curl , CURLOPT_BINARYTRANSFER , true);

        // 执行

        $data = json_decode (curl_exec ($curl));

        // 关闭连接

        curl_close ($curl);
        // 返回数据
        if ( $data ) {
            return $data -> data -> country . $data -> data -> region . $data -> data -> city . $data -> data -> isp;
        }
        else {
            return '请刷新页面重试';
        }
    }

    //退出登录
    public function end_admin ()
    {
        Session ::delete ('admin');
        $this -> success ('已退出' , 'login/admin_hui');
    }

    //获取菜单
    public function get_menu ()
    {

        $adminid = Session ::get ('admin')[ 'role' ];

        $menu_list = db () -> query ("SELECT m.* FROM api_menu as m ,api_admin as u, api_admin_role as ur ,api_menu_role as rm WHERE u.role=ur.roleid and ur.roleid=rm.r_id AND m.id=rm.m_id AND u.id={$adminid}");

        if ( $menu_list ) {
            die(json_encode (array (
                'code'     => 1 ,
                'msg'      => '获取菜单ok' ,
                'menulist' => $menu_list
            )));
        }
        else {
            die(json_encode (array (
                'code' => -1 ,
                'msg'  => '失败'
            )));
        }
    }

    //获取未处理的信息
    public function get_info_money ()
    {
        $data[ 'recharge' ]   = Db ::name ('recharge') -> where ('status' , 1) -> count ();
        $data[ 'withdrawal' ] = Db ::name ('withdrawal') -> where ('status' , 1) -> count ();
        return $this -> return_msg (200 , 'ok' , $data);
    }

}
