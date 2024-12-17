
$(document).ready(function() {
    $(".date").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0');
    loadAdjustList(1);
});

var page = "1";
var list_num = "30";

//조정 리스트
var loadAdjustList = function(pg) {

    page = pg;

    //회원명 인풋창 비었을경우  회원일련번호 초기화
    if (checkBlank($("#office_nick").val())) {
        $("#member_seqno").val("");
    }

    if (checkBlank($("#member_seqno").val()) && !checkBlank($("#office_nick").val())) {
        alert("검색창 팝업을 이용하시고 검색해주세요.");
        $("#office_nick").focus();
        return false;
    }

    showMask();
    var formData = new FormData($("#search_form")[0]);
        formData.append("page", pg);
        formData.append("list_num", list_num);

    $.ajax({

        type: "POST",
        data: formData,
	    processData : false,
	    contentType : false,
        url: "/ajax/calcul_mng/adjust_data/load_adjust_list.php",
        success: function(result) {
		var list = result.split('♪♭@');
            if($.trim(list[0]) == "") {

                $("#adjust_list").html("<tr><td colspan='5'>" + 
                    "검색된 내용이 없습니다.</td></tr>"); 

	    } else {

                $("#adjust_list").html(list[0]);
                $("#adjust_page").html(list[1]); 
                $('select[name=list_set]').val(list_num);

	    }
        hideMask();
        }, 
        error: getAjaxError
    });
}

//보여 주는 페이지 갯수 설정
var showPageSetting = function(val) {

    list_num = val;
    loadAdjustList(1);
} 

//선택 조건으로 검색(페이징 클릭)
var searchResult = function(pg) {

    page = pg;
    loadAdjustList(page);
}


