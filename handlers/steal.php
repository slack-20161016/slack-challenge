<?php ;

require_once(dirname(__FILE__).'/base.php');

class StealHandler extends HandlerBase {
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

    //Delete it.
    $game->delete();

    //Notify.
    $this->jsonExit(
		    array(
			  'response_type' => 'in_channel',
			  'text' => '@' . $this->req->user_name . ' deleted the current game.',
			  )
		    );

  }
}

?>