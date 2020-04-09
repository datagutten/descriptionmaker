<?Php
require 'vendor/autoload.php';
require_once 'functions_description.php';
require_once 'config.php';
$tvdb=new tvdb_description();

$desc=new description;
$options=getopt('',array('tvdb_id:','tvdb_lang:'));
$file=array_pop($argv);

$pathinfo=pathinfo($file);

if(empty($options['tvdb_lang']))
	$options['tvdb_lang']=false;

if(!file_exists($file))
	die("Finner ikke filen $file\n");
if(is_file($file))
	$dir=$pathinfo['dirname'];
else //Find a video file
{
	require 'firstfile.php';
	$dir=$file;
	/*foreach(array(glob($dir.'/*.mkv'), glob($dir.'/*.ts')) as $filelist)
	{
		if(!empty($filelist))
		{
			$file=$filelist[0];
			break;
		}	
	}*/
	$file=firstfile($dir,array('mkv','ts','mp4'));
}
if(empty($file))
	die("No known files found\n");
if(substr($dir,-1,1)=='/')
	$dir=substr($dir,0,-1);

/*file_put_contents($dir.'.mediainfo',$desc->simplemediainfo($file)); //Write mediainfo to a file
$snapshots=$desc->snapshots($file,$dir.'/../snapshots');

$snapshotlinks=$desc->upload_snapshots($snapshots);
if($snapshotlinks===false)
{
	die('Failed to upload snapshots: '.$desc->error."\n");
}

print_r($snapshotlinks);
*/
$release=basename($dir);
var_dump($release);
if(!preg_match('/(.+?).S([0-9]+).+/i',$release,$matches)) //Extract title and season for TVDB search
	die("Unable to find series and season\n");
if(!isset($options['tvdb_id']))
{
	$tvdb_series=$tvdb->series_search($matches[1],$options['tvdb_lang']);
	if($tvdb_series===false)
		die($tvdb->error."\n");
	$seriesid=$tvdb_series['id'];
}
else
	$seriesid=$options['tvdb_id'];

$tvdb_series=$tvdb->get_series_and_episodes($seriesid,$options['tvdb_lang']);
if($tvdb_series===false)
	die($tvdb->error."\n");

echo "TVDB banner\n";
$description=$tvdb->banner_description($tvdb_series,$release)."\n";
echo "List episodes\n";
foreach($tvdb_series['Episode'] as $episode)
{
	if($episode['airedSeason']!=(int)$matches[2])
		continue;
	$episode['series']=$seriesid;
	$description.=sprintf("[url=%s]%s[/url]",$tvdb->episode_link($episode),(empty($episode['episodeName']) ? "Episode ".$episode['airedEpisodeNumber'] : "{$episode['airedEpisodeNumber']}: ".$episode['episodeName']))."\n";
	$description.=trim($episode['overview'])."\n";
}
echo "Snapshots bbcode\n";
$description.=$desc->snapshots_bbcode($snapshotlinks);

file_put_contents($descfile=$dir.'.txt',$description); //Write the complete description to a file
echo $descfile."\n";

