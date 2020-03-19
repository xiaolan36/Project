<?php

namespace app\admin\controller;

use think\facade\Env;
use think\Db;

class Article extends Common
{

    public function index ()
    {

        $map   = [];
        $param = $this -> params;
        //条件数组默认为空
        //分类不为空
        if ( isset($param[ 'classifyID' ]) && !empty($param[ 'classifyID' ]) ) {
            $map[ 'a.p_id' ] = $param[ 'classifyID' ];
        }
        //状态不为空
        if ( isset($param[ 'statusifyID' ]) && !empty($param[ 'statusifyID' ]) ) {
            $map[ 'a.statusifyID' ] = $param[ 'statusifyID' ];
        }

        //搜索内容不为空
        if ( isset($param[ 'searchInput' ]) && !empty($param[ 'searchInput' ]) ) {
            $str                        = $param[ 'searchInput' ];
            $map[ 'a.title|a.content' ] = [ 'like' , "%$str%" ];

        }

        //分类标签
        $tags   = Db ::name ('tags') -> select ();
        $status = array (
            '0' => array ( 'id' => 1 , 'tag_name' => '已发布' ) ,
            '1' => array ( 'id' => 2 , 'tag_name' => '已下架' )
        );
        $list   = Db ::name ('article') -> alias ('a') -> join ('api_tags b ' , 'b.e_id= a.p_id') -> field ('a.*,b.tag_name') -> where ($map) -> order ('a.start_time' , 'desc') -> paginate (20 , false , [ 'query' => request () -> param () ]) -> each (function ( $item , $key ) {
            $item[ 'start_time' ] = date ('Y-m-d H:i' , $item[ 'start_time' ]);
            return $item;
        });

        // 渲染模板输出
        return $this -> fetch ('article' , [
            'status' => $status ,
            'list'   => $list ,
            'title'  => '系统信息' ,
            'title2'=>'文章管理',
            'tags'   => $tags ,
        ]);
    }

    /**
     * 添加文章页面
     * @return [html] [description]
     */
    public function article_add ()
    {
        //分类标签
        $tags = Db ::name ('tags') -> select ();
        return $this -> fetch ('article_add' , [ 'tags' => $tags ]);
    }

    /**
     * 修改文章页面
     *
     * @param  [int]   $[ids] [<文章id>]
     *
     * @return [html] [description]
     */
    public function article_edit ()
    {

        $list              = db ('article') -> where ('id' , $_GET[ 'ids' ]) -> find ();
        $list[ 'content' ] = htmlspecialchars_decode ($list[ 'content' ]);
        //分类标签
        $tags = Db ::name ('tags') -> select ();
        return $this -> fetch ('article_edit' , [ 'tags' => $tags , 'list' => $list ]);
    }

    /**
     * 添加文章
     * @return [type] [description]
     */
    public function article_add_s ()
    {
        $file = request () -> file ('btnUploadFileThumb');
        if ( !$file ) {
            $this -> return_msg (400 , '请上传缩略图');
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

        $param                     = $this -> params;
        $data[ 'title' ]           = $param[ 'articleTitle' ];
        $data[ 'content' ]         = htmlspecialchars_decode ($param[ 'editorValue' ]);
        $data[ 'articleAbstract' ] = $param[ 'articleAbstract' ];
        $data[ 'p_id' ]            = $param[ 'articleClassify' ];
        $data[ 'statusifyID' ]     = $param[ 'articlesStatus' ];
        $data[ 'start_time' ]      = time ();

        if ( Db ::name ('article') -> insert ($data) ) {
            $this -> return_msg (200 , '文章发布成功');
        }
        else {
            $this -> return_msg (400 , '文章发布失败');
        }
    }

    /**
     * 修改文章
     * @return [type] [description]
     */
    public function article_edit_s ()
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

        $param                     = $this -> params;
        $data[ 'title' ]           = $param[ 'articleTitle' ];
        $data[ 'content' ]         = $param[ 'editorValue' ];
        $data[ 'articleAbstract' ] = $param[ 'articleAbstract' ];
        $data[ 'p_id' ]            = $param[ 'articleClassify' ];
        $data[ 'statusifyID' ]     = $param[ 'articlesStatus' ];
        $data[ 'start_time' ]      = time ();

        if ( Db ::name ('article') -> where ('id' , $param[ 'id' ]) -> update ($data) ) {
            $this -> return_msg (200 , '文章修改成功');
        }
        else {
            $this -> return_msg (400 , '文章修改失败');
        }
    }

    /**
     * 获取二级菜单
     *
     * @param int $[id] [<父菜单id>]
     *
     * @return [json] [菜单]
     */
    public function get_menu ()
    {

        $param = $this -> params;
        $list  = Db ::name ('tags') -> where ('e_id' , $param[ 'id' ]) -> select ();
        if ( $list ) {
            $this -> return_msg (200 , 'ok' , $list);
        }
        else {
            $this -> return_msg (400 , 'fk');
        }

    }

    /**
     * 删除
     *
     * @param int $[id] [<文章 id>]
     *
     * @return [json] [删除结果]
     */
    public function article_del ()
    {
        $param = $this -> params;

        if ( Db ::name ('article') -> delete ($param[ 'id' ]) ) {
            $this -> return_msg (200 , '已删除');
        }
        else {
            $this -> return_msg (400 , '删除失败');
        }

    }

    /**
     * 修改状态
     *
     * @param int $[id] [文章 id]
     * @param int $[status] [<状态>]
     *
     * @return [json] [删除结果]
     */
    public function article_status ()
    {
        $param             = $this -> params;
        $param[ 'status' ] = $param[ 'status' ] == 1 ? 2 : 1;

        if ( Db ::name ('article') -> where ('id' , $param[ 'id' ]) -> update ([ 'statusifyID' => $param[ 'status' ] ]) ) {
            $this -> return_msg (200 , '修改成功');
        }
        else {
            exit(var_dump (Db ::getLastSql ()));
            $this -> return_msg (400 , '修改失败');
        }

    }
}
