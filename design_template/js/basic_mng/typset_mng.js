/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2015/12/04 왕초롱 생성
 * 2016/11/16 harry 수정 -> 나중에 할예정
 *=============================================================================
 *
 */

var page = "1"; //페이지
var list_num = "30"; //리스트 갯수

$(document).ready(function() {
    selectSearch(1);
});

//조판 리스트 가져오기
var selectSearch = function(page) {

   var url = "/ajax/basic_mng/typset_mng/load_typset_list.php";
   var blank = "<tr><td colspan=\"8\">검색 된 내용이 없습니다.</td></tr>";
   var data = {
       "page"        : page,
       "list_num"    : list_num,
       "typset_name" : $("#typset_name").val(),
       "affil_fs"    : $("input[name=affil_fs]:checked").val(),
       "affil_guk"   : $("input[name=affil_guk]:checked").val(),
       "affil_spc"   : $("input[name=affil_spc]:checked").val()
   };
   var callback = function(result) {
       var rs = result.split("♪");
       if (rs[0].trim() == "") {
           $("#typset_list").html(blank);
           return false;
       }
       $("#typset_list").html(rs[0]);
       $("#typset_page").html(rs[1]);
   };

   showMask();
   ajaxCall(url, "html", data, callback);
}

//보여 주는 페이지 갯수 설정
var showPageSetting = function(val) {
    list_num = val;
    selectSearch(1);
} 

//선택 조건으로 검색(페이징 클릭)
var movePage = function(page) {
    selectSearch(page);
}

//전체 선택
var allCheck = function() {
    //만약 전체선택 체크박스가 체크 된 상태일 경우
    if ($("#all_check").prop("checked")) {
        $("#typset_list input[type=checkbox]").prop("checked", true);
    } else {
        $("#typset_list input[type=checkbox]").prop("checked", false);
    }
}

/*
//조판 추가 팝업
var popAddTypset = function() {

    $.ajax({
        type: "POST",
        data: {},
        url: "/ajax/basic_mng/typset_mng/load_typset_pop.php",
        success: function(result) {

            openRegiPopup(result, 700);
            $('#del_typset').hide();
        }, 
        error: getAjaxError
    });
}
*/

//조판 정보 보기
var loadTypsetInfo = function(seq) {

   var url = "/ajax/basic_mng/typset_mng/load_typset_info.php";
   var blank = "<tr><td colspan=\"8\">검색 된 내용이 없습니다.</td></tr>";
   var data = {
       "typset_seqno" : seq
   };
   var callback = function(result) {
       openRegiPopup(result, 600);
   };

   showMask();
   ajaxCall(url, "html", data, callback);
}

//조판 품목 저장
var saveTypset = function(type) {

    //조판명이 비었을때
    if(checkBlank($("#pop_typset_name").val())) {

        alert("조판명을 입력해주세요.");
        $("#pop_typset_name").focus();
        return false;
    }

    //카테고리
    if(checkBlank($("#cate_bot").val())) {

        alert("카테고리 소분류까지 선택해주세요.");
        $("#cate_bot").focus();
        return false;
    }

    var formData = new FormData($("#typset_form")[0]);
    formData.append("add_yn", type);
    formData.append("typset_seqno", typset_seqno);
    formData.append("honggak_yn", $(':radio[name="honggak_yn"]:checked').val());

    $.ajax({
        type: "POST",
        data: formData,
        processData : false,
        contentType : false,
        url: "/proc/basic_mng/typset_mng/proc_typset.php",
        success: function(result) {
            if($.trim(result) == "1") {
          	    alert("저장했습니다.");
		        hideRegiPopup();
    	    	selectSearch(page);

      	    } else {
        	    alert("실패했습니다.");
            }
        }   
    });
}

//조판 선택 삭제
var delTypset = function() {

    var select_typset = getselectedNo();

    if (select_typset == "") {
        alert("삭제할 목록을 선택해주세요");
        return false;
    }

    $.ajax({
            type: "POST",
            data: {
                "select_typset" : select_typset
            },
            url: "/proc/basic_mng/typset_mng/del_typset.php",
            success: function(result) {
            if($.trim(result) == "1") {
                alert("삭제했습니다.");
            } else {
                alert("삭제에 실패했습니다.");
            }
            selectSearch(1);
           }   
    });
}

//조판 개별 삭제
var delPopTypset = function() {

    $.ajax({
            type: "POST",
            data: {
                "select_typset" : typset_seqno,
            },
            url: "/proc/basic_mng/typset_mng/del_typset.php",
            success: function(result) {
            if($.trim(result) == "1") {

                alert("삭제했습니다.");
    		hideRegiPopup();
                selectSearch(1);

            } else {

                alert("삭제에 실패했습니다.");

            }
        }   
    });
}

//체크박스 선택시 value값 가져오는 함수
var getselectedNo = function(el) {

    var selectedValue = ""; 
    
    $("#typset_list input[name=typset_chk]:checked").each(function() {
        selectedValue += ","+ $(this).val();		    
    });

    if (selectedValue != "") {
        selectedValue = selectedValue.substring(1);
    }

    return selectedValue;
}
