<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/after_mng/AfterListDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/file/FileAttachDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new AfterListDAO();
$util = new CommonUtil();
$fileDAO = new FileAttachDAO();

if (!$fb->form("seqno")) {
    echo "<script>alert('잘못된 접근입니다.');</script>";
    exit;
}

$seqno = $fb->form("seqno");
$state_arr = $fb->session("state_arr");
$option_html = "<option value=\"%s\" %s>%s</option>";

//상세보기 후공정 발주
$param = array();
$param["seqno"] = $seqno;

$rs = $dao->selectAfterProcessView($conn, $param);

//낱장형(S) / 책자형(B) 여부
$flattyp_dvs = substr($rs->fields["order_detail_dvs_num"], 0, 1);

$html_param = array();
$html_param["after_name"] = $rs->fields["after_name"]; 
$html_param["depth1"] = $rs->fields["depth1"]; 
$html_param["depth2"] = $rs->fields["depth2"]; 
$html_param["depth3"] = $rs->fields["depth3"]; 
$html_param["order_num"] = $rs->fields["order_num"]; 
$html_param["order_detail"] = $rs->fields["order_detail"]; 
$html_param["oper_sys"] = $rs->fields["oper_sys"]; 
$html_param["orderer"] = $rs->fields["orderer"]; 
$html_param["amt"] = number_format(intVal($rs->fields["amt"])); 
$html_param["amt_unit"] = $rs->fields["amt_unit"]; 
$html_param["memo"] = $rs->fields["memo"]; 
$html_param["dlvrboard"] = $rs->fields["dlvrboard"]; 
$html_param["specialty_items"] = $rs->fields["specialty_items"]; 

$disabled = "";
$btn_html = "";
$work_yn = "";
$memo_select_html = <<<HTML
                             <select id="memo" onchange="changeMemo(this.value);">
                                 <option value="">선택</option>
                                 <option value="식사 및 휴식">식사 및 휴식</option>
                                 <option value="작업중단 요청">작업중단 요청</option>
                                 <option value="후공정 파일문제">후공정 파일문제</option>
                                 <option value="전공정 문제 발견">전공정 문제 발견</option>
                                 <option value="후공정 공급/수급">후공정 공급/수급</option>
                                 <option value="작업 취소">작업 취소</option>
                                 <option value="기타">기타</option>
                             </select>
                             <input type="text" class="input_dis" style="width:77%;" id="worker_memo" disabled="disabled" value="$param[worker_memo]">
HTML;

if ($rs->fields["state"] == $state_arr["주문후공정대기"]) {
    $btn_html = <<<HTML
              <label class="btn btn_md fix_width180" onclick="getStart('$seqno');">시작</label>
              <label class="btn btn_md fix_width180" onclick="getHolding('$seqno');">보류</label>
              <!--label class="btn btn_md fix_width180" onclick="getCancel('$seqno');">작업취소</label-->

HTML;
    $work_yn = "N";

} else if ($rs->fields["state"] == $state_arr["주문후공정중"]) {
    $btn_html = <<<HTML
              <label class="btn btn_md fix_width180" onclick="getHolding('$seqno');">보류</label>
              <label class="btn btn_md fix_width180" onclick="getFinish('$seqno');">완료</label>
              <!--label class="btn btn_md fix_width180" onclick="getCancel('$seqno');">작업취소</label-->

HTML;
    $disabled = "disabled=\"disabled\"";
    $work_yn = "Y";

} else if ($rs->fields["state"] == $state_arr["주문후공정취소"]) {
    $btn_html = <<<HTML
              <label class="btn btn_md fix_width180" onclick="getRestart('$seqno');">재시작</label>

HTML;
    $disabled = "";
    $work_yn = "Y";

} else if ($rs->fields["state"] == $state_arr["주문후공정보류"]) {
    $btn_html = <<<HTML
              <label class="btn btn_md fix_width180" onclick="getRestart('$seqno');">재시작</label>

HTML;
    $disabled = "";
    $work_yn = "Y";

} else {
    $memo_select_html = "";
    $btn_html = "";
    $disabled = "disabled=\"disabled\"";
    $work_yn = "Y";
}

$html_param["disabled"] = $disabled; 
$html_param["btn_html"] = $btn_html;
$html_param["seqno"] = $seqno; 
$html_param["memo_select_html"] = $memo_select_html; 

$order_common_seqno = $rs->fields["order_common_seqno"];
$extnl_etprs_seqno = $rs->fields["extnl_etprs_seqno"];
$extnl_brand_seqno = $rs->fields["extnl_brand_seqno"];
$amt = $rs->fields["amt"];
$typset_num = $rs->fields["typset_num"];

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

//제조사(수주처)
$param = array();
$param["table"] = "extnl_etprs";
$param["col"] = "extnl_etprs_seqno ,manu_name";
$param["where"]["pur_prdt"] = "후공정";

$rs = $dao->selectData($conn, $param);

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

