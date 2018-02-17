<?php

namespace ExtendedSearchQueryBundle\Service;

/**
 * A search query form
 */
class ExtendedSearchQuery
{
    /**
     * Our search phrase (query string)
     *
     * @var string $phrase
     */
    protected $query;

    /**
     * @var string[] $searchPhrases
     */
    protected $searchPhrases = [];

    /**
     * @var string[] $excludePhrases
     */
    protected $excludePhrases = [];

    /**
     * @var string[] $options
     */
    protected $options = [];

    /**
     * @var string[] $availableOptions
     */
    protected $availableOptions = [];

    /**
     * @var bool $limitReached
     */
    protected $limitReached = false;

    /**
     * @param string $query
     * @param int $keywordsLimit
     * @param array $options
     */
    public function __construct($query, $keywordsLimit = 10, array $options = [])
    {
        // put a space for easier matching in regular expressions
        $this->availableOptions = $options;
        $this->query            = trim($query) . ' ';
        $this->parseQuery($keywordsLimit);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->query;
    }

    /**
     * Parse query using a regexp and find keywords/sentences to include/exclude
     *
     * @param int $keywordsLimit
     */
    protected function parseQuery($keywordsLimit)
    {
        preg_match_all('/(([\-\+]?)"(.*?)"|([\-\+\!]?)\w+)/i', $this->query, $matches);

        if (count($matches) > $keywordsLimit) {
            $matches = array_slice($matches, 0, $keywordsLimit);
            $this->limitReached = true;
        }

        foreach ($matches[0] as $keyword)
        {
            $operator = substr($keyword, 0, 1);
            $keyword  = str_replace('"', '', $keyword);

            if ($operator === '-')
            {
                $this->excludePhrases[] = substr($keyword, 1);
            }
            elseif ($operator === '+')
            {
                $this->searchPhrases[] = substr($keyword, 1);
            }
            elseif ($operator === '!' && isset($this->availableOptions[substr($keyword, 1)]))
            {
                $this->options[] = $this->availableOptions[substr($keyword, 1)];
            }
            else
            {
                $this->searchPhrases[] = $keyword;
            }
        }
    }

    /**
     * @return \string[]
     */
    public function getSearchPhrases()
    {
        return $this->searchPhrases;
    }

    /**
     * @return \string[]
     */
    public function getExcludePhrases()
    {
        return $this->excludePhrases;
    }

    /**
     * @return \string[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Is limit of keywords reached?
     * There is a possibility to limit count of keywords
     * passed to the search, this prevents from DoS attacks
     * on the search engine by not allowing to pass eg. 10000 keywords
     * to with "LIKE" operator into where clause
     *
     * @return boolean
     */
    public function isLimitReached()
    {
        return $this->limitReached;
    }
}