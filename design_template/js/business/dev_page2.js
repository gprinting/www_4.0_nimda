/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/04/17 ujh
 *=============================================================================
 */

 /**
  * @brief 각 탭 정보 최초로딩했는지 여부
  * 탭 클릭할 때 마다 재조회 하는가 방지용
  */
var tabDataLoad = {
    "sales_info" : false,
    "order_info" : false,
    "crm_info"   : false,
    "prdt_info"  : false
};

 /**
  * @brief 각 탭에서 차트 정보 최초로딩했는지 여부
  * 탭 클릭할 때 마다 재조회 하는가 방지용
  */
var chartDataLoad = {
    "sales_info" : false,
    "order_info" : false,
    "crm_info"   : false,
    "prdt_info"  : false
};

 /**
  * @brief 차트객체 저장
  */
var chart = {
    "salesInfo" : {
        "sumOa"      : null, // 총미수액
        "sumNet"     : null, // 순매출액
        "sumSale"    : null, // 에누리
        "sumDepo"    : null, // 입금액
        "yearSumNet" : null  // 년 순매출액
    },
    "prdtInfo" : {
        "lastYear"   : null, // 막대 차트 : 전년동기
        "curPeriod"  : null, // 막대 차트 : 현재
        "minus1mon"  : null, // 막대 차트 : -1개월
        "minus2mon"  : null, // 막대 차트 : -2개월
        "minus3mon"  : null, // 막대 차트 : -3개월
        "curYear"    : null, // 도넛 차트 : 기간 전체
        "curYearSt"  : null, // 도넛 차트 : 기간 스티커 전체
        "curYearBl"  : null, // 도넛 차트 : 기간 합판전단 전체
        "curYearBlM" : null, // 도넛 차트 : 기간 독판전단 전체
        "curYearAd"  : null, // 도넛 차트 : 기간 광고홍보물 전체
        "curYearEv"  : null, // 도넛 차트 : 기간 봉투 전체
        "curYearMt"  : null, // 도넛 차트 : 기간 마스터 전체
        "curYearEtc" : null  // 도넛 차트 : 기간 나머지 전체
    }
    /**
     * chart.setTitle({"text" : $text});
     * chart.xAxis[0].setCategories([1, 2, 3, 4 ...]);
     * chart.series[0].setData([129.2, 144.0, 176.0]);
     * chart.series[1].setData([129.2, 144.0, 176.0]);
     * chart.series[2].setData([129.2, 144.0, 176.0]);
     * chart.series[3].setData([129.2, 144.0, 176.0]);
     */
};

$(document).ready(function() {
    $(".datepicker_input").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0');

    // 매출 거래현황정보 차트 초기화
    var salesInfoChartOption = {
        chart  : {type : "column"},
        xAxis  : {categories : []},
        yAxis  : {title : {text : "단위(원)"}},
        legend : {enabled : false},
        tooltip: {
            headerFormat : '',
            pointFormat  : "<b>{point.y}</b>원<br/>"
        },
        series : [{data : []}]
    };
    salesInfoChartOption.title = {text : "총미수액"};
    chart.salesInfo.sumOa =
        new Highcharts.chart("sum_oa", salesInfoChartOption);

    salesInfoChartOption.title = {text : "순 매출액"};
    chart.salesInfo.sumNet =
        new Highcharts.chart("sum_net", salesInfoChartOption);

    salesInfoChartOption.title = {text : "에누리"};
    chart.salesInfo.sumSale =
        new Highcharts.chart("sum_sale", salesInfoChartOption);

    salesInfoChartOption.title = {text : "입금액"};
    chart.salesInfo.sumDepo =
        new Highcharts.chart("sum_depo", salesInfoChartOption);

    salesInfoChartOption.title = {text : "연 순매출액"};
    chart.salesInfo.yearSumNet =
        new Highcharts.chart("year_sum_net", salesInfoChartOption);

    $('#startModal').click(function(e) {
        showModal($('#modal'));
    });
    $('#modalCloseButton').click(function(e) {
        hideModal();
    });
    $('#modal').keydown(function(event) {
        trapTabKey($(this), event);
    });
    $('#modal').keydown(function(event) {
        trapEscapeKey($(this), event);
    });
});

