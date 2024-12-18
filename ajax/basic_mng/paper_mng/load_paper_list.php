<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/PaperMngDAO.inc');
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/pageLib.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();

$paperDAO = new PaperMngDAO();

//페이징
$list_num = $fb->form("list_num"); //한페이지에 출력할 게시물 개수
if (!$fb->form("list_num")) $list_num = 30;

$scrnum = 5; //블록 개수
$page = $fb->form("page");

if (!$page) $page = 1; // 페이지가 없으면 1 페이지

//종이
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

if ($fb->form("sort")) {

    $param["sort"] = "CONVERT(basisweight, UNSIGNED)";
}

//페이징
$param["start"] = $list_num * ($page-1);
$param["end"] = $list_num;

//결과 값을 가져옴
$result = $paperDAO->selectPrdcPaperList($conn, $param);

$param["col"] = "COUNT";
$param["start"] = "";
$param["end"] = "";

$count_rs = $paperDAO->selectPrdcPaperList($conn, $param);
$total_count = $count_rs->fields["count"]; //페이징할 총 글수

$ret = "";
$ret = mkDotAjaxPage($total_count, $page, $scrnum, $list_num, "searchResult");

//종이 테이블 그리기
$list = "";
$list = makePrdcPaperList($conn, $result, $list_num * ($page-1));

echo $list . "★" . $ret;
$conn->close();
?>
