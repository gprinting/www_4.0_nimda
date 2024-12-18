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

$idx = $fb->form("idx");

//상세보기 출력 발주
$param = array();
$param["idx"] = $idx;

$rs = $dao->removeMemberMemo($conn, $param);

$conn->close();

echo "1";
?>
