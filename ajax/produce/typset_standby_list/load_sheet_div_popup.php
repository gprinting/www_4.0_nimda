<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/TypsetStandbyListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetStandbyListDAO();

$param = array();
$param["table"] = "amt_order_detail_sheet";
$param["col"] = "amt, state";
$param["where"]["order_detail_count_file_seqno"] = $fb->form("seqno");

$rs = $dao->selectData($conn, $param);
$i = 1;

$flag = "true";
while ($rs && !$rs->EOF) {
    if ($rs->fields["state"] != '2120') {
        $flag = "flase";
    }

    $rs->moveNext();
}

$rs->moveFirst();

$seqno = $fb->form("seqno");
$total = $fb->form("total");

$add_sheet_btn_html = <<<HTML
														       <div class="pop-base tac">
														       	   <button type="button" class="btn  btn-info fwb nanum" onclick="addDivide('flatt_y');">분판 추가</button>
														       </div>
HTML;

$save_btn_html = <<<HTML
														       <div class="pop-base tac">
														       	   <button type="button" class="btn  btn-primary fwb nanum" onclick="saveDivide('$seqno', '$total', 'flatt_y')"> 저장</button>
														       </div>
HTML;

$notice_html = "<label style=\"font-size: 10px;color: red;\">*분판 된 판중 일부가 작업이 시작되어 분판 수정이 불가능 합니다.</label>";

$html = "";
$param = array();
while ($rs && !$rs->EOF) {

    $param["num"] = $i;
    $param["val"] = $rs->fields["amt"];
    $param["type"] = "flatt_y";

    if ($flag == "true") { 
        $html .= addDivide($param);
        $notice_html = "";
    } else {
        $html .= addDivideDisabled($param);
        $add_sheet_btn_html = "";
        $save_btn_html = "";
    }

    $i++;
    $rs->moveNext();
}

$param = array();
$param["seqno"] = $fb->form("seqno");
$param["total"] = $fb->form("total");
$param["html"] = $html;
$param["add_sheet_btn_html"] = $add_sheet_btn_html;
$param["save_btn_html"] = $save_btn_html;
$param["notice_html"] = $notice_html;
$param["num"] = intVal($i) - 1;
$param["idx"] = "sheetIdx";
$param["type"] = "flatt_y";

echo getDivideTypsetPopup($param);
$conn->close();
?>
