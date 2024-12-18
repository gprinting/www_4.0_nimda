<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/OutputMngDAO.inc');
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/pageLib.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();

$outputDAO = new OutputMngDAO();

//페이징
$list_num = $fb->form("list_num"); //한페이지에 출력할 게시물 개수
if (!$fb->form("list_num")) $list_num = 30;

$scrnum = 5; //블록 개수
$page = $fb->form("page");

if (!$page) $page = 1; // 페이지가 없으면 1 페이지

//출력
$param = array();

$param["name"] = $fb->form("name");
$param["manu_seqno"] = $fb->form("manu_seqno");
$param["brand_seqno"] = $fb->form("brand_seqno");
$param["affil_fs"] = $fb->form("affil_fs");
$param["affil_guk"] = $fb->form("affil_guk");
$param["affil_spc"] = $fb->form("affil_spc");
$param["crtr_unit"] = $fb->form("crtr_unit");
//sort 기준
$param["sort_type"] = $fb->form("sort_type");
$param["sort"] = $fb->form("sort");

//페이징
$param["start"] = $list_num * ($page-1);
$param["end"] = $list_num;

//결과 값을 가져옴
$result = $outputDAO->selectPrdcOutputList($conn, $param);

$param["start"] = "";
$param["end"] = "";

$total_count = $outputDAO->selectFoundRows($conn);

$ret = "";
$ret = mkDotAjaxPage($total_count, $page, $scrnum, $list_num, "searchResult");

//출력 테이블 그리기
$list = "";
$list = makePrdcOutputList($conn, $result, $list_num * ($page-1));

echo $list . "★" . $ret;
$conn->close();
?>
