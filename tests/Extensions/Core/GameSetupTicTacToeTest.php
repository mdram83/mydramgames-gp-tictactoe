<?php

namespace Tests\Extensions\Core;

use MyDramGames\Core\GameOption\GameOptionCollectionPowered;
use MyDramGames\Core\GameOption\GameOptionValueCollectionPowered;
use MyDramGames\Core\GameOption\Values\GameOptionValueAutostartGeneric;
use MyDramGames\Core\GameOption\Values\GameOptionValueForfeitAfterGeneric;
use MyDramGames\Games\TicTacToe\Extensions\Core\GameSetupTicTacToe;
use PHPUnit\Framework\TestCase;

class GameSetupTicTacToeTest extends TestCase
{
    protected GameSetupTicTacToe $setup;

    public function setUp(): void
    {
        $this->setup = new GameSetupTicTacToe(
            new GameOptionCollectionPowered(),
            new GameOptionValueCollectionPowered()
        );
    }

    public function testGetAllOptions(): void
    {
        $allOptions = $this->setup->getAllOptions();
        $numberOfPlayers = $allOptions->getOne('numberOfPlayers');
        $autostart = $allOptions->getOne('autostart');
        $forfeitAfter = $allOptions->getOne('forfeitAfter');

        $this->assertEquals(3, $allOptions->count());

        $this->assertEquals(1, $numberOfPlayers->getAvailableValues()->count());
        $this->assertEquals(2, $numberOfPlayers->getAvailableValues()->pullFirst()->getValue());
        $this->assertEquals(2, $numberOfPlayers->getDefaultValue()->getValue());

        $this->assertEquals(2, $autostart->getAvailableValues()->count());
        $this->assertEquals(GameOptionValueAutostartGeneric::Enabled->value, $autostart->getAvailableValues()->pullFirst()->getValue());
        $this->assertEquals(GameOptionValueAutostartGeneric::Enabled->value, $autostart->getDefaultValue()->getValue());

        $this->assertEquals(2, $forfeitAfter->getAvailableValues()->count());
        $this->assertEquals(GameOptionValueForfeitAfterGeneric::Disabled->value, $forfeitAfter->getAvailableValues()->pullFirst()->getValue());
        $this->assertEquals(GameOptionValueForfeitAfterGeneric::Disabled->value, $forfeitAfter->getDefaultValue()->getValue());
    }
}
