<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/dprinting/MoamoaDAO.inc');
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/output_mng/OutputListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$MoamoaDAO = new MoamoaDAO();
$OutputDAO = new OutputListDAO();
$util = new CommonUtil();

$check = 1;
if($fb->form("seqno") != null)
    $sheet_typset_seqno_arr = explode(",", $fb->form("seqno"));
else {
    $typset_num = $fb->form("typset_num");
    $param = array();
    $param["table"] = "sheet_typset";
    $param["col"] = "sheet_typset_seqno";
    $param["where"]["typset_num"] = $typset_num;
    $rs = $OutputDAO->selectData($conn, $param);
    $sheet_typset_seqno_arr[0] = $rs->fields["sheet_typset_seqno"];
}

$param = array();
if($fb->form("state") != null)
    $param['state'] = $fb->form("state");
else
    $param['state'] = "2680";

$barcode_start_char = $fb->form("barcode_start_char");
if($barcode_start_char != "") {
    $id_rs = $OutputDAO->selectBarcodeStartChar($conn, $barcode_start_char);
    $param['empl_id'] = $id_rs->fields["empl_id"];
}
if($param['empl_id'] == "")
    $param['empl_id'] = $fb->getSession()["id"];

$param['detail'] = str_replace("(판)","",$fb->form("detail"));

foreach($sheet_typset_seqno_arr as $sheet_typset_seqno) {
    $param['sheet_typset_seqno'] = $sheet_typset_seqno;

    // typset_state_history, order_state_history 에 후가공 삽입
    $MoamoaDAO->insertTypsetStateHistory($conn, $param);
    $rs = $OutputDAO->selectOrderNumInTypset($conn, $sheet_typset_seqno);
    while ($rs && !$rs->EOF) {
        $param['ordernum'] = $rs->fields['order_num'];
        $MoamoaDAO->insertStateHistory($conn, $param);
        $rs->MoveNext();
    }

    // 작업완료와 후가공수 비교
    $rs = $OutputDAO->selectTypsetAllAfterNum($conn, $sheet_typset_seqno);
    $allaftercnt = count(explode(",",$rs->fields['detail']));

    $rs = $OutputDAO->selectTypsetCompleteAfterNum($conn, $sheet_typset_seqno);
    $completeaftercnt = $rs->fields['cnt'];

    if($allaftercnt <= $completeaftercnt) {
        // 수가 같으면 재단대기로 상태변경
        $param['state'] = "2420";
        $param['detail'] = "";
        $MoamoaDAO->updateTypsetState($conn, $param);
        $rs = $OutputDAO->selectOrderNumInTypset($conn, $sheet_typset_seqno);
        while ($rs && !$rs->EOF) {
            $param['ordernum'] = $rs->fields['order_num'];

            $MoamoaDAO->updateProductStatecode($conn, $param);
            $MoamoaDAO->insertStateHistory($conn, $param);
            $rs->MoveNext();
        }
    }
}

