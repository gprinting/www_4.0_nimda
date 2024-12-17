/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/05/19 이청산
 *=============================================================================
 */


$(document).ready(function() {
    $(".datepicker_input").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true
    }).datepicker("setDate", '0');
});

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
            "member_typ"      : $("#member_typ").val(),
            "member_grade"    : $("#member_grade").val(),
            "search_dvs"      : $("#search_dvs").val(),
            "search_keyword"  : $("#search_keyword").val()

        };

        return data;

    },
    "exec"    : function() {
        var url = "/json/business/order_mng/load_esti_stats_info.php";
        var data = this.setData();
        var callback = function(result) {
            $("#esti_total_cnt").html(result.total_cnt.format());
            $("#esti_result_cnt").html(result.result_cnt.format());
            $("#esti_stats_cnt").html(result.list);
            pagingCommon("esti_stats_page",
                         "changePageEstiStats",
                         5,
                         result.result_cnt,
                         5,
                         "init");
            hideLoadingMask();
        };

        this.data = data;

        var popWidth  = $("#quo_content1 .for_table").width();
        var popHeight = $("#esti_stats_list").height();
        var param = {
            "id"     : "esti_stats_list_mask",
            "width"  : popWidth,
            "height" : popHeight,
        };
        showLoadingMask();
        console.log(popWidth);
        console.log(popHeight);
    }

}
