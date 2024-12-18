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
$param["cpn_admin_seqno"] = $fb->form("search_sell_site");
//계정 과목 일련번호
$param["acc_subject_seqno"] = $fb->form("search_acc_subject");
//계정 과목 상세 일련번호
$param["acc_detail_seqno"] = $fb->form("search_acc_subject_detail");
//증빙 시작 일자
$param["date_from"] = $fb->form("date_from");
//증빙 종료 일자
$param["date_to"] = $fb->form("date_to");
//팀 일련번호
$param["depar_admin_seqno"] = $fb->form("search_depar_list");

//페이징
$param["start"] = $list_num * ($page-1);
$param["end"] = $list_num;

//금전출납부 리스트
$result = $cashbookDAO->selectCashbookList($conn, $param);
$count_rs = $cashbookDAO->countCashbookList($conn, $param);

$total_count = $count_rs->fields["cnt"]; //페이징할 총 글수

$ret = "";
$ret = mkDotAjaxPage($total_count, $page, $scrnum, $list_num, "searchResult");

//금전출납부 테이블 그리기
$list = "";
$list = makeCashbookList($result, $list_num * ($page-1));

$param = array();
//증빙 시작 일자
$param["date_from"] = $fb->form("date_from");
//증빙 종료 일자
$param["date_to"] = $fb->form("date_to");

$result = $cashbookDAO->selectSumPrice($conn, $param);

$income = "0원";
$expen = "0원";

//수입합계가 있을때
if ($result->fields["income"]) {

    $income = $result->fields["income"] . "원";

}

//지출합계가 있을때
if ($result->fields["expen"]) {
    
    $expen = $result->fields["expen"] . "원";

}

echo $list . "♪♭@" . $ret . "♪♭@" . $income . "♪♭@" . $expen;

$conn->close();


?>
