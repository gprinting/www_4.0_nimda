<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/PrdtPriceListDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$util = new ErpCommonUtil();
$dao = new PrdtPriceListDAO();

// 공통사용 정보
$val = $util->rmComma($fb->form("val"));
$val = ($val[0] === '.') ? '0' . $val : $val; // 소수점 처리
$dvs = $fb->form("dvs");
$cate_sortcode = $fb->form("cate_sortcode");
$sell_site     = $fb->form("sell_site");
$etprs_dvs     = $fb->form("etprs_dvs");
$mono_yn       = intval($fb->form("mono_yn"));
$member_seqno  = $fb->form("member_seqno");
$min_amt       = intval($fb->form("min_amt"));
$max_amt       = intval($fb->form("max_amt"));

$table_name = "ply_price";
// 개별수정시 넘어오는 정보
$price_seqno = $fb->form("price_seqno");

if (empty($price_seqno) === true) {
    // 일괄수정
    $paper_mpcode = $fb->form("paper_mpcode");
    $stan_info    = explode('!', $fb->form("stan_info"));

    $stan_name   = $stan_info[0];
    $stan_typ    = $stan_info[1];

    $param = array();
    $param["cate_sortcode"] = $cate_sortcode;
    $param["typ"]           = $stan_typ;
    $param["name"]          = $stan_name;

    $stan_mpcode = $dao->selectCateSizeInfo($conn, "MPCODE", $param)
                       ->fields["mpcode"];

    //* 인쇄 맵핑코드 검색
    $bef_print_tmpt     = $fb->form("bef_print_tmpt");
    $bef_add_print_tmpt = $fb->form("bef_add_print_tmpt");
    $aft_print_tmpt     = $fb->form("aft_print_tmpt");
    $aft_add_print_tmpt = $fb->form("aft_add_print_tmpt");

    unset($fb);
    unset($param);
    $param["cate_sortcode"] = $cate_sortcode;
    // 전면
    $param["tmpt"] = $bef_print_tmpt;
    $rs = $dao->selectCatePrintMpcode($conn, $param);
    $bef_print_mpcode = $rs->fields["mpcode"];
    // 전면추가
    if ($bef_add_print_tmpt !== '-') {
        $param["tmpt"] = $bef_add_print_tmpt;
        $rs = $dao->selectCatePrintMpcode($conn, $param);
        $bef_add_print_mpcode = $rs->fields["mpcode"];
    }
    // 후면
    if ($bef_add_print_tmpt !== '-') {
        $param["tmpt"] = $aft_print_tmpt;
        $rs = $dao->selectCatePrintMpcode($conn, $param);
        $aft_print_mpcode = $rs->fields["mpcode"];
    }
    // 후면추가
    if ($bef_add_print_tmpt !== '-') {
        $param["tmpt"] = $aft_add_print_tmpt;
        $rs = $dao->selectCatePrintMpcode($conn, $param);
        $aft_add_print_mpcode = $rs->fields["mpcode"];
    }

    unset($rs);
    unset($param);

    //* 가격 업데이트 대상 검색
    $common_param = array();
    $common_param["member_seqno"]  = $member_seqno;
    $common_param["cate_sortcode"] = $cate_sortcode;
    $common_param["paper_mpcode"]  = $paper_mpcode;
    $common_param["stan_mpcode"]   = $stan_mpcode;
    $common_param["bef_print_mpcode"]     = intval($bef_print_mpcode);
    $common_param["bef_add_print_mpcode"] = intval($bef_add_print_mpcode);
    $common_param["aft_print_mpcode"]     = intval($aft_print_mpcode);
    $common_param["aft_add_print_mpcode"] = intval($aft_add_print_mpcode);
    $common_param["min_amt"] = $min_amt;
    $common_param["max_amt"] = $max_amt;

    $param = $common_param;

    $rs = $dao->selectAmtMemberCateSale($conn, $param);
    unset($param);

    $is_insert = true;
    if ($rs->EOF) {
        $is_insert = false;
    }

    // 입력된 수량 안된수량 비교하기 위해 바깥으로 이동
    $param["table_name"] = $table_name;
    $param["cate_sortcode"] = $cate_sortcode;
    $amt_rs = $dao->selectCateAmt($conn, $param);
    $amt_arr = array();

    while ($amt_rs && !$amt_rs->EOF) {
        $amt = $amt_rs->fields["amt"];

        // 기입력된 값이 있을 경우 min, max 제한
        if ($is_insert) {
            if ($min_amt <= $amt && $amt <= $max_amt) {
                $amt_arr[$amt] = $amt;
            }
        } else {
            $amt_arr[$amt] = $amt;
        }

        $amt_rs->MoveNext();
    }
    unset($amt_rs);
    unset($param);

    if ($is_insert) {
        $param["member_seqno"] = $member_seqno;
        $param["dvs"]          = $dvs;
        $param["val"]          = $val;
        $param["amt_arr"]      = $amt_arr;
        $amt_arr = updateAmtMemberCateSale($conn, $dao, $rs, $param);

        if ($amt_arr === false) {
            goto ERR;
        }
    } else {
        $rate = ($dvs === 'R') ? $val : 0;
        $aplc_price = ($dvs === 'A') ? $val : 0;

        $param = $common_param;

        $param["rate"]       = $rate;
        $param["aplc_price"] = $aplc_price;
        $param["amt_arr"]    = $amt_arr;

        $amt_arr = insertAmtMemberCateSale($conn, $dao, $param);
        unset($param);

        if ($amt_arr === false) {
            goto ERR;
        }
    }


} else {
    // 개별수정
    if ($price_seqno === "-1") {
        goto ERR;
    }

    //* 가격 업데이트 대상 검색
    $param["price_seqno"]   = $price_seqno;

    $rs = $dao->selectAmtMemberCateSale($conn, $param);

    unset($param);

    $rate =
        ($dvs === 'R') ? $val : $rs->fields["rate"];
    $aplc_price  =
        ($dvs === 'A') ? $val : $rs->fields["aplc_price"];

    $param["price_seqno"] = $price_seqno;
    $param["rate"]        = $rate;
    $param["aplc_price"]  = $aplc_price;

    $update_ret = $dao->updateAmtMemberCateSale($conn, $param);

    if (!$update_ret) {
        goto ERR;
    }
}

