<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Env;

class Goods extends Common
{

    //商品管理管理
    public function goods ()
    {
        $param = $this -> params;
        $map   = array ();

        //搜索内容不为空
        if ( isset($param[ 'statusifyID' ]) && !empty($param[ 'statusifyID' ]) ) {
            $map[ 'status' ] = [ 'eq' , "{$param[ 'statusifyID' ]}" ];

        }

        $list = Db ::name ('roborder') -> where ($map) -> order ('id' , 'desc') -> paginate (10 , false , [ 'query' => request () -> param () ]) -> each (function ( $item , $key ) {
            $item[ 'start_time' ] = date ('Y-m-d H:i' , $item[ 'start_time' ]);
            return $item;
        });
        $page = $list -> render ();
        // 渲染模板输出
        return $this -> fetch ('goods' , [ 'list' => $list , 'title' => '抢单管理' , 'page' => $page,'title2'=>'商品管理' ]);
    }

    /**
     * 添加商品管理页面
     * @return [html] [description]
     */
    public function add_goods ()
    {

        return $this -> fetch ('goods_add');
    }

    /**
     * 修改商品管理页面
     *
     * @param  [int]   $[ids] [<商品管理id>]
     *
     * @return [html] [description]
     */
    public function edit_goods ()
    {

        $list = db ('roborder') -> where ('id' , $_GET[ 'ids' ]) -> find ();
        //分类标签
        return $this -> fetch ('goods_edit' , [ 'list' => $list ]);
    }

    /**
     * 添加商品管理
     * @return [type] [description]
     */
    public function goods_add_s ()
    {
        $param = $this -> params;
        $num   = $param[ 'num' ];
        unset($param[ 'num' ]);
        $data = [];
        for ( $i = 0 ; $i < $num ; $i++ ) {
            $param[ 'ordernum' ] = $this -> getordernum ();
            array_push ($data , $param);
        }

        if ( Db ::name ('roborder') -> insertAll ($data) ) {
            $this -> return_msg (200 , '添加成功');
        }
        else {
            $this -> return_msg (400 , '添加失败');
        }
    }

    /**
     * 修改商品管理
     * @return [type] [description]
     */
    public function goods_edit_s ()
    {
        $file = request () -> file ('btnUploadFileThumb');
        $data = array ();
        if ( $file ) {

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
        }

        $param = $this -> params;
        unset($param[ 'btnUploadFileThumb' ]);
        if ( Db ::name ('roborder') -> where ('id' , $param[ 'id' ]) -> update ($param) ) {
            $this -> return_msg (200 , '文章修改成功');
        }
        else {
            $this -> return_msg (400 , '文章修改失败');
        }
    }

    /**
     * 删除
     *
     * @param int $[id] [<商品管理 id>]
     *
     * @return [json] [删除结果]
     */
    public function goods_del ()
    {
        $param = $this -> params;

        if ( Db ::name ('roborder') -> delete ($param[ 'id' ]) ) {
            $this -> return_msg (200 , '已删除');
        }
        else {
            $this -> return_msg (400 , '删除失败');
        }

    }

    /*随机生成订单号*/
    public function getordernum ( $length = 12 , $char = '0123456789' )
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