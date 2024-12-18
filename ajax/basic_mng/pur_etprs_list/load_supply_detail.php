<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/basic_mng/pur_etprs_mng/PurEtprsListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$purDAO = new PurEtprsListDAO();

$param = array();
//매입업체 일련번호
$param["seqno"] = $fb->form("extnl_etprs_seqno");
$param["brand_seqno"] = $fb->form("brand_seqno");
$pur_prdt = $fb->form("prdt");

//종이 품목일때
if ($pur_prdt == "종이") {

    $param["paper_seqno"] = $fb->form("seqno");
    $result = $purDAO->selectPurPaperList($conn, $param, "2"); 
    $paper_param = makePaperParam($result);
    $prdt_list = getPaperView($paper_param);

//출력 품목일때
} else if ($pur_prdt == "출력") {

    $param["output_seqno"] = $fb->form("seqno");
    $result = $purDAO->selectPurOutputList($conn, $param, "2"); 
    $output_param = makeOutputParam($result);
    $prdt_list = getOutputView($output_param);

//인쇄 품목일때
} else if ($pur_prdt == "인쇄") {

    $param["print_seqno"] = $fb->form("seqno");
    $result = $purDAO->selectPurPrintList($conn, $param, "2"); 
    $print_param = makePrintParam($result);
    $prdt_list = getPrintView($print_param);

//후공정 품목일때
} else if ($pur_prdt == "후공정") {

    $param["after_seqno"] = $fb->form("seqno");
    $param["table"] = "after";
    $result = $purDAO->selectPurAfterOptList($conn, $param, "2"); 
    $after_param = makeAfterOptParam($result);
    $prdt_list = getAfterView($after_param);

}

echo $prdt_list;

$conn->close();
?>
