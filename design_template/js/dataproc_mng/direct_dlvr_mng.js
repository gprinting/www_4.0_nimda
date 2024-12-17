

$(document).ready(function() {
    $(".datepicker_input").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0');
    loadDirectDlvrDetail(1);
});

var page = "1"; //페이지
var list_num = "15"; //리스트 갯수
var tab_id = "paper"; // 탭 id
var search_type = "1";
var ajax_data = {
    "list_num"     : "",
    "page"         : "1",
    "search_str"   : ""
}

/**********************************************************************
 * 공통 부분 *
 **********************************************************************/


// 탭 클릭시
var tabCtrl = function(el, page, type) {

    //팝업 보이기
    //showMask();

    tab_id = el; //탭 선택

    //탭 이동시 혹은 첫 검색시 리스트 카운트 초기화
    if (type == "3") {
        list_num = "15";
        $('select[name=list_set]').val(15);

        search_type = "1";
        ajax_data.search_str = "";
        resetSearchStr();

    } else if (type == "2") {

        ajax_data.search_str = $("#search_" + tab_id).val();
        search_type = "2";

    }

    //페이지 list 갯수
    ajax_data.list_num = list_num;
    //페이지
    ajax_data.page = page;

    if (el == "paper") {

        //loadPaperDscrList(ajax_data);

    } else if (el == "after") {

        //loadAfterDscrList(ajax_data);

    } else {

        //loadOptDscrList(ajax_data);

    }
}

var resetSearchStr = function() {

    $("#search_paper").val('');
    $("#search_after").val('');
    $("#search_opt").val('');
}

//보여 주는 페이지 갯수 설정
var showPageSetting = function(val) {

    list_num = val;
    tabCtrl(tab_id, '1', search_type);
}

//선택 조건으로 검색(페이징 클릭)
var searchResult = function(page) {

    tabCtrl(tab_id, page, search_type)

}

//엔터 쳤을때 검색
var enterCheck = function(event) {

    if(event.keyCode == 13) {
        tabCtrl(tab_id, '1','2');
    }

}

var popDlvrDscr = function(seq) {

    $.ajax({
        type: "POST",
        url: "/ajax/dataproc_mng/direct_dlvr_mng/load_input_dlvr_popup.php",
        success: function(result) {
            openRegiPopup(result, 420);
        },
        error: getAjaxError
    });
}

var loadDirectDlvrDetail = function(seq) {
    $.ajax({
        type: "POST",
        data: {
            "direct_dlvr_info_seqno" : seq
        },
        url: "/ajax/dataproc_mng/direct_dlvr_mng/load_direct_dlvr_detail.php",
        success: function(result) {
            var obj = JSON.parse(result);
            $("#mng").val(obj.mng);
            $("#vehi_num").val(obj.vehi_num);
            $("#car_number").val(obj.car_number);
            $("#insert_date").datepicker("setDate", obj.insert_datetime);
            $("#is_using").val(obj.is_using);
            $("#dlvr_area").val(obj.dlvr_area);
            if(obj.method == "dynamic") {
                $("input:radio[name='method']:radio[value='static']").prop("checked", false);
                $("input:radio[name='method']:radio[value='dynamic']").prop("checked", true);
                $("#cost_static_area").css('display', "none");
                $("#cost_dynamic_area").css('display', "grid");

                for(var i = 0; i < 8; i++) {
                    $("#cost_dynamic" + (i + 1) + "_area").css('display','none');
                    $("#cost_dynamic" + (i + 1)).css('display','none');
                }

                var cost = JSON.parse(obj.cost_by_case.replace(/&quot;/g, '"'));
                for(var i = 0; i < cost.length; i++) {
                    $("#cost_dynamic" + (i + 1) + "_area").val(cost[i][0]);
                    $("#cost_dynamic" + (i + 1)).val(cost[i][1]);
                    $("#cost_dynamic" + (i + 1)).css('display','inline');
                    $("#cost_dynamic" + (i + 1) + "_area").css('display','inline');
                }
            } else {
                $("input:radio[name='method']:radio[value='static']").prop("checked", true);
                $("input:radio[name='method']:radio[value='dynamic']").prop("checked", false);
                $("#cost_static_area").css('display', "grid");
                $("#cost_dynamic_area").css('display', "none");

                var cost = JSON.parse(obj.cost_by_case.replace(/&quot;/g, '"'))[0];
                $("#cost_static").val(cost);
            }
        },
        error: getAjaxError
    });
}

