/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2015/11/30 엄준현 생성
 *=============================================================================
 *
 */

/**
 * @brief 상세검색정보(종이/사이즈/인쇄) 초기화
 *
 * @param cateSortcode = 정보검색용 카테고리 분류코드
 */
var initOptInfo = function(cateSortcode) {
    if (checkBlank(cateSortcode) === true) {
	    resetOptInfo(0);
	    return false;
    }

    var url = "/ajax/basic_mng/opt_price_list/load_opt_list.php";
    var data = {
	    "cate_sortcode" : cateSortcode
    };
    var callback = function(result) {
        $("#opt_name").html(result);
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 상세검색정보 리셋
 *
 * @param depth = 초기화를 시작할 depth
 */
var resetOptInfo = function(depth) {
    $("#opt_name").html(makeOption("옵션명"));
    resetOptSelect(depth);
};

/**
 * @brief 옵션 Depth1, 2, 3 초기화
 *
 * @param depth = 초기화를 시작할 depth
 */
var resetOptSelect = function(depth) {
    var start = 1 + parseInt(depth);

    for (start; start <= 3; start++) {
        var optionStr = "Depth" + start 
        $("#opt_dep" + start).html(makeOption(optionStr));
    }
}

/**
 * @brief 옵션 셀렉트 박스 선택시
 *
 * @param depth = 선택한 옵션의 depth
 * @param val   = 선택한 옵션의 값
 */
var optSelect = {
    "depth"   : null,
    "exec"    : function(depth, opt) {
        if (checkBlank(opt) === true) {
            resetOptSelect(depth);
            return false;
        }

        if (opt === '-') {
            return false;
        }

        this.depth = parseInt(depth) + 1;

        var url = "/ajax/basic_mng/opt_price_list/load_opt_list.php";
        var data = {
            "cate_sortcode" : $("#cate_bot").val(),
            "depth"         : depth,
            "opt_name"      : $("#opt_name").val(),
            "dep1_val"      : $("#opt_dep1").val(),
            "dep2_val"      : $("#opt_dep2").val()
        };
        var callback = function(result) {
            $("#opt_dep" + optSelect.depth).html(result);
        };

        ajaxCall(url, "html", data, callback);
    }
};

/**
 * @brief 선택 조건으로 검색 클릭시
 *
 * @param updateFlag = 가격 수정여부
 */
var cndSearch = {
    "data" : null,
    "exec" : function(updateFlag) {
	    hideModiPop();

        if (isSelectCateBot() === false) {
            return false;
        }

        if (checkBlank($("#opt_name").val()) === true) {
            alert("옵션명을 선택해주세요.");
            return false;
        }

        var url = "/ajax/basic_mng/opt_price_list/load_opt_price_list.php";
	    var data = null;
        var callback = function(result) {
            $("#opt_price_list").html(result);
            $("#opt_price_list").show();
        };

    	if (updateFlag === false) {
            data = {
                "cate_sortcode" : $("#cate_bot").val(),
                "sell_site"     : $("#sell_site").val(),
                "opt_name"    : $("#opt_name").val(),
                "dep1_val"      : $("#opt_dep1").val(),
                "dep2_val"      : $("#opt_dep2").val(),
                "dep3_val"      : $("#opt_dep3").val()
            };
    	} else {
    	    data = this.data;
    	}

        this.data = data;

        showMask();

        ajaxCall(url, "html", data, callback);
    }
};

/**
 * @brief 제목 탭에서 요율, 적용금액을 클릭했을 경우 전체수정팝업 출력
 *
 * @param event = 좌표값을 얻기위한 이벤트 객체
 * @param dvs   = 어떤 항목을 클릭했는지 구분값
 * @param pos   = 몇 번째 가격항목인지 위치
 */
var modiPriceInfo = {
    "dvs"    : null,
    "pos"    : null,
    "exec"   : function(event, dvs, pos) {
        var point = getPopupPoint(event);

        selectedModiAllPriceDvs(dvs);

        this.dvs = dvs;
        this.pos = pos;

        hideModiPop("modi_each_price");
        showModiPop("modi_all_price", point.x, point.y);
    }
};

/**
 * @brief 일괄수정 적용버튼 클릭시
 */
var aplyPriceInfo = function() {
    if (checkBlank($("#modi_all_price_val").val()) === true) {
        if (modiPriceInfo.dvs === "R") {
            alert("요율을 입력해주세요.");
            return false;
        } else {
            alert("적용금액을 입력해주세요.");
            return false;
        }
    }

    var pos = modiPriceInfo.pos;

    var $sellSite  = $("#sell_site_" + pos);
    var $optMpcode = $("#mpcode_" + pos);

    var url = "/proc/basic_mng/opt_price_list/update_opt_price_list.php";
    var data = {
        "sell_site"  : $sellSite.attr("val"),
        "opt_mpcode" : $optMpcode.attr("val"),
        "val"        : $("#modi_all_price_val").val(),
        "dvs"        : modiPriceInfo.dvs
    };
    var callback = function(result) {
	    if (result === "T") {
	        cndSearch.exec(true);
	    } else {
            alert("가격 수정에 실패했습니다.");
	    }

	    hideModiPop();
    };

    ajaxCall(url, "text", data, callback);
}

/**
 * @brief 내용에서 요율, 적용금액을 클릭했을 경우 개별수정팝업 출력
 *
 * @param event = 좌표값을 얻기위한 이벤트 객체
 * @param dvs   = 어떤 항목을 클릭했는지 구분값
 * @param seqno = 가격항목 seqno
 */
var modiPriceInfoEach = {
    "dvs"    : null,
    "seqno"  : null,
    "exec"   : function(event, dvs, seqno) {
        var point = getPopupPoint(event);

        this.dvs     = dvs;
        this.seqno   = seqno;

        hideModiPop("modi_all_price");
        showModiPop("modi_each_price", point.x, point.y);
    }
};

/**
 * @brief 개별수정 적용버튼 클릭시
 */
var aplyPriceInfoEach = function() {
    if (checkBlank($("#modi_each_price_val").val()) === true) {
        if (modiPriceInfoEach.dvs === "R") {
            alert("요율을 입력해주세요.");
            return false;
        } else {
            alert("적용금액을 입력해주세요.");
            return false;
        }
    }

    var url = "/proc/basic_mng/opt_price_list/update_opt_price_list.php";
    var data = {
        "val"         : $("#modi_each_price_val").val(),
        "dvs"         : modiPriceInfoEach.dvs,
        "price_seqno" : modiPriceInfoEach.seqno,
    };
    var callback = function(result) {
	    if (result === "T") {
	        cndSearch.exec(true);
	    } else {
            alert("가격 수정에 실패했습니다.");
	    }

	    hideModiPop();
    };

    ajaxCall(url, "text", data, callback);
};

/**
 * @brief 엑셀 다운로드시 사용되는 함수
 */
var downloadFile = {
    "sellSite" : null,
    "exec"     : function() {
        if (isSelectCateBot() === false) {
            return false;
        }

        if (checkBlank($("#opt_name").val()) === true) {
            alert("옵션명을 선택해주세요.");
            return false;
        }

        this.sellSite = $("#sell_site").val();

        var url = "/ajax/basic_mng/opt_price_list/down_excel_opt_price_list.php";
        var data = null;
        var callback = function(result) {
            if (result === "FALSE") {
                alert("엑셀파일 생성에 실패했습니다.");
            } else if (result === "NOT_INFO") {
                alert("엑셀로 생성할 정보가 존재하지 않습니다.");
            } else {
                var downUrl  = "/common/excel_file_down.php?name=" + result;
                    downUrl += "&sell_site=" + downloadFile.sellSite;

                $("#file_ifr").attr("src", downUrl);
            }
        };

        if (cndSearch.data === null) {
            data = {
                "cate_sortcode" : $("#cate_bot").val(),
                "sell_site"     : downloadFile.sellSite,
                "opt_name"      : $("#opt_name").val(),
                "dep1_val"      : $("#opt_dep1").val(),
                "dep2_val"      : $("#opt_dep2").val(),
                "dep3_val"      : $("#opt_dep3").val()
            };
        } else {
            data = cndSearch.data;
        }

        showMask();

        ajaxCall(url, "text", data, callback);
    }
};

/**
 * @brief 엑셀 업로드 할 때 사용하는 함수
 *
 * @param dvs = 어떤 엑셀 파일을 업로드 하는지 구분
 */
var uploadFile = function(dvs) {

    var formData = new FormData();

    formData.append("dvs", dvs);

    if(checkExt($("#opt_price_excel_path")) === false) {
        return false;
    }

    formData.append("file"     , $("#opt_price_excel")[0].files[0]);
    formData.append("sell_site", $("#sell_site").val());

    showMask();

    excelUploadAjax(formData);
};
