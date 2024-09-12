<?php

namespace MyDramGames\Games\TicTacToe\Extensions\Utils;

use MyDramGames\Utils\Php\Collection\CollectionPoweredExtendable;

class GameCharacterTicTacToeCollectionPowered extends CollectionPoweredExtendable implements GameCharacterTicTacToeCollection
{
    protected const ?string TYPE_CLASS = GameCharacterTicTacToe::class;
    protected const int KEY_MODE = self::KEYS_METHOD;

    protected function getItemKey(mixed $item): mixed
    {
        return $item->getName();
    }
}
