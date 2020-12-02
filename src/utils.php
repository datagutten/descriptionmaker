<?php


namespace datagutten\descriptionMaker;


use datagutten\video_tools\exceptions\DurationNotFoundException;
use DependencyFailedException;
use FileNotFoundException;
use video;

class utils
{
    /**
     * Convert duration in seconds to hours, minutes and seconds
     * https://stackoverflow.com/a/3172368/2630074
     * @param int $seconds
     * @return string Minutes:Seconds
     */
    public static function seconds_to_time($seconds)
    {
        return sprintf('%02d:%02d',floor(($seconds/60) % 60),$seconds % 60);
    }

    /**
     * Create snapshots from video file
     * @param $file
     * @param bool $snapshotdir
     * @return array
     * @throws DependencyFailedException
     * @throws FileNotFoundException
     * @throws DurationNotFoundException
     */
    public static function snapshots($file, $snapshotdir = false)
    {
        $positions = video::snapshotsteps($file, 4); //Calcuate snapshot positions
        if (empty($snapshotdir)) //Create snapshot directory in video folder if other folder is not specified
            $snapshotdir = dirname($file) . '/snapshots';
        if (!file_exists($snapshotdir))
            mkdir($snapshotdir, 0777, true);
        return video::snapshots($file, $positions, $snapshotdir);
    }
}