<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_mng/PrdtBasicRegiDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$prdtBasicRegiDAO = new PrdtBasicRegiDAO();

$select_el = $fb->form("selectEl");
$sort = $fb->form("sort");

//종이정보
if ($select_el == "paper") {

    $base = "";
    $flag = "Y";
    $param = array();
    $paper_sort_rs = $prdtBasicRegiDAO->selectPaperInfo($conn, "SORT", $param);
    $paprt_sort_html= makeOptionHtml($paper_sort_rs, "", "sort", "종이대분류(전체)");

    $param["sort"] = $sort;

    $paper_name_rs = $prdtBasicRegiDAO->selectPaperInfo($conn, "NAME", $param);
    $paprt_name_html= makeOptionHtml($paper_name_rs, "", "name", $base="종이명(전체)", $flag);

    echo "true♪" . $paprt_sort_html . "♪" . $paprt_name_html;

//출력정보
} else if ($select_el == "output") {
 
    $base = "";
    $flag = "Y";
    $param = array();
    $output_name_rs = $prdtBasicRegiDAO->selectOutputInfo($conn, "NAME", $param);
    $output_name_html= makeOptionHtml($output_name_rs, "", "output_name", $base="출력명(전체)", $flag);

    echo "true♪♪" . $output_name_html;

//사이즈
} else if ($select_el == "size") {
 
    $base = "";
    $flag = "Y";
    $param = array();
    $size_sort_rs = $prdtBasicRegiDAO->selectSizeInfo($conn, "SORT", $param);
    $size_sort_html= makeOptionHtml($size_sort_rs, "", "sort", "사이즈대분류(전체)");

    $param["sort"] = $sort;

    $size_name_rs = $prdtBasicRegiDAO->selectSizeInfo($conn, "NAME", $param);
    $size_name_html= makeOptionHtml($size_name_rs, "", "name", $base="사이즈명(전체)", $flag);

    echo "true♪" . $size_sort_html . "♪" . $size_name_html;

//인쇄정보
} else if ($select_el == "print") {
 
    $base = "";
    $flag = "Y";
    $param = array();
    $print_name_rs = $prdtBasicRegiDAO->selectPrintInfo($conn, "NAME", $param);
    $print_name_html= makeOptionHtml($print_name_rs, "", "print_name", $base="인쇄명(전체)", $flag);

    echo "true♪♪" . $print_name_html;

//인쇄도수
} else if ($select_el == "tmpt") {
 
    $base = "";
    $flag = "Y";
    $param = array();
    $tmpt_sort_rs = $prdtBasicRegiDAO->selectTmptInfo($conn, "SORT", $param);
    $tmpt_sort_html= makeOptionHtml($tmpt_sort_rs, "", "sort", "인쇄도수대분류(전체)");

    $param["sort"] = $sort;

    $tmpt_name_rs = $prdtBasicRegiDAO->selectTmptInfo($conn, "NAME", $param);
    $tmpt_name_html= makeOptionHtml($tmpt_name_rs, "", "name", $base="인쇄도수명(전체)", $flag);

    echo "true♪" . $tmpt_sort_html . "♪" . $tmpt_name_html;


//후공정
} else if ($select_el == "after") {

    $base = "";
    $flag = "Y";
    $param = array();
    $after_name_rs = $prdtBasicRegiDAO->selectAfterInfo($conn, "AFTER_NAME", $param);
    $after_name_html= makeOptionHtml($after_name_rs, "", "after_name", $base="후공정명(전체)", $flag);

    echo "true♪♪" . $after_name_html;

//옵션
} else if ($select_el == "opt") {
 
    $base = "";
    $flag = "Y";
    $param = array();
    $opt_name_rs = $prdtBasicRegiDAO->selectOptInfo($conn, "OPT_NAME", $param);
    $opt_name_html= makeOptionHtml($opt_name_rs, "", "opt_name", $base="옵션명(전체)", $flag);

    echo "true♪♪" . $opt_name_html;
} else if ($select_el == "ao_after") {

    $base = "";
    $flag = "Y";
    $param = array();
    $after_name_rs = $prdtBasicRegiDAO->selectAoAfterInfo($conn, "AFTER_NAME", $param);
    $after_name_html= makeOptionHtml($after_name_rs, "", "after_name", $base="후공정명(전체)", $flag);

    echo "true♪♪" . $after_name_html;

//옵션
} else if ($select_el == "ao_opt") {
 
    $base = "";
    $flag = "Y";
    $param = array();
    $opt_name_rs = $prdtBasicRegiDAO->selectAoOptInfo($conn, "OPT_NAME", $param);
    $opt_name_html= makeOptionHtml($opt_name_rs, "", "opt_name", $base="옵션명(전체)", $flag);

    echo "true♪♪" . $opt_name_html;
}

$conn->close();
?>
