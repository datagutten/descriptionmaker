<?php


namespace datagutten\descriptionMaker;


use datagutten\tools\files\files;
use datagutten\video_tools\exceptions\DurationNotFoundException;
use datagutten\video_tools\video;
use DependencyFailedException;
use FileNotFoundException;

class utils
{
    /**
     * Replace the file extension for a file
     * @param string $source Original file name
     * @param string $extension New extension
     * @return string File name with new extension
     * @throws FileNotFoundException Original file not found
     */
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
        $positions = video::snapshotsteps($file); //Calculate snapshot positions
        if (empty($snapshot_folder)) //Create snapshot directory in video folder if other folder is not specified
            $snapshot_folder = dirname($file) . '/snapshots';
        if (!file_exists($snapshot_folder))
            mkdir($snapshot_folder, 0777, true);
        return video::snapshots($file, $positions, $snapshot_folder);
    }

    /**
     * Get path to description file
     * @param string $file
     * @return string Description file path
     */
    public static function description_file(string $file): string
    {
        $info = pathinfo($file);
        return files::path_join($info['dirname'], $info['filename'] . '.txt');
    }
}