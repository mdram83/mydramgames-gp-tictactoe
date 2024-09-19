<?php

namespace Tests\Extensions\Core;

use MyDramGames\Core\Exceptions\GamePlayException;
use MyDramGames\Core\GameBox\GameBoxGeneric;
use MyDramGames\Core\GameInvite\GameInvite;
use MyDramGames\Core\GameInvite\GameInviteGeneric;
use MyDramGames\Core\GameMove\GameMove;
use MyDramGames\Core\GameOption\GameOptionCollectionPowered;
use MyDramGames\Core\GameOption\GameOptionConfigurationCollectionPowered;
use MyDramGames\Core\GameOption\GameOptionConfigurationGeneric;
use MyDramGames\Core\GameOption\GameOptionValueCollectionPowered;
use MyDramGames\Core\GameOption\Values\GameOptionValueAutostartGeneric;
use MyDramGames\Core\GameOption\Values\GameOptionValueForfeitAfterGeneric;
use MyDramGames\Core\GameOption\Values\GameOptionValueNumberOfPlayersGeneric;
use MyDramGames\Core\GamePlay\GamePlay;
use MyDramGames\Core\GamePlay\Services\GamePlayServicesProviderGeneric;
use MyDramGames\Core\GamePlay\Storage\GamePlayStorage;
use MyDramGames\Core\GamePlay\Storage\GamePlayStorageInMemory;
use MyDramGames\Core\GameRecord\GameRecordCollectionPowered;
use MyDramGames\Games\TicTacToe\Extensions\Core\GameMoveTicTacToe;
use MyDramGames\Games\TicTacToe\Extensions\Core\GamePlayTicTacToe;
use MyDramGames\Games\TicTacToe\Extensions\Core\GameResultTicTacToe;
use MyDramGames\Games\TicTacToe\Extensions\Core\GameSetupTicTacToe;
use MyDramGames\Games\TicTacToe\Extensions\Utils\GameBoardTicTacToe;
use MyDramGames\Utils\Php\Collection\CollectionEnginePhpArray;
use MyDramGames\Utils\Player\Player;
use MyDramGames\Utils\Player\PlayerCollection;
use MyDramGames\Utils\Player\PlayerCollectionPowered;
use PHPUnit\Framework\TestCase;
use Tests\TestingHelper;

class GamePlayTicTacToeTest extends TestCase
{
    protected GamePlayTicTacToe $play;

    protected PlayerCollection $players;
    protected GameInvite $invite;
    protected GamePlayStorage $storage;

    public function setUp(): void
    {
        $this->players = $this->getPlayers();
        $this->invite = $this->getGameInvite();
        $this->storage = $this->getGamePlayStorage();

        $this->play = $this->getGamePlay();
    }

    protected function getPlayers(): PlayerCollectionPowered
    {
        $playerOne = $this->createMock(Player::class);
        $playerOne->method('getId')->willReturn(1);
        $playerOne->method('getName')->willReturn('Player 1');

        $playerTwo = $this->createMock(Player::class);
        $playerTwo->method('getId')->willReturn(2);
        $playerTwo->method('getName')->willReturn('Player 2');

        return new PlayerCollectionPowered(null, [$playerOne, $playerTwo]);
    }

    protected function getGameInvite(): GameInvite
    {
        $setup = new GameSetupTicTacToe(new GameOptionCollectionPowered(), new GameOptionValueCollectionPowered());
        $box = new GameBoxGeneric(
            'tictactoe',
            'Tic-Tac-Toe',
            $setup,
            GamePlayTicTacToe::class,
            GameMoveTicTacToe::class,
            false,
            false,
            null,
            null,
            null,
        );
        $configurations = new GameOptionConfigurationCollectionPowered(null, [
            new GameOptionConfigurationGeneric('numberOfPlayers', GameOptionValueNumberOfPlayersGeneric::Players002),
            new GameOptionConfigurationGeneric('autostart', GameOptionValueAutostartGeneric::Disabled),
            new GameOptionConfigurationGeneric('forfeitAfter', GameOptionValueForfeitAfterGeneric::Disabled),
        ]);

        $invite = new GameInviteGeneric(1, $box, $configurations, $this->players->clone());
        $invite->addPlayer($this->players->getOne(1), true);
        $invite->addPlayer($this->players->getOne(2));

        return $invite;
    }

