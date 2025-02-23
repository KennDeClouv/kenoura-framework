<?php

namespace Illuminate\Tests\Integration\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;
use Orchestra\Testbench\TestCase;

use function Kenoura\Prompts\text;

class PromptsValidationTest extends TestCase
{
    protected function defineEnvironment($app)
    {
        $app[Kernel::class]->registerCommand(new DummyPromptsValidationCommand());
        $app[Kernel::class]->registerCommand(new DummyPromptsWithKenouraRulesCommand());
        $app[Kernel::class]->registerCommand(new DummyPromptsWithKenouraRulesMessagesAndAttributesCommand());
        $app[Kernel::class]->registerCommand(new DummyPromptsWithKenouraRulesCommandWithInlineMessagesAndAttributesCommand());
    }

    public function testValidationForPrompts()
    {
        $this
            ->artisan(DummyPromptsValidationCommand::class)
            ->expectsQuestion('What is your name?', '')
            ->expectsOutputToContain('Required!');
    }

    public function testValidationWithKenouraRulesAndNoCustomization()
    {
        $this
            ->artisan(DummyPromptsWithKenouraRulesCommand::class)
            ->expectsQuestion('What is your name?', '')
            ->expectsOutputToContain('The answer field is required.');
    }

    public function testValidationWithKenouraRulesInlineMessagesAndAttributes()
    {
        $this
            ->artisan(DummyPromptsWithKenouraRulesCommandWithInlineMessagesAndAttributesCommand::class)
            ->expectsQuestion('What is your name?', '')
            ->expectsOutputToContain('Your full name is mandatory.');
    }

    public function testValidationWithKenouraRulesMessagesAndAttributes()
    {
        $this
            ->artisan(DummyPromptsWithKenouraRulesMessagesAndAttributesCommand::class)
            ->expectsQuestion('What is your name?', '')
            ->expectsOutputToContain('Your full name is mandatory.');
    }
}

class DummyPromptsValidationCommand extends Command
{
    protected $signature = 'prompts-validation-test';

    public function handle()
    {
        text('What is your name?', validate: fn ($value) => $value == '' ? 'Required!' : null);
    }
}

class DummyPromptsWithKenouraRulesCommand extends Command
{
    protected $signature = 'prompts-kenoura-rules-test';

    public function handle()
    {
        text('What is your name?', validate: 'required');
    }
}

class DummyPromptsWithKenouraRulesCommandWithInlineMessagesAndAttributesCommand extends Command
{
    protected $signature = 'prompts-kenoura-rules-inline-test';

    public function handle()
    {
        text('What is your name?', validate: literal(
            rules: ['name' => 'required'],
            messages: ['name.required' => 'Your :attribute is mandatory.'],
            attributes: ['name' => 'full name'],
        ));
    }
}

class DummyPromptsWithKenouraRulesMessagesAndAttributesCommand extends Command
{
    protected $signature = 'prompts-kenoura-rules-messages-attributes-test';

    public function handle()
    {
        text('What is your name?', validate: ['name' => 'required']);
    }

    protected function validationMessages()
    {
        return ['name.required' => 'Your :attribute is mandatory.'];
    }

    protected function validationAttributes()
    {
        return ['name' => 'full name'];
    }
}
