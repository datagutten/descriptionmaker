<?php


use datagutten\descriptionMaker\MusicBrainzDescription;
use PHPUnit\Framework\TestCase;

class MusicBrainzDescriptionTest extends TestCase
{

    public function testCover_art()
    {
        $mb = new MusicBrainzDescription();
        $art = $mb->cover_art('5901a41f-0860-4d3e-917b-dc5f9e27adfb');
        $this->assertSame('http://coverartarchive.org/release/5901a41f-0860-4d3e-917b-dc5f9e27adfb/27661418054.jpg', $art['image']);
    }
}
