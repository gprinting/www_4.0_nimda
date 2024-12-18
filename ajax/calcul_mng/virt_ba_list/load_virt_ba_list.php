<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/virt_ba_mng/VirtBaListDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/pageLib.inc");
$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$virtDAO = new VirtBaListDAO();

//페이징
$list_num = $fb->form("list_num"); //한페이지에 출력할 게시물 개수
if (!$fb->form("list_num")) $list_num = 30;

$scrnum = 5; //블록 개수
$page = $fb->form("page");

if (!$page) $page = 1; // 페이지가 없으면 1 페이지

$param = array();
//판매채널 일련번호
$param["cpn_admin_seqno"] = $fb->form("sell_site");
//검색구분
if ($fb->form("search_dvs") == "1") {

    $param["ba_num"] = $fb->form("search_str");

} else {

    $param["member_name"] = $fb->form("search_str");

}
//상태
$param["state"] = $fb->form("state");

//페이징
$param["start"] = $list_num * ($page-1);
$param["end"] = $list_num;

//가상계좌 리스트
$result = $virtDAO->selectVirtBaList($conn, $param);
$count_rs = $virtDAO->countVirtBaList($conn, $param);

$total_count = $count_rs->fields["cnt"]; //페이징할 총 글수

$ret = "";
$ret = mkDotAjaxPage($total_count, $page, $scrnum, $list_num, "searchResult");

//가상계좌리스트 테이블 그리기
$list = "";
$list = makeVirtBaList($result, $list_num * ($page-1));

echo $list . "♪♭@" . $ret;

$conn->close();
?>
