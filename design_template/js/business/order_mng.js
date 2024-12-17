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
    "sales_info"   : false,
    "order_info"   : false,
    "order_status" : false,
    "manu_limit"   : false,
    "crm_info"     : false,
    "new_member"   : false,
    "prdt_info"    : false
};

 /**
  * @brief CRM탭 정보 최초로딩했는지 여부
  * 탭 클릭할 때 마다 재조회 하는가 방지용
  */
var tabCrmDataLoad = {
    "business" : false,
    "collect"  : false
};

 /**
  * @brief 각 탭에서 차트 정보 최초로딩했는지 여부
  * 탭 클릭할 때 마다 재조회 하는가 방지용
  */
var chartDataLoad = {
    "sales_info"   : false,
    "order_info"   : false,
    "order_status" : false,
    "manu_limit"   : false,
    "crm_info"     : false,
    "new_member"   : false,
    "prdt_info"    : false
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
    }).datepicker("setDate", '0').attr('readonly', 'readonly');

    // 매출 거래현황정보 차트 초기화
    var salesInfoChartOption = {
        chart  : {
            "type"        : "column",
            "borderColor" : '#DFDFDF',
            "borderWidth" : 1
        },
        xAxis  : {"categories" : []},
        yAxis  : {"title" : {"text" : "단위(원)"}},
        legend : {"enabled" : false},
        tooltip: {
            "headerFormat" : '',
            "pointFormat"  : "<b>{point.y}</b>원<br/>"
        },
        series : [{"data" : []}]
    };
    salesInfoChartOption.title = {"text" : "총미수액"};
    chart.salesInfo.sumOa =
        new Highcharts.chart("sum_oa", salesInfoChartOption);

    salesInfoChartOption.title = {"text" : "순 매출액"};
    chart.salesInfo.sumNet =
        new Highcharts.chart("sum_net", salesInfoChartOption);

    salesInfoChartOption.title = {"text" : "에누리"};
    chart.salesInfo.sumSale =
        new Highcharts.chart("sum_sale", salesInfoChartOption);

    salesInfoChartOption.title = {"text" : "입금액"};
    chart.salesInfo.sumDepo =
        new Highcharts.chart("sum_depo", salesInfoChartOption);

    salesInfoChartOption.title = {"text" : "연 순매출액"};
    chart.salesInfo.yearSumNet =
        new Highcharts.chart("year_sum_net", salesInfoChartOption);

    // 품목별 현황정보 차트 초기화
    /*
    Highcharts.chart('container_pie1', {
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie',
            options3d: {
                enabled: true,
                alpha: 45,
                beta: 0
            }
        },
        title: {
            text: 'Browser market shares January, 2015 to May, 2015'
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                depth: 40,
                cursor: 'pointer',
                dataLabels: {
                    enabled: false
                },
                showInLegend: true
            }
        },
        series: [{
            name: 'Brands',
            colorByPoint: true,
            data: [{
                name: 'Microsoft Internet Explorer',
                y: 56.33
            }, {
                name: 'Chrome',
                y: 24.03,
                sliced: true,
                selected: true
            }, {
                name: 'Firefox',
                y: 10.38
            }, {
                name: 'Safari',
                y: 4.77
            }, {
                name: 'Opera',
                y: 0.91
            }, {
                name: 'Proprietary or Undetectable',
                y: 0.2
            }]
        }]
    });
    */

    // 카드번호 숫자만 입력받게 하는 함수
    $("#sales_depo_card_num").keydown(function (e) {
        // 허용 : backsp, del, tab, esc, enter, .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            // 허용 : Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            // 허용 : home, end, left, right, down, up 
            (e.keyCode >= 35 && e.keyCode <= 40)) {
                return;
            }
            
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();    
            } 
    });

    $($("#order_status_depar > option")[0]).remove();
    $("#sales_depo_input_pre").trigger("change");

    $("#chkAll").click(function () {
        $("input:checkbox[name=\"crm_memo_chk\"]").prop('checked', function() {
                return !$(this).prop('checked');
        }); 
    });   

    // 쿠키값에 따라서 키워드, 팀/담당자 기본설정
    var searchDvs = getCookie("search_dvs");
    var depar = getCookie("depar");
    var empl = getCookie("empl");
    
    if (!checkBlank(searchDvs)) {
        $("#search_dvs").val(searchDvs);
    }
    if (!checkBlank(depar)) {
        $("#depar").val(depar);
    }
    if (!checkBlank(empl)) {
        $("#empl").val(empl);
    }
});

/**
 * @brief Modal영역 열고닫기 함수
 *
 * @param dvs = 레이어팝업 아이디 구분값
 * @param param = 정보 파라미터
 */
function showModal(dvs, param) {
    $('.modalOverlay').css('display', 'block');
    $('#' + dvs + '_modal').css('display', 'block');
    $('#' + dvs + '_modal').attr('aria-hidden', 'false');

    if ($('#' + dvs + '_name').length > 0) {
        $('#' + dvs + '_name').html(param.name);
    }

    if ($('#' + dvs + '_cell_num').length > 0) {
        $('#' + dvs + '_cell_num').html(param.cell);
    }
};
function hideModal(dvs) {
    $('.modalOverlay').css('display', 'none');
    $('#' + dvs + '_modal').css('display', 'none');
    $('#' + dvs + '_modal').attr('aria-hidden', 'true');

    $('#' +  dvs + '_title').val('');
    $('#' +  dvs + '_msg').val('');
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
 * @brief 선택 조건으로 검색버튼 클릭시 업체리스트 검색
 */
var cndSearch = {
    "data"     : null,
    "callback" : null,
    "setData"  : function() {
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
            "search_keyword"  : $("#search_keyword").val(),
            "business_dvs"    : $("#business_dvs").val(),
            "dlvr_dvs"        : $("#dlvr_dvs").val(),
            "dlvr_code"       : $("#dlvr_code").val(),
            "search_dvs"      : $("#search_dvs").val(),
            "oa_yn"           : $("#oa_yn").val()
        };

        return data;
    },
    "exec"     : function() {
        var url = "/json/business/order_mng/load_member_stats_info.php";
        var data = this.setData();
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
            hideLoadingMask();

            $("#search_body").slideUp();
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

        var cookieParam = {
            "data"   : [
                "search_dvs|" + $("#search_dvs").val(),
                "depar|" + $("#depar").val(),
                "empl|" + $("#empl").val()
            ],
            "expire" : 3600 * 24 * 30
        };
        setCookie(cookieParam);
    },
    "key" : function(event) {
        if (event.keyCode == 13) {
            cndSearch.exec();
        }
    }
};

/**
 * @brief 업체리스트에서 회원명 검색시 입력한 회원명으로 재검색
 */
