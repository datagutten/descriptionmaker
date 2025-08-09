<?Php

use datagutten\descriptionMaker\Snapshots;
use datagutten\image_host;

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
            if (!empty($config['md5_folder']))
                $this->image_host->md5_folder = datagutten\tools\files\files::path_join($config['md5_folder'], $this->image_host->site);
            $this->snapshots = new Snapshots($this->image_host);
        }
    }
}
