<?Php

namespace datagutten\descriptionMaker;

use datagutten\musicbrainz\musicbrainz;
use datagutten\musicbrainz\exceptions\MusicBrainzException;
use SimpleXMLElement;

class MusicBrainzDescription extends musicbrainz
{

    function track_list($album)
    {
        $disc_key = 1;
        $track_key = 0;
        foreach ($album->{'release'}->{'medium-list'}->medium as $medium)
        {
            /**
             * @var $track SimpleXMLElement
             */
            foreach($medium->{'track-list'}->track as $track)
            {
                //var_dump($track_key);
                $tracknum=(int)$track->number;
                if($album->{'release'}->{'artist-credit'}->{'name-credit'}->artist->attributes()['id']=='89ad4ac3-39f7-470e-963a-56509c546377')
                {
                    $artist='';
                    $artist_credits=$track->recording->{'artist-credit'}->{'name-credit'};
                    if(!isset($artist_credits[0]))
                        $artist_credits[0]=$artist_credits;
                    foreach($artist_credits as $artist_credit) //Multiple artists
                    {
                        if(empty($artist_credit->name))
                            $artist.=$artist_credit->artist->name;
                        else
                            $artist.=$artist_credit->name;
                        if(!empty($artist_credit->attributes()['joinphrase']))
                            $artist.=$artist_credit->attributes()['joinphrase'];
                    }

                    $titles[$track_key]=sprintf('%02d %s - %s',$tracknum,$artist,$track->recording->title);
                }
                else
                    $titles[$track_key]=$tracknum.' '.$track->{'recording'}->title;

                /*$duration_sec=$track->{'length'}/1000; //Get duration in seconds
                $duration_float=$duration_sec/60;
                $duration_min=(int)$duration_float; //Remove decimals to get duration in minutes
                $durations[$track_key]=sprintf('%d:%02d',$duration_min,$duration_sec-60*$duration_min);*/
                $durations[$track_key] = utils::seconds_to_time($track->{'length'}/1000);

                $len=mb_strlen($titles[$track_key]);
                /*if($track->number>9)
                    $len++;*/
                $lengths[$track_key]=$len;
                $track_key++;
            }
            $disc_key++;
        }
        print_r($lengths);
        $max_length=max($lengths);
        $track_list='';
        foreach($titles as $track_key=>$title)
        {
            $missing_length=$max_length-$lengths[$track_key];
            $padding=str_repeat(' ',$missing_length);
            $title=$title.$padding.' '.$durations[$track_key];
            $track_list.=$title."\n";
        }
        return $track_list;
    }
    /**
     * @param $metadata_or_albumid
     * @param bool $releaseinfo
     * @return string
     * @throws MusicBrainzException
     */
	function build_description($metadata_or_albumid,$releaseinfo=false)
	{
		if(is_string($metadata_or_albumid))
			$albumid=$metadata_or_albumid;
		else
			$albumid=$metadata_or_albumid['MUSICBRAINZ_ALBUMID'];

		if($releaseinfo===false)
			$album=$this->getrelease($albumid);
		else
			$album=$releaseinfo;
		if(!is_object($album))
			return false;

		$track_count=$album->{'release'}->{'medium-list'}->medium->{'track-list'}->attributes()['count'];
		//$release_group_id=$metadata['MUSICBRAINZ_RELEASEGROUPID'];

		$amazon_link = "";
		if (!empty($asin)) {
			$amazon_link = "[url=http://www.amazon.com/exec/obidos/ASIN/" . $asin . "]Amazon[/url]" . "\n";
		}
		$country_text = "";
		if (!empty($album->{'release'}->country)) {
			$country_text = sprintf("Country: %s\n",$album->{'release'}->country);
		}
		$barcode_text = "";
		$barcode=$album->{'release'}->barcode;
		if (!empty($album->{'release'}->barcode)) {
			$barcode_text = sprintf("Barcode: %s\n",$album->{'release'}->barcode);
		}
		/*$description = $amazon_link . "[url=https://musicbrainz.org/release/" . $albumid . "]MusicBrainz[/url]" . "\n" . "\n" .
		//$description = $amazon_link . "[url=https://musicbrainz.org/release-group/" . $release_group_id . "]MusicBrainz[/url]" . "\n" . "\n" .
		$country_text .
		$barcode_text . "Tracks: " . $track_count . "\n\n" . "Track list:" . "\n";*/

		//print_r($album);
        $tracklist = $this->track_list($album);
		$description=sprintf("%s[url=https://musicbrainz.org/release/%s]MusicBrainz[/url]\n\n%s%sTracks: %d\n\nTrack list:\n[pre]%s[/pre]",
							 $amazon_link,
							 $albumid,
							 $country_text,
							 $barcode_text,
							 $track_count,
							 $tracklist);
		return $description;
	}
}