// 모달창 관련함수 나중에 수정하기
function showModal() {
    $('#modalOverlay').css('display', 'block');
    $('#modal').css('display', 'block');
    $('#modal').attr('aria-hidden', 'false');

};
function hideModal() {
    $('#modalOverlay').css('display', 'none');
    $('#modal').css('display', 'none');
    $('#modal').attr('aria-hidden', 'true');
};

/**
 * @brief 날짜 범위 설정 - 공통
 *
 * @param prefix = 데이트피커 객체구분값
 * @param dvs    = 연산구분값
 * @param calc   = 계산할 일자
 */
var setDateVal = function(prefix, dvs, calc) {
    $('#' + prefix + "_to").datepicker("setDate", '0');

    if (dvs === 'a') {
        $('#' + prefix + "_from").val('');
        return false;
    } else if (dvs === 't') {
        $('#' + prefix + "_from").datepicker("setDate", '0');
        return false;
    }

    //var from = $('#' + prefix + "_from").val();
    var to   = $('#' + prefix + "_to").val().split('-');

    /* 오늘 기준으로만 계산하니까 from은 주석처리
    if (checkBlank(from)) {
        from = to;
    } else {
        from = from.split('-');
    }
    */

    var calc = calcDate({
        "dvs" : dvs,
        "calc" : calc,
        "from" : {
            'y' : to[0],
            'm' : to[1],
            'd' : to[2]
            /*
            'y' : from[0],
            'm' : from[1],
            'd' : from[2]
            */
        },
        "to" : {
            'y' : to[0],
            'm' : to[1],
            'd' : to[2]
        }
    });

    $('#' + prefix + "_from").datepicker("setDate", calc.from);
    $('#' + prefix + "_to").datepicker("setDate", calc.to);
};

/**
 * @brief 판매채널 변경시 팀/담당자 변경
 *
 * @param val = 판매채널 일련번호
 */
