<?php

namespace MyDramGames\Games\TicTacToe\Extensions\Utils;

use MyDramGames\Utils\Exceptions\GameBoardException;
use MyDramGames\Utils\GameBoard\GameBoard;

class GameBoardTicTacToe implements GameBoard
{
    private array $fields = [
        '1' => null, '2' => null, '3' => null,
        '4' => null, '5' => null, '6' => null,
        '7' => null, '8' => null, '9' => null,
    ];

    private array $allowedValues = ['x', 'o'];

    public function toJson(): string
    {
        return json_encode($this->fields);
    }

    public function setFromJson(string $jsonBoard): void
    {
        $this->fields = json_decode($jsonBoard, true);
    }

    /**
     * @throws GameBoardException
     */
    public function setFieldValue(string $fieldId, string $value): void
    {
        if (!in_array($value, $this->allowedValues)) {
            throw new GameBoardException(GameBoardException::MESSAGE_INVALID_FIELD_VALUE);
        }

        if (!in_array($fieldId, array_keys($this->fields))) {
            throw new GameBoardException(GameBoardException::MESSAGE_INVALID_FIELD_ID);
        }

        if (isset($this->fields[$fieldId])) {
            throw new GameBoardException(GameBoardException::MESSAGE_FIELD_ALREADY_SET);
        }

        $this->fields[$fieldId] = $value;
    }

    public function getFieldValue(string $fieldId): ?string
    {
        return $this->fields[$fieldId];
    }
}
