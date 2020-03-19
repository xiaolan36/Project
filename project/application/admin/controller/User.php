<?php

namespace app\admin\controller;

use think\Db;

class User extends Common
{

    /**
     *用户主页
     * User: lanzh
     * Date: 2020/2/20 17:47
     */
    public function index ()
    {
        $param = $this -> params;
        $map   = [];
        if ( !empty($param) ) {

            //状态不为空
            if ( isset($param[ 'statusifyID' ]) && !empty($param[ 'statusifyID' ]) ) {
                $map[] = [ 'status' , 'eq' , $param[ 'statusifyID' ] ];

            }
            //搜索内容不为空
            if ( isset($param[ 'searchInput' ]) && !empty($param[ 'searchInput' ]) ) {
                if ( strlen ($param[ 'searchInput' ]) == 11 ) {
                    $map[] = [ 'phone' , 'eq' , $param[ 'searchInput' ] ];

                }
                else {
                    $map[] = [ 'userid' , 'eq' , $param[ 'searchInput' ] ];
                }
            }

            //时间戳不为空
            if ( isset($param[ 'start' ]) && isset($param[ 'end' ]) && !empty($param[ 'start' ]) && !empty($param[ 'end' ]) ) {
                $a     = strtotime ($param[ 'start' ]);
                $b     = strtotime ($param[ 'end' ]) + 86400;
                $map[] = [ 'reg_time' , 'between' , "$a,$b" ];
            }
            else {
                if ( isset($param[ 'start' ]) && !empty($param[ 'start' ]) ) {
                    $a     = strtotime ($param[ 'start' ]);
                    $b     = strtotime ($param[ 'start' ]) + 86400;
                    $map[] = [ 'reg_time' , 'between' , "$a,$b" ];
                }
            }

        }

        $list = Db ::name ('user') -> where ($map) -> order ('userid' , 'desc') -> paginate (20 , false , [ 'query' => request () -> param () ]) -> each (function ( $item , $key ) {
            $item[ 'reg_time' ]      = date ('Y-m-d H:i' , $item[ 'reg_time' ]);
            $str                     = array_reverse (explode (',' , $item[ 'all_tarent_id' ]));
            $item[ 'all_tarent_id' ] = implode (',' , $str);
            return $item;
        });
        return $this -> fetch ('index' , [
            'list'   => $list ,
            'title'  => '会员管理' ,
            'title2' => '会员列表' ,
        ]);
    }

    /**
     * 添加用户页面
     * @return [html] [description]
     */
    public function user_add ()
    {

        return $this -> fetch ('user_add');
    }

