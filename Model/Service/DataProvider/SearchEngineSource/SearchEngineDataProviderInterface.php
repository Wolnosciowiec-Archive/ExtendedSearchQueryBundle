<?php

namespace ExtendedSearchQueryBundle\Model\Service\DataProvider\SearchEngineSource;

use ExtendedSearchQueryBundle\Model\Entity\Search\SearchResultInterface;
use ExtendedSearchQueryBundle\Service\ExtendedSearchQuery;

/**
 * Search Engine Source Interface
 * ==============================
 *   Every search source should implement this interface
 *   and return results in SearchResultInterface[]
 */
interface SearchEngineDataProviderInterface
{
    /**
     * Provide search results
     *
     * @param array $searchIds
     * @return SearchResultInterface[]
     */
    public function provideSearchResults(array $searchIds) : array;

    /**
     * Fetch method, provides list of ids, not objects
     * this is an optimization practice to not run twice a query that may be not so fast
     * For performance reasons this method could speed up search in milions of records.
     *
     * Results are passed to provideSearchResults()
     *
     * NOTE: THIS METHOD SHOULD RETURN SORTED ELEMENTS
     *       Then those elements will be fetched in selected order, then merged with other results
     *       and re-ordered on page context
     *
     * @param ExtendedSearchQuery $query
     * @param array $options
     * @param int   $offset
     * @param int   $limit
     *
     * @see provideSearchResults()
     * @return array
     */
    public function provideSearchResultsIds(ExtendedSearchQuery $query, array $options = [], $offset = 0, $limit) : array;
}