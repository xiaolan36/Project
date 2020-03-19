<?php

namespace app\admin\controller;

use think\Db;
use think\helper\Time;

/**统计类
 * Class Count
 * @package app\admin\controller
 */
class Count extends Common
{

    //总表
    public function index ()
    {

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
                'dj'   => 0
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
                'dj'   => 0
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
                'dj'   => 0
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
                'dj'   => 0
            ) ,
        );

        $cursor = Db ::name ('user') -> field ('reg_time,test,my_money,pid') -> cursor ();
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
        $data[ 'user_recharge_test' ] = Db ::name ('recharge') -> alias ('a') -> join ('user b' , 'a.uid = b.userid') -> where ('a.status' , 3) -> where ('b.test' , 1) -> group ('a.uid') -> count ();
        unset($cursor);

        $cursor = Db ::name ('money_info') -> field ('type,addtime,money') -> cursor ();
        foreach ( $cursor as $val ) {

            // 今日开始和结束的时间戳
            list($start , $end) = Time ::today ();
            if ( $val[ 'addtime' ] >= $start ) {
                if ( $val[ 'type' ] == 3 || $val[ 'type' ] == 4 || $val[ 'type' ] == 5 ) {
                    $new_data[ 'today' ][ 'yjjc' ] += $val[ 'money' ];
                }
                else {

                    if ( $val[ 'type' ] == 6 ) {
                        $new_data[ 'today' ][ 'cz' ] += $val[ 'money' ];
                        $new_data[ 'today' ][ 'cq' ] += $val[ 'money' ];
                    }
                    else {
                        if ( $val[ 'type' ] == 7 ) {
                            $new_data[ 'today' ][ 'tx' ] += $val[ 'money' ];
                            $new_data[ 'today' ][ 'cq' ] -= $val[ 'money' ];
                        }
                        else {
                            if ( $val[ 'type' ] == 2 ) {
                                $new_data[ 'today' ][ 'yj' ] += $val[ 'money' ];
                            }
                            else {
                                if ( $val[ 'type' ] == 8 ) {
                                    $new_data[ 'today' ][ 'sg' ] += $val[ 'money' ];
                                }
                            }
                        }
                    }
                }
            }

            // 昨日开始和结束的时间戳
            list($start , $end) = Time ::yesterday ();

            if ( $val[ 'addtime' ] >= $start && $val[ 'addtime' ] <= $end ) {
                if ( $val[ 'type' ] == 3 || $val[ 'type' ] == 4 || $val[ 'type' ] == 5 ) {
                    $new_data[ 'yesterday' ][ 'yjjc' ] += $val[ 'money' ];
                }
                else {

                    if ( $val[ 'type' ] == 6 ) {
                        $new_data[ 'yesterday' ][ 'cz' ] += $val[ 'money' ];
                        $new_data[ 'yesterday' ][ 'cq' ] += $val[ 'money' ];
                    }
                    else {
                        if ( $val[ 'type' ] == 7 ) {
                            $new_data[ 'yesterday' ][ 'tx' ] += $val[ 'money' ];
                            $new_data[ 'yesterday' ][ 'cq' ] -= $val[ 'money' ];
                        }
                        else {
                            if ( $val[ 'type' ] == 2 ) {
                                $new_data[ 'yesterday' ][ 'yj' ] += $val[ 'money' ];
                            }
                            else {
                                if ( $val[ 'type' ] == 8 ) {
                                    $new_data[ 'yesterday' ][ 'sg' ] += $val[ 'money' ];
                                }
                            }
                        }
                    }
                }
            }

            // 本月开始和结束的时间戳
            list($start , $end) = Time ::month ();
            if ( $val[ 'addtime' ] >= $start && $val[ 'addtime' ] <= $end ) {
                if ( $val[ 'type' ] == 3 || $val[ 'type' ] == 4 || $val[ 'type' ] == 5 ) {
                    $new_data[ 'month' ][ 'yjjc' ] += $val[ 'money' ];
                }
                else {

                    if ( $val[ 'type' ] == 6 ) {
                        $new_data[ 'month' ][ 'cz' ] += $val[ 'money' ];
                        $new_data[ 'month' ][ 'cq' ] += $val[ 'money' ];
                    }
                    else {
                        if ( $val[ 'type' ] == 7 ) {
                            $new_data[ 'month' ][ 'tx' ] += $val[ 'money' ];
                            $new_data[ 'month' ][ 'cq' ] -= $val[ 'money' ];
                        }
                        else {
                            if ( $val[ 'type' ] == 2 ) {
                                $new_data[ 'month' ][ 'yj' ] += $val[ 'money' ];
                            }
                            else {
                                if ( $val[ 'type' ] == 8 ) {
                                    $new_data[ 'month' ][ 'sg' ] += $val[ 'money' ];
                                }
                            }
                        }
                    }
                }
            }
            // 上月开始和结束的时间戳
            list($start , $end) = Time ::lastMonth ();

            if ( $val[ 'addtime' ] >= $start && $val[ 'addtime' ] <= $end ) {
                if ( $val[ 'type' ] == 3 || $val[ 'type' ] == 4 || $val[ 'type' ] == 5 ) {
                    $new_data[ 'lastMonth' ][ 'yjjc' ] += $val[ 'money' ];
                }
                else {

                    if ( $val[ 'type' ] == 6 ) {
                        $new_data[ 'lastMonth' ][ 'cz' ] += $val[ 'money' ];
                        $new_data[ 'lastMonth' ][ 'cq' ] += $val[ 'money' ];
                    }
                    else {
                        if ( val[ 'type' ] == 7 ) {
                            $new_data[ 'lastMonth' ][ 'tx' ] += $val[ 'money' ];
                            $new_data[ 'lastMonth' ][ 'cq' ] -= $val[ 'money' ];
                        }
                        else {
                            if ( $val[ 'type' ] == 2 ) {
                                $new_data[ 'lastMonth' ][ 'yj' ] += $val[ 'money' ];
                            }
                            else {
                                if ( $val[ 'type' ] == 8 ) {
                                    $new_data[ 'lastMonth' ][ 'sg' ] += $val[ 'money' ];
                                }
                            }
                        }
                    }
                }
            }
        }
        unset($cursor);

        $cursor = Db ::name ('user_order') -> field ('price,addtime,status') -> where ('status' , 'neq' , 1) -> cursor ();
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

        $this -> assign ('data' , $data);
        $this -> assign ('new_data' , $new_data);
        return $this -> fetch ('index' , [ 'title' => '报表管理' , 'title2' => '总表' ]);
    }

    /**
     *注册统计
     * User: lanzh
     * Date: 2020/2/24 20:04
     */
    public function register_count ()
    {

        $param = $this -> params;
        //时间戳不为空
        if ( isset($param[ 'start' ]) && isset($param[ 'end' ]) && !empty($param[ 'start' ]) && !empty($param[ 'end' ]) ) {

            $day              = $this -> getChaBetweenTwoDate ($param[ 'start' ] , $param[ 'end' ]) + 1;
            $param[ 'start' ] = strtotime ($param[ 'start' ]);
            $param[ 'end' ]   = strtotime ($param[ 'end' ]) + 86400;
        }
        else {
            if ( isset($param[ 'start' ]) && !empty($param[ 'start' ]) ) {

                $param[ 'start' ] = strtotime ($param[ 'start' ]);
                $day              = 7;

            }
            else {
                // 获取7天前的时间戳
                list($start , $end) = Time ::dayToNow (6);
                $param[ 'start' ] = $start;
                $day              = 7;
            }
        }

        for ( $i = 0 ; $i < $day ; $i++ ) {
            $data[] = array (
                'time'        => $param[ 'start' ] + ( $i * 86400 ) ,
                'reg_count'   => 0 ,
                'recharge'    => 0 ,
                'no_recharge' => 0
            );
        }

        $data = array_reverse ($data);

        foreach ( $data as $key => $val ) {

            //包含测试
            // $data[ $key ][ 'reg_count' ]   = Db ::name ('user') -> where ('reg_time' , '>=' , $val[ 'time' ]) -> where ('reg_time' , '<=' , $val[ 'time' ] + 86400) -> count ();
            //$data[ $key ][ 'recharge' ]    = Db ::name ('recharge') -> where ('addtime' , '>=' , $val[ 'time' ]) -> where ('addtime' , '<=' , $val[ 'time' ] + 86400) -> where ('status' , 3) -> group ('uid') -> count ();

            //不包含测试
            $data[ $key ][ 'reg_count' ]   = Db ::name ('user') -> where ('reg_time' , '>=' , $val[ 'time' ]) -> where ('reg_time' , '<=' , $val[ 'time' ] + 86400) -> where ('test' , '<>' , 1) -> count ();
            $data[ $key ][ 'recharge' ]    = Db ::name ('recharge') -> alias ('a') -> join ('user b' , 'a.uid=b.userid') -> where ('b.test <> 1') -> where ('a.addtime' , '>=' , $val[ 'time' ]) -> where ('a.addtime' , '<=' , $val[ 'time' ] + 86400) -> where ('a.status' , 3) -> group ('uid') -> count ();
            $data[ $key ][ 'no_recharge' ] = $data[ $key ][ 'reg_count' ] - $data[ $key ][ 'recharge' ] < 0 ? 0 : $data[ $key ][ 'reg_count' ] - $data[ $key ][ 'recharge' ];
        }

        $this -> assign ('data' , $data);
        return $this -> fetch ('register_count' , [ 'title' => '报表管理' , 'title2' => '注册统计表' ]);
    }

    /**
     *银行卡统计
     * User: lanzh
     * Date: 2020/2/24 20:04
     */
    public function bank_count ()
    {

        $param = $this -> params;
        //时间戳不为空
        if ( isset($param[ 'start' ]) && isset($param[ 'end' ]) && !empty($param[ 'start' ]) && !empty($param[ 'end' ]) ) {

            $day              = $this -> getChaBetweenTwoDate ($param[ 'start' ] , $param[ 'end' ]) + 1;
            $param[ 'start' ] = strtotime ($param[ 'start' ]);
            $param[ 'end' ]   = strtotime ($param[ 'end' ]) + 86400;
        }
        else {
            if ( isset($param[ 'start' ]) && !empty($param[ 'start' ]) ) {

                $param[ 'start' ] = strtotime ($param[ 'start' ]);
                $day              = 7;

            }
            else {
                // 获取7天前的时间戳
                list($start , $end) = Time ::dayToNow (6);
                $param[ 'start' ] = $start;
                $day              = 7;
            }
        }

        for ( $i = 0 ; $i < $day ; $i++ ) {
            $data[] = array (
                'time'       => $param[ 'start' ] + ( $i * 86400 ) ,
                'bank_count' => 0 ,
                'bank_money' => 0 ,
                'bank_true'  => 0 ,
                'bank_false'
            );
        }

        $data = array_reverse ($data);

        foreach ( $data as $key => $val ) {

            //包含测试
            //            $data[ $key ][ 'bank_count' ] = Db ::name ('recharge') -> where ('addtime' , '>=' , $val[ 'time' ]) -> where ('addtime' , '<=' , $val[ 'time' ] + 86400) -> count ();
            //            $data[ $key ][ 'bank_money' ] = Db ::name ('recharge') -> where ('addtime' , '>=' , $val[ 'time' ]) -> where ('addtime' , '<=' , $val[ 'time' ] + 86400) -> sum ('money');
            //            $data[ $key ][ 'bank_true' ]  = Db ::name ('recharge') -> where ('addtime' , '>=' , $val[ 'time' ]) -> where ('addtime' , '<=' , $val[ 'time' ] + 86400) -> where ('status' , 3) -> sum ('money');

            //不包含测试
            $data[ $key ][ 'bank_count' ] = Db ::name ('recharge') -> alias ('a') -> join ('user b' , 'a.uid=b.userid') -> where ('b.test <> 1') -> where ('a.addtime' , '>=' , $val[ 'time' ]) -> where ('a.addtime' , '<=' , $val[ 'time' ] + 86400) -> count ();

            $data[ $key ][ 'bank_money' ] = Db ::name ('recharge') -> alias ('a') -> join ('user b' , 'a.uid=b.userid') -> where ('b.test <> 1') -> where ('a.addtime' , '>=' , $val[ 'time' ]) -> where ('a.addtime' , '<=' , $val[ 'time' ] + 86400) -> sum ('money');

            $data[ $key ][ 'bank_true' ] = Db ::name ('recharge') -> alias ('a') -> join ('user b' , 'a.uid=b.userid') -> where ('b.test <> 1') -> where ('a.addtime' , '>=' , $val[ 'time' ]) -> where ('a.addtime' , '<=' , $val[ 'time' ] + 86400) -> where ('a.status' , 3) -> sum ('money');

            $data[ $key ][ 'bank_false' ] = $data[ $key ][ 'bank_money' ] - $data[ $key ][ 'bank_true' ];
        }

        $this -> assign ('data' , $data);
        return $this -> fetch ('bank_count' , [ 'title' => '报表管理' , 'title2' => '充值统计表' ]);
    }

    /**
     *总报表统计
     * User: lanzh
     * Date: 2020/2/24 20:04
     */
    public function count_sum ()
    {

        $param = $this -> params;
        //时间戳不为空
        if ( isset($param[ 'start' ]) && isset($param[ 'end' ]) && !empty($param[ 'start' ]) && !empty($param[ 'end' ]) ) {

            $day              = $this -> getChaBetweenTwoDate ($param[ 'start' ] , $param[ 'end' ]) + 1;
            $param[ 'start' ] = strtotime ($param[ 'start' ]);
            $param[ 'end' ]   = strtotime ($param[ 'end' ]) + 86400;
            $map[]            = [ 'a.addtime' , 'between' , "{$param['start']},{$param['end']}" ];
        }
        else {
            if ( isset($param[ 'start' ]) && !empty($param[ 'start' ]) ) {

                $param[ 'start' ] = strtotime ($param[ 'start' ]);
                $day              = 7;
                $end              = $param[ 'start' ] + ( 86400 * $day );
                $map[]            = [ 'a.addtime' , 'between' , "{$param['start']},{$end}" ];

            }
            else {
                // 获取7天前的时间戳
                list($start , $end) = Time ::dayToNow (6);
                $param[ 'start' ] = $start;
                $day              = 7;
                $end              = $param[ 'start' ] + ( 86400 * $day );
                $map[]            = [ 'a.addtime' , 'between' , "{$param['start']},{$end}" ];
            }
        }

        for ( $i = 0 ; $i < $day ; $i++ ) {
            $data[] = array (
                'time' => $param[ 'start' ] + ( $i * 86400 ) ,
                'cz'   => 0 ,
                'tx'   => 0 ,
                'czrs' => 0 ,
                'txrs' => 0 ,
                'sc'   => 0 ,
                'zx'   => 0 ,
                'cq'   => 0 ,
                'sd'   => 0 ,
                'yj'   => 0 ,
                'yjjc' => 0 ,
                'sg'   => 0 ,
                'kj'   => 0 ,
                'dj'   => 0 ,
                'lc'   => 0 ,
                'sum'  => 0 ,
            );
        }

        $data     = array_reverse ($data);
        $sum_data = array (
            'time' => '统计' ,
            'cz'   => 0 ,
            'tx'   => 0 ,
            'czrs' => 0 ,
            'txrs' => 0 ,
            'sc'   => 0 ,
            'zx'   => 0 ,
            'cq'   => 0 ,
            'sd'   => 0 ,
            'yj'   => 0 ,
            'yjjc' => 0 ,
            'sg'   => 0 ,
            'kj'   => 0 ,
            'dj'   => 0 ,
            'lc'   => 0 ,
            'sum'  => 0 ,
        );

        //包含测试号码
        //$money_info = Db ::name ('money_info') -> field ('type,addtime,money') -> where ($map) -> select ();
        //$user_order = Db ::name ('user_order') -> field ('price,addtime,status') -> where ($map) -> where ('status' , 'neq' , 1) -> select ();

        //不包含测试
        $money_info = Db ::table ('api_money_info') -> alias ('a') -> join ('user b' , 'a.uid=b.userid') -> where ('b.test <> 1') -> where ($map) -> field ('a.type,a.addtime,a.money') -> select ();
        $user_order = Db ::table ('api_user_order') -> alias ('a') -> join ('user b' , 'a.uid=b.userid') -> where ($map) -> where ('a.status' , 'neq' , 1) -> where ('b.test <> 1') -> field ('a.price,a.addtime,a.status') -> select ();

        foreach ( $data as $key => $item ) {
            $endtime = $item[ 'time' ] + 86400;
            //充值提现总额，存取差额，佣金总额，提出佣金总额，手工赠金。
            foreach ( $money_info as $k => $val ) {
                if ( $val[ 'addtime' ] >= $item[ 'time' ] && $val[ 'addtime' ] <= $endtime ) {

                    switch ( intval ($val[ 'type' ]) ) {
                        case 3:
                        case 4:
                        case 5:
                            $data[ $key ][ 'yjjc' ] += $val[ 'money' ];
                            $sum_data[ 'yjjc' ]     += $val[ 'money' ];
                            break;
                        case 6:
                            $data[ $key ][ 'cz' ] += $val[ 'money' ];
                            $data[ $key ][ 'cq' ] += $val[ 'money' ];
                            $sum_data[ 'cz' ]     += $val[ 'money' ];
                            $sum_data[ 'cq' ]     += $val[ 'money' ];
                            break;
                        case 7:
                            $data[ $key ][ 'tx' ] += $val[ 'money' ];
                            $data[ $key ][ 'cq' ] -= $val[ 'money' ];
                            $sum_data[ 'tx' ]     += $val[ 'money' ];
                            $sum_data[ 'cq' ]     -= $val[ 'money' ];
                            break;
                        case 2:
                            $data[ $key ][ 'yj' ] += $val[ 'money' ];
                            $sum_data[ 'yj' ]     += $val[ 'money' ];
                            break;
                        case 1:
                            $data[ $key ][ 'lc' ] += $val[ 'money' ];
                            $sum_data[ 'lc' ]     += $val[ 'money' ];
                            break;
                        case 8:
                            $data[ $key ][ 'sg' ] += $val[ 'money' ];
                            $sum_data[ 'sg' ]     += $val[ 'money' ];
                            break;
                        case 9:
                            $data[ $key ][ 'kj' ] += $val[ 'money' ];
                            $sum_data[ 'kj' ]     += $val[ 'money' ];
                            break;
                    }
                    unset($money_info[ $k ]);
                }
            }

            //刷单商品总额和未结算
            foreach ( $user_order as $k => $val ) {
                if ( $val[ 'addtime' ] >= $item[ 'time' ] && $val[ 'addtime' ] <= $endtime ) {
                    switch ( intval ($val[ 'status' ]) ) {
                        case 3:
                            $data[ $key ][ 'sd' ] += $val[ 'price' ];
                            $sum_data[ 'sd' ]     += $val[ 'price' ];
                            break;
                        case 2:
                            $data[ $key ][ 'dj' ] += $val[ 'price' ];
                            $sum_data[ 'dj' ]     += $val[ 'price' ];
                            break;
                    }
                    unset($user_order[ $k ]);
                }
            }
            unset($map);
            //包含测试
//            $map[]                  = [ 'addtime' , 'between' , "{$item['time']},{$endtime}" ];
//            $data[ $key ][ 'czrs' ] = Db ::name ('recharge') -> where ($map) -> where ('status' , 3) -> count ();//充值人数
//            $sum_data[ 'czrs' ]     += $data[ $key ][ 'czrs' ];
//            $data[ $key ][ 'txrs' ] = Db ::name ('withdrawal') -> where ($map) -> where ('status' , 3) -> count ();//提现人数
//            $sum_data[ 'txrs' ]     += $data[ $key ][ 'txrs' ];
//            $data[ $key ][ 'sc' ]   = Db ::name ('recharge') -> where ($map) -> where ('status' , 3) -> group ('uid') -> count ();//首充
//            $sum_data[ 'sc' ]       += $data[ $key ][ 'sc' ];
//            $data[ $key ][ 'zx' ]   = Db ::name ('count_online') -> where ($map) -> count ();//在线
//            $sum_data[ 'zx' ]       += $data[ $key ][ 'zx' ];

            //包含测试
            //充值人数
            $map[]                  = [ 'a.addtime' , 'between' , "{$item['time']},{$endtime}" ];
            $data[ $key ][ 'czrs' ] = Db ::name ('recharge') -> alias ('a') -> join ('user b' , 'a.uid=b.userid') -> where ('b.test <> 1') -> where ($map) -> where ('a.status' , 3) -> group ('a.uid') -> count ();
            $sum_data[ 'czrs' ]     += $data[ $key ][ 'czrs' ];
            //提现人数
            $data[ $key ][ 'txrs' ] = Db ::name ('withdrawal') -> alias ('a') -> join ('user b' , 'a.uid=b.userid') -> where ('b.test <> 1') -> where ($map) -> where ('a.status' , 3) -> group ('a.uid') -> count ();
            $sum_data[ 'txrs' ]     += $data[ $key ][ 'txrs' ];
            //在线
            $data[ $key ][ 'zx' ] = Db ::name ('count_online') -> alias ('a') -> join ('user b' , 'a.uid=b.userid') -> where ('b.test <> 1') -> where ($map) -> count ();
            $sum_data[ 'zx' ]     += $data[ $key ][ 'zx' ];
            //首充
            $num                  = Db ::name ('recharge') -> alias ('a') -> join ('user b' , 'a.uid=b.userid') -> where ('b.test <> 1') -> where ('a.status' , 3) -> where ('a.addtime' , '<' , $item[ 'time' ]) -> group ('a.uid') -> count ();
            $num_two              = Db ::name ('recharge') -> alias ('a') -> join ('user b' , 'a.uid=b.userid') -> where ('b.test <> 1') -> where ('a.status' , 3) -> where ('a.addtime' , '<' , $endtime) -> group ('a.uid') -> count ();
            $data[ $key ][ 'sc' ] = $num_two - $num;
            $sum_data[ 'sc' ]     += $data[ $key ][ 'sc' ];
        }

        //加入总数据
        array_push ($data , $sum_data);

        //保留两位小数
        foreach ( $data as $key => $val ) {
            if ( is_numeric ($val[ 'time' ]) ) {
                $data[ $key ][ 'time' ] = date ('Y-m-d' , $data[ $key ][ 'time' ]);
            }

            foreach ( $val as $k => $v ) {
                if ( !is_int ($v) ) {
                    $data[ $key ][ $k ] = round ($v , 2);
                }
            }
        }
        $this -> assign ('data' , $data);

        //导出表格
        if ( isset($param[ 'excel' ]) ) {
            $this -> exportExcel (array (
                '时间日期' ,
                '充值总额' ,
                '提现总额' ,
                '充值人数' ,
                '提现人数' ,
                '首充人数' ,
                '在线人数' ,
                '存取差额' ,
                '刷单商品总额' ,
                '佣金总额' ,
                '佣金提成总额' ,
                '手工赠金' ,
                '手工扣金' ,
                '未结算金额' ,
                '理财收益总额'
            ) , $data , '统计报表' . date ('Y-m-d H:i' , time ()) , './' , true);
            exit();
        }
        else {
            return $this -> fetch ('count_sum' , [ 'title' => '报表管理' , 'title2' => '统计报表' ]);
        }

    }

    /**
     *用户报表
     * User: lanzh
     * Date: 2020/2/26 14:46
     */
    public function user ()
    {
        $param    = $this -> params;
        $map      = [];
        $user_map = [];
        if ( !empty($param) ) {
            //搜索内容不为空
            if ( isset($param[ 'searchInput' ]) && !empty($param[ 'searchInput' ]) ) {
                if ( strlen ($param[ 'searchInput' ]) == 11 ) {
                    $user_map[] = [ 'phone' , 'eq' , $param[ 'searchInput' ] ];
                    $id         = Db ::name ('user') -> where ($user_map) -> field ('userid') -> find ();
                    $map[]      = [ 'uid' , 'eq' , $id[ 'userid' ] ];
                }
                else {
                    $user_map[] = [ 'userid' , 'eq' , $param[ 'searchInput' ] ];
                    $map[]      = [ 'uid' , 'eq' , $param[ 'searchInput' ] ];
                }
            }

            //时间戳不为空
            if ( isset($param[ 'start' ]) && isset($param[ 'end' ]) && !empty($param[ 'start' ]) && !empty($param[ 'end' ]) ) {
                $a     = strtotime ($param[ 'start' ]);
                $b     = strtotime ($param[ 'end' ]) + 86400;
                $map[] = [ 'addtime' , 'between' , "$a,$b" ];
            }
            else {
                if ( isset($param[ 'start' ]) && !empty($param[ 'start' ]) ) {
                    $a     = strtotime ($param[ 'start' ]);
                    $b     = strtotime ($param[ 'start' ]) + 86400;
                    $map[] = [ 'addtime' , 'between' , "$a,$b" ];
                }
            }
        }

        $list     = Db ::name ('user') -> where ($user_map) -> field ('userid,phone,my_money') -> order ('my_money' , 'desc') -> paginate (20 , false , [ 'query' => request () -> param () ]) -> each (function ( $item , $key ) {
            return $item;
        });
        $page     = $list -> render ();
        $new_user = array ();
        foreach ( $list as $key => $val ) {
            $new_user[ $val[ 'userid' ] ] = array (
                'userid'   => $val[ 'userid' ] ,
                'phone'    => $val[ 'phone' ] ,
                'my_money' => $val[ 'my_money' ] ,
                'cz'       => 0 ,
                'tx'       => 0 ,
                'cq'       => 0 ,
                'sd'       => 0 ,
                'yj'       => 0 ,
                'yjtc'     => 0 ,
                'sg'       => 0 ,
                'dj'       => 0
            );
        }
        if ( empty($new_user) ) {
            $this -> success ('搜索不存在的id或账号' , 'count/user');
        }
        unset($list);
        $money_info = Db ::name ('money_info') -> field ('uid,type,addtime,money') -> where ($map) -> cursor ();
        foreach ( $money_info as $val ) {
            if ( isset($new_user[ $val[ 'uid' ] ]) ) {
                switch ( intval ($val[ 'type' ]) ) {
                    case 6:
                        $new_user[ $val[ 'uid' ] ][ 'cz' ] += $val[ 'money' ];
                        $new_user[ $val[ 'uid' ] ][ 'cq' ] += $val[ 'money' ];
                        break;
                    case 7:
                        $new_user[ $val[ 'uid' ] ][ 'tx' ] += $val[ 'money' ];
                        $new_user[ $val[ 'uid' ] ][ 'cq' ] -= $val[ 'money' ];
                        break;
                    case 2:
                        $new_user[ $val[ 'uid' ] ][ 'yj' ] += $val[ 'money' ];
                        break;
                    case 3:
                    case 4:
                    case 5:
                        $new_user[ $val[ 'uid' ] ][ 'yjtc' ] += $val[ 'money' ];
                        break;
                    case 8:
                        $new_user[ $val[ 'uid' ] ][ 'sg' ] += $val[ 'money' ];
                        break;
                }
            }
        }
        unset($money_info);

        $user_order = Db ::name ('user_order') -> field ('uid,price,addtime,status') -> where ($map) -> where ('status' , 'neq' , 1) -> cursor ();
        foreach ( $user_order as $val ) {
            if ( isset($new_user[ $val[ 'uid' ] ]) ) {
                switch ( intval ($val[ 'status' ]) ) {
                    case 3:
                        $new_user[ $val[ 'uid' ] ][ 'sd' ] += $val[ 'price' ];
                        break;
                    case 2:
                        $new_user[ $val[ 'uid' ] ][ 'dj' ] += $val[ 'price' ];
                        break;
                }
            }
        }
        foreach ( $new_user as $key => $val ) {
            foreach ( $val as $k => $v ) {
                if ( is_float ($v) ) {
                    $new_user[ $key ][ $k ] = round ($v , 2);
                }
            }
        }

        $this -> assign ('list' , $new_user);
        $this -> assign ('page' , $page);
        return $this -> fetch ('user' , [ 'title' => '报表管理' , 'title2' => '用户报表(不含下级)' ]);
    }

    //用户报表包含下级s
    public function boss_user ()
    {
        $param    = $this -> params;
        $map      = [];
        $user_map = [];
//        $res      = Db ::name ('user') -> field ('userid') -> where ('pid' , 80000) -> select ();
//        foreach ( $res as $key => $val ) {
//            $vip[] = $val[ 'userid' ];
//        }
//        $res = Db ::name ('user') -> field ('phone') -> where ('pid' , 80000) -> select ();
//        foreach ( $res as $key => $val ) {
//            $vip_phone[] = $val[ 'phone' ];
//        }

        if ( !empty($param) ) {

            //搜索内容不为空
            if ( isset($param[ 'searchInput' ]) && !empty($param[ 'searchInput' ]) ) {

                if ( strlen ($param[ 'searchInput' ]) == 11 ) {
//                    if ( !in_array ($param[ 'searchInput' ] , $vip_phone) ) {
//                        $this -> success ('搜索不存在账号' , 'count/boss_user');
//                    }
                    $user_map[] = [ 'phone' , 'eq' , $param[ 'searchInput' ] ];
                    $id         = Db ::name ('user') -> where ($user_map) -> field ('userid') -> find ();
                    $map[]      = [ 'uid' , 'eq' , $id[ 'userid' ] ];
                }

                else {
//                    if ( !in_array ($param[ 'searchInput' ] , $vip) ) {
//                        $this -> success ('搜索不存在的id' , 'count/boss_user');
//                    }
                    $user_map[] = [ 'userid' , 'eq' , $param[ 'searchInput' ] ];
                    $map[]      = [ 'uid' , 'eq' , $param[ 'searchInput' ] ];
                }
            }

            //时间戳不为空
            if ( isset($param[ 'start' ]) && isset($param[ 'end' ]) && !empty($param[ 'start' ]) && !empty($param[ 'end' ]) ) {
                $a     = strtotime ($param[ 'start' ]);
                $b     = strtotime ($param[ 'end' ]) + 86400;
                $map[] = [ 'addtime' , 'between' , "$a,$b" ];
            }
            else {
                if ( isset($param[ 'start' ]) && !empty($param[ 'start' ]) ) {
                    $a     = strtotime ($param[ 'start' ]);
                    $b     = strtotime ($param[ 'start' ]) + 86400;
                    $map[] = [ 'addtime' , 'between' , "$a,$b" ];
                }
            }
        }

//        if ( empty($user_map) ) {
//            $user = Db ::name ('user') -> where ('userid' , 'in' , $vip) -> field ('userid,phone') -> select ();
//        }
//        else {
//            $user = Db ::name ('user') -> where ($user_map) -> field ('userid,phone') -> select ();
//        }
//        exit(var_dump ($map));
        $users = Db ::name ('user') -> where ($user_map) -> order ('userid' , 'asc') -> field ('userid,phone,test,my_money') -> paginate (50 , false , [ 'query' => request () -> param () ]);

        $page = $users -> render ();
        $this -> assign ('page' , $page);
        $money_info = Db ::name ('money_info') -> field ('uid,type,money') -> where ($map) -> select ();
        $user_order = Db ::name ('user_order') -> field ('uid,price,status') -> where ($map) -> where ('status' , 'neq' , 1) -> select ();
        $ulist      = Db ::name ('user') -> field ('userid,pid,phone,my_money,test') -> where ('1=1') -> select ();

        foreach ( $users as $key => $val ) {
            $user[] = $users[ $key ];
        }

        foreach ( $user as $key => $val ) {

            $user[ $key ][ 'cz' ]       = 0;
            $user[ $key ][ 'tx' ]       = 0;
            $user[ $key ][ 'cq' ]       = 0;
            $user[ $key ][ 'sd' ]       = 0;
            $user[ $key ][ 'yj' ]       = 0;
            $user[ $key ][ 'yjtc' ]     = 0;
            $user[ $key ][ 'sg' ]       = 0;
            $user[ $key ][ 'dj' ]       = 0;
            $user[ $key ][ 'lc' ]       = 0;
            $user[ $key ][ 'sum' ]      = 0;
            $user[ $key ][ 'kj' ]       = 0;
            $user[ $key ][ 'ulist' ]    = $this -> recursion_user ($ulist , $val[ 'userid' ]);
            $user[ $key ][ 'sum_user' ] = json_encode ($user[ $key ][ 'ulist' ] , 0);
            $res                        = $this -> recursion_user_count ($user[ $key ][ 'ulist' ]);

            $new = [];
            //自己为正式才统计
            if ( $user[ $key ][ 'test' ] != 1 ) {
                $new[ $val[ 'userid' ] ] = array ();
                $user[ $key ][ 'sum' ]   += $val[ 'my_money' ];
            }

            //存在下级
            if ( $res ) {
                foreach ( $res as $k => $v ) {
                    //下级非正式不统计
                    if ( $v[ 'test' ] == 1 ) {
                        continue;
                    }
                    $new[ $v[ 'userid' ] ] = array ();
                    $user[ $key ][ 'sum' ] += $v[ 'my_money' ];
                }
            }

            //所有人都是测试号结束
            if ( empty($new) ) {
                continue;
            }

            //开始统计
            foreach ( $money_info as $key1 => $item ) {

                if ( isset($new[ $item[ 'uid' ] ]) ) {

                    switch ( intval ($item[ 'type' ]) ) {
                        case 3:
                        case 4:
                        case 5:
                            $user[ $key ][ 'yjtc' ] += $item[ 'money' ];
                            break;
                        case 1:
                            $user[ $key ][ 'lc' ] += $item[ 'money' ];
                            break;
                        case 6:
                            $user[ $key ][ 'cz' ] += $item[ 'money' ];
                            $user[ $key ][ 'cq' ] += $item[ 'money' ];
                            break;
                        case 7:
                            $user[ $key ][ 'tx' ] += $item[ 'money' ];
                            $user[ $key ][ 'cq' ] -= $item[ 'money' ];
                            break;
                        case 2:
                            $user[ $key ][ 'yj' ] += $item[ 'money' ];
                            break;
                        case 8:
                            $user[ $key ][ 'sg' ] += $item[ 'money' ];
                            break;
                        case 9:
                            $user[ $key ][ 'kj' ] += $item[ 'money' ];
                            break;
                    }
                }
            }

            foreach ( $user_order as $key2 => $item ) {
                if ( isset($new[ $item[ 'uid' ] ]) ) {
                    switch ( intval ($item[ 'status' ]) ) {
                        case 3:
                            $user[ $key ][ 'sd' ] += $item[ 'price' ];
                            break;
                        case 2:
                            $user[ $key ][ 'dj' ] += $item[ 'price' ];
                            break;
                    }
                }
            }
        }

        foreach ( $user as $key => $val ) {
            foreach ( $val as $k => $v ) {
                if ( is_float ($v) ) {
                    $user[ $key ][ $k ] = round ($v , 2);
                }
            }
        }

        $this -> assign ('list' , $this -> sortArr ($user , 'cz' , SORT_DESC , SORT_STRING));
        return $this -> fetch ('boss_user' , [ 'title' => '报表管理' , 'title2' => '用户报表' ]);
    }

    /**
     *递归汇总
     *
     * @param $list
     *
     * @return array
     * User: lanzh
     * Date: 2020/2/20 14:03
     */
    private function recursion_user_count ( $list )
    {
        $result = array ();
        foreach ( $list as $key => $val ) {

            $result[] = array (
                'userid'   => $val[ 'userid' ] ,
                'my_money' => $val[ 'my_money' ] ,
                'level'    => $val[ 'level' ] ,
                'test'     => $val[ 'test' ] ,
            );
            if ( isset($val[ 'children' ]) ) {

                $rp     = $this -> recursion_user_count ($val[ 'children' ]);
                $result = array_merge ($result , $rp);
            }
        }
        return $result;
    }

    /**
     *递归下级
     *
     * @param     $list
     * @param int $userid
     * @param int $level
     *
     * @return array
     * User: lanzh
     * Date: 2020/2/20 14:03
     */
    private function recursion_user ( $list , $userid = 0 , $level = 0 )
    {
        $result = array ();
        foreach ( $list as $key => $val ) {
            if ( $val[ 'pid' ] == $userid ) {
                $val[ 'level' ] = $level + 1;
                unset($list[ $key ]);
                if ( !empty($list) ) {
                    $child = $this -> recursion_user ($list , $val[ 'userid' ] , $level + 1);
                    if ( !empty($child) ) {
                        $val[ 'children' ] = $child;
                    }
                }
                $result[] = $val;
            }
        }
        return $result;
    }

    /**
     *获取两个时间戳的相隔天数
     *
     * @param $date1
     * @param $date2
     *
     * @return false|float
     * User: lanzh
     * Date: 2020/2/25 10:48
     */
    private function getChaBetweenTwoDate ( $date1 , $date2 )
    {
        $Date_List_a1 = explode ("-" , $date1);
        $Date_List_a2 = explode ("-" , $date2);
        $d1           = mktime (0 , 0 , 0 , $Date_List_a1[ 1 ] , $Date_List_a1[ 2 ] , $Date_List_a1[ 0 ]);
        $d2           = mktime (0 , 0 , 0 , $Date_List_a2[ 1 ] , $Date_List_a2[ 2 ] , $Date_List_a2[ 0 ]);
        $Days         = round (( $d2 - $d1 ) / 3600 / 24);
        return $Days;
    }

    //二维数组排序
    private function sortArr ( $arrays , $sort_key , $sort_order = SORT_ASC , $sort_type = SORT_NUMERIC )
    {
        $key_arrays = array ();
        if ( is_array ($arrays) ) {
            foreach ( $arrays as $array ) {
                if ( is_array ($array) ) {
                    $key_arrays[] = $array[ $sort_key ];
                }
                else {
                    return false;
                }
            }
        }
        else {
            return false;
        }
        array_multisort ($key_arrays , $sort_order , $sort_type , $arrays);
        return $arrays;
    }

}