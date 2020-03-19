<?php

namespace app\index\controller;

use think\facade\Session;
use think\Db;
use think\Validate;

/**
 * 充值提现类
 * Class Wealth
 * @package app\index\controller
 */
class Wealth extends Common
{

    /**
     *充值页面
     * @return mixed
     * User: lanzh
     * Date: 2020/2/17 21:19
     */
    public function index ()
    {
        $result = Db ::name ('system_bankcard') -> where ('status' , 1) -> select ();
        $this -> assign ('card' , $result);
        return $this -> fetch ();
    }

    /**
     *提现
     * @return mixed
     * Date: 2020/2/18 15:13
     */
    public function withdrawal ()
    {

        $uid    = Session ::get ('user' , 'index')[ 'userid' ];
        $user   = $this -> get_money ();
        $result = Db ::name ('withdrawal') -> where ('uid' , $uid) -> order ('addtime' , 'desc') -> select ();
        if ( empty($result) ) {
            $this -> assign ('empty' , '<li class="mui-table-view-cell notborder notbg" style="text-align: center;"><span
                style="color:#8f8f94;font-size:14px;">您暂时没有提现记录</span></li>');
        }
        $this -> assign ('user' , $user);
        $this -> assign ('list' , $result);
        return $this -> fetch ();
    }

    /**
     *提现页面
     * @return mixed
     * User: lanzh
     * Date: 2020/2/18 15:13
     */
    public function send_withdrawal ()
    {
        if ( $this -> request -> isAjax () ) {
            $data[ 'money' ]    = $this -> request -> param ('price');
            $data[ 'password' ] = $this -> request -> param ('password');

            $rule     = [
                'money|金额'      => 'require|number|egt:100' ,
                'password|提现密码' => 'require|chsDash|min:6' ,
            ];
            $validate = Validate ::make ($rule);
            $result   = $validate -> check ($data);
            if ( !$result ) {
                $this -> return_msg (0 , $validate -> getError ());
            }
            $uid     = Session ::get ('user' , 'index')[ 'userid' ];
            $user    = Db ::name ('user') -> field ('tx_pwd,my_money,phone,tx_status') -> where (array ( 'userid' => $uid )) -> find ();
            $card_id = Db ::name ('user_bank') -> field ('id') -> where (array (
                'uid'    => $uid ,
                'status' => 1
            )) -> find ();

            $count = Db ::name ('withdrawal') -> where (array ( 'status' => 1 , 'uid' => $uid )) -> count ();
            if ( $count > 0 ) {
                $this -> return_msg (0 , '已有一笔正在充值中，请勿重复提交');
            }

            if ( $user [ 'tx_status' ] != 1 ) {
                $this -> return_msg (0 , '您已被冻结提现功能');
            }

            $money_data = $this -> get_money ();
            if ( $data[ 'money' ] > $money_data[ 'user_money_k' ] ) {
                $this -> return_msg (0 , '当前可提现最大金额' . $money_data[ 'user_money_k' ]);
            }

            $start_time  = strtotime (date ("Y-m-d" , time ()));
            $order_count = Db ::name ('user_order') -> where ('addtime' , '>' , $start_time) -> count ();
            if ( $order_count < 60 && $data[ 'money' ] > ( $money_data[ 'user_money_k' ] * 0.1 ) ) {
                $this -> return_msg (0 , '您今日未完成60单,当前可提现最大金额' .  $money_data[ 'user_money_k' ] * 0.1 );
            }

            if ( $user && md5 ($data[ 'password' ]) != $user[ 'tx_pwd' ] ) {
                $this -> return_msg (0 , '提现密码错误');
            }

            $config = Db ::name ('system') -> field ('mix_withdraw,max_withdraw') -> where ('1=1') -> find ();

            if ( $data[ 'money' ] < $config[ 'mix_withdraw' ] || $data[ 'money' ] > $config[ 'max_withdraw' ] ) {
                $this -> return_msg (0 , '当前可提现金额为' . $config[ 'mix_withdraw' ] . '---' . $config[ 'max_withdraw' ]);
            }

            $data[ 'uid' ]     = $uid;
            $data[ 'phone' ]   = $user[ 'phone' ];
            $data[ 'status' ]  = 1;
            $data[ 'addtime' ] = time ();
            $data[ 'bank_id' ] = $card_id[ 'id' ];
            if ( Db ::name ('withdrawal') -> strict (false) -> insert ($data) ) {
                $this -> return_msg (1 , '提现已提交，请等待客服审核');
            }
            else {
                $this -> return_msg (0 , '提现失败');
            }
        }
        else {
            $uid  = Session ::get ('user' , 'index')[ 'userid' ];
            $bank = Db ::name ('user_bank') -> where (array ( 'uid' => $uid , 'status' => 1 )) -> find ();
            $user = Db ::name ('user') -> field ('tx_pwd') -> where (array ( 'userid' => $uid )) -> find ();
            $data = $this -> get_money ();
            $this -> assign ('data' , $data);
            $this -> assign ('bank' , $bank);
            $this -> assign ('user' , $user);
            return $this -> fetch ();
        }
    }

    /**
     *银行卡充值
     * @return mixed
     * User: lanzh
     * Date: 2020/2/17 21:19
     */
    public function wealth_card ()
    {

        $id   = intval ($this -> request -> param ('id'));
        $user = Session ::get ('user' , 'index');
        $conf = Db ::name ('system') -> where (array ( 'id' => 1 )) -> find ();
        $bank = Db ::name ('system_bankcard') -> where (array ( 'id' => $id )) -> find ();
        $this -> assign ('conf' , $conf);
        $this -> assign ('user' , $user);
        $this -> assign ('card' , $bank);
        return $this -> fetch ();
    }

    /**
     *充值日志
     * User: lanzh
     * Date: 2020/2/18 11:42
     */
    public function wealth_log ()
    {
        $uid    = Session ::get ('user' , 'index')[ 'userid' ];
        $result = Db ::name ('recharge') -> where ('uid' , $uid) -> order ('addtime' , 'desc') -> select ();
        if ( empty($result) ) {
            $this -> assign ('empty' , '<li class="mui-table-view-cell notborder notbg" style="text-align: center;"><span
                style="color:#8f8f94;font-size:14px;">您暂时没有充值成功的记录</span></li>');
        }

        $this -> assign ('list' , $result);
        return $this -> fetch ();
    }

    //获取银行卡
    public function yhkrandom ()
    {

        $system_bank = Db ::name ('system_bankcard') -> order ('count' , 'asc') -> find ();
        if ( empty($system_bank) ) {
            $this -> return_msg (0 , '分配付款方式失败');
        }
        else {
            Db ::name ('system_bankcard') -> where (array ( 'id' => $system_bank[ 'id' ] )) -> setInc ('count');
            $data = [
                "cz_yh"   => $system_bank[ 'bankname' ] ,
                "cz_xm"   => $system_bank[ 'username' ] ,
                "cz_kh"   => $system_bank[ 'banknum' ] ,
                "bank_id" => $system_bank[ 'id' ] ,
            ];
            $this -> return_msg (1 , 'success' , $data);
        }
    }

    /**
     *提交充值
     * User: lanzh
     * Date: 2020/2/18 10:45
     */
    public function bank_rc ()
    {

        $data[ 'uid' ]      = $this -> request -> param ('uname');
        $data[ 'bankid' ]   = $this -> request -> param ('bankid');
        $data[ 'phone' ]    = $this -> request -> param ('account');
        $data[ 'banktype' ] = $this -> request -> param ('banktype');
        $data[ 'money' ]    = $this -> request -> param ('myprice');
        $rule               = [
            'uid|用户ID'       => 'require|number' ,
            'bankid|银行卡ID'   => 'require|number' ,
            'phone|用户手机号码'   => 'require|number|length:11' ,
            'banktype|银行卡类型' => 'require|number' ,
            'money|充值金额'     => 'require|float' ,
        ];

        $validate = Validate ::make ($rule);
        $result   = $validate -> check ($data);
        if ( !$result ) {
            $this -> return_msg (0 , $validate -> getError ());
        }

        if ($data['money'] < 100){
            $this -> return_msg (0 , '充值金额必需大于等于100');
        }
        $result = Db ::name ('user') -> field ('userid,phone') -> where ('phone' , $data[ 'phone' ]) -> find ();

        if ( !$result || $result[ 'userid' ] != $data[ 'uid' ] ) {
            $this -> return_msg (0 , '用户id和账号不匹配');
        }

        $count = Db ::name ('recharge') -> where (array ( 'status' => 1 , 'phone' => $result[ 'phone' ] )) -> count ();
        if ( $count > 0 ) {
            $this -> return_msg (0 , '已有一笔正在充值中，请勿重复提交');
        }

        $data[ 'status' ]  = 1;
        $data[ 'addtime' ] = time ();
        if ( Db ::name ('recharge') -> insert ($data) && Db ::name ('system_bankcard') -> where ('id' , $data[ 'bankid' ]) -> setInc ('count' , 1) ) {
            $this -> return_msg (1 , '提交成功');
        }
        else {
            $this -> return_msg (0 , '提交失败');
        }
    }

    public function wealth_info ()
    {
        return $this -> fetch ();
    }

    /**
     *获取用户可用的钱
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * User: lanzh
     * Date: 2020/2/25 15:00
     */
    public function get_money ()
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
        $data[ 'user_money_k' ] = round ($data[ 'user_money' ] - ( $data[ 'user_money_y' ] + $data[ 'tx_money' ] + $data[ 'dj_money' ] ) , 2);
        return $data;
    }
}