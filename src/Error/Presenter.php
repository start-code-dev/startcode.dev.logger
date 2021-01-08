<?php

namespace Startcode\Logger\Error;

class Presenter
{

    private array $css = [
        'position: relative',
        'background: #ffdfdf',
        'border: 2px solid #ff5050',
        'margin: 5px auto',
        'padding: 10px',
        'min-width: 720px',
        'width: 50%',
        'font: normal 12px Verdana, monospaced',
        'overflow: auto',
        'z-index: 100',
        'clear: both'
    ];

    /**
     * @var ErrorData
     */
    private $data;

    public function display() : void
    {
        echo $this->isCli()
            ? $this->data->getDataAsString()
            : $this->getDataAsHtml();
    }

    public function setData(ErrorData $data) : self
    {
        $this->data = $data;
        return $this;
    }

    private function getFormattedCss() : string
    {
        return join('; ', $this->css);
    }

    private function getDataAsHtml() : string
    {
        return sprintf($this->getHtml(), $this->getFormattedCss(), $this->data->getDataAsString());
    }

    private function getHtml() : string
    {
        return \implode(PHP_EOL, [
            '<pre>',
                '<p style="%s">',
                    '%s',
                '</p>',
            '</pre>',
        ]) . PHP_EOL . PHP_EOL;
    }

    private function isCli() : bool
    {
        return \php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']);
    }
}
