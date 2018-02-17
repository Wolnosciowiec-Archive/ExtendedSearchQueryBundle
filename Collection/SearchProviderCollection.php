<?php

namespace ExtendedSearchQueryBundle\Collection;

use ExtendedSearchQueryBundle\Exception\Collection\CollectionException;
use ExtendedSearchQueryBundle\Model\Service\DataProvider\SearchEngineSource\SearchEngineDataProviderInterface;

/**
 * Search Provider Collection
 * ==========================
 *   List of search engine result providers
 */
class SearchProviderCollection
{
    /**
     * @var SearchEngineDataProviderInterface[] $providers
     */
    protected $providers = [];

    /**
     * @param SearchEngineDataProviderInterface $provider
     *
     * @return $this
     */
    public function putProvider(SearchEngineDataProviderInterface $provider)
    {
        if (isset($this->providers[get_class($provider)])) {
            throw new CollectionException(get_class($provider), $provider, get_class($provider));
        }

        $this->providers[get_class($provider)] = $provider;
        return $this;
    }

    /**
     * @return SearchEngineDataProviderInterface[]
     */
    public function getProviders()
    {
        return $this->providers;
    }
}