<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/ProcessMngDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/file/FileAttachDAO.inc");

$connectionPool = new ConnectionPool(); $conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ProcessMngDAO();
$util = new CommonUtil();
$fileDAO = new FileAttachDAO();

if (!$fb->form("seqno")) {
    echo "<script>alert('잘못된 접근입니다.');</script>";
    exit;
}

$seqno = $fb->form("seqno");
$state_arr = $fb->session("state_arr");
$option_html = "<option value=\"%s\" %s>%s</option>";

//상세보기 출력 발주
$param = array();
$param["seqno"] = $seqno;

$rs = $dao->selectOutputProcessView($conn, $param);

$board_amt = number_format(intVal($rs->fields["amt"]));

$html_param = array();
$html_param["output_name"] = $rs->fields["output_name"]; 
$html_param["typset_num"] = $rs->fields["typset_num"]; 
$html_param["orderer"] = $rs->fields["orderer"]; 
$html_param["typ"] = $rs->fields["typ"]; 
$html_param["typ_detail"] = $rs->fields["typ_detail"]; 
$html_param["size"] = $rs->fields["size"]; 
$html_param["affil"] = $rs->fields["affil"]; 
$html_param["amt"] = $board_amt; 
$html_param["subpaper"] = $rs->fields["subpaper"]; 
$html_param["memo"] = $rs->fields["memo"]; 

$board_dvs = "";
foreach (BOARD_DVS as $val) {

    $selected = "";
    if ($val == $rs->fields["board"]) {
        $selected = "selected=\"selected\"";
    }

    $board_dvs .= sprintf($option_html, $val, $selected, $val);
}

$html_param["board_dvs"] = $board_dvs; 

$disabled = "";
$btn_html = "";
$finish_modi = "";
$memo_select_html = <<<HTML
                             <select id="memo" onchange="changeMemo(this.value);">
                                 <option value="">선택</option>
                                 <option value="식사 및 휴식">식사 및 휴식</option>
                                 <option value="작업중단 요청">작업중단 요청</option>
                                 <option value="출력판 파손">출력판 파손</option>
                                 <option value="조판/출력 문제 발견">조판/출력 문제 발견</option>
                                 <option value="판 공급/수급">판 공급/수급</option>
                                 <option value="작업 취소">작업 취소</option>
                                 <option value="기타">기타</option>
                             </select>
                             <br />
HTML;

if ($rs->fields["state"] == $state_arr["출력대기"]) {
    $btn_html = <<<HTML
              <label class="btn btn_md fix_width180" onclick="getStart('$seqno');">시작</label>
              <label class="btn btn_md fix_width180" onclick="getHolding('$seqno');">보류</label>
              <!--<label class="btn btn_md fix_width180" onclick="getCancel('$seqno');">작업취소</label>-->

HTML;

} else if ($rs->fields["state"] == $state_arr["출력중"]) {
    $btn_html = <<<HTML
              <label class="btn btn_md fix_width180" onclick="getHolding('$seqno');">보류</label>
              <label class="btn btn_md fix_width180" onclick="getFinish('$seqno');">완료</label>
              <!--<label class="btn btn_md fix_width180" onclick="getCancel('$seqno');">작업취소</label>-->

HTML;
    $disabled = "disabled=\"disabled\"";
} else if ($rs->fields["state"] == $state_arr["출력취소"]) {
    $btn_html = <<<HTML
              <label class="btn btn_md fix_width180" onclick="getRestart('$seqno');">재시작</label>

HTML;
    $disabled = "";

} else if ($rs->fields["state"] == $state_arr["출력보류"]) {
    $btn_html = <<<HTML
              <label class="btn btn_md fix_width180" onclick="getRestart('$seqno');">재시작</label>

HTML;
    $disabled = "";
} else {
    $memo_select_html = "";
    $btn_html = "";
    $finish_modi = <<<HTML
                             이유 : <select id="modi_memo" style="width:300px;">
                                        <option value="">선택</option>
                                        <option value="판 추가 출력">판 추가 출력</option>
                                        <option value="사고">사고</option>
                                    </select>
                             판 수량 : <input type="text" class="input_co2" id="modi_board_amt" value="$board_amt">
                        <label class="btn btn_md fix_width180" onclick="getFinishUpdate('$seqno');">작업일지 기록 수정</label>
   
HTML;

    $disabled = "disabled=\"disabled\"";
}

