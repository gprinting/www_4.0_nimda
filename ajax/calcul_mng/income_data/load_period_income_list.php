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
//등록 시작 일자
$param["date_from"] = $fb->form("date_from");
//등록 종료 일자
$param["date_to"] = $fb->form("date_to");
//수입 리스트
$info = $incomeDAO->selectPeriodIncomeSum($conn, $param);
//$count_rs = $incomeDAO->countPeriodIncomeList($conn, $param);
//$total_count = $count_rs->fields["sum"]; //페이징할 총 글수

$info2 = $incomeDAO->selectMemberInfo($conn, $param);
$info3 = $incomeDAO->selectByWeekIncomeList($conn, $param);
$info4 = $incomeDAO->selectByDayIncomeList($conn, $param);
//$info41 = $incomeDAO->selectByDayIncomeList2($conn, $param);
$info5 = $incomeDAO->selectByMonthIncomeList($conn, $param);
$info6 = $incomeDAO->selectByYearIncomeList($conn, $param);


$memo = $incomeDAO->selectMemberMemo($conn, $param);

$id = $info2->fields["id"];
$prepay_rs     = $incomeDAO->selectPrepayPrice($conn, $id);
$prepay_Sql     = $incomeDAO->selectPrepayPriceSql($conn, $id);
$fields = $prepay_rs->fields;
$prepay_price  = intval($fields[0]);

//수입 테이블 그리기
$list = "";
$list = makeByWeekIncomeList($info3, $param["date_from"], $param["date_to"]);

$list_month = makeByMonthIncomeList($info5, $param["date_from"], $param["date_to"]);
$list_year = makeByYearIncomeList($info6, $param["date_from"], $param["date_to"]);

$list2 = "";
$list2 = makeByDayIncomeList($info4, $incomeDAO, $conn);
$memo_list = "";
$memo_list = makeMemberMemoList($memo);


$member_name = '<div onclick="showDetail('.$info2->fields["member_seqno"].');">' . $info2->fields["member_name"] . '</div>';
echo $list . "♪♭@" . $list2 . "♪♭@" . $member_name . "♪♭@" .
    number_format($prepay_price) . "원♪♭@" .  number_format($info->fields["pay_price"]) . "원♪♭@" .
    number_format($info->fields["depo_price"] + $info->fields["card_depo_price"]) . "원♪♭@" . $info2->fields["tel_num"] . "♪♭@" .
    $info2->fields["cell_num"] . "♪♭@" . $info2->fields["fax_num"] . "♪♭@" .
    $info2->fields["depo_finish_date"] . "♪♭@" . $memo_list . "♪♭@" .
    number_format($info->fields["pay_price"] + $info->fields["card_pay_price"] - $info->fields["adjust_sales"])
    . "원". "♪♭@"  . number_format($info->fields["enuri"])
    . "원". "♪♭@"  . number_format($info->fields["adjust_deposit"]) . "원"
    . "♪♭@"  . $list_month
    . "♪♭@"  . $list_year;

$conn->close();


?>
