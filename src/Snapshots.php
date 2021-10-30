<?php

namespace datagutten\descriptionMaker;

use datagutten\descriptionMaker\exceptions\SnapshotException;
use datagutten\image_host;
use datagutten\video_tools\exceptions\DurationNotFoundException;
use DependencyFailedException;
use FileNotFoundException;
use InvalidArgumentException;

class Snapshots
{
    /**
     * @var image_host\image_host
     */
    public $image_host;

    public function __construct(image_host\image_host $image_host = null)
    {
        if (!empty($image_host))
            $this->image_host = $image_host;
    }

    /**
     * Upload snapshots using imagehost class
     * @param array $snapshots Snapshot file names
     * @param string $prefix Uploaded file name prefix
     * @return array Uploaded image URLs
     * @throws image_host\exceptions\UploadFailed Image upload failed
     */
    function upload_snapshots(array $snapshots, string $prefix = ''): array
    {
        if (empty($snapshots))
            throw new InvalidArgumentException('Snapshots empty');
        $links = [];
        foreach ($snapshots as $key => $snapshot)
        {
            if (!empty($prefix))
            {
                $path_info = pathinfo($snapshot); //TODO: Simplify this and use files::path_join?
                $new_file = sprintf('%s/%s_%s.%s', $path_info['dirname'], $prefix, $path_info['filename'], $path_info['extension']);
                rename($snapshot, $new_file);
                $snapshot = $new_file;
            }
            $upload = $this->image_host->upload($snapshot);
            $links[$key] = $upload;
        }
        return $links;
    }

    /**
     * Create BBCode links for the snapshots
     * @param array $snapshot_links
     * @return string
     */
    function snapshots_bbcode(array $snapshot_links): string
    {
        $bbcode = '';
        foreach ($snapshot_links as $screenshot) //Lag screenshots
        {
            if (method_exists($this->image_host, 'bbcode'))
                $bbcode .= $this->image_host->bbcode($screenshot);
            else
                $bbcode .= BBCode::image($screenshot);
        }
        return $bbcode;
    }

    /**
     * Create and upload snapshots
     * @param string $file Video file
     * @return string Snapshots links with BBCode
     * @throws SnapshotException Error creating or uploading snapshots
     */
    public function snapshots(string $file, $snapshot_folder = null): string
    {
        try
        {
            $snapshots = utils::snapshots($file, $snapshot_folder);
            $snapshot_links = $this->upload_snapshots($snapshots, basename($file));
        }
        catch (DependencyFailedException | FileNotFoundException |
        DurationNotFoundException | image_host\exceptions\UploadFailed $e)
        {
            throw new SnapshotException($e->getMessage(), $e->getCode(), $e);
        }

        return $this->snapshots_bbcode($snapshot_links);
    }
}