<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/basic_mng/pur_etprs_mng/PurEtprsListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$purDAO = new PurEtprsListDAO();

//매입업체 테이블
$param = array();
//제조사
//매입 품목
$param["tob"] = $fb->form("tob");
//매입 업체 일련번호
$param["etprs_seqno"] = $fb->form("etprs_seqno");
//매입 브랜드 일련번호
$param["brand_seqno"] = $fb->form("brand_seqno");

//결과값을 가져옴
$result = $purDAO->selectExtnlEtprs($conn, $param);
echo  makeExtnlEtprsList($result);
$conn->close();
?>
