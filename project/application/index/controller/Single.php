<?php

namespace app\index\controller;

use think\Db;
use think\facade\Session;

/**
 * 抢单类
 * Class Single
 * @package app\index\controller
 */
class Single extends Common
{

    /**
     *抢单页面
     * @return mixed
     * User: lanzh
     * Date: 2020/2/17 0:38
     */
    public function index ()
    {
        $uid = Session ::get ('user' , 'index')[ 'userid' ];

        //今日订单数
        $data[ 'order_count' ] = Db ::name ('user_order') -> where ('addtime' , '>' , strtotime (date ('Y-m-d'))) -> where ('uid' , $uid) -> count ();

        //冻结
        $data[ 'dj_money' ] = round (Db ::name ('user_order') -> where (array (
            'status' => 2 ,
            'uid'    => $uid
        )) -> sum ('price') , 2);
        //理财未到期的钱
        $data[ 'user_money_y' ] = round (Db ::name ('investment_info') -> where (array (
            'uid'    => $uid ,
            'status' => 1
        )) -> sum ('num') , 2);
        //提现未审核的钱
        $data[ 'tx_money' ] = round (Db ::name ('withdrawal') -> where (array (
            'status' => 1 ,
            'uid'    => $uid
        )) -> sum ('money') , 2);

        //最大抢单金额
        $data[ 'max_money' ] = round (Db ::name ('user') -> where ('userid' , $uid) -> field ('my_money') -> find ()[ 'my_money' ] , 2);
        $data[ 'max_money' ] = round ($data[ 'max_money' ] - ( $data[ 'tx_money' ] + $data[ 'user_money_y' ] + $data[ 'dj_money' ] ) , 2);

        //今日佣金
        $data[ 'today_money' ] = round (Db ::name ('money_info') -> where (array (
            'uid'  => $uid ,
            'type' => 2 ,
        )) -> where ('addtime' , '>' , strtotime (date ('Y-m-d'))) -> sum ('money') , 2);

        //抢单时间
        $config = Db ::name ('system') -> field ('qd_cf,qd_yjjc,qd_nd,start,end') -> where ('1=1') -> find ();
        $times  = date ('H' , time ());
        if ( $times < $config[ 'start' ] || $times >= $config[ 'end' ] ) {
            $data[ 'start' ] = true;
        }
        else {
            $data[ 'start' ] = false;
        }

        //是否有钱
        $user                = Db ::name ('user') -> field ('my_money,qx_status') -> where ('userid' , $uid) -> find ();
        $data[ 'my_money' ]  = $user[ 'my_money' ];
        $data[ 'qx_status' ] = $user[ 'qx_status' ];

        //地址
        $data[ 'address' ] = Db ::name ('user_address') -> field ('id') -> where (array (
            'uid'    => $uid ,
            'status' => 1
        )) -> count ();
        //未处理订单
        $data[ 'order_status' ] = Db ::name ('user_order') -> where (array (
            'uid'    => $uid ,
            'status' => 1
        )) -> count ();

        $this -> assign ('title' , '抢单');
        $this -> assign ('data' , $data);
        $this -> assign ('config' , $config);
        return $this -> fetch ();
    }

