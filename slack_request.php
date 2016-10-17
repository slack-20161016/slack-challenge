<?php ;

require_once(dirname(__FILE__)."/game.php");

class SlackRequest {
  public function __construct() {
    $this->token = $_REQUEST['token'];
    $this->team_id = $_REQUEST['team_id'];
    $this->channel_id = $_REQUEST['channel_id'];
    $this->user_name = $_REQUEST['user_name'];
    $this->command = $_REQUEST['command'];
    $this->verbs = preg_split("/[\r\n\t ]+/", $_REQUEST['text']);
  }

  public function getCurrentGame() {
    try {
      return new Game($this->channel_id);
    }
    catch (Exception $e) {
      return null;
    }
  }

  public function exitUsage() {
    $text = "USAGE */ttt [challenge|status|play|steal]*\n";
    $text .= "*challenge <user id>* -- Challenges a user to a game.\n";
    $text .= "*status* -- Shows current game status.\n";
    $text .= "*play <spot #>* -- Plays a move at the specified location.\n";
    $text .= "*steal* -- Ungracefully deletes current game.\n";
    $res = array(
		 'response_type' => 'ephemeral',
		 'text' => $text,
		 );
    
    header('Content-Type: application/json');
    echo(json_encode($res));
    exit();
  }

  public function handle() {
    //Validate the handler.
    $handlerName = $this->verbs[0];
    $handlerFile = dirname(__FILE__)."/handlers/" . $handlerName . ".php";
    if (! @include_once($handlerFile)) {
      $this->exitUsage();
    }

    //Make and call the handler.
    $handlerClass = ucfirst($handlerName) . "Handler";
    $handler = new $handlerClass($this);
    $handler->handle();
  }

}

?>