    protected function getGamePlayStorage(): GamePlayStorage
    {
        return new GamePlayStorageInMemory($this->invite);
    }

    protected function getGamePlay(): GamePlayTicTacToe
    {
        return new GamePlayTicTacToe($this->storage, new GamePlayServicesProviderGeneric(
            new CollectionEnginePhpArray(),
            new PlayerCollectionPowered(),
            TestingHelper::getGameRecordFactory(),
            new GameRecordCollectionPowered(),
        ));
    }

    protected function getMove(?Player $overwritePlayer = null, int $fieldKey = 1): GameMoveTicTacToe
    {
        return new GameMoveTicTacToe($overwritePlayer ?? $this->players->getOne(1), $fieldKey);
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(GamePlay::class, $this->play);
    }

    public function testHandleMoveThrowExceptionWhenFinished(): void
    {
        $this->expectException(GamePlayException::class);
        $this->expectExceptionMessage(GamePlayException::MESSAGE_MOVE_ON_FINISHED_GAME);

        $this->storage->setFinished();
        $this->play = $this->getGamePlay();
        $this->play->handleMove($this->getMove());
    }

    public function testHandleMoveThrowExceptionWhenNotCurrentPlayer(): void
    {
        $this->expectException(GamePlayException::class);
        $this->expectExceptionMessage(GamePlayException::MESSAGE_NOT_CURRENT_PLAYER);

        $this->play->handleMove($this->getMove($this->players->getOne(2)));
    }

    public function testHandleMoveThrowExceptionIncompatibleMove(): void
    {
        $this->expectException(GamePlayException::class);
        $this->expectExceptionMessage(GamePlayException::MESSAGE_INCOMPATIBLE_MOVE);

        $this->play->handleMove($this->createMock(GameMove::class));
    }

    public function testHandleMoveUpdateActivePlayerAndSavesDataInStorage(): void
    {
        $this->play->handleMove($this->getMove());
        $situation = $this->play->getSituation($this->players->getOne(1));
        $play = $this->getGamePlay();

        $this->assertEquals($situation, $play->getSituation($this->players->getOne(1)));
        $this->assertSame($this->players->getOne(2), $this->play->getActivePlayer());
    }

    public function testGetSituation(): void
    {
        $expected = [
            'players' => [$this->players->getOne(1)->getName(), $this->players->getOne(2)->getName()],
            'activePlayer' => $this->players->getOne(1)->getName(),
            'characters' => ['x' => $this->players->getOne(1)->getName(), 'o' => $this->players->getOne(2)->getName()],
            'board' => json_decode((new GameBoardTicTacToe())->toJson(), true),
            'isFinished' => false,
        ];

        $this->assertEquals($expected, $this->play->getSituation($this->players->getOne(1)));
        $this->assertEquals($expected, $this->play->getSituation($this->players->getOne(2)));
    }

    public function testHandleMoveThrowExceptionWhenSettingSameFieldTwice(): void
    {
        $this->expectException(GamePlayException::class);
        $this->expectExceptionMessage(GamePlayException::MESSAGE_INCOMPATIBLE_MOVE);

        $move = $this->getMove();
        $this->play->handleMove($move);
        $move = $this->getMove($this->players->getOne(2));
        $this->play->handleMove($move);
    }

    public function testIsFinishedFalse(): void
    {
        $this->assertFalse($this->play->isFinished());
    }

    public function testHandleForfeitThrowExceptionIfNotPlayer(): void
    {
        $this->expectException(GamePlayException::class);
        $this->expectExceptionMessage(GamePlayException::MESSAGE_NOT_PLAYER);

        $play = $this->getGamePlay();
        $play->handleForfeit($this->createMock(Player::class));
    }

    public function testHandleForfeitThrowExceptionIfFinished(): void
    {
        $this->expectException(GamePlayException::class);
        $this->expectExceptionMessage(GamePlayException::MESSAGE_MOVE_ON_FINISHED_GAME);

        $this->storage->setFinished();
        $this->play = $this->getGamePlay();
        $this->play->handleForfeit($this->players->getOne(1));
    }

