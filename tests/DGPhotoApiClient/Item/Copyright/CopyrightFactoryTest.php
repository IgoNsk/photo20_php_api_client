<?php

use \DG\API\Photo\Item\Copyright;

class CopyrightFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateImageCopyright()
    {
        $code = "foursquare";
        $value = "copy";
        $url   = "img.jpg";
        $copyright = Copyright\CopyrightFactory::create('imAge  ', $code, $value, $url);

        $this->assertInstanceOf('\\DG\\API\\Photo\\Item\\Copyright\\ImageCopyright', $copyright);
        $this->assertEquals($code, $copyright->getCode());
        $this->assertEquals($value, $copyright->getValue());
        $this->assertEquals($url, $copyright->getUrl());
    }

    public function testCreateTextCopyright()
    {
        $code = "foursquare";
        $value = "copy";
        $url   = "2.gis";
        $copyright = Copyright\CopyrightFactory::create('  teXT', $code, $value, $url);

        $this->assertInstanceOf('\\DG\\API\\Photo\\Item\\Copyright\\TextCopyright', $copyright);
        $this->assertEquals($code, $copyright->getCode());
        $this->assertEquals($value, $copyright->getValue());
        $this->assertEquals($url, $copyright->getUrl());
    }

    public function testInvalidCreateCopyright()
    {
        $code = "foursquare";
        $value = "copy";
        $url   = "img.jpg";

        $this->setExpectedException('\\DG\\API\\Photo\\Exception');
        Copyright\CopyrightFactory::create('ololo type', $code, $value, $url);
    }
} 