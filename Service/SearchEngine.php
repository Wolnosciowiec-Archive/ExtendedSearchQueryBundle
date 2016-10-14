<?php

namespace ExtendedSearchQueryBundle\Service;

use ExtendedSearchQueryBundle\Exception\SearchEngineException;
use ExtendedSearchQueryBundle\ExtendedSearchQueryEvents;
use ExtendedSearchQueryBundle\Event\EventDispatcher\SearchEvent;
use ExtendedSearchQueryBundle\Model\Entity\Search\SearchResultInterface;
use ExtendedSearchQueryBundle\Model\Service\DataProvider\SearchEngineSource\SearchEngineDataProviderInterface;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Search Engine
 * =============
 *   - Call all data providers
 *   - Put them into a result set with a pagination
 *
 * @package Service
 */
class SearchEngine
{
    /**
     * @var EventDispatcherInterface $dispatcher
     */
    private $dispatcher;

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Get registered data providers for the Search Engine
     * ===================================================
     *   Data providers should attach to the AppBundleEvents::SEARCH_QUERY_EVENT
     *   and use $event->getCollection()->putProvider() to register self
     *
     * @see AppBundleEvents::SEARCH_QUERY_EVENT
     *
     * @throws SearchEngineException
     * @return SearchEngineDataProviderInterface[]
     */
    protected function getRegisteredProviders()
    {
        // create event that all providers will register to
        $event = new SearchEvent();
        $this->dispatcher
            ->dispatch(ExtendedSearchQueryEvents::SEARCH_QUERY_EVENT, $event);

        $providers = $event->getCollection()->getProviders();

        if (0 === count($providers)) {
            throw new SearchEngineException('No data providers registered');
        }

        return $providers;
    }

    /**
     * Paginate a single array
     *
     * @param array $results
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    protected function paginateResults(array &$results, $offset, $limit) : array
    {
        return array_slice($results, $offset, $limit);
    }

    /**
     * Paginate an array of arrays
     *
     * @param array $multipleResults
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    protected function paginateMultipleResults(array $multipleResults, $offset, $limit) : array
    {
        foreach ($multipleResults as $index => $result) {
            $multipleResults[$index] = $this->paginateResults($result, $offset, $limit);
        }

        return $multipleResults;
    }

    /**
     * @param SearchEngineDataProviderInterface[] $providers
     * @param int $perPage
     * @param int $page
     * @param ExtendedSearchQuery $query
     * @param array $options
     * @param int &$max
     * @param int $blockPerPage
     *
     * @return array
     */
    protected function collectProvidersData($providers, $perPage, $page, ExtendedSearchQuery $query, $options, &$max, $blockPerPage)
    {
        $providersCount = count($providers);
        $providersData = [];

        // collect total data for all pages
        $totalData = [];

        foreach ($providers as $providerName => $provider) {

            // all results for all pages (only object ids, which is very fast to fetch and to operate on)
            $totalData[$providerName] = $provider->provideSearchResultsIds($query, $options, 0, null);

            // summary of all elements
            $max += count($totalData[$providerName]);
        }

        $offsetChanges = $this->calculateOffsetChanges($totalData, $perPage, $blockPerPage, $max);

        // then paginate it
        foreach ($providers as $providerName => $provider) {

            // calculate offset on current page including a history of changes
            $offset = $this->getCurrentProviderOffset(
                $page,
                $this->getProviderBlockSize($providersCount, $perPage),
                $offsetChanges,
                $providerName
            );

            // all results for all pages (only object ids, which is very fast to fetch and to operate on)
            $providersData[$providerName] = $this->paginateResults($totalData[$providerName], $offset, $perPage);
        }

        $providersData = $this->fillPageGaps($providersData, $blockPerPage);
        return $providersData;
    }

    /**
     * Calculate the points (pages) where at least one of data providers
     * had less results to show, then other
     *
     * @param array $totalData              All results from all providers     (reference is here, as there is a possibility that the array will contain billions of keys)
     * @param int $perPage                  Total results we could show on page
     * @param int $allowedDataProviderSize  How much one data provider should be able to show? (reserved space)
     * @param int $max                      Total count of all results
     *
     * @return array
     */
    protected function calculateOffsetChanges(array &$totalData, $perPage, $allowedDataProviderSize, $max) : array
    {
        $offsetChanges = [];
        $totalPages    = (int)round($max / $perPage, 0, PHP_ROUND_HALF_UP);

        foreach ($totalData as $providerName => $ids) {
            $providerMaxPages = (int)round(count($totalData) / $perPage, 0, PHP_ROUND_HALF_UP);


            // note that on this page there is a change
            if ($providerMaxPages < $totalPages) {
                $offset = $providerMaxPages * $allowedDataProviderSize;

                // include partial results
                $offsetChanges[$providerMaxPages] = $this->calculateExtendedSpace(
                    $this->paginateMultipleResults($totalData, $offset, $perPage),
                    $allowedDataProviderSize
                );

                // include case when there are NO MORE ANY results to show
                $offsetChanges[($providerMaxPages + 1)] = $this->calculateExtendedSpace(
                    $this->paginateMultipleResults($totalData, ($offset + $allowedDataProviderSize), $perPage),
                    $allowedDataProviderSize
                );
            }
        }

        return $offsetChanges;
    }

