/*
 *
 * Copyright (c) 2015-2017 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2015/12/01 엄준현 생성(종이 탭 관련내용 추가)
 * 2015/12/02 엄준현 수정(탭 별로 자바스크립트 분리)
 * 2016/10/29 엄준현 수정(종이 검색조건값 전달부분 수정)
 * 2016/11/02 엄준현 수정(수량별 종이 할인 추가)
 * 2016/11/06 엄준현 수정(수량별 종이 할인 일괄수정/수정팝업 로직 추가)
 * 2017/01/04 엄준현 수정(출력에서 계열/사이즈 삭제)
 *=============================================================================
 *
 */

/**
 * @brief 엑셀 다운로드시 사용되는 함수
 */
var downloadFile = {
    "sellSite" : null,
    "exec"     : function(dvs) {
        if (searchValidate(dvs) === false) {
            return false;
        }

        this.sellSite = $("#" + dvs + "_sell_site").val();

        var url = "/ajax/basic_mng/calcul_price_list/down_excel_" + dvs + "_price_list.php";
        var data = null;
        var callback = function(result) {
            if (result === "FALSE") {
                alert("엑셀파일 생성에 실패했습니다.");
            } else if (result === "NOT_INFO") {
                alert("엑셀로 생성할 정보가 존재하지 않습니다.");
            } else {
                var nameArr = result.split('!');

                var downUrl  = "/common/excel_file_down.php?name=" + nameArr[1];
                    downUrl += "&sell_site=" + downloadFile.sellSite;
                    downUrl += "&file_dvs=" + nameArr[0];

                $("#file_ifr").attr("src", downUrl);
            }
        };

        if (cndSearch.data === null) {
            data = getCndSearchData(dvs);
	        data.sell_site = this.sellSite;
        } else {
            data = cndSearch.data;
        }

        showMask();

        ajaxCall(url, "text", data, callback);
    }
};

/**
 * @brief 엑셀 업로드 할 때 사용하는 함수
 *
 * @param dvs = 어떤 엑셀 파일을 업로드 하는지 구분
 */
var uploadFile = function(dvs) {

    var formData = new FormData();
    var tabDvs = getTabDvs();

    formData.append("dvs", dvs);

    if(checkExt($("#" + tabDvs + "_price_excel_path")) === false) {
        return false;
    }

    formData.append("file"     , $("#" + tabDvs + "_price_excel")[0].files[0]);
    formData.append("sell_site", $("#" + tabDvs + "_sell_site").val());

    showMask();

    excelUploadAjax(formData);
};

/**
 * @brief 선택 조건으로 검색 클릭시
 *
 * @param updateFlag = 가격 수정여부
 * @param dvs        = 탭 별 구분값
 */
var cndSearch = {
    "data" : null,
    "dvs"  : null,
    "exec" : function(dvs, updateFlag) {
	    hideModiPop();

        if (searchValidate(dvs) === false) {
            return false;
        }

        this.dvs = dvs;

        var url = "/ajax/basic_mng/calcul_price_list/load_" + dvs + "_price_list.php";
        var data = null;
        var callback = function(result) {
            $("#" + cndSearch.dvs + "_price_list").html(result);
            $("#" + cndSearch.dvs + "_price_list").show();
        };

    	if (updateFlag === false) {
            data = getCndSearchData(dvs);
            data.sell_site = $("#" + dvs + "_sell_site").val();
    	} else {
    	    data = this.data;
    	}

        this.data = data;

        showMask();

        ajaxCall(url, "html", data, callback);
    }
};

/**
 * @brief 각 탭에 해당하는 validation 검사
 */
