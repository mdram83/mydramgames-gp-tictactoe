<?php

namespace Tests\Extensions\Utils;

use MyDramGames\Games\TicTacToe\Extensions\Utils\GameCharacterTicTacToe;
use MyDramGames\Games\TicTacToe\Extensions\Utils\GameCharacterTicTacToeCollectionPowered;
use MyDramGames\Utils\Exceptions\CollectionException;
use MyDramGames\Utils\Player\Player;
use PHPUnit\Framework\TestCase;

class GameCharacterTicTacToeCollectionPoweredTest extends TestCase
{
    protected array $players;
    protected array $characters;

    public function setUp(): void
    {
        $playerOne = $this->createMock(Player::class);
        $playerOne->method('getId')->willReturn(1);
        $playerOne->method('getName')->willReturn('Player 1');
        $playerTwo = $this->createMock(Player::class);
        $playerTwo->method('getId')->willReturn(2);
        $playerTwo->method('getName')->willReturn('Player 2');

        $this->players = [$playerOne, $playerTwo];
        $this->characters = [
            new GameCharacterTicTacToe('x', $this->players[0]),
            new GameCharacterTicTacToe('o', $this->players[1]),
        ];
    }

    public function testThrowExceptionWhenAddingWithDuplicatedCharacter(): void
    {
        $this->expectException(CollectionException::class);
        $this->expectExceptionMessage(CollectionException::MESSAGE_DUPLICATE);

        $collection = new GameCharacterTicTacToeCollectionPowered();
        $collection->add(new GameCharacterTicTacToe('x', $this->players[0]));
        $collection->add(new GameCharacterTicTacToe('x', $this->players[1]));
    }

    public function testCreateWithCorrectSetup(): void
    {
        $collection = new GameCharacterTicTacToeCollectionPowered(null, $this->characters);
        $expected = [
            $this->characters[0]->getName() => $this->characters[0]->getPlayer(),
            $this->characters[1]->getName() => $this->characters[1]->getPlayer(),
        ];
        $results = $collection->toArray();


        $this->assertEquals($expected['x']->getId(), $results['x']->getPlayer()->getId());
        $this->assertEquals($expected['o']->getId(), $results['o']->getPlayer()->getId());
    }

    public function testAddWithCorrectSetup(): void
    {
        $collection = new GameCharacterTicTacToeCollectionPowered();
        $collection->add($this->characters[0]);
        $collection->add($this->characters[1]);

        $this->assertEquals(2, $collection->count());
    }
}
