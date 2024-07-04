<?php

declare(strict_types=1);

namespace Tests;

use Laucov\Cli\AbstractCommand;
use Laucov\Cli\AbstractRequest;
use Laucov\Cli\Input;
use Laucov\Cli\Interfaces\CommandInterface;
use Laucov\Cli\Router;
use PHPUnit\Framework\TestCase;

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
        $parent = new class ($request) implements CommandInterface {
            public function run(): void
            {
            }
        };
        class_alias($parent::class, 'Tests\BaseCommand');

        // Create command with legacy dependencies.
        $legacy_command = new class ($request) extends BaseCommand {
            public function __construct(AbstractRequest $request)
            {
            }
        };

        // Create command with other dependencies.
        $command = new class ('a', $request, ['b']) extends BaseCommand {
            public function __construct(
                string $some_string,
                AbstractRequest $request,
                array $some_array,
            ) {
            }
        };

        // Create command with invalid dependencies.
        $invalid_command = new class ($request, 2) extends BaseCommand {
            public function __construct(
                AbstractRequest $request,
                int $some_int,
            ) {
            }
        };

        // Create a command with no constructor.
        $no_constructor = new class extends BaseCommand {};

        return [
            'legacy command' => [true, $legacy_command::class],
            'normal command' => [true, $command::class],
            'invalid command' => [false, $invalid_command::class],
            'command w/o constructor' => [true, $no_constructor::class],
        ];
    }

    /**
     * @covers ::addCommand
     * @uses Laucov\Cli\Router::__construct
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
     * @covers ::__construct
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
     * @covers ::setInput
     * @uses Laucov\Cli\Router::__construct
     * @uses Laucov\Cli\Router::addCommand
     * @uses Laucov\Cli\Router::getCommand
     * @uses Laucov\Cli\Router::route
     */
    public function testCanSetInput(): void
    {
        // Mock input object.
        $input = $this->createMock(Input::class);
        $input
            ->expects($this->once())
            ->method('ask')
            ->with('Insert your name: ')
            ->willReturn('John');

        // Create command.
        $command = new class ($input) implements CommandInterface
        {
            public function __construct(protected Input $input)
            {
            }
            public function run(): void
            {
                $this->input->ask('Insert your name: ');
            }
        };

        // Mock request.
        $request = $this->createMock(AbstractRequest::class);
        $request
            ->method('getCommand')
            ->willReturn('test');
        
        // Set input source and add command.
        $this->router
            ->setInput($input)
            ->addCommand('test', $command::class)
            ->route($request)
            ->run();
    }

    /**
     * @covers ::addCommand
     * @uses Laucov\Cli\Router::__construct
     */
    public function testCommandClassesMustImplementTheCommandInterface(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $message = 'The command class must extend "%s".';
        $message = sprintf($message, CommandInterface::class);
        $this->expectExceptionMessage($message);
        $invalid_command = $this->createMock(\stdClass::class);
        $this->router->addCommand('invalid-command', $invalid_command::class);
    }

    /**
     * @covers ::addCommand
     * @uses Laucov\Cli\Router::__construct
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
     * @uses Laucov\Cli\Router::__construct
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
     * @covers ::addCommand
     * @covers ::route
     * @uses Laucov\Cli\Router::__construct
     * @uses Laucov\Cli\Router::getCommand
     * @dataProvider commandProvider
     */
    public function testValidatesAndResolvesConstructors(
        bool $is_valid,
        string $class_name,
    ): void {
        // Extend the router.
        $this->router = new class extends Router {
            public function __construct()
            {
                parent::__construct();
                $this->dependencies->setValue('string', 'Foobar');
                $this->dependencies->setValue('array', ['foo', 'bar', 'baz']);
            }
        };

        // Set up exception expectation.
        if (!$is_valid) {
            $this->expectException(\InvalidArgumentException::class);
            $message = "{$class_name} has invalid constructor parameters.";
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
