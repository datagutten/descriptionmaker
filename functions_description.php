<?Php

use datagutten\image_host\exceptions\UploadFailed;
use datagutten\image_host\image_host;
use datagutten\video_tools\exceptions\DurationNotFoundException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use datagutten\tvdb\tvdb;

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
     * @var image_host
     */
	public $image_host;
	public $error;

	function __construct()
	{
		$config = require 'config.php';
		$this->dependcheck=new dependcheck;
		$this->video=new video;
		$this->image_host=new $config['image_host'];
	}

    /**
     * Parse series information from release name
     * @param $release
     * @return array|bool Return false on failure
     */
	public function serieinfo($release)
	{
		if (preg_match('^(.+?)S*([0-9]*)EP*([0-9]+)^i',$release,$serieinfo)) //Try to get season and episode info from the release name
		{
			$serieinfo[1]=trim(str_replace('.',' ',$serieinfo[1])); //trim serienavn og erstatt . med mellomrom
			if($serieinfo[2]=='') //Hvis det ikke er oppgitt sesong, sett sesong til 1
				$serieinfo[2]=1;
		}
		else
			$serieinfo=false;
		return $serieinfo; //1=serienavn, 2=sesong
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
	public function snapshots($file,$snapshotdir=false)
	{
		$positions=$this->video->snapshotsteps($file,4); //Calcuate snapshot positions
		if(empty($snapshotdir)) //Create snapshot directory in video folder if other folder is not specified
			$snapshotdir=dirname($file).'/snapshots';
		if(!file_exists($snapshotdir))
			mkdir($snapshotdir,0777,true);
		return $this->video->snapshots($file,$positions,$snapshotdir);
	}

    /**
     * Upload snapshots using imagehost class
     * @param array $snapshots
     * @param string $prefix
     * @return array
     * @throws UploadFailed Image upload failed
     */
	function upload_snapshots($snapshots, $prefix = '')
	{
		if(empty($snapshots))
			throw new InvalidArgumentException('Snapshots empty');
		$snapshotlinks = [];
		foreach ($snapshots as $key=>$snapshot)
		{
		    if(!empty($prefix))
            {
                $pathinfo = pathinfo($snapshot);
                $newfile = sprintf('%s/%s_%s.%s', $pathinfo['dirname'], $prefix, $pathinfo['filename'], $pathinfo['extension']);
                rename($snapshot, $newfile);
                $snapshot = $newfile;
            }
			$upload=$this->image_host->upload($snapshot);
			$snapshotlinks[$key]=$upload;
		}
		return $snapshotlinks;
	}

    /**
     * @param array $snapshotlinks
     * @return string
     */
	function snapshots_bbcode($snapshotlinks)
	{
		$bbcode='';
		foreach ($snapshotlinks as $screenshot) //Lag screenshots
		{
			if(method_exists($this->image_host,'bbcode'))
				$bbcode.=$this->image_host->bbcode($screenshot);
			else
				$bbcode.=sprintf('[img]%s[/img]',$screenshot);
		}
		return $bbcode;
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
	public function simplemediainfo($file)
	{
        if(!file_exists($file))
            throw new FileNotFoundException($file);
        $this->dependcheck->depend('mediainfo');

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