    public function user_add_s ()
    {

        $user = Db ::name ('user') -> field ('userid,all_tarent_id,pid,gid,ggid') -> where (array (
            'phone' => $this -> params[ 'uname' ]
        )) -> find ();
        if ( empty($user) ) {
            return json (array ( 'status' => 400 , 'msg' => '上级不存在' , ));
        }

        $user_phone = Db ::name ('user') -> where (array ( 'phone' => $this -> params[ 'username' ] )) -> count ();
        if ( $user_phone > 0 ) {
            return json (array ( 'status' => 400 , 'msg' => '此账号已注册' , ));
        }
        $info[ 'pid' ]           = $user[ 'userid' ];
        $info[ 'gid' ]           = $user[ 'pid' ];
        $info[ 'ggid' ]          = $user[ 'gid' ];
        $info[ 'all_tarent_id' ] = $user[ 'all_tarent_id' ] == '' ? $user[ 'userid' ] : $user[ 'all_tarent_id' ] . ',' . $user[ 'userid' ];
        $info[ 'phone' ]         = $this -> params[ 'username' ];
        $info[ 'my_money' ]      = $this -> params[ 'money' ];
        $info[ 'user_code' ]     = rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9);
        $info[ 'login_salt' ]    = rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9);
        $info[ 'login_pwd' ]     = md5 (md5 ($this -> params[ 'password' ]) . $info[ 'login_salt' ]);
        $info[ 'reg_time' ]      = time ();
        $info[ 'reg_ip' ]        = $this -> request -> ip ();
        $info[ 'test' ]          = 1;
        $result                  = Db ::name ('user') -> insert ($info);

        if ( $result ) {
            return json (array ( 'status' => 200 , 'msg' => '添加成功' ));
        }
        else {
            return json (array ( 'status' => 400 , 'msg' => '添加失败' ));
        }
    }

    /**
     * 修改用户页面
     *
     * @param  [int]   $[ids] [<用户id>]
     *
     * @return [html] [description]
     */
    public function user_edit ()
    {
        $param = $this -> params;
        $user  = Db ::name ('user') -> field ('my_money,userid,pid') -> where ('userid' , $param[ 'ids' ]) -> find ();
        $bank  = Db ::name ('user_bank') -> where (array ( 'uid' => $param[ 'ids' ] ) , '') -> find ();
        return $this -> fetch ('user_edit' , [ 'user' => $user ]);
    }

    /**
     * 修改用户
     * @return [type] [description]
     */
    public function user_edit_s ()
    {
        $param = $this -> params;

        if ( isset($param[ 'login_pwd' ]) && !empty($param[ 'login_pwd' ]) ) {
            $data[ 'login_salt' ] = rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9);
            $data[ 'login_pwd' ]  = md5 (md5 ($param[ 'login_pwd' ]) . $data[ 'login_salt' ]);
        }

        if ( isset($param[ 'tx_pwd' ]) && !empty($param[ 'tx_pwd' ]) ) {
            $data[ 'tx_pwd' ] = md5 ($param[ 'tx_pwd' ]);
        }

        if ( isset($param[ 'pid' ]) && !empty($param[ 'pid' ]) ) {

            if ( Db ::name ('user') -> where ('userid' , $param[ 'id' ]) -> value ('pid') == 80000 ) {
                $this -> return_msg (400 , '一级代理无法修改');
            }

            $puser = Db ::name ('user') -> where ('userid' , $param[ 'pid' ]) -> field ('userid,pid,gid') -> find ();
            if ( !$puser ) {
                $this -> return_msg (400 , '该上级不存在');
            }

            $data[ 'pid' ]  = $puser[ 'userid' ];
            $data[ 'gid' ]  = $puser[ 'pid' ];
            $data[ 'ggid' ] = $puser[ 'gid' ];
        }

        if ( empty($data) ) {
            $this -> return_msg (400 , '无修改');
        }

        $result = Db ::name ('user') -> where ('userid' , $param[ 'id' ]) -> update ($data);

        if ( $result ) {
            $this -> return_msg (200 , '修改成功');
        }
        else {
            $this -> return_msg (400 , '修改失败');
        }
    }

    /**
     * 删除
     *
     * @param int $[id] [<用户 id>]
     *
     * @return [json] [删除结果]
     */
    public function user_del ()
    {
        $param = $this -> params;

        if ( Db ::name ('user') -> delete ($param[ 'id' ]) ) {
            $this -> return_msg (200 , '已删除');
        }
        else {
            $this -> return_msg (400 , '删除失败');
        }

    }

    /**
     * 删除
     *
     * @param int $[id] [<用户 id>]
     *
     * @return [json] [删除结果]
     */
    public function user_status ()
    {
        $param             = $this -> params;
        $param[ 'status' ] = $param[ 'status' ] == 1 ? 2 : 1;
        if ( Db ::name ('user') -> where ('userid' , $param[ 'id' ]) -> update (array ( 'status' => $param[ 'status' ] )) ) {
            $this -> return_msg (200 , '已冻结');
        }
        else {
            $this -> return_msg (400 , '冻结失败');
        }

    }

    //--------------------------------------------------------------------------------------------
    //--------------------------------------------------------------------------------------------
    /**
     *用户抢单主页
     * User: lanzh
     * Date: 2020/2/20 17:47
     */
    public function user_order ()
    {
        $param = $this -> params;
        $map   = [];

        if ( !empty($param) ) {

            //状态不为空
            if ( isset($param[ 'statusifyID' ]) && !empty($param[ 'statusifyID' ]) ) {
                $map[] = [ 'status' , 'eq' , $param[ 'statusifyID' ] ];

            }

            //搜索内容不为空
            if ( isset($param[ 'searchInput' ]) && !empty($param[ 'searchInput' ]) ) {
                $str   = $param[ 'searchInput' ];
                $map[] = [ 'ordernum|uaccount|uid' , 'like' , "{$param[ 'searchInput' ]}%" ];

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

        $list = Db ::name ('user_order') -> where ($map) -> order ('addtime' , 'desc') -> paginate (20 , false , [ 'query' => request () -> param () ]) -> each (function ( $item , $key ) {
            $item[ 'dongjietime' ] = empty($item[ 'finshtime' ]) ? null : date ('Y-m-d H:i' , $item[ 'dongjietime' ]);
            $item[ 'tijiao_time' ] = empty($item[ 'tijiao_time' ]) ? null : date ('Y-m-d H:i' , $item[ 'tijiao_time' ]);
            return $item;
        });
        return $this -> fetch ('user_order' , [
            'list'   => $list ,
            'title'  => '抢单管理' ,
            'title2' => '会员抢单列表'
        ]);
    }

    /**
     * 删除
     *
     * @param int $[id] [<用户抢单 id>]
     *
     * @return [json] [删除结果]
     */
    public function user_order_del ()
    {
        $param = $this -> params;

        if ( Db ::name ('user_order') -> delete ($param[ 'id' ]) ) {
            $this -> return_msg (200 , '已删除');
        }
        else {
            $this -> return_msg (400 , '删除失败');
        }

    }

    /**
     *理财主页
     * User: lanzh
     * Date: 2020/2/20 17:47
     */
    public function investment ()
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
                    $map[] = [ 'b.phone' , 'eq' , $param[ 'searchInput' ] ];
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

        $list = Db ::name ('investment_info') -> alias ('a') -> join ('api_user b ' , 'b.userid= a.uid') -> field ('a.*,b.phone,b.my_money') -> where ($map) -> order ('a.addtime' , 'desc') -> paginate (20 , false , [ 'query' => request () -> param () ]) -> each (function ( $item , $key ) {
            $item[ 'draw_time' ] = empty($item[ 'draw_time' ]) ? null : date ('Y-m-d H:i' , $item[ 'draw_time' ]);
            $item[ 'addtime' ]   = date ('Y-m-d H:i' , $item[ 'addtime' ]);
            $item[ 'endtime' ]   = date ('Y-m-d H:i' , $item[ 'endtime' ]);

            return $item;
        });
        return $this -> fetch ('investment' , [
            'list'   => $list ,
            'title'  => '抢单管理' ,
            'title2' => '理财列表' ,
        ]);
    }

    /**
     * 删除
     *
     * @param int $[id] [<理财 id>]
     *
     * @return [json] [删除结果]
     */
    public function user_investment_del ()
    {
        $param = $this -> params;

        if ( Db ::name ('investment_info') -> delete ($param[ 'id' ]) ) {
            $this -> return_msg (200 , '已删除');
        }
        else {
            $this -> return_msg (400 , '删除失败');
        }

    }

    /**
     *资金主页
     * User: lanzh
     * Date: 2020/2/20 17:47
     */
    public function money_info ()
    {
        $param = $this -> params;
        $map   = [];

        if ( !empty($param) ) {

            //状态不为空
            if ( isset($param[ 'statusifyID' ]) && !empty($param[ 'statusifyID' ]) ) {
                $map[] = [ 'type' , 'eq' , $param[ 'statusifyID' ] ];

            }

            //搜索内容不为空
            if ( isset($param[ 'searchInput' ]) && !empty($param[ 'searchInput' ]) ) {
                if ( strlen ($param[ 'searchInput' ]) == 11 ) {
                    $map[] = [ 'phone' , 'eq' , $param[ 'searchInput' ] ];

                }
                else {
                    $map[] = [ 'uid' , 'eq' , $param[ 'searchInput' ] ];
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
        $list = Db ::name ('money_info') -> where ($map) -> order ('addtime' , 'desc') -> paginate (20 , false , [ 'query' => request () -> param () ]) -> each (function ( $item , $key ) {
            return $item;
        });
        return $this -> fetch ('money_info' , [
            'list'   => $list ,
            'title'  => '抢单管理' ,
            'title2' => '资金明细' ,
        ]);
    }

    /**
     * 删除
     *
     * @param int $[id] [<资金 id>]
     *
     * @return [json] [删除结果]
     */
    public function user_money_info_del ()
    {
        $param = $this -> params;

        if ( Db ::name ('money_info') -> delete ($param[ 'id' ]) ) {
            $this -> return_msg (200 , '已删除');
        }
        else {
            $this -> return_msg (400 , '删除失败');
        }
    }

    /**
     *用户银行卡
     * User: lanzh
     * Date: 2020/3/4 10:31
     */
    public function user_bank ()
    {
        $param = $this -> params;
        $map   = [];
        if ( !empty($param) ) {

            //状态不为空
            if ( isset($param[ 'statusifyID' ]) ) {
                $map[] = [ 'a.status' , 'eq' , $param[ 'statusifyID' ] ];

            }

            //搜索内容不为空
            if ( isset($param[ 'searchInput' ]) && !empty($param[ 'searchInput' ]) ) {
                if ( strlen ($param[ 'searchInput' ]) == 11 ) {
                    $map[] = [ 'b.phone' , 'eq' , $param[ 'searchInput' ] ];

                }
                else {
                    $map[] = [ 'a.uid' , 'eq' , $param[ 'searchInput' ] ];
                }
            }
        }

        $list = Db ::name ('user_bank') -> alias ('a') -> join ('api_user b ' , 'b.userid= a.uid') -> field ('a.*,b.phone as mobile,b.truename') -> where ($map) -> paginate (20 , false , [ 'query' => request () -> param () ]) -> each (function ( $item , $key ) {
            return $item;
        });
        return $this -> fetch ('user_bank' , [
            'list'   => $list ,
            'title'  => '会员管理' ,
            'title2' => '会员银行卡管理' ,
        ]);

    }

    /**
     * 删除银行卡
     *
     * @param int $[id] [<银行卡 id>]
     *
     * @return [json] [删除结果]
     */
    public function del_bank ()
    {
        $param = $this -> params;

        if ( Db ::name ('user_bank') -> delete ($param[ 'id' ]) ) {
            $this -> return_msg (200 , '已删除');
        }
        else {
            $this -> return_msg (400 , '删除失败');
        }
    }
}