<?php

namespace app\admin\Controller;

use think\Controller;
use think\Db;
use think\facade\Request;
use think\facade\Session;
use think\Validate;
use think\facade\Env;
use PHPExcel_IOFactory;
use PHPExcel;

/**
 *主控制器
 */
class Common extends Controller
{

    protected $request; //用来处理参数s
    protected $validate; //用来验证请求参数
    protected $params; //过滤后符合要求的参数
    protected $rules = array (

        //用户列表
        'User'    => array (
            //主页条件搜索
            'index'               => array (
                'statusifyID' => 'number' ,
                'start'       => 'date' ,
                'end'         => 'date' ,
                'searchInput' => 'number' ,
            ) ,
            //添加用户
            'user_add'            => array () ,
            'user_add_s'          => array (
                'uname|上级账号'  => 'require|mobile' ,
                'username|账号' => 'require|mobile' ,
                'password|密码' => 'require|min:6' ,
                'money|余额'    => "require|number" ,
            ) ,

            //修改用户
            'user_edit_s'         => array (
                'pid'       => 'number' ,
                'login_pwd' => 'alphaDash|min:6' ,
                'tx_pwd'    => 'alphaDash|min:6' ,
                'id'        => 'require|number' ,

            ) ,

            //删除用户
            'user_del'            => array (
                'id' => 'require|number' ,
            ) ,
            //修改用户
            'user_edit'           => array (
                'ids' => 'require|number' ,
            ) ,
            //修改用户状态
            'user_status'         => array (
                'id'     => 'require|number' ,
                'status' => 'require|number' ,
            ) ,

            //--------------------
            //抢单列表条件搜索
            'user_order'          => array (
                'statusifyID' => 'number' ,
                'start'       => 'date' ,
                'end'         => 'date' ,
                'searchInput' => 'alphaNum' ,
            ) ,
            //删除抢单列表
            'user_order_del'      => array (
                'id' => 'require|number' ,
            ) ,
            //--------------------
            //理财
            'investment'          => array (
                'statusifyID' => 'number' ,
                'start'       => 'date' ,
                'end'         => 'date' ,
                'searchInput' => 'alphaNum' ,
            ) ,
            //删除理财
            'user_investment_del' => array (
                'id' => 'require|number' ,
            ) ,
            //资金明细
            'money_info'          => array (
                'statusifyID' => 'number' ,
                'start'       => 'date' ,
                'end'         => 'date' ,
                'searchInput' => 'alphaNum' ,
            ) ,
            //删除资金明细
            'user_money_info_del' => array (
                'id' => 'require|number' ,
            ) ,
            //银行卡管理
            'user_bank'           => array (
                'statusifyID' => 'number' ,
                'start'       => 'date' ,
                'end'         => 'date' ,
                'searchInput' => 'alphaNum' ,
            ) ,
            //删除银行卡
            'del_bank'            => array (
                'id' => 'require|number' ,
            )

        ) ,
        //提现充值管理
        'Wealth'  => array (
            //充值
            'recharge'          => array (
                'statusifyID' => 'number' ,
                'start'       => 'date' ,
                'end'         => 'date' ,
                'searchInput' => 'chsDash' ,
            ) ,
            //修改充值状态
            'recharge_status'   => array (
                'id'     => 'require|number' ,
                'status' => 'require|number' ,
                'uid'    => 'require|number' ,
                'money'  => 'require' ,
            ) ,
            //删除充值
            'recharge_del'      => array (
                'id' => 'require|number' ,
            ) ,
            //提现
            'withdrawal'        => array (
                'statusifyID' => 'number' ,
                'start'       => 'date' ,
                'end'         => 'date' ,
                'searchInput' => 'chsDash' ,
            ) ,
            //修改提现状态
            'withdrawal_status' => array (
                'id'     => 'require|number' ,
                'status' => 'require|number' ,
                'uid'    => 'require|number' ,
                'money'  => 'require' ,
            ) ,
            //删除提现
            'withdrawal_del'    => array (
                'id' => 'require|number' ,
            ) ,
            //手动充值扣款页面
            'manual'            => array (
                'type' => 'require|lower' ,
            ) ,
            //手动充值扣款
            'manual_edit'       => array (
                'userid|账号' => 'require|mobile' ,
                'money'     => 'require|between:1,1000000' ,
                'type'      => 'require|lower' ,
            ) ,

        ) ,
        //网址配置
        'Config'  => array (
            'index'           => array () ,
            'add_banner'      => array () ,
            'edit_config'     => array (
                'qd_cf|*抢单余额比列 ：'        => [ 'regex' => '/^[0-9]+(.[0-9]{1,4})?$/' ] ,
                'qd_min|*抢单最小比列 ：'       => [ 'regex' => '/^[0-9]+(.[0-9]{1,4})?$/' ] ,
                'qd_nd|*抢单难度，数组(1-10) ：' => 'number|between:1,10' ,
                'qd_yjjc|*佣金加成：'         => [ 'regex' => '/^[0-9]+(.[0-9]{1,4})?$/' ] ,
                'qd_minmoney|*抢单最低额度 ：'  => 'number' ,
                'min_recharge|*最低充值额度 ：' => 'number' ,
                'mix_withdraw|*最小提现额度 ：' => 'number' ,
                'max_withdraw|*最大提现额度 ：' => 'number' ,
                'team_oneyj|*一代佣金比例：'    => [ 'regex' => '/^[0-9]+(.[0-9]{1,4})?$/' ] ,
                'team_twoyj|*二代佣金比例：'    => [ 'regex' => '/^[0-9]+(.[0-9]{1,4})?$/' ] ,
                'team_threeyj||*三代佣金比例：' => [ 'regex' => '/^[0-9]+(.[0-9]{1,4})?$/' ] ,
                'start|*抢单开始时间 ：'        => 'number|between:0,24' ,
                'end|*抢单结束时间 ：'          => 'number|between:0,24' ,
                'level1|*100-24999：'     => 'number' ,
                'level2|*25000-49999 ：'  => 'number' ,
                'level3|*50000-74999 ：'  => 'number' ,
                'level4|*75000-99999 ：'  => 'number' ,
                'level5|*100000以上 ：'     => 'number' ,
                'app_link|*app下载链接 ：'    => 'url' ,
                'kefu_link|*客服链接：'       => 'url' ,
            ) ,
            //轮播图
            'banner'          => array (
                'start'       => 'date' ,
                'end'         => 'date' ,
                'searchInput' => 'chsDash' ,
            ) ,

            //添加轮播图
            'banner_add_s'    => array (
                'articleTitle'       => 'require' ,
                'btnUploadFileThumb' => 'image:' ,
                'articlesStatus'     => 'require|number' ,
            ) ,
            //修改轮播图
            'banner_edit_s'   => array (
                'articleTitle'       => 'require' ,
                'btnUploadFileThumb' => 'image:' ,
                'articlesStatus'     => 'require|number' ,
                'id'                 => 'require|number' ,
            ) ,
            //删除轮播图
            'banner_del'      => array (
                'id' => 'require|number' ,
            ) ,
            //修改轮播图
            'edit_banner'     => array (
                'ids' => 'require|number' ,
            ) ,
            //修改轮播图状态
            'banner_status'   => array (
                'id'     => 'require|number' ,
                'status' => 'require|number' ,
            ) ,
            //银行卡链接
            'link'            => array (
                'start'       => 'date' ,
                'end'         => 'date' ,
                'searchInput' => 'chsDash' ,
            ) ,
            //添加银行卡链接
            'link_add_s'      => array (
                'bankname'   => 'require' ,
                'username'   => 'require' ,
                'banknum'    => 'require' ,
                'bankdetail' => 'require' ,
                'status'     => 'require|number|between:1,2' ,
            ) ,
            //修改银行卡链接
            'link_edit_s'     => array (
                'bankname'   => 'require' ,
                'username'   => 'require' ,
                'banknum'    => 'require' ,
                'bankdetail' => 'require' ,
                'status'     => 'require|number|between:1,2' ,
                'id'         => 'require|number'
            ) ,
            //删除银行卡链接
            'link_del'        => array (
                'id' => 'require|number' ,
            ) ,
            //修改银行卡链接
            'edit_link'       => array (
                'ids' => 'require|number' ,
            ) ,
            'add_link'        => array () ,
            //修改银行卡链接状态
            'link_status'     => array (
                'id'     => 'require|number' ,
                'status' => 'require|number' ,
            ) ,

            //利息链接
            'interest'        => array (
                'start'       => 'date' ,
                'end'         => 'date' ,
                'searchInput' => 'chsDash' ,
            ) ,

            //添加利息链接
            'interest_add_s'  => array (
                'day'    => 'require|number|between:1,365' ,
                'rate'   => [ 'regex' => '/^[0-9]+(.[0-9]{1,4})?$/' ] ,
                'status' => 'require|number|between:1,2' ,
            ) ,
            //修改利息链接
            'interest_edit_s' => array (
                'day'    => 'require|number|between:1,365' ,
                'rate'   => [ 'regex' => '/^[0-9]+(.[0-9]{1,4})?$/' ] ,
                'status' => 'require|number|between:1,2' ,
                'id'     => 'require|number'
            ) ,
            //删除利息链接
            'interest_del'    => array (
                'id' => 'require|number' ,
            ) ,
            //修改利息链接
            'edit_interest'   => array (
                'ids' => 'require|number' ,
            ) ,
            'add_interest'    => array () ,
            //修改利息链接状态
            'interest_status' => array (
                'id'     => 'require|number' ,
                'status' => 'require|number' ,
            ) ,

        ) ,
        'Goods'   => array (

            //商品管理
            'goods'        => array (
                'statusifyID' => 'number|between:0,2' ,
            ) ,
            'add_goods'    => array () ,
            //添加商品管理
            'goods_add_s'  => array (
                'price' => [ 'regex' => '/^[0-9]+(.[0-9]{1,4})?$/' ] ,
                'name'  => 'require|chsDash' ,
                'shop'  => 'require|chsDash' ,
                'url'   => 'require' ,
                'num'   => 'require|number' ,
            ) ,
            //修改商品管理
            'goods_edit_s' => array (
                'price'              => [ 'regex' => '/^[0-9]+(.[0-9]{1,4})?$/' ] ,
                'name'               => 'require|chsDash' ,
                'shop'               => 'require|chsDash' ,
                'btnUploadFileThumb' => 'image:' ,
                'id'                 => 'require|number' ,
            ) ,
            //删除商品管理
            'goods_del'    => array (
                'id' => 'require|number' ,
            ) ,
            //修改商品管理
            'edit_goods'   => array (
                'ids' => 'require|number' ,
            ) ,
        ) ,
        //文章
        'Article' => array (
            //条件搜索
            'index'          => array (
                'classifyID'  => 'number' ,
                'statusifyID' => 'number' ,
                'start'       => 'date' ,
                'end'         => 'date' ,
                'searchInput' => 'chsDash' ,
            ) ,
            //添加文章
            'article_add_s'  => array (
                'articleTitle'       => 'require' ,
                'articleClassify'    => 'require|number|between:1,10' ,
                'articleAbstract'    => 'require' ,
                'btnUploadFileThumb' => 'image:' ,
                'editorValue'        => 'require' ,
                'articlesStatus'     => 'require|number' ,
            ) ,
            //修改文章
            'article_edit_s' => array (
                'articleTitle'       => 'require' ,
                'articleClassify'    => 'require|number|between:1,10' ,
                'articleAbstract'    => 'require' ,
                'btnUploadFileThumb' => 'image:' ,
                'editorValue'        => 'require' ,
                'articlesStatus'     => 'require|number' ,
                'id'                 => 'require|number' ,
            ) ,
            //获取菜单
            'get_menu'       => array (
                'id' => 'require|number' ,
            ) ,
            //删除文章
            'article_del'    => array (
                'id' => 'require|number' ,
            ) ,
            //修改文章
            'article_edit'   => array (
                'ids' => 'require|number' ,
            ) ,
            //修改文章状态
            'article_status' => array (
                'id'     => 'require|number' ,
                'status' => 'require|number' ,
            ) ,
            'article_add'    => array ()

        ) ,
        //主页
        'Index'   => array (
            'index'          => array () ,
            'end_admin'      => array () ,
            'get_menu'       => array () ,
            'get_info_money' => array () ,
        ) ,
        //统计
        'Count'   => array (
            'index'          => array () ,
            'register_count' => array (
                'start' => 'date' ,
                'end'   => 'date' ,
            ) ,
            'bank_count'     => array (
                'start' => 'date' ,
                'end'   => 'date' ,
            ) ,
            'count_sum'      => array (
                'start' => 'date' ,
                'end'   => 'date' ,
            ) ,
            'user'           => array (
                'searchInput' => 'number' ,
                'start'       => 'date' ,
                'end'         => 'date' ,
            ) ,
            'boss_user'      => array (
                'searchInput' => 'number' ,
                'start'       => 'date' ,
                'end'         => 'date' ,
            ) ,

        ) ,
        //管理员管理
        'Admin'   => array (
            'index'        => array (
                'statusifyID' => 'number' ,
                'start'       => 'date' ,
                'end'         => 'date' ,
                'searchInput' => 'number' ,
            ) ,
            //删除用户
            'admin_del'    => array (
                'id' => 'require|number' ,
            ) ,
            //修改用户
            'admin_edit'   => array (
                'ids' => 'require|number' ,
            ) ,
            //修改用户状态
            'admin_status' => array (
                'id'     => 'require|number' ,
                'status' => 'require|number' ,
            ) ,
            //添加管理员
            'add_admin'    => array (
                'name'        => 'alphaNum' ,
                'password'    => 'alphaDash|min:6' ,
                'statusifyID' => 'number' ,
            ) ,
            'edit_admin'   => array (
                'ids' => 'require|number' ,
            ) ,
            'edit_admin_s' => array (
                'name'        => 'require|alphaDash' ,
                'password'    => 'alphaDash|min:6' ,
                'statusifyID' => 'number' ,
            ) ,

            //权限页面
            'role'         => array () ,
            //修改权限页面
            'edit_role'    => array (
                'ids' => 'require|number' ,
            ) ,
            //修改权限
            'edit_role_s'  => array (
                'roleid'    => 'require|number' ,
                'Character' => 'require|array' ,
            ) ,
            //删除角色
            'role_del'     => array (
                'id' => 'require|number' ,
            ) ,
            //添加角色
            'add_role'     => array (
                'rolename'     => 'chsAlpha' ,
                'role_remarks' => 'chsAlpha' ,
            ) ,

        ) ,
    );

