<?php

namespace app\admin\controller;

use think\Db;

class Task
{
    //48小时自动解冻
    public function update_order ()
    {
        set_time_limit (0);
        $robstatus = Db ::name ('roborder') -> field ('id') -> where ('status' , 2) -> select ();
        $num       = 1;
        $id        = '';
        if ( $robstatus ) {
            foreach ( $robstatus as $key => $val ) {
                $num++;
                $id .= $val[ 'id' ] . ',';
            }
            Db ::name ('roborder') -> where (array ( 'id' => array ( 'in' , $id ) )) -> update (array (
                'status'     => 1 ,
                'uid'        => null ,
                'uname'      => null ,
                'umoney'     => null ,
                'pipeitime'  => null ,
                'finishtime' => null ,
                'ordernum'   => null
            ));
            echo date ('Y-m-d H:i' , time ()) . '-----ok' . '更新了-->' . $num;
        }
        else {
            echo date ('Y-m-d H:i' , time ()) . '-----no';
        }
    }
}