var changeCpnAdmin = function(seqno) {
    var url = "/json/business/order_mng/load_depar_info.php";
    var data = {
        "seqno" : seqno
    };
    var callback = function(result) {
        $("#depar").html(result.depar);
        $("#empl").html(result.empl);
        $("#crm_info_depar").html(result.depar);
        $("#crm_info_empl").html(result.empl);
    };

    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 팀 변경시 담당자 변경
 *
 * @param deparCode = 부서코드
 * @param prefix    = 영역별 접두사
 */
var changeDepar = function(deparCode, prefix) {
    var url = "/ajax/business/order_mng/load_empl_info.php";
    var data = {
        "depar_code" : deparCode
    };
    var callback = function(result) {
        $('#' + prefix + "empl").html(result);
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 운영체제 변경시 사용 프로그램 변경
 *
 * @param val = 판매채널 일련번호
 */
var changeOperSys = function(operSys) {
    var url = "/ajax/business/order_mng/load_pro_info.php";
    var data = {
        "oper_sys" : operSys
    };
    var callback = function(result) {
        $("#pro").html(result);
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 배송유형 변경시 거기에 속한 상세정보 검색
 *
 * @param val = 판매채널 일련번호
 */
var changeDlvrDvs = function(dlvrDvs) {
    var url = "/ajax/business/order_mng/load_dlvr_code_info.php";
    var data = {
        "dlvr_dvs" : dlvrDvs
    };
    var callback = function(result) {
        $("#dlvr_code").html(result);
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 부분 마스크 show
 *
 * @param param  = 설정값 파라미터
 * @detail 설정값 상세
 * {
 *   id     = 부분 마스크 div id, *필수
 *   width  = 부분 마스크 div width, *필수
 *   height = 부분 마스크 table height
 *   top    = 부분 마스크 div top
 * }
 */
var showLoadingMask = function(param) {
    var id     = param["id"];
    var width  = param["width"];
    var height = param["height"];
    var top    = param["top"];

    if (!checkBlank(width)) {
        $('#' + id).show().css("width", width + "px");
    }
    if (!checkBlank(height)) {
        $('#' + id + ">table").attr("height", height);
    }
    if (!checkBlank(top)) {
        $('#' + id).css("top", top + "px");
    }
};

/**
 * @brief 부분 마스크 hide
 *
 * @param id = 부분 마스크 div id
 */
var hideLoadingMask = function(id) {
    $('#' + id).hide();
};

/**
 * @brief 선택 조건으로 검색버튼 클릭시 업체리스트 검색
 */
var cndSearch = {
    "data"     : null,
    "callback" : null,
    "exec"     : function() {
        var url = "/json/business/order_mng/load_member_stats_info.php";
        var data = {
            "cpn_admin_seqno" : $("#cpn_admin").val(),
            "basic_from"      : $("#basic_from").val(),
            "basic_to"        : $("#basic_to").val(),
            "depar"           : $("#depar").val(),
            "empl"            : $("#empl").val(),
            "oper_sys"        : $("#oper_sys").val(),
            "pro"             : $("#pro").val(),
            "member_typ"      : $("#member_typ").val(),
            "member_grade"    : $("#member_grade").val(),
            "search_dvs"      : $("#search_dvs").val(),
            "search_keyword"  : $("#search_keyword").val(),
            "business_dvs"    : $("#business_dvs").val(),
            "dlvr_dvs"        : $("#dlvr_dvs").val(),
            "dlvr_code"       : $("#dlvr_code").val()
        };
        var callback = function(result) {
            $("#member_total_cnt").html(result.total_cnt.format());
            $("#member_result_cnt").html(result.result_cnt.format());
            $("#member_stats_list").html(result.list);
            pagingCommon("member_stats_page",
                         "changePageMemberStats",
                         5,
                         result.result_cnt,
                         5,
                         "init");
            hideLoadingMask("member_stats_list_mask");
            hideLoadingMask("member_stats_page_mask");
        };

        this.data = data;

        var popWidth  = $("#left_content .fix_form").width();
        var popHeight = $("#member_stats_list").height();
        var param = {
            "id"     : "member_stats_list_mask",
            "width"  : popWidth,
            "height" : popHeight
        };
        showLoadingMask(param);
        param = {
            "id"    : "member_stats_page_mask",
            "width" : popWidth,
            "top"   : 581 + (parseInt(popHeight) - 170)
        };
        showLoadingMask(param);
        ajaxCall(url, "json", data, callback);
    }
};

/**
 * @brief 페이징 html 생성 관련 공통함수
 *
 * @param id       = 영역객체 아이디
 * @param funcName = 페이지 변경시 작동할 함수명
 * @param rowCnt   = 행 표시개수
 * @param totalCnt = 전체개수
 * @param blockCnt = 페이지 버튼 표시개수
 * @param dvs      = 초기화인지 전/후 버튼인지 구분값
 */
var pagingCommon = function(id, funcName, rowCnt, totalCnt, blockCnt, dvs) {
    rowCnt   = parseInt(rowCnt);
    totalCnt = parseInt(totalCnt);
    blockCnt = parseInt(blockCnt);

    if (totalCnt === 0 || blockCnt === 0) {
        return false;
    }

    // 최대 페이지
    var maxPage = Math.ceil(totalCnt / rowCnt);
    // active 할 페이지
    var activePage = 1;

    var html = "<a href=\"#\" id=\"" + id + "_fwd\" ";
    html += "onclick=\"pagingCommon('";
    html += id + "','";
    html += funcName + "',";
    html += rowCnt + ',';
    html += totalCnt + ',';
    html += blockCnt + ',';
    html += "'fwd');\">&lt;&lt;</a>";

    if (dvs === "init") {
        // 페이지 영역 초기화
        if (maxPage < blockCnt) {
            blockCnt = maxPage;
        }
        for (var i = 1; i <= blockCnt; i++) {
            html += "<a href=\"#\" ";
            if (i === 1) {
                html += "class=\"" + id + " active_page\" ";
            } else {
                html += "class=\"" + id + "\" ";
            }
            html += "id=\"" + id + '_' + i + "\" ";
            html += "onclick=\"" + funcName + "(" + i + ");\">";
            html += i;
            html += "</a>";
        }
    } else if (dvs === "fwd") {
        // << 버튼 클릭시
        var firstPage = parseInt($('#' + id + "_fwd").next().html());
        activePage = firstPage - 1;
        var block = firstPage - blockCnt;
        if (block < 0) {
            return false;
        }
        for (var i = block; i < firstPage; i++) {
            html += "<a href=\"#\" ";
            if (i === activePage) {
                html += "class=\"" + id + " active_page\" ";
            } else {
                html += "class=\"" + id + "\" ";
            }
            html += "id=\"" + id + '_' + i + "\" ";
            html += "onclick=\"" + funcName + "(" + i + ");\">";
            html += i;
            html += "</a>";
        }
        window[funcName](activePage);
    } else if (dvs === "bwd") {
        // >> 버튼 클릭시
        var lastPage = parseInt($('#' + id + "_bwd").prev().html());
        activePage = lastPage + 1;
        if (maxPage === lastPage) {
            return false;
        }
        var block = lastPage + blockCnt;
        for (var i = lastPage + 1; i <= block; i++) {
            if (maxPage < i) {
                break;
            }
            html += "<a href=\"#\" ";
            if (i === activePage) {
                html += "class=\"" + id + " active_page\" ";
            } else {
                html += "class=\"" + id + "\" ";
            }
            html += "id=\"" + id + '_' + i + "\" ";
            html += "onclick=\"" + funcName + "(" + i + ");\">";
            html += i;
            html += "</a>";
        }
        window[funcName](activePage);
    }
    html += "<a href=\"#\" id=\"" + id + "_bwd\" ";
    html += "onclick=\"pagingCommon('";
    html += id + "','";
    html += funcName + "',";
    html += rowCnt + ','; html += totalCnt + ','; html += blockCnt + ',';
    html += "'bwd');\">&gt;&gt;</a>";

    $('#' + id).html(html);
};

/**
 * @brief 업체리스트에서 회원명 검색시 입력한 회원명으로 재검색
 */
var innerSearchMemberStats = {
    "isProc" : false,
    "exec" : function() {
        var url = "/json/business/order_mng/load_member_stats_info.php";
        var data = cndSearch.data;
        if (checkBlank(data)) {
            return false;
        }
        data.stat_member_name = $("#stat_member_name").val();
        var callback = function(result) {
            $("#member_total_cnt").html(result.total_cnt.format());
            $("#member_result_cnt").html(result.result_cnt.format());
            $("#member_stats_list").html(result.list);
            pagingCommon("member_stats_page",
                         "changePageMemberStats",
                         5,
                         result.result_cnt,
                         5,
                         "init");
            hideLoadingMask("member_stats_list_mask");
            hideLoadingMask("member_stats_page_mask");
            this.proc = true;
        };
    
        var popWidth  = $("#left_content .fix_form").width();
        var popHeight = $("#member_stats_list").height();
        var param = {
            "id"     : "member_stats_list_mask",
            "width"  : popWidth,
            "height" : popHeight
        };
        showLoadingMask(param);
        param = {
            "id"    : "member_stats_page_mask",
            "width" : popWidth,
            "top"   : 581 + (parseInt(popHeight) - 170)
        };
        showLoadingMask(param);
        this.proc = true;
        ajaxCall(url, "json", data, callback);
    }
};

/**
 * @brief 집계리스트 페이지 변경시 호출
 *
 * @param page = 선택한 페이지
 */
var changePageMemberStats = function(page) {
    if ($("#member_stats_page_" + page).hasClass("active_page")) {
        return false;
    }
    $(".member_stats_page").removeClass("active_page");
    $("#member_stats_page_" + page).addClass("active_page");

    var url = "/json/business/order_mng/load_member_stats_info.php";
        var data = cndSearch.data;
        data.page = page;
        data.page_dvs = '1';
        var callback = function(result) {
            hideLoadingMask("member_stats_list_mask");
            hideLoadingMask("member_stats_page_mask");
            $("#member_stats_list").html(result.list);
        };

            var popWidth  = $("#left_content .fix_form").width();
            var popHeight = $("#member_stats_list").height();
            var param = {
                "id"     : "member_stats_list_mask",
                "width"  : popWidth,
                "height" : popHeight
            };
            showLoadingMask(param);
            param = {
                "id"    : "member_stats_page_mask",
                "width" : popWidth,
                "top"   : 581 + (parseInt(popHeight) - 170)
            };
            showLoadingMask(param);
        ajaxCall(url, "json", data, callback);
    };

    /**
     * @brief 집계리스트 클릭시 정보 불러오는 함수
     *
     * @param seqno = 회원일련번호
     */
    var loadMemberStatsInfo = {
        "seqno" : null,
        "exec"  : function(seqno) {
            // 검색회원정보
            $(".member_stats_tr").removeClass("active_tr");
            $("#member_stats_tr_" + seqno).addClass("active_tr");

            this.seqno = seqno;

            var url = "/json/business/order_mng/load_member_detail_info.php";
            var data = {
                "seqno" : seqno
            };
            var callback = function(result) {
                $("#search_office_nick").val(result.office_nick);
                $("#search_member_tel").val(result.member_tel);
                $("#search_member_cell").val(result.member_cell);
                //$("#search_").val(result.);
                $("#search_accting_tel").val(result.accting_tel);
                $("#search_member_typ").html("<option>" + result.member_typ + "</option>");
                $("#search_member_grade").html("<option>" + result.member_grade + "</option>");
                $("#search_grade_lack_price").val(result.grade_lack_price.format());
                $("#search_first_join_date").val(result.first_join_date);
                $("#search_final_order_date").val(result.final_order_date);
                $("#search_sum_oa").val(result.sum_oa.format());
                $("#search_carryforward_oa").val(result.carryforward_oa.format());
                $("#search_sum_sales_price").val(result.sum_sales_price.format());
                $("#search_sum_sale_price").val(result.sum_sale_price.format());
                $("#search_sum_net_price").val(result.sum_net_price.format());
                $("#search_sum_depo_price").val(result.sum_depo_price.format());
                $("#search_loan_collect_dvs").html("<option>" + result.loan_collect_dvs + "</option>");
                $("#search_three_mon_avr_price").val(result.three_mon_avr_price.format());
                $("#search_last_mon_sales").val(result.last_mon_sales.format());
                $("#search_loan_limit_price").val(result.loan_limit_price.format());
                $("#search_rest_loan_price").val(result.rest_loan_price.format());
                $("#search_loan_useage").val(result.loan_useage);

                if (checkBlank(result.loan_useage) ||
                        parseInt(result.loan_useage) < 80) {
                    $("#search_loan_useage_warning").removeClass("btn_danger");
                    $("#search_loan_useage_warning").addClass("btn_view");
                    $("#search_loan_useage_warning").html("안전");
                } else {
                    $("#search_loan_useage_warning").removeClass("btn_view");
                    $("#search_loan_useage_warning").addClass("btn_danger");
                    $("#search_loan_useage_warning").html("경고");
                }

                hideLoadingMask("member_info_mask");
            };
            var popWidth = $("#right_content > .search_box").width();
            var param = {
                "id"     : "member_info_mask",
                "width"  : popWidth
            };
            showLoadingMask(param);
            ajaxCall(url, "json", data, callback);

            // 데이터 로드 플래그 초기화
            tabDataLoad.sales_info = false;
            tabDataLoad.order_info = false;
            tabDataLoad.crm_info   = false;
            tabDataLoad.prdt_info  = false;
            chartDataLoad.sales_info = false;
            chartDataLoad.order_info = false;
            chartDataLoad.crm_info   = false;
            chartDataLoad.prdt_info  = false;

            // 검색회원정보 호출 후 아래 탭에 대한 정보 검색
            var curTab = $("input[name='tabs']:checked").val();
            changeTab(curTab);
            loadChartData(curTab);
        }
    };

    /**
     * @brief 탭 변경시 정보검색
     */
    var changeTab = function(curTab) {
        if (tabDataLoad[curTab]) {
            return false;
        }

        if (curTab === "sales_info") {
            loadSalesInfo("common");
        } else if (curTab === "order_info") {
            loadOrderInfo("common");
        } else if (curTab === "crm_info") {
            loadCrmInfo.exec("common");
        } else if (curTab === "prdt_info") {
        }

        //tabDataLoad[curTab] = true;
    };

    /**
     * @brief 차트 데이터 검색했는지 확인
     */
    var loadChartData = function(curTab) {
        if (chartDataLoad[curTab]) {
            return false;
        }

        if (curTab === "sales_info") {
            loadSalesInfoChartData();
        } else if (curTab === "order_info") {
        } else if (curTab === "crm_info") {
        } else if (curTab === "prdt_info") {
        }

        //chartDataLoad[curTab] = true;
    };

    /**
     * @brief 회원명 수정 클릭시 데이터 변경
     */
    var modiMemberOfficeNick = function() {
        var seqno = loadMemberStatsInfo.seqno;

        if ($("#search_office_nick").prop("disabled")) {
            $("#search_office_nick").prop("disabled", false);
            $("#search_office_nick").css("background-color", "#fff");
        } else {
            $("#search_office_nick").prop("disabled", true);
            $("#search_office_nick").css("background-color", "#ebeae5");

            var url = "/proc/business/order_mng/update_member_info.php";
            var data = {
                "seqno"       : seqno,
                "office_nick" : $("#search_office_nick").val()
            };
            var callback = function(result) {
                hideLoadingMask("member_info_mask");
                if (!checkBlank(result)) {
                    alert(result);
                    return false;
                }
            };

            var param = {
                "id"     : "member_info_mask",
                "width"  : popWidth
            };
            showLoadingMask(param);
            ajaxCall(url, "text", data, callback);
        }
    };

    /**
     * @brief 여신한도금액 수정 클릭시 데이버 변경
     */
    var modiLoanLimitPrice = function() {
        var seqno = loadMemberStatsInfo.seqno;

        if ($("#search_member_typ").val() !== "예외업체") {
            return alertReturnFalse("예외업체만 수정이 가능합니다.");
        }

        if ($("#search_loan_limit_price").prop("disabled")) {
            $("#search_loan_limit_price").prop("disabled", false);
            $("#search_loan_limit_price").css("background-color", "#fff");
        } else {
            $("#search_loan_limit_price").prop("disabled", true);
            $("#search_loan_limit_price").css("background-color", "#ebeae5");

            var url = "/proc/business/order_mng/update_member_info.php";
            var data = {
                "seqno"            : seqno,
                "loan_limit_price" : $("#search_loan_limit_price").val()
            };
            var callback = function(result) {
                hideLoadingMask("member_info_mask");
                if (!checkBlank(result)) {
                    return alertReturnFalse(result);
                }
            };

            var param = {
                "id"     : "member_info_mask",
                "width"  : popWidth
            };
            showLoadingMask(param);
            ajaxCall(url, "text", data, callback);
        }
    };

    /**
     * @brief 명세서 출력 클릭시 팝업 출력
     */
    var showSpecificationPop = function() {
        var seqno = loadMemberStatsInfo.seqno;
        if (checkBlank(seqno)) {
            return false;
        }

        var url = "/business/popup/pop_specification.html?"
        url += "from=" + $("#basic_from").val();
        url += "&to=" + $("#basic_to").val();
        url += "&seqno=" + seqno;
        url += "&nick=" + encodeURI($("#search_office_nick").val());
        window.open(url);
    };

    /**
     * @brief 매출 거래현황정보 검색
     *
     * @param searchDvs = 검색위치 구분값
     */
    var loadSalesInfo = function(searchDvs) {
        var seqno = loadMemberStatsInfo.seqno;
        if (checkBlank(seqno)) {
            return false;
        }

        var from = $("#basic_from").val();
        var to   = $("#basic_to").val();

        if (searchDvs === "sales_info") {
            from = $("#sales_info_from").val();
            to   = $("#sales_info_to").val();
        }

        var url = "/json/business/order_mng/load_sales_info.php";
        var data = {
            "seqno"    : seqno,
            "term_dvs" : $("#content1 .btn_active").val(),
            "from"     : from,
            "to"       : to
        };
        var callback = function(result) {
            $("#sales_info_sum").html(result.thead);
            $("#sales_info_list").html(result.tbody);
            hideLoadingMask("sales_info_mask");
        };
        var popWidth  = $("#tableData2").width();
        var popHeight = $("#tableData2").height();
        var param = {
            "id"     : "sales_info_mask",
            "width"  : popWidth,
            "height" : popHeight
        };
        showLoadingMask(param);
        ajaxCall(url, "json", data, callback);
    };

    /**
     * @brief 매출 거래현황정보 차트 데이터 검색
     */
    var loadSalesInfoChartData = function() {
        var seqno = loadMemberStatsInfo.seqno;
        if (checkBlank(seqno)) {
            return false;
        }

        var date = new Date();

        var url = "/json/business/order_mng/load_sales_info_chart_data.php";
        var data = {
            "seqno" : seqno,
            "year"  : date.getFullYear(),
            "month" : date.getMonth()
        };
        var callback = function(result) {
            chart.salesInfo.sumOa.xAxis[0]
                           .setCategories(result.sum_oa.categories);
            chart.salesInfo.sumOa.series[0]
                           .setData(result.sum_oa.data);

            chart.salesInfo.sumNet.xAxis[0]
                                  .setCategories(result.sum_net.categories);
            chart.salesInfo.sumNet.series[0]
                                  .setData(result.sum_net.data);

            chart.salesInfo.sumSale.xAxis[0]
                                   .setCategories(result.sum_sale.categories);
            chart.salesInfo.sumSale.series[0]
                                   .setData(result.sum_sale.data);

            chart.salesInfo.sumDepo.xAxis[0]
                                   .setCategories(result.sum_depo.categories);
            chart.salesInfo.sumDepo.series[0]
                                   .setData(result.sum_depo.data);

            chart.salesInfo.yearSumNet.xAxis[0]
                                      .setCategories(result.year_sum_net.categories);
            chart.salesInfo.yearSumNet.series[0]
                                      .setData(result.year_sum_net.data);
            hideLoadingMask("sales_info_chart_mask");
        };
        var param = {
            "id"  : "sales_info_chart_mask",
            "top" : 920 + parseInt($("#tableData2").height())
        };
        showLoadingMask(param);
        ajaxCall(url, "json", data, callback);
    };

    /**
     * @brief 매출 거래현황정보 검색
     *
     * @param searchDvs = 검색위치 구분값
     */
    var loadOrderInfo = function(searchDvs) {
        var seqno = loadMemberStatsInfo.seqno;
        if (checkBlank(seqno)) {
            return false;
        }

        var data = {
            "seqno" : seqno
        };
        var from = $("#basic_from").val();
        var to   = $("#basic_to").val();

        if (searchDvs === "order_info") {
            from = $("#order_info_from").val();
            to   = $("#order_info_to").val();
            data.cate_top = $("#order_info_cate_top").val();
            data.cate_mid = $("#order_info_cate_mid").val();
            data.cate_bot = $("#order_info_cate_bot").val();
        }

        var url = "/json/business/order_mng/load_order_info.php";
        data.from = from;
        data.to   = to;
        var callback = function(result) {
            $("#order_info_sum").html(result.thead);
            $("#order_info_list").html(result.tbody);
            hideLoadingMask("order_info_mask");
        };
        var popWidth  = $("#content2 .table_detail").width();
        var popHeight = $("#content2 .table_detail").height();
        var param = {
            "id"     : "order_info_mask",
            "width"  : popWidth,
            "height" : popHeight
        };
        showLoadingMask(param);
        ajaxCall(url, "json", data, callback);
    };

    /**
     * @brief 주문정보 상세정보 row 숨김/확장
     *
     * @param val = 대상 객체 클래스 셀렉터 생성용 특정값
     * @param dvs = toggle 위치 구분값
     *
     */
    var toggleRow = function(val, dvs) {
        var selector = ".toggle_" + dvs + '_' + val;
        if (dvs === "mid" || !checkBlank($(selector).html())) {
            $(selector).toggleClass("hidden_row");

            if (dvs === "mid" && $(selector).hasClass("hidden_row")) {
                $(".toggle_bot").addClass("hidden_row");
            }
            return false;
        }

        var url = "/ajax/business/order_mng/load_order_info_detail.php";
        var data = {
            "order_num" : val
        };
        var callback = function(result) {
            $(selector).html(result);
            $(selector).toggleClass("hidden_row");
        };

        ajaxCall(url, "html", data, callback);
    };

    /**
     * @brief CRM정보 검색
     *
     * @param searchDvs = 검색위치 구분값
     * @writer 
     */
    var loadCrmInfo = {
        "data" : null,
        "exec" : function(searchDvs) {
            var seqno = loadMemberStatsInfo.seqno;
            if (checkBlank(seqno)) {
                return false;
            }

            var deparName = $("#depar > option:selected").text();
            var emplSeqno = $("#empl").val();
            var emplName  = $("#empl > option:selected").text();
            var from = $("#basic_from").val();
            var to   = $("#basic_to").val();
            var dvs       = '';
            var searchTxt = '';

            if (searchDvs === "crm_info") {
                deparName = $("#crm_info_depar > option:selected").text();
                emplSeqno = $("#crm_info_empl").val();
                emplName  = $("#crm_info_empl > option:selected").text();
                from = $("#crm_info_date").val();
                to   = $("#crm_info_date").val();
                dvs       = $("#crm_info_search_dvs").val();
                searchTxt = $("#crm_info_search_txt").val();
            }

            if (checkBlank(emplSeqno)) {
                emplName = '';
            }

            var url = "/json/business/order_mng/load_crm_info.php";
            var data = {
                "depar_name" : deparName,
                "empl_seqno" : emplSeqno,
                "empl_name"  : emplName,
                "search_dvs" : searchDvs,
                "from"       : from,
                "to"         : to,
                "dvs"        : dvs,
                "search_txt" : searchTxt
            };
            var callback = function(result) {
                $("#crm_info_sum").html(result.thead);
                $("#crm_info_list").html(result.tbody);

                pagingCommon("crm_info_page",
                             "changePageCrmInfo",
                             5,
                             result.result_cnt,
                             5,
                             "init");
                hideLoadingMask("crm_info_list_mask");
                hideLoadingMask("crm_info_list_page_mask");
            };

            this.data = data;

            var popWidth  = $("#tableData4").width();
            var popHeight = $("#tableData4").height();
            var param = {
                "id"     : "crm_info_list_mask",
                "width"  : popWidth,
                "height" : popHeight
            };
            showLoadingMask(param);
            param = {
                "id"    : "crm_info_list_page_mask",
                "width" : popWidth,
                "top"   : 893 + (parseInt(popHeight) - 33)
            };
            showLoadingMask(param);
            ajaxCall(url, "json", data, callback);
        }
    };

    /**
     * @brief 집계리스트 페이지 변경시 호출
     *
     * @param page = 선택한 페이지
     */
    var changePageCrmInfo = function(page) {
        if ($("#crm_info_page_" + page).hasClass("active_page")) {
            return false;
        }

        $(".crm_info_page").removeClass("active_page");
        $("#crm_info_page_" + page).addClass("active_page");

        var url = "/json/business/order_mng/load_crm_info.php";
        var data = loadCrmInfo.data;
        data.page = page;
        data.page_dvs = '1';
        var callback = function(result) {
            $("#crm_info_sum").html(result.thead);
            $("#crm_info_list").html(result.tbody);

            hideLoadingMask("crm_info_list_mask");
            hideLoadingMask("crm_info_list_page_mask");
        };

        var popWidth  = $("#tableData4").width();
        var popHeight = $("#tableData4").height();
        var param = {
            "id"     : "crm_info_list_mask",
            "width"  : popWidth,
            "height" : popHeight
        };
        showLoadingMask(param);
        param = {
            "id"    : "crm_info_list_page_mask",
            "width" : popWidth,
            "top"   : 893 + (parseInt(popHeight) - 33)
        };
        showLoadingMask(param);
        ajaxCall(url, "json", data, callback);
    };

    /**
     * @brief 집계리스트 클릭시 정보 불러오는 함수
     *
     * @param seqno = 회원일련번호
     */
    var loadCrmDetailInfo = {
        "exec" : function(seqno) {
            $(".crm_info_tr").removeClass("active_tr");
            $("#crm_info_tr_" + seqno).addClass("active_tr");
        }
    };

    /**
     * @brief 키다운 이벤트
     * @comment : 일단은 사용하지 않는데, 나중에 반영 예정
     *
     * @writer montvert
     */
    var keydownEventFunc = function(event, funcName) {
        if (event.keyCode === 13) {
            if (window[funcName].isProc) {
                return false;
        }

        window[funcName].exec();
        return false;
    }
};
