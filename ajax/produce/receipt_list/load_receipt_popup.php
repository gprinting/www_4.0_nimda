<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/com/nexmotion/doc/nimda/produce/receipt_mng/ReceiptListDOC.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/receipt_mng/ReceiptListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ReceiptListDAO();

$seqno = $fb->form("seqno");
$state = $fb->form("state");
$state_arr = $fb->session("state_arr");
$order_detail_dvs_num = $fb->form("order_detail_dvs_num");

//접수 여부 확인
$param = array();
$param["order_common_seqno"] = $seqno;

$sel_rs = $dao->selectOrderState($conn, $param);

if ($sel_rs->fields["order_state"] == $state_arr["접수중"]) {
    echo "err";
    exit;
}

$rs = $dao->selectReceiptView($conn, $param);

$param = array();
$param["table"] = "order_after_history";
$param["col"] = "max(seq) AS maxseq";
//$param["where"]["order_common_seqno"] = $seqno;
$param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;

$after_rs = $dao->selectData($conn, $param);

$maxseq = $after_rs->fields["maxseq"];

$param = array();
$param["table"] = "order_after_history";
$param["col"] = "basic_yn ,after_name ,depth1 ,depth2
                ,depth3 ,seq ,order_after_history_seqno";
//$param["where"]["order_common_seqno"] = $seqno;
$param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;
$param["order"] = "seq ASC";

$after_rs = $dao->selectData($conn, $param);

//후공정 요약 html
$html  = "\n<tr>";
$html .= "\n  <td class=\"fwb\">%s</td>";
$html .= "\n  <td>%s</td>";
$html .= "\n  <td>%s</td>";
$html .= "\n<tr>";
$i = 1;

//추가 후공정일 경우 버튼html
$button_html = "";
if ($state == 310) {
    $button_html .= "\n<button class=\"btn btn_pu fix_width40 fix_height20 orge fs11\" onclick=\"getSeqDown('%s', '%s', '%s');\" %s>▼</button>";
    $button_html .= "\n<button class=\"btn btn_pu fix_width40 fix_height20 orge fs11\" onclick=\"getSeqUp('%s', '%s', '%s');\" %s>▲</button>";
    $button_html .= "\n<button class=\"bgreen btn_pu btn fix_height20 fix_width40\" onclick=\"getAfterPop('%s', '%s', '%s', '%s');\">등록</button>";
} else {
    $button_html .= "\n<button class=\"bgreen btn_pu btn fix_height20 fix_width40\" onclick=\"getAfterViewPop('%s', '%s', '%s');\">보기</button>";
}

while ($after_rs && !$after_rs->EOF) {

    $subject = "";
    if ($i === 1) {
        $subject = "후공정";
    }

    $seq_up_dis = "";
    $seq_down_dis = "";
    if ($after_rs->fields["seq"] == 1) {
        $seq_up_dis = "disabled=\"disabled\"";
    }

    if ($after_rs->fields["seq"] == $maxseq) {
        $seq_down_dis = "disabled=\"disabled\"";
    }

    //기본후공정 여부가 N일때(추가 후공정일경우)
    if ($after_rs->fields["basic_yn"] === "N") {

        if ($state == 310) {
            $button = sprintf($button_html,
                    $seqno,
                    $order_detail_dvs_num,
                    $after_rs->fields["seq"],
                    $seq_down_dis,
                    $seqno,
                    $order_detail_dvs_num,
                    $after_rs->fields["seq"],
                    $seq_up_dis,
                    $seqno,
                    $after_rs->fields["order_after_history_seqno"],
                    $order_detail_dvs_num,
                    $state);
        } else {
            $button = sprintf($button_html,
                    $seqno,
                    $after_rs->fields["order_after_history_seqno"],
                    $state);
        }
    }

    $after_name = "";
    if ($after_rs->fields["after_name"]) {
        $after_name .= $after_rs->fields["after_name"];
    }
    if ($after_rs->fields["depth1"]) {
        $after_name .= "-". $after_rs->fields["depth1"];
    }
    if ($after_rs->fields["depth2"]) {
        if ($after_rs->fields["depth2"] == "-") {
            $after_name .= "";
        } else {
            $after_name .= "-". $after_rs->fields["depth2"];
        }
    }
    if ($after_rs->fields["depth3"]) {
        if ($after_rs->fields["depth3"] == "-") {
            $after_name .= "";
        } else {
            $after_name .= "-". $after_rs->fields["depth3"];
        }
    }

    $after_html .= sprintf($html, $subject,
                           $after_name,
                           $button);
    $i++;
    $after_rs->moveNext();
}

$stor_release_y = "";
$stor_release_n = "";
if ($rs->fields["stor_release_yn"] === "Y") {
    $stor_release_y = "checked=\"checked\"";
} else {
    $stor_release_n = "checked=\"checked\"";
}

