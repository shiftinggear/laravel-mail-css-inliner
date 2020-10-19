<?php

namespace Tests;

use Fedeisas\LaravelMailCssInliner\CssInlinerPlugin;
use Swift_Mailer;
use Swift_Message;
use Swift_NullTransport;
use PHPUnit\Framework\TestCase;

class CssInlinerPluginTest extends TestCase
{
    /**
     * @var array
     */
    protected $stubs;

    /**
     * @var array
     */
    protected $options;

    protected static $stubDefinitions = [
        'plain-text',
        'original-html',
        'original-html-with-css',
        'original-html-with-link-css',
        'original-html-with-links-css',
        'converted-html',
        'converted-html-with-css',
        'converted-html-with-links-css',
    ];

    public function setUp() : void
    {
        foreach (self::$stubDefinitions as $stub) {
            $this->stubs[$stub] = file_get_contents(__DIR__ . '/stubs/' . $stub . '.stub');
        }

        $this->options = require(__DIR__ . '/../config/css-inliner.php');
    }

    /** @test **/
    public function itShouldConvertHtmlBody() : void
    {
        $mailer = new Swift_Mailer(new Swift_NullTransport());

        $mailer->registerPlugin(new CssInlinerPlugin($this->options));

        $message = new Swift_Message();

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');
        $message->setSubject('Test');
        $message->setBody($this->stubs['original-html'], 'text/html');

        $mailer->send($message);

        $this->assertEquals($this->stubs['converted-html'], $message->getBody());
    }

    /** @test **/
    public function itShouldConvertHtmlBodyWithGivenCss() : void
    {
        $this->options['css-files'] = [__DIR__ . '/css/test.css'];
        $mailer = new Swift_Mailer(new Swift_NullTransport());

        $mailer->registerPlugin(new CssInlinerPlugin($this->options));

        $message = new Swift_Message();

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');
        $message->setSubject('Test');
        $message->setBody($this->stubs['original-html-with-css'], 'text/html');

        $mailer->send($message);

        $this->assertEquals($this->stubs['converted-html-with-css'], $message->getBody());
    }

    /** @test **/
    public function itShouldConvertHtmlBodyAndTextParts() : void
    {
        $mailer = new Swift_Mailer(new Swift_NullTransport());

        $mailer->registerPlugin(new CssInlinerPlugin($this->options));

        $message = new Swift_Message();

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');
        $message->setSubject('Test');
        $message->setBody($this->stubs['original-html'], 'text/html');
        $message->addPart($this->stubs['plain-text'], 'text/plain');

        $mailer->send($message);

        $children = $message->getChildren();

        $this->assertEquals($this->stubs['converted-html'], $message->getBody());
        $this->assertEquals($this->stubs['plain-text'], $children[0]->getBody());
    }

    /** @test **/
    public function itShouldLeavePlainTextUnmodified() : void
    {
        $mailer = new Swift_Mailer(new Swift_NullTransport());

        $mailer->registerPlugin(new CssInlinerPlugin($this->options));

        $message = new Swift_Message();

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');
        $message->setSubject('Test');
        $message->addPart($this->stubs['plain-text'], 'text/plain');

        $mailer->send($message);

        $children = $message->getChildren();

        $this->assertEquals($this->stubs['plain-text'], $children[0]->getBody());
    }

    /** @test **/
    public function itShouldConvertHtmlBodyAsAPart() : void
    {
        $mailer = new Swift_Mailer(new Swift_NullTransport());

        $mailer->registerPlugin(new CssInlinerPlugin($this->options));

        $message = new Swift_Message();

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');
        $message->setSubject('Test');
        $message->addPart($this->stubs['original-html'], 'text/html');

        $mailer->send($message);

        $children = $message->getChildren();

        $this->assertEquals($this->stubs['converted-html'], $children[0]->getBody());
    }

    /** @test **/
    public function itShouldConvertHtmlBodyWithLinkCss() : void
    {
        $mailer = new Swift_Mailer(new Swift_NullTransport());

        $mailer->registerPlugin(new CssInlinerPlugin($this->options));

        $message = new Swift_Message();

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');
        $message->setSubject('Test');

        $message->setBody($this->stubs['original-html-with-link-css'], 'text/html');

        $mailer->send($message);

        $this->assertEquals($this->stubs['converted-html-with-css'], $message->getBody());
    }

    /** @test **/
    public function itShouldConvertHtmlBodyWithLinksCss() : void
    {
        $mailer = new Swift_Mailer(new Swift_NullTransport());

        $mailer->registerPlugin(new CssInlinerPlugin($this->options));

        $message = new Swift_Message();

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');
        $message->setSubject('Test');

        $message->setBody($this->stubs['original-html-with-links-css'], 'text/html');

        $mailer->send($message);

        $this->assertEquals($this->stubs['converted-html-with-links-css'], $message->getBody());
    }
}
