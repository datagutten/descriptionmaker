<?php


use datagutten\descriptionMaker\TVSeriesUtils;
use PHPUnit\Framework\TestCase;

class TVSeriesUtilsTest extends TestCase
{

    public function testParse_release()
    {
        $this->assertEquals(['season' => 1, 'episode' => 2, 'series' => 'Mannens unyttige verden'], TVSeriesUtils::parse_release('Mannens unyttige verden S01E02'));
        $this->assertEquals(['season' => 1, 'episode' => 1, 'series' => 'Mannens unyttige verden'], TVSeriesUtils::parse_release('Mannens unyttige verden EP01'));
        $this->assertEquals(['season' => 1, 'episode' => 0, 'series' => 'Mannens unyttige verden'], TVSeriesUtils::parse_release('Mannens unyttige verden S01'));
        $this->assertEquals(['season' => 1, 'episode' => 0, 'series' => 'Mannens unyttige verden'], TVSeriesUtils::parse_release('Mannens unyttige verden'));
        $this->assertEquals(['season' => 1, 'episode' => 0, 'series' => 'Søsken'], TVSeriesUtils::parse_release('Søsken 720p HDTV H264 PHP'));
        //$this->assertEquals(['season' => 1, 'episode' => 1, 'series' => null], TVSeriesUtils::parse_release('EP01'));
    }
}
