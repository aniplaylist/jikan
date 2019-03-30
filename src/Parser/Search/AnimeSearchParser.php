<?php

namespace Jikan\Parser\Search;

use Jikan\Helper\TimeProvider;
use Jikan\Model\Search\AnimeSearch;
use Jikan\Model\Search\AnimeSearchListItem;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class AnimeSearchParser
 *
 * @package Jikan\Parser
 */
class AnimeSearchParser
{
    /**
     * @var Crawler
     */
    private $crawler;

    /**
     * @var TimeProvider
     */
    private $timeProvider;

    /**
     * AnimeSearchParser constructor.
     *
     * @param Crawler $crawler
     */
    public function __construct(Crawler $crawler, TimeProvider $timeProvider)
    {
        $this->crawler = $crawler;
        $this->timeProvider = $timeProvider;
    }

    /**
     * @return AnimeSearch
     * @throws \Exception
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getModel(): AnimeSearch
    {
        return AnimeSearch::fromParser($this);
    }

    /**
     * @return AnimeSearchListItem[]
     * @throws \Exception
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getResults(): array
    {
        $results = $this->crawler
            ->filterXPath('//div[contains(@class, "js-categories-seasonal")]/table/tr[1]');

        if (!$results->count()) {
            return [];
        }

        return $results->nextAll()
            ->each(
                function (Crawler $c) {
                    return (new AnimeSearchListItemParser($c, $this->timeProvider))->getModel();
                }
            );
    }

    /**
     * @return int
     * @throws \InvalidArgumentException
     */
    public function getLastPage(): int
    {
        $pages = $this->crawler
            ->filterXPath('//div[contains(@class, "normal_header")]/div/div/span/a');

        if (!$pages->count()) {
            return 1;
        }

        return (int)$pages->last()->text();
    }
}
