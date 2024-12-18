<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/tab/SalesTabListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new SalesTabListDAO();

$member_seqno   = $fb->form("member_seqno");

$param = array();
$param["member_seqno"] = $member_seqno;

//회원 정보 조회
$rs = $dao->selectMemberInfo($conn, $param);

echo $rs->fields["tel_num"] . "♪♭♬" . 
     $rs->fields["corp_name"] . "♪♭♬" . 
     $rs->fields["repre_name"] . "♪♭♬" . 
     $rs->fields["crn"] . "♪♭♬" . 
     $rs->fields["bc"] . "♪♭♬" . 
     $rs->fields["tob"] . "♪♭♬" . 
     $rs->fields["addr"] . " " . $rs->fields["addr_detail"] . "♪♭♬" . 
     $rs->fields["zipcode"];
     
$conn->close();
?>
	
 
