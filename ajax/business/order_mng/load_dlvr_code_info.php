<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 배송유형 변경시 배송유형에 속한 상세정보 검색 후
 * option html 생성해서 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/04/20 엄준현 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/common_define/common_info.inc");

$fb = new FormBean();

$dlvr_dvs = $fb->form("dlvr_dvs");

$dlvr_code_arr = DLVR_CODE[$dlvr_dvs];

$option = "<option value=\"%s\">%s</option>";

$ret = sprintf($option, '', "전체");

if (empty($dlvr_code_arr)) {
    goto END;
}

foreach ($dlvr_code_arr as $dlvr_code) {
    $ret .= sprintf($option, $dlvr_code, $dlvr_code);
}

END:
    echo $ret;
?>
