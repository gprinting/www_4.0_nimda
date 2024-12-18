<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/ProcessMngDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/file/FileAttachDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ProcessMngDAO();
$util = new CommonUtil();
$fileDAO = new FileAttachDAO();

$typset_num = $fb->form("typset_num");
$state_arr = $fb->session("state_arr");

//상세보기 출력 발주
$param = array();
$param["typset_num"] = $typset_num;
$rs = $dao->selectPrintProcessView($conn, $param);

$print_op_seqno = $rs->fields["print_op_seqno"];
$flattyp_dvs = "Y";
$html_param = array();
$html_param["print_title"] = $rs->fields["print_title"];
$html_param["typset_num"] = $rs->fields["typset_num"]; 
$html_param["orderer"] = $rs->fields["orderer"]; 
$html_param["typ"] = $rs->fields["typ"]; 
$html_param["typ_detail"] = $rs->fields["typ_detail"]; 
$html_param["size"] = $rs->fields["size"]; 
$html_param["affil"] = $rs->fields["affil"]; 
$html_param["amt"] = number_format(intVal($rs->fields["print_amt"]));
$html_param["amt_unit"] = $rs->fields["amt_unit"]; 
$html_param["memo"] = $rs->fields["memo"]; 
$html_param["beforeside_tmpt"] = $rs->fields["beforeside_tmpt"]; 
$html_param["beforeside_spc_tmpt"] = $rs->fields["beforeside_spc_tmpt"]; 
$html_param["aftside_tmpt"] = $rs->fields["aftside_tmpt"]; 
$html_param["aftside_spc_tmpt"] = $rs->fields["aftside_spc_tmpt"]; 

$extnl_etprs_seqno = $rs->fields["extnl_etprs_seqno"];
$extnl_brand_seqno = $rs->fields["extnl_brand_seqno"];
$amt = $rs->fields["amt"];

/*
$param = array();
$param["table"] = "print";
$param["col"] = "basic_price";
$param["where"]["extnl_brand_seqno"] = $extnl_brand_seqno;
$param["where"]["search_check"] = $rs->fields["print_name"] . "|" . $rs->fields["size"];
$where["where"]["amt_unit"] = $rs->fields["amt_unit"];

$rs = $dao->selectData($conn, $param);

$basic_price = $rs->fields["basic_price"];
$price = number_format(intVal($basic_price) * $amt);

$html_param["price"] = $price; 
*/

