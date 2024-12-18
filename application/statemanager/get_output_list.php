<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-11-06
 * Time: 오후 6:53
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

$rs = $dao->selectOutputWaiting($conn, $param);


$json['result'] = array();
while($rs && !$rs->EOF) {
    $typset = array();
    $typset['SheetTypsetSeqno'] = $rs->fields['sheet_typset_seqno'];
    $typset['TypsetNum'] = $rs->fields['typset_num'];
    $typset['OriginState'] = $rs->fields['state'];
    $typset['Amt'] = $rs->fields['print_amt'] . $rs->fields['print_amt_unit'];
    $typset['Paper'] = $rs->fields['paper_name'] . " " . $rs->fields['paper_color'] . " " . $rs->fields['paper_basisweight'];
    $typset['TypsetTitle'] = $rs->fields['print_title'];
    $typset['OutputPlace'] = $rs->fields['print_etprs'];
    $typset['RegiDate'] = $rs->fields['regi_date'];
    $typset['ImagePath'] = $dao->selectOutputImagePath($conn, $rs->fields['sheet_typset_seqno']);
    array_push($json['result'], $typset);

    $rs->MoveNext();
}

echo json_encode($json);

?>