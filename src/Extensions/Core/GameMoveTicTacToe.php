<?php

namespace MyDramGames\Games\TicTacToe\Extensions\Core;

use MyDramGames\Core\Exceptions\GameMoveException;
use MyDramGames\Core\GameMove\GameMove;
use MyDramGames\Utils\Player\Player;

readonly class GameMoveTicTacToe implements GameMove
{

    /**
     * @throws GameMoveException
     */
    public function __construct(
        private Player $player,
        private int $fieldKey
    )
    {
        if ($this->fieldKey < 1 || $this->fieldKey > 9) {
            throw new GameMoveException(GameMoveException::MESSAGE_INVALID_MOVE_PARAMS);
        }
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getDetails(): array
    {
        return ['fieldKey' => $this->fieldKey];
    }
}
