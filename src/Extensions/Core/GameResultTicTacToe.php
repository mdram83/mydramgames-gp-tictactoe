<?php

namespace MyDramGames\Games\TicTacToe\Extensions\Core;

use MyDramGames\Core\Exceptions\GameResultException;
use MyDramGames\Core\GameResult\GameResult;

class GameResultTicTacToe implements GameResult
{
    public const string MESSAGE_WIN = 'won the Game!';
    public const string MESSAGE_DRAW = 'Draw!';
    public const string MESSAGE_FORFEIT = 'won the Game by forfeit!';

    /**
     * @throws GameResultException
     */
    public function __construct(
        readonly private ?string $winner = null,
        readonly array $winningFields = [],
        readonly bool $forfeit = false,
    )
    {
        $this->validateInputs();
    }

    public function getMessage(): string
    {
        if (isset($this->winner)) {
            return $this->winner . ' ' . ($this->forfeit ? $this::MESSAGE_FORFEIT : $this::MESSAGE_WIN);
        }
        return $this::MESSAGE_DRAW;
    }

    public function getDetails(): array
    {
        return [
            'winnerName' => $this->winner,
            'winningFields' => array_map(fn($field) => (string) $field, $this->winningFields),
            'forfeit' => $this->forfeit,
        ];
    }

    public function toArray(): array
    {
        return $this->getDetails();
    }

    /**
     * @throws GameResultException
     */
    private function validateInputs(): void
    {
        if (
            (isset($this->winner) && ($this->winningFields === [] && !$this->forfeit))
            || ($this->winningFields !== [] && !isset($this->winner))
            || ($this->forfeit && !isset($this->winner))
            || ($this->forfeit && $this->winningFields !== [])
            || ($this->winningFields !== [] && count($this->winningFields) !== 3)
        ) {
            throw new GameResultException(GameResultException::MESSAGE_INCORRECT_PARAMETER);
        }
    }
}
