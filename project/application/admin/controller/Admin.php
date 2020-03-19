<?php

namespace app\admin\controller;

use think\Db;
use think\Exception;

class Admin extends Common
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
                $map[] = [ 'a.status' , 'eq' , $param[ 'statusifyID' ] ];

            }
            //搜索内容不为空
            if ( isset($param[ 'searchInput' ]) && !empty($param[ 'searchInput' ]) ) {
                if ( strlen ($param[ 'searchInput' ]) == 11 ) {
                    $map[] = [ 'a.name' , 'eq' , $param[ 'searchInput' ] ];

                }
                else {
                    $map[] = [ 'id' , 'eq' , $param[ 'searchInput' ] ];
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

        $list = Db ::name ('admin') -> alias ('a') -> join ('api_admin_role b ' , 'a.role= b.roleid') -> field ('a.*, b.rolename') -> where ($map) -> order ('a.id' , 'asc') -> paginate (20 , false , [ 'query' => request () -> param () ]) -> each (function ( $item , $key ) {
            return $item;
        });
        return $this -> fetch ('index' , [
            'list'   => $list ,
            'title'  => '系统信息' ,
            'title2' => '管理员管理' ,
        ]);
    }

    /**
     *添加管理员
     * User: lanzh
     * Date: 2020/3/3 14:55
     */
    public function add_admin ()
    {

        if ( $this -> request -> isAjax () ) {
            if ( Db ::name ('admin') -> where ('name' , $this -> params[ 'name' ]) -> find () ) {
                $this -> return_msg (200 , '改管理员已存在');
            }
            $data[ 'name' ]    = $this -> params[ 'name' ];
            $data[ 'salt' ]    = rand (1 , 10) . rand (1 , 10) . rand (1 , 10) . rand (1 , 10);
            $data[ 'pwd' ]     = md5 (md5 ($this -> params[ 'password' ]) . $data[ 'salt' ]);
            $data[ 'status' ]  = 1;
            $data[ 'role' ]    = $this -> params[ 'statusifyID' ];
            $data[ 'addtime' ] = time ();
            if ( Db ::name ('admin') -> insert ($data) ) {
                $this -> return_msg (200 , '已添加');
            }
            else {
                $this -> return_msg (400 , '添加失败');
            }
        }
        else {
            $status = Db ::name ('admin_role') -> field ('roleid,rolename') -> order ('roleid' , 'desc') -> select ();
            $this -> assign ('list' , $status);
            return $this -> fetch ('add_admin' , [
                'title'  => '系统信息' ,
                'title2' => '管理员管理' ,
            ]);
        }
    }

    /**
     * 删除
     *
     * @param int $[id] [<用户 id>]
     *
     * @return [json] [删除结果]
     */
    public function admin_del ()
    {
        $param = $this -> params;

        if ( Db ::name ('admin') -> delete ($param[ 'id' ]) ) {
            $this -> return_msg (200 , '已删除');
        }
        else {
            $this -> return_msg (400 , '删除失败');
        }

    }

    /**
     * 冻结
     *
     * @param int $[id] [<用户 id>]
     *
     * @return [json] [冻结结果]
     */
    public function admin_status ()
    {
        $param             = $this -> params;
        $param[ 'status' ] = $param[ 'status' ] == 1 ? 2 : 1;
        if ( Db ::name ('admin') -> where ('id' , $param[ 'id' ]) -> update (array ( 'status' => $param[ 'status' ] )) ) {
            $this -> return_msg (200 , '已冻结');
        }
        else {
            $this -> return_msg (400 , '冻结失败');
        }
    }

    /**
     *修改管理员页面
     * User: lanzh
     * Date: 2020/3/3 15:10
     */
    public function edit_admin ()
    {

        $admin  = Db ::name ('admin') -> where ('id' , $this -> params[ 'ids' ]) -> find ();
        $status = Db ::name ('admin_role') -> field ('roleid,rolename') -> order ('roleid' , 'desc') -> select ();
        $this -> assign ('list' , $status);
        $this -> assign ('admin' , $admin);
        return $this -> fetch ('edit_admin' , [
            'title'  => '系统信息' ,
            'title2' => '管理员管理' ,
        ]);
    }

    /**
     *修改管理员
     * User: lanzh
     * Date: 2020/3/3 15:26
     */
    public function edit_admin_s ()
    {

        //密码不为空
        if ( !empty($this -> params[ 'password' ]) ) {
            $data[ 'salt' ] = rand (1 , 10) . rand (1 , 10) . rand (1 , 10) . rand (1 , 10);
            $data[ 'pwd' ]  = md5 (md5 ($this -> params[ 'password' ]) . $data[ 'salt' ]);
        }
        $data[ 'name' ] = $this -> params[ 'name' ];
        $data[ 'role' ] = $this -> params[ 'statusifyID' ];
        $data[ 'id' ]   = $this -> params[ 'id' ];
        if ( Db ::name ('admin') -> where ('id' , $data[ 'id' ]) -> update ($data) ) {
            $this -> return_msg (200 , '已修改');
        }
        else {
            $this -> return_msg (400 , '修改失败');
        }
    }

    /**
     *角色列表
     * User: lanzh
     * Date: 2020/3/2 19:13
     */
    public function role ()
    {

        $list = Db ::name ('admin_role') -> order ('roleid' , 'asc') -> paginate (20 , false , [ 'query' => request () -> param () ]) -> each (function ( $item , $key ) {
            $listname = Db ::name ('admin') -> where ('role' , $item[ 'roleid' ]) -> field ('name') -> select ();
            foreach ( $listname as $key => $val ) {
                $str .= '<span style="margin: 0px 5px 0px 5px;color: violet;font-size: 20px"> ' . $val[ 'name' ] . ',' . '</span>';
            }
            $item[ 'listname' ] = $str;
            return $item;
        });
        return $this -> fetch ('role' , [
            'list'   => $list ,
            'title'  => '系统信息' ,
            'title2' => '角色管理' ,
        ]);
    }

    //修改权限页面
    public function edit_role ()
    {

        $roleid              = $this -> params[ 'ids' ];
        $data[ 'menu_list' ] = Db ::query ("SELECT * FROM api_menu");
        $role_menu_list      = Db ::query ("SELECT m.* FROM api_menu as m  ,api_menu_role as rm WHERE  {$roleid}=rm.r_id AND m.id=rm.m_id");

        foreach ( $data[ 'menu_list' ] as $key => $value ) {
            foreach ( $role_menu_list as $k => $v ) {
                if ( $v[ 'id' ] == $value[ 'id' ] ) {
                    $data[ 'menu_list' ][ $key ][ 'yes' ] = 1;
                }
            }
        }

        $this -> assign ('menu_list' , $data[ 'menu_list' ]);
        $this -> assign ('roleid' , $roleid);
        return $this -> fetch ('edit_role' , [
            'title'  => '系统信息' ,
            'title2' => '角色管理' ,
        ]);
    }

    /**
     *修改权限
     * User: lanzh
     * Date: 2020/3/3 11:33
     */
    public function edit_role_s ()
    {
        $roleid = $this -> params[ 'roleid' ];

        if ( $roleid == 1 ) {
            $this -> return_msg (400 , '超级管理员不可操作');
        }

        $Character = $this -> params[ 'Character' ];
        Db ::startTrans ();
        try {
            Db ::name ('menu_role') -> where ('r_id' , $roleid) -> delete ();
            foreach ( $Character as $key => $val ) {
                $data[] = array ( 'r_id' => $roleid , 'm_id' => $val );
            }
            Db ::name ('menu_role') -> insertAll ($data);
            Db ::commit ();
            $this -> return_msg (200 , '设置成功');
        } catch ( w $exception ) {
            Db ::rollback ();
            $this -> return_msg (400 , '设置失败');
        }
    }

    /**
     *添加角色
     * User: lanzh
     * Date: 2020/3/3 14:29
     */
    public function add_role ()
    {
        if ( $this -> request -> isAjax () ) {

            if ( empty($this -> params[ 'Character' ]) ) {
                $this -> return_msg (400 , '请至少为角色选择一个权限');
            }

            $Character               = $this -> params[ 'Character' ];
            $param[ 'rolename' ]     = $this -> params[ 'rolename' ];
            $param[ 'role_remarks' ] = $this -> params[ 'role_remarks' ];
            $param[ 'addtime' ]      = time ();

            Db ::startTrans ();
            try {
                $r_id = Db ::name ('admin_role') -> insertGetId ($param);
                foreach ( $Character as $key => $val ) {
                    $data[] = array ( 'r_id' => $r_id , 'm_id' => $val );
                }
                Db ::name ('menu_role') -> insertAll ($data);
                Db ::commit ();
                $this -> return_msg (200 , '添加成功');
            } catch ( Exception $exception ) {
                Db ::rollback ();
                $this -> return_msg (400 , '添加失败');
            }
        }
        else {

            $data[ 'menu_list' ] = Db ::query ("SELECT * FROM api_menu");
            $this -> assign ('menu_list' , $data[ 'menu_list' ]);
            return $this -> fetch ('add_role' , [
                'title'  => '系统信息' ,
                'title2' => '角色管理' ,
            ]);
        }
    }

    /**
     * 删除
     *
     * @param int $[id] [<用户 id>]
     *
     * @return [json] [删除结果]
     */
    public function role_del ()
    {
        $param = $this -> params;
        try {
            Db ::name ('admin_role') -> delete ($param[ 'id' ]);
            Db ::name ('menu_role') -> where ('r_id' , $param[ 'id' ]) -> delete ();
            $this -> return_msg (200 , '已删除');
        } catch ( Exception $exception ) {
            Db ::rollback ();
            $this -> return_msg (400 , '删除失败');
        }
    }
}