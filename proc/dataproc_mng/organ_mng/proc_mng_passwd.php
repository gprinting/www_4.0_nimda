<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/organ_mng/OrganMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$conn->StartTrans();

$fb = new FormBean();
$organDAO = new OrganMngDAO();

$empl_seqno = $fb->form("mng_seq");

//비밀번호 0000으로 초기화
$query  = "\n UPDATE empl";
$query .= "\n    SET passwd = PASSWORD('0000')";
$query .= "\n  WHERE empl_seqno = '%s'";
$query  = sprintf($query, $empl_seqno);

$result = $conn->Execute($query);

if ($result === false) {
    echo false;
} else {
    echo true;
}


$conn->CompleteTrans();
$conn->close();
?>
