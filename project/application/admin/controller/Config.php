<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Session;
use think\facade\Env;

class Config extends Common
{

    /**
     * /系统设置页面
     */
    public function index ()
    {

        $list = Db ::name ('system') -> find ();
        // 渲染模板输出
        return $this -> fetch ('config' , [ 'list'   => $list ,
                                            'title'  => '系统信息' ,
                                            'title2' => '系统设置' ,
                                            'title2' => '系统设置'
        ]);
    }

    /**
     * 修改网站配置
     * @return [type] [description]
     */
    public function edit_config ()
    {
        $param = $this -> params;
        foreach ( $param as $key => $val ) {
            if ( empty($val) ) {
                unset($param[ $key ]);
            }
        }

        $result = Db ::name ('system') -> where ('1=1') -> update ($param);

        if ( $result ) {
            $this -> return_msg (200 , '配置已修改');
        }
        else {
            $this -> return_msg (400 , '配置修改失败');
        }
    }

    //轮播图管理
    public function banner ()
    {
        $param = $this -> params;
        $map   = array ();

        //搜索内容不为空
        if ( isset($param[ 'searchInput' ]) && !empty($param[ 'searchInput' ]) ) {
            $str           = $param[ 'searchInput' ];
            $map[ 'href' ] = [ 'like' , "%$str%" ];

        }

        $list = Db ::name ('banner') -> where ($map) -> order ('id' , 'desc') -> paginate (10 , false , [ 'query' => request () -> param () ]) -> each (function ( $item , $key ) {
            $item[ 'start_time' ] = date ('Y-m-d H:i' , $item[ 'start_time' ]);
            return $item;
        });
        // 渲染模板输出
        return $this -> fetch ('banner' , [ 'list' => $list , 'title' => '系统信息' , 'title2' => '轮播管理' ]);
    }

    /**
     * 添加轮播图页面
     * @return [html] [description]
     */
    public function add_banner ()
    {

        return $this -> fetch ('banner_add');
    }

    /**
     * 修改轮播图页面
     *
     * @param  [int]   $[ids] [<轮播图id>]
     *
     * @return [html] [description]
     */
    public function edit_banner ()
    {

        $list = db ('banner') -> where ('id' , $_GET[ 'ids' ]) -> find ();
        //分类标签
        return $this -> fetch ('banner_edit' , [ 'list' => $list ]);
    }

    /**
     * 添加轮播图
     * @return [type] [description]
     */
    public function banner_add_s ()
    {
        $file = request () -> file ('btnUploadFileThumb');
        if ( !$file ) {
            $this -> return_msg (400 , '请上传轮播图');
        }

        $data = array ();

        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file -> validate ([
            'size' => 1024 * 1024 ,
            'ext'  => 'jpg,png,gif'
        ]) -> move (Env ::get ('root_path') . 'public' . DIRECTORY_SEPARATOR . 'uploads');
        if ( $info ) {
            // 成功上传后 获取上传信息
            $data[ 'images' ] = $info -> getSaveName ();

        }
        else {
            // 上传失败获取错误信息
            $this -> return_msg (400 , $file -> getError ());
        }

        $param                = $this -> params;
        $data[ 'href' ]       = $param[ 'articleTitle' ];
        $data[ 'status' ]     = $param[ 'articlesStatus' ];
        $data[ 'start_time' ] = time ();

        if ( Db ::name ('banner') -> insert ($data) ) {
            $this -> return_msg (200 , '添加成功');
        }
        else {
            $this -> return_msg (400 , '添加失败');
        }
    }

    /**
     * 修改轮播图
     * @return [type] [description]
     */
    public function banner_edit_s ()
    {
        $file = request () -> file ('btnUploadFileThumb');
        $data = array ();

        if ( $file ) {

            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file -> validate ([
                'size' => 1024 * 1024 ,
                'ext'  => 'jpg,png,gif'
            ]) -> move (ROOT_PATH . 'public' . DS . 'uploads');
            if ( $info ) {
                // 成功上传后 获取上传信息
                $data[ 'images' ] = $info -> getSaveName ();
            }
            else {
                // 上传失败获取错误信息
                $this -> return_msg (400 , $file -> getError ());
            }
        }

        $param                = $this -> params;
        $data[ 'href' ]       = $param[ 'articleTitle' ];
        $data[ 'status' ]     = $param[ 'articlesStatus' ];
        $data[ 'start_time' ] = time ();

        if ( Db ::name ('banner') -> where ('id' , $param[ 'id' ]) -> update ($data) ) {
            $this -> return_msg (200 , '文章修改成功');
        }
        else {
            $this -> return_msg (400 , '文章修改失败');
        }
    }

