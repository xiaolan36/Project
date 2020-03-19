<?php

namespace app\index\controller;

use think\facade\Session;
use think\helper\Time;
use think\Validate;
use think\Controller;
use think\Db;
use sms\REST;

class Login extends Controller
{

    /**
     *登录
     * @return mixed|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * User: lanzh
     * Date: 2020/2/15 18:56
     */
    public function login ()
    {
        if ( $this -> request -> isAjax () ) {
            $data[ 'mobile' ]    = trim ($this -> request -> param ('account'));
            $data[ 'login_pwd' ] = trim ($this -> request -> param ('password'));
            $rule                = [
                'mobile|手机号码'    => 'require|mobile' ,
                'login_pwd|登录密码' => 'require|alphaDash|min:6' ,
            ];

            $validate = Validate ::make ($rule);
            $result   = $validate -> check ($data);

            if ( !$result ) {
                return json (array ( 'status' => 0 , 'msg' => $validate -> getError () ));
            }

            $user = Db ::name ('user') -> field ('login_pwd,login_salt,userid,status,phone') -> where (array ( 'phone' => $data[ 'mobile' ] )) -> find ();
            if ( empty($user) ) {
                return json (array ( 'status' => 0 , 'msg' => '账号不存在' ));
            }
            else {
                if ( md5 (md5 ($data[ 'login_pwd' ]) . $user[ 'login_salt' ]) !== $user[ 'login_pwd' ] ) {
                    return json (array ( 'status' => 0 , 'msg' => '密码错误' ));
                }
                if ( $user[ 'status' ] !== 1 ) {
                    return json (array ( 'status' => 0 , 'msg' => '账号已被锁定' ));
                }
            }
            unset($user[ 'login_pwd' ]);
            unset($user[ 'login_salt' ]);
            Session ::set ('user' , $user , 'index');
            // 启动事务
            Db ::startTrans ();
            try {
                //记录登录时间
                Db ::name ('user') -> where ('userid' , $user[ 'userid' ]) -> update (array (
                    'login_time' => time () ,
                    'session_id' => session_id ()
                ));

                // 今日开始和结束的时间戳
                list($start , $end) = Time ::today ();
                $map[]  = [ 'addtime' , 'between' , "{$start},{$end}" ];
                $map[]  = [ 'uid' , 'eq' , $user[ 'userid' ] ];
                $result = Db ::name ('count_online') -> where ($map) -> find ();
                if ( empty($result) ) {
                    Db ::name ('count_online') -> insert (array ( 'uid' => $user[ 'userid' ] , 'addtime' => time () ));
                }
                // 提交事务
                Db ::commit ();
                return json (array ( 'status' => 1 , 'msg' => '登录成功' , 'url' => url ('/') ));
            } catch ( \Exception $e ) {
                // 回滚事务
                Db ::rollback ();
                return json (array ( 'status' => 0 , 'msg' => '登录失败' ));
            }

        }
        else {
            $App_link = Db ::name ('system') -> field ('app_link') -> find ();
            $this -> assign ('app_link' , $App_link);
            return $this -> fetch ();
        }

    }

    /**
     *注册
     * @return mixed|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * User: lanzh
     * Date: 2020/2/15 18:57
     */
    public function register ()
    {
        if ( $this -> request -> isAjax () ) {
            $data[ 'mobile' ]      = trim ($this -> request -> param ('mobile'));
            $data[ 'code' ]        = trim ($this -> request -> param ('code'));
            $data[ 'login_pwd' ]   = trim ($this -> request -> param ('login_pwd'));
            $data[ 'relogin_pwd' ] = trim ($this -> request -> param ('relogin_pwd'));
            $data[ 'pid' ]         = trim ($this -> request -> param ('pid'));
            $rule                  = [
                'mobile|手机号码'      => 'require|mobile' ,
                'code|验证码'         => 'require|number|length:6' ,
                'login_pwd|登录密码'   => 'require|alphaDash|min:6' ,
                'relogin_pwd|二次密码' => 'require|confirm:login_pwd' ,
                'pid|推荐人'          => 'require|number'
            ];

            $validate = Validate ::make ($rule);
            $result   = $validate -> check ($data);

            if ( !$result ) {
                return json (array ( 'status' => 0 , 'msg' => $validate -> getError () ));
            }

            $code = Db ::name ('smscode') -> where (array (
                'phone' => $data[ 'mobile' ] ,
                'type'  => 'reg'
            )) -> order ('send_time' , 'desc') -> find ();
            if ( !$code || $data[ 'code' ] != $code[ 'code' ] || time () - $code[ 'send_time' ] > 300 ) {
                return json (array ( 'status' => 0 , 'msg' => '验证码错误或已失效' ));
            }

            $user = Db ::name ('user') -> field ('userid,all_tarent_id,pid,gid,ggid') -> where (array (
                'user_code' => $data[ 'pid' ]
            )) -> find ();
            if ( empty($user) ) {
                return json (array ( 'status' => 0 , 'msg' => '推荐人不存在' , ));
            }

            $user_ip = Db ::name ('user') -> where (array ( 'reg_ip' => $this -> request -> ip () )) -> count ();
            if ( $user_ip > 0 ) {
                return json (array ( 'status' => 0 , 'msg' => '请勿重复注册' , ));
            }

            $user_phone = Db ::name ('user') -> where (array ( 'phone' => $data[ 'mobile' ] )) -> count ();
            if ( $user_phone > 0 ) {
                return json (array ( 'status' => 0 , 'msg' => '此账号已注册' , ));
            }

            $info[ 'pid' ]           = $user[ 'userid' ];
            $info[ 'gid' ]           = $user[ 'pid' ];
            $info[ 'ggid' ]          = $user[ 'gid' ];
            $info[ 'all_tarent_id' ] = $user[ 'all_tarent_id' ] == '' ? $user[ 'userid' ] : $user[ 'all_tarent_id' ] . ',' . $user[ 'userid' ];
            $info[ 'phone' ]         = $data[ 'mobile' ];
            $info[ 'user_code' ]     = rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9);
            $info[ 'login_salt' ]    = rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9);
            $info[ 'login_pwd' ]     = md5 (md5 ($data[ 'login_pwd' ]) . $info[ 'login_salt' ]);
            $info[ 'reg_time' ]      = time ();
            $info[ 'reg_ip' ]        = $this -> request -> ip ();
            $result                  = Db ::name ('user') -> insert ($info);

