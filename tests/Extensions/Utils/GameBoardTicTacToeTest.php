<?php

namespace Tests\Extensions\Utils;

use MyDramGames\Games\TicTacToe\Extensions\Utils\GameBoardTicTacToe;
use MyDramGames\Utils\Exceptions\GameBoardException;
use MyDramGames\Utils\GameBoard\GameBoard;
use PHPUnit\Framework\TestCase;

class GameBoardTicTacToeTest extends TestCase
{
    protected GameBoardTicTacToe $board;
    protected array $fieldsToTest = [
        '1' => null, '2' => 'o', '3' => 'x',
        '4' => null, '5' => 'x', '6' => null,
        '7' => null, '8' => null, '9' => null,
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->board = new GameBoardTicTacToe();
    }

    public function testClassInstance(): void
    {
        $this->assertInstanceOf(GameBoard::class, $this->board);
    }

    public function testSetFieldThrowExceptionForInvalidValue(): void
    {
        $this->expectException(GameBoardException::class);
        $this->expectExceptionMessage(GameBoardException::MESSAGE_INVALID_FIELD_VALUE);

        $this->board->setFieldValue('1', 'a');
    }

    public function testSetFieldThrowExceptionForInvalidField(): void
    {
        $this->expectException(GameBoardException::class);
        $this->expectExceptionMessage(GameBoardException::MESSAGE_INVALID_FIELD_ID);

        $this->board->setFieldValue('10', 'x');
    }

    public function testSetFieldThrowExceptionIfAlreadySet(): void
    {
        $this->expectException(GameBoardException::class);
        $this->expectExceptionMessage(GameBoardException::MESSAGE_FIELD_ALREADY_SET);

        $this->board->setFieldValue('1', 'x');
        $this->board->setFieldValue('1', 'x');
    }

    public function testGetFieldValueBeforeSettingReturnsNull(): void
    {
        $this->assertNull($this->board->getFieldValue('1'));
    }

    public function testSetAndGetValue(): void
    {
        $this->board->setFieldValue('2', 'o');
        $this->assertEquals('o', $this->board->getFieldValue('2'));
    }

    public function testToJson(): void
    {
        $this->board->setFieldValue('2', 'o');
        $this->board->setFieldValue('3', 'x');
        $this->board->setFieldValue('5', 'x');

        $this->assertJsonStringEqualsJsonString(json_encode($this->fieldsToTest), $this->board->toJson());
    }

    public function testSetFromJson(): void
    {
        $json = json_encode($this->fieldsToTest);
        $this->board->setFromJson($json);

        $this->assertEquals($this->fieldsToTest, [
            '1' => $this->board->getFieldValue('1'),
            '2' => $this->board->getFieldValue('2'),
            '3' => $this->board->getFieldValue('3'),
            '4' => $this->board->getFieldValue('4'),
            '5' => $this->board->getFieldValue('5'),
            '6' => $this->board->getFieldValue('6'),
            '7' => $this->board->getFieldValue('7'),
            '8' => $this->board->getFieldValue('8'),
            '9' => $this->board->getFieldValue('9'),
        ]);
    }
}