var innerSearchMemberStats = function() {
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
 * @brief 회원 검색리스트 페이지 변경시 호출
 *
 * @param page = 선택한 페이지
 */
var changePageMemberStats = function(page) {
    if ($("#member_stats_page_" + page).hasClass("page_accent")) {
        return false;
    }
    /*var seqno = loadMemberStatsInfo.seqno;
    if(checkBlank(seqno)) {
        return false;
    }*/
    if (isNaN(page)) {
        return false;
    }

    $(".member_stats_page").removeClass("page_accent");
    $("#member_stats_page_" + page).addClass("page_accent");
    
    var url = "/json/business/order_mng/load_member_stats_info.php";
    var data = cndSearch.data;
    data.page = page;
    data.page_dvs = '1';
    var callback = function(result) {
        hideLoadingMask("member_stats_list_mask");
        hideLoadingMask("member_stats_page_mask");
        $("#member_stats_list").html(result.list);
        
        //선택 영역 기억
        $("#" + saveSelectedBarArea.str_stats + "").addClass("active_tr");
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
 * @param name = 회원명
 * @param nick = 사내닉네임
 */
var loadMemberStatsInfo = {
    "seqno" : null,
    "name"  : null,
    "nick"  : null,
    "exec"  : function(seqno, name, nick) {

        // 검색회원정보
        $(".member_stats_tr").removeClass("active_tr");
        $("#member_stats_tr_" + seqno).addClass("active_tr");

        saveSelectedBarArea.exec();

        this.seqno = seqno;
        this.name  = name;
        this.nick  = nick;

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
                    parseInt(result.loan_useage) <= 20) {
               // $("#search_loan_useage_warning").removeClass("btn_crm_info_del");
               // $("#search_loan_useage_warning").addClass("btn_view");
               // $("#search_loan_useage_warning").html("안전");
                $("#search_loan_useage_warning").removeClass("btn_right_contents_danger");
                $("#search_loan_useage_warning").addClass("btn_right_contents_danger_red");
            } else {
               // $("#search_loan_useage_warning").removeClass("btn_view");
               // $("#search_loan_useage_warning").addClass("btn_crm_info_del");
               // $("#search_loan_useage_warning").html("경고");
                $("#search_loan_useage_warning").removeClass("btn_right_contents_danger_red");
                $("#search_loan_useage_warning").addClass("btn_right_contents_danger");
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
        initObjValAllFalse("tabDataLoad");
        initObjValAllFalse("tabCrmDataLoad");
        initObjValAllFalse("chartDataLoad");

        // 검색회원정보 호출 후 아래 탭에 대한 정보 검색
        var curTab = $("input[name='tabs']:checked").val();
        changeTab(curTab);
        loadChartData(curTab);
        initSalesDetail();
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
    } else if (curTab === "order_status") {
        loadOrderStatus.exec("common");
    } else if (curTab === "manu_limit") {
        loadManuLimitList.exec("common");
    } else if (curTab === "crm_info") {
        var crmTab = $("input[name='houses-state']:checked").val();
        //loadCrmInfo.exec("common");
        changeCrmTab(crmTab);
    } else if (curTab === "new_member") {
        loadNewMember.exec("common");
    } else if (curTab === "prdt_info") {
        loadPrdtInfo("common");
    }

    //tabDataLoad[curTab] = true;
};

/**
 * @brief CRM정보 탭 변경시 정보검색
 */
var changeCrmTab = function(crmTab) {
    if (tabCrmDataLoad[crmTab]) {
    return false;
    }
    if (crmTab === "crm_business") {
        loadCrmInfoBusinessList.exec('common');
        initCrmInfoBusiness();
    } else if (crmTab === "crm_collect") {
        checkExcptMember();
    }
    // tabCrmDataLoad[crmTab] = true;

};


/**
 * @brief CRM정보 탭 변경시 예외회원 확인
 * @brief 이 함수를 거쳐야 수금탭으로 넘어갈 수 있음
 */
var checkExcptMember = function() {
    var seqno = loadMemberStatsInfo.seqno;
    if(checkBlank(seqno)) {
        return false;
    }
    var url = "/json/business/order_mng/checkExcptMember.php";
     
    var data = {
        "member_seqno" : seqno
    };

    //2017.07.24 일반업체도 수금 탭 들어가게 해달라고 요청 : 일반회원 분기 주석처리
    var callback = function(result) {
        var res = result.member_typ;
       /* if (res == "일반회원") {
            alert("예외업체가 아닙니다!");
            $('#starks-tab').click(); */
       /* } else */if (res == "예외업체") {
            loadCrmInfoCollectFirst.exec();
            loadCrmInfoCollectList.exec('common');
            $("#crm_info_search_cs_type").css('background-color', '#ffffff');
            $("#crm_info_search_cs_type").prop("disabled", false);
            toggleWeekMonth('crm_info_collect_wm', 'crm_info_collect_day');
        } else {
            /*alert("예외업체가 아닙니다!");
            $('#starks-tab').click(); */
            loadCrmInfoCollectFirst.exec();
            loadCrmInfoCollectList.exec('common');
            $("#crm_info_search_cs_type").css('background-color', '#ffffff');
            $("#crm_info_search_cs_type").prop("disabled", false);
            toggleWeekMonth('crm_info_collect_wm', 'crm_info_collect_day');

        }
    };
    
    ajaxCall(url, "json", data, callback);
}

/**
 * @brief 차트 데이터 검색했는지 확인
 */
var loadChartData = function(curTab) {
    if (chartDataLoad[curTab]) {
        return false;
    }

    if (curTab === "sales_info") {
        loadSalesInfoChartData("common");
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
        $("#search_office_nick").css("background-color", "#f1f1f1");

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
            "id"     : "member_info_mask"
          //  "width"  : popWidth
        };
        showLoadingMask(param);
        ajaxCall(url, "text", data, callback);
    }
};

/**
 * @brief 여신한도금액 수정 클릭시 데이터 변경
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
        $("#search_loan_limit_price").css("background-color", "#f1f1f1");

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
            "id"     : "member_info_mask"
           // "width"  : popWidth
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
 * @brief CRM정보 집계 검색 
 */
var showTotal = function(termDvs) {
    var seqno = loadCrmInfoBusiness.member_seqno;
    if (checkBlank(seqno)) {
        return false;
    }

    var crmTab  = $("input[name='houses-state']:checked").val();
    var from    = $("#basic_from").val();
    var to      = $("#basic_to").val();

    var url  = "/json/business/order_mng/load_crm_info_total.php";
    var data = {
        "seqno"     : seqno,
        "term_dvs"  : termDvs,
        "from"      : from,
        "to"        : to
    };
    var callback = function(result) {
        $("#crm_total_tbody_" + termDvs).html(result.tbody);
        hideLoadingMask();
    };

    showLoadingMask();
    ajaxCall(url, "json", data, callback);
}

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
        hideLoadingMask();
        initSalesDetail();
    };
    var popWidth  = $("#tableData2").width();
    var popHeight = $("#tableData2").height();
    var param = {
        "id"     : "sales_info_mask",
        "width"  : popWidth,
        "height" : popHeight
    };
    showLoadingMask();
    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 매출 거래현황정보 차트 데이터 검색
 */
var loadSalesInfoChartData = function(searchDvs) {
    var seqno = loadMemberStatsInfo.seqno;
    if (checkBlank(seqno)) {
        return false;
    }

    var from = $("#basic_from").val();

    if (searchDvs === "sales_info") {
        from = $("#sales_info_from").val();
    }

    var url = "/json/business/order_mng/load_sales_info_chart_data.php";
    var data = {
        "seqno" : seqno,
        "from"  : from
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
 * @brief 주문정보 검색
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
        data.search_dvs     = $("#order_info_search_dvs").val();
        data.search_keyword = $("#order_info_search_keyword").val();
    }

    var url = "/json/business/order_mng/load_order_info.php";
    data.from = from;
    data.to   = to;
    var callback = function(result) {
        $("#order_info_sum").html(result.thead);
        $("#order_info_list").html(result.tbody);
        hideLoadingMask();
    };
    var popWidth  = $("#content2 .table_detail").width();
    var popHeight = $("#content2 .table_detail").height();
    var param = {
        "id"     : "order_info_mask",
        "width"  : popWidth,
        "height" : popHeight
    };
    showLoadingMask();
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
 * @brief CRM 정보 검색
 *
 * @param searchDvs = 검색위치 구분값
 */
var loadCrmInfo = {
    "data" : null,
    "exec" : function(searchDvs) {
        var seqno = loadMemberStatsInfo.seqno;
        if (checkBlank(seqno)) {
            return false;
        }

        var deparName  = $("#depar > option:selected").text();
        var emplSeqno  = $("#empl").val();
        var emplName   = $("#empl > option:selected").text();
        var memberName = loadMemberStatsInfo.name;
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
            deparName = '';
            emplName = '';
        }

        var url = "/json/business/order_mng/load_crm_info.php";
        var data = {
            "member_seqno": seqno,
            "depar_name"  : deparName,
            "empl_seqno"  : emplSeqno,
            "empl_name"   : emplName,
            "member_name" : memberName,
            "search_dvs"  : searchDvs,
            "from"        : from,
            "to"          : to,
            "dvs"         : dvs,
            "search_txt"  : searchTxt
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
            //hideLoadingMask("crm_info_list_page_mask");
        };

        this.data = data;

        var popWidth  = $("#tableData4").width();
        var popHeight = $("#content3 .detail_table").height();
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
 * @brief crm정보 문자 버튼 클릭시 팝업 출력
 *
 * @param val = 대상 객체 클래스 셀렉터 생성용 특정값
 * @param dvs = toggle 위치 구분값
 */
var showCrmMmsPop = function(dvs) {

    var url = "/json/business/order_mng/load_crm_info_member_info.php";
    var data = {
        "mms_dvs"       : dvs,
        "crm_biz_seqno" : loadCrmInfoBusiness.seqno,
        "crm_col_seqno" : loadCrmInfoCollect.seqno
    };
    
    var callback = function(result) {
        var param = {
            "name" : result.name,
            "cell" : result.cell
        };
        
        showModal(dvs, param);
    };
    ajaxCall(url, "json", data, callback);

};

/**
 * @brief crm정보 페이지 변경시 호출
 *
 * @param page = 선택한 페이지
 */
var changePageCrmInfo = function(page) {
    if ($("#crm_info_page_" + page).hasClass("page_accent")) {
        return false;
    }
    if (isNaN(page)) {
        return false;
    }

    $(".crm_info_page").removeClass("page_accent");
    $("#crm_info_page_" + page).addClass("page_accent");
    var crmTab = $("input[name='houses-state']:checked").val();
    var url    = "/json/business/order_mng/load_crm_info_list.php";
    var data   = "";

    // TODO if문으로 변경 필요, 영업/수금 탭에 따라 분기
    if (crmTab == "crm_business") {
        data = loadCrmInfoBusinessList.data;
    } else if (crmTab == "crm_collect") {
        data = loadCrmInfoCollectList.data;
    }
    data.page = page;
    data.page_dvs = '1';
    var callback = function(result) {
        $("#crm_info_sum").html(result.thead);
        $("#crm_info_list").html(result.tbody);

        hideLoadingMask();

        //선택 영역 기억
        $("#" + saveSelectedBarArea.str_crm_biz + "").addClass("active_tr");
        $("#" + saveSelectedBarArea.str_crm_col + "").addClass("active_tr");
    };

    showLoadingMask();
    ajaxCall(url, "json", data, callback);
};

/**
 * @brief CRM 정보 메모 모달 팝업
 *
 */
var showCrmMemoModal = {
    "buttonDvs" : null,
    "exec"      : function(funcDvs) {

        var crmTab = $("input[name='houses-state']:checked").val();

        var dvs = "";
        // 영업탭일때
        if (crmTab === "crm_business") {
            dvs = loadCrmInfoBusiness.seqno;
            bcDvs = "business";
        // 수금탭일때 
        } else if (crmTab === "crm_collect") {
            dvs = loadCrmInfoCollect.seqno; 
            bcDvs = "collect";
        }

        // 추가일 때
        if (funcDvs == "insert") {
            
            if (checkBlank(dvs)) {
                alert("메모를 추가할 CRM을 선택해 주세요.");
                return false;
            }

            $("#crm_memo_modal").reveal();
            $("#memo_date").val("");
            $("#memo_cont").val("");

        // 수정일 때
        } else if (funcDvs == "update") {
            
            var chkLen = $('input:checkbox[name="crm_memo_chk"]:checked').length;
            if (chkLen == '1') {
                var chkNum = $("input[name=crm_memo_chk]:checked").prop('id');
                var seqno  = chkNum.substr(13);
                var url    = "/json/business/order_mng/load_crm_memo.php"; 

                var data   = {
                    "dvs"        : bcDvs,
                    "func_dvs"   : funcDvs,
                    "memo_seqno" : seqno,
                    "func_dvs"   : "update"
                };

                var callback = function(result) {
                    $("#crm_memo_modal").reveal();
                    $("#memo_date").val(result.memo_date);
                    $("#memo_cont").val(result.memo_cont);
                    $("#memo_upd_seqno").val(seqno);
                };

                ajaxCall(url, "json", data, callback);
             
            } else {
                alert('한번에 1개의 메모만 수정 가능합니다.');
                return false;
            }
        }
        this.buttonDvs = funcDvs;
    }
};

/**
 * @brief CRM 정보 메모 모달 팝업 닫기
 *
 */
var closeCrmMemoModal = function() {
    $("#crm_memo_modal").trigger('reveal:close');
    $("#memo_date").val("");
    $("#memo_cont").val("");
};

/**
 * @brief CRM 영업정보 메모 리스트 불러오기
 *
 */
var loadCrmMemoList = {
    "data" : null,
    "exec" : function () {

        var crmTab = $("input[name='houses-state']:checked").val();
        var dvs = "";
        if (crmTab === "crm_business") {
            dvs = "business";
        } else if (crmTab === "crm_collect") {
            dvs = "collect";
        }

        var url = "/json/business/order_mng/load_crm_memo.php";

        var data = {
            "dvs"                    : dvs,
            "crm_biz_info_seqno"     : loadCrmInfoBusiness.seqno,
            "crm_collect_info_seqno" : loadCrmInfoCollect.seqno
        };
        var callback = function(result) {
            $("#crm_memo_thead").html(result.thead);
            $("#crm_memo_tbody").html(result.tbody);
            pagingCommon("crm_memo_list_page",
                         "changePageCrmMemoList",
                         5,
                         result.result_cnt,
                         5,
                         "init");
        };
        
        ajaxCall(url, "json", data, callback);
        this.data = data;
    }
}

/**
 * @brief crm정보 메모 페이지 변경시 호출
 *
 * @param page = 선택한 페이지
 */
var changePageCrmMemoList = function(page) {
    if ($("#crm_memo_list_page_" + page).hasClass("page_accent")) {
        return false;
    }
    if (isNaN(page)) {
        return false;
    }

    $(".crm_memo_list_page").removeClass("page_accent");
    $("#crm_memo_list_page_" + page).addClass("page_accent");

    var url = "/json/business/order_mng/load_crm_memo.php";
    var data = loadCrmMemoList.data;
    data.page = page;
    data.page_dvs = '1';
    var callback = function(result) {
        $("#crm_memo_thead").html(result.thead);
        $("#crm_memo_tbody").html(result.tbody);

        hideLoadingMask();
    };
    
    showLoadingMask();
    ajaxCall(url, "json", data, callback);

};

/**
 * @brief CRM 정보 메모 추가하기 
 *
 */
var insertOrUpdateCrmMemo = function () {

    var crmTab = $("input[name='houses-state']:checked").val();
    var funcDvs = showCrmMemoModal.buttonDvs;
    var crmDvs = "";
    if (crmTab === "crm_business") {
       crmDvs = "business"; 
    } else if (crmTab === "crm_collect") {
       crmDvs = "collect"; 
    }

    var url = "/proc/business/order_mng/insert_update_crm_memo.php";

    var data = {
        "dvs"                    : crmDvs,
        "funcDvs"                : funcDvs,
        "crm_biz_info_seqno"     : loadCrmInfoBusiness.seqno,
        "crm_collect_info_seqno" : loadCrmInfoCollect.seqno,
        "memo_date"              : $("#memo_date").val(),
        "memo_cont"              : $("#memo_cont").val(),
        "memo_seqno"             : $("#memo_upd_seqno").val()
    };

    var callback = function(result) {
        alert('저장되었습니다.');
        closeCrmMemoModal();
        loadCrmMemoList.exec();
    };
    
    ajaxCall(url, "text", data, callback);
};

/**
 * @brief CRM 메모 삭제
 *
 */
var delCrmMemoModal = function() {
    var chkNum =''; 
    $("input[name=crm_memo_chk]:checked").each(function() {
        chkNum += $(this).attr("id") + '!'; 
    });

    var chkLen = $("input[name=crm_memo_chk]:checked").length;
    var crmTab = $("input[name='houses-state']:checked").val();

    if (chkLen === 0) {
        alert('최소 하나의 메모는 선택해야 합니다.');
        return false;
    }

    var conf = confirm("선택한 메모를 삭제하시겠습니까?");
    
    if (conf) {
        chkNum = chkNum.split("!"); 

        var url = "/proc/business/order_mng/delete_crm_memo.php";
        var data = {
            "memo_chk" : chkNum,
            "memo_ea"  : chkLen,
            "dvs"      : crmTab
        };
        var callback = function(result) {
            alert("삭제되었습니다.");
            loadCrmMemoList.exec();
        };

        ajaxCall(url, "text", data, callback);
    }

};

/**
 * @brief CRM 정보 검색 분기
 *
 */
var loadCrmInfoList = function() {
    var crmTab = $("input[name='houses-state']:checked").val();

    if (crmTab === "crm_business") {
        loadCrmInfoBusinessList.exec('business');
    } else if (crmTab === "crm_collect") {
        loadCrmInfoCollectList.exec('collect');
    } else {
        return false;
    }
}

/**
 * @brief CRM 영업 정보 리스트 출력
 *
 */
var loadCrmInfoBusinessList = {
    "data" : null,
    "exec" : function(searchDvs) {
        var seqno = '';
        var crm_info_empl = '';
        var member_name = '';
        var from = '';
        var to = '';
        var crm_info_depar = '';
        var crm_dvs = "business";
        if (searchDvs === "common") {
            seqno = loadMemberStatsInfo.seqno;
            if (checkBlank(seqno)) {
                return false;
            }
            from       = $("#basic_from").val();
            to         = $("#basic_to").val();

        } else if (searchDvs === "business") {
            from           = $("#crm_info_date").val();
            to             = $("#crm_info_date").val();
            crm_info_depar = $("#crm_info_depar").val();
            crm_info_empl  = $("#crm_info_empl option:selected").text();
            member_name    = $("#crm_info_search_txt").val();
        }

        var url = "/json/business/order_mng/load_crm_info_list.php";
        var data = {
         // "member_seqno"   : seqno,
            "from"           : from,
            "to"             : to,
            "crm_info_depar" : crm_info_depar,
            "crm_info_empl"  : crm_info_empl,
            "member_name"    : member_name,
            "crm_dvs"        : crm_dvs
        };

        var callback = function(result) {
            $("#crm_info_sum").html(result.thead);
            $("#crm_info_list").html(result.tbody);
            $("#crm_info_search_cs_type").css('background-color', '#ebeae5');
            $("#crm_info_search_cs_type").prop("disabled", true);
            initCrmInfoBusiness();

            pagingCommon("crm_info_page",
                         "changePageCrmInfo",
                         5,
                         result.result_cnt,
                         5,
                         "init");
            hideLoadingMask();

        };
        
        showLoadingMask();
        this.data = data;

        ajaxCall(url, "json", data, callback);
    }

};

/**
 * @brief 회원 CRM 정보 영업탭 정보 가져옴
 *
 * @param searchDvs = 검색위치 구분값
 */
var loadCrmInfoBusiness = {
    "seqno"        : null,
    "member_seqno" : null,
    "exec"         : function(seqno) {
        $(".crm_info_business_tr").removeClass("active_tr");
        $("#crm_info_business_tr_" + seqno).addClass("active_tr");

        saveSelectedBarArea.exec('');

        var url = "/json/business/order_mng/load_crm_info_business.php";
        var data = {
            "crm_biz_info_seqno" : seqno
        };
        var member_seqno = "";
        var callback = function(result) {
            var cont = result.cs_cont;
            cont = cont.split("!");

            member_seqno = result.member_seqno;
            loadCrmInfoBusiness.member_seqno = member_seqno;

            $("#crm_info_cs_date").val(result.cs_date);
            $("#crm_info_cs_indu").val(result.cs_indu);
            $("#crm_info_cs_promi_date").val(result.cs_promi_date);
            $("#crm_info_cs_type").val(result.cs_type);
            $("#crm_info_interest_field").val(result.interest_field);
            $("#crm_info_interest_prdt").val(result.interest_prdt);
            $("#crm_info_expec_sales").val(result.expec_sales);
            $("#crm_info_interest_item").val(result.interest_item);
            $("#crm_info_business_empl_name").val(result.empl_name);
            $("#crm_info_plural_deal_yn").val(result.plural_deal_yn);
            $("input:checkbox[name='crm_info_sales_dvs']").each(function() {
                if ($(this).val() != cont[i]) {
                    $(this).prop("checked", false); 
                }

                for (var i = 0; i < cont.length; i++) {
                    if ($(this).val() == cont[i]) { 
                        $(this).prop("checked", true);
                    }
                }
            });
            $("#crm_info_business_cont").val(result.cs_memo);

            if (result.plural_deal_yn === 'Y') {
                $("#crm_info_plural_deal_yn").val("Y").prop("selected", true);
            } else {
                $("#crm_info_plural_deal_yn").val("N").prop("selected", true);
            }
            toggleMultiEtprs(result.plural_deal_yn);
            loadCrmInfoEtprsName();
            loadCrmMemoList.exec();
            showTotal('w'); // 기본적으로 주별 집계
        };

        this.seqno = seqno;
        ajaxCall(url, "json", data, callback);
    }
};

/**
 * @brief 회원 CRM 정보 영업탭 직원기념일 정보 
 *
 * @param searchDvs = 검색위치 구분값
 * @comment 현재 사용하지 않음
 */
/*
var loadCrmInfoEmplAnniv = function() {
    var seqno = loadMemberStatsInfo.seqno;
    if (checkBlank(seqno)) {
        return false;
    }

    var url = "/json/business/order_mng/load_crm_info_empl_anniv.php";
    var data = {
        "member_seqno" : seqno
    };
    var callback = function(result) {
        var inputClass = "anni_input";
    $('#anni').html('');        
        for(var i = 0; i < result.length; i++) {
            var data = result[i];

            var trId = "input_tr_anni_" + data.seqno;

            var html = "<tr id=\"" + trId + "\">";
            html +=     "<th></th>";
            html +=     "<td>";
            html +=         "<input type=\"text\" class=\"btnCRM "+ inputClass +"_name\" style=\"width:117px; margin-bottom:7px; margin-right:5px;\" value=\"" + data.cont + "\">";
            html +=     "</td>";
            html +=     "<td>";
            html +=         "<input type=\"text\" class=\"btnCRM "+ inputClass + "\" style=\"width:117px; margin-bottom:7px; margin-right:5px;\" value=\"" + data.empl_anniv + "\">";
            html +=     "</td>";
            html +=     "<td>";
            html +=         "<button class=\"btn_crm_info_del\" style=\"width:21px;height:21px;margin-top:0px; margin-bottom:7px;\" onclick=\"inputSubtract('anni', '" + data.seqno + "', '');\">-</button>";
            html +=     "</td>";
            html += "</tr>";
    
            $('#anni').append(html);
        // 데이트피커 
        $('#' + trId + " .btnCRM.anni_input").datepicker({
            format         : "yyyy-mm-dd",
            autoclose      : true,
            todayBtn       : "linked",
            todayHighlight : true,
            language       : "kr"
        }).datepicker();
        }
    };
    
    ajaxCall(url, "json", data, callback);
};
*/

/**
 * @brief 회원 CRM 정보 영업탭 복수거래기업 정보 
 *
 * @param searchDvs = 검색위치 구분값
 */
var loadCrmInfoEtprsName = function() {
    var seqno = loadCrmInfoBusiness.seqno;
    if (checkBlank(seqno)) {
        return false;
    }

    var url = "/json/business/order_mng/load_crm_info_etprs_name.php";
    var data = {
        "crm_biz_info_seqno" : seqno
    };
    var callback = function(result) {
        var inputClass = "etprs_input";
    $('#etprs').html('');
        for(var i = 0; i < result.length; i++) {
            var data = result[i];

            var trId = "input_tr_etprs_" + data.seqno;

            var html = "<tr id=\"" + trId + "\">";
            html +=     "<td>";
            html +=         "<input type=\"text\" class=\"btnCRM "+ inputClass + "\" style=\"width:117px; margin-bottom:7px; margin-right:5px;\" value=\"" + data.etprs_name + "\">";
            html +=     "</td>";
            html +=     "<td>";
            html +=         "<button class=\"btn_crm_info_del\" style=\"width:50px;height:21px;margin-top:0px; margin-bottom:7px;\" onclick=\"inputSubtract('etprs', '" + data.seqno + "', '');\">제거 -</button>";
            html +=     "</td>";
            html += "</tr>";
        
            $('#etprs').append(html);
        }

    };
    
    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 회원 CRM 정보 영업탭 정보 수정
 *
 */
var modiCrmInfoBusiness = function() {
    // 체크박스 
    var crmBizChk = '';
    $("input[name=crm_info_sales_dvs]:checked").each(function() {
        crmBizChk += $(this).val(); 
        crmBizChk += "!";
    });

    var seqno = loadCrmInfoBusiness.seqno;
    if (checkBlank(seqno)) {
        alert("수정할 CRM을 선택 후 수정해주세요.");
        return false;
    }
    var url = "/proc/business/order_mng/update_crm_info_business.php";
    var data = {
        "crm_biz_info_seqno" : loadCrmInfoBusiness.seqno,
        "cs_date"            : $("#crm_info_cs_date").val(),
        "cs_indu"            : $("#crm_info_cs_indu").val(),
        "cs_promi_date"      : $("#crm_info_cs_promi_date").val(),
        "cs_type"            : $("#crm_info_cs_type").val(),
        "interest_field"     : $("#crm_info_interest_field").val(),
        "interest_prdt"      : $("#crm_info_interest_prdt").val(),
        "expec_sales"        : $("#crm_info_expec_sales").val(),
        "interest_item"      : $("#crm_info_interest_item").val(),
        "plural_deal_yn"     : $("#crm_info_plural_deal_yn").val(),
        "empl_name"          : $("#crm_info_business_empl_name").val(),
        "cs_cont"            : crmBizChk,
        "cs_memo"            : $("#crm_info_business_cont").val()
    };

    if (checkBlank(data.cs_indu)) {
        alert("상담목적 유형을 입력해 주십시오.");
        return false;
        
    } else if (checkBlank(data.cs_type)) {
        alert("영업형식을 입력해 주십시오.");
        return false;

    } else if (checkBlank(data.interest_field)) {
        alert("관심분야를 입력해 주십시오.");
        return false;

    } else if (checkBlank(data.interest_prdt)) {
        alert("관심상품을 입력해 주십시오.");
        return false;

    } else if (checkBlank(data.expec_sales)) {
        alert("예상매출을 입력해 주십시오.");
        return false;

    } else if (checkBlank(data.interest_item)) {
        alert("관심아이템을 입력해 주십시오.");
        return false;

    } else if (checkBlank(data.cs_cont)) {
        alert("영업상담내용을 선택해 주십시오.");
        return false;

    } else if (checkBlank(data.cs_memo)) {
        alert("영업상담메모를 입력해 주십시오.");
        return false;

    }
 
    /*
    var checkModi = confirm("수정 하시겠습니까?");
    if (!checkModi) {
        return false;
    }
    */
    
    var callback = function(result) {
        modiCrmInfoEtprsName(loadCrmInfoBusiness.seqno);
        alert("수정되었습니다.");
        initCrmInfoBusiness();
        loadCrmInfoBusinessList.exec('common');
    };

    ajaxCall(url, "text", data, callback);
};

/**
 * @brief 회원 CRM정보 영업탭 정보 등록
 *
 */
var insertCrmInfoBusiness = function() {
    var seqno = loadMemberStatsInfo.seqno;
    if (checkBlank(seqno)) {
        return false;
    }
    var name = loadMemberStatsInfo.name;
    if (checkBlank(name)) {
        return false;
    }

    var crmBizChk = '';
    $("input[name=crm_info_sales_dvs]:checked").each(function() {
        crmBizChk += $(this).val(); 
        crmBizChk += "!";
    });

    var url = "/proc/business/order_mng/insert_crm_info_business.php";
    var data = {
        "member_seqno"      : seqno,
        "member_name"       : name,
        "cs_date"           : $("#crm_info_cs_date").val(),
        "cs_indu"           : $("#crm_info_cs_indu").val(),
        "cs_promi_date"     : $("#crm_info_cs_promi_date").val(),
        "cs_type"           : $("#crm_info_cs_type").val(),
        "interest_field"    : $("#crm_info_interest_field").val(),
        "interest_prdt"     : $("#crm_info_interest_prdt").val(),
        "expec_sales"       : $("#crm_info_expec_sales").val(),
        "interest_item"     : $("#crm_info_interest_item").val(),
        "plural_deal_yn"    : $("#crm_info_plural_deal_yn").val(),
        "cs_cont"           : crmBizChk,
        "cs_memo"           : $("#crm_info_business_cont").val()
    };
    
    if (checkBlank(data.cs_indu)) {
        alert("상담목적 유형을 입력해 주십시오.");
        return false;
        
    } else if (checkBlank(data.cs_type)) {
        alert("영업형식을 입력해 주십시오.");
        return false;

    } else if (checkBlank(data.interest_field)) {
        alert("관심분야를 입력해 주십시오.");
        return false;

    } else if (checkBlank(data.interest_prdt)) {
        alert("관심상품을 입력해 주십시오.");
        return false;

    } else if (checkBlank(data.expec_sales)) {
        alert("예상매출을 입력해 주십시오.");
        return false;

    } else if (checkBlank(data.interest_item)) {
        alert("관심아이템을 입력해 주십시오.");
        return false;

    } else if (checkBlank(data.cs_cont)) {
        alert("영업상담내용을 선택해 주십시오.");
        return false;

    } else if (checkBlank(data.cs_memo)) {
        alert("영업상담메모를 입력해 주십시오.");
        return false;

    }  

    var callback = function(result) {
        if (checkBlank(result)) {
            return alertReturnFalse(result); 
        }

        alert("등록되었습니다.");
        modiCrmInfoEtprsName(result.crm_biz_info_seqno);
        initCrmInfoBusiness(); 
        loadCrmInfoBusinessList.exec();
    };

    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 매출액,입금액 조정 초기화
 */
var initCrmInfoBusiness = function() {
    var subTr = $("#etprs tr").length;
    
    $("#crm_info_cs_date").val("");
    $("#crm_info_cs_indu").val("");
    $("#crm_info_cs_promi_date").val("");
    $("#crm_info_cs_type").val("");
    $("#crm_info_interest_field").val("");
    $("#crm_info_interest_prdt").val("");
    $("#crm_info_expec_sales").val("");
    $("#crm_info_interest_item").val("");
    $("#crm_info_plural_deal_yn").val("N");
    $("#crm_info_business_cont").val("");
    $('input:checkbox[name="crm_info_sales_dvs"]').each(function() {
        this.checked = false; 
    });
    loadCrmInfoBusiness.seqno = null;
    toggleMultiEtprs('N');
    //복수거래기업 입력전으로 초기화 
    $("#etprs").html("");
};

/**
 * @brief 회원 CRM 정보 영업탭 정보 수정
 * @comment 7월 13일부로 로직변경으로 인해 주석처리함
 */
/*
var modiCrmInfoBusinessInfo = {
    "ajaxStack" : 0,
    "exec" : function(seqno) {
        var url = "/proc/business/order_mng/update_crm_info_business.php";
        var data = {
            "member_seqno"              : seqno,
            /*"crm_info_client_class"   : $("#crm_info_client_class > option:selected").text(),
            "crm_info_io_class"         : $("#crm_info_io_class > option:selected").text(),
            "crm_info_request_tel"      : $("#crm_info_request_tel").val(),
            "crm_info_send_tel"         : $("#crm_info_send_tel").val(),*//*
            "cs_indu"                   : $("#crm_info_cs_indu").val(),
            "interest_prdt"             : $("#crm_info_interest_prdt > option:selected").val(),
            "expec_sales"               : $("#crm_info_expec_sales").val(),
            "interest_field"            : $("#crm_info_interest_field").val(),
            "interest_item"             : $("#crm_info_interest_item").val(),
            "found_year_mon"            : $("#crm_info_found_year_mon").val(),
            "interest_event"            : $("#crm_info_interest_event").val(),
            "handle_date"               : $("#crm_info_handle_date").val(),
            "main_prdt"                 : $("#crm_info_main_prdt").val(),
            "empl_amt"                  : $("#crm_info_empl_amt").val(),
            "etprs_loca"                : $("#crm_info_etprs_loca").val(),
            "etprs_size"                : $("#crm_info_etprs_size").val(),
            "repre_anniv"               : $("#crm_info_repre_anniv").val(),
            "plural_deal_yn"            : $("input[type='radio'][name='crm_info_plural_deal_yn']:checked").val(),
            "etc_cont"                  : $("#crm_info_etc_cont").val()
        };
        
        var callback = function(result) {
            modiCrmInfoEmplAnniv(seqno);
            modiCrmInfoEtprsName(seqno);

            modiCrmInfoBusinessInfo.ajaxStack--;
            hideMaskByAjaxStack(modiCrmInfoBusinessInfo.ajaxStack, loadCrmInfoBusiness);
        };
    
        showMask();
        modiCrmInfoBusinessInfo.ajaxStack++;
        ajaxCall(url, "text", data, callback);
    }
};
*/

/**
 * @brief CRM영업탭 복수거래기업 등록수정 
 */
var modiCrmInfoEtprsName = function(bizSeqno) {
    var inputArr = [];
    $("#etprs .etprs_input").each(function() {
        if (checkBlank($(this).val())) {
            return true;
        }

        inputArr.push($(this).val());         
    });

    var url = "/proc/business/order_mng/update_crm_info_etprs_name.php";
    var data = {
        "crm_biz_info_seqno"      : bizSeqno,
        "etprs_name[]"            : inputArr
    };
    
    var callback = function(result) {
        //modiCrmInfoBusinessInfo.ajaxStack--;
        //hideMaskByAjaxStack(modiCrmInfoBusinessInfo.ajaxStack, loadCrmInfoBusiness);
    };

    //showMask();
    //modiCrmInfoBusinessInfo.ajaxStack++;
    ajaxCall(url, "text", data, callback);

};
 
/**
 * @brief CRM 수금 정보 리스트 출력
 *
 */
var loadCrmInfoCollectList = {
    "data" : null,
    "exec" : function(searchDvs) {
        var seqno = '';
        var crm_info_empl = '';
        var member_name = '';
        var from = '';
        var to = '';
        var cs_type = '';
        var crm_info_depar = '';
        var crm_dvs = "collect";
        if (searchDvs === "common") {
            seqno = loadMemberStatsInfo.seqno;
            if (checkBlank(seqno)) {
                return false;
            }
            from       = $("#basic_from").val();
            to         = $("#basic_to").val();

        } else if (searchDvs === "collect") {
            from          = $("#crm_info_date").val();
            to            = $("#crm_info_date").val();
            crm_info_depar = $("#crm_info_depar").val();
            crm_info_empl  = $("#crm_info_empl option:selected").text();
            member_name   = $("#crm_info_search_txt").val();
            cs_type       = $("#crm_info_cs_type").val();
        }

        var url = "/json/business/order_mng/load_crm_info_list.php";
        var data = {
        //  "member_seqno"   : seqno,
            "from"           : from,
            "to"             : to,
            "crm_info_depar" : crm_info_depar,
            "crm_info_empl"  : crm_info_empl,
            "member_name"    : member_name,
            "crm_dvs"        : crm_dvs,
            "cs_type"        : cs_type
        };

        var callback = function(result) {
            $("#crm_info_sum").html(result.thead);
            $("#crm_info_list").html(result.tbody);
            //initCrmInfoCollect();

            pagingCommon("crm_info_page",
                         "changePageCrmInfo",
                         5,
                         result.result_cnt,
                         5,
                         "init");

            hideLoadingMask();
        };
        
        showLoadingMask();
        this.data = data;

        ajaxCall(url, "json", data, callback);
    }

};

/**
 * @brief 회원 CRM 정보 처음으로 가져옴
 * @comment 처음에는 가져오는 테이블이 다르므로 분리시켜둠
 */
var loadCrmInfoCollectFirst = {
    "loan_data" : null,
    "exec"      : function() {
        var seqno = loadMemberStatsInfo.seqno;
        if (checkBlank(seqno)) {
            return false;
        }

        var url = "/json/business/order_mng/load_crm_info_collect_loan.php";

        var data = {
            "member_seqno" : seqno 
        };

        var limit = "";
        var totalOa = "";
        var callback = function(result) {
            console.log(result);
            loadCrmInfoCollect.seqno = "";
            $("#crm_info_collect_wm").val('100');
            $("#crm_info_collect_wm").trigger("change");
            $("#crm_info_collect_loan_promi_date").val("");
            $("#crm_info_collect_loan_promi_price").val("");
            $("#crm_info_collect_memo").val("");
            $("#crm_info_bank_name").val(result.bank_name);          // 가상계좌 은행명
            $("#crm_info_ba_num").val(result.ba_num);                // 가상계좌 번호
            limit = parseInt(result.loan_limit_price); 
            if (isNaN(limit)) {
                limit = 0;
            }
            $("#crm_info_loan_limit").val(limit);  // 여신한도
            var carryForward = parseInt(result.loan_limit_use[0].carryforward_oa);
            var periodEnd    = parseInt(result.loan_limit_use[0].period_end_oa);
            totalOa      = carryForward + periodEnd;
            if (isNaN(totalOa)) {
                totalOa = 0;
            }
            $("#crm_info_loan_lack").val(totalOa);  // 한도소진금액
            var ratio = parseInt((totalOa / limit) * 100);
            if (isNaN(ratio)) {
                ratio = 0;
            }
            if (ratio < 80) {
                $("#crm_info_loan_alert").text("안전");
                $("#crm_info_loan_alert").css('color', 'green');
                $("#crm_info_loan_alert").css('font-weight', 'Bold');
            } else {
                $("#crm_info_loan_alert").text("초과 경고");
                $("#crm_info_loan_alert").css('color', 'red');
                $("#crm_info_loan_alert").css('font-weight', 'Bold');
            }
            ratio += '%';
            $("#crm_info_loan_ratio").val(ratio);
            
            loadCrmInfoCollectFirst.loan_data  = limit;
            loadCrmInfoCollectFirst.loan_data += "!";
            loadCrmInfoCollectFirst.loan_data += totalOa;
        };

        ajaxCall(url, "json", data, callback);
    }

};

/**
 * @brief 회원 CRM 정보 수금탭 정보 가져옴
 *
 * @param searchDvs = 검색위치 구분값
 */
var loadCrmInfoCollect = {
    "seqno" : null,
    "exec"  : function(seqno, memberSeq) {
        $(".crm_info_collect_tr").removeClass("active_tr");
        $("#crm_info_collect_tr_" + seqno).addClass("active_tr");

        saveSelectedBarArea.exec('');

        var url = "/json/business/order_mng/load_crm_info_collect.php";
        var data = {
            "member_seqno"           : memberSeq,
            "crm_collect_info_seqno" : seqno
        };
        var callback = function(result) {
            var wmDvs = result.collect[0].loan_pay_promi_dvs;
            wmDvs = wmDvs.split("!");
            //날짜 자르는 함수(yyyy-mm-dd)
            var shortenDate = function(adate) {
                var sDate = adate.substr(0,10);    
                return sDate;
            }
            var promiDate = result.collect[0].loan_pay_promi_date; // 결제 약속일
            $("#crm_info_bank_name").val(result.bank_name); // 가상계좌 은행명
            $("#crm_info_ba_num").val(result.ba_num);       // 가상계좌 번호
            var limit = parseInt(result.collect[0].loan_limit_price);
            if (isNaN(limit)) {
                limit = 0;
            }
            var lack  = parseInt(result.collect[0].loan_limit_use);
            if (isNaN(lack)) {
                lack = 0;
            }
            var handleDate = result.collect[0].handle_date; // 처리 일시

            $("#crm_info_loan_limit").val(limit);
            $("#crm_info_loan_lack").val(lack);
            $("#crm_info_collect_memo").val(result.collect[0].memo);
            $("#crm_info_collect_memo").val(result.collect[0].memo);
            $("#crm_info_collect_cs_date").val(result.collect[0].cs_date);
            $("#crm_info_collect_wm").val(wmDvs[0]);
            $("#crm_info_collect_wm").trigger("change");
            $("#crm_info_collect_day").val(wmDvs[1]);
            $("#crm_info_collect_loan_promi_date").val(shortenDate(promiDate));
            $("#crm_info_collect_loan_promi_price").val(result.collect[0].loan_pay_promi_price);
            $("#crm_info_collect_handle_date").val(shortenDate(handleDate));
            $('input[name="crm_info_collect_handle_dvs"][value="'+ result.collect[0].handle_dvs+'"]').prop('checked', true);
            var ratio  = parseInt((lack / limit) * 100);
            if (isNaN(ratio)) {
                ratio = 0;
            }
            if (ratio < 80) {
                $("#crm_info_loan_alert").text("안전");
                $("#crm_info_loan_alert").css('color', 'green');
                $("#crm_info_loan_alert").css('font-weight', 'Bold');
            } else {
                $("#crm_info_loan_alert").text("초과 경고");
                $("#crm_info_loan_alert").css('color', 'red');
                $("#crm_info_loan_alert").css('font-weight', 'Bold');
            }
            ratio += '%';
            $("#crm_info_loan_ratio").val(ratio);
            loadCrmMemoList.exec();
        };
        this.seqno = seqno;
        ajaxCall(url, "json", data, callback);
    }
};

/**
 * @brief 단수/복수거래 여부에 따라 중복 거래업체 show/hide
 *
 * @param id = input box가 추가될 빈tr의 id
 * @writer montvert
 */
var toggleMultiEtprs = function(val) {
    if (val === 'Y') {
        $("#crm_info_multi_etprs").show();
        $("#etprs").show();
    } else {
        $("#crm_info_multi_etprs").hide();
        $("#etprs").hide();
    }
};

/**
 * @brief +버튼을 이용한 input 박스 추가
 *
 * @param id = input box가 추가될 빈tr의 id
 * @param dvs = input box의 구분자
 * @writer montvert
 */
var inputAppend = {
    "idx" : 1000000,
    "exec" : function(id, dvs) {
        var inputClass = id + "_input";
    var trId = "input_tr_" + id + '_' + this.idx + dvs;

        var html = "<tr id=\"" + trId + "\">";
        if (id == "anni") {
            html += "<td>";
            html += "<input type=\"text\" class=\"btnCRM "+ inputClass +"_name \" style=\"width:117px; margin-bottom:7px; margin-right:5px;\">";
            html += "</td>";
        } else {
        }
        html +=     "<td>";
        html +=         "<input type=\"text\" class=\"btnCRM "+ inputClass + "\" style=\"width:117px; margin-bottom:7px; margin-right:5px;\">";
        html +=     "</td>";
        html +=     "<td>";
        html +=         "<button class=\"btn_crm_info_del\" style=\"width:50px;height:21px;margin-top:0px; margin-bottom:7px;\" onclick=\"inputSubtract('" + id + "', " + (this.idx++) + ", '" + dvs + "');\">제거 -</button>";
        html +=     "</td>";
        html += "</tr>";
        
        $('#' + id).append(html);

        /*
        $('#' + trId + " .btnCRM.anni_input").datepicker({
            format         : "yyyy-mm-dd",
            autoclose      : true,
            todayBtn       : "linked",
            todayHighlight : true,
            language       : "kr"
        }).datepicker("setDate", '0');
        */
    }
};

/**
 * @brief -버튼을 이용한 input 박스 삭제
 *
 * @param id = input box가 추가될 빈tr의 id
 * @param idx = input box가 추가될 tr의 인덱스
 * @param dvs = input box가 추가될 tr의 구분자
 * @writer montvert
 */
var inputSubtract = function(id, idx, dvs) {
    var id = "input_tr_" + id + '_' + idx + dvs;
    $('#' + id).remove();
};

/**
 * @brief 선택한 회원의 품목별 현황정보 검색
 *
 * @param searchDvs = 검색위치 구분값
 */
var loadPrdtInfo = function(searchDvs) {
    var seqno = loadMemberStatsInfo.seqno;
    if (checkBlank(seqno)) {
        return false;
    }

    var from = $("#basic_from").val();
    var to   = $("#basic_to").val();

    if (searchDvs === "prdt_info") {
        from = $("#prdt_info_from").val();
        to   = $("#prdt_info_to").val();
    }

    var url = "/json/business/order_mng/load_prdt_info.php";
    var data = {
        "member_seqno" : seqno,
        "member_name" : loadMemberStatsInfo.name,
        "member_nick" : loadMemberStatsInfo.nick,
        "from" : from,
        "to"   : to
    };
    var callback = function(result) {
        $("#prdt_info_list").html(result.list);
        $("#prdt_info_detail_list").html(result.detail);
        $("#prdt_info_cur_date").html(result.date[0]);
        $("#prdt_info_last_year_date").html(result.date[1]);
        $("#prdt_info_m1_date").html(result.date[2]);
        $("#prdt_info_m2_date").html(result.date[3]);
        $("#prdt_info_m3_date").html(result.date[4]);

        // 차트
        var chartOption = {
            chart  : {
                "type"        : "column",
                "borderColor" : '#DFDFDF',
                "borderWidth" : 1
            },
            yAxis  : {title : {text : "단위(원)"}},
            legend : {enabled : false},
            tooltip: {
                headerFormat : '',
                pointFormat  : "<b>{point.y}</b>원<br/>"
            }
        };

        var chartDataArr = result.chart;
        var arrLength = chartDataArr.length;
        var $chartAreaObj = $("#prdt_info_chart_area");
        $chartAreaObj.html('');
        for (var i = 0; i < arrLength; i++) {
            var data = chartDataArr[i];
            var divId = "prdt_info_chart_" + i;
            var divHtml = "<div id=\"" + divId + "\" style=\"min-width:400px;display:inline-block\"></div>";
            $chartAreaObj.append(divHtml);

            chartOption.title  = data.title;
            chartOption.xAxis  = {"categories" : data.categories};
            chartOption.series = [{"data" : data.data}];
            new Highcharts.chart(divId, chartOption);
        }

        hideLoadingMask();
    };

    showLoadingMask();
    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 문자 발송 함수 
 *
 * @param dvs = modal 구분자(영업, 수금)
 */
var sendMmsMessage = function(dvs) {
    var checkSendMms = confirm("문자 메시지를 전송 하시겠습니까?"); 
    if (checkSendMms) {
        var prefix  = dvs.substr(0,3); 
        var cellNum = "";
        var crmTab  = "";
        var seqno   = "";
        if (prefix == "crm") {
        crmTab = $("input[name='houses-state']:checked").val();
            //crm 영업일 때
            if (crmTab == "crm_business") { 
                cellNum = $("#crm_info_business_mms_cell_num").text();
                seqno   = loadCrmInfoBusiness.seqno;

            //crm 수금일 때
            } else if (crmTab == "crm_collect") {
                cellNum = $("#crm_info_collect_mms_cell_num").text();
                seqno   = loadCrmInfoCollect.seqno;
            }
        } else {
            cellNum = $('#' + dvs + '_cell_num').text();
        }

        /*if ($('#' + dvs + '_cell_num').length > 0) {
            cellNum = $('#' + dvs + '_cell_num').text();
        }*/

        if (cellNum == "" || cellNum == null) {
            alert("발송할 전화번호가 없습니다.");
            return false;
        }

        var url = "/proc/business/order_mng/insert_crm_info_mms.php";
        var data = {
            "subject"  : $("#" + dvs + "_title").val(),
            "cell_num" : cellNum,
            "msg"      : $("#" + dvs + "_msg").val(),
            "dvs"      : crmTab,
            "seqno"    : seqno
        };

        var callback = function(result) {
            if (!checkBlank(result)) {
                return alertReturnFalse(result);
            }

            alert('문자가 발송되었습니다.');
            hideModal(dvs);
        };

        ajaxCall(url, "text", data, callback);
    } else {
        return false;
    }
};

// CRM 수금탭에서 매월, 매주 선택에 따라 분기 
var toggleWeekMonth = function(tog, dvs) {
    var junc = $("#" + tog + "").val();

    //요일별
    if (junc == '100') {
        loadCrmInfoCollectWeekday(dvs);
    //날짜별
    } else if (junc == '101') {
        loadCrmInfoCollectDay(dvs);
    } 
    
};

/**
 * @brief 요일 불러오는 셀렉트박스
 * @param dvs = 적용하려는 selectbox 아이디
 *
 */
var loadCrmInfoCollectWeekday = function(dvs) {
    var weekday = "";
    weekday += "<option value=\"100\">월요일</option>";
    weekday += "<option value=\"101\">화요일</option>";
    weekday += "<option value=\"102\">수요일</option>";
    weekday += "<option value=\"103\">목요일</option>";
    weekday += "<option value=\"104\">금요일</option>";
    $("#" + dvs + "").html(weekday);
};

/**
 * @brief 날짜 불러오는 셀렉트박스
 * @param dvs = 적용하려는 selectbox 아이디
 *
 */
var loadCrmInfoCollectDay = function(dvs) {
    var startDay  = 1; 
    var endDay    = 31;
    var periodDay = "";

    for (var iter = startDay; iter <= endDay; iter++) {
        periodDay +="<option value="+iter+">"+iter+"일</option>";
    }
    $("#" + dvs + "").html(periodDay);
};

/**
 * @brief Crm정보 수금 정보 입력
 * @param data = 넘겨줄 데이터 값
 *
 */
var insertCrmInfoCollect = function() {
    var seqno = loadMemberStatsInfo.seqno;
    if (checkBlank(seqno)) {
        return false;
    }
    var name = loadMemberStatsInfo.name;
    if (checkBlank(name)) {
        return false;
    }

    var url = "/proc/business/order_mng/insert_crm_info.php";
    var promiDvs = "";
    promiDvs += $("#crm_info_collect_wm").val();
    promiDvs += "!";
    promiDvs += $("#crm_info_collect_day").val();
    
    var loanData = loadCrmInfoCollectFirst.loan_data;
    loanData = loanData.split("!");
    var limit = loanData[0]; 
    var lack  = loanData[1]; 
    var handleDvs = $("input[name='crm_info_collect_handle_dvs']:checked").val();

    var data = {
        "member_seqno"         : seqno,
        "member_name"          : name,
        "memo"                 : $("#crm_info_collect_memo").val(),
        "loan_limit"           : limit,
        "loan_lack"            : lack,
        "loan_pay_promi_dvs"   : promiDvs,
        "loan_pay_promi_date"  : $("#crm_info_collect_loan_promi_date").val(),
        "loan_pay_promi_price" : $("#crm_info_collect_loan_promi_price").val(),
        "handle_dvs"           : handleDvs,
        "handle_date"          : $("#crm_info_collect_handle_date").val()
    };

    var callback = function(result) {
        if (!checkBlank(result)) {
            return alertReturnFalse(result); 
        }

        alert('등록되었습니다.');
        loadCrmInfoCollectFirst.exec();
        loadCrmInfoCollectList.exec();
    };

    ajaxCall(url, "text", data, callback);
};

/**
 * @brief 회원 CRM 정보 수금탭 정보 수정
 *
 */
var modiCrmInfoCollect = function() {

    var seqno = loadCrmInfoCollect.seqno;
    if (checkBlank(seqno)) {
        alert("선택된 항목이 없습니다. 리스트에서 선택하신 후 수정해 주세요.");
        return false;
    }
    
    var url = "/proc/business/order_mng/update_crm_info_collect.php";
    var promiDvs  = "";
        promiDvs += $("#crm_info_collect_wm").val();
        promiDvs += "!";
        promiDvs += $("#crm_info_collect_day").val();

    var data = {
        "crm_collect_info_seqno" : seqno,
        "memo"                   : $("#crm_info_collect_memo").val(),
        "empl_name"              : $("#crm_info_collect_empl_name").val(),
        "loan_pay_promi_dvs"     : promiDvs,
        "loan_pay_promi_date"    : $("#crm_info_collect_loan_promi_date").val(),
        "loan_pay_promi_price"   : $("#crm_info_collect_loan_promi_price").val()
        
    };
        
    if (checkBlank(data.loan_pay_promi_dvs)) {
        alert("결제종류를 입력해 주십시오.");
        return false;

    } else if (checkBlank(data.loan_pay_promi_date)) {
        alert("결제약속일을 선택해 주십시오.");
        return false;

    } else if (checkBlank(data.loan_pay_promi_price)) {
        alert("결제 약속금액을 입력해 주십시오.");
        return false;

    } else if (checkBlank(data.memo)) {
        alert("메모를 입력해 주십시오.");
        return false;
    } 

    var callback = function(result) {
        alert("수정되었습니다.");
        loadCrmInfoCollectFirst.exec();
        loadCrmInfoCollectList.exec();
    };

    ajaxCall(url, "text", data, callback);
};



/**
 * @brief Crm정보 수금 세부정보 불러오기 
 * @param data = 넘겨줄 데이터 값
 * @comment 기존에 사용하던 CRM수금 불러오기 함수
 *
 *//*
var loadCrmCollectDetail = {
    /**
     * @brief 체크박스 체크용 함수
     * @param req = 넘겨받은 데이터
     * @param ran = 적용할 id값
     *//*
    "aprvlCheck" : function(req, ran) { 
        if (req == "t") {
            $("#crm_info_collect_aprvl_"+ ran +"").prop("checked", true);
        } else if (req == "f") {
            $("#crm_info_collect_aprvl_"+ ran +"").prop("checked", false);
        }
    },
    "exec" : function(seqno) {
        $(".crm_info_tr").removeClass("active_tr");
        $("#crm_info_tr_" + seqno).addClass("active_tr");
        var url = "/json/business/order_mng/load_crm_info_detail.php";
        var data = {
            "seqno" : seqno // 글의 일련번호
        };
        var callback = function(result) {
            var str = result.loan_pay_promi_dvs;        // 결제종류
            var pop = str.split("!");                   // 결제종류 분할 
            var promiDate = result.loan_pay_promi_date; // 결제 약속일
            var handleDate = result.handle_date;        // 처리일시
            //날짜 자르는 함수(yyyy-mm-dd)
            var shortenDate = function(adate) {
                var sDate = adate.substr(0,10);    
                return sDate;
            }
            var handleDvs = result.handle_dvs;          // 처리여부
            var handleTyp = result.handle_typ;          // 처리방법
            var req_1      = result.aprvl_req_1;        // 승인요청 : 담당자
            var req_2      = result.aprvl_req_2;        // 승인요청 : 팀장
            var req_3      = result.aprvl_req_3;        // 승인요청 : 본부장 
            var req_4      = result.aprvl_req_4;        // 승인요청 : 대표이사
    
            $("#crm_info_collect_wm").val(pop[0]);
            $("#crm_info_collect_wm").trigger("change");
            $("#crm_info_collect_day").val(pop[1]); 
            $("#crm_info_collect_loan_promi_date").val(shortenDate(promiDate)); 
            $("#crm_info_collect_loan_promi_price").val(result.loan_pay_promi_price); 
            $("#crm_info_collect_memo").val(result.memo); 
            $("input[name='crm_info_collect_handle_dvs'][value="+handleDvs+"]").prop("checked", true);
            $("#crm_info_collect_cs_type").val(result.cs_typ); 
            $("#crm_info_collect_handle_date").val(shortenDate(handleDate));
            $("input[name='crm_info_collect_handle_typ'][value="+handleTyp+"]").prop("checked", true);
            loadCrmCollectDetail.aprvlCheck(req_1, "req_1");
            loadCrmCollectDetail.aprvlCheck(req_2, "req_2");
            loadCrmCollectDetail.aprvlCheck(req_3, "req_3");
            loadCrmCollectDetail.aprvlCheck(req_4, "req_4");
        };

        ajaxCall(url, "json", data, callback);
    }
};*/

/**
 * @brief 매출 거래현황정보 세부정보 불러오기 
 * @param data = 넘겨줄 데이터 값
 */
var loadSalesDetail = {
    "clickArea" : null,
    "exec"      : function(obj, searchDvs) {
        var seqno = loadMemberStatsInfo.seqno;
        if (checkBlank(seqno)) {
            return false;
        }

        if (obj == "") {
            var clickArea = loadSalesDetail.clickArea;
        } else {
        
            var clickArea = $(obj).attr("seq");

            $(".sales_detail_tr").removeClass("active_tr");
            $("#sales_detail_tr_" + clickArea).addClass("active_tr");

            this.clickArea = clickArea;
        }

        var targetDay = $("#sales_detail_tr_" + clickArea + " .sales_date").html();

        //TODO 검색 조건 관련 데이터 필요

        var url = "/json/business/order_mng/load_sales_detail.php";
        var data = {
            "seqno"         : seqno,
            "term_dvs"      : $(".btn_sales_info.btn_active").val(),
            "target_date"   : targetDay
        };

        if (searchDvs === "sales_detail") {
            var sig = $("#sales_info_keyword option:selected").html();
            if (sig == "제작물내용") {
                data.searchTxt = $("#sales_info_searchTxt").val();
            } else if(sig == "판번호") {
                data.sig = sig;
                data.searchTxt = $("#sales_info_searchTxt").val();
            }
            data.cate_mid = $("#sales_info_cate_mid").val();
            data.cate_bot = $("#sales_info_cate_bot").val();
        }

        var callback = function(result) {
            $("#sales_detail_sum").html(result.thead);
            $("#sales_detail_list").html(result.tbody);

            hideLoadingMask();
        };
        showLoadingMask();
        ajaxCall(url, "json", data, callback);
    }
};

/**
 * @brief 생산 투입한도조회 리스트 검색
 *
 * @param searchDvs = 검색구분
 */
var loadManuLimitList = {
    "data" : null,
    "exec" : function(searchDvs) {
        var seqno = loadMemberStatsInfo.seqno;
        if (checkBlank(seqno)) {
            return false;
        }
    
        var url = "/json/business/order_mng/load_manu_limit.php";
        var data = {
            "seqno" : seqno
        };
        var callback = function(result) {
            $("#manu_limit_list").html(result.tbody);
    
            pagingCommon("manu_limit_list_page",
                         "changePageManuLimitList",
                         10,
                         result.result_cnt,
                         5,
                         "init");
            hideLoadingMask();
    
        };
        //var popWidth  = $("#tableData2").width();
        //var popHeight = $("#tableData2").height();
        //var param = {
        //    "id"     : "sales_info_mask",
        //    "width"  : popWidth,
        //    "height" : popHeight
        //};
        showLoadingMask();
        this.data = data;
        ajaxCall(url, "json", data, callback);
    }
};

/**
 * @brief 생산 투입한도조회 페이지 변경시 호출
 *
 * @param page = 선택한 페이지
 */
var changePageManuLimitList = function(page) {
    if ($("#manu_limit_list_page_" + page).hasClass("page_accent")) {
        return false;
    }
    var seqno = loadMemberStatsInfo.seqno;
    if(checkBlank(seqno)) {
        return false;
    }
    if (isNaN(page)) {
        return false;
    }
    $(".manu_limit_list_page").removeClass("page_accent");
    $("#manu_limit_list_page_" + page).addClass("page_accent");
    
    var url = "/json/business/order_mng/load_manu_limit.php";
    var data = loadManuLimitList.data;
    data.page = page;
    data.page_dvs = '1';
    var callback = function(result) {
        hideLoadingMask();
        $("#manu_limit_list").html(result.tbody);
    };

    //var popWidth  = $("#left_content .fix_form").width();
    //var popHeight = $("#member_stats_list").height();
    //var param = {
    //    "id"     : "member_stats_list_mask",
    //    "width"  : popWidth,
    //    "height" : popHeight
    //};
    //showLoadingMask(param);
    //param = {
    //    "id"    : "member_stats_page_mask",
    //    "width" : popWidth,
    //    "top"   : 581 + (parseInt(popHeight) - 170)
    //};
    showLoadingMask();
    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 주문진행정보 검색
 *
 * @param searchDvs = 검색구분
 */
var loadOrderStatus = {
    "data" : null,
    "exec" : function(searchDvs) {
        var seqno = loadMemberStatsInfo.seqno;
        if (checkBlank(seqno)) {
            return false;
        }
    
        var depar         = '';
        var memberTyp     = '';
        var sortcodeT     = '';
        var sortcodeM     = '';
        var searchKeyword = '';
        var orderStatus   = $("#order_status").val();
        var from          = $("#basic_from").val();
        var to            = $("#basic_to").val();
    
        if (searchDvs === "order_status") {
            seqno         = '';
            depar         = $("#order_status_depar").val();
            memberTyp     = $("#order_status_member_typ").val();
            sortcodeT     = $("#order_status_cate_top").val();
            sortcodeM     = $("#order_status_cate_mid").val();
            searchDvs     = $("#order_status_search_dvs").val();
            searchKeyword = $("#order_status_search_keyword").val();
            from          = $("#order_status_from").val();
            to            = $("#order_status_to").val();
        }
    
        var url = "/json/business/order_mng/load_order_status.php";
        var data = {
            "seqno"          : seqno,
            "depar"          : depar,
            "member_typ"     : memberTyp,
            "sortcode_t"     : sortcodeT,
            "sortcode_m"     : sortcodeM,
            "search_dvs"     : searchDvs,
            "search_keyword" : searchKeyword,
            "order_status"   : orderStatus,
            "from"           : from,
            "to"             : to
        };
        var callback = function(result) {
            $("#order_status_order_cnt").html(result.order_cnt.format());
            $("#order_status_member_cnt").html(result.result_cnt.format());
            $("#order_status_sum_price").html(result.sum_price);
            $("#order_status_list").html(result.tbody);
            $("#order_status_sum_order").html(result.sum_sell);
            $("#order_status_sum_sale").html(result.sum_sale);
            $("#order_status_sum_pay").html(result.sum_pay);
    
            pagingCommon("order_status_page",
                         "changePageOrderStatus",
                         10,
                         result.result_cnt,
                         5,
                         "init");
            hideLoadingMask();
    
        };
        //var popWidth  = $("#tableData2").width();
        //var popHeight = $("#tableData2").height();
        //var param = {
        //    "id"     : "sales_info_mask",
        //    "width"  : popWidth,
        //    "height" : popHeight
        //};
        
        showLoadingMask();
        this.data = data;
        ajaxCall(url, "json", data, callback);
    }
};

/**
 * @brief 주문진행현황 변경해서 검색시 테이블 요약 html 변경
 */
var changeOrderStatusSummary = function(obj) {
    var title = $(obj).find("option:selected").text();
    var code  = $(obj).val();

    var html = "<strong id=\"order_status_title\">" + title + "현황</strong>";

    switch (code) {
        case "1120" :
            html += "회원수 :<span id=\"order_status_member_cnt\">-</span>";
            html += "&nbsp;/&nbsp;";
            html += "총주문예정금액 &nbsp;&#65510;";
            html += "<span id=\"order_status_sum_price\">0</span>";

            break;
        default :
            html += "Total :<span id=\"order_status_order_cnt\">-</span>";
            html += "&nbsp;/&nbsp;";
            html += "회원수 :<span id=\"order_status_member_cnt\">-</span>";
            html += "&nbsp;/&nbsp;";
            html += "총주문금액 &nbsp;&#65510;";
            html += "<span id=\"order_status_sum_order\">0</span>";
            html += "&nbsp;/&nbsp;";
            html += "총할인금액 &nbsp;&#65510;";
            html += "<span id=\"order_status_sum_sale\">0</span>";
            html += "&nbsp;/&nbsp;";
            html += "총결제금액 &nbsp;&#65510;";
            html += "<span id=\"order_status_sum_pay\">0</span>";

            break;
    }

    $("#order_status_summary").html(html);
};

/**
 * @brief 주문상태 페이지 변경시 호출
 *
 * @param page = 선택한 페이지
 */
var changePageOrderStatus = function(page) {
    if ($("#order_status_page_" + page).hasClass("page_accent")) {
        return false;
    }
    var seqno = loadMemberStatsInfo.seqno;
    if(checkBlank(seqno)) {
        return false;
    }
    if (isNaN(page)) {
        return false;
    }
    $(".order_status_page").removeClass("page_accent");
    $("#order_status_page_" + page).addClass("page_accent");
    
    var url = "/json/business/order_mng/load_order_status.php";
    var data = loadOrderStatus.data;
    data.page = page;
    data.page_dvs = '1';
    var callback = function(result) {
        hideLoadingMask();
        $("#order_status_list").html(result.tbody);
    };

    //var popWidth  = $("#left_content .fix_form").width();
    //var popHeight = $("#member_stats_list").height();
    //var param = {
    //    "id"     : "member_stats_list_mask",
    //    "width"  : popWidth,
    //    "height" : popHeight
    //};
    //showLoadingMask(param);
    //param = {
    //    "id"    : "member_stats_page_mask",
    //    "width" : popWidth,
    //    "top"   : 581 + (parseInt(popHeight) - 170)
    //};
    showLoadingMask();
    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 주문진행정보 문자 버튼 클릭시 팝업 출력
 *
 * @param code  = 상태코드
 * @param seqno = 회원일련번호
 * @param from  = 시작일
 * @param to    = 종료일
 */
var showOrderStatusDetailPop = function(obj, code, seqno, from, to) {
    var info = getMemberInfoByOrderStatusTd(obj);

    var url = "/business/popup/";
    if (code === "1120") {
        url += "pop_order_status_detail.html";
    } else if (code === "") {
    } else {
        url += "pop_order_status_detail.html";
    }
    url += "?code=" + code;
    url += "&seqno=" + seqno;
    url += "&from=" + from;
    url += "&to=" + to;
    url += "&name=" + encodeURI(info.name);
    url += "&cell=" + info.cell;


    window.open(url, "_blank", "width=900,height=510,status=no");
};

/**
 * @brief 주문진행정보 문자 버튼 클릭시 팝업 출력
 *
 * @param val = 대상 객체 클래스 셀렉터 생성용 특정값
 * @param dvs = toggle 위치 구분값
 */
var showOrderStatusMmsPop = function(obj, dvs) {
    var info = getMemberInfoByOrderStatusTd(obj);

    var param = {
        "name" : info.name,
        "cell" : info.cell
    };

    showModal(dvs, param);
};

/**
 * @brief 주문진행정보 Td에서 사용자명하고 핸드폰번호 추출
 *
 * @param obj = 
 */
var getMemberInfoByOrderStatusTd = function(obj) {
    var $tdArr = $(obj).parent().parent().children("td");
    var name = null;
    var cellNum = null;
    $tdArr.each(function(idx) {
        if (idx === 4) {
            name = $(this).text();
        }

        if (idx === 5) {
            cellNum = $(this).text();
            return false;
        }
    });

    return {
        "name" : name,
        "cell" : cellNum
    };
};

/**
 * @brief 신규회원정보 리스트 검색
 */
var loadNewMember = {
    "data" : null,
    "exec" : function(searchDvs) {
        var from = $("#basic_from").val();
        var to   = $("#basic_to").val();
        var memberName = '';

        if (searchDvs === "new_member") {
            memberName = $("#new_member_name").val();
            from = $("#new_member_from").val();
            to   = $("#new_member_to").val();
        }

        var url = "/json/business/order_mng/load_new_member.php";
        var data = {
            "cpn_admin_seqno" : $("#cpn_admin").val(),
            "member_name"     : memberName,
            "from"            : from,
            "to"              : to
        };
        var callback = function(result) {
            $("#new_member_list").html(result.tbody);
            
            pagingCommon("new_member_page",
                         "changePageNewMember",
                         5,
                         result.result_cnt,
                         5,
                         "init");
           
            hideLoadingMask();
        };

        this.data = data;

        //var popWidth  = $("#tableData4").width();
        //var popHeight = $("#content3 .detail_table").height();
        //var param = {
        //    "id"     : "crm_info_list_mask",
        //    "width"  : popWidth,
        //    "height" : popHeight
        //};
        //showLoadingMask(param);
        //param = {
        //    "id"    : "crm_info_list_page_mask",
        //    "width" : popWidth,
        //    "top"   : 893 + (parseInt(popHeight) - 33)
        //};
        showLoadingMask();
        ajaxCall(url, "json", data, callback);
    }
};

/**
 * @brief 생산 투입한도조회 페이지 변경시 호출
 *
 * @param page = 선택한 페이지
 */
var changePageNewMember = function(page) {
    if ($("#new_member_page_" + page).hasClass("page_accent")) {
        return false;
    }
    if (isNaN(page)) {
        return false;
    }
    $(".new_member_page").removeClass("page_accent");
    $("#new_member_page_" + page).addClass("page_accent");
    
    var url = "/json/business/order_mng/load_new_member.php";
    var data = loadNewMember.data;
    data.page = page;
    data.page_dvs = '1';
    var callback = function(result) {
        hideLoadingMask();
        $("#new_member_list").html(result.tbody);
    };

    //var popWidth  = $("#left_content .fix_form").width();
    //var popHeight = $("#member_stats_list").height();
    //var param = {
    //    "id"     : "member_stats_list_mask",
    //    "width"  : popWidth,
    //    "height" : popHeight
    //};
    //showLoadingMask(param);
    //param = {
    //    "id"    : "member_stats_page_mask",
    //    "width" : popWidth,
    //    "top"   : 581 + (parseInt(popHeight) - 170)
    //};
    showLoadingMask();
    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 매출정보 상세정보 row 숨김/확장
 *
 * @param obj = 대상 객체 클래스 셀렉터 생성용 특정값
 * @param dvs = toggle 위치 구분값
 */
var toggleSalesRow = function(obj, dvs) {
    var selector = ".toggleSales_" + dvs + '_' + obj;

    if (dvs === "mid" || !checkBlank($(selector).html())) {
        $(selector).toggleClass("hidden_row");

        return false;
    }
}

/**
 * @brief 매출정보 상세정보 초기화 함수
 */
var initSalesDetail = function() {
    $("#sales_detail_sum").html("");
    $("#sales_detail_list").html("");
};

/**
 * @brief 리스트 선택영역 기억 함수
 */
var saveSelectedBarArea = { 
    "str_stats"     : null,
    "str_crm_biz"   : null,
    "str_crm_col"   : null,
    "exec"          : function() {

        var str_stats   = $(".member_stats_tr.active_tr").attr("id");
        var str_crm_biz = $(".crm_info_business_tr.active_tr").attr("id");
        var str_crm_col = $(".crm_info_collect_tr.active_tr").attr("id");
        //var str = $(".member_stats_tr.active_tr").attr("id");
        saveSelectedBarArea.str_stats     = str_stats;
        saveSelectedBarArea.str_crm_biz   = str_crm_biz;
        saveSelectedBarArea.str_crm_col   = str_crm_col;

    }
};

/**
 * @brief 생산투입한도설정 팝업창 함수
 * @param 회원번호
 */
var showManuLimitModal = function(seqno, name) {
    var seqno = seqno;
    var name  = name;
    $("#manu_limit_modal input").val('');
    $("#manu_limit_modal .datepicker_input").datepicker("setDate", '0');
    $("#manu_limit_member_title").text(name);
    $("#manu_limit_member_name").val(name);
    $("#manu_limit_modal").reveal();
    loadManuLimit.exec(seqno);
};

/**
 * @brief 생산투입한도설정 등록 함수
 *
 * @param seqno 회원번호
 */
var submitManuLimit = function() {
    var seqno = loadMemberStatsInfo.seqno;
    if (checkBlank(seqno)) {
        return false;
    }
    var url = "/proc/business/order_mng/insert_manu_limit.php";
    var data = {
        "member_seqno"      : seqno,
        "limit_price"       : $("#manu_limit_price").val(), // 생산한도설정금액
        "deal_date"         : $("#manu_limit_deal_date").val(), // 거래날짜
      //"regi_empl"         : $("#manu_limit_regi_empl").val(), // 담당자
        "memo"              : $("#manu_limit_memo").val(), // 내용
      //"limit_cate"        : $("#manu_limit_cate").val(), // 조정상품
      //"release_empl"      : $("#manu_limit_release_empl").val(), // 출고담당
      //"depo_yn"           : $("#manu_limit_depo_yn").val(), // 입금여부
        "depo_promi_date"   : $("#manu_limit_depo_promi_date").val() // 입금약속일 
    };

    if (checkBlank(data.depo_promi_date)) {
        alert("거래날짜를 입력해 주십시오.");
        return false;
        
    } else if (checkBlank(data.limit_price)) {
        alert("생산한도설정금액을 입력해 주십시오.");
        return false;

    } else if (checkBlank(data.memo)) {
        alert("내용을 입력해 주십시오.");
        return false;

    }

    var callback = function(result) {
        if (!checkBlank(result)) {
            return alertReturnFalse(result); 
        }

        alert("등록되었습니다."); 
        loadManuLimit.exec(seqno);
    };

    ajaxCall(url, "text", data, callback);

};

/**
 * @brief 생산투입한도 리스트 불러오는 함수
 *
 * @param seqno 회원번호
 */
var loadManuLimit = {
    "data" : null,
    "exec" : function(seqno) {
        var url = "/json/business/order_mng/load_manu_limit.php";
        var data = {
            "seqno" : seqno // 회원 일련번호
        };
        var callback = function(result) {
            $("#manu_limit_sum").html(result.thead);
            $("#manu_limit_tbody").html(result.tbody);
            pagingCommon("manu_limit_page",
                         "changePageManuLimit",
                         5,
                         result.result_cnt,
                         5,
                         "init");
        };

        this.data = data;

        ajaxCall(url, "json", data, callback);
    }
};

/**
 * @brief 생산투입한도리스트 페이지 변경시 호출
 * 
 * @param page = 선택한 페이지
 */
var changePageManuLimit = function(page) {
    if ($("#manu_limit_page_" + page).hasClass("page_accent")) {
        return false;
    }
    if (isNaN(page)) {
        return false;
    }

    $(".manu_limit_page").removeClass("page_accent");
    $("#manu_limit_page_" + page).addClass("page_accent");

    var url = "/json/business/order_mng/load_manu_limit.php";
    var data = loadManuLimit.data;
    data.page = page;
    data.page_dvs = '1';
    var callback = function(result) {
        $("#manu_limit_sum").html(result.thead);
        $("#manu_limit_tbody").html(result.tbody);
    };

    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 생산투입한도리스트 엑셀다운로드시 호출
 * 
 * @param page = 선택한 페이지
 */
var manuLimitExcelDown = function() {

    var url  = "/ajax/business/order_mng/down_excel_manu_limit.php?";
    url += "seqno=" + loadManuLimit.data.seqno;

    $("#file_ifr").attr("src", url);
};

/**
 * @brief 매출액,입금액 조정 팝업창
 * @param 회원번호
 */
var showSalesDepoModal = function(seqno, name) {
    var seqno = seqno;
    var name  = name;
    initSalesDepo();
    $("#sales_depo_modal").reveal();
    $("#sales_depo_member_title").text(name);
    $("#sales_depo_name").val(name);
    loadSalesDepo.exec(seqno);
};

/**
 * @brief 매출액,입금액 조정 초기화
 */
var initSalesDepo = function() {
    $("#sales_depo_deal_date").val("");
    $("#sales_depo_input_pre").val("매출");
    $("#sales_depo_input_suf").val("증가");
    loadSalesDepoInputTyp();
    $("#sales_depo_cont").val("");
    $("#sales_depo_price").val("");
    $("#cont_detail_txt").val("");
    $("#cont_detail_select").val("외환");
    $("#sales_depo_card_num").val("");
    $("#sales_depo_card_mip_mon").val("");
    $("#sales_depo_card_aprvl_num").val("");
    $("#sales_depo_card_aprvl_date").val("");
};

/**
 * @brief 매출액, 입금액 등록
 *
 * @param seqno 회원번호
 */
var submitSalesDepoData = function() {
    var seqno = loadMemberStatsInfo.seqno;
    if (checkBlank(seqno)) {
        return false;
    }
    var url = "/proc/business/order_mng/insert_sales_depo.php";
    var data = {
        "member_seqno"      : seqno,
        "deal_date"         : $("#sales_depo_deal_date").val(), // 거래날짜 
        "dvs_input"         : ($("#sales_depo_input_pre").val()
                            + $("#sales_depo_input_suf").val()), // 입력구분
        "input_typ"         : $("#sales_depo_input_typ").val(), // 입력유형
        "cont"              : $("#sales_depo_cont").val(), // 내용
        "depo_price"        : $("#sales_depo_price").val(), // 입금액 
     // "dvs_detail"        : $("#sales_depo_dvs_detail").val(), // 입금구분
        "cont_detail_txt"   : $("#cont_detail_txt").val(), // 입력세부내용(가상계좌)
        "cont_detail_select": $("#cont_detail_select").val(), // 입력세부내용(카드)
        "card_num"          : $("#sales_depo_card_num").val(), // 카드번호
        "mip_mon"           : $("#sales_depo_card_mip_mon").val(), // 할부개월
        "aprvl_num"         : $("#sales_depo_card_aprvl_num").val(), // 승인번호
        "aprvl_date"        : $("#sales_depo_card_aprvl_date").val() // 승인일시
    };

    var depoStr = $("#sales_depo_input_typ").val();

    if (checkBlank(data.deal_date)) {
        alert("거래날짜를 입력해 주십시오.");
        return false;
        
    } else if (checkBlank(data.dvs_input)) {
        alert("입력구분을 입력해 주십시오.");
        return false;

    } else if (checkBlank(data.input_typ)) {
        alert("입력유형을 입력해 주십시오.");
        return false;

    } else if (checkBlank(data.cont)) {
        alert("내용을 입력해 주십시오.");
        return false;

    } else if (checkBlank(data.depo_price)) {
        alert("입금액을 입력해 주십시오.");
        return false;

    } /*else if (checkBlank(data.dvs_detail)) {
        alert("입금구분을 선택해 주십시오.");
        return false;
    }*/

    // 입금 입력유형 가상계좌 일경우    
    if (depoStr == "100") {
    
        if (checkBlank(data.cont_detail_txt)) {
            alert("입력세부내용(계좌번호)을 입력해 주십시오.");
            return false;
        }
    // 입금 입력유형 카드 일경우
    } else if (depoStr == "102") {

        if (checkBlank(data.card_num)) {
            alert("카드번호를 입력해 주십시오.");
            return false;
        } else if (checkBlank(data.mip_mon)) {
            alert("할부개월을 입력해 주십시오.");
            return false;
        } else if (checkBlank(data.aprvl_num)) {
            alert("승인번호를 입력해 주십시오.");
            return false;
        } else if (checkBlank(data.aprvl_date)) {
            alert("승인일시를 입력해 주십시오.");
            return false;
        }
    }

    var callback = function(result) {
        if (!checkBlank(result)) {
            return alertReturnFalse(result); 
        }

        alert("등록되었습니다."); 
        saveSelectedBarArea.exec();
        loadSalesDepo.exec(seqno);
        initSalesDepo();

        // 모달팝업 뒤 데이터 업데이트 적용
        var page = $(".member_stats_page.page_accent").html()
        changePageMemberStats(page);
        
        var seq = loadMemberStatsInfo.seqno;
        var nam = loadMemberStatsInfo.name;
        var nik = loadMemberStatsInfo.nik;
        loadMemberStatsInfo.exec(seq, nam, nik);
        
    };

    ajaxCall(url, "text", data, callback);

};

/**
 * @brief 입력유형 변경시 입력 세부내용 변경
 *
 * @param code = 입력 세부내용
 */
var loadSalesDepoInputDetail = function(code) {
    switch (code) {
        case "100" :
            $("#cont_detail_txt").prop('disabled', false);
            $("#cont_detail_txt").show();
            $("#cont_detail_select").hide();
            // disabled prop = false
            break;
        case "102" :
            $("#cont_detail_txt").hide();
            $("#cont_detail_select").show();
            $("#sales_depo_card_num").prop('disabled', false);
            $("#sales_depo_card_mip_mon").prop('disabled', false);
            $("#sales_depo_card_aprvl_num").prop('disabled', false);
            $("#sales_depo_card_aprvl_date").prop('disabled', false);
            break;
        default :
            $("#cont_detail_txt").prop('disabled', true);
            $("#sales_depo_card_num").prop('disabled', true);
            $("#sales_depo_card_mip_mon").prop('disabled', true);
            $("#sales_depo_card_aprvl_num").prop('disabled', true);
            $("#sales_depo_card_aprvl_date").prop('disabled', true);
            $("#cont_detail_txt").show();
            $("#cont_detail_select").hide();
            // disabled prop = true
            break;
    }
};

/**
 * @brief 매출액,입금액 리스트 불러옴
 *
 * @param seqno 회원번호
 */
var loadSalesDepo = {
    "data" : null,
    "exec" : function(seqno) {
        var url = "/json/business/order_mng/load_sales_depo.php";
        var data = {
            "seqno" : seqno // 회원 일련번호
        };
        var callback = function(result) {
            $("#sales_depo_list").html(result.list);
            $("#sales_depo_bal").html(result.pre);
            pagingCommon("sales_depo_page",
                         "changePageSalesDepo",
                         5,
                         result.result_cnt,
                         5,
                         "init");
        };

        this.data = data;

        ajaxCall(url, "json", data, callback);
    }
};

/**
 * @brief 매출액,입금액 페이지 변경시 호출
 * 
 * @param page = 선택한 페이지
 */
var changePageSalesDepo = function(page) {
    if ($("#sales_depo_page_" + page).hasClass("page_accent")) {
        return false;
    }
    if (isNaN(page)) {
        return false;
    }

    $(".sales_depo_page").removeClass("page_accent");
    $("#sales_depo_page_" + page).addClass("page_accent");

    var url = "/json/business/order_mng/load_sales_depo.php";
    var data = loadSalesDepo.data;
    data.page = page;
    data.page_dvs = '1';
    var callback = function(result) {
        $("#sales_depo_list").html(result.list);
    };

    ajaxCall(url, "json", data, callback);
};


/**
 * @brief 생산투입한도 입력유형 불러옴
 *
 * @param seqno 회원번호
 */
var loadSalesDepoInputTyp = function() {

    var dvs = $("#sales_depo_input_pre").val();
    
    if (dvs == "매출") {
        var opt_val  = "<option value=\"000\">제품구입(수기입력)</option>";
            opt_val += "<option value=\"001\">주문취소</option>";
            opt_val += "<option value=\"002\">사고처리</option>";
            opt_val += "<option value=\"003\">별도견적</option>";
            opt_val += "<option value=\"004\">재단비</option>";
            opt_val += "<option value=\"005\">후가공</option>";
            opt_val += "<option value=\"006\">배송비</option>";
            opt_val += "<option value=\"007\">기타</option>";
        $("#sales_depo_input_typ").html(opt_val);
    } else if (dvs == "입금") {
        var opt_val  = "<option value=\"100\">가상계좌</option>";
            opt_val += "<option value=\"101\">현금</option>";
            opt_val += "<option value=\"102\">카드</option>";
            opt_val += "<option value=\"103\">은행</option>";
            opt_val += "<option value=\"104\">기타</option>";

        $("#sales_depo_input_typ").html(opt_val);
    }
    $("#sales_depo_input_typ").trigger("change");

};

/**
 * @brief 품목별 상세 현황정보 팝업 출력
 *
 * @param sortcodeTop = 카테고리 대분류
 */
var openPrdtDetailPop = function(sortcodeTop) {
    var seqno = loadMemberStatsInfo.seqno;
    if (checkBlank(seqno)) {
        return false;
    }

    var url = "/business/popup/pop_prdt_detail.html?";
    url += "sortcode_t=" + sortcodeTop;
    url += "&member_seqno=" + seqno;
    url += "&member_name=" + encodeURI(loadMemberStatsInfo.name);
    url += "&member_nick=" + encodeURI(loadMemberStatsInfo.nick);
    window.open(url);
};

/**
 * @brief 입금액 팝업창 불러옴
 * 2017/07/25 이청산 수정(함수 파라미터 값 변경)
 *
 * @param seqno 회원번호
 */
var showDepoViewPopup = function(searchDvs) {
    
    var name = $("#search_office_nick").val();

    $("#depo_view_modal").reveal(); 
    $("#depo_view_name").val(name);
    loadDepoViewData.exec(searchDvs);

};

/**
 * @brief 입금액 팝업창 데이터 불러옴
 * 2017/07/25 이청산 수정(searchDvs로 초기 검색 데이터 분기)
 *
 * @param seqno 회원번호
 */
var loadDepoViewData = {
    "data" : null,
    "exec" : function(searchDvs) {
        var seqno = loadMemberStatsInfo.seqno;
        if (checkBlank(seqno)) {
            return false;
        }

        var url = "/json/business/order_mng/load_depo_view.php";
        var data = {
            "seqno"     : seqno // 회원 일련번호
        };

        var from  = "";
        var to    = "";
        var met   = "";

        if (searchDvs == "common") {
            from  = $("#basic_from").val();
            to    = $("#basic_to").val();
            met   = "100";       // 입금방법 기본값은 가상계좌(100)

            $("#depo_view_from").datepicker('setDate', from);
            $("#depo_view_to").datepicker('setDate', to);
        } else if (searchDvs == "depo_view") {
            from  = $("#depo_view_from").val();
            to    = $("#depo_view_to").val();
            met   = $("#depo_view_met").val();
        }
        data.from = from;
        data.to   = to;
        data.met  = met;

        var callback = function(result) {
            $("#depo_view_list").html(result.list);
            pagingCommon("depo_view_page",
                         "changePageDepoView",
                         5,
                         result.result_cnt,
                         5,
                         "init");
        };

        this.data = data; 

        ajaxCall(url, "json", data, callback);
    }
};

/**
 * @brief 매출액,입금액 페이지 변경시 호출
 * 
 * @param page = 선택한 페이지
 */
var changePageDepoView = function(page) {
    if ($("#depo_view_page_" + page).hasClass("page_accent")) {
        return false;
    }
    if (isNaN(page)) {
        return false;
    }

    $(".depo_view_page").removeClass("page_accent");
    $("#depo_view_page_" + page).addClass("page_accent");

    var url = "/json/business/order_mng/load_depo_view.php";
    var data = loadDepoViewData.data;
    data.page = page;
    data.page_dvs = '1';
    var callback = function(result) {
        $("#depo_view_list").html(result.list);
    };

    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 에누리 팝업창
 * @param 회원번호
 */
var showDiscountViewModal = function() {
    var seqno = loadMemberStatsInfo.seqno;
    if (checkBlank(seqno)) {
        return false;
    }
    $("#discount_view_modal input").val('');
    var name = $("#search_office_nick").val();
    $("#discount_view_member_name").val(name);
    $("#discount_view_modal").reveal();
    loadDiscountData.exec();
};

/**
 * @brief 에누리 팝업창 조회
 * @param 회원번호
 */
var loadDiscountData =  {
    "data" : null,
    "exec" : function(searchDvs) {
        var seqno = loadMemberStatsInfo.seqno;
        if (checkBlank(seqno)) {
            return false;
        }
        
        var url = "/json/business/order_mng/load_discount_view.php";

        var data = {
            "seqno"     : seqno // 회원 일련번호
        };

        var callback = function(result) {
            $("#discount_view_list").html(result.list);
            pagingCommon("discount_view_page",
                         "changePageDiscountView",
                         5,
                         result.result_cnt,
                         5,
                         "init");
        };

        this.data = data; 

        ajaxCall(url, "json", data, callback);
    }
};

/**
 * @brief 에누리 팝업창 등록
 * @param 
 */
var insertDiscountViewData = function() {
    var seqno = loadMemberStatsInfo.seqno;
    if (checkBlank(seqno)) {
        return false;
    }
    var url = "/proc/business/order_mng/insert_discount_view_data.php";
    var data = {
        "member_seqno"      : seqno,
        "depo_price"        : $("#discount_view_depo_price").val(),
        "cont"              : $("#discount_view_cont").val()
    };

    if (checkBlank(data.depo_price)) {
        alert("입금액을 입력해 주십시오.");
        return false;
        
    } else if (checkBlank(data.cont)) {
        alert("상세내용을 입력해 주십시오.");
        return false;

    }

    var callback = function(result) {
        if (!checkBlank(result)) {
            return alertReturnFalse(result); 
        }

        alert("등록되었습니다."); 
        initDiscountModal();
        loadDiscountData.exec();
    };

    ajaxCall(url, "text", data, callback);

};

/**
 * @brief 에누리 팝업창 초기화
 * @param 
 */
var initDiscountModal = function() {
    $("#discount_view_met").val("");
    $("#discount_view_depo_price").val("");
    $("#discount_view_cont").val("");
};

/**
 * @brief 에누리 팝업창 페이징 
 * 
 * @param page = 선택한 페이지
 */
var changePageDiscountView = function(page) {
    if ($("#discount_view_page_" + page).hasClass("page_accent")) {
        return false;
    }
    if (isNaN(page)) {
        return false;
    }

    $(".discount_view_page").removeClass("page_accent");
    $("#discount_veiw_page_" + page).addClass("page_accent");

    var url = "/json/business/order_mng/load_discount_view.php";
    var data = loadDiscountData.data;
    data.page = page;
    data.page_dvs = '1';
    var callback = function(result) {
        $("#discount_view_list").html(result.list);
    };

    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 회원 메모 모달팝업
 *
 * @param onum = 주문번호
 */
var showOrderCustMemo = function(onum) {
    // 이벤트 버블링 방지용
    event.stopPropagation();

    var url = "/json/business/order_mng/load_order_cust_memo.php";
    var data = {
        "order_num" : onum
    };

    var callback = function(result) {
        $("#order_cust_memo_modal").reveal();
        $("#cust_memo").val(result.memo_cont);
    };

    ajaxCall(url, "json", data, callback);
};