/*
$slider_html = "";

//낱장
if ($flattyp_dvs === "S") {
    //주문 상세 일련번호
    $param = array();
    $param["table"] = "order_detail";
    $param["col"] = "order_detail_seqno";
    $param["where"]["order_common_seqno"] = $order_common_seqno;

    $order_detail_rs = $dao->selectData($conn, $param);
    $class = "";

    while ($order_detail_rs && !$order_detail_rs->EOF) {

        if ($order_detail_rs->fields["order_detail_seqno"]) {

            $param = array();
            $param["table"] = "order_detail_count_file";
            $param["col"] = "order_detail_count_file_seqno";
            $param["where"]["order_detail_seqno"] = $order_detail_rs->fields["order_detail_seqno"];

            $seqno_rs = $dao->selectData($conn, $param);

            while ($seqno_rs && !$seqno_rs->EOF) {

                //주문상세파일
                $param = array();
                $param["table"] = "order_detail_count_preview_file";
                $param["col"] = "preview_file_path, preview_file_name";
                $param["where"]["order_detail_count_file_seqno"] = $seqno_rs->fields["order_detail_count_file_seqno"];

                $picture_rs = $dao->selectData($conn, $param);
                $i = 1;

                while ($picture_rs && !$picture_rs->EOF) {

                    $file_path = $picture_rs->fields["preview_file_path"];
                    $file_name = $picture_rs->fields["preview_file_name"];

                    $full_path = $file_path . $file_name;
                    $chk_path = str_replace(INC_PATH, "", $full_path);

                    if (is_file($full_path) === false) {
                        $full_path = NO_IMAGE;
                    }

                    if ($i == 1) {
                        $class = "class=\"on\"";
                    } else {
                        $class = "";
                    } 

                    $slider_html .= "\n<li " . $class . "><img src=\"" . $chk_path . "\" style=\"width:420px;height:300px;\" alt=\"" . $file_name . "\"></li>";
 
                    $i++;
                    $picture_rs->moveNext();
                }
                $seqno_rs->moveNext();
            }
        }
        $order_detail_rs->moveNext();
    }
} 

$pic_html = <<<HTML
        <section class="mainBanner">
        <nav>
          <button class="prev">
              <img style="width:30px;" src="/design_template/images/mainbanner_nav_prev.png" alt="<">
          </button>
          <button class="next">
              <img style="width:30px;" src="/design_template/images/mainbanner_nav_next.png" alt=">">
          </button>
        <ul style="display:none;"></ul>
        </nav>

        <ul class="list">
            $slider_html
        </ul>
        </section>
HTML;

$html_param["pic"] = $pic_html; 

//후공정 발주 작업파일
$param = array();
$param["table"] = "after_op_work_file";
$param["col"] = "preview_file_path, preview_file_name";
$param["where"]["after_op_seqno"] = $seqno;

$picture_rs = $dao->selectData($conn, $param);

$html_param["after_pic"] = "";
while ($picture_rs && !$picture_rs->EOF) {
    $file_path = $picture_rs->fields["preview_file_path"];
    $file_name = $picture_rs->fields["preview_file_name"];

    $full_path = $file_path . $file_name;
    $chk_path = INC_PATH . $full_path;

    if (is_file($chk_path) === false) {
        $full_path = NO_IMAGE;
        $html_param["after_pic"] = "<img src=\"" . $full_path . "\" width=\"420px\" height=\"300px\">"; 
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

        $html_param["after_pic"] .= "<img src=\"" . $full_path . "\" width=\"420px\" height=\"300px\">"; 
    }
    $picture_rs->moveNext();
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
$param["table"] = "after_work_report";
$param["col"] = "worker_memo ,work_start_hour ,work_end_hour ,valid_yn, worker, state";
$param["where"]["after_op_seqno"] = $seqno;

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

    if ($state == "입고대기") {
        $state = "후공정완료";
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
*/

$param = array();
$param["table"] = "after_work_report";
$param["col"] = "worker_memo ,work_start_hour ,work_end_hour ,adjust_price, worker";
$param["where"]["after_op_seqno"] = $seqno;
$param["where"]["valid_yn"] = "Y";

$rs = $dao->selectData($conn, $param);

if ($rs->fields["worker"]) {
    $html_param["worker"] = $rs->fields["worker"]; 
} else {
    $html_param["worker"] = $fb->session("name");
}

if (!$memo_select_html) {
    $html_param["memo_select_html"] = $rs->fields["worker_memo"]; 
}
$html_param["work_start_hour"] = $rs->fields["work_start_hour"]; 
if ($rs->fields["work_end_hour"]) {
    $html_param["work_end_hour"] = " ~ " . $rs->fields["work_end_hour"]; 
}

if ($work_yn == "Y" && !$rs->fields["work_start_hour"]) {
    $html_param["work_start_hour"] = "알수없음";
}

$html_param["adjust_price"] = number_format(intVal($rs->fields["adjust_price"])); 

echo getAfterDetailPopup($html_param); 
$conn->close();
?>
