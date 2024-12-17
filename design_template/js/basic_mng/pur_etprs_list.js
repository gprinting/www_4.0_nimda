var search_loc = ""; //주소 검색
var page = "1"; //페이지
var list_num = "30"; //리스트 갯수
var pur_prdt = ""; //매입품목
var etprs_seqno = ""; //외부업체 일련번호
var brand_seqno = ""; //브랜드 일련번호
var member_seqno = ""; //외부업체 회원 일련번호
var chk_flag = false; //아이디 중복여부 체크 확인
var add_yn = "Y"; //외부업체 회원 추가,수정 여부

//브랜드 명 가져옴
var modiBrandInfo = function(seqno) {

    var url = "/ajax/basic_mng/pur_etprs_list/load_brand_info.php";
    var data = {
        "seqno" : seqno
    };
    var callback = function(result) {
        showPopMask();
        openPopPopup($("#add_brand").html(), 600);
	$("#brand_name").val(result);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//매입업체에 해당되는 브랜드 리스트 가져옴
var getBrandList = function() {
    var url = "/ajax/basic_mng/pur_etprs_list/load_brand_list.php";
    var data = {
    	"etprs_seqno" : etprs_seqno
    };
    var callback = function(result) {
	$("#brand_list").html(result);
	showBgMask();
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//매입업체에 해당하는 브랜드 가져오기
var loadExtnlBrand = function(val) {

    $.ajax({

        type: "POST",
        data: {
                "etprs_seqno" : val
        },
        url: "/ajax/basic_mng/pur_etprs_list/load_extnl_brand.php",
        success: function(result) {
            $("#pur_brand").html(result);
        }, 
        error: getAjaxError
    });
}

//매입업체 리스트 검색
var searchEtprsList = function() {

    tob = $("select[name=tob]").val();
    etprs_seqno = $("select[name=pur_manu]").val();
    brand_seqno = $("select[name=pur_brand]").val();

    if (tob == "") {
	    alert("매입품을 선택해주세요");
	    return false;
    }

    var url = "/ajax/basic_mng/pur_etprs_list/load_etprs_list.php";
    var data = {
        "tob" : tob,
        "etprs_seqno" : etprs_seqno,
        "pur_brand" : brand_seqno
    };
    var callback = function(result) {
	if (result.trim() == "") {
	        $("#pur_list").html("<tr><td colspan='6'>검색된 내용이 없습니다.</td></tr>"); 
	} else {
	        $("#pur_list").html(result);
	}
	hideMask();
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//매입업체 등록 팝업 레이어
var regiPopEtprs = function(val) {

    var url = "/ajax/basic_mng/pur_etprs_list/load_etprs_regi_pop.php";
    var callback = function(result) {
	hideMask();
        showBgMask();
        openRegiPopup(result, 1200);
    };

    showMask();
    ajaxCall(url, "html", {}, callback);
}

//매입업체 수정 팝업 레이어
var editPopEtprs = function(val) {

    etprs_seqno = val;

    var url = "/ajax/basic_mng/pur_etprs_list/load_etprs_info.php";
    var data = {
        "etprs_seqno" : val,
        "brand_seqno" : brand_seqno
    };
    var callback = function(result) {
	hideMask();
        showBgMask();
	var rs = result.split('♪');
        openRegiPopup(rs[0], 1200);
	$("#edit_pur_prdt").val(rs[1]);
	if ($("#pur_prdt").val() != "기타") {
	    supplyList('', '1');
	}
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//매입업체 정보 등록
var regiEtprsInfo = function() {

    var formData = $("#regi_form").serializeArray();

    var url = "/proc/basic_mng/pur_etprs_list/regi_etprs_info.php";
    var callback = function(result) {
	alert(result);
        hideRegiPopup();
        searchEtprsList();
    };

    showMask();
    ajaxCall(url, "html", formData, callback);
}

//매입업체 정보 수정
var editEtprsInfo = function(seq) {

    var formData = $("#edit_form").serializeArray();
    formData.push({name: 'edit_pur_prdt', value: $("#edit_pur_prdt").val()});
    formData.push({name: 'etprs_seqno', value: seq});

    var url = "/proc/basic_mng/pur_etprs_list/modi_etprs_info.php";
    var callback = function(result) {
        alert(result);
        hideMask();
        showBgMask();
    };

    showMask();
    ajaxCall(url, "html", formData, callback);
}

//매입업체 관리 창 닫기
var hideManuRegiPopup = function() {
    searchEtprsList();
    hideRegiPopup();
}

//매입업체 사업자등록증 정보 수정
var editEtprsBlsInfo = function(seq) {

    var formData = $("#edit_bls_form").serializeArray();
    formData.push({name: 'etprs_seqno', value: seq});

    var url = "/proc/basic_mng/pur_etprs_list/modi_etprs_bls_info.php";
    var callback = function(result) {
	alert(result);
	hideMask();
        showBgMask();
    };

    showMask();
    ajaxCall(url, "html", formData, callback);
}

//추가 팝업 생성 - 담당자, 브랜드
var openMngAddPopup = function(dvs, seqno) {

    var url = "/ajax/basic_mng/pur_etprs_list/load_poppopup_regi.php";
    var data = {
        "dvs"   : dvs,
	"seqno" : seqno
    }; 
    var callback = function(result) {
	var el_width = "700";
	if (dvs == "brand") {
            el_width = "400";
	}
        openPopPopup(result, el_width);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//외부업체 회원 아이디 중복 체크
var checkId = function() {

    var mem_id = $("#mem_id").val();

    if (checkBlank(mem_id)) {
        alert("아이디 값이 비어있습니다.");
	$("#mem_id").focus();
	return false;
    }

    var url = "/ajax/basic_mng/pur_etprs_list/check_extnl_member.php";
    var data = {
        "mem_id" : mem_id
    }; 
    var callback = function(result) {
        if(result == "1") {
            alert("사용가능한 아이디입니다.");
            chk_flag = true;
        } else {
            alert("중복된 아이디가 존재합니다.");
            chk_flag = false;
        }
	showBgMask();
	showPopMask();
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//외부업체 회원 추가 및 수정
var saveExtnlMember = function(flag) {

    if (flag == 'Y' && chk_flag == false) {
        alert("아이디 중복체크를 해주세요.");
        return false;
    }

    var arr = new Array();
    var msg = new Array();
    arr[0] = "mem_id";
    arr[1] = "mem_passwd";
    arr[2] = "mem_name";

    msg[0] = "아이디를 입력해주세요.";
    msg[1] = "접속코드를 입력해주세요.";
    msg[2] = "이름을 입력해주세요.";

    for (var i = 0; i < arr.length; i++) {
        if (checkBlank($.trim($("#" + arr[i]).val()))) {
            $("#" + arr[i]).focus();
            alert(msg[i]);
            return false;
        }
    }

    var memData = {
        'add_yn'      : flag,
        'etprs_seqno' : etprs_seqno,
        'mem_id'      : $("#mem_id").val(),
        'mem_passwd'  : $("#mem_passwd").val(),
        'mem_name'    : $("#mem_name").val(),
        'mem_job'     : $("#mem_job").val(),
        'mem_task'    : $("#mem_task").val(),
        'mem_mail_top': $("#mem_mail_top").val(),
        'mem_mail_btm': $("#mem_mail_btm").val(),
        'mem_tel_top' : $("#mem_tel_top").val(),
        'mem_tel_mid' : $("#mem_tel_mid").val(),
        'mem_tel_btm' : $("#mem_tel_btm").val(),
        'mem_cel_top' : $("#mem_cel_top").val(),
        'mem_cel_mid' : $("#mem_cel_mid").val(),
        'mem_cel_btm' : $("#mem_cel_btm").val()
    };

    var url = "/proc/basic_mng/pur_etprs_list/proc_extnl_member.php";
    var data = memData;
    var callback = function(result) {
        if($.trim(result) == "1") {
            alert("저장했습니다.");
            hidePopPopup();
	    showBgMask();
            loadExtnlMember();

        } else {
            alert("저장에 실패했습니다.");
	    showPopMask();
	    showBgMask();
        }
        chk_flag = false;
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//외부업체 회원 삭제
var delExtnlMember = function(seqno) {

    var url = "/proc/basic_mng/pur_etprs_list/del_extnl_member.php";
    var data = {
        "seqno" : seqno 
    };
    var callback = function(result) {
        if(result == "1") {
            alert("삭제했습니다.");
            hidePopPopup();
            loadExtnlMember();

        } else {
            alert("삭제에 실패했습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//브랜드 추가 및 수정
var saveBrand = function(seqno) {
    var url = "/proc/basic_mng/pur_etprs_list/modi_brand_info.php";
    var data = {
    	"brand_seqno" : seqno,
        "etprs_seqno" : etprs_seqno,
	"name"        : $("#brand_name").val()
    };
    var callback = function(result) {
	if (result == 1) {
            getBrandList();
            hidePopPopup();
            alert("브랜드가 추가 또는 수정 되었습니다.");
	} else {
            alert("브랜드 추가 또는 수정을 실패하였습니다.");
	}
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//브랜드 삭제
var delBrand = function(seqno) {

    if (confirm("이 브랜드와 관련 된 정보가 모두 삭제 됩니다.\n정말 삭제하시겠습니까?") == false) {
	 return false;
    }
    
    var url = "/proc/basic_mng/pur_etprs_list/del_brand_info.php";
    var data = {
    	"brand_seqno" : seqno
    };
    var callback = function(result) {
	if (result == 1) {
	    getBrandList();
            hidePopPopup();
            alert("브랜드가 삭제 되었습니다.");
	} else {
            alert("브랜드삭제를 실패하였습니다.");
	}
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//공급품 list
var supplyList = function(sort, page) {

    //sort 정보
    var sort_info = sort.split('/');
    for (var i in sort_info) {
        sort_info[i];
    }

    var url = "/ajax/basic_mng/pur_etprs_list/load_supply.php";
    var data = {
        "extnl_etprs_seqno" : etprs_seqno,
	"brand_seqno" : brand_seqno,
        "sort" : sort_info[0],
        "sort_type" : sort_info[1],
        "pur_prdt" : pur_prdt,
        "page" : page,
        "list_num" : list_num
    };
    var callback = function(result) {
	var list = result.split('♪');
	if (list[0].trim() == "") {
            $("#prdt_list").html("<tr><td colspan='11'>검색된 내용이 없습니다.</td></tr>"); 
	} else {
            $("#prdt_list").html(list[0]);
            $("#supply_page").html(list[1]); 
	    $('select[name=list_set]').val(list_num);
	}
        showBgMask();
    };

    ajaxCall(url, "html", data, callback);
}

//공급품 상세 팝업
var supplyPopDetail = function(event, seqno, prdt) {

    var url = "/ajax/basic_mng/pur_etprs_list/load_supply_detail.php";
    var data = {
        "extnl_etprs_seqno" : etprs_seqno,
	 "brand_seqno" : brand_seqno,
	 "seqno" : seqno,
	 "prdt" : prdt
    };
    var callback = function(result) {
        openPopPopup(result, 1200);
    };

    ajaxCall(url, "html", data, callback);
}

//보여 주는 페이지 갯수 설정
var showPageSetting = function(val) {

    list_num = val;
    supplyList('','1');
} 

//선택 조건으로 검색(페이징 클릭)
var searchResult = function(page) {
    supplyList('',page);
}

//컬럼별 sort
var sortList = function(val, el) {

    var flag = "";

    if ($(el).children().hasClass("fa-sort-desc")) {
        sortInit();
        flag = "ASC";
    } else {
        sortInit();
        flag = "DESC";
    }

    var sort = val + "/" + flag;
    supplyList(sort, '1');
}

var loadExtnlMember = function() {

    $.ajax({

        type: "POST",
        data: {
                "etprs_seqno" : etprs_seqno
        },
        url: "/ajax/basic_mng/pur_etprs_list/load_extnl_member_list.php",
        success: function(result) {

            $("#extnl_member").html(result);
        }, 
        error: getAjaxError
    });
}

//메일 타입 선택시
var changeMail = function(val) {

    if (val == "direct"){
        $("#mem_mail_btm").focus();

    } else {
        $("#mem_mail_btm").val(val);
    }
}
