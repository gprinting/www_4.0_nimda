<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/settle/IncomeDataDAO.inc");
include_once(INC_PATH . '/com/nexmotion/doc/nimda/calcul_mng/income_data/WithdrawPopupDOC.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new IncomeDataDAO();
$util = new CommonUtil();
$session = $fb->getSession();

$seqno = $fb->form("member_seqno");
$from_date = $fb->form("from_date");
$to_date = $fb->form("to_date");
$option_html = "<option value=\"%s\" %s>%s</option>";
$state_arr = $session["state_arr"];
//상세보기 출력 발주
$param = array();
$param["member_seqno"] = $seqno;
$param["from_date"] = $from_date;
$param["to_date"] = $to_date;

//$rs = $dao->selectWithDraw($conn, $param);

$param = array();
$param["member_seqno"] = $seqno;
$param["from"] = $from_date;
$param["to"] = $to_date;
$param["order_state"] = $state_arr["주문취소"];
$rs = $dao->selectTotalDayList($conn, $param);
$i = 0;

while($rs && !$rs->EOF) {
    $depo_price = $rs->fields['depo_price'];
    $card_depo_price = $rs->fields['card_depo_price'];
    $pay_price = $rs->fields['pay_price'];
    $card_pay_price = $rs->fields['card_pay_price'];
    $adjust_price = $rs->fields['adjust_price'];
    $member_seqno = $rs->fields['member_seqno'];
    $deal_date = explode(' ',$rs->fields['deal_date'])[0];

    if($adjust_price < 0) {
        $pay_price -= $adjust_price;
        $adjust_price = 0;
    }

    $pay_price = "" . number_format($pay_price + $card_pay_price) . "원";
    $depo_price = "" . number_format($depo_price) . "원";
    $card_depo_price = "" . number_format($card_depo_price) . "원";
    $adjust_price = "" . number_format($adjust_price) . "원";

    $html_param .= <<<HTML
    <tr>
        <td onclick="showWithdraw('$member_seqno','$deal_date','$deal_date');">$deal_date</td>
        <td>$pay_price</td>
        <td>$adjust_price</td>
        <td>$depo_price</td>
        <td>$card_depo_price</td>
        </tr>
HTML;
    $rs->MoveNext();
}

echo getTotalDayPopup($html_param);
$conn->close();
?>
