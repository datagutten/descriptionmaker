<?Php

use datagutten\descriptionMaker\Snapshots;
use datagutten\image_host;
use datagutten\tools\files\files;

class description
{
    /**
     * @var image_host\image_host
     */
    public image_host\image_host $image_host;
    /**
     * @var Snapshots
     */
    public Snapshots $snapshots;

    function __construct($config)
    {
        if (!empty($config['imagehost']))
        {
            if (empty($config['imagehost']['host']))
                $image_host = image_host\cubeupload::class;
            else
                $image_host = $config['imagehost']['host'];
            $this->image_host = new $image_host($config['imagehost']);
            $this->snapshots = new Snapshots($this->image_host);
        }
    }

    public static function description_file(string $file): string
    {
        $info = pathinfo($file);
        return files::path_join($info['dirname'], $info['filename'] . '.txt');
    }
}
