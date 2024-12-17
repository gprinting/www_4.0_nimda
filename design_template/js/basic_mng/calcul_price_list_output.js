/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2015/12/02 엄준현 생성(츌력 탭 관련내용 추가)
 *=============================================================================
 *
 */

/**
 * @brief 출력명 선택시 판구분 정보 검색
 *
 * @param val = 출력명
 */
var outputSelect = function(val) {
    var url = "/ajax/basic_mng/calcul_price_list/load_output_board_dvs.php";
    var data = {
        "output_name" : val
    };
    var callback = function(result) {
        $("#output_board_dvs").html(result);
    };

    ajaxCall(url, "html", data, callback);
};
