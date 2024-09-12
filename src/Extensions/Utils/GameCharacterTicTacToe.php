<?php

namespace MyDramGames\Games\TicTacToe\Extensions\Utils;

use MyDramGames\Utils\Exceptions\GameCharacterException;
use MyDramGames\Utils\GameCharacter\GameCharacter;
use MyDramGames\Utils\Player\Player;

readonly class GameCharacterTicTacToe implements GameCharacter
{
    /**
     * @throws GameCharacterException
     */
    public function __construct(
        private string $name,
        private Player $player
    )
    {
        if (!in_array($this->name, ['x', 'o'])) {
            throw new GameCharacterException(GameCharacterException::MESSAGE_WRONG_NAME);
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }
}
