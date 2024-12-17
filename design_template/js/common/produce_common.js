//조판 값 적용 버튼
var getTypset = function() {

    var seqno = $("input[type=radio][name=typset_info]:checked").val();

    if (checkBlank(seqno)) {
        alert("선택 된 라디오 버튼이 없습니다.");
        return false;
    }

    showMask();
    var url = "/ajax/produce/typset_list/load_typset_info.php";
    var data = {
        "seqno"       : seqno
    };
    var callback = function(result) {
        var rs = result.split("♪");
        $("#typset_name").val(rs[0]);
        $("#typset_affil").val(rs[1]);
        $("#typset_subpaper").val(rs[2]);
        $("#typset_wid_size").val(rs[3]);
        $("#typset_vert_size").val(rs[4]);

        if (rs[5] == "Y") {
            $("#typset_honggak_y").prop("checked", true);
        } else {
            $("#typset_honggak_n").prop("checked", false);
        }

        $("#typset_dlvrboard").val(rs[7]);
        $("#typset_format_seqno").val(seqno);
    }

    ajaxCall(url, "html", data, callback);
}

//출력 값 적용 버튼
var getOutput = function() {

    var seqno = $("input[type=radio][name=output_info]:checked").val();
 
    if (checkBlank(seqno)) {
        alert("선택 된 라디오 버튼이 없습니다.");
        return false;
    }

    showMask();
    var url = "/ajax/produce/typset_list/load_output_info.php";
    var data = {
        "seqno"       : seqno
    };
    var callback = function(result) {
        var rs = result.split("♪");
        $("#output_name").val(rs[0]);
        $("#output_manu_name").val(rs[1]);
        $("#output_affil").val(rs[2]);
        $("#output_wid_size").val(rs[3]);
        $("#output_vert_size").val(rs[4]);
        $("#output_board").val(rs[5]);
        $("#output_brand_seqno").val(rs[6]);
        $("#output_seqno").val(rs[7]);
    }

    ajaxCall(url, "html", data, callback);
}

//인쇄 값 적용 버튼
var getPrint = function() {

    var seqno = $("input[type=radio][name=print_info]:checked").val();
 
    if (checkBlank(seqno)) {
        alert("선택 된 라디오 버튼이 없습니다.");
        return false;
    }

    showMask();
    var url = "/ajax/produce/typset_list/load_print_info.php";
    var data = {
        "seqno"       : seqno
    };
    var callback = function(result) {
        var rs = result.split("♪");
        $("#print_name").val(rs[0]);
        $("#print_manu_name").val(rs[1]);
        $("#print_affil").val(rs[2]);
        $("#print_wid_size").val(rs[3]);
        $("#print_vert_size").val(rs[4]);
        $("#print_brand_seqno").val(rs[5]);
        $("#print_seqno").val(rs[6]);
    }

    ajaxCall(url, "html", data, callback);
}

//종이 값 적용 버튼
var getPaper = function() {

    var seqno = $("input[type=radio][name=paper_info]:checked").val();
 
    if (checkBlank(seqno)) {
        alert("선택 된 라디오 버튼이 없습니다.");
        return false;
    }

    showMask();
    var url = "/ajax/produce/typset_list/load_paper_info.php";
    var data = {
        "seqno"       : seqno
    };
    var callback = function(result) {
        var rs = result.split("♪");
        $("#paper_name").val(rs[0]);
        $("#paper_dvs").val(rs[1]);
        $("#paper_color").val(rs[2]);
        $("#paper_basisweight").val(rs[3]);
        $("#paper_manu_name").val(rs[4]);
        $("#paper_affil").val(rs[5]);
        $("#paper_op_wid_size").val(rs[6]);
        $("#paper_op_vert_size").val(rs[7]);
        $("#paper_brand_seqno").val(rs[8]);
        $("#paper_seqno").val(rs[9]);
    }

    ajaxCall(url, "html", data, callback);
}

