/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/07/21 ujh
 *=============================================================================
 */

/**
 * @brief 각 탭 정보 최초로딩했는지 여부
 * 탭 클릭할 때 마다 재조회 하는가 방지용
 */
var tabDataLoad = {
    "oa_sales_info"   : false,
    "sales_depo_info" : false
};

var sortFlag = {
    "oa_sales" : {
        "sum_net_price"     : '',
        "sum_depo_price"    : '',
        "sum_period_end_oa" : ''
    },
    "oa_sales_detail" : {
        "office_nick"       : '',
        "member_typ"        : '',
        "sum_net_price"     : '',
        "sum_depo_price"    : '',
        "sum_period_end_oa" : '',
        "m1_sum_net"        : '',
        "avg_sum_net"       : ''
    },
    "sales_depo_info" : {
        "member_name"       : '',
        "deal_date"         : '',
        "dvs"               : '',
        "sell_price"        : '',
        "exist_prepay"      : '',
        "pay_price"         : '',
        "depo_price"        : '',
        "prepay_bal"        : '',
        "input_typ"         : '',
        "empl_name"         : '',
        "cont"              : ''
    }
}

$(document).ready(function() {
    $(".datepicker_input").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0');
});

/**
 * @brief 입금유형 변경시 항목에 따라 상세항목 표시
 */
