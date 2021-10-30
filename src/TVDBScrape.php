<?php


namespace datagutten\descriptionMaker;


use DOMDocument;
use DOMNode;
use DOMXPath;

class TVDBScrape
{
    /**
     * @var \Requests_Session
     */
    private $session;

    public function __construct()
    {
        $this->session = new \Requests_Session('https://thetvdb.com/');
    }

    public static function parse_episode(string $episode): array
    {
        preg_match('/S([0-9]+)E([0-9]+)/', $episode, $matches);
        return [(int)$matches[1], (int)$matches[2]];
    }

    /**
     * Create link to an episode
     * @param array $episode Array of episode information (returned by episode_info or find_episode_by_name)
     * @return string Link to the episode
     */
    public static function episode_link(array $episode): string
    {
        return sprintf('https://www.thetvdb.com%s', $episode['href']);
    }

    function episodes(string $series, int $season, $type = 'official'): array
    {
        $response = $this->session->get(sprintf('/series/%s/seasons/%s/%d', $series, $type, $season));
        $response->throw_for_status(); //TODO: Add custom exception
        $dom = new DOMDocument();
        @$dom->loadHTML($response->body);
        $xpath = new DOMXPath($dom);

        $rows = $xpath->query('//tbody/tr');
        $episodes = [];
        foreach ($rows as $row)
        {
            $cols = $xpath->query('./td', $row);
            $episode = [];
            $episode['seasonEpisode'] = $cols->item(0)->textContent;
            //$a = $row->getElementsByTagName('a')->;
            $a = $xpath->query('td/a', $row)->item(0);
            $episode['episodeName'] = trim($a->textContent);
            $episode['href'] = $a->getAttribute('href');
            $episode['id'] = (int)preg_replace('#/series/.+/episodes/([0-9]+)#', '$1', $episode['href']);
            list($episode['airedSeason'], $episode['airedEpisodeNumber']) = self::parse_episode($episode['seasonEpisode']);
            $episodes[$episode['seasonEpisode']] = $episode;
        }
        return $episodes;
    }

    /**
     * Get overview languages
     * @param DOMXPath $xpath
     * @return array
     */
    public static function languages(DOMXPath $xpath): array
    {
        $languages_dom = $xpath->query("//div[@id='translations']/div/@data-language");
        $languages = [];
        foreach ($languages_dom as $language)
        {
            $languages[] = $language->nodeValue;
        }
        return $languages;
    }

    public function episode(string $episode_href, $languages = []): ?DOMNode
    {
        $response = $this->session->get($episode_href);
        $dom = new DOMDocument();
        @$dom->loadHTML($response->body);
        $xpath = new DOMXPath($dom);
        //$languages = self::languages($xpath);
        if ($languages)
        {
            foreach ($languages as $language)
            {
                $translation = $xpath->query(sprintf("//div[@id='translations']/div[@data-language=\"%s\"]", $language));
                if ($translation->length > 0)
                    break;
            }
        }
        else //Use first language
            $translation = $xpath->query("//div[@id='translations']/div");

        if (empty($translation))
            return null;
        return $translation->item(0);
    }

    function overview(string $episode_href, $languages = []): ?string
    {
        $translation = $this->episode($episode_href, $languages);
        if (empty($translation))
            return null;
        return $translation->firstChild->textContent;
    }

    function translation(string $episode_href, $languages = []): array
    {
        $translation = $this->episode($episode_href, $languages);
        if(empty($translation))
            return [];
        $title = $translation->attributes->getNamedItem('data-title')->textContent;
        $overview = $translation->childNodes->item(1)->textContent;
        return [$title, $overview];
    }
}