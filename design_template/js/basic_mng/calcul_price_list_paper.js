/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2015/12/02 엄준현 생성(종이 탭 관련내용 추가)
 *=============================================================================
 *
 */

/**
 * @brief 카테고리 선택시 하위 카테고리 정보 검색
 *
 * @param paperType = 종이 선택 구분(SORT, NAME, DVS, COLOR)
 * @param val       = 선택한 값
 * @param prefix    = 아이디 접두사
 */
var paperSelectCalc = {
    "type" : null,
    "exec" : function(paperType, val, prefix) {
        if (paperType === "name" && checkBlank(val) === true) {
            resetPaperInfo();
            return false;
        }

        if (checkBlank(prefix)) {
            prefix = '#';
        } else {
            prefix = '#' + prefix + '_';
        }

        this.type = paperType;

        var url = "/json/basic_mng/calcul_price_list/load_paper_info.php";
        var data = {
            "type"        : paperType,
            "paper_sort"  : $(prefix + "paper_sort").val(),
            "paper_name"  : $(prefix + "paper_name").val(),
            "paper_dvs"   : $(prefix + "paper_dvs").val(),
            "paper_color" : $(prefix + "paper_color").val()
        };

        var callback = function(result) {
            if (paperSelectCalc.type === "SORT") {
                $(prefix + "paper_name").html(result.name);
                $(prefix + "paper_dvs").html(result.dvs);
                $(prefix + "paper_color").html(result.color);
                $(prefix + "paper_basisweight").html(result.basisweight);
            }else if (paperSelectCalc.type === "NAME") {
                $(prefix + "paper_dvs").html(result.dvs);
                $(prefix + "paper_color").html(result.color);
                $(prefix + "paper_basisweight").html(result.basisweight);
            } else if (paperSelectCalc.type === "DVS") {
                $(prefix + "paper_color").html(result.color);
                $(prefix + "paper_basisweight").html(result.basisweight);
            } else if (paperSelectCalc.type === "COLOR") {
                $(prefix + "paper_basisweight").html(result.basisweight);
            }
        };

        ajaxCall(url, "json", data, callback);
    }
};
