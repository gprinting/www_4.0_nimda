var page = "1"; //페이지
var list_num = "30"; //리스트 갯수
var search_col = ""; //검색어
var add_yn = "Y";//추가/수정 여부
var typset_seqno = "";

//엔터 쳤을때 검색 창 팝업 레이어 보이기
var showPopupLayer = function(event, type) {

    if(event.keyCode == 13) {
        searchFirst(event, type);
    }

}

//조판명 가져오기
var loadTypsetName = function(search_str) {
    
    $.ajax({
            type: "POST",
            data: {
                "search_str" : search_str
            },
            url: "/ajax/basic_mng/typset_mng/load_typset_name.php",
            success: function(result) {
                $("#search_result").html(result);
           }   
    });
}

//조판파일 가져오기
var loadTypsetFile = function(search_str) {
    
    $.ajax({
            type: "POST",
            data: {
                "search_str" : search_str
            },
            url: "/ajax/basic_mng/typset_mng/load_typset_file.php",
            success: function(result) {
                $("#search_result").html(result);
           }   
    });
}

//팝업 검색된 조판명 클릭시
var nameClick = function(val) {

    closeLayer('#search_popup_layer');
    $("#typset_name").val(val);

}

//팝업 검색된 파일명 클릭시
var fileClick = function(val) {

    closeLayer('#search_popup_layer');
    $("#typset_file").val(val);

}

//팝업창 검색
var searchFirst = function(event, type) {

    var search_str = $("#typset_" + type).val();

    if ($.trim(search_str) == "") {

	    alert("검색창에 검색어를 입력해주세요.");
        return false;

    }
    
    //조판명 검색
    if (type == "name") {

        loadTypsetName(search_str);

    //조판파일 검색
    } else if (type == "file") {

        loadTypsetFile(search_str);

    }

    layerPopup(event, type, "search", "");

}

//레이어 닫기
var closeLayer = function(layer) {

    if (layer == "#add_popup_layer") {

            initTypsetData();
    }
    //list_num = "30";
    $(layer).css("display", "none");

}

//조판 리스트 가져오기
var selectSearch = function(page) {

    $.ajax({

        type: "POST",
        data: {
                "page" : page,
                "list_num" : list_num,
                "name" : $("#typset_name").val(),
                "affil_fs" : $("input[name=affil_fs]:checked").val(),
                "affil_guk" : $("input[name=affil_guk]:checked").val(),
                "affil_spc" : $("input[name=affil_spc]:checked").val(),
                "file" : $("#typset_file").val()
        },
        url: "/ajax/basic_mng/typset_mng/load_typset_list.php",
        success: function(result) {
            var list = result.split('★');

	        if ($.trim(list[0]) == "") {

                $("#typset_list").html("<tr><td colspan='8'>검색된 내용이 없습니다.</td></tr>"); 

	        } else {

                $("#typset_list").html(list[0]);
                $("#typset_page").html(list[1]); 
		        $('select[name=list_set]').val(list_num);

	        }
        }, 
        error: getAjaxError
    });

}

//팝업창 검색
var searchPopStr = function() {

    var search_str = $("#popup_search").val();
    
    if ($.trim(search_str) == "") {

	    showMsg440("검색창에 검색어를 입력해주세요.");
        return false;

    }
    
    //종이명 검색
    if (search_col == "name") {

        loadTypsetName(search_str);

    //파일 검색
    } else {

        loadTypsetFile(search_str);

    }
}

