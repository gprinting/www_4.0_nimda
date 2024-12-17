$(document).ready(function() {
    //dateSet('0'); 
    $(".datepicker_input").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0').attr('readonly', 'readonly');
    setDateVal('basic', 'd', -1,  0, '어제', false);
    searchProcessByImposition(30, 1);
    $("#typset_num").focus();

    $("#typset_num").on("input", function() {
        delay(function(){
            $("#typset_num").val("");
        }, 100 );
    });
});

//보여줄 페이지 수
var showPage = 30;
	
//선택 조건으로 검색
var searchProcess = function(showPage, page) {

    var url = "/ajax/manufacture/basic_after_list/load_basic_after_list.php";
    var blank = "<tr><td colspan=\"10\">검색 된 내용이 없습니다.</td></tr>";
    var data = {
        "state"       : $("#state").val(),
        "preset_cate" : $("#preset_cate").val(),
        "typset_num"  : $("#typset_num").val(),
        "date_cnd"    : $("#date_cnd").val(),
        "date_from"   : $("#basic_from").val(),
        "date_to"     : $("#basic_to").val(),
        "detail"      : $("#after").val()
    };
    var callback = function(result) {
        var rs = result.split("♪");
        if (rs[0].trim() == "") {
            $("#list").html(blank);
            return false;
        }
        $("#list").html(rs[0]);
        $("#page").html(rs[1]);
	$("#allCheck").prop("checked", false);
    };

    data.showPage      = showPage;
    data.page          = page;

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
 
    var url = "/ajax/manufacture/basic_after_list/load_basic_after_work_price.php";
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
	showBgMask();
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//후공정 상세보기
var openDetailView = function(seqno) {
    var url = "/ajax/manufacture/basic_after_list/load_basic_after_detail_popup.php";
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
    var url = "/ajax/manufacture/basic_after_list/load_basic_after_img_popup.php";
    var data = { 
        "seqno" : seqno 
    };

    var callback = function(result) {
        openRegiPopup(result, "1010", "727");
        $(document).ready(function() {
            $('#image-gallery').lightSlider({
                gallery:true,
                item:1,
                thumbItem:7,
                vertical:true, //세로
                verticalHeight:664.55, //세로
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

    var url = "/proc/manufacture/basic_after_list/modi_basic_after_process_start.php";
    var data = { 
        "seqno"             : seqno,
        "worker_memo"       : $("#worker_memo").val(),
        "adjust_price"      : $("#adjust_price").val().replace(/,/gi, ""),
        "work_price"        : $("#work_price").val().replace(/,/gi, ""),
        "extnl_brand_seqno" : $("#extnl_brand_seqno").val()
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

    /*
    if (checkBlank($("#worker_memo").val())) {
        alert("작업 메모를 선택 또는 입력해주세요.");
	    $("#memo").focus();
        return false;
    }
    */

    var url = "/proc/manufacture/basic_after_list/modi_basic_after_process_holding.php";
    var data = { 
        "seqno"             : seqno,
        "worker_memo"       : $("#worker_memo").val(),
        "adjust_price"      : $("#adjust_price").val().replace(/,/gi, ""),
        "work_price"        : $("#work_price").val().replace(/,/gi, ""),
        "extnl_brand_seqno" : $("#extnl_brand_seqno").val()
    };

    var callback = function(result) {
        if (result == 1) {
            alert("보류 하였습니다.");
	    hideRegiPopup();
            searchProcess(showPage, 1);
        } else {
            alert("보류을 실패 하였습니다. \n 관리자에게 문의 하십시오.");
	    showBgMask();
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//후공정 생산공정 완료
var getFinish = function(seqno) {

    var url = "/proc/manufacture/basic_after_list/modi_basic_after_process_finish.php";
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

    var url = "/proc/manufacture/basic_after_list/modi_basic_after_process_multi_finish.php";
    var data = { 
        "seqno" : getselectedNo() 
    };

    console.log(data);

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

//후공정 생산공정 재작업
var getRestart = function(seqno) {

    var url = "/proc/manufacture/basic_after_list/modi_basic_after_process_restart.php";
    var data = { 
        "seqno"             : seqno,
        "worker_memo"       : $("#worker_memo").val(),
        "adjust_price"      : $("#adjust_price").val().replace(/,/gi, ""),
        "work_price"        : $("#work_price").val().replace(/,/gi, ""),
        "extnl_brand_seqno" : $("#extnl_brand_seqno").val()
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

var getNowState = function(typset_num) {
    var url = "/ajax/manufacture/common/load_typset_info.php";
    var data = {
        "typset_num" : typset_num
    };

    var callback = function(result) {
        var state = result.split('|')[1];
        if(state == "") {
            alert("잘못된 판번호입니다.");
            searchProcess();
        }
        else
            searchProcess(typset_num);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var getStartImposition = function(seqno, barcode_start_char = '') {
    var url = "/proc/manufacture/basic_after_list/modi_basic_after_process_multi_finish.php";
    var data = {
        "seqno" : seqno,
        "detail" : $("#after").val()
    };

    var callback = function(result) {
        if (result == 1) {
            alert("완료 하였습니다.");
            hideRegiPopup();
            searchProcessByImposition();
        } else {
            alert("완료를 실패 하였습니다. \n 관리자에게 문의 하십시오.");
            showBgMask();
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var getStartImpositionByTypsetNum = function(typset_num, barcode_start_char = '') {
    var url = "/proc/manufacture/basic_after_list/modi_basic_after_process_multi_finish.php";
    var data = {
        "typset_num" : typset_num,
        "detail" : $("#after").val(),
        "barcode_start_char" : barcode_start_char
    };

    var callback = function(result) {
        if (result == 1) {
            alert("완료 하였습니다.");
            hideRegiPopup();
            searchProcessByImposition();
        } else {
            alert("완료를 실패 하였습니다. \n 관리자에게 문의 하십시오.");
            showBgMask();
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var changeState = function(event, typset_num) {
    if (event.keyCode == 9 || event.keyCode == 13) {
        var defined_typset_num = typset_num;
        var barcode_start_char = "";
        if(defined_typset_num.length > 16) {
            defined_typset_num = typset_num.substring(defined_typset_num.length - 16);
            barcode_start_char = typset_num.substring(0, typset_num.length - 16);
        }

        var change_state = change = $(":input:radio[name=changestate_yn]:checked").val();
        if(change_state == "Y") {
            getStartImpositionByTypsetNum(defined_typset_num, barcode_start_char);
            //ajaxCall(url, "html", {"typset_num": typset_num}, callback);
        }
        else
            searchProcess(typset_num);

        $("#typset_num").val("");
        event.preventDefault();
    }
}

var delay = (function(){
    var timer = 0;
    return function(callback, ms){
        clearTimeout (timer);
        timer = setTimeout(callback, ms);
    };
})();