    /**
     * 删除
     *
     * @param int $[id] [<轮播图 id>]
     *
     * @return [json] [删除结果]
     */
    public function banner_del ()
    {
        $param = $this -> params;

        if ( Db ::name ('banner') -> delete ($param[ 'id' ]) ) {
            $this -> return_msg (200 , '已删除');
        }
        else {
            $this -> return_msg (400 , '删除失败');
        }

    }

    /**
     * 修改状态
     *
     * @param int $[id] [轮播图 id]
     * @param int $[status] [<状态>]
     *
     * @return [json] [删除结果]
     */
    public function banner_status ()
    {
        $param             = $this -> params;
        $param[ 'status' ] = $param[ 'status' ] == 1 ? 2 : 1;

        if ( Db ::name ('banner') -> where ('id' , $param[ 'id' ]) -> update ([ 'status' => $param[ 'status' ] ]) ) {
            $this -> return_msg (200 , '修改成功');
        }
        else {
            $this -> return_msg (400 , '修改失败');
        }

    }

    //---------------------------------------------------------------------------------------------------
    //---------------------------------------------------------------------------------------------------

    //银行卡管理
    public function link ()
    {
        $param = $this -> params;
        $map   = array ();

        //搜索内容不为空
        if ( isset($param[ 'searchInput' ]) && !empty($param[ 'searchInput' ]) ) {
            $str   = $param[ 'searchInput' ];
            $map[] = [ 'username' , 'like' , "%$str%" ];

        }
        $list = Db ::name ('system_bankcard') -> where ($map) -> order ('id' , 'desc') -> paginate (20 , false , [ 'query' => request () -> param () ]) -> each (function ( $item , $key ) {
            $item[ 'addtime' ] = date ('Y-m-d H:i' , $item[ 'addtime' ]);
            return $item;
        });
        // 渲染模板输出
        return $this -> fetch ('link' , [ 'list' => $list , 'title' => '系统信息' , 'title2' => '充值通道管理' ]);
    }

    /**
     * 添加银行卡页面
     * @return [html] [description]
     */
    public function add_link ()
    {
        //分类标签
        return $this -> fetch ('link_add');
    }

    /**
     * 修改银行卡页面
     *
     * @param  [int]   $[ids] [<银行卡id>]
     *
     * @return [html] [description]
     */
    public function edit_link ()
    {

        $list = db ('system_bankcard') -> where ('id' , $_GET[ 'ids' ]) -> find ();
        //分类标签
        return $this -> fetch ('link_edit' , [ 'list' => $list ]);
    }

    /**
     * 添加银行卡
     * @return [type] [description]
     */
    public function link_add_s ()
    {

        $param              = $this -> params;
        $param[ 'addtime' ] = time ();
        if ( Db ::name ('system_bankcard') -> insert ($param) ) {
            $this -> return_msg (200 , '添加成功');
        }
        else {
            $this -> return_msg (400 , '添加失败');
        }
    }

    /**
     * 修改银行卡
     * @return [type] [description]
     */
    public function link_edit_s ()
    {
        $param = $this -> params;

        if ( Db ::name ('system_bankcard') -> where ('id' , $param[ 'id' ]) -> update ($param) ) {
            $this -> return_msg (200 , '修改成功');
        }
        else {
            $this -> return_msg (400 , '修改失败');
        }
    }

    /**
     * 删除
     *
     * @param int $[id] [<银行卡 id>]
     *
     * @return [json] [删除结果]
     */
    public function link_del ()
    {
        $param = $this -> params;

        if ( Db ::name ('system_bankcard') -> delete ($param[ 'id' ]) ) {
            $this -> return_msg (200 , '已删除');
        }
        else {
            $this -> return_msg (400 , '删除失败');
        }

    }

