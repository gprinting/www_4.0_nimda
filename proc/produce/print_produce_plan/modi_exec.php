<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/produce_plan/PrintProducePlanDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$conn->StartTrans();

$fb = new FormBean();
$dao = new PrintProducePlanDAO();
$check = "수정에 성공하였습니다.";

$dvs = $fb->form("dvs");
$value = $fb->form("value");
$tot = $fb->form("tot");

$param = array();
$param["dvs"] = $fb->form("dvs");
$param["value"] = $fb->form("value");
$param["print_produce_sch_seqno"] = $fb->form("seq");
if ($tot == "plus")
    $param["tot_value"] = "+1";
else
    $param["tot_value"] = "-1";

//인쇄생산계획 이행등록
$rs = $dao->updateExec($conn, $param);

if (!$rs) {
    $check = "수정에 실패하였습니다.";
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
