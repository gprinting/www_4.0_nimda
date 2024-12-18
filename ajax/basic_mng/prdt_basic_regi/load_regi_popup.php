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
$seqno = $fb->form("seqno");
$flag = true;
$select_box_val = "";
$val = array();

//일련번호가 없을 경우
if ($seqno == "") {
    $flag = false;
} else {
    //종이
    if ($select_el == "paper") {

        $param = array();
        $param["seqno"] = $seqno;
        $param["s_num"] = 0;
        $param["list_num"] = 1;

        $rs = $prdtBasicRegiDAO->selectPaperInfo($conn, "SEQ", $param);

        $size = explode("*", $rs->fields["size"]);

        $val["name"] = $rs->fields["name"];
        $val["dvs"] = $rs->fields["dvs"];
        $val["color"] = $rs->fields["color"];
        $val["basisweight"] = $rs->fields["basisweight"];
        $val["wid_size"] = $size[0];
        $val["vert_size"] = $size[1];

        $select_box_val = $rs->fields["sort"] . "♪" . $rs->fields["basisweight_unit"] . "♪" . $rs->fields["affil"] . "♪" . $rs->fields["crtr_unit"];

    //출력
    } else if ($select_el == "output") {

        $param = array();
        $param["seqno"] = $seqno;
        $param["s_num"] = 0;
        $param["list_num"] = 1;

        $rs = $prdtBasicRegiDAO->selectOutputInfo($conn, "SEQ", $param);

        $val["output_name"] = $rs->fields["output_name"];

        $select_box_val = $rs->fields["output_board_dvs"] . "♪" . $rs->fields["affil"];
    
    //사이즈
    } else if ($select_el == "size") {
        
        $param = array();
        $param["seqno"] = $seqno;
        $param["s_num"] = 0;
        $param["list_num"] = 1;

        $rs = $prdtBasicRegiDAO->selectSizeInfo($conn, "SEQ", $param);

        $val["name"] = $rs->fields["name"];
        $val["typ"] = $rs->fields["typ"];
        $val["cut_wid_size"] = $rs->fields["cut_wid_size"];
        $val["cut_vert_size"] = $rs->fields["cut_vert_size"];
        $val["work_wid_size"] = $rs->fields["work_wid_size"];
        $val["work_vert_size"] = $rs->fields["work_vert_size"];
        $val["design_wid_size"] = $rs->fields["design_wid_size"];
        $val["design_vert_size"] = $rs->fields["design_vert_size"];
        $val["tomson_wid_size"] = $rs->fields["tomson_wid_size"];
        $val["tomson_vert_size"] = $rs->fields["tomson_vert_size"];

        $output_name = $rs->fields["output_name"];
        $select_box_val = $rs->fields["sort"] . "♪" . $rs->fields["output_name"] . "♪" . $rs->fields["output_board_dvs"] . "♪" . $rs->fields["affil"];

    //인쇄정보
    } else if ($select_el == "print") {
 
        $param = array();
        $param["seqno"] = $seqno;
        $param["s_num"] = 0;
        $param["list_num"] = 1;

        $rs = $prdtBasicRegiDAO->selectPrintInfo($conn, "SEQ", $param);

        $val["print_name"] = $rs->fields["print_name"];
        $val["purp_dvs"] = $rs->fields["purp_dvs"];

        $cate_sortcode = $rs->fields["cate_sortcode"];

        if (strlen($cate_sortcode) == 3) {
            $cate_top = $cate_sortcode;
            $cate_mid = "";
        } else {
            $cate_mid = $cate_sortcode;
            $cate_top = substr($cate_sortcode, 0, 3);
        }
        
        $select_box_val = $cate_top . "♪" . $cate_mid . "♪" . $rs->fields["affil"] . "♪" . $rs->fields["crtr_unit"];
 
    //인쇄도수
    } else if ($select_el == "tmpt") {
 
        $param = array();
        $param["seqno"] = $seqno;
        $param["s_num"] = 0;
        $param["list_num"] = 1;

        $rs = $prdtBasicRegiDAO->selectTmptInfo($conn, "SEQ", $param);

        $val["name"] = $rs->fields["name"];
        $val["beforeside_tmpt"] = $rs->fields["beforeside_tmpt"];
        $val["aftside_tmpt"] = $rs->fields["aftside_tmpt"];
        $val["add_tmpt"] = $rs->fields["add_tmpt"];
        $val["tot_tmpt"] = $rs->fields["tot_tmpt"];
        $val["output_board_amt"] = $rs->fields["output_board_amt"];

        $print_name = $rs->fields["print_name"];
        $select_box_val = $rs->fields["sort"] . "♪" . $rs->fields["print_name"] . "♪" . $rs->fields["purp_dvs"] . "♪" . $rs->fields["side_dvs"];
 
    //후공정
    } else if ($select_el == "after") {
 
        $param = array();
        $param["seqno"] = $seqno;
        $param["s_num"] = 0;
        $param["list_num"] = 1;

        $rs = $prdtBasicRegiDAO->selectAfterInfo($conn, "SEQ", $param);

        $val["depth1"] = $rs->fields["depth1"];
        $val["depth2"] = $rs->fields["depth2"];
        $val["depth3"] = $rs->fields["depth3"];

        $select_box_val = $rs->fields["after_name"] . "♪" . $rs->fields["crtr_unit"];
 
    //옵션
    } else if ($select_el == "opt") {
 
        $param = array();
        $param["seqno"] = $seqno;
        $param["s_num"] = 0;
        $param["list_num"] = 1;

        $rs = $prdtBasicRegiDAO->selectOptInfo($conn, "SEQ", $param);
            
        $val["depth1"] = $rs->fields["depth1"];
        $val["depth2"] = $rs->fields["depth2"];
        $val["depth3"] = $rs->fields["depth3"];

        $select_box_val = $rs->fields["opt_name"];

    //실사 후공정
    } else if ($select_el == "ao_after") {
 
        $param = array();
        $param["seqno"] = $seqno;
        $param["s_num"] = 0;
        $param["list_num"] = 1;

        $rs = $prdtBasicRegiDAO->selectAoAfterInfo($conn, "SEQ", $param);

        $val["after_name"] = $rs->fields["after_name"];
        $val["depth1"] = $rs->fields["depth1"];
        $val["depth2"] = $rs->fields["depth2"];
        $val["depth3"] = $rs->fields["depth3"];
        $val["unitprice"] = $rs->fields["unitprice"];

        $select_box_val = $rs->fields["after_name"] . "♪" . $rs->fields["crtr_unit"];
 
    //실사 옵션
    } else if ($select_el == "ao_opt") {
 
        $param = array();
        $param["seqno"] = $seqno;
        $param["s_num"] = 0;
        $param["list_num"] = 1;

        $rs = $prdtBasicRegiDAO->selectAoOptInfo($conn, "SEQ", $param);
 
        $val["opt_name"] = $rs->fields["opt_name"];
        $val["depth1"] = $rs->fields["depth1"];
        $val["depth2"] = $rs->fields["depth2"];
        $val["depth3"] = $rs->fields["depth3"];
        $val["unitprice"] = $rs->fields["unitprice"];

        $select_box_val = $rs->fields["opt_name"];
    }
}

