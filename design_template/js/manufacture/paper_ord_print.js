$(document).ready(function() {
    $("#date").datepicker("setDate", new Date());
    $("#date_data").val($("#date").val());
    ordFn.list();
});

/**
 * @brief 선택조건으로 검색 클릭시
 */
var ordFn = {
    //발주서
    "list"       : function() {
        var url = "/ajax/manufacture/paper_ord_print/load_paper_ord_list.php";
        var blank = "<tr><td colspan=\"7\">검색 된 내용이 없습니다.</td></tr>";
        var data = {
    	    "date"  : $("#date").val(),
    	    "state" : $("#op_state").val(),
    	    "extnl_etprs_seqno" : $("#extnl_etprs_seqno").val()
	    };
        var callback = function(result) {
            $("#paper_op_print").html(result);   
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    }
};

function pagePrint(Obj, ck) {
    const setting = "width=890, height=1000";
    const objWin = window.open('', 'print', setting);
    objWin.document.open();
    if (ck == "total") {
        objWin.document.title = "종이 발주 내역서";
    } else {
        objWin.document.title = "종이 발주서";
    }
    objWin.document.write('<html><head><title>분석 레포트 </title>');
    objWin.document.write('<link rel="stylesheet" type="text/css" href="/design_template/css/common.css"/>');
    objWin.document.write('<link rel="stylesheet" type="text/css" href="/design_template/css/basic_manager.css"/>');
    objWin.document.write('<link rel="stylesheet" type="text/css" href="/design_template/css/guide.css"/>');
    objWin.document.write('<link rel="stylesheet" type="text/css" href="/design_template/css/font.css"/>');
    objWin.document.write('<link rel="stylesheet" type="text/css" href="/design_template/css/tab.css"/>');
    objWin.document.write('<link rel="stylesheet" type="text/css" href="/design_template/css/datepicker.css"/>');
    objWin.document.write('<link rel="stylesheet" type="text/css" href="/design_template/css/lightslider.css"/>');
    objWin.document.write('<link rel="stylesheet" type="text/css" href="/design_template/css/width_height.css"/>');
    objWin.document.write('</head><body>');
    objWin.document.write($('#' + Obj).html());
    objWin.document.write('</body></html>');
    objWin.focus();
    objWin.document.close();

    setTimeout(function(){ objWin.print();  }, 300);

    /*
    var W = Obj.offsetWidth;        //screen.availWidth; 
    var H = Obj.offsetHeight;       //screen.availHeight;

    var features = "menubar=no,toolbar=no,location=no,directories=no,status=no,scrollbars=yes,resizable=yes,left=0,top=0,fullscreen=yes"; 
    var PrintPage = window.open("",Obj.id,features); 

    PrintPage.document.open(); 
    PrintPage.document.write("<html><head><title></title><style type='text/css'>table th{border:1px solid #333; width:100px; text-align:center; height:30px; font-size:16px;}table td{border:1px solid #333; width:100px; text-align:center; height:30px; font-size:11px;}table{border-collapse:collapse; width:100%;}</style>\n</head>\n<body>" + Obj.innerHTML + "\n</body></html>"); 
    PrintPage.document.close();

    if (ck == "total") {
        PrintPage.document.title = "종이 발주 내역서"; 
    } else {
        PrintPage.document.title = "종이 발주서"; 
    }
    PrintPage.print(PrintPage.location.reload());
    */
}
