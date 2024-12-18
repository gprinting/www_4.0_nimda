<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/common/NimdaCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new NimdaCommonDAO();

/*@
세션별 판매채널 처리 필요
 */

$param = array();
$param["sell_site"] = $fb->form("sell_site");
$param["depar_code"] = "001";
//2016-07-26 방문(002003) 만 추가
//selectDeparInfo 를 여기서만 사용함 -> 수정함
$param["depar_code2"] = "002003";

$rs = $dao->selectDeparInfo($conn, $param);

$arr = [];
$arr["flag"] = "Y";
$arr["def"] = "팀(전체)";
$arr["dvs"] = "depar_name";
$arr["val"] = "depar_code";

//등급 검색
echo makeSelectOptionHtml($rs, $arr);
$conn->close();
?>
