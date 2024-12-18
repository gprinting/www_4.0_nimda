<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/TypsetMngDAO.inc');
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/pageLib.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetMngDAO();

//페이징
$list_num = $fb->form("list_num"); //한페이지에 출력할 게시물 개수
if (!$fb->form("list_num")) {
    $list_num = 30;
}

$scrnum = 5; //블록 개수
$page = $fb->form("page");

// 페이지가 없으면 1 페이지
if (!$page) {
    $page = 1;
}

$s_num = $list_num * ($page-1);

//조판 테이블
$param = array();

//$param["typset_seqno"] = $fb->form("typset_seqno");
$param["typset_name"] = trim($fb->form("typset_name"));
$param["affil_fs"] = $fb->form("affil_fs");
$param["affil_guk"] = $fb->form("affil_guk");
$param["affil_spc"] = $fb->form("affil_spc");
$param["file"] = $fb->form("file");
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
$param["dvs"] = "SEQ";

//결과 값을 가져옴
$rs = $dao->selectTypsetList($conn, $param);

$param["dvs"] = "COUNT";
$count_rs = $dao->selectTypsetList($conn, $param);
$rsCount = $count_rs->fields["cnt"];

$param["cnt"] = $rsCount;

$list = makeTypsetList($rs, $param);
$paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "movePage");

echo $list . "♪" . $paging;
$conn->close();
?>
