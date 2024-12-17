/*
 *
 * Copyright (c) 2015-2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2015/11/27 엄준현 생성(상세정보 초기화 추가)
 * 2015/11/30 엄준현 수정(일괄/개별수정, 엑셀 업/다운로드 추가)
 * 2016/09/27 엄준현 수정(계열관련 추가)
 * 2017/06/09 엄준현 수정(회원별 할인 탭 추가)
 *=============================================================================
 *
 */
var currTab = "#tab1";

$(document).ready(function() {
    $(".nav-tabs li").on("click", function() {
        var tab = $(this).find('a').attr("href");
        currTab = tab;

        if (tab === "#tab2") {
            //$("#office_nick").val('');
            //$("#member_seqno").val('');
            $("#office_nick").prop("readonly", false);
            $("#office_nick").prop("disabled", false);
            $("#office_nick").css("background", "#fff");
        } else {
            //$("#office_nick").val('');
            //$("#member_seqno").val('');
            $("#office_nick").prop("readonly", true);
            $("#office_nick").prop("disabled", true);
            $("#office_nick").css("background", "#e1e5ea");
        }
    });
});

/**
 * @brief 상세검색정보(종이/사이즈/인쇄) 초기화
 *
 * @param cateSortcode = 정보검색용 카테고리 분류코드
 */