    public function testHandleMoveWinSituation(): void
    {
        $this->play->handleMove($this->getMove(null, 1));
        $this->play->handleMove($this->getMove($this->players->getOne(2), 5));
        $this->play->handleMove($this->getMove(null, 2));
        $this->play->handleMove($this->getMove($this->players->getOne(2), 9));
        $this->play->handleMove($this->getMove(null, 3));

        $expectedResult = new GameResultTicTacToe($this->players->getOne(1)->getName(), [1, 2, 3]);
        $expected = [
            'players' => [$this->players->getOne(1)->getName(), $this->players->getOne(2)->getName()],
            'activePlayer' => $this->players->getOne(2)->getName(),
            'characters' => ['x' => $this->players->getOne(1)->getName(), 'o' => $this->players->getOne(2)->getName()],
            'board' => [1 => 'x', 2 => 'x', 3 => 'x', 4 => null, 5 => 'o', 6 => null, 7 => null, 8 => null, 9 => 'o'],
            'isFinished' => true,
            'result' => [
                'details' => $expectedResult->getDetails(),
                'message' => $expectedResult->getMessage(),
            ],
        ];

        $this->assertTrue($this->play->isFinished());
        $this->assertEquals($expected, $this->play->getSituation($this->players->getOne(1)));
        $this->assertEquals($expected, $this->play->getSituation($this->players->getOne(2)));
    }

    public function testHandleMoveDrawSituation(): void
    {
        $this->play->handleMove($this->getMove(null, 5));
        $this->play->handleMove($this->getMove($this->players->getOne(2), 3));
        $this->play->handleMove($this->getMove(null, 1));
        $this->play->handleMove($this->getMove($this->players->getOne(2), 9));
        $this->play->handleMove($this->getMove(null, 6));
        $this->play->handleMove($this->getMove($this->players->getOne(2), 4));
        $this->play->handleMove($this->getMove(null, 2));
        $this->play->handleMove($this->getMove($this->players->getOne(2), 8));

        $expectedResult = new GameResultTicTacToe();
        $expected = [
            'players' => [$this->players->getOne(1)->getName(), $this->players->getOne(2)->getName()],
            'activePlayer' => $this->players->getOne(1)->getName(),
            'characters' => ['x' => $this->players->getOne(1)->getName(), 'o' => $this->players->getOne(2)->getName()],
            'board' => [1 => 'x', 2 => 'x', 3 => 'o', 4 => 'o', 5 => 'x', 6 => 'x', 7 => null, 8 => 'o', 9 => 'o'],
            'isFinished' => true,
            'result' => [
                'details' => $expectedResult->getDetails(),
                'message' => $expectedResult->getMessage(),
            ],
        ];

        $this->assertTrue($this->play->isFinished());
        $this->assertEquals($expected, $this->play->getSituation($this->players->getOne(1)));
        $this->assertEquals($expected, $this->play->getSituation($this->players->getOne(2)));
    }

    public function testHandleForfeitSituation(): void
    {
        $this->play->handleForfeit($this->players->getOne(2));

        $expectedResult = new GameResultTicTacToe($this->players->getOne(1)->getName(), [], true);
        $expected = [
            'players' => [$this->players->getOne(1)->getName(), $this->players->getOne(2)->getName()],
            'activePlayer' => $this->players->getOne(1)->getName(),
            'characters' => ['x' => $this->players->getOne(1)->getName(), 'o' => $this->players->getOne(2)->getName()],
            'board' => [1 => null, 2 => null, 3 => null, 4 => null, 5 => null, 6 => null, 7 => null, 8 => null, 9 => null],
            'isFinished' => true,
            'result' => [
                'details' => $expectedResult->getDetails(),
                'message' => $expectedResult->getMessage(),
            ],
        ];

        $this->assertTrue($this->play->isFinished());
        $this->assertEquals($expected, $this->play->getSituation($this->players->getOne(1)));
        $this->assertEquals($expected, $this->play->getSituation($this->players->getOne(2)));
    }
}
