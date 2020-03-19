<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Session;

/**
 * 充值提现类
 * Class Wealth
 * @package app\admin\controller
 */
class Wealth extends Common
{

    /**
     *充值页面
     * User: lanzh
     * Date: 2020/2/21 15:56
     */
    public function recharge ()
    {

        $param = $this -> params;
        $map   = [];
        if ( !empty($param) ) {

            //状态不为空
            if ( isset($param[ 'statusifyID' ]) && !empty($param[ 'statusifyID' ]) ) {
                $map[] = [ 'a.status' , 'eq' , $param[ 'statusifyID' ] ];

            }

            //搜索内容不为空
            if ( isset($param[ 'searchInput' ]) && !empty($param[ 'searchInput' ]) ) {
                if ( strlen ($param[ 'searchInput' ]) == 11 ) {
                    $map[] = [ 'a.phone' , 'eq' , $param[ 'searchInput' ] ];
                }
                else {
                    $map[] = [ 'a.uid' , 'eq' , $param[ 'searchInput' ] ];

                }
            }

            //时间戳不为空
            if ( isset($param[ 'start' ]) && isset($param[ 'end' ]) && !empty($param[ 'start' ]) && !empty($param[ 'end' ]) ) {
                $a     = strtotime ($param[ 'start' ]);
                $b     = strtotime ($param[ 'end' ]) + 86400;
                $map[] = [ 'a.addtime' , 'between' , "$a,$b" ];
            }
            else {
                if ( isset($param[ 'start' ]) && !empty($param[ 'start' ]) ) {
                    $a     = strtotime ($param[ 'start' ]);
                    $b     = strtotime ($param[ 'start' ]) + 86400;
                    $map[] = [ 'a.addtime' , 'between' , "$a,$b" ];
                }
            }
        }
        $list = Db ::name ('recharge') -> alias ('a') -> join ('api_system_bankcard b ' , 'b.id= a.bankid') -> field ('a.*,b.bankname,b.username,b.banknum') -> where ($map) -> order ('a.addtime' , 'desc') -> paginate (10 , false , [ 'query' => request () -> param () ]) -> each (function ( $item , $key ) {
            $item[ 'addtime' ]  = date ('Y-m-d H:i' , $item[ 'addtime' ]);
            $item[ 'end_time' ] = empty($item[ 'end_time' ]) ? '未操作' : date ('Y-m-d H:i' , $item[ 'end_time' ]);
            return $item;
        });
        $page = $list -> render ();
        return $this -> fetch ('recharge' , [
            'list'   => $list ,
            'page'   => $page ,
            'title'  => '出入账管理' ,
            'title2' => '充值管理' ,
        ]);
    }

    /**
     * 修改充值状态
     *
     * @param int $[id] [用户 id]
     * @param int $[status] [<状态>]
     *
     * @return [json] [修改结果]
     */
    public function recharge_status ()
    {
        $param             = $this -> params;
        $param[ 'status' ] = $param[ 'status' ] == 1 ? 3 : 2;
        $info              = $param[ 'status' ] == 3 ? '通过' : '拒绝';

        if ( !$this -> request -> isAjax () ) {
            $this -> return_msg (400 , '错误的提交方式');
        }

        if ( Db ::name ('recharge') -> where ('id' , $this -> params[ 'id' ]) -> value ('status') == 3 ) {
            $this -> return_msg (400 , '请勿重复提交');
        }

        // 启动事务
        Db ::startTrans ();
        try {

            Db ::name ('recharge') -> where (array (
                'id'     => $param[ 'id' ] ,
                'uid'    => $param[ 'uid' ] ,
                'money'  => $param[ 'money' ] ,
                'status' => '1' ,
            )) -> update ([
                'status'   => $param[ 'status' ] ,
                'end_time' => time () ,
                'info'     => Session ::get ('admin')[ 'name' ] . '于' . date ('Y-m-d H:i' , time ()) . $info ,

            ]);

            if ( $param[ 'status' ] == 3 ) {
                Db ::name ('user') -> where ('userid' , $param[ 'uid' ]) -> setInc ('my_money' , $param[ 'money' ]);
                $user                  = Db ::name ('user') -> field ('my_money,phone') -> where ('userid' , $param[ 'uid' ]) -> find ();
                $data[ 'uid' ]         = $param[ 'uid' ];
                $data[ 'phone' ]       = $user[ 'phone' ];
                $data[ 'type' ]        = 6;
                $data[ 'info' ]        = '充值通过';
                $data[ 'addtime' ]     = time ();
                $data[ 'money' ]       = $param[ 'money' ];
                $data[ 'type_num' ]    = '+';
                $data[ 'money_start' ] = $user[ 'my_money' ];
                $data[ 'money_end' ]   = $user[ 'my_money' ] + $param[ 'money' ];
                Db ::name ('money_info') -> insert ($data);
            }

            // 提交事
            Db ::commit ();
            $this -> return_msg (200 , '审核成功');
        } catch ( \Exception $e ) {
            // 回滚事务
            Db ::rollback ();
            $this -> return_msg (400 , '审核失败');
        }

    }