//종이 대분류
$param = array();
$param["prdt_dvs"] = "1";
$paper_sort_rs = $prdtBasicRegiDAO->selectPrdtSort($conn, $param);
$paper_sort_html = makeOptionHtml($paper_sort_rs, "", "sort", "", "N");

//사이즈 대분류
$param = array();
$param["prdt_dvs"] = "2";

$size_sort_rs = $prdtBasicRegiDAO->selectPrdtSort($conn, $param);
$size_sort_html = makeOptionHtml($size_sort_rs, "", "sort", "", "N");

//사이즈 출력명
$output_name_rs = $prdtBasicRegiDAO->selectOutputInfo($conn, "NAME", $param);
$output_name_html = makeOptionHtml($output_name_rs, "", "output_name", "", "N");

//사이즈 출력판구분 전체
$output_board_dvs_rs = $prdtBasicRegiDAO->selectOutputInfo($conn, "BOARD", array());
$output_board_dvs_all_html = makeOptionHtml($output_board_dvs_rs,
                                            "",
                                            "output_board_dvs",
                                            "",
                                            "N");

$output_name_rs->moveFirst();
$param["name"] = $output_name_rs->fields["output_name"];

if ($output_name) {
    $param["name"] = $output_name;
}

//사이즈 출력판구분
$output_board_dvs_rs = $prdtBasicRegiDAO->selectOutputInfo($conn, "BOARD", $param);
$output_board_dvs_html = makeOptionHtml($output_board_dvs_rs, "", "output_board_dvs", "", "N");

//인쇄 카테고리 중분류
$param = array();
$param["table"] = "cate";
$param["col"] = "cate_name, sortcode";
$param["where"]["cate_level"] = "1";
$print_cate_top_rs =  $prdtBasicRegiDAO->selectData($conn, $param);
$print_cate_top_html = makeOptionHtml($print_cate_top_rs, "sortcode", "cate_name", "대분류(전체)", "Y");