//후공정 값 적용 버튼
var getAfter = function() {

    var seqno = $("input[type=radio][name=after_info]:checked").val();
 
    if (checkBlank(seqno)) {
        alert("선택 된 라디오 버튼이 없습니다.");
        return false;
    }

    showMask();
    var url = "/ajax/produce/typset_list/load_after_info.php";
    var data = {
        "seqno"       : seqno
    };
    var callback = function(result) {
        var rs = result.split("♪");
        $("#after_name").val(rs[0]);
        $("#after_manu_name").val(rs[1]);
        $("#after_extnl_brand_seqno").val(rs[2]);
        $("#after_depth1").val(rs[3]);
        $("#after_depth2").val(rs[4]);
        $("#after_depth3").val(rs[5]);
        $("#after_seqno").val(rs[6]);
    }

    ajaxCall(url, "html", data, callback);
}

/**
 * @brief 종이발주서 인쇄
 */
var getPaperOpPrint = function() {
   
    if( !$("input[name='selPaper']:checked").val() ) {
        alert("인쇄 할 리스트를 선택해주세요.");
        return false;
    }
    
    var url = "/ajax/produce/typset_list/load_paper_op_print.php";
    var data = { 
        "paper_op_seqno" : $("input[name='selPaper']:checked").serialize()
    };

    var callback = function(result) {
        openRegiPopup(result, "800");
    };

    ajaxCall(url, "html", data, callback);
}

//수주처 변경시
var changeManu = function(el, val) {
 
    var url = "/ajax/produce/process_mng/load_brand_option.php";
    var data = { 
        "el"    : el,
        "seqno" : val
    };

    var callback = function(result) {
        $("#extnl_brand_seqno").html(result);
	    showBgMask();
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//생산공정작업일지
var getAdd = function(seqno, el) {
    var url = "/ajax/produce/process_mng/load_" + el + "_popup.php";
    var data = { 
        "seqno" : seqno 
    };

    var callback = function(result) {
        openRegiPopup(result, "1010");

        if (el == "after") {
            (function () {
             var mainbanner = $('.mainBanner'),
             list = mainbanner.children('.list'),
             lists = list.children('li'),
             nav = mainbanner.children('nav'),
             navUl = nav.children('ul'),
             prev = nav.children('.prev'),
             next = nav.children('.next');

             lists.each(function () {
                     var target = $(this);

                     navUl.append('<li><button>' + $(this).find('img').attr('alt') + '</button></li>');
                     navUl.children('li:last-child').children('button').on('click', function () {
                             if (!$(this).hasClass('on')) {
                             list.children('.previous').remove();
                             list.append(list.children('.on').clone().addClass('previous'));

                             list.children('.on').removeClass('on');
                             navUl.children('.on').removeClass('on');

                             $(this).parent().addClass('on');
                             target.addClass('on');
                             }
                             });
                     });

             //prev
             prev.on('click', function () {
                     if (navUl.children('.on').prev().length > 0) {
                     navUl.children('.on').prev().children('button').click();
                     } else {
                     navUl.children('li:last-child').children('button').click();
                     }
                     });
             //next
             next.on('click', function () {
                     if (navUl.children('.on').next().length > 0) {
                     navUl.children('.on').next().children('button').click();
                     } else {
                     navUl.children('li:first-child').children('button').click();
                     }
                     });

             //initialize
             list.append(list.children('li:first-child').clone().addClass('previous'));
             list.children('li:first-child').addClass('on');
             navUl.children('li:first-child').addClass('on');
            })();
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//생산공정작업일지 뷰
var getView = function(seqno, el, flag) {

    if (flag) {
        window.open("/produce/" + el + "_process_view_popup.html?seqno=" + seqno + "&flag=" + flag, "_blank");
    } else {
        window.open("/produce/" + el + "_process_view_popup.html?seqno=" + seqno, "_blank");
    }
}

//바코드 처리 페이지로 이동
var goBarcode = function(el) {
    window.open("/produce/" + el + "_process.html", "_blank");
}
