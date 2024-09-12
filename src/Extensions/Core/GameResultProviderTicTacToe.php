<?php

namespace MyDramGames\Games\TicTacToe\Extensions\Core;

use MyDramGames\Core\Exceptions\GameResultException;
use MyDramGames\Core\Exceptions\GameResultProviderException;
use MyDramGames\Core\GameInvite\GameInvite;
use MyDramGames\Core\GameRecord\GameRecordCollection;
use MyDramGames\Core\GameResult\GameResult;
use MyDramGames\Core\GameResult\GameResultProvider;
use MyDramGames\Core\GameResult\GameResultProviderBase;
use MyDramGames\Games\TicTacToe\Extensions\Utils\GameBoardTicTacToe;
use MyDramGames\Games\TicTacToe\Extensions\Utils\GameCharacterTicTacToeCollectionPowered;
use MyDramGames\Utils\Exceptions\CollectionException;
use MyDramGames\Utils\Exceptions\GameBoardException;

class GameResultProviderTicTacToe extends GameResultProviderBase implements GameResultProvider
{
    private const int MOVES_PREDICTION = 2;

    private GameCharacterTicTacToeCollectionPowered $characters;

    private array $winningFields = [];
    private ?string $winningValue = null;

    private bool $resultProvided = false;
    private bool $recordsCreated = false;

    /**
     * @throws GameResultProviderException|GameResultException|GameBoardException|CollectionException
     */
    public function getResult(mixed $data): ?GameResult
    {
        if ($this->resultProvided) {
            throw new GameResultProviderException(GameResultProviderException::MESSAGE_RESULTS_ALREADY_SET);
        }

        $this->validateData($data);

        $this->characters = $data['characters'];

        if (isset($data['forfeitCharacter'])) {
            return $this->getForfeitResult($data['forfeitCharacter']);
        } else {
            return $this->getWinResult($data['board'], $data['nextMoveCharacterName']);
        }
    }

    /**
     * @throws GameResultException
     * @throws GameBoardException
     * @throws CollectionException
     */
    private function getWinResult(GameBoardTicTacToe $board, string $nextMoveCharacterName): ?GameResult
    {
        if ($this->checkWin($board)) {
            $this->resultProvided = true;
            return new GameResultTicTacToe(
                $this->characters->getOne($this->winningValue)->getPlayer()->getName(),
                $this->winningFields
            );
        }

        if ($this->checkDraw(clone $board, $nextMoveCharacterName)) {
            $this->resultProvided = true;
            return new GameResultTicTacToe();
        }

        return null;
    }

    /**
     * @throws GameResultException|CollectionException
     */
    private function getForfeitResult(string $forfeitCharacterName): GameResult
    {
        $this->resultProvided = true;
        $winningCharacterName = $this->getNextCharacterName($forfeitCharacterName);
        $this->winningValue = $winningCharacterName;
        $winningPlayerName = $this->characters->getOne($winningCharacterName)->getPlayer()->getName();

        return new GameResultTicTacToe($winningPlayerName, [], true);
    }

    /**
     * @throws GameResultProviderException
     * @throws CollectionException
     */
    public function createGameRecords(GameInvite $gameInvite): GameRecordCollection
    {
        if (!$this->resultProvided) {
            throw new GameResultProviderException(GameResultProviderException::MESSAGE_RESULT_NOT_SET);
        }

        if ($this->recordsCreated) {
            throw new GameResultProviderException(GameResultProviderException::MESSAGE_RECORD_ALREADY_SET);
        }

        $this->gameRecordCollection->reset();

        foreach (['x', 'o'] as $characterName) {
            $record = $this->gameRecordFactory->create(
                $gameInvite,
                $this->characters->getOne($characterName)->getPlayer(),
                $this->winningValue === $characterName,
                ['character' => $characterName]
            );
            $this->gameRecordCollection->add($record);
        }

        $this->recordsCreated = true;

        return $this->gameRecordCollection;
    }

    /**
     * @throws GameResultProviderException
     */
    private function validateData(mixed $data): void
    {
        if (isset($data['forfeitCharacter'])) {
            $this->validateForfeitData($data);
        } else {
            $this->validateMoveData($data);
        }
    }

    /**
     * @throws GameResultProviderException
     */
    private function validateMoveData(mixed $data): void
    {
        if (
            !isset($data['board'])
            || !($data['board'] instanceof GameBoardTicTacToe)
            || !isset($data['characters'])
            || !($data['characters'] instanceof GameCharacterTicTacToeCollectionPowered)
            || !isset($data['nextMoveCharacterName'])
            || !in_array($data['nextMoveCharacterName'], ['x', 'o'])
        ) {
            throw new GameResultProviderException(GameResultProviderException::MESSAGE_INCORRECT_DATA_PARAMETER);
        }
    }

    /**
     * @throws GameResultProviderException
     */
    private function validateForfeitData(mixed $data): void
    {
        if (
            !isset($data['characters'])
            || !($data['characters'] instanceof GameCharacterTicTacToeCollectionPowered)
            || !isset($data['forfeitCharacter'])
            || !in_array($data['forfeitCharacter'], ['x', 'o'])
        ) {
            throw new GameResultProviderException(GameResultProviderException::MESSAGE_INCORRECT_DATA_PARAMETER);
        }
    }

    private function checkWin(GameBoardTicTacToe $board): bool
    {
        for ($i = 1; $i <= 7; $i += 3) {
            if ($this->checkWinForKeys($board, [$i, $i + 1, $i + 2])) {
                return true;
            }
        }

        for ($i = 1; $i < 4; $i++) {
            if ($this->checkWinForKeys($board, [$i, $i + 3, $i + 6])) {
                return true;
            }
        }

        if ($this->checkWinForKeys($board, [1, 5, 9]) || $this->checkWinForKeys($board, [3, 5, 7])) {
            return true;
        }

        return false;
    }

    private function checkWinForKeys(GameBoardTicTacToe $board, array $keys): bool
    {
        $values = array_unique(array_values([
            $board->getFieldValue((string) $keys[0]),
            $board->getFieldValue((string) $keys[1]),
            $board->getFieldValue((string) $keys[2]),
        ]));

        if (count($values) === 1 && $values[0] !== null) {
            $this->winningFields = $keys;
            $this->winningValue = $values[0];
            return true;
        }

        return false;
    }

    /**
     * @throws GameBoardException
     */
    private function checkDraw(GameBoardTicTacToe $board, string $nextCharacterName): bool
    {
        $remainingKeys = array_keys(
            array_filter(json_decode($board->toJson(), true), fn($field) => $field === null)
        );

        if (count($remainingKeys) > $this::MOVES_PREDICTION) {
            return false;
        }

        foreach ($remainingKeys as $fieldKey) {

            $updatedBoard = clone $board;
            $updatedBoard->setFieldValue((string) $fieldKey, $nextCharacterName);

            if ($this->checkWin($updatedBoard)) {
                $this->winningFields = [];
                $this->winningValue = null;
                return false;
            }

            if (!$this->checkDraw(clone $updatedBoard, $this->getNextCharacterName($nextCharacterName))) {
                return false;
            }
        }

        return true;
    }

    private function getNextCharacterName(string $currentCharacterName): string
    {
        return $currentCharacterName === 'x' ? 'o' : 'x';
    }
}
