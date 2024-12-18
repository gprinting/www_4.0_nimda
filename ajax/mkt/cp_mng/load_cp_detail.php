<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/CpMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$cpDAO = new CpMngDAO();

//등록 or 수정
$type = $fb->form("type");

//cp_cate_sortcode 초기화
$cp_cate_sortcode = "";

$amt_dspl = "style=\"display:none\"";

$param = array();
$param["submit"] = "등록";

//수정모드 일때
if ($type == "edit") {
    $param["submit"] = "수정";

    //쿠폰 일련번호
    $coupon_seqno = $fb->form("coupon_seqno");

    $cp_param = array();
    $cp_param["coupon_seqno"] = $coupon_seqno;

    //쿠폰 정보 가져오기
    $result = $cpDAO->selectCpList($conn, $cp_param);

    $param["coupon_seqno"] = $coupon_seqno;
    $param["name"] = $result->fields["name"];
    $param["sell_site"] = $result->fields["sell_site"];
    $param["release_date"] = explode(" ", $result->fields["release_date"])[0];
    $param["expired_date"] = explode(" ", $result->fields["expired_date"])[0];

    $param["lb_coupon_number"] = "margin-left: 100px;";
    if($result->fields["coupon_kind"] == "release_all") {
        $param["release_all"] = "checked";
        $param["lb_coupon_number"] .= "display:none;";
    }

    if($result->fields["coupon_kind"] == "input_coupon") {
        $param["input_coupon"] = "checked";
        $param["coupon_number_1"] = explode("-", $result->fields["coupon_number"])[0];
        $param["coupon_number_2"] = explode("-", $result->fields["coupon_number"])[1];
        $param["coupon_number_3"] = explode("-", $result->fields["coupon_number"])[2];
    }

    $discount_rate = json_decode($result->fields["discount_rate"]);
    $categories = json_decode($result->fields["categories"]);
    if($discount_rate->sale_dvs == "%") {
        $param["per_check"] = "checked";
    } else {
        $param["won_check"] = "checked";
    }

    $param["per_val"] = $discount_rate->per_val;
    $param["max_discount_price"] = $discount_rate->max_discount_price;
    $param["min_order_price"] = $discount_rate->min_order_price;
    $param["won_val"] = $discount_rate->won_val;
}


//선택된 대상상품 구분자 제거
$cp_cate_sortcode = substr($cp_cate_sortcode, 3);

//판매채널 검색
$param["sell_site"] = $cpDAO->selectSellSite($conn);

//카테고리 중분류 쿼리 실행
$param1 = array();
$param1["table"] = "cate";
$param1["col"] = "cate_name, sortcode";
$param1["where"]["cate_level"] = "2";

$result1 = $cpDAO->selectData($conn, $param1);
//카테고리 중분류 체크박스 셋팅
$cate_mid = makeCateMidList($result1, $categories);
$param["cate_mid"] = $cate_mid;

$html = getCpView($param);

$select_box_val = $result->fields["cpn_admin_seqno"] . "♪♡♭" . 
                  $cp_cate_sortcode . "♪♡♭" . 
                  $start_hour . "♪♡♭" . $start_min . "♪♡♭" . 
                  $end_hour . "♪♡♭" . $end_min . "♪♡♭";

echo $html . "♪♥♭" . $select_box_val;

$conn->close();
?>
