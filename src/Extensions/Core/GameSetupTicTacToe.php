<?php

namespace MyDramGames\Games\TicTacToe\Extensions\Core;

use MyDramGames\Core\GameOption\GameOptionTypeGeneric;
use MyDramGames\Core\GameOption\GameOptionValueCollection;
use MyDramGames\Core\GameOption\Options\GameOptionAutostartGeneric;
use MyDramGames\Core\GameOption\Options\GameOptionForfeitAfterGeneric;
use MyDramGames\Core\GameOption\Options\GameOptionNumberOfPlayersGeneric;
use MyDramGames\Core\GameOption\Values\GameOptionValueAutostartGeneric;
use MyDramGames\Core\GameOption\Values\GameOptionValueForfeitAfterGeneric;
use MyDramGames\Core\GameOption\Values\GameOptionValueNumberOfPlayersGeneric;
use MyDramGames\Core\GameSetup\GameSetup;
use MyDramGames\Core\GameSetup\GameSetupBase;

class GameSetupTicTacToe extends GameSetupBase implements GameSetup
{
    /**
     * @inheritDoc
     */
    protected function prepareDefaultOptions(GameOptionValueCollection $valuesHandler): array
    {
        return
            [
                new GameOptionNumberOfPlayersGeneric(
                    $valuesHandler->clone()->reset([
                        GameOptionValueNumberOfPlayersGeneric::Players002,
                    ]),
                    GameOptionValueNumberOfPlayersGeneric::Players002,
                    GameOptionTypeGeneric::Radio
                ),

                new GameOptionAutostartGeneric(
                    $valuesHandler->clone()->reset([
                        GameOptionValueAutostartGeneric::Enabled,
                        GameOptionValueAutostartGeneric::Disabled
                    ]),
                    GameOptionValueAutostartGeneric::Enabled,
                    GameOptionTypeGeneric::Checkbox
                ),

                new GameOptionForfeitAfterGeneric(
                    $valuesHandler->clone()->reset([
                        GameOptionValueForfeitAfterGeneric::Disabled,
                        GameOptionValueForfeitAfterGeneric::Minute,
                    ]),
                    GameOptionValueForfeitAfterGeneric::Disabled,
                    GameOptionTypeGeneric::Checkbox
                ),

            ];
    }
}
