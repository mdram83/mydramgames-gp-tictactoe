<?php

namespace MyDramGames\Games\TicTacToe\Extensions\Core;

use MyDramGames\Core\Exceptions\GameMoveException;
use MyDramGames\Core\GameMove\GameMove;
use MyDramGames\Core\GameMove\GameMoveFactory;
use MyDramGames\Utils\Player\Player;

class GameMoveFactoryTicTacToe implements GameMoveFactory
{
    /**
     * @throws GameMoveException
     */
    public function create(Player $player, array $inputs): GameMove
    {
        $fieldKey = isset($inputs['fieldKey']) ? (int) $inputs['fieldKey'] : null;
        $this->validateFieldKey($fieldKey);

        return new GameMoveTicTacToe($player, $fieldKey);
    }

    /**
     * @throws GameMoveException
     */
    private function validateFieldKey(mixed $fieldKey): void
    {
        if (!isset($fieldKey)) {
            throw new GameMoveException(GameMoveException::MESSAGE_INVALID_MOVE_PARAMS);
        }
    }
}
