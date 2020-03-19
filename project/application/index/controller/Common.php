<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\facade\Session;
use think\Validate;

/**
 *主控制器
 */
class Common extends Controller
{

    protected $request; //用来处理参数
    protected $validate; //用来验证请求参数
    protected $params; //过滤后符合要求的参数
    protected $rules = array (

        //user
        'User' => array (
            //
            'index' => array () ,
        )
    );

    //初始化
    protected function initialize ()
    {
        parent ::initialize ();

        $user = Session ::get ('user' , 'index');

        if ( empty($user) ) {
            $this -> redirect ('Login/login');
        }

        $res = Db ::name ('user') -> field ('status,session_id') -> where ('userid' , $user[ 'userid' ]) -> find ();
        if ( $res[ 'status' ] != 1 ) {
            $this -> success ('您的账号已锁定，请联系管理员' , 'Index/Login/login');
        }

        if ( $res[ 'session_id' ] != session_id () ) {
            $this -> success ('您的账号在他处登录，您被迫下线' , 'Index/Login/login');
        }
        $this -> count_user ($user[ 'userid' ]);
    }

    //统计在线
    protected function count_user ( $userid )
    {
        $res = Db ::table ('think_session') -> where ('session_id' , $userid) -> count ();
        if ( $res ) {
            Db ::table ('think_session') -> where ('session_id' , $userid) -> setField ('session_expire' , time ());
        }
        else {
            Db ::table ('think_session') -> insert (array ( 'session_id' => $userid , 'session_expire' => time () ));
        }
    }



    /**
     * 验证器
     *
     * @param  [ary] $arr [传递过来的参数]
     *
     * @return [ary]      [返回验证通过的参数]
     */
    public function check_params ( $arr )
    {

        //获取参数的验证规则
        $rule = $this -> rules[ $this -> request -> controller () ][ $this -> request -> action () ];
        //验证参数并返回错误
        $this -> validate = new Validate($rule);
        if ( !$this -> validate -> check ($arr) ) {
            $this -> return_msg (400 , $this -> validate -> getError ());
        }

        //通过验证返回
        // array_shift($arr);//请求的路由和方法名会成为第一个元素??，暂时不觉得有用就删掉了
        return $arr;

    }

    /**
     * 检查验证码
     *
     * @param  [string] $user_name [用户名]
     * @param  [int]     $code      [验证码]
     *
     * @return [json]              [检查结果]
     */
    public function check_code ( $username , $code )
    {

        //检查验证码是否正确
        $md5_code = md5 ($username . '_' . md5 ($code));
        if ( session ($username . '_code') !== $md5_code ) {
            $this -> return_msg (400 , '验证码错误');
        }

        //检查是否超时
        $last_time = session ($username . 'last_send_time');
        if ( time () - $last_time > 600 ) {
            $this -> return_msg (400 , '验证超时,请在5分钟内验证');
        }

        //删除session验证码
        session ($username . '_code' , null);
    }

    /**
     * /封装json输出
     *
     * @param  [intval] $code [状态码]
     * @param  [string] $msg  [错误详情]
     * @param array $data [返回的数据]
     *
     * @return [json]       [description]
     */
    public function return_msg ( $code , $msg , $data = [] )
    {

        $return_data[ 'status' ] = $code;
        $return_data[ 'msg' ]    = $msg;
        if ( !empty($data) ) {
            $return_data[ 'data' ] = $data;
        }
        echo json_encode ($return_data);
        exit;
    }

}