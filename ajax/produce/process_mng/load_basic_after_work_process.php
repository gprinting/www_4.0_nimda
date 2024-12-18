<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/Template.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/ProcessMngDAO.inc");
include_once(INC_PATH . "/common_define/order_status.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$template = new Template();
$dao = new ProcessMngDAO();

if (!$fb->form("seqno")) {
    echo "<script>alert('잘못된 접근입니다.');window.close();</script>";
    exit;
}

$seqno = $fb->form("seqno");
$option_html = "<option value=\"%s\" %s>%s</option>";

//상세보기 후공정 발주
$param = array();
$param["seqno"] = $seqno;

$rs = $dao->selectAfterProcessView($conn, $param);

$html_param = array();
$html_param["after_op_seqno"] = $rs->fields["after_op_seqno"]; 
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

$dvs = "";
if ($rs->fields["state"] == OrderStatus::STATUS_PROC["후공정"]["대기"]) {
    $dvs = "대기";
} else if ($rs->fields["state"] == OrderStatus::STATUS_PROC["후공정"]["중"]) {
    $dvs = "중";
} else {
    $dvs = "완료";
}

$order_common_seqno = $rs->fields["order_common_seqno"];
$extnl_etprs_seqno = $rs->fields["extnl_etprs_seqno"];
$extnl_brand_seqno = $rs->fields["extnl_brand_seqno"];
$amt = $rs->fields["amt"];
$flattyp_dvs = $rs->fields["flattyp_dvs"];
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

//옵션 목록
$param = array();
$param["table"] = "order_opt_history";
$param["col"] = "opt_name";
$param["where"]["order_common_seqno"] = $order_common_seqno;

$opt_rs = $dao->selectData($conn, $param);

$opt_list = "";
while ($opt_rs && !$opt_rs->EOF) {

    $opt_list .= "," . $opt_rs->fields["opt_name"];
    $opt_rs->moveNext();
}

$html_param["opt_list"] = substr($opt_list, 1); 

$slider_html = "";
$slider_html2 = "";

//주문 상세 일련번호
$param = array();
$param["table"] = "order_detail";
$param["col"] = "order_detail_seqno";
$param["where"]["order_common_seqno"] = $order_common_seqno;

$order_detail_rs = $dao->selectData($conn, $param);

while ($order_detail_rs && !$order_detail_rs->EOF) {

    if ($order_detail_rs->fields["order_detail_seqno"]) {
        //주문상세파일
        $param = array();
        $param["table"] = "order_detail_count_file";
        $param["col"] = "file_path, save_file_name, origin_file_name";
        $param["where"]["order_detail_seqno"] = $order_detail_rs->fields["order_detail_seqno"];

        $picture_rs = $dao->selectData($conn, $param);

        $file_path = $picture_rs->fields["file_path"];
        $file_name = $picture_rs->fields["save_file_name"];
        $origin_file_name = $picture_rs->fields["origin_file_name"];

        $full_path = $file_path . $file_name;
        $chk_path = str_replace(INC_PATH, "", $full_path);
        if (is_file($full_path) === false) {
            $full_path = NO_IMAGE;
        }

        $slider_html .= "\n<li><img src=\"" . $chk_path . "\" style=\"max-width:340px;max-height:200px;\" alt=\"" . $origin_file_name . "\"></li>";
    }

    $order_detail_rs->moveNext();
}

$pic_html = <<<HTML
        <ul class="list" style="position: absolute;left: 555px;">
            $slider_html
        </ul>
        <nav>
            <!--<ul></ul>-->
            <button class="prev"><img style="width:35px;" src="/design_template/images/mainbanner_nav_prev.png" alt="<"></button>
            <button class="next"><img style="width:35px;" src="/design_template/images/mainbanner_nav_next.png" alt=">"></button>
        </nav>
HTML;

$html_param["pic"] = $pic_html; 

//후공정 발주 작업파일
$param = array();
$param["table"] = "after_op_work_file";
$param["col"] = "file_path, save_file_name";
$param["where"]["after_op_seqno"] = $seqno;

$picture_rs = $dao->selectData($conn, $param);

$file_path = $picture_rs->fields["file_path"];
$file_name = $picture_rs->fields["save_file_name"];

$full_path = $file_path . $file_name;
$chk_path = INC_PATH . $full_path;

if (is_file($chk_path) === false) {
    $full_path = NO_IMAGE;
}

$html_param["after_pic"] = $full_path; 

$param = array();
$param["table"] = "after_work_report";
$param["col"] = "worker_memo ,work_start_hour ,work_end_hour ,adjust_price, worker";
$param["where"]["after_op_seqno"] = $seqno;
$param["where"]["valid_yn"] = "Y";

$rs = $dao->selectData($conn, $param);

$html_param["worker"] = $rs->fields["worker"]; 
$html_param["worker_memo"] = $rs->fields["worker_memo"]; 
$html_param["work_start_hour"] = $rs->fields["work_start_hour"]; 
if ($rs->fields["work_end_hour"]) {
    $html_param["work_end_hour"] = " ~ " . $rs->fields["work_end_hour"]; 
}
$html_param["adjust_price"] = number_format(intVal($rs->fields["adjust_price"])); 

$disabled = "";
$btn_html = <<<HTML
    <label class="btn btn_md fix_width120" onclick="getStart('$seqno');"> 후공정시작</label>
HTML;

$btn_html2 = <<<HTML
    <label class="btn btn_md fix_width120" onclick="windowClose();"> 닫기</label>
	<label class="fix_width20">  </label>
	<label class="btn btn_md fix_width120" onclick="getCancel('$seqno');"> 작업취소</label>
HTML;

if ($dvs == "완료") {
    $btn_html = <<<HTML
        <label class="btn btn_md fix_width120" onclick="getRestart('$seqno');">후공정 재시작</label>
HTML;

    $btn_html2 = <<<HTML
        <label class="btn btn_md fix_width120" onclick="windowClose();"> 닫기</label>
HTML;

} else if ($dvs == "중"){

    $disabled = "disabled=\"disabled\"";
    $btn_html = <<<HTML
        <label class="btn btn_md fix_width120" onclick="getFinish('$seqno');"> 후공정완료</label>
HTML;
}

$html_param["disabled"] = $disabled; 
$html_param["disabled_class"] = $disabled_class; 
$html_param["btn_html"] = $btn_html; 
$html_param["btn_html2"] = $btn_html2; 

echo afterProcessAddPopup($html_param); 
$conn->close();
?>