$html_param["disabled"] = $disabled; 
$html_param["btn_html"] = $btn_html;
$html_param["finish_modi"] = $finish_modi;
$html_param["seqno"] = $seqno; 
$html_param["memo_select_html"] = $memo_select_html; 

$extnl_etprs_seqno = $rs->fields["extnl_etprs_seqno"];
$extnl_brand_seqno = $rs->fields["extnl_brand_seqno"];
$amt = $rs->fields["amt"];
$flattyp_dvs = $rs->fields["flattyp_dvs"];
$typset_num = $rs->fields["typset_num"];

$param = array();
$param["table"] = "output";
$param["col"] = "basic_price";
$param["where"]["extnl_brand_seqno"] = $extnl_brand_seqno;
$param["where"]["search_check"] = $rs->fields["output_name"] . "|" . $rs->fields["board"] . "|" . $rs->fields["size"];

$rs = $dao->selectData($conn, $param);

$basic_price = $rs->fields["basic_price"];
$price = number_format(intVal($basic_price) * $amt);

$html_param["price"] = $price; 

//제조사(수주처)
$param = array();
$param["table"] = "extnl_etprs";
$param["col"] = "extnl_etprs_seqno ,manu_name";
$param["where"]["pur_prdt"] = "출력";

$rs = $dao->selectData($conn, $param);

//$manu_html = "<option value=\"\">출력 제조사(전체)</option>";
$manu_html = "";

while ($rs && !$rs->EOF) {

    $selected = "";
    if ($rs->fields["extnl_etprs_seqno"] == $extnl_etprs_seqno) {
        $selected = "selected=\"selected\"";
    }
    $manu_html .= sprintf($option_html
            , $rs->fields["extnl_etprs_seqno"]
            , $selected
            , $rs->fields["manu_name"]);

    $rs->moveNext();
}

$html_param["manu_html"] = $manu_html; 

//브랜드
$param = array();
$param["table"] = "extnl_brand";
$param["col"] = "extnl_brand_seqno ,name";
$param["where"]["extnl_etprs_seqno"] = $extnl_etprs_seqno;

$rs = $dao->selectData($conn, $param);

//$brand_html = "<option value=\"\">출력 브랜드(전체)</option>";
$brand_html = "";

while ($rs && !$rs->EOF) {
 
    $selected = "";
    if ($rs->fields["extnl_brand_seqno"] == $extnl_brand_seqno) {
        $selected = "selected=\"selected\"";
    }
    $brand_html .= sprintf($option_html
            , $rs->fields["extnl_brand_seqno"]
            , $selected
            , $rs->fields["name"]);

    $rs->moveNext();
}

$html_param["brand_html"] = $brand_html; 

