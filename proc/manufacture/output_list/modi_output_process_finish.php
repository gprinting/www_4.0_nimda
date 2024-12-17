<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/output_mng/OutputListDAO.inc");
include_once(INC_PATH . '/com/dprinting/CypressDAO.inc');
include_once(INC_PATH . "/common_lib/cypress_file.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$CypressDAO = new CypressDAO();
$OutputDAO = new OutputListDAO();
$util = new CommonUtil();


$sheet_typset_seqno_arr = explode(',',$fb->form("seqno"));
echo $sheet_typset_seqno_arr;
exit;
$param = array();
$param['from_state'] = "2220";
$param['to_state'] = "3120";

foreach($sheet_typset_seqno_arr as $sheet_typset_seqno) {
    $rs = $OutputDAO->selectOrderDetailFileNumFromSheetTypsetSeqno($conn, $sheet_typset_seqno);

    while ($rs && !$rs->EOF) {
        $param['order_detail_file_num'] = $rs->fields['order_detail_file_num'];
        $param['sheet_typset_seqno'] = $rs->fields['sheet_typset_seqno'];

        $CypressDAO->updateState($conn, $param);

        $rs->MoveNext();
    }
}

$conn->Close();
echo $check;
?>