    /**
     *生成抢单订单
     * User: lanzh
     * Date: 2020/2/19 23:38
     */
    public function send ()
    {

        $times  = date ('H' , time ());
        $config = Db ::name ('system') -> where ('1=1') -> find ();
        if ( $times < $config[ 'start' ] || $times >= $config[ 'end' ] ) {
            $this -> return_msg (0 , '抢单时间为早' . $config[ 'start' ] . '点到晚' . $config[ 'end' ] . '点');
        }

        $uid = Session ::get ('user' , 'index')[ 'userid' ];

        //是否有钱
        $user = Db ::name ('user') -> field ('my_money,qx_status,phone,qq') -> where ('userid' , $uid) -> find ();
        if ( $user[ 'my_money' ] <= 0 ) {
            $this -> return_msg (0 , '请充值');
        }

        //禁止抢单
        if ( $user[ 'qx_status' ] != 1 ) {
            $this -> return_msg (0 , '您已被管理员禁止抢单');
        }

        $address = Db ::name ('user_address') -> field ('id') -> where (array (
            'uid'    => $uid ,
            'status' => 1
        )) -> find ();

        if ( empty($address) ) {
            $this -> return_msg (0 , '请先设置默认地址');
        }

        $order_status = Db ::name ('user_order') -> where (array (
            'uid'    => $uid ,
            'status' => 1
        )) -> count ();

        if ( $order_status ) {
            $this -> return_msg (0 , '您有未处理的订单 ，请先处理');
        }

        //今日订单数
        $order_count = Db ::name ('user_order') -> where (array (
            'uid' => $uid
        )) -> where ('addtime' , '>=' , strtotime (date ('Y-m-d'))) -> count ();

        if ( $user[ 'my_money' ] < $config[ 'qd_minmoney' ] ) {
            $this -> return_msg (0 , '抢单最小金额为' . $config[ 'qd_minmoney' ]);
        }

        if ( $user[ 'my_money' ] <= 24999 && $order_count >= $config[ 'level1' ] ) {
            $this -> return_msg (0 , '您的账户金额为' . $user[ 'my_money' ] . '，每日最大抢单数为' . $config[ 'level1' ]);
        }
        if ( $user[ 'my_money' ] >= 25000 && $user[ 'my_money' ] <= 49999 && $order_count >= $config[ 'level2' ] ) {
            $this -> return_msg (0 , '您的账户金额为' . $user[ 'my_money' ] . '，每日最大抢单数为' . $config[ 'level2' ]);
        }
        if ( $user[ 'my_money' ] >= 50000 && $user[ 'my_money' ] <= 74999 && $order_count >= $config[ 'level3' ] ) {
            $this -> return_msg (0 , '您的账户金额为' . $user[ 'my_money' ] . '，每日最大抢单数为' . $config[ 'level3' ]);
        }
        if ( $user[ 'my_money' ] >= 75000 && $user[ 'my_money' ] <= 99999 && $order_count >= $config[ 'level4' ] ) {
            $this -> return_msg (0 , '您的账户金额为' . $user[ 'my_money' ] . '，每日最大抢单数为' . $config[ 'level4' ]);
        }
        if ( $user[ 'my_money' ] >= 100000 && $order_count >= $config[ 'level5' ] ) {
            $this -> return_msg (0 , '您的账户金额为' . $user[ 'my_money' ] . '，每日最大抢单数为' . $config[ 'level5' ]);
        }
        unset($order_count);

        //冻结
        $data[ 'dj_money' ] = round (Db ::name ('user_order') -> where (array (
            'status' => 2 ,
            'uid'    => $uid
        )) -> sum ('price') , 2);
        //理财未到期的钱
        $data[ 'user_money_y' ] = round (Db ::name ('investment_info') -> where (array (
            'uid'    => $uid ,
            'status' => 1
        )) -> sum ('num') , 2);
        //提现未审核的钱
        $data[ 'tx_money' ] = round (Db ::name ('withdrawal') -> where (array (
            'status' => 1 ,
            'uid'    => $uid
        )) -> sum ('money') , 2);

        //最大抢单金额
        $new_money = $user[ 'my_money' ] - ( $data[ 'tx_money' ] + $data[ 'user_money_y' ] + $data[ 'dj_money' ] );
        unset($data);
        $max = intval ($new_money * $config[ 'qd_cf' ] / 100);
        $min = intval ($new_money * $config[ 'qd_min' ] / 100);

        $goods_count = Db ::name ('roborder') -> where ([ 'status' => 1 ]) -> count ();
        if ( !$goods_count ) {
            $this -> return_msg (0 , '商品数量不足');
        }
        unset($goods_count);

        $goods = Db ::name ('roborder') -> where (array ( 'status' => 1 )) -> where ('price' , 'between' , "{$min},{$max}") -> field ('id,price') -> orderRand () -> find ();
        if ( empty($goods) ) {
            $this -> return_msg (0 , '暂时没有可匹配您额度的商品');
        }

        $goods_data[ 'uid' ]       = $uid;
        $goods_data[ 'uname' ]     = $user[ 'phone' ];
        $goods_data[ 'umoney' ]    = $user[ 'my_money' ];
        $goods_data[ 'pipeitime' ] = time ();
        $goods_data[ 'status' ]    = 2;
        $result                    = Db ::name ('roborder') -> where (array ( 'id' => $goods[ 'id' ] )) -> update ($goods_data);
        unset($goods_data);

        if ( $result ) {
            $data[ 'uid' ]       = $uid;
            $data[ 'price' ]     = $goods[ 'price' ];
            $data[ 'yjjc' ]      = $config[ 'qd_yjjc' ];
            $data[ 'umoney' ]    = $user[ 'my_money' ];
            $data[ 'uaccount' ]  = $user[ 'phone' ];
            $data[ 'ppid' ]      = $goods[ 'id' ];
            $data[ 'status' ]    = 1;
            $data[ 'addtime' ]   = time ();
            $data[ 'pipeitime' ] = time ();
            $data[ 'address' ]   = $address[ 'id' ];
            $data[ 'ordernum' ]  = $this -> getordernum ();

            $id = Db ::name ('user_order') -> insertGetId ($data);
            if ( $id ) {
                $this -> return_msg (1 , '抢单成功' , array ( 'id' => $id ));
            }
            else {
                $this -> return_msg (0 , '抢单失败');
            }
        }
        else {
            $this -> return_msg (0 , '抢单失败-1');
        }
    }

