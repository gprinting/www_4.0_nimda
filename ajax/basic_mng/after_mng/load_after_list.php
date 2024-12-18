<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/AfterMngDAO.inc');
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/pageLib.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$afterDAO = new AfterMngDAO();

//페이징
$list_num = $fb->form("list_num"); //한페이지에 출력할 게시물 개수
if (!$fb->form("list_num")) $list_num = 30;

$scrnum = 5; //블록 개수
$page = $fb->form("page");

if (!$page) $page = 1; // 페이지가 없으면 1 페이지

//후공정 테이블
$param = array();
$param["name"] = $fb->form("name");
$param["depth1"] = $fb->form("depth1");
$param["depth2"] = $fb->form("depth2");
$param["depth3"] = $fb->form("depth3");
$param["affil"]    = $fb->form("affil");
$param["subpaper"] = $fb->form("subpaper");
$param["manu_seqno"] = $fb->form("manu_seqno");
$param["brand_seqno"] = $fb->form("brand_seqno");
$param["crtr_unit"] = $fb->form("crtr_unit");

//페이징
$param["start"] = $list_num * ($page-1);
$param["end"] = $list_num;

//결과 값을 가져옴
$result = $afterDAO->selectPrdcAfterList($conn, $param);

$param["start"] = "";
$param["end"] = "";

$total_count  = $afterDAO->selectFoundRows($conn);

$ret = "";
$ret = mkDotAjaxPage($total_count, $page, $scrnum, $list_num, "searchResult");

//후공정 테이블 그리기
$list = "";
$list = makePrdcAfterList($conn, $result, $list_num * ($page-1));

echo $list . "★" . $ret;
$conn->close();
?>