var changeDepoInputTyp = function(val) {
    var url = "/ajax/business/settle_mng/load_depo_input_typ_detail.php";
    var data = {
        "depo_typ" : val
    };
    var callback = function(result) {
        $("#depo_input_detail").html(result);
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 선택조건으로 검색 버튼 눌렀을때 실행
 */
var cndSearch = {
    "data" : null,
    "setData" : function() {
        //var highDepar = $($("#depar > option")[1]).val().substr(0, 3);

        var data = {
            "cpn_admin_seqno"   : $("#cpn_admin").val(),
            "basic_from"        : $("#basic_from").val(),
            "basic_to"          : $("#basic_to").val(),
            "depar"             : $("#depar").val(),
          //  "high_depar"        : highDepar,
            "empl"              : $("#empl").val(),
            "depo_input_typ"    : $("#depo_input_typ").val(),
            "depo_input_detail" : $("#depo_input_detail").val(),
            "deal_yn"           : $("#deal_yn").val(),
            "dlvr_dvs"          : $("#dlvr_dvs").val(),
            "dlvr_code"         : $("#dlvr_code").val(),
            "search_dvs"        : $("#search_dvs").val(),
            "search_keyword"    : $("#search_keyword").val(),
            "search_depo"       : $("#search_depo").val(),
            "depo_dvs"          : $("#depo_dvs").val(),
            "depo_keyword"      : $("#depo_keyword").val(),
            "member_typ"        : $("#member_typ").val(),
            "member_grade"      : $("#member_grade").val(),
            "cate_top"          : $("#cate_top").val(),
            "cate_mid"          : $("#cate_mid").val(),
            "cate_bot"          : $("#cate_bot").val(),
            "oper_sys"          : $("#oper_sys").val(),
            "oa_yn"             : $("#oa_yn").val()
        };

        return data;
    },
    "exec" : function() {
        this.data = this.setData();
        initObjValAllFalse("tabDataLoad");
        loadOaSalesDetail.seqno = null;
        $("#oa_sales_detail_head").html('');
        $("#oa_sales_detail_sum").html('');
        $("#oa_sales_info_detail_body").html('');
        $("#oa_sales_detail_page").html('');

        showLoadingMask();

        changeTab();
    }
};

/**
 * @brief 탭 변경시 정보검색
 */
var changeTab = function() {
    var curTab = $("input[name='lastTable']:checked").val();

    if (tabDataLoad[curTab]) {
        return false;
    }

    if (curTab === "oa_sales_info") {
        $(".order_th").find(".sort").removeClass("asc");
        $(".order_th").find(".sort").removeClass("desc");
        loadOaSalesInfo();
    } else if (curTab === "sales_depo_info") {
        loadSalesDepoInfo.exec();
    }

    //tabDataLoad[curTab] = true;
};

/**
 * @brief 탭 변경에 따른 검색조건 활성화
 */ 
var changeTabSearchCond = function() {
    var curTab = $("input[name='lastTable']:checked").val();
    if (tabDataLoad[curTab]) {
        return false;
    }
    if (curTab === "oa_sales_info") {
        $("#oa_sales_info").prop("checked", true);
        $("#deal_yn").prop("disabled", false);
        $("#cate_top").prop("disabled", false);
        $("#cate_mid").prop("disabled", false);
        $("#cate_bot").prop("disabled", false);
        $("#oa_yn").prop("disabled", false);
        $("#oper_sys").prop("disabled", false);
        $("#search_dvs").val("title");
        $("#depo_dvs").prop("disabled", true);
        $("#depo_keyword").prop("disabled", true);
        changeInputBox("title");
    } else if (curTab === "sales_depo_info") {
        $("#sales_depo_info").prop("checked", true);
        $("#deal_yn").prop("disabled", true);
        $("#cate_top").prop("disabled", true);
        $("#cate_mid").prop("disabled", true);
        $("#cate_bot").prop("disabled", true);
        $("#oa_yn").prop("disabled", true);
        $("#oper_sys").prop("disabled", true);
        $("#search_dvs").val("price");
        $("#depo_dvs").prop("disabled", false);
        $("#depo_keyword").prop("disabled", false);
        changeInputBox("price");
    }
};

/**
 * @brief 개인별 업체별 미수/매출액 검색
 */
var loadOaSalesInfo = function() {
    var url = "/json/business/settle_mng/load_oa_sales_info.php";
    var callback = function(result) {
        $("#oa_sales_info_sum").html(result.sum);
        $("#oa_sales_info_list").html(result.list);

        hideLoadingMask();
        pagingCommon("oa_sales_info_page",
                     "changeOaSalesPage",
                     5,
                     result.result_cnt,
                     5,
                     "init");

        hideLoadingMask();
    };

    ajaxCall(url, "json", cndSearch.data, callback);
};

/**
 * @brief 개인별 업체별 미수/매출액 페이지 변경시 호출
 * 
 * @param page = 선택한 페이지
 * @param isSort = 정렬로 인한 재검색인지
 */
var changeOaSalesPage = function(page, isSort) {
    if (!isSort && $("#oa_sales_info_page_" + page).hasClass("page_accent")) {
        return false;
    }

    $(".oa_sales_info_page").removeClass("active_page");
    $("#oa_sales_info_page_" + page).addClass("active_page");

    var url = "/json/business/settle_mng/load_oa_sales_info.php";
    var data = cndSearch.data;
    data.empl = null;
    data.page = page;
    data.page_dvs = '1';
    var callback = function(result) {
        $("#oa_sales_info_list").html(result.list);
        $("#oa_sales_tr_" + loadOaSalesDetail.seqno).addClass("active_tr");
    };

    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 개인별 업체별 미수/매출액에서 tr 클릭시 담당자에 지정된 업체별 상세정보 검색
 *
 * @param seqno = 직원 일련번호
 */
var loadOaSalesDetail = {
    "seqno" : null,
    "exec"  : function(seqno) {
        this.seqno = seqno;

        $(".active_tr").removeClass();
        $("#oa_sales_tr_" + seqno).addClass("active_tr");

        var url = "/json/business/settle_mng/load_oa_sales_detail.php";
        var data = cndSearch.data;
        data.page = '';
        data.page_dvs = '';
        data.empl = seqno;
        var callback = function(result) {
            hideLoadingMask();
            $("#oa_sales_detail_head").html(result.thead);
            $("#oa_sales_detail_sum").html(result.sum);
            $("#oa_sales_info_detail_body").html(result.list);

            pagingCommon("oa_sales_detail_page",
                         "changeOaSalesDetailPage",
                         5,
                         result.result_cnt,
                         5,
                         "init");

            
        };

        showLoadingMask();

        ajaxCall(url, "json", data, callback);
    }
};

/**
 * @brief 매출액,입금액 페이지 변경시 호출
 * 
 * @param page = 선택한 페이지
 * @param isSort = 정렬로 인한 재검색인지
 */
var changeOaSalesDetailPage = function(page, isSort) {
    if (!isSort && $("#oa_sales_detail_page_" + page).hasClass("page_accent")) {
        return false;
    }

    $(".oa_sales_detail_page").removeClass("active_page");
    $("#oa_sales_detail_page_" + page).addClass("active_page");

    var url = "/json/business/settle_mng/load_oa_sales_detail.php";
    var data = cndSearch.data;
    data.page = page;
    data.page_dvs = '1';
    data.empl = loadOaSalesDetail.seqno;
    var callback = function(result) {
        $("#oa_sales_info_detail_body").html(result.list);
    };

    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 매출액/입금액 조정내역 조회
 */
var loadSalesDepoInfo = {
    "data" : null,
    "exec" : function() {
        var url  = "/json/business/settle_mng/load_sales_depo_info.php";
        var data = cndSearch.data;
        var empl = $("#empl option:selected").text();
        if (empl == "전체") {
            empl = "";
        }
        data.empl = empl;
        var dlvr_code = $("#dlvr_code option:selected").text();
        if (dlvr_code == "전체") {
            dlvr_code = "";
        }
        data.dlvr_code = dlvr_code;
        var searchDepo = $("#search_depo").val();
        if (searchDepo == "sell") {
            $("#sale_depo_price").html("매출액<i class=\"fa fa-sort\"></i>");
        } else if (searchDepo == "depo") {
            $("#sale_depo_price").html("입금액<i class=\"fa fa-sort\"></i>");
        } else {
            $("#sale_depo_price").html("매출액<i class=\"fa fa-sort\"></i><br />입금액<i class=\"fa fa-sort\"></i>");
        }
        var callback = function(result) {
            $("#sales_depo_info_list_head").html(result.thead);
            $("#sales_depo_info_list_body").html(result.tbody);
            pagingCommon("sales_depo_list_page",
                         "changePageSalesDepoList",
                         5,
                         result.result_cnt,
                         5,
                         "init");

            hideLoadingMask();
        };

        ajaxCall(url, "json", data, callback);
        this.data = data;
    }
};

/**
 * @brief 매출액/입금액 페이지 변경시 호출
 *
 * @param page = 선택한 페이지
 */
var changePageSalesDepoList = function(page) {
    /* 결산관리는 정렬을 위해 이부분 주석처리함
    if ($("#sales_depo_list_page_" + page).hasClass("page_accent")) {
        return false;
    }
    */
    if (isNaN(page)) {
        return false;
    }

    $(".sales_depo_list_page").removeClass("page_accent");
    $("#sales_depo_list_page_" + page).addClass("page_accent");

    var url = "/json/business/settle_mng/load_sales_depo_info.php";
    var data = loadSalesDepoInfo.data;
    data.page = page;
    data.page_dvs = '1';
    var callback = function(result) {
        hideLoadingMask();
        $("#sales_depo_info_list_head").html(result.thead);
        $("#sales_depo_info_list_body").html(result.tbody);
    };

    showLoadingMask();
    
    ajaxCall(url, "json", data, callback);

};



/**
 * @brief 정렬 플래그 세팅 후 재검색
 */
var changeSort = function(obj, area, dvs) {

    if (checkBlank(cndSearch.data)) {
        return false;
    }

    var flag = sortFlag[area][dvs];

//    $(".order_th").find(".sort").removeClass("asc");
//    $(".order_th").find(".sort").removeClass("desc");
//    $(".order_th").find(".sort_s").removeClass("asc");
//    $(".order_th").find(".sort_s").removeClass("desc");
//    $(".order_th").find(".sort_d").removeClass("asc");
//    $(".order_th").find(".sort_d").removeClass("desc");
        
    $(".order_th").find(".fa").removeClass("fa-sort-asc");
    $(".order_th").find(".fa").removeClass("fa-sort-desc");
//    $(obj).find(".sort").html('');
//    $(obj).find(".sort_s").html('');
//    $(obj).find(".sort_d").html('');
    $(obj).find(".fa").html('');

    if (flag === "ASC") {
        //$(obj).find(".sort").removeClass("desc");
//        $(obj).find(".sort").addClass("asc");
//        $(obj).find(".sort_s").addClass("asc");
//        $(obj).find(".sort_d").addClass("asc");
        $(obj).find(".fa").addClass("fa-sort-asc");
        sortFlag[area][dvs] = "DESC";
    } else {
        //$(obj).find(".sort").removeClass("asc");
//        $(obj).find(".sort").addClass("desc");
//        $(obj).find(".sort_s").addClass("desc");
//        $(obj).find(".sort_d").addClass("desc");
        $(obj).find(".fa").addClass("fa-sort-desc");
        sortFlag[area][dvs] = "ASC";
    }

    var order = {};
    order[dvs] = sortFlag[area][dvs];
    cndSearch.data.order = order;

    switch(area) {
        case "oa_sales" :
            var page = $(".oa_sales_info_page.page_accent").html();
            changeOaSalesPage(page, true);
            break;
        case "oa_sales_detail" :
            var page = $(".oa_sales_detail_page.page_accent").html();
            changeOaSalesDetailPage(page, true);
            break;
        case "sales_depo_info" : 
            var page = $(".sales_depo_list_page.page_accent").html();
            changePageSalesDepoList(page, true);
            break;
    }
};

/**
 * @brief 키워드검색 항목변경시 호출
 */
var changeInputBox = function(val) {
    var inputVal = val;
    if (inputVal == "price") {
        $("#search_keyword").hide();
        $("#search_depo").show();
    } else if (inputVal == "title") {
        $("#search_keyword").show();
        $("#search_depo").hide();
    }
};


