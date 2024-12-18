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

$rs = $dao->selectAfterProcessView($conn, $param);

//낱장형(S) / 책자형(B) 여부
$flattyp_dvs = substr($rs->fields["order_detail_dvs_num"], 0, 1);

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

//낱장
if ($flattyp_dvs === "S") {
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
            //     $param["col"] = "file_path, save_file_name, origin_file_name";
            $param["col"] = "preview_file_path, preview_save_file_name, preview_origin_file_name";
            $param["where"]["order_detail_seqno"] = $order_detail_rs->fields["order_detail_seqno"];

            $picture_rs = $dao->selectData($conn, $param);

            while ($picture_rs && !$picture_rs->EOF) {

                $file_path = $picture_rs->fields["preview_file_path"];
                $file_name = $picture_rs->fields["preview_save_file_name"];
                $origin_file_name = $picture_rs->fields["preview_origin_file_name"];

                $full_path = $file_path . $file_name;
                $chk_path = str_replace(INC_PATH, "", $full_path);
                if (is_file($full_path) === false) {
                    $full_path = NO_IMAGE;
                }

                $slider_html .= "\n<li><img src=\"" . $chk_path . "\" style=\"max-width:250px;max-height:200px;\" alt=\"" . $origin_file_name . "\"></li>";

                $picture_rs->moveNext();
            }
        }

        $order_detail_rs->moveNext();
    }
} 

$pic_html = <<<HTML
        <ul class="list">
            $slider_html
        </ul>
        <nav>
          <button class="prev"><img style="width:30px;" src="/design_template/images/mainbanner_nav_prev.png" alt="<"></button>
          <button class="next"><img style="width:30px;" src="/design_template/images/mainbanner_nav_next.png" alt=">"></button>
        <ul style="display:none;"></ul>
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

echo afterBarcode($html_param); 
$conn->close();
?>
