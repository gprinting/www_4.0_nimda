<?php
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/esti_mng/EstiListDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new EstiListDAO();
$util = new CommonUtil();

$session = $fb->getSession();
$fb = $fb->getForm();

$state_arr = $session["state_arr"];
$typ_arr = ["cover", "inner1", "inner2", "inner3"];
$typ_ko = [
    "cover" => "표지"
    ,"inner1" => "내지1"
    ,"inner2" => "내지2"
    ,"inner3" => "내지3"
];

$esti_seqno = $fb["esti_seqno"];
$flattyp_yn = $fb["flattyp_yn"];
$memo       = $fb["memo"];

$origin_price = $util->rmComma($fb["origin_price"]);
$sale_rate    = $util->rmComma($fb["sale_rate"]);
$sale_price   = $util->rmComma($fb["sale_price"]);
$esti_price   = $util->rmComma($fb["esti_price"]);
$vat          = $util->rmComma($fb["vat"]);
$order_price  = $util->rmComma($fb["order_price"]);

//$conn->debug = 1;
$conn->StartTrans();

$param = [];
$param["esti_seqno"]   = $esti_seqno;
$param["memo"]         = $memo;
$param["origin_price"] = $origin_price;
$param["sale_rate"]    = $sale_rate;
$param["sale_price"]   = $sale_price;
$param["esti_price"]   = $esti_price;
$param["vat"]          = $vat;
$param["order_price"]  = $order_price;
$ret = $dao->updateEstiPrice($conn, $param);

if (!$ret || $conn->HasFailedTrans()) {
    $conn->FailTrans();
    $conn->RollbackTrans();
    goto ERR;
}

unset($param);
$param["esti_seqno"] = $esti_seqno;
foreach ($typ_arr as $typ) {
    $price_arr = $fb[$typ];

    if (empty($price_arr)) {
        continue;
    }

    $aft_arr = $price_arr["after"];

    $param["paper"]             = $price_arr["paper_mpcode"];
    $param["paper_tmpt"]        = $price_arr["paper_tmpt"];
    $param["paper_unitprice"]   = $price_arr["paper_unitprice"];
    $param["paper_machine_amt"] = $price_arr["paper_mach_count"];
    $param["paper_R_amt"]       = $price_arr["paper_r_count"];
    $param["paper_price"]       = $price_arr["paper_price"];
    $param["paper_note"]        = $price_arr["paper_note"];

    $param["output"]             = $price_arr["output_mpcode"];
    $param["output_tmpt"]        = $price_arr["output_tmpt"];
    $param["output_unitprice"]   = $price_arr["output_unitprice"];
    $param["output_machine_amt"] = $price_arr["output_mach_count"];
    $param["output_R_amt"]       = $price_arr["output_r_count"];
    $param["output_price"]       = $price_arr["output_price"];
    $param["output_note"]        = $price_arr["output_note"];

    $param["print"]             = $price_arr["print_mpcode"];
    $param["print_tmpt"]        = $price_arr["print_tmpt"];
    $param["print_unitprice"]   = $price_arr["print_unitprice"];
    $param["print_machine_amt"] = $price_arr["print_mach_count"];
    $param["print_R_amt"]       = $price_arr["print_r_count"];
    $param["print_price"]       = $price_arr["print_price"];
    $param["print_note"]        = $price_arr["print_note"];

    $param["typ"] = $typ_ko[$typ];
                                 
    if ($flattyp_yn === 'Y') {
        $ret = $dao->updateEstiDetailPrice($conn, $param);

        if (!$ret || $conn->HasFailedTrans()) {
            $conn->FailTrans();
            $conn->RollbackTrans();
            goto ERR;
        }

    } else {
        $ret = $dao->updateEstiDetailBrochurePrice($conn, $param);

        if (!$ret || $conn->HasFailedTrans()) {
            $conn->FailTrans();
            $conn->RollbackTrans();
            goto ERR;
        }
    }

    if (!empty($aft_arr)) {
        foreach ($aft_arr as $aft) {
            $aft_param = [];
            $aft_param["esti_after_history_seqno"] = $aft["seqno"];
            $aft_param["unitprice"]   = $aft["unitprice"];
            $aft_param["machine_amt"] = $aft["mach_count"];
            $aft_param["R_amt"]       = $aft["r_count"];
            $aft_param["price"]       = $aft["price"];
            $aft_param["note"]        = $aft["note"];

            $ret = $dao->updateEstiAfterPrice($conn, $aft_param);

            if (!$ret || $conn->HasFailedTrans()) {
                $conn->FailTrans();
                $conn->RollbackTrans();
                goto ERR;
            }
        }
    }
}

$param["state"]      = $state_arr["견적완료"];
$param["esti_mng"]   = $session["name"];
$param["esti_seqno"] = $esti_seqno;
$ret = $dao->updateEstiState($conn, $param);

if (!$ret || $conn->HasFailedTrans()) {
    $conn->FailTrans();
    $conn->RollbackTrans();
    goto ERR;
}

$conn->CompleteTrans();
goto END;

ERR:
    echo "-1";
    $conn->Close();
    exit;
END:
    echo "1";
    $conn->Close();
    exit;
