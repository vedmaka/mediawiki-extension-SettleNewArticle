<?php

/**
 *
 * Class SpecialSettleNewArticle
 */
class SpecialSettleNewArticle extends SpecialPage {

    private $templateParser;

    public function __construct($name = '', $restriction = '', $listed = true, $function = false, $file = '', $includable = false)
    {
        parent::__construct('SettleNewArticle');
    }

    public function execute($subPage)
    {
        $this->templateParser = new TemplateParser( dirname(__FILE__).'/../templates/', true );

        $data = array(

        );

        $html = $this->templateParser->processTemplate('main', $data);
        $this->getOutput()->addHTML($html);
    }

}