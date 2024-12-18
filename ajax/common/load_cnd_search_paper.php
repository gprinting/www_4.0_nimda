<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/common/NimdaCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new NimdaCommonDAO();

$search_cnd = $fb->form("search_cnd");
$search_txt = $fb->form("search_txt");

$param = array();
$param["search_cnd"] = $search_cnd;
$param["search_txt"] = $search_txt;

$rs = $dao->selectCndPaper($conn, $param);

$arr = array();
$arr["opt"] = $search_cnd;
$arr["opt_val"] = "paper_op_seqno";
$arr["func"] = "cnd";

echo makeSearchListSub($rs, $arr);
$conn->Close();
?>
