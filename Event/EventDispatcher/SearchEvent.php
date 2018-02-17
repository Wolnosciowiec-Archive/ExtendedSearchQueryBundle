<?php

namespace ExtendedSearchQueryBundle\Event\EventDispatcher;

use ExtendedSearchQueryBundle\Collection\SearchProviderCollection;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Search Event
 * ============
 *   Used to collect information
 *   about search data providers
 */
class SearchEvent extends GenericEvent
{
    /**
     * @var SearchProviderCollection $collection
     */
    private $collection;

    /**
     * @return SearchProviderCollection
     */
    public function getCollection()
    {
        if (!$this->collection instanceof SearchProviderCollection) {
            $this->collection = new SearchProviderCollection();
        }

        return $this->collection;
    }
}