    /**
     *删除充值记录
     * User: lanzh
     * Date: 2020/2/21 18:45
     */
    public function recharge_del ()
    {

        $param = $this -> params;
        if ( Db ::name ('recharge') -> delete ($param[ 'id' ]) ) {
            $this -> return_msg (200 , '已删除');
        }
        else {
            $this -> return_msg (400 , '删除失败');
        }

    }

    /**
     *提现页面
     * User: lanzh
     * Date: 2020/2/21 15:56
     */
    public function withdrawal ()
    {

        $param = $this -> params;
        $map   = [];
        if ( !empty($param) ) {

            //状态不为空
            if ( isset($param[ 'statusifyID' ]) && !empty($param[ 'statusifyID' ]) ) {
                $map[] = [ 'a.status' , 'eq' , $param[ 'statusifyID' ] ];

            }

            //搜索内容不为空
            if ( isset($param[ 'searchInput' ]) && !empty($param[ 'searchInput' ]) ) {
                if ( strlen ($param[ 'searchInput' ]) == 11 ) {
                    $map[] = [ 'a.phone' , 'eq' , $param[ 'searchInput' ] ];

                }
                else {
                    $map[] = [ 'a.uid' , 'eq' , $param[ 'searchInput' ] ];
                }
            }

            //时间戳不为空
            if ( isset($param[ 'start' ]) && isset($param[ 'end' ]) && !empty($param[ 'start' ]) && !empty($param[ 'end' ]) ) {
                $a     = strtotime ($param[ 'start' ]);
                $b     = strtotime ($param[ 'end' ]) + 86400;
                $map[] = [ 'a.addtime' , 'between' , "$a,$b" ];
            }
            else {
                if ( isset($param[ 'start' ]) && !empty($param[ 'start' ]) ) {
                    $a     = strtotime ($param[ 'start' ]);
                    $b     = strtotime ($param[ 'start' ]) + 86400;
                    $map[] = [ 'a.addtime' , 'between' , "$a,$b" ];
                }
            }
        }
        $list = Db ::name ('withdrawal') -> alias ('a') -> join ('api_user_bank b ' , 'b.id= a.bank_id') -> field ('a.*,b.username,b.address,b.address_info,b.phone as banknum') -> where ($map) -> order ('a.addtime' , 'desc') -> paginate (10 , false , [ 'query' => request () -> param () ]) -> each (function ( $item , $key ) {
            $item[ 'addtime' ]  = date ('Y-m-d H:i' , $item[ 'addtime' ]);
            $item[ 'end_time' ] = empty($item[ 'end_time' ]) ? '未操作' : date ('Y-m-d H:i' , $item[ 'end_time' ]);
            return $item;
        });

        $page = $list -> render ();
        return $this -> fetch ('withdrawal' , [
            'list'   => $list ,
            'page'   => $page ,
            'title'  => '出入账管理' ,
            'title2' => '提现管理' ,
        ]);
    }