$param = array();
$param["table"] = "order_file";
$param["col"] = "order_file_seqno, origin_file_name";
$param["where"]["order_common_seqno"] = $seqno;

$order_file_rs = $dao->selectData($conn, $param);

$html  = "<label class=\"control-label fix_width75 tar\">원본파일</label>";
$html .= "<label class=\"fix_width20 fs14 tac\">:</label>";
$html .= "<label class=\"control-label\"><a href=\"/common/order_file_down.inc?seqno=%s\">%s</a></label><br />";

$order_file_html = sprintf($html,
        $order_file_rs->fields["order_file_seqno"],
        $order_file_rs->fields["origin_file_name"]);

$param = array();
$param["order_common_seqno"] = $seqno;

$order_dlvr_rs = $dao->selectOrderDlvr($conn, $param);

$param = array();
$param["table"] = "order_opt_history";
$param["col"] = "opt_name";
$param["where"]["order_common_seqno"] = $seqno;

$opt_rs = $dao->selectData($conn, $param);

$opt_name = "";
while ($opt_rs && !$opt_rs->EOF) {

    $opt_name .= $opt_rs->fields["opt_name"] . ", ";
    $opt_rs->moveNext();
}

//전에 대기일 경우 접수중으로 변경
if ($state == $state_arr["접수대기"]) {
    $conn->StartTrans();

    //차후 옵션명이 명확해지면 수정 해야됨
    $order_state = "";
    if ($opt_rs->fields["opt_name"] === "시안요청") {
        $order_state = $state_arr["시안요청중"];
    } else {
        $order_state = $state_arr["접수중"];
    }

    //접수 상태 변경 (중, 시안확인)
    $param = array();
    $param["seqno"] = $seqno;
    $param["order_state"] = $order_state;
    $param["receipt_mng"] = $fb->session("name");

    $dao->updateReceipt($conn, $param);

    $conn->CompleteTrans();
}

$param = array();
$param["table"] = "order_detail";
$param["col"] = "order_detail_seqno";
$param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;

$sel_rs = $dao->selectData($conn, $param);

$order_detail_seqno = $sel_rs->fields["order_detail_seqno"];

$pre_file_html = "";

$file_html = "<span style=\"font-size: 14px;font-weight: bold;padding: 10px 0;\"><a href=\"/common/order_detail_count_file_down.inc?seqno=%s\">%s</a></span><br />";
$file_html1 = "<label class=\"fix_width99\"></label><span style=\"font-size: 14px;font-weight: bold;padding: 10px 0;\"><a href=\"/common/order_detail_count_file_down.inc?seqno=%s\">%s</a></span><br />";
$work_file_html = <<<HTML
							                    	           	    <label class="control-label fix_width75 tar" style="padding-bottom:0;">작업파일</label><label class="fix_width20 fs14 tac">:</label>	
HTML;

$i = 1;
$uploadedFileHtml = "";
$param = array();
$param["table"] = "order_detail_count_file";
$param["col"] = "origin_file_name, order_detail_count_file_seqno, size, file_path";
$param["where"]["order_detail_seqno"] = $order_detail_seqno;

$sel_rs2 = $dao->selectData($conn, $param);

while ($sel_rs2 && !$sel_rs2->EOF) {

    if ($i == 1) {
        $work_file_html .= sprintf($file_html, 
                $sel_rs2->fields["order_detail_count_file_seqno"], 
                $sel_rs2->fields["origin_file_name"]);
    } else {
        $work_file_html .= sprintf($file_html1,
                $sel_rs2->fields["order_detail_count_file_seqno"], 
                $sel_rs2->fields["origin_file_name"]);
    }

    $i++;

    //uploadedFile 리스트 작성
    if ($sel_rs2->fields["order_detail_count_file_seqno"] && $sel_rs2->fields["file_path"]) {
        $uploadedFileHtml .= "<span id=\"uploadedFile_". $sel_rs2->fields["order_detail_count_file_seqno"] ."\" style=\"padding-left: 98px;\">";
        $uploadedFileHtml .= "<a href=\"/common/order_detail_count_file_down.inc?seqno=" . $sel_rs2->fields["order_detail_count_file_seqno"] . "\">" . $sel_rs2->fields["origin_file_name"] ." (";
        $uploadedFileHtml .= getFileSize($sel_rs2->fields["size"]) .")";
        $uploadedFileHtml .= "<b>100%</b></a>&nbsp";
        $uploadedFileHtml .= "<img src=\"/design_template/images/btn_circle_x_red.png\"";
        $uploadedFileHtml .= "  id=\"work_file_del_". $sel_rs2->fields["order_detail_count_file_seqno"] ."\"";
        $uploadedFileHtml .= "  file_seqno=\"". $sel_rs2->fields["order_detail_count_file_seqno"] ."\"";
        $uploadedFileHtml .= "  alt=\"X\"";
        $uploadedFileHtml .= "  onclick=\"removeUploadedFile('". $sel_rs2->fields["order_detail_count_file_seqno"] ."');\"";
        $uploadedFileHtml .= "  style=\"cursor:pointer;\" /><br />";
        $uploadedFileHtml .= "</span>";
    }
    $sel_rs2->moveNext();
} 

