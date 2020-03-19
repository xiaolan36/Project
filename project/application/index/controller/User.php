<?php

namespace app\index\controller;

use think\Db;
use think\facade\Session;
use think\Validate;

class User extends Common
{

    protected function initialize ( $uid = 0 )
    {
        parent ::initialize ();

        if ( $this -> request -> isGet () ) {
            $this -> assign ('title' , '我的');
        }
    }

    /**
     *我的页面
     * @return mixed
     * User: lanzh
     * Date: 2020/2/17 0:39
     */
    public function index ()
    {
        $uid  = Session ::get ('user' , 'index')[ 'userid' ];
        $user = Db ::name ('user') -> where ('userid' , $uid) -> field ('my_money,profit_money,userid,truename,phone,user_code') -> find ();
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
        $user[ 'my_money' ] = $user[ 'my_money' ] - ( $data[ 'user_money_y' ] + $data[ 'tx_money' ] + $data[ 'dj_money' ] );
        $user[ 'nick' ]     = empty($user[ 'truename' ]) ? $user[ 'phone' ] : $user[ 'truename' ];
        $this -> assign ('user' , $user);
        return $this -> fetch ();
    }

    /**
     *资金明细
     * User: lanzh
     * Date: 2020/2/20 11:40
     */
    public function user_money_info ()
    {
        $uid    = Session ::get ('user' , 'index')[ 'userid' ];
        $result = Db ::name ('money_info') -> where ('uid' , $uid) -> order ('addtime' , 'desc') -> select ();

        if ( empty($result) ) {
            $this -> assign ('empty' , '<li class="mui-table-view-cell notborder notbg" style="text-align: center;"><span
                style="color:#8f8f94;font-size:14px;">您暂时没有提现记录</span></li>');
        }
        else {
            foreach ( $result as $key => $val ) {
                $result[ $key ][ 'money' ] = round ($val[ 'money' ] , 2);
            }
        }
        $this -> assign ('list' , $result);
        return $this -> fetch ();
    }

    /**
     *直属下线
     * User: lanzh
     * Date: 2020/2/20 11:56
     */

    public function user_team ()
    {
        //cursor生成器查询减少大量数据查询的内存
        $cursor = Db ::name ('user') -> field ('userid,pid,phone,my_money,reg_time') -> order ('reg_time' , 'desc') -> cursor ();
        $uid    = Session ::get ('user' , 'index')[ 'userid' ];
        foreach ( $cursor as $result ) {
            $ulist[] = $result;
        }
        unset($cursor);

        //递归所有下级
        $res = $this -> recursion_user ($ulist , $uid , 0);
        $res = $this -> recursion_user_count ($res);

        //数组重组
        $new_user = array ();
        foreach ( $res as $key => $val ) {
            $val[ 'mobile' ] = $val[ 'mobile' ] = mb_substr ($val[ 'mobile' ] , 0 , 3) . '***' . substr ($val[ 'mobile' ] , -4);
            if ( $val[ 'reg_time' ] > strtotime (date ('Y-m-d')) ) {
                $val[ 'new' ] = 1;
            }
            $val[ 'reg_time' ]                   = date ('Y-m-d H:i' , $val[ 'reg_time' ]);
            $new_user[ $val[ 'id' ] ]            = $val;
            $new_user[ $val[ 'id' ] ][ 'count' ] = 0;
        }
        unset($res);
        unset($ulist);

        //下级人数
        foreach ( $new_user as $key => $val ) {
            if ( isset($new_user[ $val[ 'pid' ] ]) ) {
                $new_user[ $val[ 'pid' ] ][ 'count' ] += 1;
            }
        }

        //团队统计
        $team = [
            [
                'my_profit'    => 0 ,
                'profit'       => 0 ,
                'recharge'     => 0 ,
                'withdraw'     => 0 ,
                'sum_user'     => 0 ,
                'one_recharge' => 0 ,
                'two_recharge' => 0
            ] ,
            [
                'my_profit'    => 0 ,
                'profit'       => 0 ,
                'recharge'     => 0 ,
                'withdraw'     => 0 ,
                'sum_user'     => 0 ,
                'one_recharge' => 0 ,
                'two_recharge' => 0
            ] ,
            [
                'my_profit'    => 0 ,
                'profit'       => 0 ,
                'recharge'     => 0 ,
                'withdraw'     => 0 ,
                'sum_user'     => 0 ,
                'one_recharge' => 0 ,
                'two_recharge' => 0
            ]
        ];

        $new_user_list = array ( 'one' => array () , 'two' => array () , 'three' => array () );
        //各级总人数
        $ids = '';
        foreach ( $new_user as $key => $val ) {

            //首充二充人数
            $num = Db ::name ('recharge') -> where ('status' , 3) -> where ('uid' , $val[ 'id' ]) -> count ();
            if ( $num >= 2 ) {
                $team[ intval ($val[ 'level' ]) - 1 ][ 'two_recharge' ] += 1;
            }
            else {
                if ( $num >= 1 ) {
                    $team[ intval ($val[ 'level' ]) - 1 ][ 'one_recharge' ] += 1;
                }
            }

            switch ( intval ($val[ 'level' ]) ) {
                case 1:
                    $new_user_list[ 'one' ][] = $val;
                    $team[ 0 ][ 'sum_user' ]  += 1;
                    break;
                case 2:
                    $new_user_list[ 'two' ][] = $val;
                    $team[ 1 ][ 'sum_user' ]  += 1;
                    break;
                case 3:
                    $new_user_list[ 'three' ][] = $val;
                    $team[ 2 ][ 'sum_user' ]    += 1;
                    break;
                default:
                    break;
            }
            $ids .= $val[ 'id' ] . ',';
        }
        //统计佣金充值提现
        $cursor = Db ::name ('money_info') -> alias ('a') -> join ('user b' , 'a.uid=b.userid') -> where ('b.test <> 1') -> whereIn ('a.type' , '2,3,4,5,6,7') -> whereIn ('a.uid' , $ids) -> field ('a.uid,a.type,a.money') -> cursor ();
        foreach ( $cursor as $val ) {
            if ( isset($new_user[ $val[ 'uid' ] ]) ) {
                //抢单佣金
                if ( $val[ 'type' ] == 2 && $new_user[ $val[ 'uid' ] ][ 'level' ] == 1 ) {
                    $team[ 0 ][ 'profit' ] += $val[ 'money' ];
                }
                else {
                    if ( $val[ 'type' ] == 2 && $new_user[ $val[ 'uid' ] ][ 'level' ] == 2 ) {
                        $team[ 1 ][ 'profit' ] += $val[ 'money' ];
                    }
                    else {
                        if ( $val[ 'type' ] == 2 && $new_user[ $val[ 'uid' ] ][ 'level' ] == 3 ) {
                            $team[ 2 ][ 'profit' ] += $val[ 'money' ];
                        }
                        else {
                            //给我的佣金
                            if ( $val[ 'type' ] == 3 ) {
                                $team[ 0 ][ 'my_profit' ] += $val[ 'money' ];
                            }
                            else {
                                if ( $val[ 'type' ] == 4 ) {
                                    $team[ 1 ][ 'my_profit' ] += $val[ 'money' ];
                                }
                                else {
                                    if ( $val[ 'type' ] == 5 ) {
                                        $team[ 2 ][ 'my_profit' ] += $val[ 'money' ];
                                    }
                                    else {
                                        //充值
                                        if ( $val[ 'type' ] == 6 && $new_user[ $val[ 'uid' ] ][ 'level' ] == 1 ) {
                                            $team[ 0 ][ 'recharge' ] += $val[ 'money' ];
                                        }
                                        else {
                                            if ( $val[ 'type' ] == 6 && $new_user[ $val[ 'uid' ] ][ 'level' ] == 2 ) {
                                                $team[ 1 ][ 'recharge' ] += $val[ 'money' ];
                                            }
                                            else {

                                                if ( $val[ 'type' ] == 6 && $new_user[ $val[ 'uid' ] ][ 'level' ] == 3 ) {
                                                    $team[ 2 ][ 'recharge' ] += $val[ 'money' ];
                                                }
                                                else {

                                                    //提现
                                                    if ( $val[ 'type' ] == 7 && $new_user[ $val[ 'uid' ] ][ 'level' ] == 1 ) {
                                                        $team[ 0 ][ 'withdraw' ] += $val[ 'money' ];
                                                    }
                                                    else {
                                                        if ( $val[ 'type' ] == 7 && $new_user[ $val[ 'uid' ] ][ 'level' ] == 2 ) {
                                                            $team[ 1 ][ 'withdraw' ] += $val[ 'money' ];
                                                        }
                                                        else {

                                                            if ( $val[ 'type' ] == 7 && $new_user[ $val[ 'uid' ] ][ 'level' ] == 3 ) {
                                                                $team[ 2 ][ 'withdraw' ] += $val[ 'money' ];
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        unset($cursor);
        $this -> assign ('empty' , '<ul class="mui-table-view ullist"><li class="mui-table-view-cell mui-collapse-content"><p style="text-align: center;">暂时没有记录</p> </li></ul>');
        $this -> assign ('team' , $team);
        $this -> assign ('list' , $new_user_list);
        return $this -> fetch ();
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
                if ( $val[ 'level' ] > 3 ) {
                    continue;
                }
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
                'id'       => $val[ 'userid' ] ,
                'pid'      => $val[ 'pid' ] ,
                'level'    => $val[ 'level' ] ,
                'mobile'   => $val[ 'phone' ] ,
                'money'    => $val[ 'my_money' ] ,
                'reg_time' => $val[ 'reg_time' ]
            );
            if ( isset($val[ 'children' ]) ) {

                $rp     = $this -> recursion_user_count ($val[ 'children' ]);
                $result = array_merge ($result , $rp);
            }
        }
        return $result;
    }

    /**
     *设置
     * @return mixed
     * User: lanzh
     * Date: 2020/2/17 0:39
     */
    public function user_set ()
    {
        return $this -> fetch ();
    }

    /**
     *分享
     * User: lanzh
     * Date: 2020/2/20 15:33
     */
    public function user_share ()
    {
        $userid = Session ::get ('user' , 'index')[ 'userid' ];
        $u_ID   = Db ::name ('user') -> where (array ( 'userid' => $userid )) -> field ('user_code') -> find ()[ 'user_code' ];
        $aurl   = "http://" . $_SERVER[ 'SERVER_NAME' ] . url ('/Login/register/' . $u_ID);
        $config = Db ::name ('system') -> where (array ( 'id' => 1 )) -> find ();
        $this -> assign ('u_ID' , $u_ID);
        $this -> assign ('aurl' , $aurl);
        $this -> assign ('config' , $config);
        return $this -> fetch ();
    }

    /**
     *地址管理
     * @return mixed
     * User: lanzh
     * Date: 2020/2/17 18:00
     */
    public function user_address ()
    {

        $uid    = Session ::get ('user' , 'index')[ 'userid' ];
        $result = Db ::name ('user_address') -> where ('uid' , $uid) -> select ();
        $this -> assign ('info' , $result);
        return $this -> fetch ();
    }

    /**
     *添加修改地址
     * @return mixed
     * User: lanzh
     * Date: 2020/2/17 18:00
     */
    public function address_add ()
    {

        if ( $this -> request -> isAjax () ) {
            $data[ 'username' ]     = trim ($this -> request -> param ('name'));
            $data[ 'address' ]      = trim ($this -> request -> param ('city'));
            $data[ 'address_info' ] = trim ($this -> request -> param ('address'));
            $data[ 'phone' ]        = trim ($this -> request -> param ('tel'));
            $data[ 'code' ]         = trim ($this -> request -> param ('code'));
            $id                     = trim ($this -> request -> param ('id'));

            $rule = [
                'phone|手机号码'        => 'require|number|length:11' ,
                'code|邮编'           => 'require|number|min:6' ,
                'username|收件人'      => 'require|chsDash' ,
                'address|地区'        => 'require|chsDash' ,
                'address_info|详细地址' => 'require|chsDash' ,
            ];

            $validate = Validate ::make ($rule);

            $result = $validate -> check ($data);
            if ( !$result ) {
                $this -> return_msg (0 , $validate -> getError ());
            }
            $uid           = Session ::get ('user' , 'index')[ 'userid' ];
            $data[ 'uid' ] = $uid;

            if ( empty($id) ) {
                $count = Db ::name ('user_address') -> where (array ( 'uid' => $uid )) -> count ();
                if ( $count > 0 ) {
                    $data[ 'status' ] = 0;
                }
                else {
                    $data[ 'status' ] = 1;
                }
                $result = Db ::name ('user_address') -> insert ($data);
            }
            else {
                $result = Db ::name ('user_address') -> where ('id' , $id) -> update ($data);
            }

            if ( $result ) {
                $this -> return_msg (1 , '提交成功');
            }
            else {
                $this -> return_msg (0 , '提交失败');
            }

        }
        else {
            $id     = $this -> request -> param ('id');
            $uid    = Session ::get ('user' , 'index')[ 'userid' ];
            $result = Db ::name ('user_address') -> where (array ( 'id' => $id , 'uid' => $uid )) -> find ();
            $this -> assign ('info' , $result);
            $this -> assign ('id' , $id);
            return $this -> fetch ();

        }
    }

    /**
     *地址设为默认
     * User: lanzh
     * Date: 2020/2/17 18:00
     */
    public function user_address_def ()
    {
        $id  = $this -> request -> param ('id');
        $uid = Session ::get ('user' , 'index')[ 'userid' ];
        // 启动事务
        Db ::startTrans ();
        try {
            Db ::name ('user_address') -> where ('uid' , $uid) -> update (array ( 'status' => 0 ));
            Db ::name ('user_address') -> where (array (
                'id'  => $id ,
                'uid' => $uid
            )) -> update (array ( 'status' => 1 ));
            // 提交事务
            Db ::commit ();
            $this -> return_msg (1 , '设置成功');
        } catch ( \Exception $e ) {
            // 回滚事务
            Db ::rollback ();
            $this -> return_msg (0 , '设置失败');
        }
    }

    /**
     *删除地址
     * Date: 2020/2/17 18:01
     */
    public function user_address_del ()
    {
        $id     = $this -> request -> param ('id');
        $uid    = Session ::get ('user' , 'index')[ 'userid' ];
        $result = Db ::name ('user_address') -> where (array ( 'id' => $id , 'uid' => $uid )) -> delete ();
        if ( $result ) {
            $this -> return_msg (1 , '删除成功');
        }
        else {
            $this -> return_msg (1 , '删除失败');
        }
    }


//    -------------------------------------

    /**
     *银行卡管理
     * @return mixed
     * User: lanzh
     * Date: 2020/2/17 18:00
     */
    public function user_bank ()
    {

        $uid    = Session ::get ('user' , 'index')[ 'userid' ];
        $result = Db ::name ('user_bank') -> where ('uid' , $uid) -> select ();
        $this -> assign ('info' , $result);
        return $this -> fetch ();
    }

    /**
     *添加修改银行卡
     * @return mixed
     * User: lanzh
     * Date: 2020/2/17 18:00
     */
    public function bank_add ()
    {

        if ( $this -> request -> isAjax () ) {
            $data[ 'username' ]     = trim ($this -> request -> param ('name'));
            $data[ 'address' ]      = trim ($this -> request -> param ('city'));
            $data[ 'address_info' ] = trim ($this -> request -> param ('address'));
            $data[ 'phone' ]        = trim ($this -> request -> param ('tel'));
            $id                     = trim ($this -> request -> param ('id'));

            $rule = [
                'phone|银行卡号'        => 'require|number|min:15|max:19' ,
                'username|持卡人'      => 'require|chsDash' ,
                'address|所属银行'      => 'require|chsDash' ,
                'address_info|支行名称' => 'require|chsDash' ,
            ];

            $validate = Validate ::make ($rule);

            $result = $validate -> check ($data);
            if ( !$result ) {
                $this -> return_msg (0 , $validate -> getError ());
            }
            $uid           = Session ::get ('user' , 'index')[ 'userid' ];
            $data[ 'uid' ] = $uid;

            $count_card = Db ::name ('user_bank') -> where (array ( 'phone' => $data[ 'phone' ] )) -> count ();

            if ( $count_card > 0 ) {
                $this -> return_msg (1 , '该银行卡已被绑定');
            }

            if ( empty($id) ) {
                $count = Db ::name ('user_bank') -> where (array ( 'uid' => $uid )) -> count ();
                if ( $count > 0 ) {
                    $data[ 'status' ] = 0;
                }
                else {
                    $data[ 'status' ] = 1;
                }

                $result = Db ::name ('user_bank') -> insert ($data);
            }
            else {
                $result = Db ::name ('user_bank') -> where ('id' , $id) -> update ($data);
            }

            if ( $result ) {
                $this -> return_msg (1 , '提交成功');
            }
            else {
                $this -> return_msg (0 , '提交失败');
            }
        }
        else {
            $id     = $this -> request -> param ('id');
            $uid    = Session ::get ('user' , 'index')[ 'userid' ];
            $result = Db ::name ('user_bank') -> where (array ( 'id' => $id , 'uid' => $uid )) -> find ();
            $this -> assign ('info' , $result);
            $this -> assign ('id' , $id);
            return $this -> fetch ();

        }
    }

    /**
     *地址设为默认
     * User: lanzh
     * Date: 2020/2/17 18:00
     */
    public function user_bank_def ()
    {
        $id  = $this -> request -> param ('id');
        $uid = Session ::get ('user' , 'index')[ 'userid' ];
        // 启动事务
        Db ::startTrans ();
        try {
            Db ::name ('user_bank') -> where ('uid' , $uid) -> update (array ( 'status' => 0 ));
            Db ::name ('user_bank') -> where (array (
                'id'  => $id ,
                'uid' => $uid
            )) -> update (array ( 'status' => 1 ));
            // 提交事务
            Db ::commit ();
            $this -> return_msg (1 , '设置成功');
        } catch ( \Exception $e ) {
            // 回滚事务
            Db ::rollback ();
            $this -> return_msg (0 , '设置失败');
        }
    }

    /**
     *删除地址
     * Date: 2020/2/17 18:01
     */
    public function user_bank_del ()
    {
        $id     = $this -> request -> param ('id');
        $uid    = Session ::get ('user' , 'index')[ 'userid' ];
        $result = Db ::name ('user_bank') -> where (array ( 'id' => $id , 'uid' => $uid )) -> delete ();
        if ( $result ) {
            $this -> return_msg (1 , '删除成功');
        }
        else {
            $this -> return_msg (1 , '删除失败');
        }
    }

    /**
     *修改密码
     * @return mixed
     * User: lanzh
     * Date: 2020/2/17 0:39
     */
    public function edit_pwd ()
    {
        if ( $this -> request -> isAjax () ) {
            $data[ 'password' ]   = trim ($this -> request -> param ('password'));
            $data[ 'c_password' ] = trim ($this -> request -> param ('c_password'));
            $data[ 'code' ]       = trim ($this -> request -> param ('code'));
            $data[ 'moblie' ]     = trim ($this -> request -> param ('moblie'));
            $data[ 'type' ]       = trim ($this -> request -> param ('type'));
            $rule                 = [
                'moblie|手机号码'     => 'require|mobile' ,
                'code|验证码'        => 'require|number|length:6' ,
                'password|登录密码'   => 'require|alphaDash|min:6' ,
                'c_password|二次密码' => 'require|confirm:password' ,
                'type|修改类型'       => 'require'
            ];

            $validate = Validate ::make ($rule);
            $result   = $validate -> check ($data);
            if ( !$result ) {
                $this -> return_msg (0 , $validate -> getError ());
            }

            $code = Db ::name ('smscode') -> where (array (
                'phone' => $data[ 'moblie' ] ,
                'type'  => 'backpwd'
            )) -> order ('send_time' , 'desc') -> find ();
            if ( !$code || $data[ 'code' ] != $code[ 'code' ] || time () - $code[ 'send_time' ] > 30000 ) {
                $this -> return_msg (0 , '验证码错误或已失效');
            }

            if ( $data[ 'type' ] == 'login' ) {
                $salt                 = rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9);
                $sava[ 'login_salt' ] = $salt;
                $sava[ 'login_pwd' ]  = md5 (md5 ($data[ 'password' ]) . $sava[ 'login_salt' ]);
            }
            else {
                if ( $data[ 'type' ] = 'withdrawal' ) {
                    $sava[ 'tx_pwd' ] = md5 ($data[ 'password' ]);
                }
            }
            $uid = Session ::get ('user' , 'index')[ 'userid' ];
            $re  = Db ::name ('user') -> where (array ( 'userid' => $uid )) -> update ($sava);
            if ( $re ) {
                $num = $data[ 'type' ] == 'login' ? 2 : 1;
                if ( $num == 2 ) {
                    session_destroy ();
                }
                $this -> return_msg ($num , '修改成功');
            }
            else {
                $this -> return_msg (0 , '修改失败');
            }
        }
        else {
            $phone = Session ::get ('user' , 'index')[ 'phone' ];
            $this -> assign ('phone' , $phone);
            return $this -> fetch ();
        }

    }

    //个人信息
    public function user_info ()
    {

        if ( $this -> request -> isAjax () ) {
            $rule = [
                'qq|qq'         => 'require|number' ,
                'weixin|微信'     => 'require|alphaDash' ,
                'truename|真实姓名' => 'require|chs' ,
            ];

            $validate = Validate ::make ($rule);
            $result   = $validate -> check ($this -> request -> param ());
            if ( !$result ) {
                $this -> return_msg (0 , $validate -> getError ());
            }
            $uid    = Session ::get ('user' , 'index')[ 'userid' ];
            $result = Db ::name ('user') -> where ('userid' , $uid) -> update ($this -> request -> param ());

            if ( $result ) {
                $this -> return_msg (1 , '修改成功');
            }
            else {
                $this -> return_msg (0 , '修改失败');
            }
        }
        else {

            $uid  = Session ::get ('user' , 'index')[ 'userid' ];
            $user = Db ::name ('user') -> where ('userid' , $uid) -> field ('qq,weixin,truename,userid') -> find ();
            $this -> assign ('info' , $user);
            return $this -> fetch ();
        }

    }

    /**
     *退出登录
     * User: lanzh
     * Date: 2020/2/17 0:43
     */
    public function loginout ()
    {

        Session ::clear ('index');
        $this -> redirect ('Login/login');
    }

}