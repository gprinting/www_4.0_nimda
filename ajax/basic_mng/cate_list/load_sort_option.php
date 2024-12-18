<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/basic_mng/cate_mng/CateListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$cateListDAO = new CateListDAO();

$select_el = $fb->form("selectEl");
$sort = $fb->form("sort");

//구성아이템 종이
if ($select_el == "paper") {

    $base = "";
    $flag = "Y";
    $param = array();
    $param["cate_sortcode_like"] = $fb->form("cate_sortcode");

    $paper_sort_rs = $cateListDAO->selectCatePaperInfo($conn, "SORT", $param);
    $paprt_sort_html = makeOptionHtml($paper_sort_rs, "", "sort", "종이대분류(전체)");

    echo $paprt_sort_html;

//구성아이템 사이즈
} else if ($select_el == "size") {
 
    $base = "";
    $flag = "Y";
    $param = array();
    $param["cate_sortcode_like"] = $fb->form("cate_sortcode");

    $size_sort_rs = $cateListDAO->selectCateSizeInfo($conn, "SORT", $param);
    $size_sort_html = makeOptionHtml($size_sort_rs, "", "sort", "사이즈대분류(전체)");

    echo $size_sort_html;

//구성아이템 인쇄도수
} else if ($select_el == "print") {
 
    $base = "";
    $flag = "Y";
    $param = array();
    $param["cate_sortcode_like"] = $fb->form("cate_sortcode");

    $print_sort_rs = $cateListDAO->selectCateTmptInfo($conn, "SORT", $param);
    $print_sort_html = makeOptionHtml($print_sort_rs, "", "sort", "인쇄도수대분류(전체)");

    echo $print_sort_html;

//구성아이템 후공정
} else if ($select_el == "after") {

    $after_sort_html  = "<option value=\"\">후공정 분류(전체)</option>";   
    $after_sort_html .= "<option value=\"Y\">기본 후공정</option>";   
    $after_sort_html .= "<option value=\"N\">추가 후공정</option>";   

    echo $after_sort_html;

//구성아이템 옵션
} else if ($select_el == "opt") {
 
    $opt_sort_html  = "<option value=\"\">옵션 분류(전체)</option>";   
    $opt_sort_html .= "<option value=\"Y\">기본 옵션</option>";   
    $opt_sort_html .= "<option value=\"N\">추가 옵션</option>";   

    echo $opt_sort_html;
}

$conn->close();
?>
