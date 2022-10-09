<?php

namespace datagutten\descriptionMaker;
use datagutten\tvdb\TVDBScrape;

class TVDB
{
    /**
     * @var TVDBScrape
     */
    private TVDBScrape $tvdb_scrape;
	public string $language;

    public function __construct($language = 'eng')
    {
        $this->language = $language;
        $this->tvdb_scrape = new TVDBScrape();
    }

    function episode_list(string $tvdb_series_slug, int $season_number, string $ordering='official'): string
    {
        $tvdb_episodes = $this->tvdb_scrape->episodes($tvdb_series_slug, $ordering, true, $season_number);
        $description = '';
        foreach ($tvdb_episodes as $id=>$episode)
        {
            $description .= BBCode::link($episode->url(), $episode->episode_title()) . "\n";
            if (!empty($episode->description))
                $description .= trim($episode->description) . "\n\n";
        }
        return $description;
    }

    function episode(string $tvdb_series_slug, int $season_number, int $episode_number): string
    {
		$series_obj = $this->tvdb_scrape->series($tvdb_series_slug, $this->language);
		$season_obj = $series_obj->season($season_number);
		$episode_obj = $season_obj->episode($episode_number);
		
		$title = $episode_obj->episode_name();
		$description = BBCode::link($episode_obj->url(), $title) . "\n";

        if(!empty($episode_obj->description))
            $description .= $episode_obj->description;
        return $description;
    }
}