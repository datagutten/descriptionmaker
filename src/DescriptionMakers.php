<?php

namespace datagutten\descriptionMaker;

use datagutten\AudioMetadata\AudioMetadata;
use datagutten\descriptionMaker;
use datagutten\musicbrainz;
use datagutten\tools\files\files;
use FileNotFoundException;
use InvalidArgumentException;
use OrpheusNET\Logchecker\Logchecker;


class DescriptionMakers
{

    public static function eac_log(string $log_file): string
    {
        $logchecker = new Logchecker();
        $logchecker->newFile($log_file);
        $logchecker->parse();
        $score = $logchecker->getScore();
        $log_text = $logchecker->getLog();

        $log_bbcode = EAClogBBCode::rewrite($log_text);
        return sprintf("\n\nLog score: %d\n[spoiler]%s[/spoiler]", $score, $log_bbcode);
    }

    public static function album($path, $extensions = ['flac'])
    {
        $mb = new MusicBrainzDescription;

        try
        {
            $file = files::first_file($path, $extensions);
            $metadata = AudioMetadata::read_metadata($file);
            $desc = $mb->build_description($metadata);
        } catch (FileNotFoundException $e)
        {
            die($e->getMessage());
        } catch (musicbrainz\exceptions\MusicBrainzException $e)
        {
            die('Error from MusicBrainz: ' . $e->getMessage());
        }

        try
        {
            $file_log = files::first_file($path, ['log']);
            $desc .= self::eac_log($file_log);

        } catch (InvalidArgumentException $e)
        {
            throw new descriptionMaker\exceptions\DescriptionException('No log found', previous: $e);
        }

        return $desc;
    }

    /**
     * Generate mediainfo and save to file
     * @param string $file
     * @return void
     */
    public function mediainfo(string $file)
    {

    }
}