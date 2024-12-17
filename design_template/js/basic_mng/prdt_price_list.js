/*
 * Copyright (c) 2015-2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2015/11/24 엄준현 수정(상세정보 관련 추가)
 * 2015/11/25 엄준현 수정(검색조건 초기화, 가격검색 추가)
 * 2015/11/26 엄준현 수정(엑셀 업/다운로드, 가격 일괄/개별수정 추가)
 * 2016/02/09 엄준현 수정(계산형 가격 출력부분 추가)
 * 2016/09/26 엄준현 수정(사이즈 유형 관련 추가)
 *=============================================================================
 *
 */
var currTab = "#tab1";

$(document).ready(function() {
    $(".nav-tabs li").on("click", function() {
        var tab = $(this).find('a').attr("href");
        currTab = tab;

        if (tab === "#tab3") {
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
 * @brief 상세검색정보 리셋
 */
var resetDetailInfo = function() {
    $("#paper_name").html(makeOption("종이명(전체)"));
    resetPaperInfo();
    $("#output_size_typ").html(makeOption("사이즈유형(전체)"));
    $("#output_size").html(makeOption("사이즈명(전체)"));
    $("#print_tmpt").html(makeOption("인쇄도수(전체)"));
    $("#print_purp").html(makeOption("인쇄방식(전체)"));
};

/**
 * @brief 상세검색정보(종이/사이즈/인쇄) 초기화
 *
 * @param cateSortcode = 정보검색용 카테고리 분류코드
 */
var initDetailInfo = function(cateSortcode) {
    if (checkBlank(cateSortcode) === true) {
        resetDetailInfo();
        return false;
    }

    var url = "/json/detail/prdt_price_list/load_detail.php";
    var data = {
        "cate_sortcode" : cateSortcode,
        "sell_site"     : $("#sell_site").val(),
        "etprs_dvs"     : $("input[name='etprs_dvs']:checked").val(),
        "mono_yn"       : $("input[name='mono_yn']:checked").val()
    };
    var callback = function(result) {
        $("#paper_name").html(result.paper);
        $("#output_size_typ").html(result.size_typ);
        //$("#output_size").html(result.size);
        $("#print_cond").html(result.print);
        $("#min_amt").html("<option value=\"\">최소수량</option>");
        $("#min_amt").append(result.amt);
        $("#max_amt").html("<option value=\"\">최대수량</option>");
        $("#max_amt").append(result.amt);

        var tmptDvs = result.tmpt_dvs;
        $("#cate_bot").attr("tmpt_dvs", tmptDvs);

        var monoDvs = result.mono_dvs;

        if (monoDvs === '3') {
           // 계산형
            $("input[name='mono_yn'][value='1']").prop("checked", true);
            $("input[name='mono_yn'][value='1']").prop("disabled", false);
            $("input[name='mono_yn'][value='0']").prop("disabled", true);

            activePrintPurp('1');
        } else if (monoDvs === '2') {
           // 확정형
            $("input[name='mono_yn'][value='0']").prop("checked", true);
            $("input[name='mono_yn'][value='0']").prop("disabled", false);
            $("input[name='mono_yn'][value='1']").prop("disabled", true);

            activePrintPurp('0');
        } else{
            // 전체 
            $("input[name='mono_yn'][value='0']").prop("checked", true);
            $("input[name='mono_yn'][value='0']").prop("disabled", false);
            $("input[name='mono_yn'][value='1']").prop("disabled", false);

            activePrintPurp('0');
        }

    };

    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 선택 조건으로 검색 클릭시 가격 검색
 *
 * @param updateFlag = 가격 업데이트 했는지 여부
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

        if (currTab === "#tab3" && checkBlank($("#member_seqno").val())) {
            return alertReturnFalse("사내닉네임을 검색해주세요.");
        }

        var tmptDvs = $("#cate_bot").attr("tmpt_dvs");

        var url = "/ajax/basic_mng/prdt_price_list/";

        if (currTab === "#tab1") {
            url += "load_prdt_price_list.php";
        } else if (currTab === "#tab2") {
            url += "load_grade_sale_price_list.php";
        } else {
            url += "load_member_sale_price_list.php";
        }

        var callback = function(result) {

            if (currTab === "#tab1") {
                $("#prdt_price_list").html(result);
                $("#prdt_price_list").show();
            } else if (currTab === "#tab2") {
                $("#grade_sale_price_list").html(result);
                $("#grade_sale_price_list").show();
            } else {
                $("#member_sale_price_list").html(result);
                $("#member_sale_price_list").show();
            }
        };

    	if (updateFlag === false) {
            this.data = getSearchData(tmptDvs);
    	}

        showMask();

        ajaxCall(url, "html", this.data, callback);
    }
};

/**
 * @brief 계산형 여부에 따른 검색파라미터 생성
 *
 * @parma tmptDvs = 도수구분
 *
 * @return 검색파라미터
 */ var getSearchData = function(tmptDvs) {
    var data = {
        "cate_sortcode" : $("#cate_bot").val(),
        "sell_site"     : $("#sell_site").val(),
        "mono_yn"       : $("input[name='mono_yn']:checked").val(),
        "etprs_dvs"     : $("input[name='etprs_dvs']:checked").val(),
        "tmpt_dvs"      : tmptDvs,
        "paper_name"        : $("#paper_name").val(),
        "paper_dvs"         : $("#paper_dvs").val(),
        "paper_color"       : $("#paper_color").val(),
        "paper_basisweight" : $("#paper_basisweight").val(),
        "output_size" : $("#output_size").val(),
        "print_purp"  : $("#print_purp").val(),
        "min_amt"  : $("#min_amt").val(),
        "max_amt"  : $("#max_amt").val(),
        "member_seqno" : $("#member_seqno").val(),
        "tax_yn" : $("input[name='tax_yn']:checked").val()
    };

    if (tmptDvs === '0') {
        // 단/양면 도수
        data.bef_print_tmpt = $("#print_tmpt").val();
        data.aft_print_tmpt = '';
    } else {
        // 전/후면 도수이면서 확정형
        data.bef_print_tmpt = $("#print_tmpt_front").val();
        data.aft_print_tmpt = $("#print_tmpt_back").val();
    }

    if (currTab === "#tab3") {
        data.member_seqno = $("#member_seqno").val();
    }

    return data;
}

/**
 * @brief 엑셀 다운로드시 사용되는 함수
 *
 * @param dvs = 다운로드할 엑셀파일 구분
 */
var downloadFile = {
    "monoYn"   : null,
    "exec"     : function(dvs) {
        if (isSelectCateBot() === false) {
            return false;
        }

        var monoYn  = $("input[name='mono_yn']:checked").val();
        var tmptDvs = $("#cate_bot").attr("tmpt_dvs");

        this.monoYn   = monoYn;

        var url = "/ajax/basic_mng/prdt_price_list/";
        var data = null;
        var callback = function(result) {
            if (result === "FALSE") {
                alert("엑셀파일 생성에 실패했습니다.");
            } else if (result === "NOT_INFO") {
                alert("엑셀로 생성할 정보가 존재하지 않습니다.");
            } else {
                var nameArr = result.split('!');

                var downUrl  = "/common/excel_file_down.php?name=" + nameArr[1];
                    downUrl += "&sell_site=" + 1;
                    downUrl += "&mono_yn=" + downloadFile.monoYn;
                    downUrl += "&cate_name=" + $("#cate_bot > option:selected").text();
                    downUrl += "&etprs_dvs=" + $("input[name='etprs_dvs']:checked").val();
                    downUrl += "&file_dvs=" + nameArr[0];

                $("#file_ifr").attr("src", downUrl);
            }
        };

        if (cndSearch.data === null) {
            data = getSearchData(tmptDvs);
        } else {
            data = cndSearch.data;
        }

        if (dvs === "prdt_price_list") {
            if (tmptDvs === '0') {
                url += "down_excel_prdt_price_list_single_both.php"; 
            } else {
                url += "down_excel_prdt_price_list_aft_bef.php"; 
            }
        } else if (dvs === "grade_sale_price_list") {
            if (tmptDvs === '0') {
                url += "down_excel_grade_sale_price_list_single_both.php"; 
            } else {
                url += "down_excel_grade_sale_price_list_aft_bef.php"; 
            }
        } else if (dvs === "member_sale_price_list") {
            url += "down_excel_member_sale_price_list_single_both.php"; 
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

    console.log(formData);

    if (dvs === "prdt_price") {
        if(checkExt($("#prdt_price_excel_path")) === false) {
            return false;
        }

        var filePath = $("#prdt_price_excel_path").val();
        /*
        var etprsDvs = $("input[name='etprs_dvs']:checked").val();

        if (etprsDvs === "new") {
            if (filePath.indexOf("신규") === -1) {
                if (!confirm("신규판매가 엑셀이 맞으면\n확인을 눌러주세요.")) {
                    return false;
                }
            }
        } else {
            if (filePath.indexOf("기존") === -1) {
                if (!confirm("기존판매가 엑셀이 맞으면\n확인을 눌러주세요.")) {
                    return false;
                }
            }
        }
        */

        formData.append("file"      , $("#prdt_price_excel")[0].files[0]);
        formData.append("mono_yn"   , $("input[name='mono_yn']:checked").val());
        formData.append("tax_yn"   , $("input[name='tax_yn']:checked").val());
        //formData.append("etprs_dvs" , etprsDvs);
    } else if (dvs === "member_sale_price") {
        if(checkExt($("#member_sale_price_excel_path")) === false) {
            return false;
        }

        formData.append("file"      , $("#member_sale_price_excel")[0].files[0]);
    }

    showMask();

    excelUploadAjax(formData);
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
    "monoYn" : null,
    "exec"   : function(event, dvs, pos) {
        var point = getPopupPoint(event);

        selectedModiAllPriceDvs(dvs);

        this.dvs     = dvs;
        this.pos     = pos;
        this.monoYn  = $("input[name='mono_yn']:checked").val();

        hideModiPop();
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

    var $cateName   = $("#cate_name_" + pos);
    var $paperInfo  = $("#paper_info_" + pos);
    var $cateSize   = $("#cate_size_" + pos);
    var $befPrintTmpt     = $("#bef_print_tmpt_" + pos);
    var $befAddPrintTmpt  = $("#bef_print_add_tmpt_" + pos);
    var $aftPrintTmpt     = $("#aft_print_tmpt_" + pos);
    var $aftAddPrintTmpt  = $("#aft_print_add_tmpt_" + pos);

    var url = "/proc/basic_mng/prdt_price_list/update_prdt_price_list.php";
    var data = {
        "cate_sortcode" : $cateName.attr("val"),
        "paper_mpcode"  : $paperInfo.attr("val"),
        "stan_info"     : $cateSize.attr("val"),
        "bef_print_tmpt"     : $befPrintTmpt.attr("val"),
        "bef_add_print_tmpt" : $befAddPrintTmpt.attr("val"),
        "aft_print_tmpt"     : $aftPrintTmpt.attr("val"),
        "aft_add_print_tmpt" : $aftAddPrintTmpt.attr("val"),
        "val"           : $("#modi_all_price_val").val(),
        "dvs"           : modiPriceInfo.dvs,
        "mono_yn"       : modiPriceInfo.monoYn,
        "etprs_dvs"     : $("input[name='etprs_dvs']:checked").val(),
        "min_amt"       : $("#min_amt").val(),
        "max_amt"       : $("#max_amt").val(),
        "sell_site"     : $("#sell_site").val()
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
var modiPriceInfoEach = {
    "dvs"    : null,
    "seqno"  : null,
    "monoYn" : null,
    "exec"   : function(event, dvs, seqno) {
        var point = getPopupPoint(event);

        this.dvs     = dvs;
        this.seqno   = seqno;
        this.monoYn = $("input[name='mono_yn']:checked").val();

        hideModiPop();
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

    var url = "/proc/basic_mng/prdt_price_list/update_prdt_price_list.php";
    var data = {
        "val"         : $("#modi_each_price_val").val(),
        "dvs"         : modiPriceInfoEach.dvs,
        "price_seqno" : modiPriceInfoEach.seqno,
        "mono_yn"     : modiPriceInfoEach.monoYn,
        "etprs_dvs"   : $("input[name='etprs_dvs']:checked").val(),
        "sell_site"   : $("#sell_site").val()
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
    "dvs"    : null,
    "pos"    : null,
    "monoYn" : null,
    "exec"   : function(event, dvs, pos) {
        var point = getPopupPoint(event);

        selectedModiAllPriceDvs(dvs);

        this.dvs     = dvs;
        this.pos     = pos;
        this.monoYn  = $("input[name='mono_yn']:checked").val();

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
    var $cateName    = $("#sale_cate_name_" + pos);
    var $paperInfo   = $("#sale_paper_info_" + pos);
    var $cateSize    = $("#sale_cate_size_" + pos);
    var $befPrintTmpt     = $("#sale_bef_print_tmpt_" + pos);
    var $befAddPrintTmpt  = $("#sale_bef_print_add_tmpt_" + pos);
    var $aftPrintTmpt     = $("#sale_aft_print_tmpt_" + pos);
    var $aftAddPrintTmpt  = $("#sale_aft_print_add_tmpt_" + pos);

    var url = "/proc/basic_mng/prdt_price_list/update_member_sale_price_list.php";
    var data = {
        "member_seqno"  : $memberSeqno.attr("val"),
        "cate_sortcode" : $cateName.attr("val"),
        "paper_mpcode"  : $paperInfo.attr("val"),
        "stan_info"     : $cateSize.attr("val"),
        "bef_print_tmpt"     : $befPrintTmpt.attr("val"),
        "bef_add_print_tmpt" : $befAddPrintTmpt.attr("val"),
        "aft_print_tmpt"     : $aftPrintTmpt.attr("val"),
        "aft_add_print_tmpt" : $aftAddPrintTmpt.attr("val"),
        "val"           : $("#modi_all_member_sale_price_val").val(),
        "dvs"           : modiSalePriceInfo.dvs,
        "mono_yn"       : modiSalePriceInfo.monoYn,
        "etprs_dvs"     : $("input[name='etprs_dvs']:checked").val(),
        "min_amt"       : $("#min_amt").val(),
        "max_amt"       : $("#max_amt").val(),
        "sell_site"     : $("#sell_site").val()
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
    "dvs"    : null,
    "seqno"  : null,
    "monoYn" : null,
    "exec"   : function(event, dvs, seqno) {
        var point = getPopupPoint(event);

        this.dvs     = dvs;
        this.seqno   = seqno;
        this.monoYn = $("input[name='mono_yn']:checked").val();

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

    var url = "/proc/basic_mng/prdt_price_list/update_member_sale_price_list.php";
    var data = {
        "val"         : $("#modi_each_member_sale_price_val").val(),
        "dvs"         : modiSalePriceInfoEach.dvs,
        "price_seqno" : modiSalePriceInfoEach.seqno,
        "mono_yn"     : modiSalePriceInfoEach.monoYn,
        "etprs_dvs"   : $("input[name='etprs_dvs']:checked").val(),
        "sell_site"   : $("#sell_site").val()
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
 * @brief 계산방식에 따라 인쇄 방식을 활성화/비활성화
 *
 * @param dvs = 계산방식
 */
var activePrintPurp = function(val) {
    // 확정형 일 때는 사용 안함
    if (val === "0") {
        $("#print_purp").prop("disabled", true);
        return false;
    } else {
        $("#print_purp").prop("disabled", false);
    }
};
