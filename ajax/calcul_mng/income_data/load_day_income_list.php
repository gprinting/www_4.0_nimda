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
$param["date"] = $fb->form("date");

//수입 리스트
$info = $incomeDAO->selectDayIncomeList($conn, $param);

//수입 테이블 그리기
$list = "";
$list = makeDayIncomeList($info);

echo $list;

$conn->close();


?>
