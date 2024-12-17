/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2015/12/04 엄준현 생성(인쇄 탭 관련내용 추가)
 *=============================================================================
 *
 */

/**
 * @brief 출력명 선택시 판구분 정보 검색
 *
 * @param val = 인쇄명
 */
var printSelect = function(val) {
    var url = "/ajax/basic_mng/calcul_price_list/load_print_info.php";
    var data = {
        "dvs"        : "PURP",
        "print_name" : val
    };
    var callback = function(result) {
        $("#print_purp_dvs").html(result);
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 카테고리 중분류에 해당하는 인쇄명 반환
 *
 * @param sortcode = 카테고리 중분류 분류코드
 */
var initPrintInfo = function(sortcode) {
    var url = "/ajax/basic_mng/calcul_price_list/load_print_info.php";
    var data = {
        "dvs"           : "NAME",
        "cate_sortcode" : sortcode
    };
    var callback = function(result) {
        $("#print_name").html(result);
    };

    ajaxCall(url, "html", data, callback);
}