var initAftInfo = function(cateSortcode) {
    if (checkBlank(cateSortcode) === true) {
        resetAftInfo(0);
        return false;
    }

    var url = "/ajax/basic_mng/after_price_list/load_after_list.php";
    var data = {
        "cate_sortcode" : cateSortcode
    };
    var callback = function(result) {
        $("#aft_name").html(result);
        $("#aft_dep1").html("<option value=''>Depth1</option>");
        $("#aft_dep2").html("<option value=''>Depth2</option>");
        $("#aft_dep3").html("<option value=''>Depth3</option>");
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 상세검색정보 리셋
 *
 * @param depth = 초기화를 시작할 depth
 */
var resetAftInfo = function(depth) {
    $("#aft_name").html(makeOption("후공정명"));
    resetAftSelect(depth);
};

/**
 * @brief 후공정 Depth1, 2, 3 초기화
 *
 * @param depth = 초기화를 시작할 depth
 */
var resetAftSelect = function(depth) {
    var start = 1 + parseInt(depth);

    for (start; start <= 3; start++) {
        var optionStr = "Depth" + start 
        $("#aft_dep" + start).html(makeOption(optionStr));
    }

    $("#min_amt").html("<option value=\"\">최소수량</option>");
    $("#max_amt").html("<option value=\"\">최대수량</option>");
};

/**
 * @brief 후공정명에 해당하는 수량 검색
 */
var loadAfterAmt = function(aft) {
    if (checkBlank(aft) === true) {
        resetAftSelect('0');
        return false;
    }

    var url = "/ajax/basic_mng/after_price_list/load_after_amt.php";
    var data = {
        "cate_sortcode" : $("#cate_bot").val(),
        "after_name"    : aft,
    "sell_site"     : $("#sell_site").val()
    };
    var callback = function(result) {
        $("#min_amt").html("<option value=\"\">최소수량</option>");
        $("#min_amt").append(result);
        $("#max_amt").html("<option value=\"\">최대수량</option>");
        $("#max_amt").append(result);
    };

    ajaxCall(url, "html", data, callback);

    aftSelect.exec('0', aft);
};

/**
 * @brief 후공정 셀렉트 박스 선택시
 *
 * @param depth = 선택한 후공정의 depth
 * @param val   = 선택한 후공정의 값
 */
var aftSelect = {
    "depth"   : null,
    "exec"    : function(depth, aft) {
        if (checkBlank(aft) === true) {
            resetAftSelect(depth);
            return false;
        }

        if (aft === '-') {
            return false;
        }

        this.depth = parseInt(depth) + 1;

        var url = "/ajax/basic_mng/after_price_list/load_after_list.php";
        var data = {
            "cate_sortcode" : $("#cate_bot").val(),
            "depth"         : depth,
            "after_name"    : $("#aft_name").val(),
            "dep1_val"      : $("#aft_dep1").val(),
            "dep2_val"      : $("#aft_dep2").val()
        };
        var callback = function(result) {
            $("#aft_dep" + aftSelect.depth).html(result);
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

        var minAmt = parseFloat($("#min_amt").val());
        var maxAmt = parseFloat($("#max_amt").val());

        if (!checkBlank(minAmt) && minAmt > maxAmt) {
            return alertReturnFalse("최소수량이 최대수량보다 큽니다.");
        }

        if (currTab === "#tab2" && checkBlank($("#member_seqno").val())) {
            return alertReturnFalse("사내닉네임을 검색해주세요.");
        }

        if (checkBlank($("#aft_name").val()) === true) {
            alert("후공정명을 선택해주세요.");
            return false;
        }

        var url = "/ajax/basic_mng//";
        var url = "/ajax/basic_mng/after_price_list/";

        if (currTab === "#tab1") {
            url += "load_after_price_list.php";
        } else if (currTab === "#tab2") {
            url += "load_member_sale_price_list.php";
        }

        var data = null;
        var callback = function(result) {
            if (currTab === "#tab1") {
                $("#aft_price_list").html(result);
                $("#aft_price_list").show();
            } else if (currTab === "#tab2") {
                $("#member_sale_price_list").html(result);
                $("#member_sale_price_list").show();
            } 
        };

        if (updateFlag === false) {
            data = {
                "member_seqno"  : $("#member_seqno").val(),
                "cate_sortcode" : $("#cate_bot").val(),
                "sell_site"     : $("#sell_site").val(),
                "after_name"    : $("#aft_name").val(),
                "dep1_val"      : $("#aft_dep1").val(),
                "dep2_val"      : $("#aft_dep2").val(),
                "dep3_val"      : $("#aft_dep3").val(),
                "size"          : $("#size").val(),
                "min_amt"       : $("#min_amt").val(),
                "max_amt"       : $("#max_amt").val(),
                "tax_yn"        : $("input[name='tax_yn']:checked").val()
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
    "dvs"  : null,
    "pos"  : null,
    "exec" : function(event, dvs, pos) {
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

    //var $basicYn   = $("#basic_yn_" + pos);
    var $sellSite  = $("#sell_site_" + pos);
    var $aftMpcode = $("#mpcode_" + pos);

    var url = "/proc/basic_mng/after_price_list/update_after_price_list.php";
    var data = {
        //"basic_yn"   : $basicYn.attr("val"),
        "sell_site"  : $sellSite.attr("val"),
        "aft_mpcode" : $aftMpcode.attr("val"),
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
        showModiPop("modi_each_price", point.x + 100, point.y);
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

    var url = "/proc/basic_mng/after_price_list/update_after_price_list.php";
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
 * @brief 제목 탭에서 요율, 적용금액을 클릭했을 경우 전체수정팝업 출력
 *
 * @param event = 좌표값을 얻기위한 이벤트 객체
 * @param dvs   = 어떤 항목을 클릭했는지 구분값
 * @param pos   = 몇 번째 가격항목인지 위치
 */
var modiSalePriceInfo = {
    "dvs"      : null,
    "pos"      : null,
    "sellSite" : null,
    "exec"     : function(event, dvs, pos) {
        var point = getPopupPoint(event);

        selectedModiAllPriceDvs(dvs);

        this.dvs = dvs;
        this.pos = pos;

        hideModiPop();
        showModiPop("modi_all_member_sale_price", point.x, point.y);
    }
};

/**
 * @brief 일괄수정 적용버튼 클릭시
 */
var aplySalePriceInfo = function() {
    if (checkBlank($("#modi_all_member_sale_price_val").val()) === true) {
        if (modiSalePriceInfo.dvs === "R") {
            alert("요율을 입력해주세요.");
            return false;
        } else {
            alert("적용금액을 입력해주세요.");
            return false;
        }
    }

    var pos = modiSalePriceInfo.pos;

    var $memberSeqno = $("#sale_member_seqno_" + pos);
    var $basicYn     = $("#sale_basic_yn_" + pos);
    var $sellSite    = $("#sale_sale_sell_site_" + pos);
    var $mpcode      = $("#sale_mpcode_" + pos);

    modiSalePriceInfo.sellSite = $sellSite.attr("val");

    var url = "/proc/basic_mng/after_price_list/update_member_sale_price_list.php";
    var data = {
        "val"          : $("#modi_all_member_sale_price_val").val(),
        "dvs"          : modiSalePriceInfo.dvs,
        "member_seqno" : $memberSeqno.attr("val"),
        "basic_yn"     : $basicYn.attr("val"),
        "mpcode"       : $mpcode.attr("val"),
        "min_amt"      : $("#min_amt").val(),
        "max_amt"      : $("#max_amt").val(),
        "sell_site"    : modiSalePriceInfo.sellSite
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
 * @brief 내용에서 요율, 적용금액을 클릭했을 경우 개별수정팝업 출력
 *
 * @param event = 좌표값을 얻기위한 이벤트 객체
 * @param dvs   = 어떤 항목을 클릭했는지 구분값
 * @param seqno = 가격항목 seqno
 */
var modiSalePriceInfoEach = {
    "dvs"      : null,
    "seqno"    : null,
    "exec"     : function(event, dvs, seqno) {
        var point = getPopupPoint(event);

        this.dvs     = dvs;
        this.seqno   = seqno;

        hideModiPop();
        showModiPop("modi_each_member_sale_price", point.x, point.y);
    }
};

/**
 * @brief 개별수정 적용버튼 클릭시
 */
var aplySalePriceInfoEach = function() {
    if (checkBlank($("#modi_each_member_sale_price_val").val()) === true) {
        if (modiSalePriceInfoEach.dvs === "R") {
            alert("요율을 입력해주세요.");
            return false;
        } else {
            alert("적용금액을 입력해주세요.");
            return false;
        }
    }

    if (modiSalePriceInfoEach.seqno === "-1") {
        return alertReturnFalse("요율 또는 적용금액\n전체 적용 후 가능합니다.");
    }

    var url = "/proc/basic_mng/after_price_list/update_member_sale_price_list.php";
    var data = {
        "val"         : $("#modi_each_member_sale_price_val").val(),
        "dvs"         : modiSalePriceInfoEach.dvs,
        "min_amt"     : $("#min_amt").val(),
        "max_amt"     : $("#max_amt").val(),
        "price_seqno" : modiSalePriceInfoEach.seqno,
        "sell_site"   : modiSalePriceInfo.sellSite
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
 *
 * @param dvs = 다운로드할 엑셀파일 구분
 */
var downloadFile = {
    "sellSite" : null,
    "exec"     : function(dvs) {
        if (isSelectCateBot() === false) {
            return false;
        }

        if (checkBlank($("#aft_name").val()) === true) {
            alert("후공정명을 선택해주세요.");
            return false;
        }

        this.sellSite = $("#sell_site").val();

        var url = "/ajax/basic_mng/after_price_list/";
        if (dvs === "after_price_list") {
            url += "down_excel_after_price_list.php"; 
        } else if (dvs === "member_sale_price_list") {
            url += "down_excel_after_member_sale_price_list.php"; 
        } 
        var data = null;
        var callback = function(result) {
            if (result === "FALSE") {
                alert("엑셀파일 생성에 실패했습니다.");
            } else if (result === "NOT_INFO") {
                alert("엑셀로 생성할 정보가 존재하지 않습니다.");
            } else {
                var nameArr = result.split('!');

                var downUrl  = "/common/excel_file_down.php?name=" + nameArr[1];
                    downUrl += "&sell_site=" + downloadFile.sellSite;
                    downUrl += "&file_dvs=" + nameArr[0];

                $("#file_ifr").attr("src", downUrl);
            }
        };

        if (cndSearch.data === null) {
            data = {
                "member_seqno"  : $("#member_seqno").val(),
                "cate_sortcode" : $("#cate_bot").val(),
                "sell_site"     : downloadFile.sellSite,
                "after_name"    : $("#aft_name").val(),
                "dep1_val"      : $("#aft_dep1").val(),
                "dep2_val"      : $("#aft_dep2").val(),
                "dep3_val"      : $("#aft_dep3").val(),
                "size"          : $("#size").val(),
                "min_amt"       : $("#min_amt").val(),
                "max_amt"       : $("#max_amt").val(),
                "tax_yn"        : $("input[name='tax_yn']:checked").val()
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
    formData.append("sell_site", $("#sell_site").val());
    formData.append("dvs", dvs);

    if (dvs === "aft_sell_price") {
        if (checkExt($("#aft_price_excel_path")) === false) {
            return false;
        }

        formData.append("file", $("#aft_price_excel")[0].files[0]);
    } else if (dvs === "aft_member_sale_price") {
        if (checkExt($("#member_sale_price_excel")) === false) {
            return false;
        }

        formData.append("file", $("#member_sale_price_excel")[0].files[0]);
    }

    showMask();

    excelUploadAjax(formData);
};
