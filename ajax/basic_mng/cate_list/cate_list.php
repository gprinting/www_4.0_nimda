<?
/*
 * Copyright (c) 2015-2016 Nexmotion, Inc. All rights reserved. 
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2016/10/11 엄준현 회원할인 관련로직 추가
 *=============================================================================
 *
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/basic_mng/cate_mng/CateListDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$cateListDAO = new CateListDAO();

//한페이지에 출력할 게시물 갯수
$list_num = $fb->form("showPage"); 

//현재 페이지
$page = $fb->form("page");

//선택된 탭
$select_el = $fb->form("selectEl");

//검색할 단어
$search_txt = $fb->form("searchTxt");

//검색할 대분류
$select_sort = $fb->form("select_sort");

//검색할 이름
$select_name = $fb->form("select_name");

//정렬
$sorting = $fb->form("sorting");
$sorting_type = $fb->form("sorting_type");

//리스트 보여주는 갯수 설정
if (!$fb->form("showPage")) {
    $list_num = 30;
}

//블록 갯수
$scrnum = 5; 

// 페이지가 없으면 1 페이지
if (!$page) {
    $page = 1; 
}

$s_num = $list_num * ($page-1);
 
//검색조건
$param = array();
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
$param["search_txt"] = $search_txt;
$param["sort"] = $select_sort;
$param["sorting"] = $sorting;
$param["sorting_type"] = $sorting_type;
$param["cate_sortcode_like"] = $fb->form("cate_sortcode");

//구성아이템 종이 일경우
if ($select_el == "paper") {
 
    $paper_rs = $cateListDAO->selectCatePaperInfo($conn, "SEQ", $param);
    $list = makeCatePaperListHtml($paper_rs, $param);
  
    $count_rs = $cateListDAO->selectCatePaperInfo($conn, "COUNT", $param);
    $rsCount = $count_rs->fields["cnt"];

    $paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "movePage", "paper");
   
    echo $list . "♪" . $paging;

//구성아이템 사이즈 일경우
} else if ($select_el == "size") {
  
    $size_rs = $cateListDAO->selectCateSizeInfo($conn, "SEQ", $param);
    $list = makeCateSizeListHtml($size_rs, $param);
  
    $count_rs = $cateListDAO->selectCateSizeInfo($conn, "COUNT", $param);
    $rsCount = $count_rs->fields["cnt"];

    $paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "movePage", "size");
   
    echo $list . "♪" . $paging;

//구성아이템 인쇄도수 일경우
} else if ($select_el == "print") {
  
    $print_rs = $cateListDAO->selectCateTmptInfo($conn, "SEQ", $param);
    $list = makeCateTmptListHtml($print_rs, $param);
  
    $count_rs = $cateListDAO->selectCateTmptInfo($conn, "COUNT", $param);
    $rsCount = $count_rs->fields["cnt"];

    $paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "movePage", "print");
   
    echo $list . "♪" . $paging;

//구성아이템 후공정 일경우
} else if ($select_el == "after") {
  
    $param["after_name"] = $select_name;
    $param["basic_yn"] = $select_sort;

    $after_rs = $cateListDAO->selectCateAftInfo($conn, "SEQ", $param);
    $list = makeCateAfterListHtml($after_rs, $param);
  
    $count_rs = $cateListDAO->selectCateAftInfo($conn, "COUNT", $param);
    $rsCount = $count_rs->fields["cnt"];

    $paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "movePage", "after");
   
    echo $list . "♪" . $paging;

//구성아이템 옵션 일경우
} else if ($select_el == "opt") {
  
    $param["opt_name"] = $select_name;
    $param["basic_yn"] = $select_sort;

    $opt_rs = $cateListDAO->selectCateOptInfo($conn, "SEQ", $param);
    $list = makeCateOptListHtml($opt_rs, $param);
  
    $count_rs = $cateListDAO->selectCateOptInfo($conn, "COUNT", $param);
    $rsCount = $count_rs->fields["cnt"];

    $paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "movePage", "opt");
   
    echo $list . "♪" . $paging;

//등급할인
} else if ($select_el == "grade") {

    $param = array();
    $param["cate_sortcode"] = $fb->form("cate_sortcode");

    $grade_info_rs = $cateListDAO->selectCateGradeInfo($conn, $param);

    $list = makeGradeListHtml($grade_info_rs);

    echo $list;

// 회원할인
} else if ($select_el == "member") {
    $param = array();
    $param["cate_sortcode"] = $fb->form("cate_sortcode");

    $rs = $cateListDAO->selectCateMemberSale($conn, $param);

    $list = makeMemberSaleListHtml($rs);

    echo $list;
} 

$conn->close();
?>
