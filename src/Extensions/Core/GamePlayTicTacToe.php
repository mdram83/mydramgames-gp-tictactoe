<?php

namespace MyDramGames\Games\TicTacToe\Extensions\Core;

use MyDramGames\Core\Exceptions\GamePlayException;
use MyDramGames\Core\Exceptions\GamePlayStorageException;
use MyDramGames\Core\Exceptions\GameResultException;
use MyDramGames\Core\Exceptions\GameResultProviderException;
use MyDramGames\Core\GameMove\GameMove;
use MyDramGames\Core\GamePlay\GamePlay;
use MyDramGames\Core\GamePlay\GamePlayStorableBase;
use MyDramGames\Games\TicTacToe\Extensions\Utils\GameBoardTicTacToe;
use MyDramGames\Games\TicTacToe\Extensions\Utils\GameCharacterTicTacToe;
use MyDramGames\Games\TicTacToe\Extensions\Utils\GameCharacterTicTacToeCollectionPowered;
use MyDramGames\Utils\Exceptions\CollectionException;
use MyDramGames\Utils\Exceptions\GameBoardException;
use MyDramGames\Utils\Exceptions\GameCharacterException;
use MyDramGames\Utils\Player\Player;

class GamePlayTicTacToe extends GamePlayStorableBase implements GamePlay
{
    protected GameCharacterTicTacToeCollectionPowered $characters;
    protected GameBoardTicTacToe $board;
    protected ?GameResultTicTacToe $result = null;

    protected const ?string GAME_MOVE_CLASS = GameMoveTicTacToe::class;

    /**
     * @throws GamePlayException
     * @throws CollectionException|GameResultException|GameResultProviderException
     */
    public function handleMove(GameMove $move): void
    {
        $this->validateNotFinished();
        $this->validateMove($move);

        try {
            $this->board->setFieldValue(
                $move->getDetails()['fieldKey'],
                $this->getPlayerCharacterName($move->getPlayer())
            );

            $this->setActivePlayer($this->getNextPlayer($move->getPlayer()));
            $this->saveData();

            $resultProvider = new GameResultProviderTicTacToe(
                $this->gamePlayServicesProvider->getGameRecordFactory(),
                $this->gamePlayServicesProvider->getGameRecordCollection(),
            );

            if ($this->result = $resultProvider->getResult([
                'board' => $this->board,
                'characters' => $this->characters,
                'nextMoveCharacterName' => $this->getPlayerCharacterName($this->activePlayer),
            ])) {
                $resultProvider->createGameRecords($this->getGameInvite());
                $this->storage->setFinished();
            }

        } catch (GameBoardException) {
            throw new GamePlayException(GamePlayException::MESSAGE_INCOMPATIBLE_MOVE);

        } catch (GamePlayStorageException) {
            throw new GamePlayException(GamePlayException::MESSAGE_STORAGE_INCORRECT);

        }
    }

    /**
     * @throws GamePlayException
     * @throws GameResultProviderException|CollectionException|GameResultException
     */
    public function handleForfeit(Player $player): void
    {
        $this->validateGamePlayer($player);
        $this->validateNotFinished();

        try {

            $resultProvider = new GameResultProviderTicTacToe(
                $this->gamePlayServicesProvider->getGameRecordFactory(),
                $this->gamePlayServicesProvider->getGameRecordCollection(),
            );
            $this->result = $resultProvider->getResult(
                ['forfeitCharacter' => $this->getPlayerCharacterName($player), 'characters' => $this->characters]
            );
            $resultProvider->createGameRecords($this->getGameInvite());
            $this->storage->setFinished();

        } catch (GamePlayStorageException) {
            throw new GamePlayException(GamePlayException::MESSAGE_STORAGE_INCORRECT);

        } catch (GameBoardException $e) {
            throw new GamePlayException(GamePlayException::MESSAGE_INCOMPATIBLE_MOVE);

        }
    }

    public function getSituation(Player $player): array
    {
        $resultArray = isset($this->result)
            ? ['result' => ['details' => $this->result->getDetails(), 'message' => $this->result->getMessage()]]
            : [];

        return array_merge(
            [
                'players' => [
                    $this->storage->getGameInvite()->getPlayers()->clone()->pullFirst()->getName(),
                    $this->storage->getGameInvite()->getPlayers()->clone()->pullLast()->getName(),
                ],
                'activePlayer' => $this->activePlayer->getName(),
                'characters' => [
                    'x' => $this->characters->getOne('x')->getPlayer()->getName(),
                    'o' => $this->characters->getOne('o')->getPlayer()->getName(),
                ],
                'board' => json_decode($this->board->toJson(), true),
                'isFinished' => $this->isFinished(),
            ],
            $resultArray
        );
    }

    /**
     * @throws GamePlayStorageException|CollectionException|GameCharacterException
     */
    protected function initialize(): void
    {
        $players = $this->storage->getGameInvite()->getPlayers()->clone();
        $playerX = $players->pullFirst();
        $playerO = $players->pullLast();

        $this->setActivePlayer($playerX);
        $this->setCharacters($playerX, $playerO);
        $this->setBoard();
        $this->saveData();
    }

    /**
     * @throws GamePlayStorageException|CollectionException
     */
    protected function saveData(): void
    {
        $this->storage->setGameData([
            'activePlayerId' => $this->activePlayer->getId(),
            'characters' => [
                'x' => $this->characters->getOne('x')->getPlayer()->getId(),
                'o' => $this->characters->getOne('o')->getPlayer()->getId(),
            ],
            'board' => $this->board->toJson(),
        ]);
    }

    /**
     * @throws GamePlayException
     * @throws GameCharacterException|GamePlayStorageException|CollectionException
     */
    protected function loadData(): void
    {
        $data = $this->storage->getGameData();

        $this->setActivePlayer($this->getPlayers()->getOne($data['activePlayerId']));
        $this->setCharacters(
            $this->getPlayers()->getOne($data['characters']['x']),
            $this->getPlayers()->getOne($data['characters']['o'])
        );
        $this->setBoard($data['board']);
    }

    private function setActivePlayer(Player $player): void
    {
        $this->activePlayer = $player;
    }

    /**
     * @throws GameCharacterException|CollectionException
     */
    private function setCharacters(Player $playerX, Player $playerO): void
    {
        $this->characters = new GameCharacterTicTacToeCollectionPowered(
            null,
            [new GameCharacterTicTacToe('x', $playerX), new GameCharacterTicTacToe('o', $playerO)],
        );
    }

    private function setBoard(?string $json = null): void
    {
        $this->board = new GameBoardTicTacToe();

        if (isset($json)) {
            $this->board->setFromJson($json);
        }
    }

    /**
     * @throws CollectionException
     */
    private function getPlayerCharacterName(Player $player): string
    {
        return $this->characters
            ->filter(fn($value) => $value->getPlayer()->getId() === $player->getId())
            ->pullFirst()
            ->getName();
    }

    /**
     * @throws CollectionException
     */
    private function getNextPlayer(Player $currentPlayer): Player
    {
        return $this->characters
            ->filter(fn($value) => $value->getPlayer()->getId() !== $currentPlayer->getId())
            ->pullFirst()
            ->getPlayer();
    }

    protected function configureGamePlayServices(): void
    {

    }

    protected function runConfigurationAfterHooks(): void
    {

    }
}