    //初始化
    protected function initialize ()
    {
        parent ::initialize ();
        if ( !Session ::has ('admin') ) {
            $this -> success ('请先登录' , 'Login/admin_hui');
        }

        $res = Db ::name ('admin') -> field ('status,session_id') -> where ('id' , Session ::get ('admin')[ 'id' ]) -> field ('status,session_id') -> find ();

        if ( !$res ) {
            $this -> success ('您的账号已被删除，请联系管理员' , 'Login/admin_hui');
        }

        if ( $res[ 'status' ] != 1 ) {
            $this -> success ('您的账号已锁定，请联系管理员' , 'Login/admin_hui');
        }

        if ( $res[ 'session_id' ] != session_id () ) {
            $this -> success ('您的账号在他处登录，您被迫下线' , 'Login/admin_hui');
        }

        //获取菜单
        $adminid   = Session ::get ('admin')[ 'id' ];
        $menu_list = db () -> query ("SELECT m.* FROM `api_menu` as m ,`api_admin` as u, `api_admin_role` as ur ,`api_menu_role` as rm WHERE u.role=ur.roleid and ur.roleid=rm.r_id AND m.id=rm.m_id AND u.id={$adminid}");

        //权限校验
        if ( !$this -> auth ($menu_list) ) {
            $this -> success ('权限不足' , 'Login/admin_hui');
        }
        $this -> assign ('menu_list' , $menu_list);

        //获取未处理充值提现信息
        $data[ 'recharge' ]   = Db ::name ('recharge') -> where ('status' , 1) -> count ();
        $data[ 'withdrawal' ] = Db ::name ('withdrawal') -> where ('status' , 1) -> count ();
        $this -> assign ('data_count' , $data);

        //路由参数校验
        $this -> request = Request ::instance ();
        $this -> params  = $this -> check_params ($this -> request -> param ());

        //记录日志
        if ( $this -> request -> action () != 'get_info_money' ) {
            $txt = Session ::get ('admin')[ 'name' ];
            $log = '时间:' . date ('y-m-d h:i:s' , time ()) . PHP_EOL . 'IP:' . $this -> request -> ip () . PHP_EOL . '模块：' . $this -> request -> module () . PHP_EOL . '控制器：' . $this -> request -> controller () . PHP_EOL . '方法：' . $this -> request -> action ();
            file_put_contents ($txt . '.txt' , PHP_EOL . $log . PHP_EOL , FILE_APPEND);
            file_put_contents ($txt . '.txt' , PHP_EOL . '-------------------------下一条--------------------------' , FILE_APPEND);
            $log = file_put_contents ('jsonlog.txt' , json_encode (array (
                'time'       => time () ,
                'ip'         => $this -> request -> ip () ,
                'module'     => $this -> request -> module () ,
                'controller' => $this -> request -> controller () ,
                'action'     => $this -> request -> action ()
            )) , FILE_APPEND);
        }
    }

