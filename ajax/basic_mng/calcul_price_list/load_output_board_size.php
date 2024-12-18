<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/common_define/common_info.inc');

$fb = new FormBean();

$type_46  = $fb->form("46");
$type_guk = $fb->form("GUK");

$ret = "<option value=\"\">전체</option>";

$option_form = "<option value=\"%s*%s\">%s*%s</option>";

if ($type_46 === "true") {
    $board_size_arr = TYPE_46_BOARD_SIZE;

    foreach ($board_size_arr as $affil => $val1) {
        if ($val1[0] === null) {
            $ret .= sprintf($option_form, $val1["WID"]
                                        , $val1["VERT"]
                                        , $val1["WID"]
                                        , $val1["VERT"]);
        } else {
            foreach ($val1 as $idx => $val2) {
                $ret .= sprintf($option_form, $val2["WID"]
                                            , $val2["VERT"]
                                            , $val2["WID"]
                                            , $val2["VERT"]);
            }
        }
}
if ($type_guk === "true") {
    $board_size_arr = TYPE_GUK_BOARD_SIZE;

    foreach ($board_size_arr as $affil => $val1) {
        if ($val1[0] === null) {
            $ret .= sprintf($option_form, $val1["WID"]
                                        , $val1["VERT"]
                                        , $val1["WID"]
                                        , $val1["VERT"]);
        } else {
            foreach ($val1 as $idx => $val2) {
                $ret .= sprintf($option_form, $val2["WID"]
                                            , $val2["VERT"]
                                            , $val2["WID"]
                                            , $val2["VERT"]);
            }
        }
    }
}

echo $ret;
?>
