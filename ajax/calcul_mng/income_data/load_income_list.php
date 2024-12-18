<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/calcul_mng/settle/IncomeDataDAO.inc');
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/pageLib.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$incomeDAO = new IncomeDataDAO();

//페이징
$list_num = $fb->form("list_num"); //한페이지에 출력할 게시물 개수
if (!$fb->form("list_num")) $list_num = 30;

$scrnum = 5; //블록 개수
$page = $fb->form("page");

if (!$page) $page = 1; // 페이지가 없으면 1 페이지

$param = array();

$channel = $fb->form("sell_site") == 1 ? "GP" : "DP";
$channel = "";
if($fb->form("sell_site") == 1)
    $channel = "GP";

if($fb->form("sell_site") == 2)
    $channel = "DP";
//판매채널 일련번호
$param["cpn_admin_seqno"] = $channel;
//회원 일련번호
$param["member_seqno"] = $fb->form("member_seqno");
//입출금경로 일련번호
$param["depo_path"] = $fb->form("path");
//입출금경로상세 일련번호
$param["depo_path_detail"] = $fb->form("path_detail");
//등록 시작 일자
$param["date_from"] = $fb->form("date_from");
//등록 종료 일자
$param["date_to"] = $fb->form("date_to");

$param["show_day"] = $fb->form("show_day");
$param["dlvr_dvs"] = $fb->form("dlvr_dvs");

//페이징
$param["start"] = $list_num * ($page-1);
$param["end"] = $list_num;
//수입 리스트
$param["dvs"] = "SEQ";
$result = $incomeDAO->selectIncomeList($conn, $param);
$param["dvs"] = "COUNT";
$count_rs = $incomeDAO->selectIncomeList($conn, $param);

$param["dvs"] = "SUM";
$sum_rs = $incomeDAO->selectIncomeList($conn, $param);
$total_count = $count_rs->recordCount(); //페이징할 총 글수

$ret = "";
$ret = mkDotAjaxPage($total_count, $page, $scrnum, $list_num, "searchResult");

//수입 테이블 그리기
$list = "";
$list = makeIncomeList($conn, $incomeDAO,$param,  $result, $list_num * ($page-1));

$param["sum_dvs"] = "";

$cash = "0";
$param["sum_dvs"] = "현금";
$result = $incomeDAO->selectIncomeSumPrice($conn, $param);
//현금합계가 있을때
if ($result->fields["income"] || $result->fields["trsf_income"]) {
    $cash = $result->fields["income"] + $result->fields["trsf_income"];

}

$bankbook = "0";
$param["sum_dvs"] = "가상계좌";
$result = $incomeDAO->selectIncomeSumPrice($conn, $param);
//가상계좌(통장) 합계가 있을때
if ($result->fields["income"] || $result->fields["trsf_income"]) {

    $bankbook = $result->fields["income"] + $result->fields["trsf_income"];

}

/*
$card = "0";
$param["sum_dvs"] = "카드";
$result = $incomeDAO->selectIncomeSumPrice($conn, $param);
//가상계좌(통장) 합계가 있을때
if ($result->fields["income"] || $result->fields["trsf_income"]) {

    $card = $result->fields["income"] + $result->fields["trsf_income"];

}
*/

$etc = "0";
$param["sum_dvs"] = "기타";
$result = $incomeDAO->selectIncomeSumPrice($conn, $param);
//기타 합계가 있을때
if ($result->fields["income"] || $result->fields["trsf_income"]) {

    $etc = $result->fields["income"] + $result->fields["trsf_income"];

}

// 입금총액
$result = $incomeDAO->selectWithDrawSum($conn, $param);
$bankbook = $result->fields['depo_price'];
$card_depo = $result->fields['card_depo_price'];
$result = $incomeDAO->selectSellPriceSum($conn, $param);
$depo_sum = $bankbook + $card_depo + $result->fields['card_pay_price'];
$sell_sum = $result->fields['pay_price'] + $result->fields['card_pay_price'];
$card = $result->fields['card_pay_price'];

$result = $incomeDAO->selectDeliveryPriceSum($conn, $param);
$delivery_sum = $result->fields['pay_price'];

$result = $incomeDAO->selectAdjustPriceSum($conn, $param);
$adjust_sum = $sum_rs->fields['enuri'];

//$sales_sum = $sell_sum + $delivery_sum - $adjust_sum;// + $delivery_sum;
$sales_sum = $sum_rs->fields["pay_price"] + $sum_rs->fields["card_pay_price"] - $sum_rs->fields["adjust_sales"];
$adjust_price = $adjust_sum;
$result_price = $sales_sum - $adjust_sum;

echo $list . "♪♭@" . $ret . "♪♭@" . number_format($cash) . "원♪♭@" . 
     number_format($bankbook) . "원♪♭@" .  number_format($card_depo) . "원♪♭@" .
     number_format($card) . "원♪♭@" . number_format($result_price) . "원♪♭@" .
    number_format($depo_sum) . "원♪♭@" . number_format($sales_sum) . "원♪♭@" .
    number_format($adjust_price) . "원♪♭@";

$conn->close();


?>
