<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/AfterPriceListDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new AfterPriceListDAO();
$util = new ErpCommonUtil();

// 공통사용 정보
$val = $util->rmComma($fb->form("val"));
$val = ($val[0] === '.') ? '0' . $val : $val; // 소수점 처리
$dvs = $fb->form("dvs");
//$basic_yn     = $fb->form("basic_yn");
//$sell_site    = $fb->form("sell_site");
$member_seqno = $fb->form("member_seqno");
$mpcode       = $fb->form("mpcode");
$min_amt      = $fb->form("min_amt");
$max_amt      = $fb->form("max_amt");

// 개별수정시 넘어오는 정보
$price_seqno = $fb->form("price_seqno");

if (empty($price_seqno) === true) {
    // 일괄수정
    $param = array();
    //$param["basic_yn"]     = $basic_yn;
    //$param["sell_site"]    = $sell_site;
    $param["member_seqno"] = $member_seqno;
    $param["after_mpcode"] = $mpcode;
    $param["min_amt"]      = $min_amt;
    $param["max_amt"]      = $max_amt;

    $rs = $dao->selectMemberCateAftSalePrice($conn, $param);

    $is_insert = true;
    if ($rs->EOF) {
        $is_insert = false;
    }

    // 입력된 수량 안된수량 비교하기 위해 바깥으로 이동
    $amt_rs = $dao->selectCateAfterAmt($conn, $param);
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

    if ($is_insert) {
        $param["dvs"]     = $dvs;
        $param["val"]     = $val;
        $param["amt_arr"] = $amt_arr;
        $amt_arr = updateMemberCateAftSale($conn, $dao, $rs, $param);

        if ($amt_arr === false) {
            goto ERR;
        }
    } else {
        $rate = ($dvs === 'R') ? $val : 0;
        $aplc_price = ($dvs === 'A') ? $val : 0;

        $param["rate"]       = $rate;
        $param["aplc_price"] = $aplc_price;
        $param["amt_arr"]    = $amt_arr;

        $amt_arr = insertMemberCateAftSale($conn, $dao, $param);

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
    $param["price_seqno"]  = $price_seqno;

    $rs = $dao->selectMemberCateAftSalePrice($conn, $param);

    unset($param);

    $rate =
        ($dvs === 'R') ? $val : $rs->fields["rate"];
    $aplc_price  =
        ($dvs === 'A') ? $val : $rs->fields["aplc_price"];

    $param["price_seqno"] = $price_seqno;
    $param["rate"]        = $rate;
    $param["aplc_price"]  = $aplc_price;

    $update_ret = $dao->updateMemberCateAftSale($conn, $param);

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

function insertMemberCateAftSale($conn, $dao, $param) {
    $amt_arr = $param["amt_arr"];
    unset($param["amt_arr"]);

    $conn->StartTrans();
    foreach ($amt_arr as $amt) {
        $param["amt"] = $amt;
        unset($amt_arr[$amt]);

        $insert_ret = $dao->insertMemberCateAftSale($conn, $param);

        if (!$insert_ret) {
            return false;
        }
    }
    $conn->CompleteTrans();

    return $amt_arr;
}

function updateMemberCateAftSale($conn, $dao, $rs, $param) {
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

        $update_ret = $dao->updateMemberCateAftSale($conn, $param);

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
