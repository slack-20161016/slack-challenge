<?php ;

require_once(dirname(__FILE__).'/base.php');

class StatusHandler extends HandlerBase {
  public function __construct($slackRequest) {
    parent::__construct($slackRequest);    
  }
  
  public function handle() {    
    //Is there a game?
    $game = $this->req->getCurrentGame();
    if (! $game) {
      $this->jsonExit(
		      array(
			    'response_type' => 'ephemeral',
			    'text' => 'There\'s no active game.  You should start one!',
			    )
		      );
    }

    //Notify current status.
    $status = $game->getStatus();
    $text = "It's @" . $status['user1'] . " vs. @" . $status['user2'] . ".  ";
    $text .= "Who will emerge triumphant, and who will crawl away in shame?  ";
    $text .= "Only time will tell...\n";
    $text .= $status['board_str'] . "\n";
    $text .= "@" . $status['user1'] . " is 'X', @" . $status['user2'] . " is 'O'.  ";
    $text .= "It's @" . $status['turn_user'] . "'s turn";
    $this->jsonExit(
		    array(
			  'response_type' => 'ephemeral',
			  'text' => $text,
			  )
		    );
  }
}

?>