$(document).ready(function() {

    loadAccList(1);

});

var page = 1;
var list_num = 30;
var acc_detail_seqno = "";//계정상세 일련번호
var edit_yn = "N";

//계정 리스트 불러오기
var loadAccList = function(pg) {

    showMask();

    $.ajax({
        type: "POST",
        data: {
                "page"     : pg,
                "list_num" : list_num
        },
        url: "/ajax/calcul_mng/basic_data/load_acc_list.php",
        success: function(result) {

	    var list = result.split('♪♭@');
            if($.trim(list[0]) == "") {

                $("#acc_list").html("<tr><td colspan='5'>검색된 내용이 없습니다.</td></tr>"); 

	        } else {

                $("#acc_list").html(list[0]);
                $("#acc_page").html(list[1]); 
                $('select[name=list_set]').val(list_num);

	        }
	    hideMask();
        },
        error: getAjaxError
    });
}
//계정 등록 팝업
var popAccRegi = function() {

    edit_yn = "N";
    openRegiPopup($('#acc_popup').html(), 450);

}

//계정 상세 불러오기
var editAccDetail = function(seq, acc, acc_detail, note) {

    edit_yn = "Y";
    openRegiPopup($("#acc_popup").html(), 450);

    $("#acc_subject").val(acc);
    $("#acc_detail").val(acc_detail);
    $("#note").val(note);

    acc_detail_seqno = seq;

}

var saveAccDetail = function() {

    //계정상세가 없을때
    if ($("#acc_detail").val() == "") {

        alert("계정상세를 입력해주세요.");
        return false;
    }

    var formData = new FormData($("#acc_form")[0]);

    if (edit_yn == "Y") {

        formData.append("acc_detail_seqno", acc_detail_seqno);

    }

    $.ajax({
        type: "POST",
        data: formData,
	    processData : false,
	    contentType : false,
        url: "/proc/calcul_mng/basic_data/proc_acc_detail.php",
        success: function(result) {

            if($.trim(result) == "1") {
                alert("저장했습니다.");
                hideRegiPopup();
                loadAccList(page);

            } else {

                alert("실패했습니다.");

            }
        },
        error: getAjaxError
    });
}

//보여 주는 페이지 갯수 설정
var showPageSetting = function(val) {

    list_num = val;
    loadAccList(1);
} 

//선택 조건으로 검색(페이징 클릭)
var searchResult = function(pg) {

    page = pg;
    loadAccList(page);
}


