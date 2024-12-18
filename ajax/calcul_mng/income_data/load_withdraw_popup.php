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

$input_dvs = $fb->form("input_dvs");

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

$rs = $dao->selectTransactionalInfoList($conn, $param);
$i = 0;

while($rs && !$rs->EOF) {
    $depo_way = $rs->fields['depo_way'];
    $depo_price = $rs->fields['depo_price'];
    $card_depo_price = $rs->fields['card_depo_price'];
    $adjust_price = $rs->fields['adjust_price'];
    $sell_price = $rs->fields['sell_price'];
    $deal_date = $rs->fields['deal_date'];
    $deal_num = $rs->fields['deal_num'];
    $aprvl_num = $rs->fields['aprvl_num'];
    $card_cpn = $rs->fields['card_cpn'];
    $card_num = $rs->fields['card_num'];
    $dvs = $rs->fields['dvs'];
    $input_dvs = $rs->fields['input_dvs'];
    $input_dvs_detail = $rs->fields['input_dvs_detail'];
    $cont = $rs->fields['cont'];
    $deposit_dvs = $rs->fields['deposit_dvs'];
    $card_pay_price = $rs->fields['card_pay_price'];

    if($card_depo_price != 0) {
        $path = "선입금 카드결제";
        $input_dvs = "카드";
    }
    if($depo_price != 0) $path = "가상계좌 입금";
    if($deposit_dvs == "cash") $path = "현금 입금(수기)";
    if($adjust_price != 0) $path = "";
    $depo_price += $card_depo_price;
    $price = "";

    if($dvs == "배송비" && $sell_price == 0) {

    } else {
        if($depo_price != 0) {
            $price = ($adjust_price > 0 ? "":"") . number_format($depo_price) . "원";
        }

        if($adjust_price != 0) {
            $price = ($adjust_price > 0 ? "":"") . number_format($adjust_price) . "원";
            $path = $input_dvs . "-" . $input_dvs_detail . "(" . $cont . ")";
        }

        if($sell_price != 0) {
            $price = number_format($sell_price) . "원";
            $path = "건별 카드결제";
        }

        if($dvs == "배송비") {
            $path = "";
        }

        $dvs_class = "clsss cls_";
        if($deposit_dvs != null) {
            $dvs_class .= $deposit_dvs;
        } else {
            if($path == "가상계좌 입금") {
                $dvs_class .= "bank";
            } else if($card_pay_price != 0) {
                $dvs_class .= "card";
            } else {
                $dvs_class .= "etc";
            }
        }

        $html_param .= <<<HTML
            <tr class="$dvs_class">
                <td>$deal_date</td>
                <td>$dvs</td>
                <td>$price</td>
                <td>$path</td>
                <td>$card_cpn</td>
                <td>$card_num</td>
                <td>$aprvl_num</td>
            </tr>
HTML;
    }
    $rs->MoveNext();
}

echo getWithdrawPopup($html_param);

$conn->close();
?>
