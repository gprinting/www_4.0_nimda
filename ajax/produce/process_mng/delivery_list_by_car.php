<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/ProcessMngDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ProcessMngDAO();

$param = array();
$param['carname'] = $fb->form("carname");
$rs = $dao->selectDlvrListByCar($conn, $param);
$i = 0;
while ($rs && !$rs->EOF) {
    $json_string["product"][$i] = array(
        'order_num'	=>		 $rs->fields['order_num'],
        'order_detail_num'	=>		 $rs->fields['order_detail_dvs_num'],
        'order_detail'	=>		 $rs->fields['order_detail'],
        'addr'		=>  	 $rs->fields["addr"],
        'title'		=> 		 $rs->fields["title"],
        'dlvr_way' 	=> 		 $rs->fields["dlvr_way"],
        'amt' 		=> 		 $rs->fields["amt"],
        'count' 	=> 		 $rs->fields["count"],
        'order_state' => 	 $rs->fields['state']
    );
    $i++;
    $rs->MoveNext();
}

if($json_string != null) {
    echo json_encode($json_string);
}
$conn->close();

?>