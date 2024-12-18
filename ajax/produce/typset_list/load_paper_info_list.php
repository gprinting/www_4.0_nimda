<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();

$param = array();
$param["table"] = "sheet_typset";
$param["col"] = "paper_name, paper_dvs ,paper_color ,paper_basisweight";
$param["where"]["sheet_typset_seqno"] = $fb->form("seqno");

$rs = $dao->selectData($conn, $param);

if( $rs->fields["paper_name"] ) {
    $search_check = $rs->fields["paper_name"] . "|" . $rs->fields["paper_dvs"]
 . "|" . $rs->fields["paper_color"] . "|" . $rs->fields["paper_basisweight"];
} else {
    $search_check = "";
}

//종이 정보리스트
$param = array();
$param["extnl_etprs_seqno"] = $fb->form("extnl_etprs_seqno");
$param["search_check"] = $search_check;
$param["sorting"] = $fb->form("sorting");
$param["sorting_type"] = $fb->form("sorting_type");

$rs = $dao->selectPaperInfoList($conn, $param);

$list = makePaperInfoListHtml($rs, $param);

echo $list;
$conn->close();
?>
