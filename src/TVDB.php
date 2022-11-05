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

    public function __construct($language = 'eng', TVDBScrape $tvdb = null)
    {
        $this->language = $language;
        if (empty($tvdb))
            $this->tvdb_scrape = new TVDBScrape();
        else
            $this->tvdb_scrape = $tvdb;
    }

    function episode_list(string $tvdb_series_slug, int $season_number, string $ordering='official'): string //TODO: Change argument to season object
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

    function episode(string $tvdb_series_slug, int $season_number, int $episode_number): string //TODO: Change argument to episode object
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