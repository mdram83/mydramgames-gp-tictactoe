<?php

namespace Tests\Extensions\Core;

use MyDramGames\Core\GameBox\GameBox;
use MyDramGames\Core\GameBox\GameBoxGeneric;
use MyDramGames\Core\GameInvite\GameInvite;
use MyDramGames\Core\GameInvite\GameInviteGeneric;
use MyDramGames\Core\GameOption\GameOptionCollection;
use MyDramGames\Core\GameOption\GameOptionCollectionPowered;
use MyDramGames\Core\GameOption\GameOptionConfigurationCollectionPowered;
use MyDramGames\Core\GameOption\GameOptionConfigurationGeneric;
use MyDramGames\Core\GameOption\GameOptionValueCollection;
use MyDramGames\Core\GameOption\GameOptionValueCollectionPowered;
use MyDramGames\Core\GameOption\Values\GameOptionValueAutostartGeneric;
use MyDramGames\Core\GameOption\Values\GameOptionValueForfeitAfterGeneric;
use MyDramGames\Core\GameOption\Values\GameOptionValueNumberOfPlayersGeneric;
use MyDramGames\Core\GamePlay\Services\GamePlayServicesProviderGeneric;
use MyDramGames\Core\GamePlay\Storage\GamePlayStorageFactoryInMemory;
use MyDramGames\Core\GameRecord\GameRecordCollectionPowered;
use MyDramGames\Games\TicTacToe\Extensions\Core\GameIndexTicTacToe;
use MyDramGames\Games\TicTacToe\Extensions\Core\GameMoveTicTacToe;
use MyDramGames\Games\TicTacToe\Extensions\Core\GamePlayTicTacToe;
use MyDramGames\Games\TicTacToe\Extensions\Core\GameSetupTicTacToe;
use MyDramGames\Utils\Php\Collection\CollectionEnginePhpArray;
use MyDramGames\Utils\Player\Player;
use MyDramGames\Utils\Player\PlayerCollectionPowered;
use PHPUnit\Framework\TestCase;
use Tests\TestingHelper;

class GameIndexTicTacToeTest extends TestCase
{
    protected GameIndexTicTacToe $index;

    protected GameOptionCollection $optionsHandler;
    protected GameOptionValueCollection $valuesHandler;

    public function setUp(): void
    {
        $this->index = new GameIndexTicTacToe(
            new GameOptionCollectionPowered(),
            new GameOptionValueCollectionPowered(),
            new GamePlayStorageFactoryInMemory(),
            new GamePlayServicesProviderGeneric(
                new CollectionEnginePhpArray(),
                new PlayerCollectionPowered(),
                TestingHelper::getGameRecordFactory(),
                new GameRecordCollectionPowered()
            ),
        );
    }

    protected function getGameInvite(): GameInvite
    {
        $setup = new GameSetupTicTacToe(new GameOptionCollectionPowered(), new GameOptionValueCollectionPowered());
        $box = new GameBoxGeneric('tictactoe', 'Tic-Tac-Toe', $setup, true, false, null, null, null);
        $configurations = new GameOptionConfigurationCollectionPowered(null, [
            new GameOptionConfigurationGeneric('numberOfPlayers', GameOptionValueNumberOfPlayersGeneric::Players002),
            new GameOptionConfigurationGeneric('autostart', GameOptionValueAutostartGeneric::Disabled),
            new GameOptionConfigurationGeneric('forfeitAfter', GameOptionValueForfeitAfterGeneric::Disabled),
        ]);

        $invite = new GameInviteGeneric(1, $box, $configurations, new PlayerCollectionPowered());

        $playerOne = $this->createMock(Player::class);
        $playerOne->method('getId')->willReturn(1);
        $playerOne->method('getName')->willReturn('Player 1');

        $playerTwo = $this->createMock(Player::class);
        $playerTwo->method('getId')->willReturn(2);
        $playerTwo->method('getName')->willReturn('Player 2');

        $invite->addPlayer($playerOne, true);
        $invite->addPlayer($playerTwo);

        return $invite;
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(GameIndexTicTacToe::class, $this->index);
    }

    public function testGetSlug(): void
    {
        $this->assertNotEquals('', $this->index->getSlug());
    }

    public function testGetGameSetup(): void
    {
        $this->assertInstanceOf(GameSetupTicTacToe::class, $this->index->getGameSetup());
    }

    public function testGetGameBox(): void
    {
        $this->assertInstanceOf(GameBox::class, $this->index->getGameBox());
    }

    public function testCreateGameMove(): void
    {
        $this->assertInstanceOf(GameMoveTicTacToe::class, $this->index->createGameMove(
            $this->createMock(Player::class),
            ['fieldKey' => 1]
        ));
    }

    public function testCreateGamePlay(): void
    {
        $invite = $this->getGameInvite();
        $this->assertInstanceOf(GamePlayTicTacToe::class, $this->index->createGamePlay($invite));
    }
}