var searchValidate = function(dvs) {
    if (dvs === "paper") {
        if (checkBlank($("#paper_sort").val()) === true) {
            alert("종이 분류를 선택해주세요.");
            return false;
        }
        if (checkBlank($("#paper_name").val()) === true) {
            alert("종이명을 선택해주세요.");
            return false;
        }
    } else if (dvs === "output") {
        if (checkBlank($("#output_name").val()) === true) {
            alert("출력명을 선택해주세요.");
            return false;
        }
    } else if (dvs === "print") {
        if (checkBlank($("#cate_top").val()) === true) {
            alert("카테고리를 선택해주세요.");
            return false;
        }
    } else if (dvs === "sale_paper") {
        if (checkBlank($("#sale_cate_bot").val()) === true) {
            alert("카테고리를 선택해주세요.");
            return false;
        }
        if (checkBlank($("#sale_paper_name").val()) === true) {
            alert("종이명을 선택해주세요.");
            return false;
        }
        if (checkBlank($("#sale_output_size").val()) === true) {
            alert("사이즈명을 선택해주세요.");
            return false;
        }

        var minAmt = parseFloat($("#sale_min_amt").val());
        var maxAmt = parseFloat($("#sale_max_amt").val());

        if (!checkBlank(minAmt) && minAmt > maxAmt) {
            return alertReturnFalse("최소수량이 최대수량보다 큽니다.");
        }
    }

    return true;
}

/**
 * @brief 탭에 따라서 가격검색할 url 반환
 *
 * @param dvs = 탭 구분
 *
 * @return 가격검색 url
 */
var getCndSearchUrl = function(dvs) {
    var ret = null;

    return ret;
};

/**
 * @brief 탭에 따라서 가격검색할 파라미터 반환
 *
 * @param dvs = 탭 구분
 *
 * @return 가격검색용 파라미터
 */
var getCndSearchData = function(dvs) {
    var ret = null;

    if (dvs === "paper") {
        ret = {
            "paper_sort"        : $("#paper_sort").val(),
            "paper_name"        : $("#paper_name").val(),
            "paper_dvs"         : $("#paper_dvs").val(),
            "paper_color"       : $("#paper_color").val(),
            "paper_basisweight" : $("#paper_basisweight").val(),
            "paper_affil"       : clickAffil.affil[dvs],
            "paper_size"        : $("#paper_size").val(),
            "tax_yn"            : $("input[name='paper_tax_yn']:checked").val()
        };
    } else if (dvs === "output") {
        ret = {
            "output_name"        : $("#output_name").val(),
            "output_board_dvs"   : $("#output_board_dvs").val(),
            "tax_yn"             : $("input[name='output_tax_yn']:checked").val()
        };
    } else if (dvs === "print") {
        ret = {
            "print_name"     : $("#print_name").val(),
            "print_purp_dvs" : $("#print_purp_dvs").val(),
            "print_affil"    : clickAffil.affil[dvs],
            "tax_yn"         : $("input[name='print_tax_yn']:checked").val()
        };

        if (checkBlank($("#cate_mid").val()) === true) {
            ret.cate_sortcode = $("#cate_top").val();
        } else {
            ret.cate_sortcode = $("#cate_mid").val();
        }
    } else if (dvs === "sale_paper") {
        ret = {
            "sell_site"         : $("#sale_sell_site").val(),
            "cate_sortcode"     : $("#sale_cate_bot").val(),
            "cate_name"         : $("#sale_cate_bot > option:selected").text(),
            "paper_name"        : $("#sale_paper_name").val(),
            "paper_dvs"         : $("#sale_paper_dvs").val(),
            "paper_color"       : $("#sale_paper_color").val(),
            "paper_basisweight" : $("#sale_paper_basisweight").val(),
            "paper_affil"       : $("#sale_output_size > option:selected").attr("affil"),
            "stan_mpcode"       : $("#sale_output_size").val(),
            "stan_typ"          : $("#sale_size_typ").val(),
            "stan_name"         : $("#sale_output_size > option:selected").text(),
            "pos_num"           : $("#sale_output_size > option:selected").attr("pos_num"),
            "min_amt"           : $("#sale_min_amt").val(),
            "max_amt"           : $("#sale_max_amt").val(),
            "tax_yn"            : $("input[name='sale_paper_tax_yn']:checked").val()
        };
    }


    return ret;
};

/**
 * @brief 제목 테이블에서 요율, 적용금액을 클릭했을 경우
 *
 * @param event = 좌표값을 얻기위한 이벤트 객체
 * @param dvs   = 어떤 항목을 클릭했는지 구분값
 * @param pos   = 몇 번째 가격항목인지 위치
 */
