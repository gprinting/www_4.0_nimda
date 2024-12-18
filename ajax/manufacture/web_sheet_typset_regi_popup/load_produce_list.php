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

$paper_yn = "사용안함";
if ($rs->fields["paper_yn"] == "Y") {
    $paper_yn = "사용";
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
$param["table"] = "sheet_typset";
$param["col"] = "sheet_typset_seqno";
$param["where"]["typset_num"] = $typset_num;

$sheet_typset_seqno = $dao->selectData($conn, $param)->fields["sheet_typset_seqno"];

$param = array();
$param["table"] = "sheet_typset_file";
$param["col"] = "sheet_typset_file_seqno";
$param["where"]["sheet_typset_seqno"] = $sheet_typset_seqno;

$file_save_yn = $dao->selectData($conn, $param)->fields["sheet_typset_file_seqno"];

if ($file_save_yn == "Y") {
    $file_save_yn = "저장";
} else {
    $file_save_yn = "저장안함";
}

$html = <<<HTML
<tr>
  <td>조판</td>
  <td>-</td>
  <td>$typset_save_yn</td>
  <td rowspan="3">$file_save_yn</td>
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

echo $html;
$conn->close();
?>