    /**
     *查看订单
     * User: lanzh
     * Date: 2020/2/20 0:22
     */
    public function order_info ()
    {
        $id = intval ($this -> request -> param ('order_id'));
        if ( $id > 0 ) {

            $uid   = Session ::get ('user' , 'index')[ 'userid' ];
            $olist = Db ::name ('user_order') -> where ('id' , $id) -> find ();
            if ( $uid == $olist[ 'uid' ] ) {
                $order = Db ::name ('roborder') -> where (array ( 'id' => $olist[ 'ppid' ] )) -> find ();

                $str = trim (substr (html_entity_decode (trim ($order[ 'url' ]) , ENT_QUOTES , "UTF-8") , -1));
                if ( $str != '>' ) {
                    $order[ 'url' ] = $order[ 'url' ] . '">';
                }

                $config  = Db ::name ('system') -> where ('1=1') -> find ();
                $address = Db ::name ('user_address') -> where ('id' , $olist[ 'address' ]) -> find ();
                if ( empty($address) ) {
                    $uid     = Session ::get ('user' , 'index')[ 'userid' ];
                    $address = Db ::name ('user_address') -> where ('uid' , $uid) -> where ('status' , 1) -> find ();
                }

                $address[ 'phone' ]        = mb_substr ($address[ 'phone' ] , 0 , 3) . '***' . substr ($address[ 'phone' ] , -4);
                $address[ 'address_info' ] = mb_substr ($address[ 'address_info' ] , 0 , 1) . '***' . substr ($address[ 'address_info' ] , -4);
            }

            $this -> assign ('config' , $config);
            $this -> assign ('olist' , $olist);
            $this -> assign ('order' , $order);
            $this -> assign ('address' , $address);
            return $this -> fetch ();
        }

    }

