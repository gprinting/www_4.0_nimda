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
include_once(INC_PATH . '/com/nexmotion/common/util/ConnectionPool.inc');
include_once(INC_PATH . '/com/nexmotion/common/entity/FormBean.inc');
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_mng/PrdtItemRegiDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PrdtItemRegiDAO();

$check = 1;

//상품종이 일련번호
$seqno = explode(',', $fb->form("seqno"));
$table = $fb->form("table");
$price_table = "";

if ($table === "cate_size") {
    $table = "cate_stan";
} else if ($table === "cate_tmpt") {
    $table = "cate_print";
}

$param = array();
$param["table"] = $table;
$param["seqno"] = $fb->form("seqno");

//$conn->debug = 1;

$sel_rs = $dao->selectCateMpcode($conn, $param);

$conn->StartTrans();

while ($sel_rs && !$sel_rs->EOF) {

    //합판금액 삭제
    if ($table === "cate_print") {
        $check = deletePlyPrice($conn,
                                $dao,
                                "cate_beforeside_print_mpcode",
                                $seq_rs->fields["mpcode"]);

        if ($check === 0) {
            $conn->FailTrans();
            $conn->RollbackTrans();
            $conn->Close();

            $check = 0;
            echo $check;
            exit;
        }

        $check = deletePlyPrice($conn,
                                $dao,
                                "cate_beforeside_add_print_mpcode",
                                $seq_rs->fields["mpcode"]);

        if ($check === 0) {
            $conn->FailTrans();
            $conn->RollbackTrans();
            $conn->Close();

            $check = 0;
            echo $check;
            exit;
        }

        $check = deletePlyPrice($conn,
                                $dao,
                                "cate_aftside_print_mpcode",
                                $seq_rs->fields["mpcode"]);

        if ($check === 0) {
            $conn->FailTrans();
            $conn->RollbackTrans();
            $conn->Close();

            $check = 0;
            echo $check;
            exit;
        }

        $check = deletePlyPrice($conn,
                                $dao,
                                "cate_aftside_add_print_mpcode",
                                $seq_rs->fields["mpcode"]);

        if ($check === 0) {
            $conn->FailTrans();
            $conn->RollbackTrans();
            $conn->Close();

            $check = 0;
            echo $check;
            exit;
        }
    } else if ($table == "cate_after" || $table == "cate_opt") {
        //카테고리 후공정/옵션 가격 삭제
        $param = array();
        $param["table"] = $table . "_price";
        $param["prk"] = $table . "_mpcode";
        $param["prkVal"] = $sel_rs->fields["mpcode"];

        $rs = $dao->deleteData($conn, $param);

        if (!$rs) {
            $conn->FailTrans();
            $conn->RollbackTrans();
            $conn->Close();

            $check = 0;
            echo $check;
            exit;
        }
    } else if ($table == "cate_ao_after" || $table == "cate_ao_opt") {
        break;
    } else {
        $check = deletePlyPrice($conn,
                                $dao,
                                $table . "_mpcode",
                                $sel_rs->fields["mpcode"]);

        if ($check === 0) {
            $conn->FailTrans();
            $conn->RollbackTrans();
            $conn->Close();

            $check = 0;
            echo $check;
            exit;
        }
    }

    $sel_rs->moveNext();
}

$param = array();
$param["table"] = $table;
$param["prk"] = $table . "_seqno";
$param["prkVal"] = $seqno;

$rs = $dao->deleteMultiData($conn, $param);
 
if (!$rs) {
    $check = 0;
}

echo $check;
//$conn->FailTrans();
//$conn->RollbackTrans();
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
    $param["table"] = "ply_price";
    $param["prk"] = $prk;
    $param["prkVal"] = $prk_val;

    $rs = $dao->deleteData($conn, $param);

    if (!$rs) {
        return 0;
    }

    return 1;
}
?>
