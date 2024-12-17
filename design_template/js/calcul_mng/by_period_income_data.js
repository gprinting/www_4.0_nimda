$(document).ready(function() {
    $(".date").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0');

    var date = $("#date_to").val().split('-');
    date = date[0] + "-" + date[1] + "-01";
    $("#date_from").val(date);

    //loadIncomeList(1);

    tabInit();
    $("#paper_op_1").show();
});

var page = "1";
var list_num = "30";

//입출금경로 상세 불러오기
var loadPathDetail = function() {

    //입출금경로 미선택시
    if ($("#depo_path").val() == "") {

        $("#depo_path_detail").html("<option value=\"\">선택</option>");
        return false;

    }

    $.ajax({

        type: "POST",
        data: {
            "depo_path" : $("#depo_path").val()
        },
        url: "/ajax/calcul_mng/income_data/load_path_detail.php",
        success: function(result) {

            $("#depo_path_detail").html(result);
        },
        error: getAjaxError
    });
}

var select_day_row = function(date, value) {
    showMask();
    var formData = new FormData();
    formData.append("member_seqno", $("#member_seqno").val());
    formData.append("date", date);
    $("#list2 > tr").each(function() {
        $(this).removeClass("cell_bold");
    });
    $(value).addClass("cell_bold");


    loadOrderInfo("common", date);
}

var loadOrderInfo = function(searchDvs, date) {
    var seqno = $("#member_seqno").val();
    if (checkBlank(seqno)) {
        return false;
    }

    var data = {
        "seqno" : seqno
    };
    var from = date;
    var to   = date;

    $("#date_from4").val(date);

    if (searchDvs === "order_info") {
        from = date;
        to   = date;
        data.cate_top = "";
        data.cate_mid = "";
        data.cate_bot = "";
        data.search_dvs     = "title";
        data.search_keyword = "";
    }

    var url = "/json/business/order_mng/load_order_info.php";
    data.from = from;
    data.to   = to;
    var callback = function(result) {
        $("#order_info_sum").html(result.thead);
        $("#order_info_list").html(result.tbody);
    };
    var popWidth  = $("#content2 .table_detail").width();
    var popHeight = $("#content2 .table_detail").height();
    var param = {
        "id"     : "order_info_mask",
        "width"  : popWidth,
        "height" : popHeight
    };
    ajaxCall(url, "json", data, callback);
};

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

//수입자료 리스트 불러오기
var loadIncomeList = function(pg) {

    //회원명 인풋창 비었을경우  회원일련번호 초기화
    if (checkBlank($("#office_nick").val())) {
        $("#member_seqno").val("");
        alert("검색창 팝업을 이용하시고 검색해주세요.");
        return false;
    }

    if (checkBlank($("#member_seqno").val()) && !checkBlank($("#office_nick").val())) {
        alert("검색창 팝업을 이용하시고 검색해주세요.");
        $("#office_nick").focus();
        return false;
    }

    $("#buf_date_from").val($("#date_from").val());
    $("#buf_date_to").val($("#date_to").val());

    showMask();
    var formData = new FormData($("#income_form")[0]);
    formData.append("page", pg);
    formData.append("list_num", list_num);

    $.ajax({

        type: "POST",
        data: formData,
        processData : false,
        contentType : false,
        url: "/ajax/calcul_mng/income_data/load_period_income_list.php",
        success: function(result) {
            var list = result.split('♪♭@');
            $("#list1").html(list[0]);
            $("#list2").html(list[1]);
            $('select[name=list_set]').val(list_num);
            $("#member_name").html(list[2]);
            $("#depo_price").html(list[3]);
            $("#result_price").html(list[4]);
            $("#depo_sum").html(list[5] + "<button class='btn_float_right btn_Turquoise01' style='width: 80px; height: 25px; margin-left: 10px; margin-top: -6px;'" + "onclick='depositListPopup();'>보기</button>");

            $("#tel_num").html(list[6]); // V
            $("#cell_num").html(list[7]); //V
            $("#fax_num").html(list[8]); // V
            $("#depo_finish_date").html(list[9]); // V
            $("#memo_list").html(list[10]);
            $("#period_pay_price").html(list[11]+ "<button class='btn_float_right btn_Turquoise01' style='width: 80px; height: 25px; margin-left: 10px; margin-top: -6px;'" + "onclick='payListPopup();'>보기</button>");
            $("#period_enuri").html(list[12]+ "<button class='btn_float_right btn_Turquoise01' style='width: 80px; height: 25px; margin-left: 10px; margin-top: -6px;'" + "onclick='enuriListPopup();'>보기</button>");
            $("#list_month").html(list[14]);
            $("#list_year").html(list[15]);
            hideMask();
        },
        error: getAjaxError
    });
}

