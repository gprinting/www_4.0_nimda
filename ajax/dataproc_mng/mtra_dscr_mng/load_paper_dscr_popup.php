<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/set/MtraDscrMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$mtraDAO = new MtraDscrMngDAO();

//종이 설명 일련번호
$paper_dscr_seq = $fb->form("paper_dscr_seq");
$param = array();

//종이 설명 수정일때
if($paper_dscr_seq) {

    //종이 설명
    $param["table"] = "paper_dscr";
    $param["col"] = "top, name, dvs, color, basisweight, dscr, purp, paper_sense, able_after, warn";
    $param["where"]["paper_dscr_seqno"] =  $paper_dscr_seq;
    $result = $mtraDAO->selectData($conn, $param);

    //html data 셋팅
    $param = array();
    $param["top"]         = $result->fields["top"];
    $param["name"]        = $result->fields["name"];
    $param["dvs"]         = $result->fields["dvs"];
    $param["color"]       = $result->fields["color"];
    $param["basisweight"] = $result->fields["basisweight"];
    $param["dscr"]        = $result->fields["dscr"];
    $param["purp"]        = $result->fields["purp"];
    $param["sense"]       = $result->fields["paper_sense"];
    $param["able_after"]  = $result->fields["able_after"];
    $param["warn"]        = $result->fields["warn"];
    $param["paper_dscr_seqno"] = $paper_dscr_seq;
}

$html = getPaperDscrHtml($param);

echo $html;
$conn->close();
?>
