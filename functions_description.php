<?Php

use datagutten\descriptionMaker\Snapshots;
use datagutten\image_host;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class description
{
    /**
     * @var dependcheck
     */
	private $dependcheck;
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
        $this->dependcheck = new dependcheck;
        $this->video = new video;
    }

    /**
     * @param string $file
     * @return string
     * @throws DependencyFailedException
     * @throws FileNotFoundException
     */
	public function mediainfo($file)
	{
	    if(!file_exists($file))
	        throw new FileNotFoundException($file);
		$this->dependcheck->depend('mediainfo');

        $process = new Process(array('mediainfo', '--Output=XML', $file));
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

		//$info=shell_exec("mediainfo --Output=XML \"$path\" 2>&1");
		//die($info);
		$xml=simplexml_load_string($process->getOutput());
        $key_lengths = [];
        $output = [];
		foreach ($xml->{'media'}->{'track'} as $track)
		{
            $output[] = $track->attributes()->{'type'};
			//$output[]=$data['@attributes']['type'];
			$outputkeys[]='header';
			foreach ($track as $key=>$value) {
                if (array_search($key, array('@attributes', 'Unique_ID', 'Complete_name', 'Encoding_settings', 'Color_primaries', 'Transfer_characteristics', 'Matrix_coefficients')) === false) {
                    $output[] = $value;
                    $outputkeys[] = $key;
                    $key_lengths[] = strlen($key);
                }
            }
		}

		$maxlen=max($key_lengths); //Find the longest key
		$mediainfo='';
		foreach ($output as $key=>$value)
		{
			if ($outputkeys[$key]!='header')
				$mediainfo.=str_pad($outputkeys[$key],$maxlen+5).": $value\n";
			else
				$mediainfo.= "\n[b]".$value."[/b]\n";	
		}
		return $mediainfo;
	}

    /**
     * @param string $file
     * @return string
     * @throws DependencyFailedException
     * @throws FileNotFoundException
     */
	public static function simplemediainfo(string $file): string
	{
	    $dependcheck = new dependcheck();
        if(!file_exists($file))
            throw new FileNotFoundException($file);
        $dependcheck->depend('mediainfo');

        $process = new Process(array('mediainfo', $file));
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $info = $process->getOutput();
		//$info=shell_exec($cmd="mediainfo \"$path\" 2>&1");
		$info=preg_replace("/Complete name.+\n/",'',$info);
		$info=preg_replace("/Unique ID.+\n/",'',$info);
		return $info;
	}
}