    /**
     *权限验证
     *
     * @param $list
     * User: lanzh
     * Date: 2020/3/3 16:10
     */
    public function auth ( $list )
    {
        if ( strtoupper ($this -> request -> controller ()) == 'INDEX' ) {
            return true;
        }

        foreach ( $list as $key => $val ) {
            if ( $val[ 'parentid' ] != 0 ) {
                $url = strtoupper ($val[ 'url' ]);
                //exit(var_dump (strtoupper ($this -> request -> module ().'/'.$this -> request -> controller ())));
                $C = $this -> cut_str ($url , '/' , 0);
                $A = $this -> cut_str ($url , '/' , 1);
                if ( $C == strtoupper ($this -> request -> module ()) && $A == strtoupper ($this -> request -> controller ()) ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 按符号截取字符串的指定部分
     *
     * @param string $str    需要截取的字符串
     * @param string $sign   需要截取的符号
     * @param int    $number 如是正数以0为起点从左向右截  负数则从右向左截
     *
     * @return string 返回截取的内容
     */
    private function cut_str ( $str , $sign , $number )
    {
        $array  = explode ($sign , $str);
        $length = count ($array);
        if ( $number < 0 ) {
            $new_array  = array_reverse ($array);
            $abs_number = abs ($number);
            if ( $abs_number > $length ) {
                return 'error';
            }
            else {
                return $new_array[ $abs_number - 1 ];
            }
        }
        else {
            if ( $number >= $length ) {
                return 'error';
            }
            else {
                return $array[ $number ];
            }
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
        $this -> validate = Validate ::make ($rule);
        if ( !$this -> validate -> check ($arr) ) {
            $this -> return_msg (400 , $this -> validate -> getError ());
        }
        //通过验证返回
        // array_shift($arr);//请求的路由和方法名会成为第一个元素??，暂时不觉得有用就删掉了
        return $arr;

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

        $return_data[ 'code' ] = $code;
        $return_data[ 'msg' ]  = $msg;
        if ( !empty($data) ) {
            $return_data[ 'data' ] = $data;
        }
        echo json_encode ($return_data);
        exit;
    }

    /**
     * 数据导出excel
     *
     * @param array  $title    标题行名称
     * @param array  $data     导出数据
     * @param string $fileName 文件名
     * @param string $savePath 保存路径
     * @param        $type     是否下载  false--保存   true--下载
     *
     * @return string   返回文件全路径sss
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     */
    public function exportExcel ( $title = array () , $data = array () , $fileName = '' , $savePath = './' , $isDown = false )
    {

        $obj = new \PHPExcel();

        //横向单元格标识
        $cellName = array (
            'A' ,
            'B' ,
            'C' ,
            'D' ,
            'E' ,
            'F' ,
            'G' ,
            'H' ,
            'I' ,
            'J' ,
            'K' ,
            'L' ,
            'M' ,
            'N' ,
            'O' ,
            'P' ,
            'Q' ,
            'R' ,
            'S' ,
            'T' ,
            'U' ,
            'V' ,
            'W' ,
            'X' ,
            'Y' ,
            'Z' ,
            'AA' ,
            'AB' ,
            'AC' ,
            'AD' ,
            'AE' ,
            'AF' ,
            'AG' ,
            'AH' ,
            'AI' ,
            'AJ' ,
            'AK' ,
            'AL' ,
            'AM' ,
            'AN' ,
            'AO' ,
            'AP' ,
            'AQ' ,
            'AR' ,
            'AS' ,
            'AT' ,
            'AU' ,
            'AV' ,
            'AW' ,
            'AX' ,
            'AY' ,
            'AZ'
        );

        $obj -> getActiveSheet (0) -> setTitle ('表格');   //设置sheet名称
        $_row = 1;   //设置纵向单元格标识
        if ( $title ) {
            $_cnt = count ($title);
            $obj -> getActiveSheet (0) -> mergeCells ('A' . $_row . ':' . $cellName[ $_cnt - 1 ] . $_row);   //合并单元格
            $obj -> setActiveSheetIndex (0) -> setCellValue ('A' . $_row , '数据导出：' . date ('Y-m-d H:i:s'));  //设置合并后的单元格内容
            $_row++;
            $i = 0;
            foreach ( $title AS $v ) {   //设置列标题
                $obj -> setActiveSheetIndex (0) -> setCellValue ($cellName[ $i ] . $_row , $v);
                $i++;
            }
            $_row++;
        }

        //填写数据
        if ( $data ) {
            $i = 0;
            foreach ( $data AS $_v ) {
                $j = 0;
                foreach ( $_v AS $_cell ) {
                    $obj -> getActiveSheet (0) -> setCellValue ($cellName[ $j ] . ( $i + $_row ) , $_cell);
                    $j++;
                }
                $i++;
            }
        }

        //文件名处理
        if ( !$fileName ) {
            $fileName = uniqid (time () , true);
        }

        $objWrite = PHPExcel_IOFactory ::createWriter ($obj , 'Excel5');
        if ( $isDown ) {   //网页下载
            header ('pragma:public');
            header ("Content-type:application/vnd.ms-excel");
            header ("Content-Disposition:attachment;filename=$fileName.xls");
            $objWrite -> save ('php://output');
            exit;
        }

        $_fileName = iconv ("utf-8" , "gb2312" , $fileName);   //转码
        $_savePath = $savePath . $_fileName . '.xlsx';
        $objWrite -> save ($_savePath);

        return $savePath . $fileName . '.xlsx';
    }

    /**
     *高精度相加
     * User: lanzh
     * Date: 2020/3/19 10:30
     */
    public static function get_bcadd ( $left , $right , $num )
    {
        return bcadd ($left , $right , $num);
    }

    /**
     *高精度相减
     * User: lanzh
     * Date: 2020/3/19 10:30
     */
    public static function get_bcsub ( $left , $right , $num )
    {
        return bcsub ($left , $right , $num);
    }
}