var modiPriceInfo = {
    "dvs"    : null,
    "seqno"  : null,
    "exec"   : function(event, dvs, pos) {
        var point = getPopupPoint(event);
        var tabDvs = getTabDvs();

        selectedModiAllPriceDvs(dvs);

        this.dvs    = dvs;
        this.pos    = pos;

        hideModiPop("modi_each_" + tabDvs + "_price");
        showModiPop("modi_all_" + tabDvs + "_price", point.x + 100, point.y);
    }
};

/**
 * @brief 내용에서 요율, 적용금액을 클릭했을 경우
 *
 * @param event = 좌표값을 얻기위한 이벤트 객체
 * @param dvs   = 어떤 항목을 클릭했는지 구분값
 * @param seqno = 가격항목 seqno
 */
var modiPriceInfoEach = {
    "dvs"    : null,
    "seqno"  : null,
    "exec"   : function(event, dvs, seqno) {
        var point = getPopupPoint(event);
        var tabDvs = getTabDvs();

        this.dvs    = dvs;
        this.seqno  = seqno;

        hideModiPop("modi_all_" + tabDvs + "_price");
        showModiPop("modi_each_" + tabDvs + "_price", point.x + 220, point.y);
    }
};

/**
 * @brief 일괄수정 적용버튼 클릭시
 *
 * @param dvs = 어느 탭인지 구분값
 */
var aplyPriceInfo = {
    "tabDvs" : null,
    "exec"   : function(dvs) {
        if (checkBlank($("#modi_all_" + dvs + "_price_val").val()) === true) {
            if (modiPriceInfo.dvs === "R") {
                alert("요율을 입력해주세요.");
                return false;
            } else {
                alert("적용금액을 입력해주세요.");
                return false;
            }
        }

        this.tabDvs = dvs;
    
        var url = "/proc/basic_mng/calcul_price_list/update_" + dvs + "_price_list.php";
        var data =  getAplyPriceData(dvs);
        var callback = function(result) {
            if (result === "T") {
                cndSearch.exec(aplyPriceInfo.tabDvs, true);
            } else {
                alert("가격 수정에 실패했습니다.");
            }
    
            hideModiPop();
        };
    
        ajaxCall(url, "text", data, callback);
    }
};

/**
 * @brief 탭 별 가격 일괄수정시 넘길 파라미터 생성
 *
 */
var getAplyPriceData = function(dvs) {
    var ret = null;
    var pos = modiPriceInfo.pos;
    var val = $("#modi_all_" + aplyPriceInfo.tabDvs + "_price_val").val();
    var prefix = '#' + dvs + '_';

    if (dvs === "paper") {
        var $sellSite = $(prefix + "sell_site_" + pos);
        var $info     = $(prefix + "info_" + pos);
        var $affil    = $(prefix + "affil_" + pos);
        var $size     = $(prefix + "size_" + pos);

        ret = {
            "sell_site" : $sellSite.attr("val"),
            "info"      : $info.attr("val"),
            "affil"     : $affil.attr("val"),
            "size"      : $size.attr("val"),
            "val"       : val,
            "dvs"       : modiPriceInfo.dvs
        };
    } else if (dvs === "output") {
        var $sellSite = $(prefix + "sell_site_" + pos);
        var $mpcode   = $(prefix + "mpcode_" + pos);

        ret = {
            "sell_site" : $sellSite.attr("val"),
            "mpcode"    : $mpcode.attr("val"),
            "val"       : val,
            "dvs"       : modiPriceInfo.dvs
        };
    } else if (dvs === "print") {
        var $sellSite = $(prefix + "sell_site_" + pos);
        var $mpcode   = $(prefix + "mpcode_" + pos);

        ret = {
            "sell_site" : $sellSite.attr("val"),
            "mpcode"    : $mpcode.attr("val"),
            "val"       : val,
            "dvs"       : modiPriceInfo.dvs
        };
    } else if (dvs === "sale_paper") {
        var sellSite     = $(prefix + "sell_site_" + pos).attr("val");
        var cateSortcode = $(prefix + "cate_sortcode_" + pos).attr("val");
        var paperInfo    = $(prefix + "info_" + pos).attr("val");
        var paperAffil   = $(prefix + "affil_" + pos).attr("val");
        var stanName     = $(prefix + "stan_name_" + pos).attr("val");
            stanName     = stanName.split('!')[0];
        var pageInfo     = $(prefix + "page_info_" + pos).attr("val");

        var stanMpcode = getValueByKey($("#sale_output_size"), stanName);

        ret = {
            "sell_site"     : sellSite,
            "cate_sortcode" : cateSortcode,
            "paper_info"    : paperInfo,
            "paper_affil"   : paperAffil,
            "stan_mpcode"   : stanMpcode,
            "page_info"     : pageInfo,
            "val"           : val,
            "dvs"           : modiPriceInfo.dvs
        };
    }

    return ret;
}

