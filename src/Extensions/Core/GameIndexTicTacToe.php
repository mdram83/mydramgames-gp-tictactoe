<?php

namespace MyDramGames\Games\TicTacToe\Extensions\Core;

use MyDramGames\Core\Exceptions\GameOptionException;
use MyDramGames\Core\GameIndex\GameIndex;
use MyDramGames\Core\GameIndex\GameIndexStorableBase;
use MyDramGames\Core\GameMove\GameMove;
use MyDramGames\Core\GameMove\GameMoveFactory;
use MyDramGames\Core\GameSetup\GameSetup;
use MyDramGames\Utils\Exceptions\CollectionException;
use MyDramGames\Utils\Player\Player;

class GameIndexTicTacToe extends GameIndexStorableBase implements GameIndex
{
    public const string SLUG = 'tic-tac-toe';
    public const string NAME = 'Tic Tac Toe';
    public const ?string DESCRIPTION = 'Famous Tic Tac Toe game that you can now play with friends online!';
    public const ?int DURATION_IN_MINUTES = 1;
    public const ?int MIN_PLAYER_AGE = 4;
    public const bool IS_ACTIVE = true;
    public const bool IS_PREMIUM = false;

    protected const string GAMEPLAY_CLASSNAME = GamePlayTicTacToe::class;

    protected GameMoveFactory $gameMoveFactory;

    protected function configureGameIndex(): void
    {
        $this->gameMoveFactory = new GameMoveFactoryTicTacToe();
    }

    /**
     * @inheritDoc
     * @throws GameOptionException|CollectionException
     */
    public function getGameSetup(): GameSetup
    {
        return new GameSetupTicTacToe(
            $this->optionsHandler->clone(),
            $this->valuesHandler->clone(),
        );
    }

    /**
     * @inheritDoc
     */
    public function createGameMove(Player $player, array $inputs): GameMove
    {
        return $this->gameMoveFactory->create($player, $inputs);
    }
}
