#! /usr/bin/php -f
<?
include_once("/var/www/html/nimda/common_define/common_config.php");
include_once(dirname(__FILE__) . "/common/ConnectionPool.php");
include_once(dirname(__FILE__) . "/dao/EventMngDAO.php");
include_once(dirname(__FILE__) . "/dao/FileAttachDAO.php");

function makeOeventHtml($rs) {

    $i = 0;
    $html = "";
    $ret  = "\n<li class=\"%s\">";
    $ret .= "\n   <a href=\"#none\" target=\"_self\">";
    $ret .= "\n       <dl>";
    $ret .= "\n           <dd class=\"figure\">";
    $ret .= "\n               <div class=\"label\">";
                                //퍼센트 이미지
    $ret .= "\n                 %s ";
//    $ret .= "\n                   <img src=\"[TPH_Vdesign_dir]/images/main/event_number_%s.png\">";
//    $ret .= "\n                   <img src=\"[TPH_Vdesign_dir]/images/main/event_number_%s.png\">";
    $ret .= "\n               </div>";
                            //상품사진
    $ret .= "\n               <img src=\"%s\" style=\"width:300px;height:300px;\">";
    $ret .= "\n           </dd>";
    $ret .= "\n           <dt>";
    $ret .= "\n               <ul>";
                                //이벤트이름
    $ret .= "\n                   <li>%s</li>";
                                //종이카테고리
    $ret .= "\n                   <li>%s</li>";
                                //인쇄물카테고리
    $ret .= "\n                   <li>%s</li>";
                                //상품규격
    //$ret .= "                   <li>%s</li>";
                                //수량
    $ret .= "\n                   <li>%s</li>";
    $ret .= "\n               </ul>";
    $ret .= "\n           </dt>";
    $ret .= "\n           <dd class=\"price\">";
    $ret .= "\n               %s<span class=\"unit\">원</span>";
    $ret .= "\n           </dd>";
    $ret .= "\n       </dl>";
    $ret .= "\n   </a>";
    $ret .= "\n</li>";

    while ($rs && !$rs->EOF) {

        if ($i == 0)
            $class = "on";
        else
            $class = "";

        $sale_price = (int)$rs->fields["sale_price"];
        $sum_price = (int)$rs->fields["sum_price"];
        $basic_price = $sale_price + $sum_price;
       
        $percent = round($sale_price / $basic_price * 100); 

        $img = "";
        if (strlen($percent) == 2) {
            $img  = "\n                   <img src=\"[TPH_Vdesign_dir]/images/main/event_number_".substr($percent, 0, 1).".png\">";
            $img .= "\n                   <img src=\"[TPH_Vdesign_dir]/images/main/event_number_".substr($percent, 1, 1).".png\">";
        } else if (strlen($percent) == 1) {
            $img  = "\n                   <img src=\"[TPH_Vdesign_dir]/images/main/event_number_".$percent.".png\">";
        } else {
            $img  = "\n                   <img src=\"[TPH_Vdesign_dir]/images/main/event_number_0.png\">";
        }

        $paper_detail = $rs->fields["paper_name"] . " "
                        . $rs->fields["paper_dvs"] . " "
                        . $rs->fields["paper_color"] . " "
                        . $rs->fields["paper_basisweight"];

        $file = explode(".", $rs->fields["save_file_name"]);

        $html .= sprintf($ret, $class
                        , $img
                        , $rs->fields["file_path"] . $file[0] . "_300_300." . $file[1]
                        //, $rs->fields["file_path"] . $rs->fields["save_file_name"]
                        , $rs->fields["name"]
                        , $rs->fields["name"]
                        , $paper_detail
                        , $rs->fields["print_tmpt"]
                        , number_format($rs->fields["amt"]) . " " . $rs->fields["amt_unit"]
                        , number_format($rs->fields["sum_price"]));
        $i++;
        $rs->moveNext();
    }

    return $html;
}


function do_loop() {

    $connectionPool = new ConnectionPool();
    $conn = $connectionPool->getPooledConnection();

    $eventDAO = new EventMngDAO();
    $fileDAO = new FileAttachDAO();

    //이벤트종료시간이 끝난 이벤트는 플래그변경
    $eventDAO->updateOeventFlag($conn);

    //main페이지에 표출되는 파일 재생성
    $result = $eventDAO->selectOeventHtml($conn);

    //파일 썸네일 추가
    while ($result && !$result->EOF) {
        $param = array();
        $param["fs"] = $result->fields["file_path"] . $result->fields["save_file_name"];
        $param["req_width"] = "300";
        $param["req_height"] = "300";
    
        $fileDAO->makeThumbnail($param);
        $result->moveNext();
    }
    $result->moveFirst();

    $oevent_html = makeOeventHtml($result);
    $fp = fopen(OEVENT_HTML, "w+") or die("can't open file");
    fwrite($fp, $oevent_html);
    fclose($fp);

    $conn->close();
}

function main() {
    while (1) {
        do_loop();
        sleep(60);
    }
}

main();
?>
