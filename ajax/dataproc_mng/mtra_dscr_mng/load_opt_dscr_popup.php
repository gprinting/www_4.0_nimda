<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/set/MtraDscrMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$mtraDAO = new MtraDscrMngDAO();

//옵션 설명 일련번호
$opt_dscr_seq = $fb->form("opt_dscr_seq");
$param = array();

//옵션 설명 수정일때
if ($opt_dscr_seq) {

    //옵션 설명
    $param["table"] = "opt_dscr";
    $param["col"] = "name, dscr";
    $param["where"]["opt_dscr_seqno"] =  $opt_dscr_seq;
    $result = $mtraDAO->selectData($conn, $param);

    //html data 셋팅
    $param = array();
    $param["name"] = $result->fields["name"];
    $param["dscr"] = $result->fields["dscr"];
    $param["opt_dscr_seqno"] = $opt_dscr_seq;

}

$html = getOptDscrHtml($param);

echo $html;
$conn->close();
?>
