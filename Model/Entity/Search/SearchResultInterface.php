<?php

namespace ExtendedSearchQueryBundle\Model\Entity\Search;

/**
 * Search Result Interface
 */
interface SearchResultInterface
{
    /**
     * @return string
     */
    public function getTitle() : string;

    /**
     * @return string
     */
    public function getCategoryName() : string;

    /**
     * @return string
     */
    public function getShortDescription() : string;

    /**
     * @return string
     */
    public function getPreviewImage() : string;

    /**
     * Should return a route name from the routing
     * that after navigating to will show a correct element
     *
     * @return string
     */
    public function getRouteName() : string;

    /**
     * @return string
     */
    public function getMetaText() : string;

    /**
     * Get additional route parameters eg. object id
     * =============================================
     *
     * @return array
     */
    public function getRouteParameters() : array;
}