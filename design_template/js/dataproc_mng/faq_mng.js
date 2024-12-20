/*
 *
 * Copyright (c) 2018 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2018/03/20 이청산 생성
 *============================================================================
 *
 */

$(document).ready(function() {
    loadFaqList();
});

var page = 1; //페이지
var list_num = 30; //리스트 갯수
var oEditors = [];

//공지사항 리스트 불러오기
var loadFaqList = function() {

    showMask();

    $.ajax({

        type: "POST",
        data: {
                "page" : page,
                "list_num" : list_num
        },
        url: "/ajax/dataproc_mng/faq_mng/load_faq_list.php",
        success: function(result) {
	    var list = result.split('★');
            if($.trim(list[0]) == "") {
                $("#faq_list").html("<tr><td colspan='7'>검색된 내용이 없습니다.</td></tr>"); 
            } else {
              	$("#faq_list").html(list[0]);
              	$("#faq_page").html(list[1]); 
                $('select[name=list_set]').val(list_num);
	     }
	     hideMask();
        }, 
        error: getAjaxError
    });
}

//공지사항 설정 레이어
var popFaqSet = function(seq) {

    showMask();

    $.ajax({

        type: "POST",
        data: {
            "faq_seq" : seq
        },
        url: "/ajax/dataproc_mng/faq_mng/load_faq_popup.php",
        success: function(result) {
        
	        var rs = result.split("♪");
            openRegiPopup(rs[0], 845);

            nhn.husky.EZCreator.createInIFrame({
	        oAppRef: oEditors,
                elPlaceHolder: "ir1",
                sSkinURI: "/design_template/seditor/SmartEditor2Skin.html",	
                htParams : {
                    // 툴바 사용 여부 (true:사용/ false:사용하지 않음)
                    bUseToolbar : true,				
                    // 입력창 크기 조절바 사용 여부 (true:사용/ false:사용하지 않음)
                    bUseVerticalResizer : false,		
                    // 모드 탭(Editor | HTML | TEXT) 사용 여부 (true:사용/ false:사용하지 않음)
                    bUseModeChanger : true,			
                    //aAdditionalFontList : aAdditionalFontSet,		// 추가 글꼴 목록
                    fOnBeforeUnload : function(){
                    }
                }, //boolean
                fOnAppLoad : function(){
                    hideMask();
                    showBgMask();
                    if(rs[1] != "") {
                        oEditors.getById["ir1"].exec("PASTE_HTML", [rs[1]]);
                    }
                },
                fCreator: "createSEditor2"
            });
            
            $("#sort").val(rs[2]);
        }, 
        error: getAjaxError
    });
}

//FAQ 삭제
var delFaq = function(seq) {

    $.ajax({

        type: "POST",
        data: {
		    "faq_seq" : seq
        },
        url: "/proc/dataproc_mng/faq_mng/del_faq.php",
        success: function(result) {
            if($.trim(result) == "1") {

                alert("삭제했습니다.");
                loadFaqList();
                hideRegiPopup();
				//saveFaqHtml();
	        } else {

	    	    alert("삭제에 실패했습니다.");
	       }
        }, 
        error: getAjaxError
    });
}

//FAQ 파일 삭제
var delFaqFile = function(seq, idx) {

    $.ajax({

        type: "POST",
        data: {
		    "file_seq" : seq
        },
        url: "/proc/dataproc_mng/faq_mng/del_faq_file.php",
        success: function(result) {
            if($.trim(result) == "1") {
	    	    alert("삭제했습니다.");
                //$("#file_area" + idx).hide();
                $("#file_area" + idx).html("<br><br>");
	        } else {
	    	    alert("삭제에 실패했습니다.");
	       }
        }, 
        error: getAjaxError
    });
}

//FAQ 저장
var saveFaq = function(seq) {

    //제목이 비었을때
    if ($("#title").val() == "") {

        alert("제목을 입력해주세요.");
	    $("#title").focus();
        return false;
    }

    //내용이 비었을때
    if ($("#cont").val() == "") {

        alert("내용을 입력해주세요.");
	    $("#cont").focus();
        return false;
    }

    var formData = new FormData($("#faq_form")[0]);
    formData.append("faq_seq", seq);
	formData.append("cont" , oEditors.getById["ir1"].getIR());

    $.ajax({

        type: "POST",
        data: formData,
	    processData : false,
	    contentType : false,
        url: "/proc/dataproc_mng/faq_mng/proc_faq.php",
        success: function(result) {
        	if($.trim(result) == "1") {
	    		alert("저장했습니다.");
                loadFaqList();
                hideRegiPopup();
				//saveFaqHtml();
	     	} else {
	        	alert("저장에 실패했습니다.");
	     	}
        }, 
        error: getAjaxError
    });
}

//메인페이지 공지사항 HTML 파일 생성
/*
var saveFaqHtml = function() {
    $.ajax({
        type: "POST",
        data: {},
        url: "/ajax/dataproc_mng/faq_mng/load_faq_html.php",
        dataType : 'html',
        success: function(result) {
			//console.log(result);
		}, 
        error: getAjaxError
    });
}
*/

//보여 주는 페이지 갯수 설정
var showPageSetting = function(val) {

    page = 1;
    list_num = val;
    loadFaqList();
} 

//선택 조건으로 검색(페이징 클릭)
var searchResult = function(pg) {

    page = pg;
    loadFaqList();

}
