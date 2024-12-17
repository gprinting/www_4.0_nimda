<?
define("INC_PATH", $_SERVER["INC"]);
/*
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016/11/08 엄준현 수정(가격테이블명 수정)
 *============================================================================
 *
 */
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_mng/PrdtBasicRegiDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$prdtBasicRegiDAO = new PrdtBasicRegiDAO();

//$conn->debug = 1;

$conn->StartTrans();

$select_el = $fb->form("selectEl");

$check = 1;

//종이(상품종이)
if ($select_el == "paper") {

    //상품종이 검색
    $param = array();
    $param["table"] = "prdt_paper";
    $param["col"]  = "name,";
    $param["col"] .= "sort,";
    $param["col"] .= "dvs,";
    $param["col"] .= "color,";
    $param["col"] .= "basisweight,";
    $param["col"] .= "basisweight_unit,";
    $param["col"] .= "mpcode";
    $param["where"]["prdt_paper_seqno"] = $fb->form("seqno");

    $sel_rs = $prdtBasicRegiDAO->selectData($conn, $param); 
 
    //상품종이금액 삭제
    $param = array();
    $param["table"] = "prdt_paper_price";
    $param["prk"] = "prdt_paper_mpcode";
    $param["prkVal"] = $sel_rs->fields["mpcode"];

    $rs = $prdtBasicRegiDAO->deleteData($conn, $param);

    if (!$rs) {
        $check = 0;
        echo $check;
        exit;
    }

    //카테고리종이 검색
    $param = array();
    $param["table"] = "cate_paper";
    $param["col"] = "cate_paper_seqno, mpcode";
    $param["where"]["name"] = $sel_rs->fields["name"];
    $param["where"]["sort"] = $sel_rs->fields["sort"];
    $param["where"]["dvs"] = $sel_rs->fields["dvs"];
    $param["where"]["color"] = $sel_rs->fields["color"];
    $param["where"]["basisweight"] = $sel_rs->fields["basisweight"] .
                                     $sel_rs->fields["basisweight_unit"];

    $sel_rs = $prdtBasicRegiDAO->selectData($conn, $param); 

    while ($sel_rs && !$sel_rs->EOF) {
  
        //카테고리 종이 삭제
        $param = array();
        $param["table"] = "cate_paper";
        $param["prk"] = "cate_paper_seqno";
        $param["prkVal"] = $sel_rs->fields["cate_paper_seqno"];

        $rs = $prdtBasicRegiDAO->deleteData($conn, $param);

        if (!$rs) {
            $check = 0;
            echo $check;
            exit;
        }
 
        //합판금액 삭제
        $check = deletePlyPrice($conn,
                                $prdtBasicRegiDAO,
                                "cate_paper_mpcode",
                                $sel_rs->fields["mpcode"]);

        if ($check === 0) {
            $check = 0;
            echo $check;
            exit;
        }

        $sel_rs->MoveNext();
    }
 
    //상품종이 삭제
    $param = array();
    $param["table"] = "prdt_paper";
    $param["prk"] = "prdt_paper_seqno";
    $param["prkVal"] = $fb->form("seqno");

    $rs = $prdtBasicRegiDAO->deleteData($conn, $param);

    if (!$rs) {
        $check = 0; 
    }

//출력(출력정보)
} else if ($select_el == "output") {

    //출력정보 검색
    $param = array();
    $param["table"] = "prdt_output_info";
    $param["col"] = "output_name, output_board_dvs, mpcode";
    $param["where"]["prdt_output_info_seqno"] = $fb->form("seqno");

    $sel_rs = $prdtBasicRegiDAO->selectData($conn, $param); 

    //상품규격금액 삭제
    $param = array();
    $param["table"] = "prdt_stan_price";
    $param["prk"] = "prdt_output_info_mpcode";
    $param["prkVal"] =  $sel_rs->fields["mpcode"];

    $rs = $prdtBasicRegiDAO->deleteData($conn, $param);

    if (!$rs) {
        $check = 0; 
    }

    //상품규격 검색
    $param = array();
    $param["table"] = "prdt_stan";
    $param["col"] = "prdt_stan_seqno";
    $param["where"]["output_name"] = $sel_rs->fields["output_name"];
    $param["where"]["output_board_dvs"] = $sel_rs->fields["output_board_dvs"];

    $sel_rs = $prdtBasicRegiDAO->selectData($conn, $param); 

    while ($sel_rs && !$sel_rs->EOF) {

        //카테고리 규격 검색
        $param = array();
        $param["table"] = "cate_stan";
        $param["col"] = "mpcode";
        $param["where"]["prdt_stan_seqno"] = $sel_rs->fields["prdt_stan_seqno"];

        $seq_rs = $prdtBasicRegiDAO->selectData($conn, $param); 

        while ($seq_rs && !$seq_rs->EOF) {
            //합판금액 삭제
            $check = deletePlyPrice($conn,
                                    $prdtBasicRegiDAO,
                                    "cate_stan_mpcode",
                                    $seq_rs->fields["mpcode"]);

            if ($check === 0) {
                $check = 0;
                echo $check;
                exit;
            }

            $seq_rs->MoveNext();
        }

        //카테고리규격 삭제
        $param = array();
        $param["table"] = "cate_stan";
        $param["prk"] = "prdt_stan_seqno";
        $param["prkVal"] = $sel_rs->fields["prdt_stan_seqno"];

        $rs = $prdtBasicRegiDAO->deleteData($conn, $param);

        if (!$rs) {
            $check = 0;
            echo $check;
            exit;
        }

        //상품규격 삭제
        $param = array();
        $param["table"] = "prdt_stan";
        $param["prk"] = "prdt_stan_seqno";
        $param["prkVal"] = $sel_rs->fields["prdt_stan_seqno"];

        $rs = $prdtBasicRegiDAO->deleteData($conn, $param);

        if (!$rs) {
            $check = 0;
            echo $check;
            exit;
        }

        $sel_rs->moveNext();
    }

    //출력정보 삭제
    $param = array();
    $param["table"] = "prdt_output_info";
    $param["prk"] = "prdt_output_info_seqno";
    $param["prkVal"] = $fb->form("seqno");
 
    $rs = $prdtBasicRegiDAO->deleteData($conn, $param);

    if (!$rs) {
        $check = 0; 
    }

//사이즈(상품규격)
} else if ($select_el == "size") {

    //카테고리 규격 검색
    $param = array();
    $param["table"] = "cate_stan";
    $param["col"] = "mpcode";
    $param["where"]["prdt_stan_seqno"] = $fb->form("seqno");

    $seq_rs = $prdtBasicRegiDAO->selectData($conn, $param); 

    while ($seq_rs && !$seq_rs->EOF) {
        //합판금액 삭제
        $check = deletePlyPrice($conn,
                                $prdtBasicRegiDAO,
                                "cate_stan_mpcode",
                                $seq_rs->fields["mpcode"]);

        if ($check === 0) {
            $check = 0;
            echo $check;
            exit;
        }

        $seq_rs->MoveNext();
    }

    //카테고리규격 삭제
    $param = array();
    $param["table"] = "cate_stan";
    $param["prk"] = "prdt_stan_seqno";
    $param["prkVal"] = $fb->form("seqno");

    $rs = $prdtBasicRegiDAO->deleteData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

    //상품규격 삭제
    $param = array();
    $param["table"] = "prdt_stan";
    $param["prk"] = "prdt_stan_seqno";
    $param["prkVal"] = $fb->form("seqno");

    $rs = $prdtBasicRegiDAO->deleteData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

//인쇄(인쇄정보)
} else if ($select_el == "print") {

    //인쇄정보 검색
    $param = array();
    $param["table"] = "prdt_print_info";
    $param["col"] = "print_name, purp_dvs, mpcode";
    $param["where"]["prdt_print_info_seqno"] = $fb->form("seqno");

    $sel_rs = $prdtBasicRegiDAO->selectData($conn, $param); 

    //상품인쇄금액 삭제
    $param = array();
    $param["table"] = "prdt_print_price";
    $param["prk"] = "prdt_print_info_mpcode";
    $param["prkVal"] =  $sel_rs->fields["mpcode"];

    $rs = $prdtBasicRegiDAO->deleteData($conn, $param);

    if (!$rs) {
        $check = 0; 
    }

    //상품인쇄 검색
    $param = array();
    $param["table"] = "prdt_print";
    $param["col"] = "prdt_print_seqno";
    $param["where"]["print_name"] = $sel_rs->fields["print_name"];
    $param["where"]["purp_dvs"] = $sel_rs->fields["purp_dvs"];

    $sel_rs = $prdtBasicRegiDAO->selectData($conn, $param); 

    while ($sel_rs && !$sel_rs->EOF) {

        //카테고리 인쇄 검색
        $param = array();
        $param["table"] = "cate_print";
        $param["col"] = "mpcode";
        $param["where"]["prdt_print_seqno"] = $sel_rs->fields["prdt_print_seqno"];

        $seq_rs = $prdtBasicRegiDAO->selectData($conn, $param); 

        while ($seq_rs && !$seq_rs->EOF) {
            //합판금액 삭제
            $check = deletePlyPrice($conn,
                                    $prdtBasicRegiDAO,
                                    "cate_beforeside_print_mpcode",
                                    $seq_rs->fields["mpcode"]);

            if ($check === 0) {
                $check = 0;
                echo $check;
                exit;
            }

            $check = deletePlyPrice($conn,
                                    $prdtBasicRegiDAO,
                                    "cate_beforeside_add_print_mpcode",
                                    $seq_rs->fields["mpcode"]);

            if ($check === 0) {
                $check = 0;
                echo $check;
                exit;
            }

            $check = deletePlyPrice($conn,
                                    $prdtBasicRegiDAO,
                                    "cate_aftside_print_mpcode",
                                    $seq_rs->fields["mpcode"]);

            if ($check === 0) {
                $check = 0;
                echo $check;
                exit;
            }

            $check = deletePlyPrice($conn,
                                    $prdtBasicRegiDAO,
                                    "cate_aftside_add_print_mpcode",
                                    $seq_rs->fields["mpcode"]);

            if ($check === 0) {
                $check = 0;
                echo $check;
                exit;
            }

            $seq_rs->MoveNext();
        }

        //카테고리인쇄 삭제
        $param = array();
        $param["table"] = "cate_print";
        $param["prk"] = "prdt_print_seqno";
        $param["prkVal"] = $sel_rs->fields["prdt_print_seqno"];

        $rs = $prdtBasicRegiDAO->deleteData($conn, $param);

        if (!$rs) {
            $check = 0;
            echo $check;
            exit;
        }

        //상품인쇄 삭제
        $param = array();
        $param["table"] = "prdt_print";
        $param["prk"] = "prdt_print_seqno";
        $param["prkVal"] = $sel_rs->fields["prdt_print_seqno"];

        $rs = $prdtBasicRegiDAO->deleteData($conn, $param);

        if (!$rs) {
            $check = 0;
            echo $check;
            exit;
        }

        $sel_rs->moveNext();
    }

    //인쇄정보 삭제
    $param = array();
    $param["table"] = "prdt_print_info";
    $param["prk"] = "prdt_print_info_seqno";
    $param["prkVal"] = $fb->form("seqno");
 
    $rs = $prdtBasicRegiDAO->deleteData($conn, $param);

    if (!$rs) {
        $check = 0; 
    }

//인쇄도수(상품인쇄)
} else if ($select_el == "tmpt") {

    //카테고리 인쇄 검색
    $param = array();
    $param["table"] = "cate_print";
    $param["col"] = "mpcode";
    $param["where"]["prdt_print_seqno"] = $fb->form("seqno");

    $seq_rs = $prdtBasicRegiDAO->selectData($conn, $param); 

    while ($seq_rs && !$seq_rs->EOF) {
        //합판금액 삭제
        $check = deletePlyPrice($conn,
                                $prdtBasicRegiDAO,
                                "cate_beforeside_print_mpcode",
                                $seq_rs->fields["mpcode"]);

        if ($check === 0) {
            $check = 0;
            echo $check;
            exit;
        }

        $check = deletePlyPrice($conn,
                                $prdtBasicRegiDAO,
                                "cate_beforeside_add_print_mpcode",
                                $seq_rs->fields["mpcode"]);

        if ($check === 0) {
            $check = 0;
            echo $check;
            exit;
        }

        $check = deletePlyPrice($conn,
                                $prdtBasicRegiDAO,
                                "cate_aftside_print_mpcode",
                                $seq_rs->fields["mpcode"]);

        if ($check === 0) {
            $check = 0;
            echo $check;
            exit;
        }

        $check = deletePlyPrice($conn,
                                $prdtBasicRegiDAO,
                                "cate_aftside_add_print_mpcode",
                                $seq_rs->fields["mpcode"]);

        if ($check === 0) {
            $check = 0;
            echo $check;
            exit;
        }

        $seq_rs->MoveNext();
    }

    //카테고리규격 삭제
    $param = array();
    $param["table"] = "cate_print";
    $param["prk"] = "prdt_print_seqno";
    $param["prkVal"] = $fb->form("seqno");

    $rs = $prdtBasicRegiDAO->deleteData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

    //상품규격 삭제
    $param = array();
    $param["table"] = "prdt_print";
    $param["prk"] = "prdt_print_seqno";
    $param["prkVal"] = $fb->form("seqno");

    $rs = $prdtBasicRegiDAO->deleteData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

//후공정(상품후공정)
} else if ($select_el == "after") {

    //카테고리 후공정 검색
    $param = array();
    $param["table"] = "cate_after";
    $param["col"] = "mpcode";
    $param["where"]["prdt_after_seqno"] = $fb->form("seqno");

    $seq_rs = $prdtBasicRegiDAO->selectData($conn, $param); 

    while ($seq_rs && !$seq_rs->EOF) {

        //카테고리 후공정 금액
        $param = array();
        $param["table"] = "cate_after_price";
        $param["prk"] = "cate_after_mpcode";
        $param["prkVal"] = $seq_rs->fields["mpcode"];

        $rs = $prdtBasicRegiDAO->deleteData($conn, $param);

        if (!$rs) {
            $check = 0;
            echo $check;
            exit;
        }

        $seq_rs->moveNext();
    }

    //카테고리 후공정 삭제
    $param = array();
    $param["table"] = "cate_after";
    $param["prk"] = "prdt_after_seqno";
    $param["prkVal"] = $fb->form("seqno");

    $rs = $prdtBasicRegiDAO->deleteData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

    //상품후공정 삭제
    $param = array();
    $param["table"] = "prdt_after";
    $param["prk"] = "prdt_after_seqno";
    $param["prkVal"] = $fb->form("seqno");

    $rs = $prdtBasicRegiDAO->deleteData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

//옵션(상품옵션)
} else if ($select_el == "opt") {

    //카테고리 옵션 검색
    $param = array();
    $param["table"] = "cate_opt";
    $param["col"] = "mpcode";
    $param["where"]["prdt_opt_seqno"] = $fb->form("seqno");

    $seq_rs = $prdtBasicRegiDAO->selectData($conn, $param); 

    while ($seq_rs && !$seq_rs->EOF) {

        //카테고리 옵션 금액
        $param = array();
        $param["table"] = "cate_opt_price";
        $param["prk"] = "cate_opt_mpcode";
        $param["prkVal"] = $seq_rs->fields["mpcode"];

        $rs = $prdtBasicRegiDAO->deleteData($conn, $param);

        if (!$rs) {
            $check = 0;
            echo $check;
            exit;
        }

        $seq_rs->moveNext();
    }

    //카테고리 옵션 삭제
    $param = array();
    $param["table"] = "cate_opt";
    $param["prk"] = "prdt_opt_seqno";
    $param["prkVal"] = $fb->form("seqno");

    $rs = $prdtBasicRegiDAO->deleteData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

    //상품 옵션 삭제
    $param = array();
    $param["table"] = "prdt_opt";
    $param["prk"] = "prdt_opt_seqno";
    $param["prkVal"] = $fb->form("seqno");

    $rs = $prdtBasicRegiDAO->deleteData($conn, $param);

    if (!$rs) {
        $check = 0;
    }
}

