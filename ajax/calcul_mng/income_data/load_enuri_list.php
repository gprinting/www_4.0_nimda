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

$rs = $dao->selectMemberEnuriList($conn, $param);
$i = 0;
$all_depo_price = 0;
while($rs && !$rs->EOF) {
    $deal_date = str_replace("01:00:00", "", $rs->fields['deal_date']);
    $dvs = $rs->fields['dvs'];
    $depo_price = number_format($rs->fields['adjust_price']) . "원";
    $all_depo_price += $rs->fields['adjust_price'];
    //$card_depo_price = $rs->fields['card_depo_price'];
    $cont = $rs->fields['cont'];
    $order_num = $rs->fields['order_num'];

    $html_param .= <<<HTML
            <tr>
                <td>$deal_date</td>
                <td>$order_num</td>
                <td>$depo_price</td>
                <td>$cont</td>
            </tr>
HTML;

    $rs->MoveNext();
}

$all_depo_price = number_format($all_depo_price);
$html_param = <<<HTML
            <tr>
                <td>합계</td>
                <td></td>
                <td>$all_depo_price</td>
                <td></td>
            </tr>
HTML . $html_param;

$html_param = <<<HTML
            <tr>
                <th>일자</th>
                <th>주문번호</th>
                <th>에누리 금액</th>
                <th>내용</th>
            </tr>
HTML . $html_param;


echo $html_param;

$conn->close();
?>