/**
 * @brief 개별수정 적용버튼 클릭시
 *
 * @param dvs = 어느 탭인지 구분값
 */
var aplyPriceInfoEach = {
    "tabDvs" : null,
    "exec"   : function(dvs) {
        if (checkBlank($("#modi_each_" + dvs + "_price_val").val()) === true) {
            if (modiPriceInfoEach.dvs === "R") {
                alert("요율을 입력해주세요.");
                return false;
            } else {
                alert("적용금액을 입력해주세요.");
                return false;
            }
        }

        if (modiPriceInfoEach.seqno === "-1") {
            return alertReturnFalse("요율 또는 적용금액\n전체 적용 후 가능합니다.");
        }

        this.tabDvs = dvs;
    
        var url = "/proc/basic_mng/calcul_price_list/update_" + dvs + "_price_list.php";
        var data = {
            "val"         : $("#modi_each_" + dvs + "_price_val").val(),
            "dvs"         : modiPriceInfoEach.dvs,
            "price_seqno" : modiPriceInfoEach.seqno,
        };
        var callback = function(result) {
            if (result === "T") {
                cndSearch.exec(aplyPriceInfoEach.tabDvs, true);
            } else {
                alert("가격 수정에 실패했습니다.");
            }
    
            hideModiPop();
        };

        ajaxCall(url, "text", data, callback);
    }
};

/**
 * @brief 현재 어떤 탭을 선택하고 있는지 반환
 *
 * @return 선택하고 있는 탭
 */
var getTabDvs = function() {
    var ret = null;

    $("#root_tab").children("li").each(function() {
        if (checkBlank($(this).attr("class")) === false) {
            ret = $(this).attr("dvs");
        }
    });

    return ret;
}

/**
 * @brief 계열 선택시 사이즈 검색
 *
 * @param obj   = 체크박스 객체
 * @param affil = 클릭한 체크박스 구분
 * @param dvs   = 사이즈 구분
 */
var clickAffil = {
    "affil" : {
        "paper"        : {"46" : false, "GUK" : false, "SPC" : false},
        "output_board" : {"46" : false, "GUK" : false, "SPC" : false},
        "print"        : {"46" : false, "GUK" : false, "SPC" : false}
    },
    "dvs"  : null,
    "exec" : function(obj, affil, dvs) {
        if($(obj).prop("checked")) {
            this.affil[dvs][affil] = true;
        } else {
            this.affil[dvs][affil] = false;
        }

        this.dvs = dvs;

        // 인쇄 사이즈는 종이 사이즈랑 똑같은 값 가져옴
        if (dvs === "print") {
            dvs = "paper";
        }

        var url = "/ajax/basic_mng/calcul_price_list/load_" + dvs + "_size.php";
        var callback = function(result) {
            $("#" + clickAffil.dvs + "_size_width").val('*');
            $("#" + clickAffil.dvs + "_size_height").val('*');
            $("#" + clickAffil.dvs + "_size").html(result);
        };

        ajaxCall(url, "html", this.affil[this.dvs], callback);
    }
};

/**
 * @brief 사이즈 선택시 가로 사이즈, 세로 사이즈 변경
 *
 * @param dvs  = 탭 구분
 * @param size = 선택한 출력판 사이즈
 */
var selectSize = function(dvs, size) {
    var sizeTemp = size.split('*');

    $("#" + dvs + "_size_width").val(sizeTemp[0]);
    $("#" + dvs + "_size_height").val(sizeTemp[1]);
};
