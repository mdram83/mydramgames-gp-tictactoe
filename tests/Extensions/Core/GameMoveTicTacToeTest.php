<?php

namespace Tests\Extensions\Core;

use MyDramGames\Core\Exceptions\GameMoveException;
use MyDramGames\Core\GameMove\GameMove;
use MyDramGames\Games\TicTacToe\Extensions\Core\GameMoveTicTacToe;
use MyDramGames\Utils\Player\Player;
use PHPUnit\Framework\TestCase;

class GameMoveTicTacToeTest extends TestCase
{
    protected GameMoveTicTacToe $move;
    protected Player $player;
    protected int $fieldKey = 1;

    public function setUp(): void
    {
        parent::setUp();
        $this->player = $this->createMock(Player::class);
        $this->move = $this->createMove();
    }

    protected function createMove(int $overwriteKey = null): GameMoveTicTacToe
    {
        return new GameMoveTicTacToe($this->player, $overwriteKey ?? $this->fieldKey);
    }

    public function testInstance(): void
    {
        $this->assertInstanceOf(GameMove::class, $this->move);
    }

    public function testThrowExceptionWithInvalidFieldKey(): void
    {
        $this->expectException(GameMoveException::class);
        $this->expectExceptionMessage(GameMoveException::MESSAGE_INVALID_MOVE_PARAMS);

        $this->move = $this->createMove(10);
    }

    public function testGetPlayer(): void
    {
        $this->assertSame($this->player, $this->move->getPlayer());
    }

    public function testGetDetails(): void
    {
        $expected = ['fieldKey' => $this->fieldKey];
        $this->assertEquals($expected, $this->move->getDetails());
    }

    public function testCreateThrowExceptionIfParamsMissingFieldKey(): void
    {
        $this->expectException(GameMoveException::class);
        $this->expectExceptionMessage(GameMoveException::MESSAGE_INVALID_MOVE_PARAMS);

        GameMoveTicTacToe::create($this->player, ['invalid-key' => 1]);
    }

    public function testCreateThrowExceptionIfParamsMissFieldKeyValue(): void
    {
        $this->expectException(GameMoveException::class);
        $this->expectExceptionMessage(GameMoveException::MESSAGE_INVALID_MOVE_PARAMS);

        GameMoveTicTacToe::create($this->player, ['fieldKey' => null]);
    }

    public function testCreateConvertStringValueToInt(): void
    {
        $move = GameMoveTicTacToe::create($this->player, ['fieldKey' => '1']);
        $this->assertInstanceOf(GameMoveTicTacToe::class, $move);
    }

    public function testCreate(): void
    {
        $this->assertInstanceOf(
            GameMoveTicTacToe::class,
            GameMoveTicTacToe::create($this->player, ['fieldKey' => 1])
        );
    }
}
