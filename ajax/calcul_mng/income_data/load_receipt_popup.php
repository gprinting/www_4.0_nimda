<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/tab/SalesTabListDAO.inc");
include_once(INC_PATH . '/com/nexmotion/doc/nimda/calcul_mng/income_data/WithdrawPopupDOC.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new SalesTabListDAO();
$util = new CommonUtil();
$session = $fb->getSession();

$seqno = $fb->form("member_seqno");
$dvs = $fb->form("dvs");
$state = $fb->form("state");
$year = $fb->form("year");
$month = $fb->form("month");
$dvs_detail = $fb->form("dvs_detail");
$kind = $fb->form("kind");

//상세보기 출력 발주
$param = array();
$param["member_seqno"] = $seqno;
$param["year"] = $year;
$param["month"] = $month;
$param["dvs_detail"] = $dvs_detail;
$param["state"] = $state;
$param["kind"] = $kind;
$param["dvs"] = "SEQ";

$rs = $dao->selectCashreceiptListBycase($conn, $param);
$i = 0;

while($rs && !$rs->EOF) {
    $i++;
    $deal_date = $rs->fields['deal_date'];
    $sell_channel = $rs->fields['sell_channel'];
    $corp_name = $rs->fields['corp_name'] == "" ? "미등록" : $rs->fields['corp_name'];
    $member_name = $rs->fields['member_name'];
    $pay_price = $rs->fields['pay_price'] + $rs->fields['card_pay_price'];
    $adjust_price = $rs->fields['adjust_price'];
    $order_num = $rs->fields['order_num'];
    $pure_price = $pay_price - $adjust_price;
    $tel_num = $rs->fields['tel_num'];
    $dvs_detail = $rs->fields['dvs_detail'];
    $tax_price = $rs->fields['tax_price'];

    $change_state = ($rs->fields['public_state'] == "대기" ? "완료" : "대기");
    $change_button = ($rs->fields['public_state'] == "대기" ? "발행완료" : "발행취소");

    $html_param .= <<<HTML
            <tr>
                <td>$i</td>
                <td>$sell_channel</td>
                <td>$member_name</td>
                <td>$dvs_detail</td>
                <td>$corp_name</td>
                <td>$tel_num</td>
                <td>$order_num</td>
                <td>$pay_price</td>
                <td>$adjust_price</td>
                <td>$pure_price</td>
                <td>$pure_price</td>
                <td><select id='dvs_detail_$order_num' name='dvs_detail' class='fix_width100'>
                        <option value='세금계산서'>세금계산서</option>
                        <option value='지출증빙'>지출증빙</option>
                        <option value='소득공제'>소득공제</option>
                        <option value='미발행'>미발행</option>
                    </select>
                    <button type="button" onclick="ChangeDvsPeriodByCase('$order_num');" class="btn_Turquoise01 btn_pu fix_height20 fix_width60">
                        변경
                    </button>
                </td>
                <td><button class="btn_Turquoise01" onclick="receipt_issue('$order_num','$change_state','$kind');">$change_button</button></td>
            </tr>
HTML;
    $rs->MoveNext();
}

echo getReceptCashPopup($html_param);

$conn->close();
?>
