<?php ;

require_once(dirname(__FILE__).'/base.php');

class PlayHandler extends HandlerBase {
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
			    'text' => 'There is no game in progress.  You should challenge someone!',
			    )
		      );
    }

    //Is the person in the game?
    if (! $game->isPlayer($this->req->user_name)) {
      $this->jsonExit(
		      array(
			    'response_type' => 'ephemeral',
			    'text' => 'You\'re not in the game.  Get in the next one!',
			    )
		      );
    }
    
    //Is it their turn?
    if (! $game->isTurn($this->req->user_name)) {
      $this->jsonExit(
		      array(
			    'response_type' => 'ephemeral',
			    'text' => 'Oops!  It\'s not your turn.',
			    )
		      );
    }
    
    //Is it a valid play?
    $pos = $this->req->verbs[1];
    if (! preg_match("/^[0-9]{1}$/", $pos)) {
      $this->jsonExit(
		      array(
			    'response_type' => 'ephemeral',
			    'text' => 'USAGE: turn <board position #>',
			    )
		      );
    }

    //Make the play.
    $pos = intval($pos);
    try {
      $play_outcome = $game->playAt($this->req->user_name, $pos);
    }
    catch (Exception $e) {
      error_log($e);
      $this->errExit();
    }

    // Check winner before checking play outcome, to sanity check that the game isn't funky.
    $winner = $game->getWinner();
    if ($winner) {
      //Over!  Delete and notify.
      $game->delete();
      $this->jsonExit(
		      array(
			    'response_type' => 'in_channel',
			    'text' => $game->makeBoard() . "\n<@" . $winner . "> wins!  Who's next?",
			    )
		      );      
    }

    // Is it a tie?
    if ($game->isTie()) {
      //Yep!  Delete and notify.
      $game->delete();
      $this->jsonExit(
		      array(
			    'response_type' => 'in_channel',
			    'text' => $game->makeBoard() . "\nCat's game.  Who's next?",
			    )
		      );      
    }

    //Did the play succeed?
    if (! $play_outcome) {
      //Nope.  The spot is bad or taken.
      $this->jsonExit(
		      array(
			    'response_type' => 'ephemeral',
			    'text' => 'Oops!  You can\'t play there.  Please try again.',
			    )
		      );
    }

    //Notify of the play.
    $text = '<@' . $this->req->user_name . ">, just played in position " . $pos . "\n";
    $text .= $game->makeBoard() . "\n";
    $text .= '<@' . $game->getOpponent($this->req->user_name) . ">, it's your turn at Tic Tac Toe!  You're '" . 
      $game->getOpponentSymbol($this->req->user_name) . "'";
    $this->jsonExit(
		    array(
			  'response_type' => 'in_channel',
			  'text' => $text,
			  )
		    );
  }
}

?>