//레이어 팝업
var layerPopup = function(event, type, dvs, seq) { 

    var layer = "";

    if (dvs == "search") {

        $("#popup_search").val("");
        $("#search_result").empty();

        layer = document.getElementById("search_popup_layer");

    } else if (dvs == "save"){

        //조판 추가,수정인지 여부를 저장
        add_yn = type;
        layer = document.getElementById("add_popup_layer");

    }

    var ua = window.navigator.userAgent;

    //마우스로 선택한곳의 x축(화면에서 좌측으로부터의 거리)를 얻는다.
    var _x = null;

    if (ua.indexOf("Chrome") === -1) {
        _x = event.clientX + document.documentElement.scrollLeft;
    } else {
        _x = event.clientX + document.body.scrollLeft;
    }
    //마우스로 선택한곳의 y축(화면에서 상단으로부터의 거리)를 얻는다.
    var _y = event.clientY + document.documentElement.scrollTop;

    if (ua.indexOf("Chrome") === -1) {
        _y = event.clientY + document.documentElement.scrollTop;
    } else {
        _y = event.clientY + document.body.scrollTop;
    }

    //마우스로 선택한 위치의 값이 -값이면 0으로 초기화. (화면은 0,0으로 시작한다.)
    if(_x < 0) _x = 0;
    //마우스로 선택한 위치의 값이 -값이면 0으로 초기화. (화면은 0,0으로 시작한다.)
    if(_y < 0) _y = 0;

    if (dvs == "search" || type == "Y") {
        
        //레이어팝업의 좌측으로부터의 거리값을 마우스로 클릭한곳의 위치값으로 변경.
        layer.style.left = _x-120+"px";
        //레이어팝업의 상단으로부터의 거리값을 마우스로 클릭한곳의 위치값으로 변경.
        layer.style.top = _y-180+"px";
    } else {

            layer.style.left = _x-960+"px";
            layer.style.top = _y-150+"px";
    }

    if (dvs == "search") {

        $('#search_popup_layer').css("display", "block");
        $("#popup_search").focus();

    } else if (dvs == "save"){

        if (type == "Y") {

            initTypsetData();
            $('#del_typset').hide();
            $('#add_popup_layer').css("display", "block");

        } else {

            typset_seqno = seq;

            $('#del_typset').show();

	    modiTypsetInfo(seq);

        }
    }
    search_col = type;
};

//조판 정보 팝업에 입력
var modiTypsetInfo = function(seq) {

    $.ajax({

        type: "POST",
        data: {
                "typset_seqno" : seq
        },
        url: "/ajax/basic_mng/typset_mng/load_typset_info.php",
        success: function(result) {

            var typset_info = result.split('♪♥♭');

            $("#pop_typset_name").val(typset_info[0]);
            $("#affil").val(typset_info[1]);
            $("#subpaper").val(typset_info[2]);
            $("#upload_file").val(typset_info[3]);
            $("#wid_size").val(typset_info[4]);
            $("#vert_size").val(typset_info[5]);
            $("#dscr").val(typset_info[6]);

            $('#add_popup_layer').css("display", "block");

        }, 
        error: getAjaxError
    });


}

//조판 팝업 인풋 비우기
var initTypsetData = function() {

    $("#pop_typset_name").val('');
    $("#affil").val('46');
    $("#subpaper").val('2절');
    $("#upload_file").val('');
    $("#upload_btn").val('');
    $("#wid_size").val('');
    $("#vert_size").val('');
    $("#dscr").val('');

}

//보여 주는 페이지 갯수 설정
var showPageSetting = function(val) {

    list_num = val;
    selectSearch('1');
} 

//선택 조건으로 검색(페이징 클릭)
var searchResult = function(page) {
    selectSearch(page);
}

//팝업 검색창 엔터 쳤을때 검색
var popEnterCheck = function(event) {

    if(event.keyCode == 13) {

	 searchPopStr();
    }
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

//조판 품목 저장
var saveTypset = function(type) {

    //조판명이 비었을때
    if($("#pop_typset_name").val() == "") {

        alert("조판명을 입력해주세요.");
	    $("#pop_typset_name").focus();
        return false;
    }

    var formData = new FormData($("#typset_form")[0]);
        formData.append("add_yn", add_yn);
        formData.append("typset_seqno", typset_seqno);

    $.ajax({
            type: "POST",
            data: formData,
	    processData : false,
	    contentType : false,
            url: "/proc/basic_mng/typset_mng/proc_typset.php",
            success: function(result) {
            	if($.trim(result) == "1") {

	    	    alert("추가했습니다.");

		    closeLayer("#add_popup_layer");
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
            selectSearch("1");
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


