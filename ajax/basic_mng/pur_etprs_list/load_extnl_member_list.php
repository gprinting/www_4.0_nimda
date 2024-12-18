<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/basic_mng/pur_etprs_mng/PurEtprsListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$purDAO = new PurEtprsListDAO();

$member_list = "";
$param = array();
//매입업체 일련번호
$param["table"] = "extnl_etprs_member";
$param["col"] = "mng, extnl_etprs_seqno, id, access_code, tel_num,
                 cell_num, mail, resp_task, extnl_etprs_member_seqno";
$param["where"]["extnl_etprs_seqno"] = $fb->form("etprs_seqno");

//매입업체 회원 결과리스트를 가져옴
$result = $purDAO->selectData($conn, $param);
$member_list = makeExtnlMemberList($result);

echo $member_list;

?>
