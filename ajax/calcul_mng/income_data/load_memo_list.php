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

//회원 일련번호
$param["member_seqno"] = $fb->form("member_seqno");
//등록 시작 일자
$param["date_from"] = $fb->form("date_from");
//등록 종료 일자
$param["date_to"] = $fb->form("date_to");

$memo = $incomeDAO->selectMemberMemo($conn, $param);

$memo_list = "";
$memo_list = makeMemberMemoList($memo);

echo $memo_list;

$conn->close();


?>
