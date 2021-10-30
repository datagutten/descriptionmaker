<?php

namespace datagutten\descriptionMaker;

use InvalidArgumentException;

class TVSeriesUtils
{

    public static function season_episode(int $season = null, int $episode = null, string $title = null): string
    {
        if (!empty($season) && !empty($episode))
            $string = sprintf('S%02dE%02d', $season, $episode);
        elseif (!empty($episode))
            $string = sprintf('EP%02d', $episode);
        elseif (!empty($title))
            return $title;
        else
            throw new InvalidArgumentException('All arguments empty');

        if (!empty($title))
            return sprintf('%s - %s', $string, $title);
        else
            return $string;
    }

    /**
     * Parse series information from release name
     * @param $release
     * @return array Return false on failure
     */
    public static function parse_release($release): array
    {
        if (preg_match('^(.+?)S*([0-9]*)EP*([0-9]+)^i', $release, $matches)) //Try to get season and episode info from the release name
        {
            $series = $matches[1];
            if (empty($matches[2])) //Hvis det ikke er oppgitt sesong, sett sesong til 1
                $season = 1;
            else
                $season = (int)$matches[2];
            $episode = (int)$matches[3];
        }
        elseif (preg_match('/(.+)S([0-9]+)/', $release, $matches))
        {
            $series = $matches[1];
            $season = (int)$matches[2];
            $episode = 0;
        }
        else //No season and episode, try to strip quality to get series name
        {
            preg_match('#(.+?)(?:[\s.][0-9]{3,4}[ip]|PAL|[A-Z]DTV|WEB-DL)#', $release, $matches);
            if (!empty($matches))
                $series = $matches[1];
            else
                $series = $release;
            $season = 1;
            $episode = 0;
        }
        $series = trim(str_replace('.', ' ', $series)); //trim serienavn og erstatt . med mellomrom
        return ['series' => $series, 'season' => $season, 'episode' => $episode];
    }
}