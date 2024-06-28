<?php

declare(strict_types=1);

namespace Tests;

use Laucov\Cli\AbstractCommand;
use Laucov\Cli\AbstractRequest;
use Laucov\Cli\OutgoingRequest;
use Laucov\Cli\Router;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @coversDefaultClass \Laucov\Cli\Router
 */
final class RouterTest extends TestCase
{
    /**
     * Router instance.
     */
    protected Router $router;

    /**
     * Provides command test data.
     */
    public function commandProvider(): array
    {
        // Create mock.
        $request = $this->createMock(AbstractRequest::class);

        // Create parent command.
        $parent = new class ($request) extends AbstractCommand {
            public function run(): void
            {
            }
        };
        class_alias($parent::class, 'Tests\BaseCommand');

        // Create command with normal dependencies.
        $child_a = new class ($request) extends BaseCommand {
            public function __construct(AbstractRequest $request)
            {
            }
        };

        return [
            [true, $child_a::class],
        ];
    }

    /**
     * @covers ::addCommand
     */
    public function testCanAddCommand(): void
    {
        // Mock and add command.
        $command = $this->createMock(AbstractCommand::class);
        $this->assertSame(
            $this->router,
            $this->router->addCommand('valid-class', $command::class),
        );
    }

    /**
     * @covers ::getCommand
     * @covers ::route
     * @uses Laucov\Cli\AbstractCommand::__construct
     * @uses Laucov\Cli\Router::addCommand
     */
    public function testCanRoute(): void
    {
        // Create example command.
        $command = $this->createMock(AbstractCommand::class);

        // Route with a valid command.
        $request = $this->createMock(AbstractRequest::class);
        $request
            ->method('getCommand')
            ->willReturn('test-the-router');
        $command = $this->router
            ->addCommand('test-the-router', $command::class)
            ->route($request);
        $this->assertInstanceOf($command::class, $command);

        // Route with an unregistered command.
        $request = $this->createMock(AbstractRequest::class);
        $request
            ->method('getCommand')
            ->willReturn('brake-the-router');
        $this->assertNull($this->router->route($request));
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
        $invalid_command = $this->createMock(\stdClass::class);
        $this->router->addCommand('invalid-command', $invalid_command::class);
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

    /**
     * @covers ::route
     */
    public function testRequestMustHaveACommandName(): void
    {
        $request = $this->createMock(AbstractRequest::class);
        $request
            ->method('getCommand')
            ->willReturn(null);
        $this->expectException(\InvalidArgumentException::class);
        $this->router->route($request);
    }

    /**
     * @coversNothing
     * @dataProvider commandProvider
     */
    public function testValidatesAndResolvesConstructors(
        bool $is_valid,
        string $class_name,
    ): void {
        // Set up exception expectation.
        if (!$is_valid) {
            $this->expectException(\InvalidArgumentException::class);
            $message = 'Invalid command constructor parameters.';
            $this->expectExceptionMessage($message);
        }

        // Add command.
        $this->router->addCommand('do-something', $class_name);

        // Route command.
        if ($is_valid) {
            $request = $this->createMock(AbstractRequest::class);
            $request
                ->method('getCommand')
                ->willReturn('do-something');
            $command = $this->router->route($request);
            $this->assertInstanceOf($class_name, $command);
        }
    }

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        $this->router = new Router();
    }
}
