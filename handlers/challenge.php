<?php ;

require_once(dirname(__FILE__).'/base.php');
require_once(dirname(__FILE__)."/../game.php");

class ChallengeHandler extends HandlerBase {
  public function __construct($slackRequest) {
    parent::__construct($slackRequest);    
  }

  public function handle() {    
    //Is there a game?
    $game = $this->req->getCurrentGame();
    if ($game) {
      $this->jsonExit(
		      array(
			    'response_type' => 'in_channel',
			    'text' => 'There is already a game in progress, please wait.',
			    )
		      );
    }

    //Get the opponent.
    $opponent = ltrim($this->req->verbs[1], '@');
    if (! $opponent) {
      $this->jsonExit(
		      array(
			    'response_type' => 'in_channel',
			    'text' => 'Please specify an opponent.',
			    )
		      );
    }

    //They can't play theirself.
    if ($opponent == $this->req->user_name) {
      $this->jsonExit(
		      array(
			    'response_type' => 'ephemeral',
			    'text' => 'You should try challenging yourself in a more meaningful way.',
			    )
		      );
    }

    //Create a game.
    $sql = "INSERT INTO `games` set " . 
      "`user1`='" . $this->db->escape_string($this->req->user_name) . "', " . 
      "`user2`='" . $this->db->escape_string($opponent) . "', " .
      "`channel`='" . $this->db->escape_string($this->req->channel_id) . "'";
    if (! $this->db->query($sql)) {
      error_log($sql);
      error_log($this->db->error);
      $this->errExit();
    }

    //Make the game object.
    $game = new Game($this->req->channel_id);
    
    //Notify the opponent.
    $text = "<@" . $opponent . "> it's on!  <@" . $this->req->user_name . "> has challenged you to a game!\n";
    $text .= $game->makeBoard() . "\n";
    $text .= "Use */ttt play <spot #>* to take your turn";
    $res = array(
		 'response_type' => 'in_channel',
		 'text' => $text,
		 );
    $this->jsonExit($res);
  }
}

?>