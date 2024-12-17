var cateSortcode = null;
var cateName     = null;

/**
 * @brief 카테고리 소분류 선택시 등록된 템플릿 정보 출력
 */
var loadTemplateInfo = function(sortcode) {
    cateSortcode = sortcode;

    if (checkBlank($("#cate_bot").val())) {
        cateName = null;
        return false;
    }

    cateName = $("#cate_bot > option:selected").text();
    $("#selected_cate").html(cateName);

    var url = "/ajax/dataproc_mng/template_mng/load_template_list.php";
    var data = {
        "cate_sortcode" : sortcode
    };
    var callback = function(result) {
        $("#template_list").html(result);
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 카테고리 템플릿정보 입력 팝업 출력
 */
var showTemplatePop = function(seqno) {
    if (!isSelectCateBot()) {
        return false;
    }

    var url = "/ajax/dataproc_mng/template_mng/load_template_pop.php";
    var data = {
        "cate_sortcode" : cateSortcode,
        "seqno"         : seqno
    };
    var callback = function(result) {
        openRegiPopup(result, 560);
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 카테고리 템플릿정보 저장/수정
 */
var modiTemplate = function(seqno) {
    if (!isSelectCateBot()) {
        return false;
    }

    var aiFile  = $("#pop_ai_file").val();
    var epsFile = $("#pop_eps_file").val();
    var cdrFile = $("#pop_cdr_file").val();
    var sitFile = $("#pop_sit_file").val();

    if (checkBlank(seqno) &&
            checkBlank(aiFile) &&
            checkBlank(epsFile) &&
            checkBlank(cdrFile) &&
            checkBlank(sitFile)) {
        return alertReturnFalse("템플릿 파일을 올려주세요.");
    }

    var url = "/proc/dataproc_mng/template_mng/";
    var data = new FormData();

    if (checkBlank(seqno)) {
        url += "insert_template.php";
    } else {
        data.append("seqno", seqno);
        url += "update_template.php";
    }

    data.append("cate_sortcode", cateSortcode);
    data.append("cate_name"    , cateName);
    data.append("uniq_num"     , $("#pop_uniq_num").val());
    data.append("stan_name"    , $("#pop_stan_name").val());
    data.append("cut_wid_size"  , $("#pop_cut_wid_size").val());
    data.append("cut_vert_size" , $("#pop_cut_vert_size").val());
    data.append("work_wid_size" , $("#pop_work_wid_size").val());
    data.append("work_vert_size", $("#pop_work_vert_size").val());
    if (!checkBlank(aiFile)) {
        var ext = aiFile.split('.').pop().toLowerCase();
        if (!chkExt(ext, "ai")) {
            return alertReturnFalse("AI 파일이 아닙니다.");
        }
        data.append("ai_file" , $("#pop_ai_file")[0].files[0]);
    }
    if (!checkBlank(epsFile)) {
        var ext = epsFile.split('.').pop().toLowerCase();
        if (!chkExt(ext, "eps")) {
            return alertReturnFalse("EPS 파일이 아닙니다.");
        }
        data.append("eps_file", $("#pop_eps_file")[0].files[0]);
    }
    if (!checkBlank(cdrFile)) {
        var ext = cdrFile.split('.').pop().toLowerCase();
        if (!chkExt(ext, "cdr")) {
            return alertReturnFalse("CDR 파일이 아닙니다.");
        }
        data.append("cdr_file", $("#pop_cdr_file")[0].files[0]);
    }
    if (!checkBlank(sitFile)) {
        var ext = sitFile.split('.').pop().toLowerCase();
        if (!chkExt(ext, "sit")) {
            return alertReturnFalse("SIT 파일이 아닙니다.");
        }
        data.append("sit_file", $("#pop_sit_file")[0].files[0]);
    }
    var callback = function(result) {
        if (!checkBlank($.trim(result))) {
            return alertReturnFalse(result);
        }

        //alert("작업이 완료되었습니다.");
        //hideRegiPopup();
        loadTemplateInfo(cateSortcode);
    };

    ajaxCallMultipart(url, "text", data, callback);
};

/**
 * @brief 카테고리 템플릿 파일 다운로드
 *
 * @param seqno = 템플릿 일련번호
 * @param dvs   = 파일 구분값
 */
var downloadTemplate = function(seqno, dvs) {
    if (checkBlank(seqno)) {
        return false;
    }

    var url = "/dataproc_mng/template_file_down.php?";
    url += "&seqno=" + seqno;
    url += "&dvs=" + dvs;

    $("#file_ifr").attr("src", url);
};

/**
 * @brief 카테고리 템플릿 삭제
 *
 * @param seqno = 템플릿 일련번호
 * @param dvs   = 파일 구분값, 없으면 전체삭제
 */
var removeTemplate = function(seqno, dvs) {
    if (checkBlank(seqno)) {
        return alertReturnFalse("존재하지 않는 템플릿입니다.");
    }

    var url = "/proc/dataproc_mng/template_mng/delete_template.php";
    var data = {
        "cate_sortcode" : cateSortcode,
        "cate_name"     : cateName,
        "seqno"         : seqno,
        "dvs"           : dvs 
    };
    var callback = function(result) {
        if (!checkBlank($.trim(result))) {
            return alertReturnFalse(result);
        }

        alert("작업이 완료되었습니다.");

        if (checkBlank(dvs)) {
            hideRegiPopup();
            loadTemplateInfo(cateSortcode);
            return false;
        }

        $('#' + dvs + "_file_name").html("없음");
        loadTemplateInfo(cateSortcode);
    };

    ajaxCall(url, "text", data, callback);
};

/**
 * @brief 팝업에서 사이즈 변경시 재단, 작업사이즈 값 변경
 *
 * @parma obj = option 객체
 */
var changeSize = function(obj) {
    var cutWid  = $(obj).attr("cut_wid");
    var cutVert = $(obj).attr("cut_vert");
    var workWid  = $(obj).attr("work_wid");
    var workVert = $(obj).attr("work_vert");

    var cutSize = '';
    var workSize = '';

    if (!checkBlank(cutWid) && !checkBlank(cutVert)) {
        cutSize = cutWid + '*' + cutVert;
    }
    if (!checkBlank(workWid) && !checkBlank(workVert)) {
        workSize = workWid + '*' + workVert;
    }

    $("#pop_cut_size").val(cutSize);
    $("#pop_cut_wid_size").val(cutWid);
    $("#pop_cut_vert_size").val(cutVert);

    $("#pop_work_size").val(workSize);
    $("#pop_work_wid_size").val(workWid);
    $("#pop_work_vert_size").val(workVert);
};

/**
 * @brief 확장자 비교
 * 
 * @param ext = compare와 비교할 확장자 문자열
 * @param compare = 기준 확장자
 *
 * @return 같으면 true
 */
var chkExt = function(ext, compare) {
    if (ext.toLowerCase() !== compare.toLowerCase()) {
        return false;
    }

    return true;
};