var clickRadio = function(val) {
    if(val == "static") {
        $("#cost_static_area").css('display', "grid");
        $("#cost_dynamic_area").css('display', "none");
    } else {
        $("#cost_static_area").css('display', "none");
        $("#cost_dynamic_area").css('display', "grid");
    }
}

var addCase = function() {
    for(var i = 0; i < 8; i++) {
        var asd = $("#cost_dynamic" + (i + 1)).css('display');
        if(asd == "none") {
            $("#cost_dynamic" + (i + 1)).css('display','inline');
            $("#cost_dynamic" + (i + 1) + "_area").css('display','inline');
            break;
        }
    }
}

var apply = function() {
    showMask();
    var j = 0;
    var method = $(':radio[name="method"]:checked').val();
    if(method == "dynamic") {
        for (var i = 0; i < 8; i++) {
            var asd = $("#cost_dynamic" + (i + 1)).css('display');
            var value = $("#cost_dynamic" + (i + 1) + "_area").val();
            if (asd == "none" || value == "") {
                break;
            }
            j++;
        }
        var obj = new Array(j);

        for (var i = 0; i < j; i++) {
            var asd = $("#cost_dynamic" + (i + 1)).css('display');
            if (asd == "none") {
                break;
            }

            var cost_dynamic = $("#cost_dynamic" + (i + 1)).val();
            var cost_dynamic_area = $("#cost_dynamic" + (i + 1) + "_area").val();

            obj[i] = new Array(2);
            obj[i][0] = cost_dynamic_area;
            obj[i][1] = cost_dynamic;
        }
    } else {
        var obj = new Array(1);
        var cost_static = $("#cost_static").val();
        obj[0] = cost_static;
    }

    $.ajax({
        type: "POST",
        data: {
            "mng" : $("#mng").val(),
            "vehi_num" : $("#vehi_num").val(),
            "car_number" : $("#car_number").val(),
            "is_using" : $("#is_using").val(),
            "dlvr_area" : $("#dlvr_area").val(),
            "method" : $(':radio[name="method"]:checked').val(),
            "cost_by_case" : JSON.stringify(obj),
            "direct_dlvr_info_seqno" : $("#cate_top").val().replace(/&quot;/g, '"')
        },
        url: "/ajax/dataproc_mng/direct_dlvr_mng/input_direct_dlvr_detail.php",
        success: function(result) {
            //location.reload();
            alert("저장완료");
            hideMask();
        },
        error: getAjaxError
    });
}


var searchProcess = function(showPage, page) {
    var url = "/ajax/dataproc_mng/direct_dlvr_mng/load_direct_dlvr_list.php";
    var blank = "<tr><td colspan=\"9\">검색 된 내용이 없습니다.</td></tr>";
    var data = {
        "preset_cate" : $("#preset_cate").val(),
        "typset_num"  : $("#typset_num").val(),
        "date_from"   : $("#basic_from").val(),
        "date_to"     : $("#basic_to").val()
    };
    var callback = function(result) {
        var rs = result.split("♪");
        if (rs[0].trim() == "") {
            $("#list").html(blank);
            return false;
        }
        $("#paper_list").html(rs[0]);
        $("#page").html(rs[1]);
        $("#allCheck").prop("checked", false);
    };

    data.showPage      = showPage;
    data.page          = page;

    showMask();
    ajaxCall(url, "html", data, callback);
}

var saveDirectCar = function() {
    var url = "/ajax/dataproc_mng/direct_dlvr_mng/input_direct_dlvr.php";
    var data = {
        "vehi_num" : $("#vehi_num").val(),
        "mng"  : $("#mng").val(),
        "dlvr_area"   : $("#dlvr_area").val(),
        "car_number"     : $("#car_number").val()
    };
    var callback = function(result) {
        hideRegiPopup();
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}