<?php


use datagutten\descriptionMaker\EAClogBBCode;
use PHPUnit\Framework\TestCase;

class EAClogBBCodeTest extends TestCase
{

/*    public function testRewrite()
    {

    }*/

    public function testColor_class()
    {
        $this->assertSame('[color=yellow]test[/color]', EAClogBBCode::color_class(['', 'log2', 'test']));
        $this->assertSame('[b][color=green]test[/color][/b]', EAClogBBCode::color_class(['', 'good', 'test']));
        $this->assertSame('[b]test[/b]', EAClogBBCode::color_class(['', 'log4', 'test']));
    }
}
