$(document).ready(function() {
    $(".datepicker_input").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '-0');

    cndSearch.exec(30, 1);

    $("#cb_chooseorder").change(function() {
        if($(this).is(':checked')) {
            $("input:checkbox[name='cb_order']").prop("checked", true);
        } else {
            $("input:checkbox[name='cb_order']").prop("checked", false);
        }
    });

    $("#dlvr_way").change(function() {
        if($(this).val() == '02') { //직배일경우
            var str_option = "<option>전체</option>"
            str_option += "<option value='A1'>서울배송1</option>"
            str_option += "<option value='A2'>서울배송2</option>"
            str_option += "<option value='A3'>서울배송3</option>"
            str_option += "<option value='A4'>서울배송4</option>"
            str_option += "<option value='A5'>서울배송5</option>"
            str_option += "<option value='A6'>서울배송6</option>"
            str_option += "<option value='A7'>서울배송7</option>"
            str_option += "<option value='A8'>서울배송8</option>"
            str_option += "<option value='A9'>서울배송9</option>"
            str_option += "<option value='A10'>서울배송10</option>"
            str_option += "<option value='A11'>서울배송11</option>"
            str_option += "<option value='A12'>서울배송12</option>"
            str_option += "<option value='B1'>직배1</option>"
            str_option += "<option value='B2'>직배2</option>"
            str_option += "<option value='B3'>직배3</option>"
            str_option += "<option value='B4'>직배4</option>"
            str_option += "<option value='B5'>직배5</option>"
            str_option += "<option value='B6'>직배6</option>"
            str_option += "<option value='B7'>직배7</option>"
            str_option += "<option value='B8'>직배8</option>"
            str_option += "<option value='B9'>직배9</option>"
            str_option += "<option value='B10'>직배10</option>"
            str_option += "<option value='B11'>직배11</option>"
            str_option += "<option value='B12'>직배12</option>"
            $("#dlvr_way_detail").html(str_option);
        } else {
            var str_option = "<option>전체</option>"
            str_option += "<option value='인현'>인현</option>"
            str_option += "<option value='성수'>성수</option>"
            $("#dlvr_way_detail").html(str_option);
        }
    });
});

//보여줄 페이지 수
var showPage = 30;

/**
 * @brief 선택조건으로 검색 클릭시
 */
var cndSearch = {
    "exec"       : function(showPage, page) {
        var url = "/ajax/manufacture/storage_mng/release_list.php";
        var blank = "<tr><td colspan=\"13\">검색 된 내용이 없습니다.</td></tr>";
        var data = {
            "extnl_etprs_seqno" : $("#extnl_etprs_seqno").val(),
            "state"             : $("#state").val(),
            "after_yn"             : $(":input:radio[name=after_yn]:checked").val(),
            "theday_yn"             : $(":input:radio[name=theday_yn]:checked").val(),
            "detail_info"       : $("#detail_info").val(),
            "product_sort"             : $("#product_sort").val(),
            "category"          : $("#category").val(),
            "keyword"             : $("#keyword").val(),
            "search_cnd"        : $("#search_cnd").val(),
            "member_name"        : $("#member_name").val(),
            "title"        : $("#title").val(),
            "date_from"         : $("#date_from").val(),
            "date_to"           : $("#date_to").val(),
            "dlvr_way"           : $("#dlvr_way").val(),
            "dlvr_way_detail"           : $("#dlvr_way_detail").val(),
            "state"           : $("#state").val(),
        };
        var callback = function(result) {
            var rs = result.split("♪");
            if (rs[0].trim() == "") {
                $("#output_list").html(blank);
                return false;
            }

            var date_from = $("#date_from").val();
            var date_to = $("#date_to").val();
            if(date_from != date_to) {
                date_from += " ~ " + date_to;
            }

            date_from = "조회일자 : " + date_from;

            var title = date_from;
            title += "  /  배송 : " + $("#dlvr_way > option:selected").html();
            var title_detail = $("#dlvr_way_detail > option:selected").html();
            if(title_detail != '-') {
                title += '(' + title_detail + ')';
            }
            $("#list1_title").html(title);
            $("#list").html(rs[0]);
            $("#page").html(rs[1]);
        };

        data.showPage      = showPage;
        data.page          = page;

        showMask();
        ajaxCall(url, "html", data, callback);
    }
};

