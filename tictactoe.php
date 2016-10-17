<?php ;

require_once(dirname(__FILE__)."/slack_request.php");
$req = new SlackRequest();
$req->handle();
exit();
