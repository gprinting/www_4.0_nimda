<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/organ_mng/OrganMngDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/pageLib.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$fb = new FormBean();
$organDAO = new OrganMngDAO();

//페이징
$list_num = $fb->form("list_num"); //한페이지에 출력할 게시물 개수
if (!$fb->form("list_num")) $list_num = 30;

$scrnum = 5; //블록 개수
$page = $fb->form("page");

if (!$page) $page = 1; // 페이지가 없으면 1 페이지

$param = array();
//페이징
$param["start"] = $list_num * ($page-1);
$param["end"] = $list_num;

$param["cpn_admin_seqno"] = $fb->form("sell_site");

//관리 권한 리스트
$result = $organDAO->selectMngAuthList($conn, $param);

$count_rs = $organDAO->countMngAuthList($conn, $param);

$total_count = $count_rs->fields["cnt"]; //페이징할 총 글수

$ret = "";
$ret = mkDotAjaxPage($total_count, $page, $scrnum, $list_num, "searchResult");

//관리 권한 테이블 그리기
$list = "";
$list = makeMngAuthList($result, $list_num * ($page-1));

echo $list . "★" . $ret;

$conn->close();
?>
