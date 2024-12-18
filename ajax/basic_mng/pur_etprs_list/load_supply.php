<?
ini_set('display_errors', 1);
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/basic_mng/pur_etprs_mng/PurEtprsListDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/pageLib.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$purDAO = new PurEtprsListDAO();

$param = array();
$pur_prdt = $fb->form("pur_prdt");

//페이징
$list_num = $fb->form("list_num"); //한페이지에 출력할 게시물 개수
if (!$fb->form("list_num")) $list_num = 30;

$scrnum = 5; //블록 개수
$page = $fb->form("page");

if (!$page) $page = 1; // 페이지가 없으면 1 페이지

//매입업체 일련번호
$param["seqno"] = $fb->form("extnl_etprs_seqno");
$param["brand"] = $fb->form("brand_seqno");
$param["sort"] = $fb->form("sort");
$param["sort_type"] = $fb->form("sort_type");

$s_param = array();
$s_param["brand_sort"] = "fa-sort";
$s_param["name_sort"] = "fa-sort";
$s_param["brand_sort_type"] = "";
$s_param["name_sort_type"] = "";

//sort가 브랜드일때
if ($param["sort"] == "brand") {

    $s_param["brand_sort"] = "";

    //내림차순 정렬일때
    if($param["sort_type"] == "DESC") {

        $s_param["brand_sort_type"] = "fa-sort-desc";

    } else {

        $s_param["brand_sort_type"] = "fa-sort-asc";

    }

    $s_param["name_sort"] = "fa-sort";

//sort가 이름일때
} else if ($param["sort"] == "name") {
    
    $s_param["name_sort"] = "";

    //오름차순 정렬일때
    if($param["sort_type"] == "DESC") {

        $s_param["name_sort_type"] = "fa-sort-desc";

    } else {

        $s_param["name_sort_type"] = "fa-sort-asc";

    }
    
    $s_param["brand_sort"] = "fa-sort";
}

$prdt_list = "";

$query_func = "";
$tbody_func = "";
$thead_func = "";

if ($pur_prdt == "종이") {

    $query_func = "selectPurPaperList";
    $tbody_func = "makePaperTbody";
    $thead_func = "makePaperThead";

} else if ($pur_prdt == "출력") {

    $query_func = "selectPurOutputList";
    $tbody_func = "makeOutputTbody";
    $thead_func = "makeOutputThead";

} else if ($pur_prdt == "인쇄") {

    $query_func = "selectPurPrintList";
    $tbody_func = "makePrintTbody";
    $thead_func = "makePrintThead";

} else if ($pur_prdt == "후공정") {

    $param["table"] = "after";
    $query_func = "selectPurAfterOptList";
    $tbody_func = "makeAfterOptTbody";
    $thead_func = "makeAfterThead";

}

//페이징
$param["start"] = $list_num * ($page-1);
$param["end"] = $list_num;

$p_result = $purDAO->$query_func($conn, $param,"1"); 

$param["start"] = "";
$param["end"] = "";
$count_rs = $purDAO->$query_func($conn, $param, "1");

$total_count = $count_rs->recordCount(); //페이징할 총 글수

$ret = "";
$ret = mkDotAjaxPage($total_count, $page, $scrnum, $list_num, "searchResult");

$p_param["prdt_tbody"] = $tbody_func($p_result, $list_num * ($page-1));
$p_param["prdt_thead"] = $thead_func($s_param);
$p_param["list_num"] = $list_num;
$prdt_list = getPurPrdtList($p_param);

echo $prdt_list . "♪" . $ret;
$conn->close();
?>
