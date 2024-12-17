$(document).ready(function() {
    //dateSet('0');
    $(".datepicker_input").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0').attr('readonly', 'readonly');

    searchProcess(30, 1);
    $("#order_num").focus();

    $("#order_num").on("input", function() {
        delay(function(){
            $("#order_num").val("");
        }, 100 );
    });
});

//보여줄 페이지 수
var showPage = 30;

//선택 조건으로 검색
var searchProcess = function(showPage, page) {

    var url = "/ajax/manufacture/release_process/load_release_list.php";
    var blank = "<tr><td colspan=\"10\">검색 된 내용이 없습니다.</td></tr>";
    var data = {
        "state"             : $("#state").val(),
        "cate_sortcode"     : $("#cate_sortcode").val(),
        "extnl_etprs_seqno" : $("#extnl_etprs_seqno").val(),
        "date_cnd"          : $("#date_cnd").val(),
        "date_from"         : $("#basic_from").val(),
        "date_to"           : $("#basic_to").val(),
        "after"           : $("#after").val(),
    };
    var callback = function(result) {
        var rs = result.split("♪");
        $("#list1").html(rs[0]);
        $("#list2").html(rs[1]);
        $("#list3").html(rs[2]);
        $("#allCheck").prop("checked", false);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//보여 주는 페이지 갯수 설정
var showPageSetting = function(val) {

    showPage = val;
    searchProcess(showPage, 1);
}

//상품리스트 페이지 이동
var movePage = function(val) {
    searchProcess(showPage, val);
}

//후공정 작업금액
var getWorkPrice = function() {

    var url = "/ajax/produce/process_mng/load_after_work_price.php";
    var data = {
        "extnl_brand_seqno" : $("#extnl_brand_seqno").val(),
        "after_name"        : $("#after_name").val(),
        "depth1"            : $("#depth1").val(),
        "depth2"            : $("#depth2").val(),
        "depth3"            : $("#depth3").val(),
        "amt"               : $("#amt").val().replace(/,/gi, ""),
        "amt_unit"          : $("#amt_unit").val()
    };

    var callback = function(result) {
        var val = result + " 원";
        $("#work_price_val").html(val);
        $("#work_price").val(result);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//후공정 상세보기
var openDetailView = function(seqno) {
    var url = "/ajax/manufacture/after_list/load_after_detail_popup.php";
    var data = {
        "seqno" : seqno
    };

    var callback = function(result) {
        openRegiPopup(result, "1010");
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//후공정 이미지보기
var openImgView = function(seqno) {
    var url = "/ajax/manufacture/after_list/load_after_img_popup.php";
    var data = {
        "seqno" : seqno
    };

    var callback = function(result) {
        openRegiPopup(result, "1250");
        $(document).ready(function() {
            $('#image-gallery').lightSlider({
                gallery:true,
                item:1,
                thumbItem:5,
                vertical:true, //세로
                verticalHeight:350, //세로
                slideMargin: 0,
                // speed:500,
                // auto:true,
                loop:true,
                onSliderLoad: function() {
                    $('#image-gallery').removeClass('cS-hidden');
                }
            });
        });
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var showDlvrWith = function(bun_dlvr_order_num, member_seqno) {
    window.open("/order/order_common_mng.html?val_search_dvs=bun_dlvr_order_num&val_bun_dlvr_order_num=" + bun_dlvr_order_num + "&val_member_seqno=" + member_seqno, '_blank')
}

var showDetail = function(order_num) {
    window.open("/order/order_common_mng.html?search_dvs=order_num&search_keyword=" + order_num, '_blank')
}

//후공정 생산공정시작
var getStart = function(seqno) {

    var url = "/proc/manufacture/after_list/modi_after_process_start.php";
    var data = {
        "seqno"             : seqno,
        "worker_memo"       : $("#worker_memo").val(),
        "adjust_price"      : $("#adjust_price").val().replace(/,/gi, ""),
        "work_price"        : $("#work_price").val().replace(/,/gi, ""),
        "extnl_etprs_seqno" : $("#extnl_etprs_seqno").val()
    };

    var callback = function(result) {
        if (result == 1) {
            alert("시작 하였습니다.");
            hideRegiPopup();
            searchProcess(showPage, 1);
        } else {
            alert("시작을 실패 하였습니다. \n 관리자에게 문의 하십시오.");
            showBgMask();
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//후공정 보류
var getHolding = function(seqno) {

    var url = "/proc/manufacture/after_list/modi_after_process_holding.php";
    var data = {
        "seqno"             : seqno,
        "worker_memo"       : $("#worker_memo").val(),
        "adjust_price"      : $("#adjust_price").val().replace(/,/gi, ""),
        "work_price"        : $("#work_price").val().replace(/,/gi, ""),
        "extnl_etprs_seqno" : $("#extnl_etprs_seqno").val()
    };

    var callback = function(result) {
        if (result == 1) {
            alert("보류 하였습니다.");
            hideRegiPopup();
            searchProcess(showPage, 1);
        } else {
            alert("보류를 실패 하였습니다. \n 관리자에게 문의 하십시오.");
            showBgMask();
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//후공정 생산공정완료
var getFinish = function(seqno) {

    var url = "/proc/manufacture/after_list/modi_after_process_finish.php";
    var data = {
        "seqno" : seqno
    };

    var callback = function(result) {
        if (result == 1) {
            alert("완료 하였습니다.");
            hideRegiPopup();
            searchProcess(showPage, 1);
        } else {
            alert("완료를 실패 하였습니다. \n 관리자에게 문의 하십시오.");
            showBgMask();
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//후공정 생산공정 다중 완료
var multiFinish = function() {

    if (checkBlank(getselectedNo())) {
        alert("선택한 항목이 없습니다.");
        return false;
    }

    var url = "/proc/manufacture/after_list/modi_after_process_multi_finish.php";
    var data = {
        "seqno" : getselectedNo()
    };

    var callback = function(result) {
        if (result == 1) {
            alert("완료 하였습니다.");
            hideRegiPopup();
            searchProcess(showPage, 1);
        } else {
            alert("완료를 실패 하였습니다. \n 관리자에게 문의 하십시오.");
            showBgMask();
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}
/*
var changeState = function(event, order_num) {
    var url = "/ajax/manufacture/release_process/modi_release_process_finish.php";
    var data = {
        "order_num"       : order_num,
        "state"       : state,
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
*/
var changeState = function(event, order_num) {
    if (event.keyCode == 9 || event.keyCode == 13) {
        var change_state = change = $(":input:radio[name=changestate_yn]:checked").val();
        if(change_state == "Y") {
            getNowState(order_num);
            //ajaxCall(url, "html", {"typset_num": typset_num}, callback);
        }
        else
            searchProcess(order_num);

        $("#order_num").val("");
        event.preventDefault();
    }
}

var getNowState = function(order_num) {
    var url = "/ajax/manufacture/common/load_order_info.php";
    var data = {
        "order_num" : order_num
    };

    var callback = function(result) {
        var url2 = "/ajax/manufacture/release_process/modi_release_process_finish.php";
        var callback2 = function(result2) {
            setTimeout(function(){ searchProcess(); }, 500);
            $("#order_num").focus();
        };

        var order_common_seqno = result.split('|')[0];
        var state = result.split('|')[1];
        if(state == "") {
            alert("잘못된 주문번호입니다.");
            searchProcess();
        }
        else if(state == "3120" || state == "3220") {
            ajaxCall(url2, "html", {"order_num": order_common_seqno, "state": "3330"}, callback2);
        }
        else if(state == "3330") {
            ajaxCall(url2, "html", {"order_num": order_common_seqno, "state": "3380"}, callback2);
        }
        else
            searchProcess(typset_num);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//후공정 생산공정 재작업
var getRestart = function(seqno) {

    var url = "/proc/produce/process_mng/modi_after_process_restart.php";
    var data = {
        "seqno"             : seqno,
        "worker_memo"       : $("#worker_memo").val(),
        "adjust_price"      : $("#adjust_price").val().replace(/,/gi, ""),
        "work_price"        : $("#work_price").val().replace(/,/gi, ""),
        "extnl_etprs_seqno" : $("#extnl_etprs_seqno").val()
    };

    var callback = function(result) {
        if (result == 1) {
            alert("재시작을 하였습니다.");
            hideRegiPopup();
            searchProcess(showPage, 1);
        } else {
            alert("재시작을 실패 하였습니다. \n 관리자에게 문의 하십시오.");
            showBgMask();
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//생산공정취소 
var getCancel = function(seqno) {

    var url = "/proc/manufacture/after_list/modi_after_process_cancel.php";
    var data = {
        "seqno" : seqno
    };

    var callback = function(result) {
        if (result == 1) {
            alert("작업취소 하였습니다.");
            hideRegiPopup();
            searchProcess(showPage, 1);
        } else {
            alert("작업취소를 실패 하였습니다. \n 관리자에게 문의 하십시오.");
            showBgMask();
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}
