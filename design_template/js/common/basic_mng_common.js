/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2015/11/27 엄준현 수정(카테고리 선택 여기로 이동)
 * 2015/11/27 엄준현 수정(카테고리 선택 삭제, 엑셀 업로드 이동)
 * 2016/01/13 임종건 수정(매입품에 해당하는 매입업체 이동)
 * 2016/11/06 엄준현 수정(getValueByKey 이동)
 *=============================================================================
 *
 */

/**
 * @brief 엑셀 업로드 공통처리 ajax 함수
 *
 * @param data = multipart form 데이터
 */
var excelUploadAjax = function(data) {
    $.ajax({
        type        : "POST",
        data        : data,
        url         : "/proc/basic_mng/common/excel_upload.php",
        dataType    : "text",
        processData : false,
        contentType : false,
        success     : function(data) {
            hideMask();

            data = data.trim();

            if (data !== "TRUE") {
                alert(data);
            }
        },
        error    : getAjaxError   
    });
};

//매입품에 해당하는 매입업체 가져오기
var loadExtnlEtprs = function(val, el) {

    $.ajax({
        type: "POST",
        data: {"val" : val},
        url: "/ajax/basic_mng/pur_etprs_list/load_extnl_etprs.php",
        success: function(result) {
            $("#" + el).html(result);
        }, 
        error: getAjaxError
    });
};

/**
 * @brief 사이즈 유형에 따른 사이즈 검색
 *
 * @param val    = 사이즈유형
 * @param prefix = dom 객체 아이디 접두사
 */
var loadOutputSize = function(val, prefix) {
    if (checkBlank(prefix)) {
        prefix = '#';
    } else {
        prefix = '#' + prefix + '_';
    }

    if (checkBlank(val)) {
        $(prefix + "output_size").html(makeOption("사이즈명(전체)"));
        return false;
    }

    var url = "/ajax/basic_mng/prdt_price_list/load_output_size.php";
    var data = {
        "cate_sortcode" : $(prefix + "cate_bot").val(),
        "typ"           : val
    };
    var callback = function(result) {
        $(prefix + "output_size").html(result);
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 팝업창에서 적용버튼을 클릭했을 경우
 * 제목등에서 가져온 텍스트 값으로 검색조건의 셀렉트 박스를
 * 검색에서 해당하는 맵핑코드 등을 반환하는 함수
 *
 * @param $obj = 검색할 셀렉트박스 객체(jquery 랩핑)
 * @param key  = 검색어
 *
 * @return 검색결과로 나온 맵핑코드 값 등
 */
var getValueByKey = function($obj, key) {
    var ret = null;

    $obj.children("option").each(function() {
        if ($(this).text() === key) {
            ret = $(this).val();
            return false;
        }
    });

    return ret;
}
