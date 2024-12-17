<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_mng/PrdtBasicRegiDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$prdtBasicRegiDAO = new PrdtBasicRegiDAO();

$conn->StartTrans();

$select_el = $fb->form("selectEl");
$seqno = $fb->form("seqno");

//종이
$conn->debug = 1;
if ($select_el == "paper") {

    $param = array();
    $param["table"] = "prdt_paper";
    $param["col"] = "MAX(mpcode) AS mpcode";
    $mpcode_rs = $prdtBasicRegiDAO->selectData($conn, $param);

    $mpcode = $mpcode_rs->fields["mpcode"];

    if ($mpcode == "" || $mpcode == NULL) {
        $mpcode = 0;
    }

    if ($mpcode_rs->EOF == 1) {
        $mpcode = 0;
    }
 
    $param = array();
    $param["table"] = "prdt_paper";
    $param["col"] = "sort, name, dvs, color, basisweight, basisweight_unit, affil, size";
    $param["where"]["sort"] = $fb->form("sort");
    $param["where"]["name"] = $fb->form("name");
    $param["where"]["dvs"] = $fb->form("dvs");
    $param["where"]["color"] = $fb->form("color");
    $param["where"]["basisweight"] = $fb->form("basisweight");
    $param["where"]["basisweight_unit"] = $fb->form("basisweight_unit");
    $param["where"]["affil"] = $fb->form("affil");
    $param["where"]["size"] = $fb->form("wid_size") . "*" . $fb->form("vert_size");
    $param["where"]["crtr_unit"] = $fb->form("crtr_unit");

    $rs = $prdtBasicRegiDAO->selectData($conn, $param);

    //중복된 값이 없으면
    if ($rs->EOF == 1) {
        $param = array();
        $param["table"] = "prdt_paper";
        $param["col"]["sort"] = $fb->form("sort");
        $param["col"]["name"] = $fb->form("name");
        $param["col"]["dvs"] = $fb->form("dvs");
        $param["col"]["color"] = $fb->form("color");
        $param["col"]["basisweight"] = $fb->form("basisweight");
        $param["col"]["basisweight_unit"] = $fb->form("basisweight_unit");
        $param["col"]["affil"] = $fb->form("affil");
        $param["col"]["size"] = $fb->form("wid_size") . "*" . $fb->form("vert_size");
        $param["col"]["search_check"] = $fb->form("name") . "|" . $fb->form("dvs") . "|" . $fb->form("color") . "|" . $fb->form("basisweight") . $fb->form("basisweight_unit");
        $param["col"]["crtr_unit"] = $fb->form("crtr_unit");

        if ($seqno == "") {
            $mpcode = intval($mpcode) + 1;
            $param["col"]["mpcode"] = $mpcode;
            echo $prdtBasicRegiDAO->insertData($conn, $param);

            // 2016-05-31 엄준현 기본가격 입력부분 추가
            unset($param);
            $param["table"] = "prdt_paper_price";
            $param["col"]["basic_price"]       = '0';
            $param["col"]["sell_rate"]         = '0';
            $param["col"]["sell_aplc_price"]   = '0';
            $param["col"]["sell_price"]        = '0';
            $param["col"]["prdt_paper_mpcode"] = $mpcode;
            $prdtBasicRegiDAO->insertData($conn, $param);
        } else {
            $param["prk"] = "prdt_paper_seqno";
            $param["prkVal"] = $seqno;
            echo $prdtBasicRegiDAO->updateData($conn, $param);
        }
    } else {
        echo "over";
    }

//출력정보
} else if ($select_el == "output") {

    //사이즈 => 출력명, 출력판 변경시
    $param = array();
    $param["table"] = "prdt_output_info";
    $param["col"] = "output_name, output_board_dvs";
    $param["where"]["prdt_output_info_seqno"] = $seqno;

    $rs = $prdtBasicRegiDAO->selectData($conn, $param);

    $pre_output_name = $rs->fields["output_name"];
    $pre_board_dvs = $rs->fields["output_board_dvs"];

    $param = array();
    $param["table"] = "prdt_stan";
    $param["col"] = "prdt_stan_seqno";
    $param["where"]["output_name"] = $pre_output_name;
    $param["where"]["output_board_dvs"] = $pre_board_dvs;

    $rs = $prdtBasicRegiDAO->selectData($conn, $param);

    while ($rs && !$rs->EOF) {
        
        $param = array();
        $param["table"] = "prdt_stan";
        $param["col"]["output_name"] = $fb->form("output_name");
        $param["col"]["output_board_dvs"] = $fb->form("output_board_dvs");
        $param["prk"] = "prdt_stan_seqno";
        $param["prkVal"] = $rs->fields["prdt_stan_seqno"];

        $prdtBasicRegiDAO->updateData($conn, $param);
        
        $rs->moveNext();
    }

    $param = array();
    $param["table"] = "prdt_output_info";
    $param["col"] = "MAX(mpcode) AS mpcode";
    $mpcode_rs = $prdtBasicRegiDAO->selectData($conn, $param);

    $mpcode = $mpcode_rs->fields["mpcode"];

    if ($mpcode == "" || $mpcode == NULL) {
        $mpcode = 0;
    }

    if ($mpcode_rs->EOF == 1) {
        $mpcode = 0;
    }
 
    $param = array();
    $param["table"] = "prdt_output_info";
    $param["col"] = "output_name, output_board_dvs, affil, size";
    $param["where"]["output_name"] = $fb->form("output_name");
    $param["where"]["output_board_dvs"] = $fb->form("output_board_dvs");

    $rs = $prdtBasicRegiDAO->selectData($conn, $param);

    //중복된 값이 없으면
    if ($rs->EOF == 1 || $rs->fields["size"] != $fb->form("wid_size") . "*" . $fb->form("vert_size")) {
        $param = array();
        $param["table"] = "prdt_output_info";
        $param["col"]["output_name"] = $fb->form("output_name");
        $param["col"]["output_board_dvs"] = $fb->form("output_board_dvs");
        $param["col"]["affil"] = $fb->form("affil");
        $param["col"]["size"] = $fb->form("wid_size") . "*" . $fb->form("vert_size");

        if ($seqno == "") {
            $param["col"]["mpcode"] = $mpcode + 1;
            echo $prdtBasicRegiDAO->insertData($conn, $param);
        } else {
            $param["prk"] = "prdt_output_info_seqno";
            $param["prkVal"] = $seqno;
            echo $prdtBasicRegiDAO->updateData($conn, $param);
        }
    } else {
        echo "over";
    }

//사이즈
} else if ($select_el == "size") {

    $param = array();
    $param["table"] = "prdt_stan";
    $param["col"] = "sort, name, typ, output_name,
        output_board_dvs, cut_wid_size, cut_vert_size,
        work_wid_size, work_vert_size, design_wid_size,
        design_vert_size, tomson_wid_size, tomson_vert_size";
    $param["where"]["sort"] = $fb->form("sort");
    $param["where"]["name"] = $fb->form("name");
    $param["where"]["typ"] = $fb->form("typ");
    $param["where"]["affil"] = $fb->form("affil");
    $param["where"]["output_name"] = $fb->form("output_name");
    $param["where"]["output_board_dvs"] = $fb->form("output_board_dvs");
    $param["where"]["cut_wid_size"] = $fb->form("cut_wid_size");
    $param["where"]["cut_vert_size"] = $fb->form("cut_vert_size");
    $param["where"]["work_wid_size"] = $fb->form("work_wid_size");
    $param["where"]["work_vert_size"] = $fb->form("work_vert_size");
    $param["where"]["design_wid_size"] = $fb->form("design_wid_size");
    $param["where"]["design_vert_size"] = $fb->form("design_vert_size");
    $param["where"]["tomson_wid_size"] = $fb->form("tomson_wid_size");
    $param["where"]["tomson_vert_size"] = $fb->form("tomson_vert_size");

    $rs = $prdtBasicRegiDAO->selectData($conn, $param);

    //중복된 값이 없으면
    if ($rs->EOF == 1) {
        $param = array();
        $param["table"] = "prdt_stan";
        $param["col"]["sort"] = $fb->form("sort");
        $param["col"]["name"] = $fb->form("name");
        $param["col"]["typ"] = $fb->form("typ");
        $param["col"]["affil"] = $fb->form("affil");
        $param["col"]["output_name"] = $fb->form("output_name");
        $param["col"]["output_board_dvs"] = $fb->form("output_board_dvs");
        $param["col"]["cut_wid_size"] = $fb->form("cut_wid_size");
        $param["col"]["cut_vert_size"] = $fb->form("cut_vert_size");
        $param["col"]["work_wid_size"] = $fb->form("work_wid_size");
        $param["col"]["work_vert_size"] = $fb->form("work_vert_size");
        $param["col"]["design_wid_size"] = $fb->form("design_wid_size");
        $param["col"]["design_vert_size"] = $fb->form("design_vert_size");
        $param["col"]["tomson_wid_size"] = $fb->form("tomson_wid_size");
        $param["col"]["tomson_vert_size"] = $fb->form("tomson_vert_size");

        if ($seqno == "") {
            echo $prdtBasicRegiDAO->insertData($conn, $param);
        } else {
            $param["prk"] = "prdt_stan_seqno";
            $param["prkVal"] = $seqno;
            echo $prdtBasicRegiDAO->updateData($conn, $param);
        }
    } else {
        echo "over";
    }

//인쇄정보
} else if ($select_el == "print") {
 
    //사이즈 => 출력명, 출력판 변경시
    $param = array();
    $param["table"] = "prdt_print_info";
    $param["col"] = "print_name, purp_dvs";
    $param["where"]["prdt_print_info_seqno"] = $seqno;

    $rs = $prdtBasicRegiDAO->selectData($conn, $param);

    $pre_print_name = $rs->fields["print_name"];
    $pre_purp_dvs = $rs->fields["purp_dvs"];

    $param = array();
    $param["table"] = "prdt_print";
    $param["col"] = "prdt_print_seqno";
    $param["where"]["print_name"] = $pre_print_name;
    $param["where"]["purp_dvs"] = $pre_purp_dvs;

    $rs = $prdtBasicRegiDAO->selectData($conn, $param);

    while ($rs && !$rs->EOF) {
        
        $param = array();
        $param["table"] = "prdt_print";
        $param["col"]["print_name"] = $fb->form("print_name");
        $param["col"]["purp_dvs"] = $fb->form("purp_dvs");
        $param["prk"] = "prdt_print_seqno";
        $param["prkVal"] = $rs->fields["prdt_print_seqno"];

        $prdtBasicRegiDAO->updateData($conn, $param);
        
        $rs->moveNext();
    }

    $param = array();
    $param["table"] = "prdt_print_info";
    $param["col"] = "MAX(mpcode) AS mpcode";
    $mpcode_rs = $prdtBasicRegiDAO->selectData($conn, $param);

    $mpcode = $mpcode_rs->fields["mpcode"];

    if ($mpcode == "" || $mpcode == NULL) {
        $mpcode = 0;
    }

    if ($mpcode_rs->EOF == 1) {
        $mpcode = 0;
    }
 
    $param = array();
    $param["table"] = "prdt_print_info";
    $param["col"] = "print_name, purp_dvs, cate_sortcode, affil";
    $param["where"]["print_name"] = $fb->form("print_name");
    $param["where"]["purp_dvs"] = $fb->form("purp_dvs");

    $rs = $prdtBasicRegiDAO->selectData($conn, $param);

    //중복된 값이 없으면
    if ($rs->EOF == 1 || $rs->fields["cate_sortcode"] != $fb->form("cate_sortcode") 
            || $rs->fields["crtr_unit"] != $fb->form("crtr_unit")
            || $rs->fields["affil"] != $fb->form("affil")) {
        $param = array();
        $param["table"] = "prdt_print_info";
        $param["col"]["print_name"] = $fb->form("print_name");
        $param["col"]["purp_dvs"] = $fb->form("purp_dvs");
        $param["col"]["cate_sortcode"] = $fb->form("cate_sortcode");
        $param["col"]["affil"] = $fb->form("affil");
        $param["col"]["crtr_unit"] = $fb->form("crtr_unit");

        if ($seqno == "") {
            $param["col"]["mpcode"] = $mpcode + 1;
            echo $prdtBasicRegiDAO->insertData($conn, $param);
        } else {
            $param["prk"] = "prdt_print_info_seqno";
            $param["prkVal"] = $seqno;
            echo $prdtBasicRegiDAO->updateData($conn, $param);
        }
    } else {
        echo "over";
    }

//인쇄도수
} else if ($select_el == "tmpt") {

    $param = array();
    $param["table"] = "prdt_print";
    $param["col"] = "sort, name, print_name, purp_dvs, side_dvs, 
        beforeside_tmpt, aftside_tmpt, add_tmpt, tot_tmpt";
    $param["where"]["sort"] = $fb->form("sort");
    $param["where"]["name"] = $fb->form("name");
    $param["where"]["print_name"] = $fb->form("print_name");
    $param["where"]["purp_dvs"] = $fb->form("purp_dvs");
    $param["where"]["side_dvs"] = $fb->form("side_dvs");
    $param["where"]["beforeside_tmpt"] = $fb->form("beforeside_tmpt");
    $param["where"]["aftside_tmpt"] = $fb->form("aftside_tmpt");
    $param["where"]["add_tmpt"] = $fb->form("add_tmpt");
    $param["where"]["tot_tmpt"] = $fb->form("tot_tmpt");
    $param["where"]["output_board_amt"] = $fb->form("output_board_amt");

    $rs = $prdtBasicRegiDAO->selectData($conn, $param);

    //중복된 값이 없으면
    if ($rs->EOF == 1) {
        $param = array();
        $param["table"] = "prdt_print";
        $param["col"]["sort"] = $fb->form("sort");
        $param["col"]["name"] = $fb->form("name");
        $param["col"]["print_name"] = $fb->form("print_name");
        $param["col"]["purp_dvs"] = $fb->form("purp_dvs");
        $param["col"]["side_dvs"] = $fb->form("side_dvs");
        $param["col"]["beforeside_tmpt"] = $fb->form("beforeside_tmpt");
        $param["col"]["aftside_tmpt"] = $fb->form("aftside_tmpt");
        $param["col"]["add_tmpt"] = $fb->form("add_tmpt");
        $param["col"]["tot_tmpt"] = $fb->form("tot_tmpt");
        $param["col"]["output_board_amt"] = $fb->form("output_board_amt");

        if ($seqno == "") {
            echo $prdtBasicRegiDAO->insertData($conn, $param);
        } else {
            $param["prk"] = "prdt_print_seqno";
            $param["prkVal"] = $seqno;
            echo $prdtBasicRegiDAO->updateData($conn, $param);
        }
    } else {
        echo "over";
    }

//후공정
} else if ($select_el == "after") {

    $param = array();
    $param["table"] = "prdt_after";
    $param["col"] = "after_name, depth1, depth2, depth3";
    $param["where"]["after_name"] = $fb->form("after_name");
    $param["where"]["depth1"] = $fb->form("depth1");
    $param["where"]["depth2"] = $fb->form("depth2");
    $param["where"]["depth3"] = $fb->form("depth3");

    $rs = $prdtBasicRegiDAO->selectData($conn, $param);

    //중복된 값이 없으면
    if ($rs->EOF == 1) {
        $param = array();
        $param["table"] = "prdt_after";
        $param["col"]["after_name"] = $fb->form("after_name");
        $param["col"]["depth1"] = $fb->form("depth1");
        $param["col"]["depth2"] = $fb->form("depth2");
        $param["col"]["depth3"] = $fb->form("depth3");

        if ($seqno == "") {
            echo $prdtBasicRegiDAO->insertData($conn, $param);
        } else {
            $param["prk"] = "prdt_after_seqno";
            $param["prkVal"] = $seqno;
            echo $prdtBasicRegiDAO->updateData($conn, $param);
        }
    } else {
        echo "over";
    }

//옵션
} else if ($select_el == "opt") {
 
    $param = array();
    $param["table"] = "prdt_opt";
    $param["col"] = "opt_name, depth1, depth2, depth3";
    $param["where"]["opt_name"] = $fb->form("opt_name");
    $param["where"]["depth1"] = $fb->form("depth1");
    $param["where"]["depth2"] = $fb->form("depth2");
    $param["where"]["depth3"] = $fb->form("depth3");

    $rs = $prdtBasicRegiDAO->selectData($conn, $param);

    //중복된 값이 없으면
    if ($rs->EOF == 1) {
        $param = array();
        $param["table"] = "prdt_opt";
        $param["col"]["opt_name"] = $fb->form("opt_name");
        $param["col"]["depth1"] = $fb->form("depth1");
        $param["col"]["depth2"] = $fb->form("depth2");
        $param["col"]["depth3"] = $fb->form("depth3");

        if ($seqno == "") {
            echo $prdtBasicRegiDAO->insertData($conn, $param);
        } else {
            $param["prk"] = "prdt_opt_seqno";
            $param["prkVal"] = $seqno;
            echo $prdtBasicRegiDAO->updateData($conn, $param);
        }
    } else {
        echo "over";
    }

//실사후공정
} else if ($select_el == "ao_after") {

    $param = array();
    $param["table"] = "ao_after";
    $param["col"] = "after_name, depth1, depth2, depth3";
    $param["where"]["after_name"] = $fb->form("after_name");
    $param["where"]["depth1"] = $fb->form("depth1");
    $param["where"]["depth2"] = $fb->form("depth2");
    $param["where"]["depth3"] = $fb->form("depth3");
    $param["where"]["unitprice"] = $fb->form("unitprice");

    $rs = $prdtBasicRegiDAO->selectData($conn, $param);

    //중복된 값이 없으면
    if ($rs->EOF == 1) {
        $param = array();
        $param["table"] = "ao_after";
        $param["col"]["after_name"] = $fb->form("after_name");
        $param["col"]["depth1"] = $fb->form("depth1");
        $param["col"]["depth2"] = $fb->form("depth2");
        $param["col"]["depth3"] = $fb->form("depth3");
        $param["col"]["unitprice"] = $fb->form("unitprice");

        if ($seqno == "") {
            echo $prdtBasicRegiDAO->insertData($conn, $param);
        } else {
            $param["prk"] = "ao_after_seqno";
            $param["prkVal"] = $seqno;
            echo $prdtBasicRegiDAO->updateData($conn, $param);
        }
    } else {
        echo "over";
    }

//실사옵션
} else if ($select_el == "ao_opt") {
 
    $param = array();
    $param["table"] = "ao_opt";
    $param["col"] = "opt_name, depth1, depth2, depth3";
    $param["where"]["opt_name"] = $fb->form("opt_name");
    $param["where"]["depth1"] = $fb->form("depth1");
    $param["where"]["depth2"] = $fb->form("depth2");
    $param["where"]["depth3"] = $fb->form("depth3");
    $param["where"]["unitprice"] = $fb->form("unitprice");

    $rs = $prdtBasicRegiDAO->selectData($conn, $param);

    //중복된 값이 없으면
    if ($rs->EOF == 1) {
        $param = array();
        $param["table"] = "ao_opt";
        $param["col"]["opt_name"] = $fb->form("opt_name");
        $param["col"]["depth1"] = $fb->form("depth1");
        $param["col"]["depth2"] = $fb->form("depth2");
        $param["col"]["depth3"] = $fb->form("depth3");
        $param["col"]["unitprice"] = $fb->form("unitprice");

        if ($seqno == "") {
            echo $prdtBasicRegiDAO->insertData($conn, $param);
        } else {
            $param["prk"] = "ao_opt_seqno";
            $param["prkVal"] = $seqno;
            echo $prdtBasicRegiDAO->updateData($conn, $param);
        }
    } else {
        echo "over";
    }
}

$conn->CompleteTrans();
$conn->close();
?>
