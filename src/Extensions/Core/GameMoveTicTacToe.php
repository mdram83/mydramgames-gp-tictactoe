<?php

namespace MyDramGames\Games\TicTacToe\Extensions\Core;

use MyDramGames\Core\Exceptions\GameMoveException;
use MyDramGames\Core\GameMove\GameMove;
use MyDramGames\Core\GameMove\GameMoveFactory;
use MyDramGames\Utils\Player\Player;

readonly class GameMoveTicTacToe implements GameMove, GameMoveFactory
{
    /**
     * @throws GameMoveException
     */
    public function __construct(
        private Player $player,
        private int $fieldKey
    )
    {
        $this->validateFieldKey($this->fieldKey);
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getDetails(): array
    {
        return ['fieldKey' => $this->fieldKey];
    }

    /**
     * @throws GameMoveException
     */
    public static function create(Player $player, array $inputs): GameMove
    {
        $fieldKey = isset($inputs['fieldKey']) ? (int) $inputs['fieldKey'] : null;
        self::validateFieldKey($fieldKey);

        return new self($player, $fieldKey);
    }

    /**
     * @throws GameMoveException
     */
    protected static function validateFieldKey(mixed $fieldKey): void
    {
        if (!isset($fieldKey) || $fieldKey < 1 || $fieldKey > 9) {
            throw new GameMoveException(GameMoveException::MESSAGE_INVALID_MOVE_PARAMS);
        }
    }
}