            if ( $result ) {
                $link = Db ::name ('system') -> field ('app_link') -> find ();
                return json (array ( 'status' => 1 , 'msg' => '注册成功' , 'url' => $link[ 'app_link' ] ));
            }
            else {
                return json (array ( 'status' => 0 , 'msg' => '注册失败' ));
            }
        }
        else {
            $id = $this -> request -> param ('id') > 0 ? intval ($this -> request -> param ('id')) : null;

            $this -> assign ('code' , $id);
            $App_link = Db ::name ('system') -> field ('app_link') -> find ();
            $this -> assign ('app_link' , $App_link);
            return $this -> fetch ();
        }
    }

    /**
     *忘记密码
     * User: lanzh
     * Date: 2020/2/24 10:36
     */
    public function getpsw ()
    {
        if ( $this -> request -> isAjax () ) {
            $data[ 'mobile' ]      = trim ($this -> request -> param ('mobile'));
            $data[ 'code' ]        = trim ($this -> request -> param ('code'));
            $data[ 'login_pwd' ]   = trim ($this -> request -> param ('login_pwd'));
            $data[ 'relogin_pwd' ] = trim ($this -> request -> param ('relogin_pwd'));
            $rule                  = [
                'mobile|手机号码'      => 'require|mobile' ,
                'code|验证码'         => 'require|number|length:6' ,
                'login_pwd|登录密码'   => 'require|alphaDash|min:6' ,
                'relogin_pwd|二次密码' => 'require|confirm:login_pwd' ,
            ];

            $validate = Validate ::make ($rule);
            $result   = $validate -> check ($data);
            if ( !$result ) {
                return json (array ( 'status' => 0 , 'msg' => $validate -> getError () ));
            }

            $user_phone = Db ::name ('user') -> where (array ( 'phone' => $data[ 'mobile' ] )) -> count ();
            if ( $user_phone == 0 ) {
                return json (array ( 'status' => 0 , 'msg' => '此账号不存在' , ));
            }

            $code = Db ::name ('smscode') -> where (array (
                'phone' => $data[ 'mobile' ] ,
                'type'  => 'editpwd'
            )) -> order ('send_time' , 'desc') -> find ();
            if ( !$code || $data[ 'code' ] != $code[ 'code' ] || time () - $code[ 'send_time' ] > 300 ) {
                return json (array ( 'status' => 0 , 'msg' => '验证码错误或已失效' ));
            }

            $info[ 'login_salt' ] = rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9);
            $info[ 'login_pwd' ]  = md5 (md5 ($data[ 'login_pwd' ]) . $info[ 'login_salt' ]);
            $result               = Db ::name ('user') -> where ('phone' , $data[ 'mobile' ]) -> update ($info);
            if ( $result ) {
                return json (array ( 'status' => 1 , 'msg' => '修改成功' ));
            }
            else {
                return json (array ( 'status' => 0 , 'msg' => '修改失败' ));
            }
        }
        else {

            return $this -> fetch ();
        }
    }

    /**
     *发送验证码
     * @return \think\response\Json
     * User: lanzh
     * Date: 2020/2/15 18:57
     */
    public function send_sms ()
    {
        $phone = $this -> request -> param ('mobile');
        $type  = $this -> request -> param ('type');
        $rule  = [
            'mobile|手机格式' => 'require|mobile' ,
            'type|类型'     => 'require|lower' ,
        ];

        $validate = Validate ::make ($rule);
        $result   = $validate -> check ($this -> request -> param ());
        if ( !$result ) {
            return json (array ( 'msg' => $validate -> getError () ));
        }

        $count = Db ::name ('user') -> where (array ( 'phone' => $phone )) -> count ();
        switch ( $type ) {
            case 'reg':
                if ( $count > 0 ) {
                    return json (array ( 'status' => 0 , 'msg' => '手机号已注册,请登录!' ));
                }
                break;
            case 'editpwd':
                if ( $count == 0 ) {
                    return json (array ( 'status' => 0 , 'msg' => '手机号不存在!' ));
                }
                break;

            default:
                break;
        }
        //判断发送次数
        $Maxcount             = 10;
        $todaytime            = strtotime (date ("Y-m-d"));
        $Code                 = Db ::name ('smscode');
        $where[ 'phone' ]     = $phone;
        $where[ 'send_time' ] = array (
            'GT' ,
            $todaytime
        );
        $count                = $Code -> where ($where) -> count ();
        if ( $count >= $Maxcount ) {
            $data[ 'msg' ] = "验证码发送频繁,请明天再试";
        }
        else {
            $where = array (
                'send_time' => array ( 'GT' , time () - 60 ) ,
                'phone'     => $phone ,
            );
            $count = $Code -> where ($where) -> count ();
            if ( $count ) {
                $data[ 'msg' ] = "验证码发送频繁,请稍后再试";
            }
            else {

                $smscode = rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9);

                //初始化
                $curl = curl_init ();
                curl_setopt ($curl , CURLOPT_HTTPHEADER , array ( 'Expect:' ));
                curl_setopt ($curl , CURLOPT_URL , 'http://www.467890.com/Admin/index.php/Message/send');
                curl_setopt ($curl , CURLOPT_HEADER , 0);
                curl_setopt ($curl , CURLOPT_RETURNTRANSFER , 1);
                curl_setopt ($curl , CURLOPT_POST , 1);
                $post_data = json_encode (array (
                    "uid"     => "6493" ,
                    "pwd"     => 'fWU99wdl' ,
                    "mobile"  => "$phone" ,
                    "content" => "【惠万家】您的验证码为{$smscode}，请于5分钟内正确输入，如非本人操作，请忽略此短信"
                ));
                curl_setopt ($curl , CURLOPT_POSTFIELDS , $post_data);
                $res = curl_exec ($curl);
                curl_close ($curl);
                $result = json_decode ($res , 1);

                // 初始化REST SDK4
                //$result = $this -> sendTemplateSMS ($phone , array ( $smscode ) , 563144);
                if ( $result && $result[ 'status' ] == 'success' ) {
                    Db ::name ('smscode') -> insert (array (
                        'phone'     => $phone ,
                        'code'      => $smscode ,
                        'send_time' => time () ,
                        'type'      => $type
                    ));
                    $data[ 'msg' ]    = '验证码已发送';
                    $data[ 'status' ] = 1;
                }
                else {
                    $data[ 'msg' ] = '验证码发送失败';
                }
            }
            return json ($data);
        }
    }

    /**
     * 发送模板短信
     *
     * @param to 手机号码集合,用英文逗号分开
     * @param datas 内容数据 格式为数组 例如：array('Marry','Alon')，如不需替换请填 null
     * @param $tempId 模板Id
     */
    function sendTemplateSMS ( $to , $datas , $tempId )
    {
        $accountSid = '8a216da86812593601685006e01e1ce7';
        //主帐Token
        $accountToken = '1e3f1e26c37f49fe84dec7acf432e719';
        //应用Id
        $appId = '8aaf07086812057f0168500d2a941cb9';
        //请求地址，格式如下，不需要写https://
        $serverIP = 'app.cloopen.com';

        //请求端口
        $serverPort = '8883';
        //REST版本号
        $softVersion = '2013-12-26';
        // 初始化REST SDK

        $rest = new REST($serverIP , $serverPort , $softVersion);
        $rest -> setAccount ($accountSid , $accountToken);
        $rest -> setAppId ($appId);
        $result = $rest -> sendTemplateSMS ($to , $datas , $tempId);
        if ( $result == null ) {
            return false;
            echo "result error!";
            exit();
        }
        if ( $result -> statusCode != 0 ) {
            return false;
            echo "error code :" . $result -> statusCode . "<br>";
            echo "error msg :" . $result -> statusMsg . "<br>";
            //TODO 添加错误处理逻辑
        }
        else {
            return true;
            //TODO 添加成功处理逻辑
        }
    }
}