/*
foreach ($seqno_arr as $key => $value) {

    $print_op_seqno = $value;
    $state = $util->status2statusCode("조판후공정대기");
    $stor_state = $util->status2statusCode("인쇄대기");

    //인쇄 발주서 검색
    $param = array();
    $param["table"] = "print_op";
    $param["col"] = "typset_num, flattyp_dvs, state
        ,aftside_tmpt ,aftside_spc_tmpt ,amt ,amt_unit, extnl_brand_seqno";
    $param["where"]["print_op_seqno"] = $print_op_seqno;

    $sel_rs = $dao->selectData($conn, $param);

    $process_yn_state = $sel_rs->fields["state"];
    $process_yn = "Y";
    $flattyp_dvs = $sel_rs->fields["flattyp_dvs"];
    $typset_num = $sel_rs->fields["typset_num"];
    $aftside_tmpt = $sel_rs->fields["aftside_tmpt"];
    $aftside_spc_tmpt = $sel_rs->fields["aftside_spc_tmpt"];
    $amt = $sel_rs->fields["amt"];
    $amt_unit = $sel_rs->fields["amt_unit"];
    $extnl_brand_seqno = $sel_rs->fields["extnl_brand_seqno"];

    if ($process_yn_state == $state) {
        $process_yn = "N";
    }

    if ($stor_state == $process_yn_state) {
        $param = array();
        $param["table"] = "extnl_brand";
        $param["col"] = "extnl_etprs_seqno";
        $param["where"]["extnl_brand_seqno"] = $extnl_brand_seqno;

        $extnl_etprs_seqno = $dao->selectData($conn, $param)->fields["extnl_etprs_seqno"];

        $param = array();
        $param["table"] = "extnl_etprs";
        $param["col"] = "manu_name";
        $param["where"]["extnl_etprs_seqno"] = $extnl_etprs_seqno;

        $manu = $dao->selectData($conn, $param)->fields["manu_name"];

        //R->장 = 장수 * 500 * 자리수(절수)
        //장->R = 장수 / 자리수(절수) / 500

        $amt = $sel_rs->fields["amt"];
        $subpaper = $sel_rs->fields["subpaper"];
        if ($sel_rs->fields["amt_unit"] == "장") {
            if ($subpaper == "별" || !$subpaper) {
                $subpaper = 1;
            }
            $amt = $sel_rs->fields["amt"] / $subpaper / 500;
        }

        $param = array();
        if ($flattyp_dvs == "Y") {
            $param["table"] = "sheet_typset";
        } else {
            $param["table"] = "brochure_typset";
        }
        $param["col"] = "paper_name, paper_dvs, paper_color, paper_basisweight";
        $param["where"]["typset_num"] = $typset_num;

        $sel_rs = $dao->selectData($conn, $param);

        $paper_name = $sel_rs->fields["paper_name"];
        $paper_dvs = $sel_rs->fields["paper_dvs"];
        $paper_color = $sel_rs->fields["paper_color"];
        $paper_basisweight = $sel_rs->fields["paper_basisweight"];

        $param = array();
        $param["paper_name"] = $paper_name;
        $param["paper_dvs"] = $paper_dvs;
        $param["paper_color"] = $paper_color;
        $param["paper_basisweight"] = $paper_basisweight;
        $param["manu"] = $manu;

        $last_amt = $dao->selectLastStockAmt($conn, $param)->fields["stock_amt"];

        $param = array();
        $param["table"] = "manu_paper_stock_detail";
        $param["col"]["regi_date"] = date("Y-m-d H:i:s");
        $param["col"]["stor_yn"] = "N";
        $param["col"]["paper_name"] = $paper_name;
        $param["col"]["paper_dvs"] = $paper_dvs;
        $param["col"]["paper_color"] = $paper_color;
        $param["col"]["paper_basisweight"] = $paper_basisweight;
        $param["col"]["manu"] = $manu;
        $param["col"]["use_amt"] = $amt;
        $param["col"]["typset_num"] = $typset_num;
        $param["col"]["stock_amt"] = $last_amt - $amt;

        $rs = $dao->insertData($conn, $param);

        if (!$rs) {
            $check = 0;
        }

        $param = array();
        $param["table"] = "print_op";
        $param["col"]["paper_stor_yn"] = "Y";
        $param["prk"] = "print_op_seqno";
        $param["prkVal"] = $print_op_seqno;

        $rs = $dao->updateData($conn, $param);

        if (!$rs) {
            $check = 0;
        }
    }

    if ($process_yn == "Y") {

        $param = array();
        $param["table"] = "basic_after_op";
        $param["col"]["state"] = $state;
        $param["prk"] = "typset_num";
        $param["prkVal"] = $sel_rs->fields["typset_num"];

        $rs = $dao->updateData($conn, $param);

        if (!$rs) {
            $check = 0;
        }

        $param = array();
        $param["flattyp_dvs"] = $sel_rs->fields["flattyp_dvs"];
        $param["typset_num"] = $sel_rs->fields["typset_num"];
        $param["state"] = $state;

        $check = $util->changeOrderState($conn, $dao, $param);

        //인쇄지시서 상태 변경
        $param = array();
        $param["table"] = "print_op";
        $param["col"]["state"] = $state;
        $param["prk"] = "print_op_seqno";
        $param["prkVal"] = $print_op_seqno;

        $rs = $dao->updateData($conn, $param);

        if (!$rs) {
            $check = 0;
        }

        $expec_perform_mark = "2";

        if (($aftside_tmpt == 0 ||
                    $aftside_tmpt == NULL ||
                    $aftside_tmpt == "") &&
                ($aftside_spc_tmpt == 0 ||
                 $aftside_spc_tmpt == NULL ||
                 $aftside_spc_tmpt == "")) {

            $expec_perform_mark = "1";
        }

        $expec_perform_paper = $amt;
        if ($amt_unit == "장") {

            $expec_perform_paper = intVal($amt) / 500;
        }
        $subpaper = str_replace("절", "", $fb->form("subpaper"));
        $expec_perform_bucket = $subpaper * 500 * $expec_perform_mark * $expec_perform_paper;

        $param = array();
        $param["table"] = "print_work_report";
        $param["col"] = "worker_memo ,work_start_hour, worker
            , perform_date, ink_C, ink_M, ink_Y, ink_K, subpaper
            , adjust_price, work_price, extnl_etprs_seqno";
        $param["where"]["print_op_seqno"] = $print_op_seqno;
        $param["where"]["valid_yn"] = "Y";

        $rs = $dao->selectData($conn, $param);

        $worker_memo = $rs->fields["worker_memo"];
        $work_start_hour = $rs->fields["work_start_hour"];
        $perform_date = $rs->fields["perform_date"];
        $worker = $rs->fields["worker"];
        $subpaper = $rs->fields["subpaper"];
        $ink_C = $rs->fields["ink_C"];
        $ink_M = $rs->fields["ink_M"];
        $ink_Y = $rs->fields["ink_Y"];
        $ink_K = $rs->fields["ink_K"];
        $adjust_price = $rs->fields["adjust_price"];
        $work_price = $rs->fields["work_price"];
        $extnl_etprs_seqno = $rs->fields["extnl_etprs_seqno"];

        $param = array();
        $param["table"] = "print_work_report";
        $param["col"] = "print_work_report_seqno";
        $param["where"]["print_op_seqno"] = $print_op_seqno;

        $rs = $dao->selectData($conn, $param);

        if (!$rs || $rs->EOF) {

            $param = array();
            $param["table"] = "print_work_report";
            $param["col"]["worker_memo"] = "다중선택 완료처리";
            $param["col"]["extnl_etprs_seqno"] = $fb->session("extnl_etprs_seqno");
            $param["col"]["worker"] = $fb->session("name");
            $param["col"]["valid_yn"] = "Y";
            $param["col"]["state"] = $state;
            $param["col"]["print_op_seqno"] = $print_op_seqno;

            $rs = $dao->insertData($conn, $param);

            if (!$rs) {
                $check = 0;
            }
        } else {

            //기존 작업일지 유효여부 수정
            $param = array();
            $param["table"] = "print_work_report";
            $param["col"]["work_end_hour"] = date("Y-m-d H:i:s");
            $param["col"]["state"] = $state;
            $param["prk"] = "print_op_seqno";
            $param["prkVal"] = $print_op_seqno;

            $rs = $dao->updateWorkReport($conn, $param);

            if (!$rs) {
                $check = 0;
            }
        }
    }
}

$conn->CompleteTrans();
*/
$conn->Close();
echo $check;
?>
