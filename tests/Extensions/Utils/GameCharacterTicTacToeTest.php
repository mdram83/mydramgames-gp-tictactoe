<?php

namespace Tests\Extensions\Utils;

use MyDramGames\Games\TicTacToe\Extensions\Utils\GameCharacterTicTacToe;
use MyDramGames\Utils\Exceptions\GameCharacterException;
use MyDramGames\Utils\Player\Player;
use PHPUnit\Framework\TestCase;

class GameCharacterTicTacToeTest extends TestCase
{
    protected Player $player;

    public function setUp(): void
    {
        parent::setUp();
        $this->player = $this->createMock(Player::class);
    }

    public function testThrowExceptionWhenCreatingWithWrongName(): void
    {
        $this->expectException(GameCharacterException::class);
        $this->expectExceptionMessage(GameCharacterException::MESSAGE_WRONG_NAME);

        new GameCharacterTicTacToe('1', $this->player);
    }

    public function testGetXName(): void
    {
        $character = new GameCharacterTicTacToe('x', $this->player);
        $this->assertEquals('x', $character->getName());
    }

    public function testGetOName(): void
    {
        $character = new GameCharacterTicTacToe('o', $this->player);
        $this->assertEquals('o', $character->getName());
    }

    public function testGetPlayer(): void
    {
        $character = new GameCharacterTicTacToe('o', $this->player);
        $this->assertSame($this->player, $character->getPlayer());
    }
}