//낱장형 여부
if ($flattyp_dvs == "Y") {

    $param = array();
    $param["table"] = "sheet_typset";
    $param["col"] = "opt_list ,sheet_typset_seqno, oper_sys
        ,paper_name ,paper_dvs ,paper_color ,paper_basisweight
        ,beforeside_tmpt ,beforeside_spc_tmpt ,aftside_tmpt
        ,aftside_spc_tmpt, dlvrboard";
    $param["where"]["typset_num"] = $typset_num;
 
    //옵션 목록 호출 
    $sel_rs = $dao->selectData($conn, $param);
    $html_param["opt_list"] = $sel_rs->fields["opt_list"]; 
    $html_param["oper_sys"] = $sel_rs->fields["oper_sys"];
    $html_param["paper_info"] = $sel_rs->fields["paper_name"] . " " .
        $sel_rs->fields["paper_dvs"] .  " " . $sel_rs->fields["paper_color"] . 
        " " . $sel_rs->fields["paper_basisweight"];

    $html_param["beforeside_tmpt"] = $sel_rs->fields["beforeside_tmpt"];
    $html_param["beforeside_spc_tmpt"] = $sel_rs->fields["beforeside_spc_tmpt"];
    $html_param["aftside_tmpt"] = $sel_rs->fields["aftside_tmpt"];
    $html_param["aftside_spc_tmpt"] = $sel_rs->fields["aftside_spc_tmpt"];
    $html_param["dlvrboard"] = $sel_rs->fields["dlvrboard"];

    //조판 파일
    $param = array();
    $param["table"] = "sheet_typset_preview_file";
    $param["col"] = "file_path, save_file_name";
    $param["where"]["sheet_typset_seqno"] = $sel_rs->fields["sheet_typset_seqno"];
 
    $picture_rs = $dao->selectData($conn, $param);
  
    $html_param["pic"] = "";
    while ($picture_rs && !$picture_rs->EOF) {
        $file_path = $picture_rs->fields["file_path"];
        $file_name = $picture_rs->fields["save_file_name"];

        $full_path = $file_path . $file_name;
        $chk_path = INC_PATH . $full_path;

        if (is_file($chk_path) === false) {
            $full_path = NO_IMAGE;
            $html_param["pic"] .= "<img src=\"" . $full_path . "\" style=\"width:100%; height:60%;\">"; 
        } else {

            $temp = explode('.', $file_name);
            $ext = strtolower($temp[1]);

            $param = array();
            $param["fs"] = $full_path;
            $param["req_width"] = "840";
            $param["req_height"] = "600";

            $pic = $fileDAO->makeThumbnail($param);

            //$html_param["pic"] .= "<img src=\"" . $file_path . $temp[0] . "_500_750." . $ext . "\">"; 

            //echo $file_path . $temp[0] . "_500_750." . $ext;

            $html_param["pic"] .= "<img src=\"" . $full_path . "\" style=\"width:840px; height:600px;\">"; 
        }
        $picture_rs->moveNext();
    }
 
//책자형 여부
} else if ($flattyp_dvs == "N") {
    $param = array();
    $param["table"] = "brochure_typset";
    $param["col"] = "opt_list ,brochure_typset_seqno, oper_sys
        ,paper_name ,paper_dvs ,paper_color ,paper_basisweight
        ,beforeside_tmpt ,beforeside_spc_tmpt ,aftside_tmpt
        ,aftside_spc_tmpt, dlvrboard";
    $param["where"]["typset_num"] = $typset_num;
    
    $sel_rs = $dao->selectData($conn, $param);
    
    //옵션 목록 호출 
    $html_param["opt_list"] = $sel_rs->fields["opt_list"]; 
    $html_param["oper_sys"] = $sel_rs->fields["oper_sys"];
    $html_param["paper_info"] = $sel_rs->fields["paper_name"] . " " .
        $sel_rs->fields["paper_dvs"] .  " " . $sel_rs->fields["paper_color"] . 
        " " . $sel_rs->fields["paper_basisweight"];

    $html_param["beforeside_tmpt"] = $sel_rs->fields["beforeside_tmpt"];
    $html_param["beforeside_spc_tmpt"] = $sel_rs->fields["beforeside_spc_tmpt"];
    $html_param["aftside_tmpt"] = $sel_rs->fields["aftside_tmpt"];
    $html_param["aftside_spc_tmpt"] = $sel_rs->fields["aftside_spc_tmpt"];
    $html_param["dlvrboard"] = $sel_rs->fields["dlvrboard"];

    //조판 파일
    $param = array();
    $param["table"] = "brochure_typset_file";
    $param["col"] = "file_path, save_file_name";
    $param["where"]["brochure_typset_seqno"] = $sel_rs->fields["brochure_typset_seqno"];
 
    $picture_rs = $dao->selectData($conn, $param);
  
    $html_param["pic"] = "";
    while ($picture_rs && !$picture_rs->EOF) {
        $file_path = $picture_rs->fields["file_path"];
        $file_name = $picture_rs->fields["save_file_name"];

        $full_path = $file_path . $file_name;
        $chk_path = INC_PATH . $full_path;

        if (is_file($chk_path) === false) {
            $full_path = NO_IMAGE;
            $html_param["pic"] .= "<img src=\"" . $full_path . "\" style=\"width:100%; height:60%;\">"; 
        } else {

            $temp = explode('.', $file_name);
            $ext = strtolower($temp[1]);

            $param = array();
            $param["fs"] = $full_path;
            $param["req_width"] = "840";
            $param["req_height"] = "600";

            $pic = $fileDAO->makeThumbnail($param);

            //$html_param["pic"] .= "<img src=\"" . $file_path . $temp[0] . "_500_750." . $ext . "\">"; 

            //echo $file_path . $temp[0] . "_500_750." . $ext;

            $html_param["pic"] .= "<img src=\"" . $full_path . "\" style=\"width:840px; height:600px;\">"; 
        }
        $picture_rs->moveNext();
    }
}

