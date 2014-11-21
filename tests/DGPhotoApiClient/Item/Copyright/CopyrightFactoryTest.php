<?php

use \DG\API\Photo\Item\Copyright;

class CopyrightFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateImageCopyright()
    {
        $value = "copy";
        $url   = "img.jpg";
        $copyright = Copyright\CopyrightFactory::create('imAge  ', $value, $url);

        $this->assertInstanceOf('\\DG\\API\\Photo\\Item\\Copyright\\ImageCopyright', $copyright);
        $this->assertEquals($value, $copyright->getValue());
        $this->assertEquals($url, $copyright->getUrl());
    }

    public function testCreateTextCopyright()
    {
        $value = "copy";
        $url   = "2.gis";
        $copyright = Copyright\CopyrightFactory::create('  teXT', $value, $url);

        $this->assertInstanceOf('\\DG\\API\\Photo\\Item\\Copyright\\TextCopyright', $copyright);
        $this->assertEquals($value, $copyright->getValue());
        $this->assertEquals($url, $copyright->getUrl());
    }

    public function testInvalidCreateCopyright()
    {
        $value = "copy";
        $url   = "img.jpg";

        $this->setExpectedException('\\DG\\API\\Photo\\Exception');
        Copyright\CopyrightFactory::create('ololo type', $value, $url);
    }
} 