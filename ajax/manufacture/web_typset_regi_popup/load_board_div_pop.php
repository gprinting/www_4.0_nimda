<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();

$order_detail_dvs_num = $fb->form("order_detail_dvs_num");
$state_arr = $fb->session("state_arr");

$state = $state_arr["조판중"];

$param = array();
$param["table"] = "order_detail_brochure";
$param["col"] = "page_amt";
$param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;

$total = $dao->selectData($conn, $param)->fields["page_amt"];

$param = array();
$param["table"] = "page_order_detail_brochure";
$param["col"] = "page";
$param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;

$rs = $dao->selectData($conn, $param);

$add_sheet_btn_html = <<<HTML
	<div class="pop-base tac">
		   <button type="button" class="btn  btn-info fwb nanum" onclick="orderFn.add();">분판 추가</button>
	</div>
HTML;

$save_btn_html = <<<HTML
	<div class="pop-base tac">
		   <button type="button" class="btn  btn-primary fwb nanum" onclick="orderFn.save('$order_detail_dvs_num', '$total'); return false;"> 저장</button>
	</div>
HTML;

$notice_html = "<label style=\"font-size: 10px;color: red;\">*분판 된 판중 일부가 작업이 시작되어 분판 수정이 불가능 합니다.</label>";

$html = "";
$param = array();
$i = 1;
while ($rs && !$rs->EOF) {

    $param["num"] = $i;
    $param["val"] = $rs->fields["page"];

    $html .= addDivide($param);
    $notice_html = "";

    $i++;
    $rs->moveNext();
}

$param = array();
$param["order_detail_dvs_num"] = $order_detail_dvs_num;
$param["total"] = $total;
$param["html"] = $html;
$param["add_sheet_btn_html"] = $add_sheet_btn_html;
$param["save_btn_html"] = $save_btn_html;
$param["notice_html"] = $notice_html;
$param["num"] = intVal($i) - 1;
$param["idx"] = "div_idx";

echo getDivideTypsetPopup($param);
$conn->close();
?>