    /**
     * Fill elements count on page to fixed size
     * =========================================
     *
     *   1. On a single page we could have multiple data providers
     *   for results
     *   2. One data provider could output less data
     *   than its expected.
     *   3. To have a fixed number of elements on
     *   every page we need to detect which provider is
     *   outputting less than assigned and re-use its free space
     *
     * @param array $providersData              List of ids
     * @param int   $allowedDataProviderSize    How much one data provider should be able to show? (reserved space)
     *
     * @return array
     */
    protected function fillPageGaps($providersData, $allowedDataProviderSize)
    {
        $entriesToExtend = $this->calculateExtendedSpace($providersData, $allowedDataProviderSize);

        // truncate every provider to selected limit
        // if the provider got an additional space from other
        // provider then it will be included here from $entriesToExtend
        foreach ($providersData as $providerName => $items) {
            $limit = $allowedDataProviderSize;

            if (isset($entriesToExtend[$providerName])) {
                $limit += $entriesToExtend[$providerName];
            }

            $providersData[$providerName] = array_slice($items, 0, $limit);
        }

        return $providersData;
    }

    /**
     * Only calculates the space to allow later fill gaps leaved
     * by some data providers
     *
     *   Returns a list of providers that should have
     *   extended its space, because their neighbours
     *   can't show more results anymore
     *
     * @param array $providersData
     * @param int   $allowedDataProviderSize    How much one data provider should be able to show? (reserved space)
     *
     * @return array
     */
    protected function calculateExtendedSpace($providersData, $allowedDataProviderSize)
    {
        $entriesToExtend = [];
        $providersLength = $this->getResultsCount($providersData);

        // look for providers that have LESS RESULTS THAN WE EXPECT
        // so the FREE SPACE would remain
        foreach ($providersLength as $providerName => $length) {
            if ($length < $allowedDataProviderSize) {
                $missingLength = $allowedDataProviderSize - $length;

                // find a provider that has more results to show
                // and can take a free space that we have
                // then put it into a $entriesToExtend[$providerName]
                foreach ($providersLength as $targetIndex => $targetLen) {
                    if ($targetLen >= $allowedDataProviderSize + $missingLength) {
                        if (!isset($entriesToExtend[$targetIndex])) {
                            $entriesToExtend[$targetIndex] = 0;
                        }

                        $entriesToExtend[$targetIndex] += $missingLength;
                    }
                }
            }
        }

        return $entriesToExtend;
    }

    /**
     * @param array $providersData
     * @return array
     */
    protected function getResultsCount($providersData)
    {
        $providersLength = array_map(function ($data) { return count($data); }, $providersData);
        arsort($providersLength);

        return $providersLength;
    }

    /**
     * Performs the search
     * ===================
     *   Result format is:
     *     results: SearchResultInterface[]
     *     total: int (total results count for all pages)
     *
     * @param ExtendedSearchQuery $query
     * @param array $options
     * @param int $page
     * @param int $allowedDataProviderSize   How much one data provider should be able to show? (reserved space)
     *
     * @return array
     */
    public function query(ExtendedSearchQuery $query, $options = [], $page = 1, $allowedDataProviderSize = 5) : array
    {
        $results           = []; // on current page
        $totalResultsCount = 0;  // count of all results on all pages

        $providers = $this->getRegisteredProviders();

        // number of total results from all providers on a single page
        $resultsPerPage        = count($providers) * $allowedDataProviderSize;

        // available data to use, with a reserve in case when page should have a filled gap
        $providersData = $this->collectProvidersData($providers, $resultsPerPage, $page, $query, $options, $totalResultsCount, $allowedDataProviderSize);

        foreach ($providers as $providerName => $provider) {
            $results = array_merge(
                $results,
                $provider->provideSearchResults($providersData[$providerName])
            );

            $results = $this->reorderResults($results);
        }

        return [
            // current results for actual page
            'results' => $results,

            // maximum elements of all providers (in simple words: count of all results from the query)
            'total'   => $totalResultsCount,

            // count of elements that are displayed per single page
            'perPage' => $resultsPerPage,
        ];
    }

    /**
     * Get offset for a block including an offset change
     * due to filling up the page from one provider
     * because other had no more results to show
     *
     * @param int $page
     * @param int $blockSize
     * @param array $offsetChanges          Array indexed by page number on which there is a
     *                                      list of elements that have increased offset and the amount
     * @param string $currentProviderName   Provider in current iteration/context
     *
     * @return int
     */
    protected function getCurrentProviderOffset($page, $blockSize, $offsetChanges, $currentProviderName)
    {
        $currentSize = $blockSize;
        $offset      = 0;

        for ($pageNum = 1; $pageNum <= ($page - 1); $pageNum++) {

            if (isset($offsetChanges[$pageNum][$currentProviderName])) {
                $currentSize = $blockSize + $offsetChanges[$pageNum][$currentProviderName];
            }

            $offset += $currentSize;
        }

        return $offset;
    }

    /**
     * @param SearchResultInterface[] $results
     * @return SearchResultInterface[]
     */
    protected function reorderResults($results)
    {
        usort($results, function (SearchResultInterface $a, SearchResultInterface $b) {
            return strnatcmp($a->getTitle(), $b->getTitle());
        });

        return $results;
    }

    /**
     * @param int $providersCount
     * @param int $perPage
     *
     * @return int
     */
    protected function getProviderBlockSize($providersCount, $perPage)
    {
        return $perPage / $providersCount;
    }
}