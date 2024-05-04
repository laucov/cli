<?php

declare(strict_types=1);

namespace Tests;

use Laucov\Cli\AbstractCommand;
use Laucov\Cli\OutgoingRequest;
use Laucov\Cli\Router;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Cli\Router
 */
final class RouterTest extends TestCase
{
    protected Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
    }

    /**
     * @covers ::addCommand
     */
    public function testCanAddCommand(): void
    {
        $this->assertSame(
            $this->router,
            $this->router->addCommand('valid-class', ValidCommand::class),
        );
    }

    /**
     * @covers ::route
     * @covers ::getCommand
     * @uses Laucov\Cli\AbstractRequest::getCommand
     * @uses Laucov\Cli\AbstractCommand::__construct
     * @uses Laucov\Cli\OutgoingRequest::setArguments
     * @uses Laucov\Cli\OutgoingRequest::setCommand
     * @uses Laucov\Cli\Router::addCommand
     */
    public function testCanRoute(): void
    {
        // Get a request instance.
        $request = new OutgoingRequest();

        // Create example command.
        $mock = $this
            ->getMockBuilder(AbstractCommand::class)
            ->setConstructorArgs(['request' => $request])
            ->setMockClassName('RouterCommandTest')
            ->getMockForAbstractClass();

        // Route with valid command.
        $request->setCommand('test-the-router');
        $request->setArguments([]);
        $command = $this->router
            ->addCommand('test-the-router', $mock::class)
            ->route($request);
        $this->assertInstanceOf($mock::class, $command);

        // Route with invalid command.
        $request->setCommand('inexistent-command');
        $this->assertNull($this->router->route($request));

        // Route without command.
        $this->expectException(\InvalidArgumentException::class);
        $this->router->route(new OutgoingRequest());
    }

    /**
     * @covers ::addCommand
     */
    public function testCommandClassesMustExtendTheAbstractCommandClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $message = 'The command class must extend "%s".';
        $message = sprintf($message, AbstractCommand::class);
        $this->expectExceptionMessage($message);
        $this->router->addCommand('invalid-class', InvalidCommand::class);
    }

    /**
     * @covers ::addCommand
     */
    public function testCommandClassesMustExist(): void
    {
        $class_name = 'Foo\Bar\BazCommand';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($class_name . ' does not exist.');
        $this->router->addCommand('inexistent-class', $class_name);
    }
}

class ValidCommand extends AbstractCommand
{
    public function run(): void
    {
    }
}

class InvalidCommand
{
}
