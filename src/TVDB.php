<?php

namespace datagutten\descriptionMaker;

class TVDB
{
    /**
     * @var TVDBScrape
     */
    private $tvdb_scrape;

    public function __construct()
    {
        $this->tvdb_scrape = new TVDBScrape();
    }

    function episode_list($tvdb_series_slug, $season_number, $languages = ['eng']): string
    {
        $tvdb_episodes = $this->tvdb_scrape->episodes($tvdb_series_slug, $season_number);
        $description = '';
        foreach ($tvdb_episodes as $episode)
        {
            if ($episode['airedSeason'] != $season_number)
                continue;
            list($title, $overview) = $this->tvdb_scrape->translation($episode['href'], $languages);

            $episode_string = TVSeriesUtils::season_episode((int)$episode['airedSeason'], (int)$episode['airedEpisodeNumber'], $title);
            $description .= BBCode::link(TVDBScrape::episode_link($episode), $episode_string) . "\n";
            //$overview = $this->tvdb_scrape->overview($episode['href'], ['nor', 'eng']);

            if (!empty($overview))
                $description .= trim($overview) . "\n\n";
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