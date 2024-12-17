<?php
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . '/com/nexmotion/common/util/ConnectionPool.inc');
include_once(INC_PATH . '/com/nexmotion/common/entity/FormBean.inc');
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_mng/PrdtItemRegiDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PrdtItemRegiDAO();

$fb = $fb->getForm();

$seqno = $fb["seqno"];
$nick  = $fb["nick"];

$param = [];
$param["cate_paper_seqno"] = $seqno;
$param["nick"] = $nick;

if (!$dao->updateCatePaperNick($conn, $param)) {
    echo "별칭 수정에 실패했습니다.";
}

$conn->Close();
