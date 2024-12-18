<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/ProcessMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ProcessMngDAO();
$util = new CommonUtil();

$typset_num = $fb->form("typset_num");
$state_arr = $fb->session("state_arr");

//상세보기 후공정 발주
$param = array();
$param["seqno"] = $seqno;

$rs = $dao->selectBasicAfterProcessView($conn, $param);

$basic_after_op_seqno = $rs->fields["basic_after_op_seqno"];
$flattyp_dvs = $rs->fields["flattyp_dvs"];
$html_param = array();
$html_param["basic_after_op_seqno"] = $rs->fields["basic_after_op_seqno"]; 
$html_param["after_name"] = $rs->fields["after_name"]; 
$html_param["depth1"] = $rs->fields["depth1"]; 
$html_param["depth2"] = $rs->fields["depth2"]; 
$html_param["depth3"] = $rs->fields["depth3"]; 
$html_param["cate"] = $rs->fields["cate_name"]; 
$html_param["typset_num"] = $rs->fields["typset_num"]; 
$html_param["orderer"] = $rs->fields["orderer"]; 
$html_param["typ"] = $rs->fields["op_typ"]; 
$html_param["typ_detail"] = $rs->fields["op_typ_detail"]; 
$html_param["amt"] = number_format(intVal($rs->fields["amt"])); 
$html_param["amt_unit"] = $rs->fields["amt_unit"]; 
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
            $param["req_width"] = "500";
            $param["req_height"] = "750";

            $pic = $fileDAO->makeThumbnail($param);

            //$html_param["pic"] .= "<img src=\"" . $file_path . $temp[0] . "_500_750." . $ext . "\">"; 

            //echo $file_path . $temp[0] . "_500_750." . $ext;

            $html_param["pic"] .= "<img src=\"" . $full_path . "\" style=\"width:100%; height:60%;\">"; 
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
            $param["req_width"] = "500";
            $param["req_height"] = "750";

            $pic = $fileDAO->makeThumbnail($param);

            //$html_param["pic"] .= "<img src=\"" . $file_path . $temp[0] . "_500_750." . $ext . "\">"; 

            //echo $file_path . $temp[0] . "_500_750." . $ext;

            $html_param["pic"] .= "<img src=\"" . $full_path . "\" style=\"width:100%; height:60%;\">"; 
        }
        $picture_rs->moveNext();
    }
}

$param = array();
$param["table"] = "after";
$param["col"] = "basic_price";
$param["where"]["extnl_brand_seqno"] = $extnl_brand_seqno;
$param["where"]["search_check"] = $rs->fields["after_name"] . "|" . $rs->fields["depth1"] . "|" . $rs->fields["depth2"] . "|" . $rs->fields["depth3"];
$where["where"]["crtr_unit"] = $rs->fields["amt_unit"];

$rs = $dao->selectData($conn, $param);

$basic_price = $rs->fields["basic_price"];
$price = number_format(intVal($basic_price) * $amt);

$html_param["price"] = $price; 

$param = array();
$param["table"] = "basic_after_work_report";
$param["col"] = "worker_memo ,work_start_hour ,work_end_hour ,adjust_price, worker";
$param["where"]["basic_after_op_seqno"] = $seqno;
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

echo basicAfterBarcode($html_param); 
$conn->close();
?>
