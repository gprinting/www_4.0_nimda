$(document).ready(function() {
    //dateSet('0');
    $(".datepicker_input").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '-7').attr('readonly', 'readonly');
    setDateVal('basic', 'd', -7,  0, '일주일', false);
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

    var url = "/ajax/manufacture/after_list/load_after_list.php";
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

var changeState = function(event, order_num) {
    if (event.keyCode == 9 || event.keyCode == 13) {
        var defined_order_num = order_num;
        var barcode_start_char = "";
        if(defined_order_num.length > 16) {
            defined_order_num = order_num.substring(defined_order_num.length - 16);
            barcode_start_char = order_num.substring(0, order_num.length - 16);
        }

        var change_state = change = $(":input:radio[name=changestate_yn]:checked").val();
        if(change_state == "Y") {
            multiFinish(defined_order_num, '2780');
            //getStartImpositionByTypsetNum(defined_order_num, barcode_start_char);
            //ajaxCall(url, "html", {"typset_num": typset_num}, callback);
        }
        else
            searchProcess(order_num);

        $("#order_num").val("");
        event.preventDefault();
    }
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
var multiFinish = function(ordernum, state) {
    var url = "/proc/manufacture/after_list/modi_after_process_multi_finish.php";
    var data = {
        "ordernum" : ordernum,
        "state" : state,
        "after" : $("#after").val()
    };

    var callback = function(result) {
        if (result == 1) {
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
