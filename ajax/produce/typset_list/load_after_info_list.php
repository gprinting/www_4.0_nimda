<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();

$after_name = $fb->form("after_name");
$depth1 = $fb->form("after_depth1");
$depth2 = $fb->form("after_depth2");
$depth3 = $fb->form("after_depth3");

if (!$depth1) {
    $depth1 = "-";
}
if (!$depth2) {
    $depth2 = "-";
}
if (!$depth3) {
    $depth3 = "-";
}

//출력 정보리스트
$param = array();
$param["extnl_etprs_seqno"] = $fb->form("extnl_etprs_seqno");
$param["sorting"] = $fb->form("sorting");
$param["sorting_type"] = $fb->form("sorting_type");

if ($fb->form("after_name")) {
    $param["search_check"] = $after_name . "|". 
        $depth1 . "|". 
        $depth2 . "|". 
        $depth3;
}

$rs = $dao->selectAfterInfoList($conn, $param);
$list = makeAfterInfoListHtml($rs, $param);

echo $list;
$conn->close();
?>
