<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/output_mng/OutputListDAO.inc");
include_once(INC_PATH . '/com/dprinting/MoamoaDAO.inc');
include_once(INC_PATH . "/common_lib/cypress_file.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$MoamoaDAO = new MoamoaDAO();
$OutputDAO = new OutputListDAO();
$util = new CommonUtil();

$check = 1;

$sheet_typset_seqno_arr = explode(',',$fb->form("seqno"));

$param = array();
if($fb->form("state") != null)
    $param['state'] = $fb->form("state");
else {
    $param['state'] = "2320";
}
$param['empl_id'] = $fb->getSession()["id"];

foreach($sheet_typset_seqno_arr as $sheet_typset_seqno) {
    $param['sheet_typset_seqno'] = $sheet_typset_seqno;
    $MoamoaDAO->updateTypsetState($conn, $param);

    $rs = $OutputDAO->selectOrderNumInTypset($conn, $sheet_typset_seqno);
    $typset_num = "";
    while ($rs && !$rs->EOF) {

        $param['ordernum'] = $rs->fields['order_num'];
        $typset_num = $rs->fields['typset_num'];
        $MoamoaDAO->updateProductStatecode($conn, $param);
        $MoamoaDAO->insertStateHistory($conn, $param);
        $rs->MoveNext();
    }

    $start_typset = explode("-", $typset_num)[1];
    if(startsWith($start_typset, "15")) {
        // 수가 같으면 재단대기로 상태변경
        $param['state'] = "2420";
        $MoamoaDAO->updateTypsetState($conn, $param);
        $rs = $OutputDAO->selectOrderNumInTypset($conn, $sheet_typset_seqno);
        while ($rs && !$rs->EOF) {
            $param['ordernum'] = $rs->fields['order_num'];

            $MoamoaDAO->updateProductStatecode($conn, $param);
            $MoamoaDAO->insertStateHistory($conn, $param);
            $rs->MoveNext();
        }
    }
}

$conn->Close();
echo $check;

function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}

?>
