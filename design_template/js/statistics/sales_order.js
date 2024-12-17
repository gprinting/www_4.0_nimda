//보여줄 페이지 수
var showPage = "";

$(document).ready(function() {
//    dateSet('0');
    loadDeparInfo();
    memberListAjaxCall(30, 1);
});

//회원 리스트 호출
var memberListAjaxCall = function(sPage, page) {

    if (checkBlank($("#office_nick").val())) {
        $("#member_seqno").val("");
    }

    if (checkBlank($("#member_seqno").val()) && !checkBlank($("#office_nick").val())) {
        alert("검색창 팝업을 이용하시고 검색해주세요.");
        $("#office_nick").focus();
        return false;
    }

    var data = {
        "sell_site"    : $("#sell_site").val(),
        "depar_code"   : $("#depar_code").val(),
        "member_seqno" : $("#member_seqno").val(),
        "search_cnd"   : $("#search_cnd").val(),
        "date_from"    : $("#date_from").val(),
        "date_to"      : $("#date_to").val(),
        "time_from"    : $("#time_from").val(),
        "time_to"      : $("#time_to").val(),
        "grade"        : $("#grade").val(),
        "member_typ"   : $("#member_typ").val(),
        "showPage"     : sPage,
        "page"         : page
    };

    var url = "/ajax/member/member_common_list/load_member_common_list.php";

    var blank = "<tr><td colspan=\"10\">검색 된 내용이 없습니다.</td></tr>";

    showMask();
    $.ajax({
        type: "POST",
        data: data,
        url: url,
        success: function(result) {

            hideMask();
            var rs = result.split("♪");

            if (rs[0].trim() == "") {
                $("#member_list").html(blank);
            } else {
                $("#member_list").html(rs[0]);
            }

            $("#member_page").html(rs[1]);
        }
    });
}

//회원 검색
var searchMember = function() {
    memberListAjaxCall(30, 1);
}

//페이지 이동
var movePage = function(val) {
    memberListAjaxCall(showPage, val);
}

//보여줄 페이지 수 설정
var showPageSetting = function(val) {
    showPage = val;
    memberListAjaxCall(val, 1);
}
