<?php ;

require_once(dirname(__FILE__)."/db.php");

class Game {
  public function __construct($channel) {
    $this->channel = $channel;

    //Get any active game.  Raises exception if missing.
    $sql = "SELECT * FROM `games` WHERE `channel`='" .
      $GLOBALS['db_conn']->escape_string($channel) . "' LIMIT 1";
    $result = $GLOBALS['db_conn']->query($sql);
    if (! $result) {
      error_log($GLOBALS['db_conn']->error);
      throw new Exception();
    }
    $row = mysqli_fetch_assoc($result);
    if (! $row) {
      throw new Exception();
    }

    $this->id = $row['pk'];
    $this->user1 = $row['user1'];
    $this->user2 = $row['user2'];
    $this->turn = $row['turn'];

    //Unpack the game data.
    $this->data = json_decode($row['data'], true);
    if (! $this->data) {
      $this->data = array();
    }

    //Make sure the board is there.
    if (! isset($this->data['board'])) {
      $this->data['board'] = array(null, null, null, null, null, null, null, null, null);
    }

    //Figure out who's turn it is.
    if ($row['turn'] == 1) {
      $this->turn_user = $this->user1;
    }
    else {
      $this->turn_user = $this->user2;
    }
  }

  function isTurn($user) {
    return $user == $this->turn_user;
  }

  function getStatus() {
    return array(
		 'user1' => $this->user1,
		 'user2' => $this->user2,
		 'turn_user' => $this->turn_user,
		 'board_str' => $this->makeBoard(),
		 );
  }

  function isTie() {
    //Are there any empty spots?
    $board = $this->data['board'];
    for ($i = 0 ; $i < 9 ; $i++) {
      if ($board[$i] == null) {
	//Unplayed spot.  Not a tie yet.
	return false;
      }
    }

    //Is there a winner?
    if ($this->getWinner()) {
      //Someone won.  Not a tie.
      return false;
    }

    //No empty spots, no winner.  It's a tie.
    return true;
  }

  function getWinner() {
    $board = $this->data['board'];

    //Columns
    for ($i = 0 ; $i < 3 ; $i++) {
      if (($board[$i] == $board[$i+3]) && ($board[$i] == $board[$i+6])) {
	$user_var = 'user' . $board[$i];
	return $this->{$user_var};
      }
    }

    //Rows
    for ($i = 0 ; $i < 7 ; $i += 3) {
      if (($board[$i] == $board[$i+1]) && ($board[$i] == $board[$i+2])) {
	$user_var = 'user' . $board[$i];
	return $this->{$user_var};
      }
    }

    //Diagonals
    if (($board[0] == $board[4]) && ($board[0] == $board[8])) {
      $user_var = 'user' . $board[0];
      return $this->{$user_var};
    }
    if (($board[6] == $board[4]) && ($board[6] == $board[2])) {
      $user_var = 'user' . $board[6];
      return $this->{$user_var};
    }

    return null;
  }

  function delete() {
    //Gets rid of the game.
    $sql = "DELETE FROM `games` WHERE `channel`='" .
      $GLOBALS['db_conn']->escape_string($this->channel) . "' LIMIT 1";
    $result = $GLOBALS['db_conn']->query($sql);
    if (! $result) {
      error_log($GLOBALS['db_conn']->error);
      throw new Exception();
    }
  }

  function getOpponent($user) {
    if ($user == $this->user1) {
      return $this->user2;
    }

    return $this->user1;
  }

  function isPlayer($user) {
    error_log($user);
    return (
	    ($user == $this->user1) ||
	    ($user == $this->user2)
	    );
  }

  function getOpponentSymbol($user) {
    if ($user == $this->user1) {
      return "O";
    }

    return "X";
  }

  function getUserNum($user) {
    if ($user == $this->user1) {
      return '1';
    }
    
    if ($user == $this->user2) {
      return '2';
    }
    
    error_log('Invalid user ' . $user . ' for game ' . $this->id);
    throw new Exception();
  }

  function playAt($user, $pos) {
    //Sanity check -- is it their turn?
    if (! $this->isTurn($user)) {
      return False;
    }

    //Sanity check board pos.
    if (! (is_int($pos) && ($pos > 0) && ($pos < 10))) {
      return False;
    }

    //Is the spot free?
    if ($this->data['board'][$pos-1] !== null) {
      return False;
    }

    $this->data['board'][$pos-1] = $this->getUserNum($user);

    $sql = "UPDATE `games` SET `turn`='" . (($this->turn % 2) + 1) . "', `data`='" . 
      $GLOBALS['db_conn']->escape_string(json_encode($this->data)) . 
      "' where `pk`='" . $GLOBALS['db_conn']->escape_string($this->id) . "' limit 1";
    if (! $GLOBALS['db_conn']->query($sql)) {
      throw new Exception($GLOBALS['db_conn']->error);
    }

    return True;
  }

  function makeBoard() {
    $ret = "";

    $elements = array();
    foreach ($this->data['board'] as $pos => $cell) {
      if ($cell === null) {
	$item = "[" . ($pos + 1) . "]";
      }
      elseif ($cell == 1) {
	$item = "[*X*]";
      }
      else {
	$item = "[*O*]";
      }

      if ((($pos + 1) % 3) == 0) {
	$item .= "\n";
      }

      $elements[] = $item;
    }
      
    return join($elements, " ");
  }

}

?>