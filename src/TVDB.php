<?php

namespace datagutten\descriptionMaker;
use datagutten\tvdb\objects\Episode;
use datagutten\tvdb\objects\Season;

class TVDB
{
    function episode_list(Season $season): string
    {
        $description = '';
        foreach ($season->episodes(true) as $id=>$episode)
        {
            $description .= BBCode::link($episode->url(), $episode->episode_title()) . "\n";
            if (!empty($episode->description))
                $description .= trim($episode->description) . "\n\n";
        }
        return $description;
    }

    function episode(Episode $episode): string
    {
		$title = $episode->episode_name();
		$description = BBCode::link($episode->url(), $title) . "\n";

        if(!empty($episode->description))
            $description .= $episode->description;
        return $description;
    }
}