$param["where"]["high_sortcode"] = $cate_top;
$param["where"]["cate_level"] = "2";
$print_cate_mid_rs =  $prdtBasicRegiDAO->selectData($conn, $param);
$print_cate_mid_html = makeOptionHtml($print_cate_mid_rs, "sortcode", "cate_name", "중분류(전체)", "Y");

//인쇄도수 대분류
$param = array();
$param["prdt_dvs"] = "3";
$tmpt_sort_rs = $prdtBasicRegiDAO->selectPrdtSort($conn, $param);
$tmpt_sort_html = makeOptionHtml($tmpt_sort_rs, "", "sort", "", "N");

//인쇄도수 인쇄명
$tmpt_print_name_rs = $prdtBasicRegiDAO->selectPrintInfo($conn, "NAME", $param);
$tmpt_print_name_html = makeOptionHtml($tmpt_print_name_rs, "", "print_name", "", "N");

$tmpt_print_name_rs->moveFirst();
$param["name"] = $tmpt_print_name_rs->fields["print_name"];

if ($print_name) {
    $param["name"] = $print_name;
}
//인쇄도수 인쇄 용도구분
$tmpt_purp_dvs_rs = $prdtBasicRegiDAO->selectPrintInfo($conn, "PURP", $param);
$tmpt_purp_dvs_html = makeOptionHtml($tmpt_purp_dvs_rs, "", "purp_dvs", "", "N");

$param = array();
$param["table"] = "after_dscr";
$param["col"] = "DISTINCT name";

//후공정명
$after_name_rs = $prdtBasicRegiDAO->selectData($conn, $param); 
$after_name_html = makeOptionHtml($after_name_rs, "", "name", "", "N");

$param["table"] = "opt_dscr";

//옵션명
$opt_name_rs = $prdtBasicRegiDAO->selectData($conn, $param);
$opt_name_html = makeOptionHtml($opt_name_rs, "", "name", "", "N");

$param = array();
$param["paper"] = $paper_sort_html . "♪" . $flag . "♪" . $seqno;
$param["output"] = $flag . "♪" . $seqno . "♪" . $output_board_dvs_all_html;
$param["size"] = $size_sort_html . "♪" . $output_name_html . "♪" . $output_board_dvs_html . "♪" . $flag . "♪" . $seqno;
$param["print"] = $print_cate_top_html . "♪" . $print_cate_mid_html . "♪" . $flag . "♪" . $seqno;
$param["tmpt"] = $tmpt_sort_html . "♪" . $tmpt_print_name_html. "♪" . $tmpt_purp_dvs_html . "♪" . $flag . "♪" . $seqno;
$param["after"] = $after_name_html . "♪" . $flag . "♪" . $seqno;
$param["opt"] = $opt_name_html . "♪" . $flag . "♪" . $seqno;

/*
 * /com/nexmotion/html/basic_mng/prdt_mng/RegiPopupHtml.inc
 *
 * paperRegiPopupHtml($param["paper"], $val);
 * outputRegiPopupHtml($param["output"], $val);
 * sizeRegiPopupHtml($param["size"], $val);
 * printRegiPopupHtml($param["print"], $val);
 * tmptRegiPopupHtml($param["tmpt"], $val);
 * afterRegiPopupHtml($param["after"], $val);
 * optRegiPopupHtml($param["opt"], $val);
 *
 * $func = $select_el . "RegiPopupHtml";
 * $html = $func($param[$select_el], $val);
 */

//종이
if ($select_el == "paper") {
    $html = paperRegiPopupHtml($param["paper"], $val);
//출력
} else if ($select_el == "output") {
    $html = outputRegiPopupHtml($param["output"], $val);
//사이즈
} else if ($select_el == "size") {
    $html = sizeRegiPopupHtml($param["size"], $val);
//인쇄
} else if ($select_el == "print") {
    $html = printRegiPopupHtml($param["print"], $val);
//인쇄도수
} else if ($select_el == "tmpt") {
    $html = tmptRegiPopupHtml($param["tmpt"], $val);
//후공정
} else if ($select_el == "after") {
    $html = afterRegiPopupHtml($param["after"], $val);
//옵션
} else if ($select_el == "opt") {
    $html = optRegiPopupHtml($param["opt"], $val);
// 실사 후공정
} else if ($select_el == "ao_after") {
    $html = aoAfterRegiPopupHtml($param["after"], $val);
// 실사 옵션
} else if ($select_el == "ao_opt") {
    $html = aoOptRegiPopupHtml($param["opt"], $val);
}

echo $html . "♪" . $select_box_val;
$conn->close();
?>
