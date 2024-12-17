<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/TypsetListDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();

$check = 1;
$seqno_arr = explode(",", $fb->form("seqno"));
$board = $fb->form("board");

$param = array();
$param["table"] = "sheet_typset";
$param["col"] = "COUNT(typset_num) AS cnt";
$param["blike"]["regi_date"] = date("Y-m-d");
$param["blike"]["typset_num"] = $board;

$typset_cnt = (int)$dao->selectData($conn, $param)->fields["cnt"] + 1;

if (!$typset_num) {
    $typset_num = 1;
}

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

    $specialty_items = "";
    while ($opt_rs && !$opt_rs->EOF) {

        $param = array();
        $param["table"] = "opt_info";
        $param["col"] = "nick";
        $param["where"]["opt_name"] = $opt_rs->fields["opt_name"];

        $nick = $dao->selectData($conn, $parma)->fields["nick"];

        if ($nick) {
            $specialty_items .= $nick . " ";
        }
        $opt_list .= "," . $opt_rs->fields["opt_name"];
        $opt_rs->moveNext();
    }

    //주문정보 가져옴
    $param = array();
    $param["table"] = "order_common";
    $param["col"] = "oper_sys, page_cnt";
    $param["where"]["order_common_seqno"] = $sel_rs->fields["order_common_seqno"];

    $rs = $dao->selectData($conn, $param);

    $oper_sys = $rs->fields["oper_sys"];
    $page_cnt = $rs->fields["page_cnt"];

    //주문 상태변경
    $param = array();
    $param["table"] = "order_common";
    $param["col"]["order_state"] = "2130";
    $param["prk"] = "order_common_seqno";
    $param["prkVal"] = $sel_rs->fields["order_common_seqno"];

    $rs = $dao->updateData($conn, $param);

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
$param["table"] = "order_common";
$param["col"] = "cate_sortcode";
$param["where"]["order_common_seqno"] = $rs->fields["order_common_seqno"];

$rs = $dao->selectData($conn, $param);

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

$typset_num = $board . date("ymd") . "M" . str_pad($typset_cnt,"2","0",STR_PAD_LEFT);

$conn->StartTrans();

$param = array();
$param["table"] = "sheet_typset";
$param["col"]["typset_num"] = $typset_num;
$param["col"]["state"] = "2130";
$param["col"]["empl_seqno"] = $fb->session("empl_seqno");
$param["col"]["regi_date"] = date("Y-m-d H:i:s"); 
$param["col"]["cate_sortcode"] = $cate_sortcode;
$param["col"]["paper_name"] = $paper_name;
$param["col"]["paper_dvs"] = $paper_dvs;
$param["col"]["paper_color"] = $paper_color;
$param["col"]["paper_basisweight"] = $paper_basisweight;
$param["col"]["print_amt_unit"] = "장";
$param["col"]["prdt_page"] = 2;
$param["col"]["prdt_page_dvs"] = "낱장";
$param["col"]["oper_sys"] = $oper_sys;
$param["col"]["after_list"] = substr($after_list, 1);
$param["col"]["opt_list"] = substr($opt_list, 1);
$param["col"]["typset_way"] = "MANUAL";
$param["col"]["save_path"] = "";
$param["col"]["save_yn"] = "Y";
$param["col"]["specialty_items"] = $specialty_items;

$rs = $dao->insertData($conn, $param);

if (!$rs) {
    $check = 0;
}

$insert_seqno = $conn->Insert_ID();

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
$param["table"] = "output_op";
$param["col"]["typset_num"] = $typset_num;
$param["col"]["state"] = $state_arr["출력준비"];
$param["col"]["flattyp_dvs"] = "Y";
$param["col"]["typ"] = "자동발주";
$param["col"]["typ_detail"] = "자동생성";

$rs = $dao->insertData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "print_op";
$param["col"]["typset_num"] = $typset_num;
$param["col"]["state"] = $state_arr["인쇄준비"];
$param["col"]["amt_unit"] = "장";
$param["col"]["flattyp_dvs"] = "Y";
$param["col"]["typ"] = "자동발주";
$param["col"]["typ_detail"] = "자동생성";

$rs = $dao->insertData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "order_after_history";
$param["col"] = "after_name, depth1, depth2, depth3";
$param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;
$param["where"]["basic_yn"] = "Y";

$sel_rs = $dao->selectData($conn, $param);

while ($sel_rs && !$sel_rs->EOF) {
    $param = array();
    $param["table"] = "basic_after_op";
    $param["col"]["typset_num"] = $typset_num;
    $param["col"]["state"] = $state_arr["조판후공정준비"];
    $param["col"]["flattyp_dvs"] = "N";
    $param["col"]["op_typ"] = "자동발주";
    $param["col"]["op_typ_detail"] = "자동생성";
    $param["col"]["after_name"] = $sel_rs->fields["after_name"];
    $param["col"]["depth1"] = $sel_rs->fields["depth1"];
    $param["col"]["depth2"] = $sel_rs->fields["depth2"];
    $param["col"]["depth3"] = $sel_rs->fields["depth3"];

    $rs = $dao->insertData($conn, $param);

    if (!$rs) {
        $check = 0;
    }
    $sel_rs->moveNext();
}

$param = array();
$param["table"] = "produce_process_flow";
$param["col"]["typset_num"] = $typset_num;

$rs = $dao->insertData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->close();
echo $check; 
?>
