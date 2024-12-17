/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2015/12/04 임종건 생성
 * 2015/12/08 임종건 기본정보 관련 함수 추가
 * 2015/12/09 임종건 요약정보 관련 함수 추가
 * 2015/12/10 임종건 회원정보 관련 함수 추가
 * 2015/12/11 임종건 정산 관련 함수 추가
 * 2015/12/15 임종건 추가회원정보 관련 함수 추가
 * 2015/12/17 임종건 배송관리 관련 함수 추가
 * 2015/12/20 임종건 매출정보 관련 함수 추가
 * 2015/12/21 임종건 등급 관련 함수 추가
 * 2015/12/22 임종건 포인트 관련 함수 추가
 * 2015/12/22 임종건 쿠폰 관련 함수 추가
 * 2015/12/22 임종건 이벤트 관련 함수 추가
 * 2016/03/24 임종건 회원 상세 관련함수 /design_template/js/member/member_common_popup.js 로 이동
 * 2016/09/24 엄준현 memberListAjaxCall colspan 10으로 수정
 *=============================================================================
 *
 */

//보여줄 페이지 수
var showPage = "";

$(document).ready(function() {
//    dateSet('0');
    loadDeparInfo();
    memberListAjaxCall(30, 1);
});

//회원 리스트 호출
var memberListAjaxCall = function(sPage, page) {
    var data = {
        "search_dvs"   : $("#search_dvs").val(),
        "keyword"      : $("#keyword").val(),
        "version"      : $("#version option:selected").val(),
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
