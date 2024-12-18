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

$rs = $dao->selectOutputProcessView($conn, $param);

$output_op_seqno = $rs->fields["output_op_seqno"];
$flattyp_dvs = $rs->fields["flattyp_dvs"];
$html_param = array();
$html_param["output_name"] = $rs->fields["output_name"]; 
$html_param["typset_num"] = $rs->fields["typset_num"]; 
$html_param["orderer"] = $rs->fields["orderer"]; 
$html_param["typ"] = $rs->fields["typ"]; 
$html_param["typ_detail"] = $rs->fields["typ_detail"]; 
$html_param["subpaper"] = $rs->fields["supaper"]; 
$html_param["size"] = $rs->fields["size"]; 
$html_param["affil"] = $rs->fields["affil"]; 
$html_param["amt"] = number_format(intVal($rs->fields["amt"])); 
$html_param["amt_unit"] = $rs->fields["amt_unit"]; 
$html_param["board"] = $rs->fields["board"]; 
$html_param["memo"] = $rs->fields["memo"]; 

//낱장형 여부
if ($flattyp_dvs == "Y") {

    $param = array();
    $param["table"] = "sheet_typset";
    $param["col"] = "opt_list ,sheet_typset_seqno, oper_sys
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

$param = array();
$param["table"] = "output_work_report";
$param["col"] = "worker_memo ,work_start_hour ,work_end_hour ,adjust_price, worker";
$param["where"]["output_op_seqno"] = $output_op_seqno;
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

echo outputBarcode($html_param);
$conn->close();
?>
