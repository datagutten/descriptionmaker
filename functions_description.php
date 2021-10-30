<?Php

use datagutten\descriptionMaker\Snapshots;
use datagutten\image_host;
use datagutten\tvdb\tvdb;
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
     * Parse series information from release name
     * @param $release
     * @return array|bool Return false on failure
     */
	public function serieinfo($release)
	{
        preg_match('/.*?(?:S([0-9]+))?(?:EP?([0-9]+))?/', $release, $matches);

		if (preg_match('^(.+?)S*([0-9]*)EP*([0-9]+)^i',$release,$serieinfo)) //Try to get season and episode info from the release name
		{
			$serieinfo[1]=trim(str_replace('.',' ',$serieinfo[1])); //trim serienavn og erstatt . med mellomrom
			if($serieinfo[2]=='') //Hvis det ikke er oppgitt sesong, sett sesong til 1
				$serieinfo[2]=1;
		}
        elseif(preg_match('/(.+)S([0-9]+)/', $release,$serieinfo))
        {
            $series = $serieinfo[1];
            $season = (int)$serieinfo[2];
            $episode = 0;
            return;
        }
		else
			$serieinfo=false;
		return $serieinfo; //1=serienavn, 2=sesong
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

    /**
     * Get banner from TVDB and format with tags
     * @param array $series
     * @param bool $alternate_name
     * @return string
     */
    public function tvdb_banner($series,$alternate_name=false)
    {
        if(!isset($series['Series']))
            throw new InvalidArgumentException('Missing key "Series"');

        $episodelink=tvdb::series_link($series['Series']['id']);

        if(!empty($series['Series']['banner']))
        {
            $banner_url = 'https://artworks.thetvdb.com/banners/'.$series['Series']['banner'];
            return '[url='.$episodelink.'][img]'.$banner_url.'[/img][/url]';
        }
        else
            return sprintf('[url=%s][b]%s[/b][/url]',$episodelink,($alternate_name!==false ? $alternate_name : $series['Series']['SeriesName'])); //In case the series is not found or don't have a banner, use the series name as banner
    }
}
