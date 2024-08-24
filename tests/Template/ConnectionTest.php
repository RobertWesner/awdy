<?php

declare(strict_types=1);

namespace RobertWesner\AWDY\Tests\Template;

use PHPUnit\Framework\Attributes\CoversClass;
use RobertWesner\AWDY\Template\Connection;
use RobertWesner\AWDY\Template\Facing;
use RobertWesner\AWDY\Tests\BaseTest;

#[CoversClass(Connection::class)]
final class ConnectionTest extends BaseTest
{
    public function test(): void
    {
        $connection = Connection::horizontal()
            ->begin(0, 3, Facing::RIGHT)
            ->end(-5, 3, Facing::LEFT);

        self::assertSame(Connection::TYPE_HORIZONTAL, $connection->type);
        self::assertSame(0, $connection->beginX);
        self::assertSame(3, $connection->beginY);
        self::assertSame(Facing::RIGHT, $connection->beginFacing);
        self::assertSame(-5, $connection->endX);
        self::assertSame(3, $connection->endY);
        self::assertSame(Facing::LEFT, $connection->endFacing);

        $connection = Connection::vertical()
            ->begin(8, 10, Facing::BOTTOM)
            ->end(8, 30, Facing::ALL);

        self::assertSame(Connection::TYPE_VERTICAL, $connection->type);
        self::assertSame(8, $connection->beginX);
        self::assertSame(10, $connection->beginY);
        self::assertSame(Facing::BOTTOM, $connection->beginFacing);
        self::assertSame(8, $connection->endX);
        self::assertSame(30, $connection->endY);
        self::assertSame(Facing::ALL, $connection->endFacing);
    }
}
