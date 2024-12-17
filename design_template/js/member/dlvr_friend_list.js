/*
 *
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016/01/21 왕초롱 생성
 * 2016/03/24 임종건 함수 수정 및 재개발
 * 2016/03/24 임종건 수정 주소API작업
 *============================================================================
 *
 */

$(document).ready(function() {
    searchDlvrFriend('All');
});

var search_nick = "";
var main_seq = "";
var member_seq = "";
var sub_sub_seqno = ""; 
var main_member_seqno = "";

//배송친구 검색
var searchDlvrFriend = function(type) {

    var data = {
        "search": $("#search_addr").val(),
        "type"  : $('input[name=dlvr_type]:checked').val()
    }

    if (type == "All") {
        dlvrFriendList(data);
        dlvrMainReqList(data);
        dlvrSubReqList(data);
    } else if (type == "Aprvl") {
        dlvrFriendList(data);
    }
}

//배송친구 승인 리스트
var dlvrFriendList = function(data) {

    var url = "/ajax/member/dlvr_friend_list/load_dlvr_friend.php";
    var blank = "<tr><td colspan='6'>검색된 내용이 없습니다.</td></tr>";
    var callback = function(result) {
        if ($.trim(result) == "") {
            $("#friend_list").html(blank);
        } else {
            $("#friend_list").html(result);
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//배송친구 신청 리스트 - Main
var dlvrMainReqList = function(data) {

    var url = "/ajax/member/dlvr_friend_list/load_dlvr_main_req.php";
    var blank = "<tr><td colspan='6'>검색된 내용이 없습니다.</td></tr>";
    var callback = function(result) {
        if ($.trim(result) == "") {
            $("#main_req_list").html(blank);
        } else {
            $("#main_req_list").html(result);
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//배송친구 신청 리스트 - Sub
var dlvrSubReqList = function(data) {

    var url = "/ajax/member/dlvr_friend_list/load_dlvr_sub_req.php";
    var blank = "<tr><td colspan='9'>검색된 내용이 없습니다.</td></tr>";
    var callback = function(result) {
        if ($.trim(result) == "") {
            $("#sub_req_list").html(blank);
        } else {
            $("#sub_req_list").html(result);
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//컬럼별 sort
var sortList = function(val, type, el) {

    var flag = "";

    if ($(el).children().hasClass("fa-sort-desc")) {
        sortInit();
        $(el).children().addClass("fa-sort-asc");
        $(el).children().removeClass("fa-sort");
        flag = "ASC";
    } else {
        sortInit();
        $(el).children().addClass("fa-sort-desc");
        $(el).children().removeClass("fa-sort");
        flag = "DESC";
    }

    var sort = val + "/" + flag;

    if (type == "main") {
        mainAprvlList(sort);
    } else {
        subMainAprvlList(sort);
    }
}

/**********************************************************************
                         * 메인 부분 *
 **********************************************************************/

//배송친구 메인 리스트
var mainAprvlList = function(sort) {

    var sort_col = "";
    var sort_type = "";

    if (sort != "") {

	var sort_info = sort.split('/');
	sort_col = sort_info[0];
	sort_type = sort_info[1];
     
    }
    var url = "/ajax/member/dlvr_friend_list/load_main_aprvl_list.php";
    var blank = "<tr><td colspan='3'>검색된 내용이 없습니다.</td></tr>";
    var data = {
	"sort_col" : sort_col,
        "sort_type": sort_type
    }
    var callback = function(result) {
	showBgMask();
        if ($.trim(result) == "") {
            $("#main_list").html(blank);
        } else {
            $("#main_list").html(result);
        }
    };
    
    ajaxCall(url, "html", data, callback);
}

//배송친구 메인 요청 리스트
var mainReqList = function(seqno, member_seqno) {

    main_seq = seqno;
    member_seq = member_seqno;
    var url = "/ajax/member/dlvr_friend_list/load_main_list.php";
    var blank = "<tr><td colspan='3'>검색된 내용이 없습니다.</td></tr>";
    var data = {
	"seqno" : main_seq
    }
    var callback = function(result) {
        openRegiPopup($("#main_pop").html(), 700);
        var main_info = result.split('♪@♭');
        if ($.trim(main_info[0]) == "") {
            $("#main_list").html(blank);
        } else {
            $('#main_list').html(main_info[0]);
        }
        var member_info = main_info[1].split('♩§¶');
        $('#main_regi_date').val(member_info[0]);
        $('#main_name').val(member_info[1]);
        $('#main_addr').val(member_info[2]);
        $('#main_tel').val(member_info[3]);
    };
    showMask();
    ajaxCall(url, "html", data, callback);
}

//메인 업체 승인
var mainReqAprvl = function() {

    var url = "/proc/member/dlvr_friend_list/update_main_aprvl.php";
    var data = {
	"seqno" : main_seq,
	"member_seqno" : member_seq,
	"type"  : "2"
    }
    var callback = function(result) {
        hideRegiPopup();
        searchDlvrFriend("All");
        alert(result);
    };
    showMask();
    ajaxCall(url, "html", data, callback);
}

//메인 업체 거절
var mainReqReject = function() {

    var url = "/proc/member/dlvr_friend_list/update_main_aprvl.php";
    var data = {
	"seqno" : main_seq,
	"type"  : "3"
    }
    var callback = function(result) {
        hideRegiPopup();
        searchDlvrFriend("All");
        alert(result);
    };
    showMask();
    ajaxCall(url, "html", data, callback);
}

/**********************************************************************
                         * 서브 부분 *
 **********************************************************************/

//서브 팝업에 있는 배송친구 메인 리스트
var subMainAprvlList = function(sort) {

    var sort_col = "";
    var sort_type = "";

    if (sort != "") {
	var sort_info = sort.split('/');
	sort_col = sort_info[0];
	sort_type = sort_info[1];
    }

    var url = "/ajax/member/dlvr_friend_list/load_sub_main_aprvl_list.php";
    var blank = "<tr><td colspan='4'>검색된 내용이 없습니다.</td></tr>";
    var data = {
	"sort_col"          : sort_col,
        "sort_type"         : sort_type,
	"search_nick"       : search_nick,
        "main_member_seqno" : main_member_seqno
    }
    var callback = function(result) {
	showBgMask();
        if ($.trim(result) == "") {
            $("#sub_main_list").html(blank);
        } else {
            $('#sub_main_list').html(result);
        }
    };
 
    showMask();
    ajaxCall(url, "html", data, callback);
}

//배송친구 서브 요청 리스트
var subReqList = function(mem_seqno, main, sub) {

    sub_sub_seqno = sub;
    main_member_seqno = mem_seqno;

    var url = "/ajax/member/dlvr_friend_list/load_sub_list.php";
    var blank = "<tr><td colspan='4'>검색된 내용이 없습니다.</td></tr>";
    var data = {
        "main_member_seqno" : main_member_seqno
    }
    var callback = function(result) {
        openRegiPopup($("#sub_pop").html(), 950);
        if ($.trim(result) == "") {
            $("#sub_main_list").html(blank);
        } else {
            $('#sub_main_list').html(result);
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//서브 업체 승인
var subReqAprvl = function() {

    var url = "/proc/member/dlvr_friend_list/update_sub_aprvl.php";
    var data = {
        "member_seqno" : $('input[name=main_radio]:checked').val(),
        "sub_seqno"    : sub_sub_seqno,
        "type"         : "2"
    }
    var callback = function(result) {
        hideRegiPopup();
        searchDlvrFriend("All");
        alert(result);
    };
    showMask();
    ajaxCall(url, "html", data, callback);
}

//서브 업체 거절
var subReqReject = function() {
 
    var url = "/proc/member/dlvr_friend_list/update_sub_aprvl.php";
    var data = {
        "member_seqno" : $('input[name=main_radio]:checked').val(),
        "sub_seqno"    : sub_sub_seqno,
        "type"         : "3"
    }
    var callback = function(result) {
        hideRegiPopup();
        searchDlvrFriend("All");
        alert(result);
    };
    showMask();
    ajaxCall(url, "html", data, callback);
}

//팝업 안 검색 사내닉네임 가져오기
var loadMainNick = function(dvs, event) {
    
    if (dvs == "enter") {
        if (event.keyCode != 13) {
            return false;
        }
    }

    search_nick = "";
    if (!checkBlank($("#search_nick").val())) {
        search_nick = $.trim($("#search_nick").val());
    }

    var url = "/ajax/member/dlvr_friend_list/load_sub_main_aprvl_list.php";
    var blank = "<tr><td colspan='4'>검색된 내용이 없습니다.</td></tr>";
    var data = {
        "search_nick": search_nick,
        "main_member_seqno" : main_member_seqno
    }
    var callback = function(result) {
        openRegiPopup($("#sub_pop").html(), 950);
        if ($.trim(result) == "") {
            $("#sub_main_list").html(blank);
        } else {
            $('#sub_main_list').html(result);
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}
