<?Php

use datagutten\descriptionMaker\Snapshots;
use datagutten\image_host;
use datagutten\video_tools\video;

class description
{
    /**
     * @var video
     */
	private $video;
    /**
     * @var image_host\image_host
     */
	public $image_host;
	public $error;
    /**
     * @var Snapshots
     */
    public $snapshots;

    function __construct($config)
    {
        if(!empty($config['imagehost']))
        {
            if (empty($config['imagehost']['host']))
                $image_host = image_host\cubeupload::class;
            else
                $image_host = $config['imagehost']['host'];
            $this->image_host = new $image_host($config['imagehost']);
            $this->snapshots = new Snapshots($this->image_host);
        }
        $this->video = new video;
    }

}
