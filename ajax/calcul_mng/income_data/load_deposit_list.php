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
$param["dvs"] = $fb->form("dvs");
if($param["dvs"] == "all") $param["dvs"] = null;

$rs = $dao->selectMemberDepositList($conn, $param);
$i = 0;
$all_depo_price = 0;

while($rs && !$rs->EOF) {
    $deal_date = str_replace("01:00:00", "", $rs->fields['deal_date']);
    $dvs = $rs->fields['dvs'];
    $depo_price = number_format($rs->fields['depo_price'] + $rs->fields['card_depo_price']) . "원";
    //$card_depo_price = $rs->fields['card_depo_price'];
    $cont = $rs->fields['cont'];
    $deal_num = $rs->fields['deal_num'];
    $member_name = $rs->fields['member_name'];
    $card_num = $rs->fields['card_num'];
    $card_approve_num = $rs->fields['card_approve_num'];
    $card_approve_date = $rs->fields['card_approve_date'];
    $card_kind = $rs->fields['card_kind'];
    $deposit_dvs = $rs->fields['deposit_dvs'];
    $card_inst_months = $rs->fields['card_inst_months'];
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
                <td>$cont</td>
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

$html_param = <<<HTML
            <tr>
                <th>입금일자</th>
                <th>입금금액</th>
                <th>입금경로</th>
                <th>입금자</th>
                <th>카드 번호</th>
                <th>할부개월</th>
                <th>승인번호</th>
                <th>담당자</th>
            </tr>
HTML . $html_param;

echo $html_param;

$conn->close();
?>