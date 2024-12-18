<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-10-28
 * Time: 오후 2:58
 */

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/classes/dprinting/StateManager/StateManager.php");


$fb = new FormBean();
$param = array();
foreach($fb->fb as $key=>$value)
{
    $param[$key] = $value;
}

$manager = new StateManager($param['order_detail_file_num']);
$manager->ToNextState();



?>