//낱장형 여부
if ($flattyp_dvs == "Y") {

    $param = array();
    $param["table"] = "sheet_typset";
    $param["col"] = "sheet_typset_seqno, oper_sys, 
        beforeside_tmpt, beforeside_spc_tmpt, aftside_tmpt, 
        aftside_spc_tmpt, paper_name, paper_dvs, paper_color,
        paper_basisweight, dlvrboard, after_list, opt_list";
    $param["where"]["typset_num"] = $typset_num;
 
    $sel_rs = $dao->selectData($conn, $param);
    $html_param["after_list"] = $sel_rs->fields["after_list"]; 
    $html_param["opt_list"] = $sel_rs->fields["opt_list"]; 
    $html_param["oper_sys"] = $sel_rs->fields["oper_sys"];
    $html_param["beforeside_tmpt"] = $sel_rs->fields["beforeside_tmpt"]; 
    $html_param["beforeside_spc_tmpt"] = $sel_rs->fields["beforeside_spc_tmpt"]; 
    $html_param["aftside_tmpt"] = $sel_rs->fields["aftside_tmpt"]; 
    $html_param["aftside_spc_tmpt"] = $sel_rs->fields["aftside_spc_tmpt"]; 
    $html_param["paper_name"] = $sel_rs->fields["paper_name"]; 
    $html_param["paper_dvs"] = $sel_rs->fields["paper_dvs"]; 
    $html_param["paper_color"] = $sel_rs->fields["paper_color"]; 
    $html_param["paper_basisweight"] = $sel_rs->fields["paper_basisweight"]; 
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
            $html_param["pic"] = "<img src=\"" . $full_path . "\" width=\"840px\" height=\"600px\">"; 
        } else {

            $temp = explode('.', $file_name);
            $ext = strtolower($temp[1]);

            $param = array();
            $param["fs"] = $full_path;
            $param["req_width"] = "840";
            $param["req_height"] = "600";

            $pic = $fileDAO->makeThumbnail($param);

            //$html_param["pic"] .= "<img src=\"" . $file_path . $temp[0] . "_840_600." . $ext . "\">"; 

            //echo $file_path . $temp[0] . "_840_600." . $ext;

            $html_param["pic"] .= "<img src=\"" . $full_path . "\" width=\"840px\" height=\"600px\">"; 
        }
        $picture_rs->moveNext();
    }

} else if ($flattyp_dvs == "N") {
    $param = array();
    $param["table"] = "brochure_typset";
    $param["col"] = "brochure_typset_seqno, oper_sys, 
        beforeside_tmpt, beforeside_spc_tmpt, aftside_tmpt, 
        aftside_spc_tmpt, paper_name, paper_dvs, paper_color,
        paper_basisweight, dlvrboard";
    $param["where"]["typset_num"] = $typset_num;

    $sel_rs = $dao->selectData($conn, $param);
    $html_param["oper_sys"] = $sel_rs->fields["oper_sys"];
    $html_param["beforeside_tmpt"] = $sel_rs->fields["beforeside_tmpt"]; 
    $html_param["beforeside_spc_tmpt"] = $sel_rs->fields["beforeside_spc_tmpt"]; 
    $html_param["aftside_tmpt"] = $sel_rs->fields["aftside_tmpt"]; 
    $html_param["aftside_spc_tmpt"] = $sel_rs->fields["aftside_spc_tmpt"]; 
    $html_param["paper_name"] = $sel_rs->fields["paper_name"]; 
    $html_param["paper_dvs"] = $sel_rs->fields["paper_dvs"]; 
    $html_param["paper_color"] = $sel_rs->fields["paper_color"]; 
    $html_param["paper_basisweight"] = $sel_rs->fields["paper_basisweight"]; 
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
            $html_param["pic"] = "<img src=\"" . $full_path . "\" width=\"840px\" height=\"600px\">"; 
        } else {

            $temp = explode('.', $file_name);
            $ext = strtolower($temp[1]);

            $param = array();
            $param["fs"] = $full_path;
            $param["req_width"] = "840";
            $param["req_height"] = "600";

            $pic = $fileDAO->makeThumbnail($param);

            //$html_param["pic"] .= "<img src=\"" . $file_path . $temp[0] . "_840_600." . $ext . "\">"; 

            //echo $file_path . $temp[0] . "_840_600." . $ext;

            $html_param["pic"] .= "<img src=\"" . $full_path . "\" width=\"840px\" height=\"600px\">"; 
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
$param["table"] = "output_work_report";
$param["col"] = "worker_memo ,work_start_hour ,work_end_hour ,valid_yn, worker, state";
$param["where"]["output_op_seqno"] = $seqno;

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

    if ($state == "인쇄대기" || $state == "후공정대기") {
        $state = "출력완료";
    }

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
$param["table"] = "output_work_report";
$param["col"] = "worker_memo ,work_start_hour ,work_end_hour ,adjust_price, worker";
$param["where"]["output_op_seqno"] = $seqno;
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

echo outputProcessAddPopup($html_param); 
$conn->close();
?>
