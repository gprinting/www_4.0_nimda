$(document).ready(function() {
    dateSet('0');
    searchProcess(30, 1);
});

//보여줄 페이지 수
var showPage = 30;

//선택 조건으로 검색
var searchProcess = function(showPage, page) {

    var url = "/ajax/manufacture/cooperator_list/load_cooperator_list.php";
    var blank = "<tr><td colspan=\"13\">검색 된 내용이 없습니다.</td></tr>";
    var data = {
        "outsource_etprs_cate_name" : $("#outsource_etprs_cate_name").val(),
//    	"extnl_etprs_seqno"         : $("#extnl_etprs_seqno").val(),
        "search_cnd"                : $("#search_cnd").val(),
        "search_txt"                : $("#search_txt").val(),
        "oper_sys"                  : $("#oper_sys").val(),
        "order_state"               : $("#order_state").val(),
        "date_cnd"                  : $("#date_cnd").val(),
        "date_from"                 : $("#date_from").val(),
        "date_to"                   : $("#date_to").val(),
        "time_from"                 : $("#time_from").val(),
        "time_to"                   : $("#time_to").val()
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

//협력업체 설정
var changeCateName = function(val) {

    var url = "/ajax/manufacture/cooperator_list/load_cooperator_option.php";
    var data = {
        "outsource_etprs_cate_name" : val
    };
    var callback = function(result) {
        $("#extnl_etprs_seqno").html(result);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//원본 다운로드
var oriFileDown = function(seqno) {
    location.href="/common/order_file_down.php?seqno=" + seqno;
}

//PDF 다운로드
var pdfFileDown = function(seqno) {
    location.href="/common/order_detail_count_file_down.php?seqno=" + seqno;
}

//다중 PDF 다운로드
var pdfMultiFileDown = function() {

    var seqno = getselectedNo();
    location.href="/common/order_detail_count_file_multi_down.php?seqno=" + seqno;
    searchProcess(showPage, 1);
}

//입고처리
var storProcess = function(seqno) {

    if (!confirm("입고처리 하시겠습니까?")){
        return false;
    }

    var url = "/proc/manufacture/cooperator_list/modi_stor_process.php";
    var data = { 
        "seqno" : seqno
    };

    var callback = function(result) {
        if (result == 1) {
            alert("완료 하였습니다.");
            searchProcess(showPage, 1);
        } else {
            alert("완료를 실패 하였습니다. \n 관리자에게 문의 하십시오.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//다중 입고처리
var storMultiProcess = function() {

    if (!confirm("입고처리 하시겠습니까?")){
        return false;
    }

    if (checkBlank(getselectedNo())) {
        alert("선택한 항목이 없습니다.");
        return false;
    }

    var url = "/proc/manufacture/cooperator_list/modi_stor_multi_process.php";
    var data = { 
        "seqno" : getselectedNo() 
    };

    var callback = function(result) {
        if (result == 1) {
            alert("완료 하였습니다.");
            searchProcess(showPage, 1);
        } else {
            alert("완료를 실패 하였습니다. \n 관리자에게 문의 하십시오.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//배송정보 입력
var regiDeliveryInfo = function(seqno) {
    var url = "/ajax/manufacture/cooperator_list/load_delivery_info_regi_popup.php";
    var data = { 
        "seqno" : seqno 
    };

    var callback = function(result) {
        openRegiPopup(result, "600");
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//배송정보 수정
var modiDeliveryinfo = function() {

    if (checkBlank($("#invo_cpn").val())) {
        alert("배송회사를 입력해주세요.");
	$("#invo_cpn").focus();
	return false;
    }

    if (checkBlank($("#invo_num").val())) {
        alert("송장번호를 입력해주세요.");
	$("#invo_num").focus();
	return false;
    }

    var url = "/proc/manufacture/cooperator_list/modi_delivery_info.php";
    var data = { 
        "order_common_seqno" : $("#order_common_senqo").val(),
        "order_detail_seqno" : $("#order_detail_seqno").val(),
	"invo_cpn"           : $("#invo_cpn").val(),
	"invo_num"           : $("#invo_num").val()
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

//배송처리
var deliveryProcess = function() {

    if (checkBlank($("#invo_cpn").val())) {
        alert("배송회사를 입력해주세요.");
	$("#invo_cpn").focus();
	return false;
    }

    if (checkBlank($("#invo_num").val())) {
        alert("송장번호를 입력해주세요.");
	$("#invo_num").focus();
	return false;
    }

    var url = "/proc/manufacture/cooperator_list/modi_delivery_process.php";
    var data = { 
        "order_common_seqno" : $("#order_common_senqo").val(),
        "order_detail_seqno" : $("#order_detail_seqno").val(),
	"invo_cpn"           : $("#invo_cpn").val(),
	"invo_num"           : $("#invo_num").val()
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
