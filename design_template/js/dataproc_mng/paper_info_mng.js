/**
 * @brief 종이 정보 검색
 *
 * @param dvs = 선택구분값
 */
var loadPaperInfo = function(dvs) {
    initSelectInfo(dvs);
    initPhotoInfo();

    var url = "/ajax/dataproc_mng/paper_info_mng/load_paper_info.php";
    var data = {
        "dvs" : dvs,
        "paper_name" : $("#paper_name").val(),
        "paper_dvs"  : $("#paper_dvs").val()
    };
    var callback = function(result) {
        $("#paper_" + dvs).html(result);
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 종이 정보 셀렉트박스 초기화
 *
 * @param dvs = 선택구분값
 */
var initSelectInfo = function(dvs) {
    // 셀렉트박스 초기화
    if (dvs === "dvs") {
        $("#paper_dvs").html("<option value=''>구분(선택)</option>");
    }
    $("#paper_color").html("<option value=''>색상(선택)</option>");
};

/**
 * @brief 종이 미리보기 정보 초기화
 */
var initPhotoInfo = function() {
    // 정보 초기화
    $("#preview_photo").html('');
    $("#file_name").html('');
    $("#del_btn").remove();
};

/**
 * @brief 재질 미리보기 정보 검색
 */
var loadPreviewInfo = function(flag) {
    var url = "/json/dataproc_mng/paper_info_mng/load_paper_preview_info.php";
    var data = {
        "paper_name"  : $("#paper_name").val(),
        "paper_dvs"   : $("#paper_dvs").val(),
        "paper_color" : $("#paper_color").val()
    };
    var callback = function(result) {
        var imgPath  = result.path;
        var imgName  = result.name;
        var imgSeqno = result.seqno;

        if (!checkBlank(imgName) && !checkBlank(imgSeqno)) {
            var delBtn = "<button onclick=\"delPreviewFile('" + imgSeqno + "')\" type=\"button\" id=\"del_btn\" class=\"btn btn-sm bred fa\">이미지삭제</button>";
            var downUrl = "/common/paper_preivew_file_down.php?seqno=" + imgSeqno;
            var html = "<a href=\"" + downUrl + "\">" + imgName + "</span>";

            $("#file_name").html(html);
            $("#file_btn").html(delBtn);
        }

	var img = "<img src=\"" + imgPath + "\" style=\"width:100%; height:100%;\" />";
        $("#preview_photo").html(img);
        $("#preview_seqno").val(imgSeqno);
    };

    showMask();
    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 재질 미리보기 이미지파일 삭제
 *
 * @param seqno = 파일 일련번호
 */
var delPreviewFile = function(seqno) {
    var url  = "/proc/dataproc_mng/paper_info_mng/delete_paper_preview_info.php";
    var data = {
        "seqno" : seqno
    };
    var callback = function(result) {
        initPhotoInfo();
        loadPreviewInfo();
    };

    ajaxCall(url, "text", data, callback);
};

/**
 * @brief 종이미리보기 정보 저장
 */
var savePreviewInfo = function() {
    if (checkBlank($("#paper_color").val())) {
        return alertReturnFalse("종이를 색상까지 선택해주세요.");
    }

    var url  = "/proc/dataproc_mng/paper_info_mng/insert_paper_preview_info.php";
    var data = new FormData($("#form")[0]);
    var callback = function(result) {
        loadPreviewInfo();
    };

    showMask();
    ajaxCallMultipart(url, "text", data, callback);
};
