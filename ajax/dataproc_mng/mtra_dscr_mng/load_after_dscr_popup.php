<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/set/MtraDscrMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$mtraDAO = new MtraDscrMngDAO();

//후공정 설명 일련번호
$after_dscr_seq = $fb->form("after_dscr_seq");
$param = array();

//후공정 설명 수정일때
if ($after_dscr_seq) {

    //후공정 설명
    $param["table"] = "after_dscr";
    $param["col"] = "name, dscr";
    $param["where"]["after_dscr_seqno"] =  $after_dscr_seq;
    $result = $mtraDAO->selectData($conn, $param);

    //html data 셋팅
    $param = array();
    $param["name"] = $result->fields["name"];
    $param["dscr"] = $result->fields["dscr"];
    $param["after_dscr_seqno"] = $after_dscr_seq;

}

$html = getAfterDscrHtml($param);

echo $html;

$conn->close();
?>
