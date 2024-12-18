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

$param["member_seqno"] = $fb->form("member_seqno");
$param["date_from"] = $fb->form("from");
$param["date_to"] = $fb->form("to");

$member_name = $fb->form("member_name");
$rs = $dao->selectMemberDepositList($conn, $param);
$i = 0;
$all_depo_price = 0;

while($rs && !$rs->EOF) {
    if($rs->fields['adjust_date'] == null) {
        $deal_date = str_replace("01:00:00", "", $rs->fields['deal_date']);
    } else {
        $deal_date = str_replace("01:00:00", "", $rs->fields['adjust_date']);
    }

    $dvs = $rs->fields['dvs'];
    $cont = $rs->fields['cont'];
    $deal_num = $rs->fields['deal_num'];
    $deposit_dvs = $rs->fields['deposit_dvs'];
    $cash_dvs = $rs->fields['cash_dvs'];
    $card_kind = $rs->fields['card_kind'];
    $card_inst_months = $rs->fields['card_inst_months'];
    $card_num = $rs->fields['card_num'];
    $card_approve_num = $rs->fields['card_approve_num'];
    $card_approve_date = $rs->fields['card_approve_date'];
    $card_member = $rs->fields['card_member'];
    $etc_dvs = $rs->fields['etc_dvs'];
    $member_name = $rs->fields['member_name'];
    $depo_price = number_format($rs->fields['depo_price'] + $rs->fields['card_depo_price']) . "원";
    //$card_depo_price = $rs->fields['card_depo_price'];
    $cont = $rs->fields['cont'];
    $all_depo_price += ($rs->fields['depo_price'] + $rs->fields['card_depo_price']);

    if($deposit_dvs == "bank") {
        $cont = "[가상계좌]" . $cont;
    }

    if($deposit_dvs == "card") {
        $cont = "[카드(단말기)]" . $cont;
    }

    if($deposit_dvs == "cash") {
        $cont = "[현금]" . $cont;
    }

    if($deposit_dvs == "etc") {
        $cont = "[기타]" . $cont;
    }

    $deposit_dvs = "clsss cls_" . $deposit_dvs;
    $html_param .= <<<HTML
            <tr class="$deposit_dvs">
                <td>$deal_date</td>
                <td>$depo_price</td>
                <td style="width:200px;">$cont</td>
                <td>$member_name</td>
                <td>$card_num</td>
                <td>$card_inst_months</td>
                <td>$card_approve_num</td>
                <td></td>
            </tr>
HTML;

    $rs->MoveNext();
}

$all_depo_price = number_format($all_depo_price) . "원";
$html_param = <<<HTML
            <tr>
                <td>합계</td>
                <td>$all_depo_price</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
HTML . $html_param;

echo getDepositPopup($html_param);

$conn->close();
?>