echo "T";
$conn->close();
exit;

ERR :
    $conn->CompleteTrans();
    $conn->close();
    echo "";
    exit;

/******************************************************************************
 *************************** 함수영역
 ******************************************************************************/

function insertAmtMemberCateSale($conn, $dao, $param) {
    $amt_arr = $param["amt_arr"];
    unset($param["amt_arr"]);

    $conn->StartTrans();
    foreach ($amt_arr as $amt) {
        $param["amt"] = $amt;
        unset($amt_arr[$amt]);

        $insert_ret = $dao->insertAmtMemberCateSale($conn, $param);

        if (!$insert_ret) {
            return false;
        }
    }
    $conn->CompleteTrans();

    return $amt_arr;
}

function updateAmtMemberCateSale($conn, $dao, $rs, $param) {
    $dvs     = $param["dvs"];
    $val     = $param["val"];
    $amt_arr = $param["amt_arr"];

    unset($param["dvs"]);
    unset($param["val"]);
    unset($param["amt_arr"]);

    $conn->StartTrans();
    while (!$rs->EOF) {
        $price_seqno = $rs->fields["price_seqno"];
        $rate =
            ($dvs === 'R') ? $val : $rs->fields["rate"];
        $aplc_price  =
            ($dvs === 'A') ? $val : $rs->fields["aplc_price"];
        $amt = $rs->fields["amt"];

        $param["price_seqno"] = $price_seqno;
        $param["rate"]        = $rate;
        $param["aplc_price"]  = $aplc_price;

        $update_ret = $dao->updateAmtMemberCateSale($conn, $param);

        unset($amt_arr[$amt]);

        if (!$update_ret) {
            return false;
        }

        $rs->MoveNext();
    }
    $conn->CompleteTrans();

    return $amt_arr;
}
?>