echo $check;
$conn->CompleteTrans();
$conn->Close();
exit;

/******************************************************************************
                            함수 영역
 *****************************************************************************/

/**
 * @brief 합판가격 테이블 정보 삭제
 *
 * @param $conn    = db connection
 * @param $dao     = 작업수행용 dao 객체
 * @param $prk     = 기본키 필드명
 * @param $prk_val = 기본키 필드값
 *
 * @return 쿼리실행결과
 */
function deletePlyPrice($conn, $dao, $prk, $prk_val) {
    //합판금액 굿프린팅 삭제
    $param = array();
    $param["table"] = "ply_price_gp_new";
    $param["prk"] = $prk;
    $param["prkVal"] = $prk_val;

    $rs = $dao->deleteData($conn, $param);

    if (!$rs) {
        return 0;
    }

    $param["table"] = "ply_price_gp_exist";
    $rs = $dao->deleteData($conn, $param);

    if (!$rs) {
        return 0;
    }

    //합판금액 디프린팅 삭제
    $param["table"] = "ply_price_dp_new";
    $rs = $dao->deleteData($conn, $param);

    if (!$rs) {
        return 0;
    }

    $param["table"] = "ply_price_dp_exist";
    $rs = $dao->deleteData($conn, $param);

    if (!$rs) {
        return 0;
    }

    return 1;
}
?>
