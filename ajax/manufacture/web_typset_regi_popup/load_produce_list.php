<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();

$typset_num = $fb->form("typset_num");

$param = array();
$param["table"] = "produce_process_flow";
$param["col"] = "paper_yn ,output_yn ,print_yn 
,typset_save_yn ,output_save_yn ,print_save_yn";
$param["where"]["typset_num"] = $typset_num;

$rs = $dao->selectData($conn, $param);

$typset_save_yn = "저장안함";
if ($rs->fields["typset_save_yn"] == "Y") {
    $typset_save_yn = "저장";
}

$output_yn = "사용안함";
if ($rs->fields["output_yn"] == "Y") {
    $output_yn = "사용";
}

$output_save_yn = "저장안함";
if ($rs->fields["output_save_yn"] == "Y") {
    $output_save_yn = "저장";
}

$paper_yn = "사용안함";
if ($rs->fields["paper_yn"] == "Y") {
    $paper_yn = "사용";
}

$print_yn = "사용안함";
if ($rs->fields["print_yn"] == "Y") {
    $print_yn = "사용";
}

$print_save_yn = "저장안함";
if ($rs->fields["print_save_yn"] == "Y") {
    $print_save_yn = "저장";
}

$param = array();
$param["table"] = "brochure_typset";
$param["col"] = "brochure_typset_seqno";
$param["where"]["typset_num"] = $typset_num;

$brochure_typset_seqno = $dao->selectData($conn, $param)->fields["brochure_typset_seqno"];

$param = array();
$param["table"] = "page_order_detail_brochure";
$param["col"] = "order_detail_dvs_num";
$param["where"]["brochure_typset_seqno"] = $brochure_typset_seqno;

$order_detail_dvs_num = $dao->selectData($conn, $param)->fields["order_detail_dvs_num"];

$param = array();
$param["table"] = "after_op";
$param["col"] = "after_op_seqno, order_common_seqno";
$param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;

$sel_rs = $dao->selectData($conn, $param);

$after_op_seqno = $sel_rs->fields["after_op_seqno"];
$order_common_seqno = $sel_rs->fields["order_common_seqno"];

$param = array();
$param["table"] = "brochure_work_file";
$param["col"] = "brochure_work_file_seqno";
$param["where"]["order_common_seqno"] = $order_common_seqno;

$brochure_work_file_seqno = $dao->selectData($conn, $param)->fields["brochure_work_file_seqno"];

$work_file_yn = "저장안함";
if ($brochure_work_file_seqno) {
    $work_file_yn = "저장";
}

$param = array();
$param["table"] = "after_op_work_file";
$param["col"] = "after_op_work_file_seqno";
$param["where"]["after_op_seqno"] = $after_op_seqno;

$after_op_work_file_seqno = $dao->selectData($conn, $param)->fields["after_op_work_file_seqno"];

$after_file_yn = "저장안함";
if ($after_op_work_file_seqno) {
    $after_file_yn = "저장";
}

$param = array();
$param["table"] = "brochure_typset_file";
$param["col"] = "brochure_typset_file_seqno";
$param["where"]["brochure_typset_seqno"] = $brochure_typset_seqno;

$brochure_typset_file_seqno = $dao->selectData($conn, $param)->fields["brochure_typset_file_seqno"];

$typset_file_yn = "저장안함";
if ($brochure_typset_file_seqno) {
    $typset_file_yn = "저장";
}

$html = <<<HTML
<tr>
  <td>조판</td>
  <td>-</td>
  <td>$typset_save_yn</td>
  <td rowspan="3">$work_file_yn</td>
  <td rowspan="3">$after_file_yn</td>
  <td rowspan="3">$typset_file_yn</td>
</tr>
<!--tr class="cellbg">
  <td>종이</td>
  <td>$paper_yn</td>
  <td>-</td>
</tr-->
<tr class="cellbg">
  <td>출력</td>
  <td>$output_yn</td>
  <td>$output_save_yn</td>
</tr>
<tr>
  <td>인쇄</td>
  <td>$print_yn</td>
  <td>$print_save_yn</td>
</tr>
HTML;

Echo $html;
$conn->close();
?>
