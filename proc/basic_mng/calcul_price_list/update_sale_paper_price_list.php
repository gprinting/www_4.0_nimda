<?
define("INC_PATH", $_SERVER["INC"]);
/*
 *
 * Copyright (c) 2015-2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2016/11/06 엄준현 생성
 *=============================================================================
 *
 */
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/CalculPriceListDAO.inc');
include_once(INC_PATH . "/common_define/prdt_default_info.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$util = new ErpCommonUtil();
$dao = new CalculPriceListDAO();

// 공통사용 정보
$val = $fb->form("val");
$val = ($val[0] === '.') ? '0' . $val : $val; // 소수점 처리
$dvs = $fb->form("dvs");
$sell_site  = $fb->form("sell_site");

// 개별수정시 넘어오는 정보
$price_seqno = $fb->form("price_seqno");

if (empty($price_seqno) === true) {
    //$conn->debug = 1;
    // 일괄수정
    $cate_sortcode = $fb->form("cate_sortcode");
    $info          = explode('!', $fb->form("paper_info"));
    $affil         = $fb->form("paper_affil");
    $stan_mpcode   = $fb->form("stan_mpcode");
    $page_info     = explode('!', $fb->form("page_info"));

    //* 종이 정보에 해당하는 맵핑코드 검색
    $param = array();
    $param["cate_sortcode"] = $cate_sortcode;
    $param["name"]          = $info[0];
    $param["dvs"]           = $info[1];
    $param["color"]         = $info[2];
    $param["basisweight"]   = $info[3];

    $cate_paper_mpcode = $dao->selectCatePaperMpcode($conn, $param);

    //* 가격 업데이트 대상 검색
    unset($param);
    $param["sell_site"]         = $sell_site;
    $param["cate_sortcode"]     = $cate_sortcode;
    $param["cate_paper_mpcode"] = $cate_paper_mpcode;
    $param["cate_stan_mpcode"]  = $stan_mpcode;
    $param["typ"]               = $page_info[0];
    $param["page_amt"]          = $page_info[1];

    $rs = $dao->selectAmtPaperSale($conn, $param);

    $conn->StartTrans();
    // 입력값 없을경우 전체입력
    if ($rs->EOF) {
        $amt_arr = PrdtDefaultInfo::AMT[$cate_sortcode];

        if ($dvs === 'R') {
            $param["rate"] = $val;
            $param["aplc_price"] = 0;
        } else {
            $param["rate"] = 0;
            $param["aplc_price"] = $val;
        }

        $cate_flattyp_yn = $dao->selectCateInfo($conn,
                                            array("sortcode" => $cate_sortcode))
                               ->fields["flattyp_yn"];

        $page_amt = null;
        if ($cate_flattyp_yn === 'Y') {
            $page_amt = PrdtDefaultInfo::PAGE_INFO["FLAT"];
        } else {
            $page_amt = PrdtDefaultInfo::PAGE_INFO[$cate_sortcode];
        }

        foreach ($amt_arr as $amt) {
            $param["amt"] = $amt;

            foreach ($page_amt as $typ => $page_arr) {
                $param["typ"]      = $typ;

                foreach ($page_arr as $page) {
                    $param["page_amt"] = $page;
                    $dao->insertAmtPaperSale($conn, $param);
                }
            }
        }
    }

    // 입력값 있을경우 전체수정
    while ($rs && !$rs->EOF) {
        $seqno = $rs->fields["amt_paper_sale_seqno"];

        unset($param);
        $param["amt_paper_sale_seqno"] = $seqno;

        if ($dvs === 'R') {
            $param["rate"] = $val;
        } else {
            $param["aplc_price"] = $val;
        }

        $dao->updateAmtPaperSale($conn, $param);

        $rs->MoveNext();
    }
    $conn->CompleteTrans();

} else {
    //$conn->debug = 1;
    // 개별수정
    $param["amt_paper_sale_seqno"] = $price_seqno;

    if ($dvs === 'R') {
        $param["rate"] = $val;
    } else {
        $param["aplc_price"] = $val;
    }

    $update_ret = $dao->updateAmtPaperSale($conn, $param);

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
?>
