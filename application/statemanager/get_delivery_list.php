<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-11-13
 * Time: 오전 11:03
 */

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/dprinting/StateManagerDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new StateManagerDAO();
$fb = new FormBean();

$param = array();

foreach($fb->fb as $key=>$value)
{
    $param[$key] = $value;
}

$rs = $dao->selectDeliveryWating($conn, $param);

$json['result'] = array();
while($rs && !$rs->EOF) {
    $countfile = array();
    $countfile['OrderDetailFileNum'] = $rs->fields['order_detail_file_num'];
    $countfile['Title'] = $rs->fields['title'];
    $countfile['Amt'] = $rs->fields['amt'] . $rs->fields['amt_unit_dvs'] ;
    $countfile['Paper'] = $rs->fields['order_detail'];
    $countfile['DlvrKind'] = $rs->fields['invo_cpn'];
    $countfile['RegiDate'] = $rs->fields['order_regi_date'];
    $countfile['OriginState'] = $rs->fields['state'];
    $countfile['Barcode'] = $rs->fields['barcode_num'];
    array_push($json['result'], $countfile);
    $rs->MoveNext();

}

echo json_encode($json);

?>