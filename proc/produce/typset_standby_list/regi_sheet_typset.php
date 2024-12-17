<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/TypsetStandbyListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetStandbyListDAO();
$check = 1;

$seqno_arr = explode(",", $fb->form("seqno"));

//echo $seqno_arr[0]; exit;
$print_title = $fb->form("print_title");

$after_list = "";
$opt_list = "";

for ($i = 0; $i < count($seqno_arr); $i++) {

    //조판 중복 방지 및 임의의 주문상세일련번호 가져옴
    //후공정 목록 생성, 옵션 목록 생성
    $param = array();
    $param["table"] = "amt_order_detail_sheet";
    $param["col"] = "state, order_detail_count_file_seqno";
    $param["where"]["amt_order_detail_sheet_seqno"] = $seqno_arr[$i];

    $rs = $dao->selectData($conn, $param);

    if ($rs->fields["state"] == "2130") {
        echo "2";
        exit;
    }

    $order_detail_count_file_seqno = $rs->fields["order_detail_count_file_seqno"];

    $param = array();
    $param["table"] = "order_detail_count_file";
    $param["col"] = "order_detail_seqno";
    $param["where"]["order_detail_count_file_seqno"] = $order_detail_count_file_seqno;

    $rs = $dao->selectData($conn, $param);

    $order_detail_seqno = $rs->fields["order_detail_seqno"];

    $param = array();
    $param["table"] = "order_detail";
    $param["col"] = "order_common_seqno, order_detail_dvs_num";
    $param["where"]["order_detail_seqno"] = $order_detail_seqno;

    $sel_rs = $dao->selectData($conn, $param);

    //후공정 목록
    $param = array();
    $param["table"] = "order_after_history";
    $param["col"] = "after_name";
    $param["where"]["order_detail_dvs_num"] = $sel_rs->fields["order_detail_dvs_num"];

    $after_rs = $dao->selectData($conn, $param);

    while ($after_rs && !$after_rs->EOF) {

        $after_list .= "," . $after_rs->fields["after_name"];
        $after_rs->moveNext();
    }

    //옵션 목록
    $param = array();
    $param["table"] = "order_opt_history";
    $param["col"] = "opt_name";
    $param["where"]["order_common_seqno"] = $sel_rs->fields["order_common_seqno"];

    $opt_rs = $dao->selectData($conn, $param);

    while ($opt_rs && !$opt_rs->EOF) {

        $opt_list .= "," . $opt_rs->fields["opt_name"];
        $opt_rs->moveNext();
    }

    //운영체제 가져옴
    $param = array();
    $param["order_common_seqno"] = $sel_rs->fields["order_common_seqno"];

    $oper_sys = $dao->selectOperSys($conn, $param)->fields["oper_sys"];

    //주문 상태변경
    $param = array();
    $param["order_state"] = "2130";
    $param["order_common_seqno"] = $sel_rs->fields["order_common_seqno"];

    $rs = $dao->updateOrderState($conn, $param);

    if (!$rs) {
        $check = 0;
    }
}

//주문 공통 일련번호, 카테고리 종이 맵핑코드
$param = array();
$param["table"] = "order_detail";
$param["col"] = "order_common_seqno, cate_paper_mpcode";
$param["where"]["order_detail_seqno"] = $order_detail_seqno;

$rs = $dao->selectData($conn, $param);

$cate_paper_mpcode = $rs->fields["cate_paper_mpcode"];

//카테고리 분류코드 가져옴
$param = array();
$param["seqno"] = $rs->fields["order_common_seqno"];

$rs = $dao->selectCateSortcode($conn, $param);

$cate_sortcode = $rs->fields["cate_sortcode"];

//카테고리 종이 정보 가져옴
$param = array();
$param["table"] = "cate_paper";
$param["col"] = "name, dvs, color, basisweight";
$param["where"]["mpcode"] = $cate_paper_mpcode;

$rs = $dao->selectData($conn, $param);

$paper_name = $rs->fields["name"];
$paper_dvs = $rs->fields["dvs"];
$paper_color = $rs->fields["color"];
$paper_basisweight = $rs->fields["basisweight"];


$conn->StartTrans();

$param = array();
$param["table"] = "sheet_typset";
$param["col"]["typset_num"] = "";
$param["col"]["state"] = "2130";
$param["col"]["print_title"] = $print_title;
$param["col"]["empl_seqno"] = $fb->session("empl_seqno");
$param["col"]["regi_date"] = date("Y-m-d H:i:s"); 
$param["col"]["cate_sortcode"] = $cate_sortcode;
$param["col"]["paper_name"] = $paper_name;
$param["col"]["paper_dvs"] = $paper_dvs;
$param["col"]["paper_color"] = $paper_color;
$param["col"]["paper_basisweight"] = $paper_basisweight;
$param["col"]["prdt_page"] = 2;
$param["col"]["prdt_page_dvs"] = "낱장";
$param["col"]["oper_sys"] = $oper_sys;
$param["col"]["after_list"] = substr($after_list, 1);
$param["col"]["opt_list"] = substr($opt_list, 1);

$rs = $dao->insertData($conn, $param);

if (!$rs) {
    echo 'sheet_typset_in';
    $check = 0;
}

$insert_seqno = $conn->Insert_ID();
$typset_num = "S" . $insert_seqno;

$param = array();
$param["table"] = "sheet_typset";
$param["col"]["typset_num"] = $typset_num;
$param["prk"] = "sheet_typset_seqno";
$param["prkVal"] = $insert_seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

for ($i = 0; $i < count($seqno_arr); $i++) {

    $param = array();
    $param["table"] = "amt_order_detail_sheet";
    $param["col"]["state"] = "2130";
    $param["col"]["sheet_typset_seqno"] = $insert_seqno;
    $param["prk"] = "amt_order_detail_sheet_seqno";
    $param["prkVal"] = $seqno_arr[$i];

    $rs = $dao->updateData($conn, $param);
 
    if (!$rs) {
        $check = 0;
    }
}

$param = array();
$param["table"] = "produce_process_flow";
$param["col"]["typset_num"] = $typset_num;

$rs = $dao->insertData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
