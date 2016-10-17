<?php ;

require_once(dirname(__FILE__).'/../db.php');

class HandlerBase {
  public function __construct($slackRequest) {
    $this->db = $GLOBALS['db_conn'];
    $this->req = $slackRequest;
  }

  function jsonExit($arr) {
    header('Content-Type: application/json');
    echo(json_encode($arr));
    exit();
  }

  function errExit() {
      $this->jsonExit(
		     array(
			   'response_type' => 'ephemeral',
			   'text' => 'Sorry, we apear to be having technical difficulties.  Please try again.',
			   )
		     );
  }

}

?>