    /**
     * 修改提现状态
     *
     * @param int $[id] [用户 id]
     * @param int $[status] [<状态>]
     *
     * @return [json] [修改结果]
     */
    public function withdrawal_status ()
    {
        $param             = $this -> params;
        $param[ 'status' ] = $param[ 'status' ] == 1 ? 3 : 2;
        $info              = $param[ 'status' ] == 3 ? '通过' : '拒绝';
        // 启动事务
        Db ::startTrans ();
        try {

            Db ::name ('withdrawal') -> where (array (
                'id'     => $param[ 'id' ] ,
                'uid'    => $param[ 'uid' ] ,
                'money'  => $param[ 'money' ] ,
                'status' => '1' ,
            )) -> update ([
                'status'  => $param[ 'status' ] ,
                'endtime' => time () ,
                'info'    => Session ::get ('admin')[ 'name' ] . '于' . date ('Y-m-d H:i' , time ()) . $info ,

            ]);

            if ( $param[ 'status' ] == 3 ) {
                Db ::name ('user') -> where ('userid' , $param[ 'uid' ]) -> setDec ('my_money' , $param[ 'money' ]);
                $user                  = Db ::name ('user') -> field ('my_money,phone') -> where ('userid' , $param[ 'uid' ]) -> find ();
                $data[ 'uid' ]         = $param[ 'uid' ];
                $data[ 'phone' ]       = $user[ 'phone' ];
                $data[ 'type' ]        = 7;
                $data[ 'info' ]        = '提现通过';
                $data[ 'addtime' ]     = time ();
                $data[ 'money' ]       = $param[ 'money' ];
                $data[ 'type_num' ]    = '-';
                $data[ 'money_start' ] = $user[ 'my_money' ];
                $data[ 'money_end' ]   = $user[ 'my_money' ] - $param[ 'money' ];
                Db ::name ('money_info') -> insert ($data);
            }

            // 提交事
            Db ::commit ();
            $this -> return_msg (200 , '审核成功');
        } catch ( \Exception $e ) {
            // 回滚事务
            Db ::rollback ();
            $this -> return_msg (400 , '通过失败');
        }

    }

    /**
     *删除充值记录
     * User: lanzh
     * Date: 2020/2/21 18:45
     */
    public function withdrawal_del ()
    {

        $param = $this -> params;
        if ( Db ::name ('withdrawal') -> delete ($param[ 'id' ]) ) {
            $this -> return_msg (200 , '已删除');
        }
        else {
            $this -> return_msg (400 , '删除失败');
        }

    }

    /**
     *管理员加减钱页面
     * User: lanzh
     * Date: 2020/3/19 10:56
     */
    public function manual ()
    {
        $type = $this -> request -> param ('type');
        return $this -> fetch ('manual' , [ 'type' => $type ]);
    }

    /**
     *管理员加减钱
     * User: lanzh
     * Date: 2020/3/19 10:56
     */
    public function manual_edit ()
    {
        $param = $this -> params;
        $user  = Db ::name ('user') -> where ('phone' , $param[ 'userid' ]) -> field ('userid,phone,my_money') -> find ();
        if ( empty($user) ) {
            $this -> return_msg (400 , '用户不存在');
        }

        Db ::startTrans ();
        try {
            if ( $param[ 'type' ] == 'yes' ) {
                Db ::name ('user') -> where ('userid' , $user[ 'userid' ]) -> setInc ('my_money' , $param[ 'money' ]);
                $data[ 'uid' ]         = $user[ 'userid' ];
                $data[ 'phone' ]       = $user[ 'phone' ];
                $data[ 'type' ]        = 8;
                $data[ 'info' ]        = '管理员加钱';
                $data[ 'money_info' ]  = Session ::get ('admin')[ 'name' ] . '操作于' . date ('Y-m-d H:i' , time ());
                $data[ 'addtime' ]     = time ();
                $data[ 'money' ]       = $param[ 'money' ];
                $data[ 'type_num' ]    = '+';
                $data[ 'money_start' ] = $user[ 'my_money' ];
                $data[ 'money_end' ]   = $user[ 'my_money' ] + $param[ 'money' ];
                Db ::name ('money_info') -> insert ($data);
            }
            else {
                Db ::name ('user') -> where ('userid' , $user[ 'userid' ]) -> setDec ('my_money' , $param[ 'money' ]);
                $data[ 'uid' ]         = $user[ 'userid' ];
                $data[ 'phone' ]       = $user[ 'phone' ];
                $data[ 'type' ]        = 9;
                $data[ 'info' ]        = '管理员减钱';
                $data[ 'money_info' ]  = Session ::get ('admin')[ 'name' ] . '操作于' . date ('Y-m-d H:i' , time ());
                $data[ 'addtime' ]     = time ();
                $data[ 'money' ]       = $param[ 'money' ];
                $data[ 'type_num' ]    = '-';
                $data[ 'money_start' ] = $user[ 'my_money' ];
                $data[ 'money_end' ]   = $user[ 'my_money' ] - $param[ 'money' ];
                Db ::name ('money_info') -> insert ($data);
            }
            // 提交事
            Db ::commit ();
            $this -> return_msg (200 , '处理成功');

        } catch ( \Exception $e ) {
            // 回滚事务
            Db ::rollback ();
            $this -> return_msg (400 , '处理失败');
        }

    }

}