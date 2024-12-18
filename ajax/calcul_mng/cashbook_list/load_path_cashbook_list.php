<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/calcul_mng/cashbook/CashbookListDAO.inc');
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/pageLib.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$cashbookDAO = new CashbookListDAO();

//페이징
$list_num = $fb->form("list_num"); //한페이지에 출력할 게시물 개수
if (!$fb->form("list_num")) $list_num = 30;

$scrnum = 5; //블록 개수
$page = $fb->form("page");

if (!$page) $page = 1; // 페이지가 없으면 1 페이지

$param = array();
//판매채널 일련번호
$param["cpn_admin_seqno"] = $fb->form("path_sell_site");
//입출금경로 일련번호
$param["depo_path"] = $fb->form("depo_path");
//입출금경로상세 일련번호
$param["depo_path_detail"] = $fb->form("depo_path_detail");
//적요
$param["sumup"] = $fb->form("sumup");
//증빙 시작 일자
$param["date_from"] = $fb->form("path_date_from");
//증빙 종료 일자
$param["date_to"] = $fb->form("path_date_to");

//페이징
$param["start"] = $list_num * ($page-1);
$param["end"] = $list_num;

//금전출납부 리스트
$result = $cashbookDAO->selectPathTypeList($conn, $param);
$count_rs = $cashbookDAO->countPathTypeList($conn, $param);

$total_count = $count_rs->fields["cnt"]; //페이징할 총 글수

$ret = "";
$ret = mkDotAjaxPage($total_count, $page, $scrnum, $list_num, "searchResult");

//금전출납부 테이블 그리기
$list = "";
$list = makePathTypeList($result, $list_num * ($page-1));

$result = $cashbookDAO->selectTrsfSumPrice($conn, $param);

$trsf_income = "0";
$trsf_expen = "0";

//수입합계가 있을때
if ($result->fields["trsf_income"]) {
    $trsf_income = $result->fields["trsf_income"];
}

//지출합계가 있을때
if ($result->fields["trsf_expen"]) {
    $trsf_expen = $result->fields["trsf_expen"];
}

$sum = $trsf_income - $trsf_expen;

echo $list . "♪♭@" . $ret . "♪♭@" . number_format($trsf_income) . "원♪♭@" . 
     number_format($trsf_expen) . "원♪♭@" .  number_format($sum) . "원";

$conn->close();


?>
