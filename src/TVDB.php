<?php

namespace datagutten\descriptionMaker;
use datagutten\tvdb\TVDBScrape;

class TVDB
{
    /**
     * @var TVDBScrape
     */
    private TVDBScrape $tvdb_scrape;

    public function __construct()
    {
        $this->tvdb_scrape = new TVDBScrape();
    }

    function episode_list($tvdb_series_slug, $season_number, $languages = ['eng'], $ordering='official'): string
    {
        $tvdb_episodes = $this->tvdb_scrape->episodes($tvdb_series_slug, $ordering, true);
        $season = $this->tvdb_scrape->season($tvdb_series_slug, $season_number, $ordering);
        $description = '';
        foreach ($season as $id=>$episode_num)
        {
            $episode = $tvdb_episodes[$id];

            list($title, $overview) = $this->tvdb_scrape->translation($episode->url, $languages);
            $description .= BBCode::link($episode->url, $episode->episode_title()) . "\n";

            if (!empty($episode->description))
                $description .= trim($episode->description) . "\n\n";
        }
        return $description;
    }

    function episode(string $tvdb_series_slug, int $season_number, int $episode_number, $languages = ['eng']): string
    {
        $tvdb_episodes = $this->tvdb_scrape->episodes($tvdb_series_slug, $season_number);
        $episode_string = TVSeriesUtils::season_episode($season_number, $episode_number);
        $episode = $tvdb_episodes[$episode_string]; //TODO: Check isset
        list($title, $overview) = $this->tvdb_scrape->translation($episode['href'], $languages);

        $title = TVSeriesUtils::season_episode($season_number, $episode_number, $title);
        $description = BBCode::link(TVDBScrape::episode_link($episode), $title) . "\n";
        if(!empty($overview))
            $description .= $overview;
        return $description;
    }
}