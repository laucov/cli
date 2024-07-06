<?php

namespace Laucov\Cli;

use Laucov\Cli\Interfaces\CommandInterface;
use Laucov\Injection\Repository;
use Laucov\Injection\Resolver;
use Laucov\Injection\Validator;

/**
 * Stores routes and provides `AbstractCommand` instances.
 */
class Router
{
    /**
     * Stored command names.
     * 
     * @var array<string, class-string<AbstractCommand>>
     */
    protected array $commands = [];

    /**
     * Dependency repository.
     */
    protected Repository $dependencies;

    /**
     * Dependency resolver.
     */
    protected Resolver $resolver;

    /**
     * Dependency validator.
     */
    protected Validator $validator;

    /**
     * Create the router instance.
     */
    public function __construct()
    {
        $this->dependencies = new Repository();
        $this->resolver = new Resolver($this->dependencies);
        $this->validator = new Validator($this->dependencies);
        $this->validator->allow(AbstractRequest::class);
    }

    /**
     * Add a command.
     * 
     * @param class-string $class_string
     */
    public function addCommand(string $name, string $class_string): static
    {
        // Check if the command class exist.
        if (!class_exists($class_string)) {
            $message = "{$class_string} does not exist.";
            throw new \InvalidArgumentException($message);
        }

        // Check if extends the abstract command.
        if (!is_a($class_string, CommandInterface::class, true)) {
            $class_name = CommandInterface::class;
            $message = 'The command class must extend "' . $class_name . '".';
            throw new \InvalidArgumentException($message);
        }

        // Check if has a valid constructor.
        if (
            method_exists($class_string, '__construct')
            && !$this->validator->validate([$class_string, '__construct'])
        ) {
            $message = "{$class_string} has invalid constructor parameters.";
            throw new \InvalidArgumentException($message);
        }

        // Set command.
        $this->commands[$name] = $class_string;

        return $this;
    }

    /**
     * Retrieve a command based on the given request object.
     */
    public function route(AbstractRequest $request): null|CommandInterface
    {
        $command_name = $request->getCommand();
        if ($command_name === null) {
            $message = 'Cannot route request with unset command name.';
            throw new \InvalidArgumentException($message);
        }

        $class_string = $this->getCommand($command_name);
        if ($class_string === null) {
            return null;
        }

        $this->dependencies->setValue(AbstractRequest::class, $request);
        return $this->resolver->instantiate($class_string);
    }

    /**
     * Set the current input object.
     */
    public function setInput(Input $input): static
    {
        $this->dependencies->setValue(Input::class, $input);
        return $this;
    }

    /**
     * Set the current printer object.
     */
    public function setPrinter(Printer $printer): static
    {
        $this->dependencies->setValue(Printer::class, $printer);
        return $this;
    }

    /**
     * Get the command class name stored under the given name.
     * 
     * @return class-string<AbstractCommand>
     */
    protected function getCommand(string $name): ?string
    {
        return $this->commands[$name] ?? null;
    }
}
