<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/ProcessMngDAO.inc");
include_once(INC_PATH . "/classes/cjparcel/CJparcel.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ProcessMngDAO();

$param = array();
$param['order_detail_num'] = substr($fb->form("order_detail_num"), 0,strlen($fb->form("order_detail_num")) - 2);


$rs = $dao->selectBunGroup($conn, $param);
$param['bun_dlvr_order_num'] = $rs->fields['bun_dlvr_order_num'];
$param['bun_group'] = $rs->fields['bun_group'];
$dlvr_way = $rs->fields['dlvr_way'];

$order_detail_num = array();
$order_detail_num[0] = $fb->form("order_detail_num");
$param['order_detail_num'] = $order_detail_num;

$order_state = $rs->fields['state'];

if($order_state <= '3320') {
    $param['state'] = '3330';
} else {
    $param['state'] = '9120';
}

$rs1 = $dao->selectBunGroupSeq($conn, $param);

$seq = "";

while ($rs1 && !$rs1->EOF) {
    $seq .= "'".$rs1->fields["order_common_seqno"]."',";
    $rs1->MoveNext();
}

$param["seqs"] = substr($seq, 0, -1);

$rs_update = $dao->updateBunOrderCommonState($conn, $param);
$rs_update = $dao->updateBunOrderDetailState($conn, $param);

$rs2 = $dao->selectDeliveryWaitinList($conn, $param);

$i = 0;
while ($rs2 && !$rs2->EOF) {
    $order_state = $rs2->fields['state'];
    $dlvr_way = $rs2->fields["dlvr_way"];

    $json_string["product"][$i] = array(
        'order_num'	=>		 $rs2->fields['order_num'],
        'order_detail_num'	=>		 $rs2->fields['order_detail_dvs_num'],
        'order_detail'	=>		 $rs2->fields['order_detail'],
        'addr'		=>  	 $rs2->fields["addr"],
        'title'		=> 		 $rs2->fields["title"],
        'dlvr_way' 	=> 		 $dlvr_way,
        'amt' 		=> 		 $rs2->fields["amt"],
        'count' 	=> 		 $rs2->fields["count"],
        'order_state' => 	 $order_state
    );
    $i++;
    $rs2->MoveNext();
}

if($json_string != null) {
    echo json_encode($json_string);
}

?>