    /**
     * 修改状态
     *
     * @param int $[id] [银行卡 id]
     * @param int $[status] [<状态>]
     *
     * @return [json] [删除结果]
     */
    public function link_status ()
    {
        $param             = $this -> params;
        $param[ 'status' ] = $param[ 'status' ] == 1 ? 2 : 1;

        if ( Db ::name ('system_bankcard') -> where ('id' , $param[ 'id' ]) -> update ([ 'status' => $param[ 'status' ] ]) ) {
            $this -> return_msg (200 , '修改成功');
        }
        else {
            $this -> return_msg (400 , '修改失败');
        }

    }



    //---------------------------------------------------------------------------------------------------
    //---------------------------------------------------------------------------------------------------

    //利息管理
    public function interest ()
    {
        $param = $this -> params;
        $map   = array ();

        //搜索内容不为空
        if ( isset($param[ 'searchInput' ]) && !empty($param[ 'searchInput' ]) ) {
            $str               = $param[ 'searchInput' ];
            $map[ 'day|rate' ] = [ 'like' , "%$str%" ];
        }

        $list = Db ::name ('investment_rate') -> where ($map) -> order ('id' , 'desc') -> paginate (20 , false , [ 'query' => request () -> param () ]) -> each (function ( $item , $key ) {
            $item[ 'addtime' ] = date ('Y-m-d H:i' , $item[ 'addtime' ]);
            return $item;
        });
        // 渲染模板输出
        return $this -> fetch ('interest' , [ 'list' => $list , 'title' => '系统信息' , 'title2' => '利息设置' ]);
    }

    /**
     * 添加利息页面
     * @return [html] [description]
     */
    public function add_interest ()
    {
        //分类标签
        return $this -> fetch ('interest_add');
    }

    /**
     * 修改利息页面
     *
     * @param  [int]   $[ids] [<利息id>]
     *
     * @return [html] [description]
     */
    public function edit_interest ()
    {

        $list = db ('investment_rate') -> where ('id' , $_GET[ 'ids' ]) -> find ();
        //分类标签
        return $this -> fetch ('interest_edit' , [ 'list' => $list ]);
    }

    /**
     * 添加利息
     * @return [type] [description]
     */
    public function interest_add_s ()
    {

        $param              = $this -> params;
        $param[ 'addtime' ] = time ();
        if ( Db ::name ('investment_rate') -> insert ($param) ) {
            $this -> return_msg (200 , '添加成功');
        }
        else {
            $this -> return_msg (400 , '添加失败');
        }
    }

    /**
     * 修改利息
     * @return [type] [description]
     */
    public function interest_edit_s ()
    {
        $param = $this -> params;

        if ( Db ::name ('investment_rate') -> where ('id' , $param[ 'id' ]) -> update ($param) ) {
            $this -> return_msg (200 , '修改成功');
        }
        else {
            $this -> return_msg (400 , '修改失败');
        }
    }

    /**
     * 删除
     *
     * @param int $[id] [<利息 id>]
     *
     * @return [json] [删除结果]
     */
    public function interest_del ()
    {
        $param = $this -> params;

        if ( Db ::name ('investment_rate') -> delete ($param[ 'id' ]) ) {
            $this -> return_msg (200 , '已删除');
        }
        else {
            $this -> return_msg (400 , '删除失败');
        }

    }

    /**
     * 修改状态
     *
     * @param int $[id] [利息 id]
     * @param int $[status] [<状态>]
     *
     * @return [json] [删除结果]
     */
    public function interest_status ()
    {
        $param             = $this -> params;
        $param[ 'status' ] = $param[ 'status' ] == 1 ? 2 : 1;

        if ( Db ::name ('investment_rate') -> where ('id' , $param[ 'id' ]) -> update ([ 'status' => $param[ 'status' ] ]) ) {
            $this -> return_msg (200 , '修改成功');
        }
        else {
            $this -> return_msg (400 , '修改失败');
        }

    }

}
