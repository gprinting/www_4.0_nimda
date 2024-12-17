$(document).ready(function() {
    $(".datepicker_input").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '-3').attr('readonly', 'readonly');
    setDateVal('basic', 'd', -3,  0, '일주일', false);
    cndSearch.exec(30, 1);
    $("#cb_chooseorder").change(function() {
        if($(this).is(':checked')) {
            $("input:checkbox[name='cb_order']").prop("checked", true);
        } else {
            $("input:checkbox[name='cb_order']").prop("checked", false);
        }
    });

    $("#dlvr_way").change(function() {
        if($(this).val == '02') { //직배일경우

        } else {
            $(this).html();
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
        var url = "/ajax/manufacture/storage_mng/delivery_list.php";
        var blank = "<tr><td colspan=\"13\">검색 된 내용이 없습니다.</td></tr>";
        var data = {
            "state"             : $("#state").val(),
            "after_yn"             : $(":input:radio[name=after_yn]:checked").val(),
            "dlvr_dvs"             : $("#dlvr_dvs").val(),
            "dlvr_way_detail"   : $("#dlvr_way_detail").val(),
            "member_name"             : $("#member_name").val(),
            "title"             : $("#title").val(),
            "theday_yn"             : $(":input:radio[name=theday_yn]:checked").val(),
            "search_cnd"        : $("#search_cnd").val(),
            "search_txt"        : $("#output_search").val(),
            "date_from"         : $("#basic_from").val(),
            "date_to"           : $("#basic_to").val(),
            //"time_from"         : leadingZeros($("#time_from").val(), 2),
            //"time_to"           : leadingZeros($("#time_to").val(), 2),
            "dlvr_way"           : $("#dlvr_way").val()
        };
        var callback = function(result) {
            var rs = result.split("♪");
            if (rs[0].trim() == "") {
                $("#output_list").html(blank);
                return false;
            }
            $("#list").html(rs[0]);
            $("#page").html(rs[1]);
        };

        data.showPage      = showPage;
        data.page          = page;

        showMask();
        ajaxCall(url, "html", data, callback);
    }
};

/**
 * @brief 엑셀다운로드 함수
 *
 * @param dvs = 다운로드할 엑셀파일 구분 > 쓸지 말지 고민좀 해보자
 */
var downloadFile = function() {

    var url = "/ajax/manufacture/storage_mng/";
    var data = null;
    // 데이터는 cndSearch의 데이터를 사용
    data = cndSearch.data;
    // 추가로 다운로드 url을 붙임
    url += "down_excel_delivery.php";

    $.ajax({
        type     : "POST",
        url      : url,
        dataType : "text",
        data     : {
            "state"             : $("#state").val(),
            "after_yn"             : $(":input:radio[name=after_yn]:checked").val(),
            "theday_yn"             : $(":input:radio[name=theday_yn]:checked").val(),
            "dlvr_dvs"             : $("#dlvr_dvs").val(),
            "dlvr_way_detail"   : $("#dlvr_way_detail").val(),
            "member_name"             : $("#member_name").val(),
            "title"             : $("#title").val(),
            "search_cnd"        : $("#search_cnd").val(),
            "search_txt"        : $("#output_search").val(),
            "date_from"         : $("#basic_from").val(),
            "date_to"           : $("#basic_to").val(),
            //"time_from"         : leadingZeros($("#time_from").val(), 2),
            //"time_to"           : leadingZeros($("#time_to").val(), 2),
            "dlvr_way"           : $("#dlvr_way").val()
        },
        success  : function(result) {
            hideMask();
            if (result === "FALSE") {
                alert("엑셀파일 생성에 실패했습니다.");
            } else if (result === "NOT_INFO") {
                alert("엑셀로 생성할 정보가 존재하지 않습니다.");
            } else {
                var nameArr  = result.split('!');
                var downUrl  = "/common/excel_file_down.php?name=" + nameArr[1];
                downUrl += "&from=" + $("#from").val();
                downUrl += "&to=" + $("#to").val();
                downUrl += "&file_dvs=" + nameArr[0];

                $("#file_ifr").attr("src", downUrl);

            }
        },
        error    : function(request,status,error) {
            hideMask();
            alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
            console.log("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
        }
    });

    showMask();
};

var openBunDlvr = function(seqno) {
    var url = "/ajax/order/order_common_mng/load_bun_dlvr_html.php";
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

var showDlvrWith = function(bun_dlvr_order_num, member_seqno) {
    window.open("/order/order_common_mng.html?val_search_dvs=bun_dlvr_order_num&val_bun_dlvr_order_num=" + bun_dlvr_order_num + "&val_member_seqno=" + member_seqno, '_blank')
}

var getCategory = function() {
    if($("#cate_bot").val() != "") {
        return $("#cate_bot").val();
    }

    if($("#cate_mid").val() != "") {
        return $("#cate_mid").val() + "%";
    }

    if($("#cate_top").val() != "") {
        return $("#cate_top").val() + "%";
    }

    return "";
}

var click_ordernum = function(order_num) {
    window.open("/order/order_common_mng.html?search_dvs=order_num&search_keyword=" + order_num, '_blank')
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

var savePrdtInfo = function() {

    //카테고리 소분류가 비었을때
    if ($("#upload_btn1").val() == "") {
        alert("먼저 파일을 선택해주세요");
        return false;
    }

    if($("#upload_btn1").val().split('.').pop() != "xls"
        && $("#upload_btn1").val().split('.').pop() != "xlsx")
    {
        alert("엑셀파일을 업로드해야합니다");
        return false;
    }

    var formData = new FormData($("#prdt_form")[0]);

    showMask();
    $.ajax({
        type: "POST",
        data: formData,
        processData : false,
        contentType : false,
        url: "/ajax/manufacture/storage_mng/upload_deliveryinfo.php",
        success: function(result) {
            if($.trim(result) == "1") {
                hideRegiPopup();
                alert("저장했습니다.");
                cndSearch.exec(30, 1);
            } else {
                alert("저장에 실패했습니다.");
            }
            hideMask();
        },
        error: getAjaxError
    });
}