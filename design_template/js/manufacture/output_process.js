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
    searchProcess();
    $("#typset_num").focus();
});

//선택 조건으로 검색
var searchProcess = function(typset_num) {

    var url = "/ajax/manufacture/output_list/load_output_process.php";
    var blank = "<tr><td colspan=\"9\">검색 된 내용이 없습니다.</td></tr>";
    var data = {
        "state"             : $("#state").val(),
        "preset_cate"       : $("#preset_cate").val(),
        "extnl_etprs_seqno" : $("#extnl_etprs_seqno").val(),
        "date_cnd"          : $("#date_cnd").val(),
        "date_from"         : $("#basic_from").val(),
        "date_to"           : $("#basic_to").val()
    };
    var callback = function(result) {
        var rs = result.split("♪");
        $("#list1").html(rs[0]);
        $("#list2").html(rs[1]);
        $("#list3").html(rs[2]);
        $("#allCheck").prop("checked", false);
    };

    if(typset_num != null)
        data.typset_num      = typset_num;

    showMask();
    ajaxCall(url, "html", data, callback);
}

var getStart = function(seqno, state) {
    var url = "/proc/manufacture/output_list/modi_output_process_multi_finish.php";
    var data = {
        "seqno" : seqno,
        "state" : state
    };

    var callback = function(result) {
        if (result == 1) {
            //alert("완료 하였습니다.");
            hideRegiPopup();
            searchProcess();
        } else {
            alert("완료를 실패 하였습니다. \n 관리자에게 문의 하십시오.");
            showBgMask();
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//보여 주는 페이지 갯수 설정
var showPageSetting = function(val) {

    showPage = val;
    searchProcess();
}

//상품리스트 페이지 이동
var movePage = function(val) {
    searchProcess();
}

//인쇄 작업금액
var getWorkPrice = function() {

    var url = "/ajax/manufacture/print_list/load_print_work_price.php";
    var data = {
        "extnl_brand_seqno" : $("#extnl_brand_seqno").val(),
        "print_name"        : $("#print_name").val(),
        "amt"               : $("#amt").val().replace(/,/gi, ""),
        "amt_unit"          : $("#amt_unit").val(),
        "size"              : $("#size_val").val()
    };

    var callback = function(result) {
        var price_val = result + " 원";
        $("#work_price_val").html(price_val);
        $("#work_price").val(result);
        showBgMask();
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//인쇄 상세보기
var openDetailView = function(seqno) {
    var url = "/ajax/manufacture/print_list/load_print_detail_popup.php";
    var data = {
        "seqno" : seqno
    };

    var callback = function(result) {
        openRegiPopup(result, "1010");
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//인쇄 이미지보기
var openImgView = function(seqno) {
    var url = "/ajax/manufacture/print_list/load_print_img_popup.php";
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

//인쇄 보류
var getHolding = function(seqno) {

    /*
    if (checkBlank($("#worker_memo").val())) {
        alert("작업 메모를 선택 또는 입력해주세요.");
	    $("#memo").focus();
        return false;
    }
    */

    var url = "/proc/manufacture/print_list/modi_print_process_holding.php";
    var data = {
        "seqno"             : seqno,
        "worker_memo"       : $("#worker_memo").val(),
        "ink_C"             : $("#ink_C").val(),
        "ink_M"             : $("#ink_M").val(),
        "ink_Y"             : $("#ink_Y").val(),
        "ink_K"             : $("#ink_K").val(),
        "affil"             : $("#affil").val(),
        "subpaper"          : $("#subpaper").val(),
        "size"              : $("#size_val").val(),
        "adjust_price"      : $("#adjust_price").val().replace(/,/gi, ""),
        "work_price"        : $("#work_price").val().replace(/,/gi, ""),
        "extnl_etprs_seqno" : $("#extnl_etprs_seqno").val(),
        "extnl_brand_seqno" : $("#extnl_brand_seqno").val()
    };

    var callback = function(result) {
        if (result == 1) {
            alert("보류 하였습니다.");
            hideRegiPopup();
            searchProcess();
        } else {
            alert("보류을 실패 하였습니다. \n 관리자에게 문의 하십시오.");
            showBgMask();
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//인쇄 생산공정 완료
var getFinish = function(seqno) {

    var url = "/proc/manufacture/print_list/modi_cutting_process_finish.php";
    var data = {
        "seqno" : seqno
    };

    var callback = function(result) {
        if (result == 1) {
            alert("완료 하였습니다.");
            hideRegiPopup();
            searchProcess();
        } else {
            alert("완료를 실패 하였습니다. \n 관리자에게 문의 하십시오.");
            showBgMask();
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//인쇄 생산공정 다중 완료
var multiFinish = function() {

    if (checkBlank(getselectedNo())) {
        alert("선택한 항목이 없습니다.");
        return false;
    }

    var url = "/proc/manufacture/cutting_list/modi_cutting_process_multi_finish.php";
    var data = {
        "seqno" : getselectedNo()
    };

    var callback = function(result) {
        if (result == 1) {
            alert("완료 하였습니다.");
            hideRegiPopup();
            searchProcess();
        } else {
            alert("완료를 실패 하였습니다. \n 관리자에게 문의 하십시오.");
            showBgMask();
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//출력 생산공정 재작업
var getRestart = function(seqno) {

    var url = "/proc/manufacture/print_list/modi_print_process_restart.php";
    var data = {
        "seqno"             : seqno,
        "worker_memo"       : $("#worker_memo").val(),
        "ink_C"             : $("#ink_C").val(),
        "ink_M"             : $("#ink_M").val(),
        "ink_Y"             : $("#ink_Y").val(),
        "ink_K"             : $("#ink_K").val(),
        "affil"             : $("#affil").val(),
        "subpaper"          : $("#subpaper").val(),
        "size"              : $("#size_val").val(),
        "adjust_price"      : $("#adjust_price").val().replace(/,/gi, ""),
        "work_price"        : $("#work_price").val().replace(/,/gi, ""),
        "extnl_etprs_seqno" : $("#extnl_etprs_seqno").val(),
        "extnl_brand_seqno" : $("#extnl_brand_seqno").val()
    };

    var callback = function(result) {
        if (result == 1) {
            alert("재시작을 하였습니다.");
            hideRegiPopup();
            searchProcess();
        } else {
            alert("재시작을 실패 하였습니다. \n 관리자에게 문의 하십시오.");
            showBgMask();
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

/*
//출력 생산공정 취소
var getCancel = function(seqno) {

    var url = "/proc/produce/process_mng/modi_output_process_cancel.php";
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
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}
*/

//종이입고(사용)
var paperStor = function(seqno) {

    var url = "/proc/manufacture/print_list/modi_paper_stor.php";
    var data = {
        "seqno" : seqno
    };

    var callback = function(result) {
        showBgMask();
        if (result == 1) {
            $("#paper_use_btn").hide();
            $("#paper_stor_yn").val("Y");
            alert("종이를 입고하였습니다.");
        } else {
            alert("종이입고를 실패 하였습니다. \n 관리자에게 문의 하십시오.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//인쇄 사이즈 가져옴
var getSize = function() {

    var url = "/ajax/manufacture/print_list/load_print_size.php";
    var data = {
        "affil"    : $("#affil").val(),
        "subpaper" : $("#subpaper").val()
    };

    var callback = function(result) {
        var size = result;
        $("#size").html("(" + size + ")");
        $("#size_val").val(size);
        showBgMask();
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//바코드 처리 페이지로 이동
var goBarcode = function() {
    window.open("/manufacture/print_process.html", "_blank");
}

var getNowState = function(typset_num) {
    var url = "/ajax/manufacture/common/load_typset_info.php";
    var data = {
        "typset_num" : typset_num
    };

    var callback = function(result) {
        var url2 = "/proc/manufacture/output_list/modi_output_process_multi_finish.php";
        var callback2 = function(result2) {
            setTimeout(function(){ searchProcess(); }, 500);
            $("#typset_num").focus();
        };

        var sheet_typset_seqno = result.split('|')[0];
        var state = result.split('|')[1];
        if(state == "2220") {
            ajaxCall(url2, "html", {"seqno": sheet_typset_seqno, "state": "2320"}, callback2);
        }
        else if(state == "2230") {
            ajaxCall(url2, "html", {"seqno": sheet_typset_seqno, "state": "2320"}, callback2);
        }
        else
            searchProcess(typset_num);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var changeState = function(event, typset_num) {
    if (event.keyCode == 9 || event.keyCode == 13) {
        var change_state = change = $(":input:radio[name=changestate_yn]:checked").val();
        if(change_state == "Y") {
            getNowState(typset_num);
            //ajaxCall(url, "html", {"typset_num": typset_num}, callback);
        }
        else
            searchProcess(typset_num);

        $("#typset_num").val("");
        event.preventDefault();
    }
}