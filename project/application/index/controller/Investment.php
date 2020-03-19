<?php

namespace app\index\controller;

use think\Db;
use think\facade\Session;
use think\Validate;

/**
 * 理财类
 * Class Investment
 * @package app\index\controller
 */
class Investment extends Common
{
    /**
     *主页
     * @return mixed
     * User: lanzh
     * Date: 2020/2/18 17:32
     */
    public function index ()
    {
        $uid = Session ::get ('user' , 'index')[ 'userid' ];
        //用户余额
        $data[ 'user_money' ] = round (Db ::name ('user') -> field ('my_money') -> where ('userid' , $uid) -> find ()[ 'my_money' ] , 2);
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
        //冻结的钱
        $data[ 'dj_money' ] = round (Db ::name ('user_order') -> where (array (
            'status' => 2 ,
            'uid'    => $uid
        )) -> sum ('price') , 2);

        //可存入的钱
        $data[ 'user_money_k' ] = $data[ 'user_money' ] - ( $data[ 'user_money_y' ] + $data[ 'tx_money' ] + $data[ 'dj_money' ] );
        $result                 = Db ::name ('investment_rate') -> field ('day,rate,id') -> select ();
        $this -> assign ('data' , $data);
        $this -> assign ('list' , $result);
        return $this -> fetch ();
    }

    /**
     *理财列表
     * @return mixed
     * User: lanzh
     * Date: 2020/2/18 17:32
     */
    public function investment_info ()
    {
        $uid  = Session ::get ('user' , 'index')[ 'userid' ];
        $list = Db ::name ('investment_info') -> where (array ( 'uid' => $uid )) -> order ('addtime' , 'desc') -> select ();
        foreach ( $list as $key => $val ) {

            $list[ $key ][ 'times' ] = time () > $val[ 'endtime' ] ? 1 : 2;
        }
        $this -> assign ('list' , $list);
        return $this -> fetch ();
    }

    /**
     *领取利息
     * User: lanzh
     * Date: 2020/2/19 18:15
     */
    public function investment_get ( $id )
    {
        $uid   = Session ::get ('user' , 'index')[ 'userid' ];
        $phone = Session ::get ('user' , 'index')[ 'phone' ];
        $id    = intval ($this -> request -> param ('id'));
        $info  = Db ::name ('investment_info') -> where (array ( 'id' => $id )) -> find ();
        if ( $info[ 'status' ] == 2 ) {
            $this -> return_msg (0 , '已领取');
        }
        $user_money = Db ::name ('user') -> field ('my_money') -> where ('userid' , $uid) -> find ();
        if ( $info && time () > $info[ 'endtime' ] && $info[ 'uid' ] == $uid ) {
            $money = round ($info[ 'num' ] * $info[ 'rate' ] / 100 * $info[ 'days' ] , 2);
            Db ::startTrans ();
            try {
                //修改收益信息
                $data[ 'yqsy' ]      = $money;
                $data[ 'sjsy' ]      = $money;
                $data[ 'sj_rate' ]   = $info[ 'rate' ];
                $data[ 'draw_time' ] = time ();
                $data[ 'status' ]    = 2;
                Db ::name ('investment_info') -> where ('id' , $id) -> update ($data);
                unset($data);
                //记录资金
                $data[ 'uid' ]         = $uid;
                $data[ 'phone' ]       = $phone;
                $data[ 'type' ]        = 1;
                $data[ 'info' ]        = '理财收益利息';
                $data[ 'addtime' ]     = time ();
                $data[ 'money' ]       = $money;
                $data[ 'type_num' ]    = '+';
                $data[ 'money_start' ] = $user_money[ 'my_money' ];
                $data[ 'money_end' ]   = $user_money[ 'my_money' ] + $money;
                Db ::name ('money_info') -> insert ($data);
                unset($data);
                //增加资金
                Db ::name ('user') -> where ('userid' , $uid) -> setInc ('my_money' , $money);
                // 提交事务
                Db ::commit ();
                $this -> return_msg (1 , '领取成功');
            } catch ( \Exception $e ) {
                // 回滚事务
                Db ::rollback ();
                $this -> return_msg (0 , '领取失败');
            }
        }
    }

    /**
     *提交理财申请
     * User: lanzh
     * Date: 2020/2/18 17:32
     */
    public function send_investment ()
    {

        $data[ 'num' ]     = trim ($this -> request -> param ('price'));
        $data[ 'rate_id' ] = trim ($this -> request -> param ('rate_id'));

        $rule = [
            'num|存入金额'   => 'require|number|egt:100' ,
            'rate_id|周期' => 'require|number' ,
        ];

        $validate = Validate ::make ($rule);
        $result   = $validate -> check ($data);
        if ( !$result ) {
            $this -> return_msg (0 , $validate -> getError ());
        }

        $rate = Db ::name ('investment_rate') -> where (array ( 'id' => $data[ 'rate_id' ] )) -> find ();
        if ( empty($rate) ) {
            $this -> return_msg (0 , '周期错误');
        }

        $uid = Session ::get ('user' , 'index')[ 'userid' ];

        $user = Db ::name ('user') -> field ('my_money') -> where (array ( 'userid' => $uid )) -> find ();
        //提现未审核的钱
        $tx_money = Db ::name ('withdrawal') -> where (array ( 'status' => 1 , 'uid' => $uid )) -> sum ('money');
        //存入未到期的钱
        $info_money = Db ::name ('investment_info') -> where (array ( 'status' => 1 , 'uid' => $uid )) -> sum ('num');
        //冻结
        $dj_money = Db ::name ('user_order') -> where (array (
            'status' => 2 ,
            'uid'    => $uid
        )) -> sum ('price');

        $money = round ($user[ 'my_money' ] - $tx_money - $info_money - $dj_money , 2);
        if ( $data[ 'num' ] > $money ) {
            $this -> return_msg (0 , '当前可存入余额' . $money);
        }

        $data[ 'uid' ]     = $uid;
        $data[ 'days' ]    = $rate[ 'day' ];
        $data[ 'addtime' ] = time ();
        $data[ 'endtime' ] = time () + $rate[ 'day' ] * 86400;
        $data[ 'rate' ]    = $rate[ 'rate' ];
        $data[ 'yqsy' ]    = $data[ 'num' ] * $rate[ 'rate' ] * $rate[ 'day' ] / 100;
        $data[ 'status' ]  = 1;
        if ( Db ::name ('investment_info') -> strict (false) -> insert ($data) ) {
            $this -> return_msg (1 , '存入成功');
        }
        else {
            $this -> return_msg (0 , '存入失败');
        }

    }
}