var showDlvrDetail = function(seqno) {
    var url = "/ajax/manufacture/release_list/load_release_detail_html.php";
    var data = {
        "order_common_seqno" : seqno
    };

    var callback = function(result) {

        var PrintPage = window.open("_blank");
        PrintPage.document.open();
        PrintPage.document.write(result);
        PrintPage.document.close();

        setTimeout(function(){ PrintPage.print();PrintPage.close();  }, 300);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}


var printArea = function(print_list) {
    const setting = "width=890, height=1000";
    const objWin = window.open('', 'print', setting);
    objWin.document.open();
    objWin.document.write('<html><head><title>분석 레포트 </title>');
    objWin.document.write('<link rel="stylesheet" type="text/css" href="/design_template/css/print.css"/>');
    //objWin.document.write('<link rel="stylesheet" type="text/css" href="/design_template/css/common.css"/>');
    //objWin.document.write('<link rel="stylesheet" type="text/css" href="/design_template/css/basic_manager.css"/>');
    //objWin.document.write('<link rel="stylesheet" type="text/css" href="/design_template/css/basic_manager.css"/>');
    objWin.document.write('</head><body>');
    objWin.document.write($('#' + print_list).html());
    objWin.document.write('</body></html>');
    objWin.focus();
    objWin.document.close();

    setTimeout(function(){ objWin.print();  }, 300);
    //objWin.close();
}

var enterCheck = function(event) {
    if(event.keyCode == 13) {
        cndSearch.exec(30, 1);
    }
}

//검색
var searchProcess = function() {
    cndSearch.exec(30, 1);
}


//보여 주는 페이지 갯수 설정
var showPageSetting = function(val, el) {
    showPage = val;
    cndSearch.exec(val, 1);
}

//상품리스트 페이지 이동
var movePage = function(val, el) {
    cndSearch.exec(el, val);
}

//검색어 검색 엔터
var searchKey = function(event, val, el) {

    if (event.keyCode == 13) {
        cndSearch.exec(el, showPage, 1);
    }
}

//검색어 검색 버튼
var searchText = function(el) {
    cndSearch.exec(el, showPage, 1);
}


var print_dlvr = function() {
    showMask();

    $.ajax({
        type     : "POST",
        url      : "/proc/produce/delivery_socket/invoice_socket.php",
        dataType : "html",
        success  : function(result) {
            hideMask();
            alert(result);

            if(result == "SUCESS")
            {
                alert('성공');
            } else if (result == "FAILED") {
                alert('실패');
            } else if (result == "DBCON_LOST") {
                alert('DB연결 끊김');
            } else if (result == "INV_FAILED") {
                alert('송장번호 가져오기 실패 (송장번호 부족)');
            } else if (result == "STR_FAILED") {
                alert('송신 배송정보 가져오기 실패 (송신 정보없음)');
            } else if (result == "SRV_FAILED") {
                alert('수신 배송정보 가져오기 실패 (수신 정보없음)');
            } else if (result == "SHIP_FAILED") {
                alert('송수신 배송정보가 없거나 잘못 되었습니다.');
            } else if (result == "ORC_NOT_CONN") {
                alert('오라클 DB 연결이 잘못 되었습니다.');
            } else if (result == "OC_NONE_PRINT") {
                alert('출력 할 송장 데이터가 존재하지 않습니다.');
            } else if (result == "ORC_QUERY_FAILED") {
                alert('오라클 DB 쿼리가 잘못 되었습니다.');
            } else if (result == "ORC_ADDR_FAILD_20000") {
                alert('입력파라미터 중 코드값이 잘못되어 있을 발생하는 오류입니다.');
            } else if (result == "ORC_ADDR_FAILD_20001") {
                alert('CJ 대한통운에 등록되지 않은 고객ID 입니다.');
            } else if (result == "ORC_ADDR_FAILD_20002") {
                alert('입력하신 주소에 대한 분석에 실패한 경우입니다.');
            } else if (result == "ORC_ADDR_FAILD_20003") {
                alert('입력하신 주소에 대해 집배권역(집화 및 배달대리점)을 설정값을 찾지 못한 경우입니다.');
            } else if (result == "ORC_ADDR_FAILD_20004") {
                alert('집배권역에서 반환된 접소정보가 폐점이나 사용중지 된 점소일 경우 반환되는 경우입니다.');
            } else if (result == "ORC_ADDR_FAILD_20005") {
                alert('배달이나 집화를 처리해 주어야 하는 사원이 설정되지 않은 경우 반환되는 값입니다.');
            } else if (result == "ORC_ADDR_FAILD_20006") {
                alert('허브터미널에서 분류를 하기 위한 도착지 코드 추출에 실패한 경우 반횐되는 값입니다.');
            } else if (result == "ORC_ADDR_FAILD_20007") {
                alert('서버터미널에서 분류를 하기 위한 분류주소 추출에 실패한 경우 반횐되는 값입니다.');
            } else if (result == "UPD_FAILED") {
                alert('송장번호 업데이트 실패');
            } else if (result == "SK_CRT_FAILED") {
                alert('소켓생성 실패');
            } else if (result == "SK_CON_FAILED") {
                alert('소켓연결 실패');
            } else {
                alert('예치기지 못한 오류가 발생했습니다.');
            }
        },
        error: getAjaxError
    });
};

var getAjaxError = function(request,status,error) {
    alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
    console.log("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
};

var changeState = function(order_num) {
    var url = "/ajax/produce/process_mng/delivery_waitin_stock_force.php";
    var data = {
        "order_detail_num"       : order_num
    };
    var callback = function(result) {
        //var rs = result.split("♪");
        if(result == "1") {
            searchProcess();
        } else {
            alert("입고처리 실패");
        }
    };

    ajaxCall(url, "html", data, callback);
}

var change_state_checked = function() {
    var seq = '';
    $("input[name='cb_order']:checked").each(function(i) {
        seq += $(this).attr("val") + '|';
    });

    seq = seq.slice(0, -1);

    var url = "/ajax/produce/process_mng/delivery_waitin_stock_force.php";
    var data = {
        "order_detail_num"       : seq
    };
    var callback = function(result) {
        //var rs = result.split("♪");
        if(result == "1") {
            searchProcess();
        } else {
            alert("입고처리 실패");
        }
    };

    ajaxCall(url, "html", data, callback);
};

//선택목록 출고중으로
var change_state_all = function() {
    var url = "/ajax/produce/process_mng/delivery_waitin_stock_all.php";
    var data = {
        "after_yn"             : $("#hidden_after").val(),
        "theday_yn"             : $("#hidden_theday").val(),
        "product_sort"             : $("#hidden_sort").val(),
        "keyword"             : $("#hidden_keyword").val(),
        "search_cnd"        : $("#hidden_search_cnd").val(),
        "date_from"         : $("#hidden_date_from").val(),
        "date_to"           : $("#hidden_date_to").val(),
        "time_from"         : $("#hidden_time_from").val(),
        "time_to"           : $("#hidden_time_to").val(),
        "dlvr_way"           : $("#hidden_dlvrway").val()
    };
    var callback = function(result) {
        if(result == "1") {
            searchProcess();
        } else {
            alert("출고처리 실패");
        }
    };

    ajaxCall(url, "html", data, callback);
};

function leadingZeros(n, digits) {
    var zero = '';
    n = n.toString();

    if (n.length < digits) {
        for (var i = 0; i < digits - n.length; i++)
            zero += '0';
    }
    return zero + n;
};