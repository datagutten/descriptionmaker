<?php

use datagutten\image_host\imgur;
use datagutten\tvdb\tvdb;

require 'vendor/autoload.php';
class tvdb_description extends tvdb
{
	private $imagehost;
	function __construct()
	{
		parent::__construct();
		$this->imagehost=new imgur;
	}
	public function banner_description($series,$alternate_name=false)
	{
		$episodelink=$this->series_link($series['Series']['id']);

		if(!empty($series['Series']['banner']))
		{
			copy('http://www.thetvdb.com/banners/'.$series['Series']['banner'],'/tmp/banner');
			$upload_banner=$this->imagehost->upload('/tmp/banner'); //Upload the banner
			return '[url='.$episodelink.'][img]'.$upload_banner.'[/img][/url]';
		}
		else
			return sprintf('[url=%s][b]%s[/b][/url]',$episodelink,($alternate_name!==false ? $alternate_name : $series['Series']['SeriesName'])); //In case the series is not found or don't have a banner, use the series name as banner	
	}
}