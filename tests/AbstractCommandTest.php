<?php

declare(strict_types=1);

namespace Tests;

use Laucov\Cli\AbstractCommand;
use Laucov\Cli\OutgoingRequest;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Cli\AbstractCommand
 */
final class AbstractCommandTest extends TestCase
{
    protected AbstractCommand $command;

    protected function setUp(): void
    {
        $request = new OutgoingRequest();
        $request->setCommand('do-something');

        $this->command = $this->getMockForAbstractClass(
            AbstractCommand::class,
            ['request' => $request],
        );
    }

    /**
     * @covers ::run
     * @covers ::__construct
     * @uses Laucov\Cli\OutgoingRequest::setCommand
     */
    public function testCanRun(): void
    {
        $this->assertNull($this->command->run());
    }
}
