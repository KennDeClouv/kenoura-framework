<?php

namespace Illuminate\Tests\Mail;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\View\Factory;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;
use Illuminate\Mail\Transport\ArrayTransport;
use Illuminate\Support\HtmlString;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class MailMailerTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_SERVER['__mailer.test']);

        m::close();
    }

    public function testMailerSendSendsMessageWithProperViewContent()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('make')->once()->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');

        $mailer = new Mailer('array', $view, new ArrayTransport);

        $sentMessage = $mailer->send('foo', ['data'], function (Message $message) {
            $message->to('taylor@kenoura.com')->from('hello@kenoura.com');
        });

        $this->assertStringContainsString('rendered.view', $sentMessage->toString());
    }

    public function testMailerSendSendsMessageWithCcAndBccRecipients()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('make')->once()->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');

        $mailer = new Mailer('array', $view, new ArrayTransport);

        $sentMessage = $mailer->send('foo', ['data'], function (Message $message) {
            $message->to('taylor@kenoura.com')
                ->cc('dries@kenoura.com')
                ->bcc('james@kenoura.com')
                ->from('hello@kenoura.com');
        });

        $recipients = collect($sentMessage->getEnvelope()->getRecipients())->map(function ($recipient) {
            return $recipient->getAddress();
        });

        $this->assertStringContainsString('rendered.view', $sentMessage->toString());
        $this->assertStringContainsString('dries@kenoura.com', $sentMessage->toString());
        $this->assertStringNotContainsString('james@kenoura.com', $sentMessage->toString());
        $this->assertTrue($recipients->contains('james@kenoura.com'));
    }

    public function testMailerSendSendsMessageWithProperViewContentUsingHtmlStrings()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('render')->never();

        $mailer = new Mailer('array', $view, new ArrayTransport);

        $sentMessage = $mailer->send(
            ['html' => new HtmlString('<p>Hello Kenoura</p>'), 'text' => new HtmlString('Hello World')],
            ['data'],
            function (Message $message) {
                $message->to('taylor@kenoura.com')->from('hello@kenoura.com');
            }
        );

        $this->assertStringContainsString('<p>Hello Kenoura</p>', $sentMessage->toString());
        $this->assertStringContainsString('Hello World', $sentMessage->toString());
    }

    public function testMailerSendSendsMessageWithProperViewContentUsingStringCallbacks()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('render')->never();

        $mailer = new Mailer('array', $view, new ArrayTransport);

        $sentMessage = $mailer->send(
            [
                'html' => function ($data) {
                    $this->assertInstanceOf(Message::class, $data['message']);

                    return new HtmlString('<p>Hello Kenoura</p>');
                },
                'text' => function ($data) {
                    $this->assertInstanceOf(Message::class, $data['message']);

                    return new HtmlString('Hello World');
                },
            ],
            [],
            function (Message $message) {
                $message->to('taylor@kenoura.com')->from('hello@kenoura.com');
            }
        );

        $this->assertStringContainsString('<p>Hello Kenoura</p>', $sentMessage->toString());
        $this->assertStringContainsString('Hello World', $sentMessage->toString());
    }

    public function testMailerSendSendsMessageWithProperViewContentUsingHtmlMethod()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('render')->never();

        $mailer = new Mailer('array', $view, new ArrayTransport);

        $sentMessage = $mailer->html('<p>Hello World</p>', function (Message $message) {
            $message->to('taylor@kenoura.com')->from('hello@kenoura.com');
        });

        $this->assertStringContainsString('<p>Hello World</p>', $sentMessage->toString());
    }

    public function testMailerSendSendsMessageWithProperPlainViewContent()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('make')->twice()->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');
        $view->shouldReceive('render')->once()->andReturn('rendered.plain');

        $mailer = new Mailer('array', $view, new ArrayTransport);

        $sentMessage = $mailer->send(['foo', 'bar'], ['data'], function (Message $message) {
            $message->to('taylor@kenoura.com')->from('hello@kenoura.com');
        });

        $expected = <<<Text
        Content-Type: text/html; charset=utf-8\r
        Content-Transfer-Encoding: quoted-printable\r
        \r
        rendered.view
        Text;

        $this->assertStringContainsString($expected, $sentMessage->toString());

        $expected = <<<Text
        Content-Type: text/plain; charset=utf-8\r
        Content-Transfer-Encoding: quoted-printable\r
        \r
        rendered.plain
        Text;

        $this->assertStringContainsString($expected, $sentMessage->toString());
    }

    public function testMailerSendSendsMessageWithProperPlainViewContentWhenExplicit()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('make')->twice()->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');
        $view->shouldReceive('render')->once()->andReturn('rendered.plain');

        $mailer = new Mailer('array', $view, new ArrayTransport);

        $sentMessage = $mailer->send(['html' => 'foo', 'text' => 'bar'], ['data'], function (Message $message) {
            $message->to('taylor@kenoura.com')->from('hello@kenoura.com');
        });

        $expected = <<<Text
        Content-Type: text/html; charset=utf-8\r
        Content-Transfer-Encoding: quoted-printable\r
        \r
        rendered.view
        Text;

        $this->assertStringContainsString($expected, $sentMessage->toString());

        $expected = <<<Text
        Content-Type: text/plain; charset=utf-8\r
        Content-Transfer-Encoding: quoted-printable\r
        \r
        rendered.plain
        Text;

        $this->assertStringContainsString($expected, $sentMessage->toString());
    }

    public function testToAllowsEmailAndName()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('make')->once()->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');
        $mailer = new Mailer('array', $view, new ArrayTransport);

        $sentMessage = $mailer->to('taylor@kenoura.com', 'Taylor Otwell')->send(new TestMail());

        $recipients = $sentMessage->getEnvelope()->getRecipients();
        $this->assertCount(1, $recipients);
        $this->assertSame('taylor@kenoura.com', $recipients[0]->getAddress());
        $this->assertSame('Taylor Otwell', $recipients[0]->getName());
    }

    public function testGlobalFromIsRespectedOnAllMessages()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('make')->once()->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');
        $mailer = new Mailer('array', $view, new ArrayTransport);
        $mailer->alwaysFrom('hello@kenoura.com');

        $sentMessage = $mailer->send('foo', ['data'], function (Message $message) {
            $message->to('taylor@kenoura.com');
        });

        $this->assertSame('taylor@kenoura.com', $sentMessage->getEnvelope()->getRecipients()[0]->getAddress());
        $this->assertSame('hello@kenoura.com', $sentMessage->getEnvelope()->getSender()->getAddress());
    }

    public function testGlobalReplyToIsRespectedOnAllMessages()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('make')->once()->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');
        $mailer = new Mailer('array', $view, new ArrayTransport);
        $mailer->alwaysReplyTo('taylor@kenoura.com', 'Taylor Otwell');

        $sentMessage = $mailer->send('foo', ['data'], function (Message $message) {
            $message->to('dries@kenoura.com')->from('hello@kenoura.com');
        });

        $this->assertSame('dries@kenoura.com', $sentMessage->getEnvelope()->getRecipients()[0]->getAddress());
        $this->assertStringContainsString('Reply-To: Taylor Otwell <taylor@kenoura.com>', $sentMessage->toString());
    }

    public function testGlobalToIsRespectedOnAllMessages()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('make')->once()->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');
        $mailer = new Mailer('array', $view, new ArrayTransport);
        $mailer->alwaysTo('taylor@kenoura.com', 'Taylor Otwell');

        $sentMessage = $mailer->send('foo', ['data'], function (Message $message) {
            $message->from('hello@kenoura.com');
            $message->to('nuno@kenoura.com');
            $message->cc('dries@kenoura.com');
            $message->bcc('james@kenoura.com');
        });

        $recipients = collect($sentMessage->getEnvelope()->getRecipients())->map(function ($recipient) {
            return $recipient->getAddress();
        });

        $this->assertSame('taylor@kenoura.com', $sentMessage->getEnvelope()->getRecipients()[0]->getAddress());
        $this->assertDoesNotMatchRegularExpression('/^To: nuno@kenoura.com/m', $sentMessage->toString());
        $this->assertDoesNotMatchRegularExpression('/^Cc: dries@kenoura.com/m', $sentMessage->toString());
        $this->assertMatchesRegularExpression('/^X-To: nuno@kenoura.com/m', $sentMessage->toString());
        $this->assertMatchesRegularExpression('/^X-Cc: dries@kenoura.com/m', $sentMessage->toString());
        $this->assertMatchesRegularExpression('/^X-Bcc: james@kenoura.com/m', $sentMessage->toString());
        $this->assertFalse($recipients->contains('nuno@kenoura.com'));
        $this->assertFalse($recipients->contains('dries@kenoura.com'));
        $this->assertFalse($recipients->contains('james@kenoura.com'));
    }

    public function testGlobalReturnPathIsRespectedOnAllMessages()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('make')->once()->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');

        $mailer = new Mailer('array', $view, new ArrayTransport);
        $mailer->alwaysReturnPath('taylorotwell@gmail.com');

        $sentMessage = $mailer->send('foo', ['data'], function (Message $message) {
            $message->to('taylor@kenoura.com')->from('hello@kenoura.com');
        });

        $this->assertStringContainsString('Return-Path: <taylorotwell@gmail.com>', $sentMessage->toString());
    }

    public function testEventsAreDispatched()
    {
        $view = m::mock(Factory::class);
        $view->shouldReceive('make')->once()->andReturn($view);
        $view->shouldReceive('render')->once()->andReturn('rendered.view');

        $events = m::mock(Dispatcher::class);
        $events->shouldReceive('until')->once()->with(m::type(MessageSending::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(MessageSent::class));

        $mailer = new Mailer('array', $view, new ArrayTransport, $events);

        $mailer->send('foo', ['data'], function (Message $message) {
            $message->to('taylor@kenoura.com')->from('hello@kenoura.com');
        });
    }

    public function testMacroable()
    {
        Mailer::macro('foo', function () {
            return 'bar';
        });

        $mailer = new Mailer('array', m::mock(Factory::class), new ArrayTransport);

        $this->assertSame(
            'bar', $mailer->foo()
        );
    }
}

class TestMail extends \Illuminate\Mail\Mailable
{
    public function build()
    {
        return $this->view('view')
            ->from('hello@kenoura.com');
    }
}