//입금내역 상세보기
var showWithdraw = function(member_seqno,start_date, end_date) {
    if(start_date == null) start_date = $("#buf_date_from").val();
    if(end_date == null) end_date = $("#buf_date_to").val();
    var url = "/ajax/calcul_mng/income_data/load_withdraw_popup.php";
    var data = {
        "member_seqno" : member_seqno,
        "from_date" : start_date,
        "to_date" : end_date
    };

    var callback = function(result) {
        openRegiPopup(result, "1010");
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var removeMemo = function() {
    var arr_idx = [];
    $("input[name='member_memo']:checked").each(function () {
        var $obj = $(this);
        arr_idx.push($obj.attr('value'));
    });

    var url = "/ajax/calcul_mng/income_data/remove_member_memo.php";
    var data = {
        "idx" : arr_idx.join(',')
    };

    var callback = function(result) {
        if(result == "1")
            alert("삭제완료");
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var search_week = function() {
    var member_seqno = $("#member_seqno").val();
    if(member_seqno == "") {
        alert("회원을 먼저 검색해주세요.");
        return;
    }

    var formData = new FormData($("#income_form")[0]);
    formData.append("member_seqno", member_seqno);
    formData.append("date_from", $("#date_from2").val());
    formData.append("date_to", $("#date_to2").val());

    $.ajax({

        type: "POST",
        data: formData,
        processData : false,
        contentType : false,
        url: "/ajax/calcul_mng/income_data/load_period_income_list.php",
        success: function(result) {
            var list = result.split('♪♭@');
            $("#list1").html(list[0]);
            hideMask();
        },
        error: getAjaxError
    });
}

var search_months = function() {
    var member_seqno = $("#member_seqno").val();
    if(member_seqno == "") {
        alert("회원을 먼저 검색해주세요.");
        return;
    }

    var formData = new FormData();
    formData.append("member_seqno", member_seqno);
    formData.append("date_from", $("#date_from6").val());
    formData.append("date_to", $("#date_to6").val());

    $.ajax({

        type: "POST",
        data: formData,
        processData : false,
        contentType : false,
        url: "/ajax/calcul_mng/income_data/load_period_income_list.php",
        success: function(result) {
            var list = result.split('♪♭@');
            $("#list_month").html(list[14]);
            hideMask();
        },
        error: getAjaxError
    });
}

var search_years = function() {
    var member_seqno = $("#member_seqno").val();
    if(member_seqno == "") {
        alert("회원을 먼저 검색해주세요.");
        return;
    }

    var formData = new FormData();
    formData.append("member_seqno", member_seqno);
    formData.append("date_from", $("#date_from7").val());
    formData.append("date_to", $("#date_to7").val());

    $.ajax({

        type: "POST",
        data: formData,
        processData : false,
        contentType : false,
        url: "/ajax/calcul_mng/income_data/load_period_income_list.php",
        success: function(result) {
            var list = result.split('♪♭@');
            $("#list_year").html(list[15]);
            hideMask();
        },
        error: getAjaxError
    });
}

var search_day = function() {
    var member_seqno = $("#member_seqno").val();
    if(member_seqno == "") {
        alert("회원을 먼저 검색해주세요.");
        return;
    }

    var formData = new FormData($("#income_form")[0]);
    formData.append("member_seqno", member_seqno);
    formData.append("date_from", $("#date_from3").val());
    formData.append("date_to", $("#date_to3").val());

    $.ajax({

        type: "POST",
        data: formData,
        processData : false,
        contentType : false,
        url: "/ajax/calcul_mng/income_data/load_period_income_list.php",
        success: function(result) {
            var list = result.split('♪♭@');
            $("#list2").html(list[1]);
            hideMask();
        },
        error: getAjaxError
    });
}

var search_day_detail = function() {
    var member_seqno = $("#member_seqno").val();
    if(member_seqno == "") {
        alert("회원을 먼저 검색해주세요.");
        return;
    }

    var data = {
        "seqno" : member_seqno
    };
    var from = $("#date_from4").val();
    var to   = $("#date_from4").val();
    showMask();
    var url = "/json/business/order_mng/load_order_info.php";
    data.from = from;
    data.to   = to;
    var callback = function(result) {
        $("#order_info_sum").html(result.thead);
        $("#order_info_list").html(result.tbody);
        hideMask();
    };
    var popWidth  = $("#content2 .table_detail").width();
    var popHeight = $("#content2 .table_detail").height();
    var param = {
        "id"     : "order_info_mask",
        "width"  : popWidth,
        "height" : popHeight
    };
    ajaxCall(url, "json", data, callback);
}

var searchDeposit = function() {
    var member_seqno = $("#member_seqno").val();
    var url = "/ajax/calcul_mng/income_data/load_deposit_list.php";
    var data = {
        "from" : $("#deposit_from").val(),
        "to"   : $("#deposit_to").val(),
        "member_seqno" : member_seqno,
        "dvs" : $("#dvs2").val()
    };

    var callback1 = function(result) {
        $("#deposit_list").html(result);
       // hideLoadingMask();
    };

    showMask();
    ajaxCall(url, "html", data, callback1);
}

var searchEnuri = function() {
    var member_seqno = $("#member_seqno").val();
    var url = "/ajax/calcul_mng/income_data/load_enuri_list.php";
    var data = {
        "from" : $("#enuri_from").val(),
        "to"   : $("#enuri_to").val(),
        "member_seqno" : member_seqno
    };

    var callback1 = function(result) {
        $("#enuri_list").html(result);
        hideLoadingMask();
    };

    showMask();
    ajaxCall(url, "html", data, callback1);
}

var searchPayprice = function() {
    var member_seqno = $("#member_seqno").val();
    var url = "/ajax/calcul_mng/income_data/load_payprice_list.php";
    var data = {
        "from" : $("#payprice_from").val(),
        "to"   : $("#payprice_to").val(),
        "member_seqno" : member_seqno
    };

    var callback1 = function(result) {
        $("#payprice_list").html(result);
        hideLoadingMask();
    };

    showMask();
    ajaxCall(url, "html", data, callback1);
}

var depositListPopup = function() {
    var member_seqno = $("#member_seqno").val();
    if(member_seqno == "") {
        alert("회원을 먼저 검색해주세요.");
        return;
    }

    var url = "/ajax/calcul_mng/income_data/load_deposit_popup.php";
    var data = {
        "from" : $("#date_from").val(),
        "to"   : $("#date_to").val(),
        "member_seqno" : member_seqno,
        "member_name" : $("#office_nick").val()
    };

    var callback = function(result) {
        openRegiPopup(result, "1010");

        $(".deposit_from").datepicker({
            format         : "yyyy-mm-dd",
            autoclose      : true,
            todayBtn       : "linked",
            todayHighlight : true,
            language       : "kr"
        }).datepicker("setDate", $("#date_from").val());
        $(".deposit_to").datepicker({
            format         : "yyyy-mm-dd",
            autoclose      : true,
            todayBtn       : "linked",
            todayHighlight : true,
            language       : "kr"
        }).datepicker("setDate", $("#date_to").val());
        $("#deposit_date").datepicker({
            format         : "yyyy-mm-dd",
            autoclose      : true,
            todayBtn       : "linked",
            todayHighlight : true,
            language       : "kr"
        }).datepicker("setDate", $("#date_to").val());
        var date = $("#date_from").val().split('-');
        date = date[0] + "-" + date[1] + "-" + date[2];
        $("#deposit_from").val(date);
        $("#member_name").val($("#office_nick").val());
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var saveAdjust = function() {
    var member_seqno = $("#member_seqno").val();
    if(member_seqno == "") {
        alert("회원을 먼저 검색해주세요.");
        return;
    }

    var dvs = $("#dvs").val();
    if(dvs == 'bank') {
        if ($("#bank_price").val() == "") {
            alert("입금액을 입력해주세요.");
            return false;
        }

        if ($("#bank_name").val() == "") {
            alert("입금자명을 입력해주세요.");
            return false;
        }

        var bank_price = $("#bank_price").val();
        var bank_name = $("#bank_name").val();
        var bank_memo = $("#bank_cont").val();
        var deposit_date = $("#deposit_date").val();
        var deposit_dvs = $("#dvs").val();

        $.ajax({
            type: "POST",
            data: {
                "member_seqno" : member_seqno,
                "bank_price" : bank_price,
                "bank_name" : bank_name,
                "bank_memo" : bank_memo,
                "deal_date" : deposit_date,
                "deposit_dvs" : deposit_dvs
            },
            url: "/proc/calcul_mng/adjust_regi/regi_adjust_bank.php",
            success: function(result) {
                if($.trim(result) == "1") {
                    alert("저장했습니다.");
                    searchDeposit();
                }
            },
            error: getAjaxError
        });
    }

    if(dvs == 'cash') {
        if ($("#cash_price").val() == "") {
            alert("입금액을 입력해주세요.");
            return false;
        }

        if ($("#cash_name").val() == "") {
            alert("입금자명을 입력해주세요.");
            return false;
        }

        var deposit_date = $("#deposit_date").val();
        var deposit_dvs = $("#dvs").val();
        var cash_price = $("#cash_price").val();
        var cash_name = $("#cash_name").val();
        var cash_memo = $("#cash_memo").val();
        var cash_dvs = $("#cash_dvs").val();

        $.ajax({
            type: "POST",
            data: {
                "member_seqno" : member_seqno,
                "cash_price" : cash_price,
                "cash_name" : cash_name,
                "cash_memo" : cash_memo,
                "deal_date" : deposit_date,
                "deposit_dvs" : deposit_dvs,
                "cash_dvs" : cash_dvs
            },
            url: "/proc/calcul_mng/adjust_regi/regi_adjust_cash.php",
            success: function(result) {
                if($.trim(result) == "1") {
                    alert("저장했습니다.");
                    searchDeposit();
                }
            },
            error: getAjaxError
        });
    }

    if(dvs == 'card') {
        if ($("#card_price").val() == "") {
            alert("입금액을 입력해주세요.");
            return false;
        }

        if ($("#card_name").val() == "") {
            alert("입금자명을 입력해주세요.");
            return false;
        }

        var deposit_date = $("#deposit_date").val();
        var deposit_dvs = $("#dvs").val();
        var card_price = $("#card_price").val();
        var card_name = $("#card_name").val();
        var card_memo = $("#card_memo").val();
        var card_kind = $("#card_kind").val();
        var card_inst_months = $("#card_inst_months").val();
        var card_num = $("#card_num").val();
        var card_approve_num = $("#card_approve_num").val();
        var card_approve_date = $("#card_approve_date").val();
        var card_member = $("#card_member").val();

        $.ajax({
            type: "POST",
            data: {
                "member_seqno" : member_seqno,
                "card_price" : card_price,
                "card_name" : card_name,
                "card_memo" : card_memo,
                "deal_date" : deposit_date,
                "deposit_dvs" : deposit_dvs,
                "card_kind" : card_kind,
                "card_inst_months" : card_inst_months,
                "card_num" : card_num,
                "card_approve_num" : card_approve_num,
                "card_approve_date" : card_approve_date,
                "card_member" : card_member,

            },
            url: "/proc/calcul_mng/adjust_regi/regi_adjust_card.php",
            success: function(result) {
                if($.trim(result) == "1") {
                    alert("저장했습니다.");
                    searchDeposit();
                }
            },
            error: getAjaxError
        });
    }

    if(dvs == 'etc') {
        if ($("#etc_price").val() == "") {
            alert("입금액을 입력해주세요.");
            return false;
        }

        if ($("#etc_name").val() == "") {
            alert("입금자명을 입력해주세요.");
            return false;
        }

        var etc_price = $("#etc_price").val();
        var etc_memo = $("#etc_cont").val();
        var etc_dvs = $("#etc_dvs").val();
        var deposit_date = $("#deposit_date").val();
        var deposit_dvs = $("#dvs").val();

        $.ajax({
            type: "POST",
            data: {
                "member_seqno" : member_seqno,
                "etc_price" : etc_price,
                "etc_memo" : etc_memo,
                "etc_dvs" : etc_dvs,
                "deal_date" : deposit_date,
                "deposit_dvs" : deposit_dvs
            },
            url: "/proc/calcul_mng/adjust_regi/regi_adjust_etc.php",
            success: function(result) {
                if($.trim(result) == "1") {
                    alert("저장했습니다.");
                    searchDeposit();
                }
            },
            error: getAjaxError
        });
    }

}

var saveEnuri = function() {
    var member_seqno = $("#member_seqno").val();
    if(member_seqno == "") {
        alert("회원을 먼저 검색해주세요.");
        return;
    }

    var enuri_price = $("#enuri_price").val();
    var enuri_memo = $("#enuri_memo").val();
    var enuri_date = $("#enuri_date").val();
    var order_num = $("#order_num").val();

    $.ajax({
        type: "POST",
        data: {
            "member_seqno" : member_seqno,
            "enuri_price" : enuri_price,
            "enuri_memo" : enuri_memo,
            "deal_date" : enuri_date,
            "order_num" : order_num
        },
        url: "/proc/calcul_mng/adjust_regi/regi_enuri.php",
        success: function(result) {
            if($.trim(result) == "1") {
                alert("저장했습니다.");
                searchDeposit();
            }
        },
        error: getAjaxError
    });
}

var savePayprice = function() {
    var member_seqno = $("#member_seqno").val();
    if(member_seqno == "") {
        alert("회원을 먼저 검색해주세요.");
        return;
    }

    var payprice_price = $("#payprice_price").val();
    var payprice_memo = $("#payprice_memo").val();
    var payprice_date = $("#payprice_date").val();

    $.ajax({
        type: "POST",
        data: {
            "member_seqno" : member_seqno,
            "payprice_price" : payprice_price,
            "payprice_memo" : payprice_memo,
            "deal_date" : payprice_date
        },
        url: "/proc/calcul_mng/adjust_regi/regi_payprice.php",
        success: function(result) {
            if($.trim(result) == "1") {
                alert("저장했습니다.");
                searchPayprice();
            }
        },
        error: getAjaxError
    });
}

var payListPopup = function() {
    var member_seqno = $("#member_seqno").val();
    if(member_seqno == "") {
        alert("회원을 먼저 검색해주세요.");
        return;
    }

    var url = "/ajax/calcul_mng/income_data/load_payprice_popup.php";
    var data = {
        "from" : $("#date_from").val(),
        "to"   : $("#date_to").val(),
        "member_seqno" : member_seqno
    };

    var callback = function(result) {
        openRegiPopup(result, "1010");

        $(".payprice_from").datepicker({
            format         : "yyyy-mm-dd",
            autoclose      : true,
            todayBtn       : "linked",
            todayHighlight : true,
            language       : "kr"
        }).datepicker("setDate", $("#date_from").val());
        $(".payprice_to").datepicker({
            format         : "yyyy-mm-dd",
            autoclose      : true,
            todayBtn       : "linked",
            todayHighlight : true,
            language       : "kr"
        }).datepicker("setDate", $("#date_to").val());
        $("#payprice_date").datepicker({
            format         : "yyyy-mm-dd",
            autoclose      : true,
            todayBtn       : "linked",
            todayHighlight : true,
            language       : "kr"
        }).datepicker("setDate", $("#date_to").val());
        var date = $("#date_from").val().split('-');
        date = date[0] + "-" + date[1] + "-" + date[2];
        $("#payprice_from").val(date);
        $("#member_name").val($("#office_nick").val());
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var enuriListPopup = function(order_num) {
    var member_seqno = $("#member_seqno").val();
    if(member_seqno == "") {
        alert("회원을 먼저 검색해주세요.");
        return;
    }

    var url = "/ajax/calcul_mng/income_data/load_enuri_popup.php";
    var data = {
        "from" : $("#date_from").val(),
        "to"   : $("#date_to").val(),
        "member_seqno" : member_seqno
    };

    var callback = function(result) {
        openRegiPopup(result, "1010");

        $(".enuri_from").datepicker({
            format         : "yyyy-mm-dd",
            autoclose      : true,
            todayBtn       : "linked",
            todayHighlight : true,
            language       : "kr"
        }).datepicker("setDate", $("#date_from").val());
        $(".enuri_to").datepicker({
            format         : "yyyy-mm-dd",
            autoclose      : true,
            todayBtn       : "linked",
            todayHighlight : true,
            language       : "kr"
        }).datepicker("setDate", $("#date_to").val());
        $("#enuri_date").datepicker({
            format         : "yyyy-mm-dd",
            autoclose      : true,
            todayBtn       : "linked",
            todayHighlight : true,
            language       : "kr"
        }).datepicker("setDate", $("#date_to").val());
        var date = $("#date_from").val().split('-');
        date = date[0] + "-" + date[1] + "-" + date[2];
        $("#enuri_from").val(date);
        $("#order_num").val(order_num);
        $("#member_name").val($("#office_nick").val());
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var set_enuri = function(order_num) {
    enuriListPopup(order_num);
}

var search_memo = function() {
    var member_seqno = $("#member_seqno").val();
    if(member_seqno == "") {
        alert("회원을 먼저 검색해주세요.");
        return;
    }

    var url = "/ajax/calcul_mng/income_data/load_memo_list.php";
    var data = {
        "date_from" : $("#date_from5").val(),
        "date_to"   : $("#date_to5").val(),
        "member_seqno" : member_seqno
    };

    var callback = function(result) {
        $("#memo_list").html(result);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var change_dvs = function(dvs) {
    var old_form = $("#active_form").val();
    $("#" + old_form + "_form").hide();

    $("#active_form").val(dvs);
    $("#" + dvs + "_form").show();
}

var change_dvs2 = function(dvs) {
    searchDeposit();
}

//회원 상세보기
var showDetail = function(seqno) {
    $("#seqno").val(seqno);
    var f = document.frm;
    window.open("", "POP")
    f.action = "/member/popup/pop_member_detail.html";
    //f.action = "/member/member_common_popup.html";
    f.target = "POP";
    f.method = "POST";
    f.submit();
    return false;
}

var tabView = function(dvs) {

    tabInit();
    $("#paper_op_" + dvs).show();
}

var tabInit = function() {

    $("#paper_op_1").hide();
    $("#paper_op_2").hide();
    $("#paper_op_3").hide();

}

var openMemoPopup = function() {
    var member_seqno = $("#member_seqno").val();
    if(member_seqno == "") {
        alert("회원을 먼저 검색해주세요.");
        return;
    }

    var url = "/ajax/calcul_mng/income_data/load_memo_popup.php";
    var data = {
        "member_seqno" : member_seqno
    };

    var callback = function(result) {
        openRegiPopup(result, "1010");

        $(".memo_date").datepicker({
            format         : "yyyy-mm-dd",
            autoclose      : true,
            todayBtn       : "linked",
            todayHighlight : true,
            language       : "kr"
        }).datepicker("setDate", '0');

        var date = $("#memo_date").val().split('-');
        date = date[0] + "-" + date[1] + "-" + date[2];
        $("#memo_date").val(date);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var changeDvs = function(value) {
    $(".clsss").hide();
    if(value == "전체") {
        $(".clsss").show();
    } else if(value == "가상계좌") {
        $(".cls_account").show();
    } else if(value == "현금") {
        $(".cls_cash").show();
    } else if(value == "카드") {
        $(".cls_card").show();
    } else if(value == "기타") {
        $(".cls_etc").show();
    }
}

var writeMemo = function(member_seqno) {
    var url = "/ajax/calcul_mng/income_data/write_memo.php";
    var data = {
        "member_seqno" : member_seqno,
        "member_memo" : $("#member_memo").val(),
        "memo_date" : $("#memo_date").val()
    };

    var callback = function(result) {
        hideRegiPopup();
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var showTotalByDay = function(member_seqno) {
    var url = "/ajax/calcul_mng/income_data/load_totalday_popup.php";
    var data = {
        "member_seqno" : member_seqno,
        "from_date" : $("#buf_date_from").val(),
        "to_date" : $("#buf_date_to").val()
    };

    var callback = function(result) {
        openRegiPopup(result, "1010");
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}


//보여 주는 페이지 갯수 설정
var showPageSetting = function(val) {

    list_num = val;
    loadIncomeList(1);
}

//선택 조건으로 검색(페이징 클릭)
var searchResult = function(pg) {

    page = pg;
    loadIncomeList(page);
}

