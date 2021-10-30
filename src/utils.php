<?php


namespace datagutten\descriptionMaker;


use datagutten\tools\files\files;
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

    public static function file_path(string $source, string $extension): string
    {
        if (!file_exists($source))
            throw new FileNotFoundException($source);
        $path_info = pathinfo($source);

        return files::path_join($path_info['dirname'], $path_info['filename'] . '.' . $extension);
    }

    /**
     * Create snapshots from video file
     * @param string $file Video file
     * @param string|null $snapshot_folder Folder to save snapshots
     * @return array Snapshot files
     * @throws DependencyFailedException No tool available to make snapshots
     * @throws FileNotFoundException Video file not found
     * @throws DurationNotFoundException Unable to get video file duration
     */
    public static function snapshots(string $file, string $snapshot_folder = null): array
    {
        $positions = video::snapshotsteps($file, 4); //Calculate snapshot positions
        if (empty($snapshot_folder)) //Create snapshot directory in video folder if other folder is not specified
            $snapshot_folder = dirname($file) . '/snapshots';
        if (!file_exists($snapshot_folder))
            mkdir($snapshot_folder, 0777, true);
        return video::snapshots($file, $positions, $snapshot_folder);
    }
}