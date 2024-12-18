<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/ProcessMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ProcessMngDAO();

$param = array();
$param['order_detail_num'] = $fb->form("order_detail_num");
$changestate = $fb->form("changestate_yn");

$rs = $dao->selectBunGroup($conn, $param);
$param['bun_dlvr_order_num'] = $rs->fields['bun_dlvr_order_num'];
$param['bun_group'] = $rs->fields['bun_group'];

$order_detail_num = array();
$order_detail_num[0] = $fb->form("order_detail_num");
$param['order_detail_num'] = $order_detail_num;

$order_state = $rs->fields['state'];

if ($changestate == "Y") { //출고처리 && order_state이 출고대기일 경우 출고완료로 상태변경
    $param['state'] = '3320';
    $rs_update = $dao->updateOrderState($conn, $param);

    if ($rs_update) {
        $order_state = '3320';
    }
}


$rs1 = $dao->selectBunGroupSeq($conn, $param);

$seq = "";
$after_processes = array();
$opt_processes = array();

while ($rs1 && !$rs1->EOF) {
    $param['order_common_seqno'] = $rs1->fields["order_common_seqno"];
    $rs3 = $dao->selectAfterProcess($conn, $param);

    while ($rs3 && !$rs3->EOF) {
        $after_processes[$param['order_common_seqno']] .= $rs3->fields['after_name'] . " / ";
        $rs3->MoveNext();
    }

    $rs4 = $dao->selectOption($conn, $param);

    while ($rs4 && !$rs4->EOF) {
        $opt_processes[$param['order_common_seqno']] .= $rs4->fields['opt_name'] . " / ";
        $rs4->MoveNext();
    }

    $seq .= "'".$rs1->fields["order_common_seqno"]."',";
    $rs1->MoveNext();
}

$param["seqs"] = substr($seq, 0, -1);

$rs2 = $dao->selectDeliveryWaitinList($conn, $param);

$i = 0;
while ($rs2 && !$rs2->EOF) {
    $order_state = $rs2->fields['state'];
    $dlvr_way = $rs2->fields["dlvr_way"];

    //직배라면 member테이블에 있는 직배정보 가져오기
    if($dlvr_way == "02") {
        $param["member_seqno"] = $rs2->fields["member_seqno"];

        $rs_direct = $dao->selectDirectDlvrInfo($conn, $param);

        $dlvr_way = $rs_direct->fields["dlvr_code"];
    }

    $json_string["product"][$i] = array(
        'order_num'	=>		 $rs2->fields['order_num'],
        'order_detail_num'	=>		 $rs2->fields['order_detail_dvs_num'],
        'addr'		=>  	 $rs2->fields["addr"],
        'title'		=> 		 $rs2->fields["title"],
        'dlvr_way' 	=> 		 $rs2->fields["dlvr_way"],
        'amt' 		=> 		 $rs2->fields["amt"],
        'count' 	=> 		 $rs2->fields["count"],
        'order_state' => 	 $order_state,
        'after'		=>	     $after_processes[$rs2->fields["order_common_seqno"]],
        'option'		=>	 $opt_processes[$rs2->fields["order_common_seqno"]]
    );
    $i++;
    $rs2->MoveNext();
}

if($json_string != null) {
    echo json_encode($json_string);
}

?>