$list  = "\n<tr class='%s'>";
$list .= "\n    <td>%s</td>";
$list .= "\n    <td>%s</td>";
$list .= "\n    <td>%s</td>";
$list .= "\n    <td>%s</td>";
$list .= "\n    <td>%s</td>";
$list .= "\n</tr>";

//작업지시서 히스토리
$param = array();
$param["table"] = "print_work_report";
$param["col"] = "worker_memo ,work_start_hour ,work_end_hour ,valid_yn, worker, state";
$param["where"]["print_op_seqno"] = $print_op_seqno;

$rs = $dao->selectData($conn, $param);

if ($rs->EOF) {
   $html_param["work_list"] = "<tr><td colspan=\"5\">작업일지 히스토리가 없습니다.</td></tr>";
}

$i = 1;
while ($rs && !$rs->EOF) {

    if ($i % 2 == 0) {
        $class = "cellbg";
    } else if ($i % 2 == 1) {
        $class = "";
    }

    $work_time = $rs->fields["work_start_hour"];
    if ($rs->fields["work_end_hour"]) {
        $work_time .= " ~ " . $rs->fields["work_end_hour"];
    }

    $state = $util->statusCode2status($rs->fields["state"]);

    $html_param["work_list"] .= sprintf($list, $class, 
            $work_time,
            $rs->fields["worker_memo"],
            $rs->fields["valid_yn"],
            $rs->fields["worker"],
            $state);
    $i++;
    $rs->moveNext();
}

$param = array();
$param["table"] = "print_work_report";
$param["col"] = "worker_memo ,work_start_hour ,subpaper
,work_end_hour ,adjust_price, worker ,ink_C ,ink_M, ink_Y ,ink_K";
$param["where"]["print_op_seqno"] = $print_op_seqno;
$param["where"]["valid_yn"] = "Y";

$rs = $dao->selectData($conn, $param);

if ($rs->fields["worker"]) {
    $html_param["worker"] = $rs->fields["worker"]; 
} else {
    $html_param["worker"] = $fb->session("name");
}

$html_param["worker_memo"] = $rs->fields["worker_memo"]; 
$html_param["work_start_hour"] = $rs->fields["work_start_hour"]; 
if ($rs->fields["work_end_hour"]) {
    $html_param["work_end_hour"] = " ~ " . $rs->fields["work_end_hour"]; 
}
$html_param["adjust_price"] = number_format(intVal($rs->fields["adjust_price"])); 
$html_param["ink_C"] = $rs->fields["ink_C"]; 
$html_param["ink_M"] = $rs->fields["ink_M"]; 
$html_param["ink_Y"] = $rs->fields["ink_Y"]; 
$html_param["ink_K"] = $rs->fields["ink_K"]; 
$subpaper = $rs->fields["subpaper"]; 
$arr = array();
$arr[0] = "전절";
$arr[1] = "2절";
$arr[2] = "4절";
$arr[3] = "8절";
$arr[4] = "16절";
$arr[5] = "32절";

$subpaper_html = "<option value=\"\">절수(선택)</option>";
for ($i=0; $i < 5; $i++) {

    $selected = "";
    if ($subpaper == $arr[$i]) {
        $selected = "selected=\"selected\"";
    }

    $subpaper_html .= sprintf($option_html, $arr[$i], 
            $selected, $arr[$i]);
}
$html_param["subpaper_html"] = $subpaper_html; 

echo printBarcode($html_param);
$conn->close();
?>
