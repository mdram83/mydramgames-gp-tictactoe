<?php

namespace Tests\Extensions\Core;

use MyDramGames\Core\Exceptions\GameMoveException;
use MyDramGames\Games\TicTacToe\Extensions\Core\GameMoveFactoryTicTacToe;
use MyDramGames\Games\TicTacToe\Extensions\Core\GameMoveTicTacToe;
use MyDramGames\Utils\Player\Player;
use PHPUnit\Framework\TestCase;

class GameMoveFactoryTicTacToeTest extends TestCase
{
    protected GameMoveFactoryTicTacToe $factory;
    protected array $params = ['fieldKey' => 1];
    protected Player $player;

    public function setUp(): void
    {
        parent::setUp();
        $this->factory = new GameMoveFactoryTicTacToe();
        $this->player = $this->createMock(Player::class);
    }

    public function testThrowExceptionIfParamsMissingFieldKey(): void
    {
        $this->expectException(GameMoveException::class);
        $this->expectExceptionMessage(GameMoveException::MESSAGE_INVALID_MOVE_PARAMS);

        $this->factory->create($this->player, ['invalid-key' => 1]);
    }

    public function testThrowExceptionIfParamsMissFieldKeyValue(): void
    {
        $this->expectException(GameMoveException::class);
        $this->expectExceptionMessage(GameMoveException::MESSAGE_INVALID_MOVE_PARAMS);

        $this->factory->create($this->player, ['fieldKey' => null]);
    }

    public function testCreateConvertStringValueToInt(): void
    {
        $move = $this->factory->create($this->player, ['fieldKey' => '1']);
        $this->assertInstanceOf(GameMoveTicTacToe::class, $move);
    }

    public function testCreate(): void
    {
        $this->assertInstanceOf(GameMoveTicTacToe::class, $this->factory->create($this->player, $this->params));
    }
}
