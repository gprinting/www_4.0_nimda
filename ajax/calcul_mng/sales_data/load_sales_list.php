<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/settle/SalesDataDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/pageLib.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$salesDAO = new SalesDataDAO();

//페이징
$list_num = $fb->form("list_num"); //한페이지에 출력할 게시물 개수
if (!$fb->form("list_num")) $list_num = 30;

$scrnum = 5; //블록 개수
$page = $fb->form("page");

if (!$page) $page = 1; // 페이지가 없으면 1 페이지

$param = array();
//판매채널 일련번호
$param["cpn_admin_seqno"] = $fb->form("sell_site");
//카테고리 분류코드 초기화
$sortcode = "";

//대분류가 선택되었을때
if ($fb->form("cate_top")) $sortcode = $fb->form("cate_top");

//중분류가 선택되었을때
if ($fb->form("cate_mid")) $sortcode = $fb->form("cate_mid");

//소분류가 선택되었을때
if ($fb->form("cate_bot")) $sortcode = $fb->form("cate_bot");

//카테고리 분류코드
$param["sortcode"] = $sortcode;
//팀구분
$param["depar_dvs"] = $fb->form("depar_dvs");
//데이터구분
$param["oper_sys"] = $fb->form("oper_sys");
//주문일자 시작
$param["date_from"] = $fb->form("date_from");
//주문일자 종료
$param["date_to"] = $fb->form("date_to");

//페이징
$param["start"] = $list_num * ($page-1);
$param["end"] = $list_num;

//매출 리스트
$result = $salesDAO->selectSalesList($conn, $param);

$param["count"] = "1";
$count_rs = $salesDAO->selectSalesList($conn, $param);

$total_count = $count_rs->fields["cnt"]; //페이징할 총 글수

$ret = "";
$ret = mkDotAjaxPage($total_count, $page, $scrnum, $list_num, "searchResult");

//매출 테이블 그리기
$list = "";
$list = makeSalesList($result, $list_num * ($page-1));

$result = $salesDAO->countMember($conn, $param);
$member_count = $result->fields["cnt"]; //회원(발주업체) 수

$sales = "0";
$order_discount = "0";
$adjust_discount = "0";
$discount = "0";
$income = "0";
$result = $salesDAO->selectSumPrice($conn, $param, "sales");

//매출합계가 있을때
if ($result->fields["sales"]) {

    $sales = $result->fields["sales"];

}

$result = $salesDAO->selectSumPrice($conn, $param, "discount");
//차감총액이 있을때
if ($result->fields["point_price"] ||
    $result->fields["grade_price"] ||
    $result->fields["event_price"] ||
    $result->fields["cp_price"]) {

    $order_discount = $result->fields["point_price"] +
                $result->fields["grade_price"] +
                $result->fields["event_price"] + 
                $result->fields["cp_price"];

}

$adjust_result = $salesDAO->selectSumAdjustPrice($conn, $param);
if ($adjust_result->fields["discount"]) {

    $adjust_discount = $adjust_result->fields["discount"];
}

$discount = $order_discount + $adjust_discount;
$income = $sales + $discount;

echo $list . "♪♭@" . $ret . "♪♭@" . number_format($member_count) . "♪♭@" .
     number_format($sales) . "원♪♭@" . number_format($discount) . "원♪♭@" . 
     number_format($income) . "원";

$conn->close();
?>