$upload_html = <<<HTML
							                    	           	    <label class="control-label fix_width75 tar blue_text01">프로그램</label><label class="fix_width20 fs14 tac">:</label>	
                                                                    <select id="font_file_upload_yn">
                                                                        <option value="">선택</option>
                                                                        <option value="N">CorelDraw</option>
                                                                        <option value="N">Illustrator</option>
                                                                        <option value="Y">Quark</option>
                                                                    </select>
							                    	           	    <label class="tar fs13 red_text01"> *작업프로그램 타입을 설정해주세요.</label>	
                                                                    <br />

							                    	           	    <label class="control-label fix_width75 tar blue_text01">작업파일</label><label class="fix_width20 fs14 tac">:</label>	
											                 	    <label class="fileUpload">
											                 	         <button class="btn btn-sm btn-info fa fa-folder-open" id="work_file">찾아보기</button>
											                 	    </label>
                                                                    <button class="btn btn-sm btn-info fa fa-folder-open" onclick="uploadFile('{$order_detail_seqno}');">업로드</button>
                                                                    <br />

                                                                    <div id="uploaded_work_file_{$seqno}">$uploadedFileHtml</div>

                                                                    <div id="work_file_list"></div>
                                                                    <input type="hidden" id="work_file_seqno" name="work_file_seqno" />
                                                                    <label class="fix_width94"></label>
							                    	           	    <label class="tar fs13 red_text01"> *작업파일을 건수와 동일 갯수로 업로드해주세요.</label>	
                                                                    <br />
                                                                    <label class="fix_width94"></label>
							                    	           	    <label class="tar fs13 red_text01"> *작업파일명은 주문 상세 건수번호로 변경됩니다.</label>	
HTML;

$cate_name = $rs->fields["cate_name"];
$order_detail = str_replace($cate_name . " /","",$rs->fields["order_detail"]);
$paper_info = explode("/", $order_detail);

$param = array();
$param["order_detail_seqno"] = $order_detail_seqno;
$param["order_num"] = $rs->fields["order_num"];
$param["member_name"] = $rs->fields["member_name"];
$param["office_nick"] = $rs->fields["office_nick"];
$param["oper_sys"] = $rs->fields["oper_sys"];
$param["title"] = $rs->fields["title"];
$param["cate_name"] = $cate_name;
$param["amt"] = $rs->fields["amt"];
$param["amt_unit_dvs"] = $rs->fields["amt_unit_dvs"];
$param["paper_info"] = $paper_info[0]; 
$param["stan_name"] = $rs->fields["stan_name"];
$param["print_tmpt_name"] = $rs->fields["print_tmpt_name"];
$param["after_html"] = $after_html;
$param["stor_release_y"] = $stor_release_y;
$param["stor_release_n"] = $stor_release_n;
$param["memo"] = $rs->fields["memo"];
$param["count"] = $rs->fields["count"];
$param["order_file_html"] = $order_file_html;
$param["dlvr_way"] = DLVR_TYP[$order_dlvr_rs->fields["dlvr_way"]];
$param["dlvr_sum_way"] = DLVR_PAY_TYP[$order_dlvr_rs->fields["dlvr_sum_way"]];
$param["order_addr"] = $order_dlvr_rs->fields["addr"];
$param["order_name"] = $order_dlvr_rs->fields["name"];
$param["opt_name"] = substr($opt_name , 0, -2);
$param["seqno"] = $seqno;
$param["state"] = $state;
$param["order_detail_dvs_num"] = $order_detail_dvs_num;

if ($state != $state_arr["접수대기"]) {
    $param["disabled"] = "disabled=\"disabled\"";
    $param["upload_html"] = $work_file_html;
} else {
    $param["upload_html"] = $upload_html;
}

echo receiptPopup($param);
$conn->close();

//파일 용량 단위 변환 함수
function getFileSize($size, $float = 0) { 
    $unit = array('byte', 'kb', 'mb', 'gb', 'tb'); 
    for ($L = 0; intval($size / 1024) > 0; $L++, $size/= 1024); 
    if (($float === 0) && (intval($size) != $size)) $float = 2; 
    return round(number_format($size, $float, '.', ',')) .' '. $unit[$L]; 
} 
?>
