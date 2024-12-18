<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/ReduceListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$reduceListDAO = new ReduceListDAO();

$param = array();
$param["member_seqno"] = $fb->form("seqno");

$rs = $reduceListDAO->selectMemberDetailInfo($conn, $param);
$order_count_rs = $reduceListDAO->selectOrderCountInfo($conn, $param);

$param = array();
$param["table"] = "cp_issue";
$param["col"] = "COUNT(*) AS cp";
$param["where"]["member_seqno"] = $fb->form("seqno");

$cp_count_rs = $reduceListDAO->selectData($conn, $param);

$param = array();
$param["table"] = "member_withdraw";
$param["col"] = "withdraw_code, reason";
$param["where"]["member_seqno"] = $fb->form("seqno");

$withdraw_rs = $reduceListDAO->selectData($conn, $param);

$withdraw_code = explode(",", $withdraw_rs->fields["withdraw_code"]);

$withdarw_arr = array();
$withdarw_arr[1] = "N";
$withdarw_arr[2] = "N";
$withdarw_arr[3] = "N";
$withdarw_arr[4] = "N";
$withdarw_arr[5] = "N";
$withdarw_arr[6] = "N";
$withdarw_arr[7] = "N";
$withdarw_arr[8] = "N";
$withdarw_arr[9] = "N";
$withdarw_arr[10] = "N";
$withdarw_arr[11] = "N";
$withdarw_arr[12] = "N";
$withdarw_arr[13] = "N";
$withdarw_arr[14] = "N";

foreach($withdraw_code as $key => $value) {

    $withdarw_arr[$value] = "Y";
}

$opt = array();
$opt[0] = "주문일";

$optVal = array();
$optVal[0] = "order_regi_date";

$param = array();
$param["value"] = $optVal;
$param["fields"] = $opt;
$param["id"] = "sales_search_cnd";
$param["flag"] = TRUE;
$param["from_id"] = "sales_from";
$param["to_id"] = "sales_to";
$param["func"] = "salesDateSet";

//날짜 검색
$date_picker_html = makeDatePickerHtml($param);

$param = array();
$param["member_seqno"] = $rs->fields["member_seqno"];
$param["member_name"] = $rs->fields["member_name"];
$param["member_id"] = $rs->fields["member_id"];
$param["member_dvs"] = $rs->fields["member_dvs"];
$param["sell_site"] = $rs->fields["sell_site"];
$param["cell_num"] = $rs->fields["cell_num"];
$param["mail"] = $rs->fields["mail"];
$param["birth"] = $rs->fields["birth"];
$param["office_eval"] = $rs->fields["office_eval"];
$param["first_join_date"] = $rs->fields["first_join_date"];
$param["first_order_date"] = $rs->fields["first_order_date"];
$param["final_order_date"] = $rs->fields["final_order_date"];
$param["own_point"] = $rs->fields["own_point"];
$param["grade_name"] = $rs->fields["grade_name"];
$param["cp"] = $cp_count_rs->fields["cp"];
$param["order_count"] = $order_count_rs->fields["order_count"];
$param["member_typ"] = $rs->fields["member_typ"];
$param["date_picker_html"] = $date_picker_html;
$param["withdraw_reason"] = $withdraw_rs->fields["reason"];

echo makeReduceMemberInfo($param) . "♪" . $rs->fields["new_yn"] . "♪" . 
     $withdarw_arr[1] . "♪" . $withdarw_arr[2] . "♪" . 
     $withdarw_arr[3] . "♪" . $withdarw_arr[4] . "♪" . 
     $withdarw_arr[5] . "♪" . $withdarw_arr[6] . "♪" . 
     $withdarw_arr[7] . "♪" . $withdarw_arr[8] . "♪" . 
     $withdarw_arr[9] . "♪" . $withdarw_arr[10] . "♪" . 
     $withdarw_arr[11] . "♪" . $withdarw_arr[12] . "♪" . 
     $withdarw_arr[13] . "♪" . $withdarw_arr[14];

$conn->close();
?>
