<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_mng/PrdtItemRegiDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$prdtItemRegiDAO = new PrdtItemRegiDAO();

$select_el = $fb->form("selectEl");
$sort = $fb->form("sort");

//종이
if ($select_el == "paper") {

    $base = "";
    $flag = "Y";
    $param = array();

    $paper_sort_rs = $prdtItemRegiDAO->selectPaperInfo($conn, "SORT", $param);
    $paprt_sort_html = makeOptionHtml($paper_sort_rs, "", "sort", "종이대분류(전체)");

    $param["sort"] = $sort;

    $paper_name_rs = $prdtItemRegiDAO->selectPaperInfo($conn, "NAME", $param);
    $paprt_name_html = makeOptionHtml($paper_name_rs, "", "name", $base="종이명(전체)", $flag);

    echo "true♪" . $paprt_sort_html . "♪" . $paprt_name_html;

//구성아이템 종이
} else if ($select_el == "cate_paper") {

    $base = "";
    $flag = "Y";
    $param = array();
    $param["cate_sortcode"] = $fb->form("cate_sortcode");

    $paper_sort_rs = $prdtItemRegiDAO->selectCatePaperInfo($conn, "SORT", $param);
    $paprt_sort_html = makeOptionHtml($paper_sort_rs, "", "sort", "종이대분류(전체)");

    $param["sort"] = $sort;

    $paper_name_rs = $prdtItemRegiDAO->selectCatePaperInfo($conn, "NAME", $param);
    $paprt_name_html = makeOptionHtml($paper_name_rs, "", "name", $base="종이명(전체)", $flag);

    echo "true♪" . $paprt_sort_html . "♪" . $paprt_name_html;

//사이즈
} else if ($select_el == "size") {
 
    $base = "";
    $flag = "Y";
    $param = array();

    $size_sort_rs = $prdtItemRegiDAO->selectSizeInfo($conn, "SORT", $param);
    $size_sort_html = makeOptionHtml($size_sort_rs, "", "sort", "사이즈대분류(전체)");

    $param["sort"] = $sort;

    $size_name_rs = $prdtItemRegiDAO->selectSizeInfo($conn, "NAME", $param);
    $size_name_html = makeOptionHtml($size_name_rs, "", "name", $base="사이즈명(전체)", $flag);

    echo "true♪" . $size_sort_html . "♪" . $size_name_html;

//구성아이템 사이즈
} else if ($select_el == "cate_size") {
 
    $base = "";
    $flag = "Y";
    $param = array();
    $param["cate_sortcode"] = $fb->form("cate_sortcode");

    $size_sort_rs = $prdtItemRegiDAO->selectCateSizeInfo($conn, "SORT", $param);
    $size_sort_html = makeOptionHtml($size_sort_rs, "", "sort", "사이즈대분류(전체)");

    $param["sort"] = $sort;

    $size_name_rs = $prdtItemRegiDAO->selectCateSizeInfo($conn, "NAME", $param);
    $size_name_html = makeOptionHtml($size_name_rs, "", "name", $base="사이즈명(전체)", $flag);

    echo "true♪" . $size_sort_html . "♪" . $size_name_html;

//인쇄도수
} else if ($select_el == "tmpt") {
 
    $base = "";
    $flag = "Y";
    $param = array();

    $tmpt_sort_rs = $prdtItemRegiDAO->selectTmptInfo($conn, "SORT", $param);
    $tmpt_sort_html = makeOptionHtml($tmpt_sort_rs, "", "sort", "인쇄도수대분류(전체)");

    $param["sort"] = $sort;

    $tmpt_name_rs = $prdtItemRegiDAO->selectTmptInfo($conn, "NAME", $param);
    $tmpt_name_html = makeOptionHtml($tmpt_name_rs, "", "name", $base="인쇄도수명(전체)", $flag);

    echo "true♪" . $tmpt_sort_html . "♪" . $tmpt_name_html;

//구성아이템 인쇄도수
} else if ($select_el == "cate_tmpt") {
 
    $base = "";
    $flag = "Y";
    $param = array();
    $param["cate_sortcode"] = $fb->form("cate_sortcode");
    
    $tmpt_sort_rs = $prdtItemRegiDAO->selectCateTmptInfo($conn, "SORT", $param);
    $tmpt_sort_html = makeOptionHtml($tmpt_sort_rs, "", "sort", "인쇄도수대분류(전체)");

    $param["sort"] = $sort;

    $tmpt_name_rs = $prdtItemRegiDAO->selectCateTmptInfo($conn, "NAME", $param);
    $tmpt_name_html = makeOptionHtml($tmpt_name_rs, "", "name", $base="인쇄도수명(전체)", $flag);

    echo "true♪" . $tmpt_sort_html . "♪" . $tmpt_name_html;

//후공정
} else if ($select_el == "after") {

    $base = "";
    $flag = "Y";
    $param = array();

    $after_name_rs = $prdtItemRegiDAO->selectAfterInfo($conn, "AFTER_NAME", $param);
    $after_name_html = makeOptionHtml($after_name_rs, "", "after_name", $base="후공정명(전체)", $flag);

    echo "true♪♪" . $after_name_html;

//구성아이템 후공정
} else if ($select_el == "cate_after") {

    $base = "";
    $flag = "Y";
    $param = array();
    $param["cate_sortcode"] = $fb->form("cate_sortcode");
 
    $after_sort_html  = "<option value=\"\">후공정 분류(전체)</option>";   
    $after_sort_html .= "<option value=\"Y\">기본 후공정</option>";   
    $after_sort_html .= "<option value=\"N\">추가 후공정</option>";   

    $param["basic_yn"] = $sort;

    $after_name_rs = $prdtItemRegiDAO->selectCateAftInfo($conn, "AFTER_NAME", $param);
    $after_name_html = makeOptionHtml($after_name_rs, "", "after_name", $base="후공정명(전체)", $flag);

    echo "true♪" . $after_sort_html . "♪" . $after_name_html;

//옵션
} else if ($select_el == "opt") {
 
    $base = "";
    $flag = "Y";
    $param = array();
    $opt_name_rs = $prdtItemRegiDAO->selectOptInfo($conn, "OPT_NAME", $param);
    $opt_name_html= makeOptionHtml($opt_name_rs, "", "opt_name", $base="옵션명(전체)", $flag);

    echo "true♪♪" . $opt_name_html;

//구성아이템 옵션
} else if ($select_el == "cate_opt") {
 
    $base = "";
    $flag = "Y";
    $param = array();
    $param["cate_sortcode"] = $fb->form("cate_sortcode");

    $opt_sort_html  = "<option value=\"\">옵션 분류(전체)</option>";   
    $opt_sort_html .= "<option value=\"Y\">기본 옵션</option>";   
    $opt_sort_html .= "<option value=\"N\">추가 옵션</option>";   

    $param["basic_yn"] = $sort;

    $opt_name_rs = $prdtItemRegiDAO->selectCateOptInfo($conn, "OPT_NAME", $param);
    $opt_name_html = makeOptionHtml($opt_name_rs, "", "opt_name", $base="옵션명(전체)", $flag);

    echo "true♪" . $opt_sort_html . "♪" . $opt_name_html;

//실사후공정
} else if ($select_el == "ao_after") {
    $conn->debug = 1;

    $base = "";
    $flag = "Y";
    $param = array();

    $after_name_rs = $prdtItemRegiDAO->selectAoAfterInfo($conn, "AFTER_NAME", $param);
    $after_name_html = makeOptionHtml($after_name_rs, "", "after_name", $base="후공정명(전체)", $flag);

    echo "true♪♪" . $after_name_html;

//구성아이템 후공정
} else if ($select_el == "cate_ao_after") {

    $base = "";
    $flag = "Y";
    $param = array();
    $param["cate_sortcode"] = $fb->form("cate_sortcode");
 
    $after_sort_html  = "<option value=\"\">후공정 분류(전체)</option>";   
    $after_sort_html .= "<option value=\"Y\">기본 후공정</option>";   
    $after_sort_html .= "<option value=\"N\">추가 후공정</option>";   

    $param["basic_yn"] = $sort;

    $after_name_rs = $prdtItemRegiDAO->selectCateAoAftInfo($conn, "AFTER_NAME", $param);
    $after_name_html = makeOptionHtml($after_name_rs, "", "after_name", $base="후공정명(전체)", $flag);

    echo "true♪" . $after_sort_html . "♪" . $after_name_html;

//옵션
} else if ($select_el == "ao_opt") {
 
    $base = "";
    $flag = "Y";
    $param = array();
    $opt_name_rs = $prdtItemRegiDAO->selectAoOptInfo($conn, "OPT_NAME", $param);
    $opt_name_html= makeOptionHtml($opt_name_rs, "", "opt_name", $base="옵션명(전체)", $flag);

    echo "true♪♪" . $opt_name_html;

//구성아이템 옵션
} else if ($select_el == "cate_ao_opt") {
 
    $base = "";
    $flag = "Y";
    $param = array();
    $param["cate_sortcode"] = $fb->form("cate_sortcode");

    $opt_sort_html  = "<option value=\"\">옵션 분류(전체)</option>";   
    $opt_sort_html .= "<option value=\"Y\">기본 옵션</option>";   
    $opt_sort_html .= "<option value=\"N\">추가 옵션</option>";   

    $param["basic_yn"] = $sort;

    $opt_name_rs = $prdtItemRegiDAO->selectCateAoOptInfo($conn, "OPT_NAME", $param);
    $opt_name_html = makeOptionHtml($opt_name_rs, "", "opt_name", $base="옵션명(전체)", $flag);

    echo "true♪" . $opt_sort_html . "♪" . $opt_name_html;
}

$conn->close();
?>
