<?Php

namespace datagutten\descriptionMaker;

use datagutten\musicbrainz\exceptions\MusicBrainzException;
use datagutten\musicbrainz\musicbrainz;
use datagutten\musicbrainz\seed;
use datagutten\video_tools\video;
use InvalidArgumentException;

class MusicBrainzDescription extends musicbrainz
{
    /**
     * Build a formatted track list from a release object
     * @param seed\Release $release
     * @return string
     */
    public static function track_list(seed\Release $release): string
    {
        $disc_key = 1;
        $track_key = 0;
        /**
         * @var int[] $lengths Line lengths
         */
        $lengths = []; //Line lengths
        /**
         * Track durations
         */
        $durations = []; //Track durations
        /**
         * @var string[] $titles Track titles
         */
        $titles = [];

        foreach ($release->mediums as $medium)
        {
            foreach($medium->tracks as $track)
            {
                //var_dump($track_key);
                $tracknum=(int)$track->number;
                if ($release->artists[0]['id'] == '89ad4ac3-39f7-470e-963a-56509c546377')
                {
                    $artist='';
                    foreach($track->artists as $artist_credit) //Multiple artists
                    {
                        if(empty($artist_credit->name))
                            $artist.=$artist_credit->artist_name;
                        else
                            $artist.=$artist_credit->name;
                        if(!empty($artist_credit->join_phrase))
                            $artist.=$artist_credit->join_phrase;
                    }

                    $titles[$track_key]=sprintf('%02d %s - %s',$tracknum,$artist,$track->title);
                }
                else
                    $titles[$track_key]=$tracknum.' '.$track->title;

                $durations[$track_key] = video::seconds_to_time($track->length/1000);

                $len=mb_strlen($titles[$track_key]);
                /*if($track->number>9)
                    $len++;*/
                $lengths[$track_key]=$len;
                $track_key++;
            }
            $disc_key++;
        }
        //print_r($lengths);
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
     * @param string $album_id Album ID
     * @param string $position Art position
     * @return array
     * @throws MusicBrainzException
     */
    function cover_art(string $album_id, string $position = 'front'): array
    {
        try
        {
            $response = $this->get('https://coverartarchive.org/release/' . $album_id); //, ['Accept'=>'application/json']
        }
        catch (MusicBrainzException $e)
        {
            return [];
        }

        if($response->status_code == 404)
            return [];
        elseif(!$response->success)
            throw new MusicBrainzException('Error fetching cover art');

        $images = json_decode($response->body, true);
        foreach($images['images'] as $image)
        {
            if(!isset($image[$position]))
                throw new InvalidArgumentException('Invalid position, must be front or back');
            if($image[$position] === true)
                return $image;
        }
        return [];
    }

    /**
     * @param $metadata_or_albumid
     * @param ?seed\Release $release Release object
     * @param bool $cover_art
     * @return string
     * @throws MusicBrainzException
     */
	function build_description($metadata_or_albumid, seed\Release $release = null, bool $cover_art = true): string
	{

		if(is_string($metadata_or_albumid))
			$albumid=$metadata_or_albumid;
		else
			$albumid=$metadata_or_albumid['MUSICBRAINZ_ALBUMID'];

		if(empty($release))
            $release = $this->releaseFromMBID($albumid, ['artists', 'recordings', 'artist-credits']);

        if($cover_art)
        {
            $mb_art = $this->cover_art($albumid);
            if(!empty($mb_art))
                $art = sprintf("[url=%s][img]%s[/img][/url]\n", $mb_art['image'], $mb_art['thumbnails'][250]);
        }

		$track_count=count($release->mediums[0]->tracks); // TODO: Summarize track count for multiple discs

		/*$country_text = "";
		if (!empty($album->{'release'}->country)) {
			$country_text = sprintf("Country: %s\n",$album->{'release'}->country);
		}*/
        $links = $this->get_links($release);
        $link_string = BBCode::link($release->link(), 'MusicBrainz') . "\n";
        foreach ($links as $link)
        {
            $link_string .= BBCode::link($link['url'], $link['text']) . "\n";
        }

		$barcode_text = "";
		if (!empty($release->barcode)) {
			$barcode_text = sprintf("Barcode: %s\n",$release->barcode);
		}

        $tracklist = self::track_list($release);
        return sprintf("%s%s\n\n%s%sTracks: %d\n\nTrack list:\n[pre]%s[/pre]",
                             $art ?? '',
                             $link_string,
                             $country_text ?? '',
                             $barcode_text,
                             $track_count,
                             $tracklist);
	}
}