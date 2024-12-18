<?php
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 입금유형에 따른 상세 유형 option html 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/07/21 엄준현 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/define/nimda/order_mng_define.inc");
include_once(INC_PATH . "/common_define/common_info.inc");

$fb = new FormBean();
$fb = $fb->getForm();

$depo_input_typ = $fb["depo_typ"];
$depo_input_typ_arr = DEPO_INPUT_TYPE[1];

$form = "<option value='%s'>%s</option>";

$ret = sprintf($form, '', "전체");
if ($depo_input_typ_arr[$depo_input_typ] === "카드") {
    $card_arr = CARD_COMPANY;

    foreach ($card_arr as $card) {
        $ret .= sprintf($form, $card, $card);
    }
} else if ($depo_input_typ_arr[$depo_input_typ] === "은행") {
    $bank_arr = BANK_INFO;

    foreach ($bank_arr as $bank) {
        $ret .= sprintf($form, $bank, $bank);
    }
}

echo $ret;