    /**
     *提交订单
     * User: lanzh
     * Date: 2020/2/20 0:23
     */
    public function order_send ()
    {
        $order_id = intval ($this -> request -> param ('id'));

        if ( $order_id > 0 ) {

            $order = Db ::name ('user_order') -> where ('id' , $order_id) -> find ();
            if ( $order[ 'status' ] == 2 ) {
                $this -> return_msg (2 , '该订单已被冻结');
            }
            if ( $order[ 'status' ] == 3 ) {
                $this -> return_msg (3 , '请勿重复提交');
            }

            if ( time () - $order[ 'addtime' ] > 120 && $order[ 'status' ] == 1 ) {
                $data[ 'status' ]       = 2;
                $data[ 'dongjie_time' ] = time ();
                Db ::name ('user_order') -> where (array ( 'id' => $order_id )) -> update ($data);
                $this -> return_msg (2 , '该订单已被冻结');
            }

            $uid = Session ::get ('user' , 'index')[ 'userid' ];
            if ( $order[ 'uid' ] == $uid && $order[ 'status' ] == 1 ) {
                //事务开始
                Db ::startTrans ();
                $config = Db ::name ('system') -> where ('1=1') -> find ();
                try {
                    //1修改订单状态为通过
                    $data[ 'status' ]      = 3;
                    $data[ 'tijiao_time' ] = time ();
                    Db ::name ('user_order') -> where (array ( 'id' => $order_id )) -> update ($data);
                    unset($data);

                    $money      = round ($order[ 'price' ] * $order[ 'yjjc' ] , 2);//我的佣金
                    $team_one   = round ($money * $config[ 'team_oneyj' ] , 2);//一级佣金
                    $team_two   = round ($money * $config[ 'team_twoyj' ] , 2);//二级拥金
                    $team_three = round ($money * $config[ 'team_threeyj' ] , 2);//三级佣金
                    $user       = Db ::name ('user') -> where ('userid' , $uid) -> field ('pid,gid,ggid,phone,my_money') -> find ();
                    //2 增加自己的钱和佣金
                    Db ::name ('user') -> where ('userid' , $uid) -> setInc ('my_money' , $money);
                    Db ::name ('user') -> where ('userid' , $uid) -> setInc ('profit_money' , $money);

                    //记录资金数组最后插入
                    $data = [
                        [
                            'uid'         => $uid ,
                            'phone'       => $user[ 'phone' ] ,
                            'type'        => 2 ,
                            'info'        => '抢单佣金' ,
                            'addtime'     => time () ,
                            'money'       => $money ,
                            'type_num'    => '+' ,
                            'money_start' => $user[ 'my_money' ] ,
                            'money_end'   => $user[ 'my_money' ] + $money
                        ] ,
                    ];

                    //3给三个上级返利并添加资金记录
                    if ( !empty($user[ 'pid' ])  ) {
                        $pid_user  = Db ::name ('user') -> where ('userid' , $user[ 'pid' ]) -> field ('my_money,phone') -> find ();
                        $info_data = array (
                            'uid'         => $user[ 'pid' ] ,
                            'phone'       => $pid_user[ 'phone' ] ,
                            'type'        => 3 ,
                            'info'        => '一级返佣' ,
                            'addtime'     => time () ,
                            'money'       => $team_one ,
                            'type_num'    => '+' ,
                            'money_start' => $pid_user[ 'my_money' ] ,
                            'money_end'   => $pid_user[ 'my_money' ] + $team_one
                        );
                        array_push ($data , $info_data);
                        Db ::name ('user') -> where ('userid' , $user[ 'pid' ]) -> setInc ('my_money' , $team_one);
                        unset($info_data);
                        unset($pid_user);
                    }
                    if ( !empty($user[ 'gid' ]) ) {
                        $gid_user  = Db ::name ('user') -> where ('userid' , $user[ 'gid' ]) -> field ('my_money,phone') -> find ();
                        $info_data = array (
                            'uid'         => $user[ 'gid' ] ,
                            'phone'       => $gid_user[ 'phone' ] ,
                            'type'        => 4 ,
                            'info'        => '二级返佣' ,
                            'addtime'     => time () ,
                            'money'       => $team_two ,
                            'type_num'    => '+' ,
                            'money_start' => $gid_user[ 'my_money' ] ,
                            'money_end'   => $gid_user[ 'my_money' ] + $team_two
                        );
                        array_push ($data , $info_data);
                        Db ::name ('user') -> where ('userid' , $user[ 'pid' ]) -> setInc ('my_money' , $team_two);
                        unset($info_data);
                        unset($gid_user);
                    }
                    if ( !empty($user[ 'ggid' ]) ) {
                        $ggid_user = Db ::name ('user') -> where ('userid' , $user[ 'ggid' ]) -> field ('my_money,phone') -> find ();
                        $info_data = array (
                            'uid'         => $user[ 'ggid' ] ,
                            'phone'       => $ggid_user[ 'phone' ] ,
                            'type'        => 5 ,
                            'info'        => '三级返佣' ,
                            'addtime'     => time () ,
                            'money'       => $team_three ,
                            'type_num'    => '+' ,
                            'money_start' => $ggid_user[ 'my_money' ] ,
                            'money_end'   => $ggid_user[ 'my_money' ] + $team_three
                        );
                        array_push ($data , $info_data);
                        Db ::name ('user') -> where ('userid' , $user[ 'pid' ]) -> setInc ('my_money' , $team_three);
                        unset($info_data);
                        unset($ggid_user);
                    }
                    //添加资金记录
                    Db ::name ('money_info') -> insertAll ($data);
                    unset($data);
                    // 提交事务
                    Db ::commit ();
                    $this -> return_msg (1 , '提交成功，佣金已到账');
                } catch ( \Exception $e ) {
                    // 回滚事务
                    Db ::rollback ();
                    $this -> return_msg (0 , '提交失败');
                }
            }
        }
    }

