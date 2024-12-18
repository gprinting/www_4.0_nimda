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

$member_seqno = $fb->form("member_seqno");
$member_memo = $fb->form("member_memo");
$memo_date = $fb->form("memo_date");

//상세보기 출력 발주
$param = array();
$param["member_seqno"] = $member_seqno;
$param["member_memo"] = $member_memo;
$param["memo_date"] = $memo_date;

$rs = $dao->insertMemberMemo($conn, $param);

$conn->close();

echo "1";
?>
