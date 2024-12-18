<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/esti_mng/EstiListDAO.inc");
include_once(INC_PATH . "/com/nexmotion/doc/nimda/business/esti_mng/EstiRegiInfo.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new EstiListDAO();
$commonDAO = $dao;

$dvs = $fb->form("dvs");
$idx = $fb->form("idx");

if ($dvs == "paper") {

    $param = array();
    $param["table"] = "prdt_paper";
    $param["col"] = "DISTINCT name";

    $paper_rs = $dao->selectData($conn, $param);
    $paper_option_html = makeOptionHtml($paper_rs, "name", "name", "종이명(전체)");

    $param = array();
    $param["paper_option_html"] = $paper_option_html;
    $param["idx"] = $idx;

    echo makePaperLowHtml($param);

} else if ($dvs == "output") {

    $param = array();
    $param["table"] = "prdt_output_info";
    $param["col"] = "DISTINCT output_name";

    $output_rs = $dao->selectData($conn, $param);
    $output_option_html = makeOptionHtml($output_rs, "output_name", "output_name", "출력명(전체)");

    $param = array();
    $param["output_option_html"] = $output_option_html;
    $param["idx"] = $idx;

    echo makeOutputLowHtml($param);

} else if ($dvs == "print") {
 
    $print_rs = $dao->selectCate($conn);
    $print_option_html = makeOptionHtml($print_rs, "cate_sortcode", "cate_name", "카테고리중분류(전체)");

    $param = array();
    $param["print_option_html"] = $print_option_html;
    $param["idx"] = $idx;

    echo makePrintLowHtml($param);

} else if ($dvs == "after") {

    $param = array();
    $param["table"] = "prdt_after";
    $param["col"] = "DISTINCT after_name";

    $after_rs = $dao->selectData($conn, $param);
    $after_option_html = makeOptionHtml($after_rs, "after_name", "after_name", "후공정명(전체)");

    $param = array();
    $param["after_option_html"] = $after_option_html;
    $param["idx"] = $idx;

    echo makeAfterLowHtml($param);

} else if ($dvs == "etc") {
 
    $param = array();
    $param["idx"] = $idx;

    echo makeEtcLowHtml($param);
}

$conn->Close();
?>