    /**
     *收单页面
     * @return mixed
     * User: lanzh
     * Date: 2020/2/17 0:38
     */
    public function single_info ()
    {
        $uid    = Session ::get ('user' , 'index')[ 'userid' ];
        $result = Db ::name ('user_order') -> where ('uid' , $uid) -> order ('addtime' , 'desc') -> select ();

        $this -> assign ('list' , $result);
        $this -> assign ('title' , '记录');
        return $this -> fetch ();
    }

    /**
     *解冻订单
     * User: lanzh
     * Date: 2020/2/20 11:37
     */
    public function order_thaw ()
    {

        $id    = intval ($this -> request -> param ('id'));
        $order = Db ::name ('user_order') -> field ('id,uid,status,dongjie_time') -> where ('id' , $id) -> find ();
        $uid   = Session ::get ('user' , 'index')[ 'userid' ];
        if ( $uid == $order[ 'uid' ] && $order[ 'status' ] == 2 && time () - $order[ 'dongjie_time' ] > 86400 ) {
            Db ::name ('user_order') -> where ('id' , $id) -> update (array ( 'status' => 1 , 'addtime' => time () ));
            $this -> return_msg (1 , '已解冻');
        }
        else {
            $this -> return_msg (0 , '解冻时间为' . date ('Y-m-d H:i:s' , $order[ 'dongjie_time' ] + 86400));
        }

    }

    /**
     *客服页面
     * @return mixed
     * User: lanzh
     * Date: 2020/2/17 0:39
     */
    public function customer ()
    {
        $config = Db ::name ('system') -> field ('kefu_link') -> where ('id' , 1) -> find ();
        $this -> assign ('title' , '客服');
        $this -> assign ('config' , $config);
        return $this -> fetch ();
    }

    /*随机生成订单号*/
    private function getordernum ( $length = 12 , $char = '0123456789' )
    {
        if ( !is_int ($length) || $length < 0 ) {
            return false;
        }
        $string = '';
        for ( $i = $length ; $i > 0 ; $i-- ) {
            $string .= $char[ mt_rand (0 , strlen ($char) - 1) ];
        }
        return 'N' . $string;
    }

}