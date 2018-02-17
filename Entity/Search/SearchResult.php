<?php

namespace ExtendedSearchQueryBundle\Entity\Search;

use ExtendedSearchQueryBundle\Model\Entity\Search\SearchResultInterface;

/**
 * A single entry on the search results list
 */
class SearchResult implements SearchResultInterface
{
    /**
     * @var string $title
     */
    protected $title;

    /**
     * @var string $previewImage
     */
    protected $previewImage;

    /**
     * @var string $shortDescription
     */
    protected $shortDescription;

    /**
     * @var string $routeParameters
     */
    protected $routeParameters;

    /**
     * @var string $routeName
     */
    protected $routeName;

    /**
     * @var string $categoryName
     */
    protected $categoryName = '';

    /**
     * @var string $metaText
     */
    protected $metaText;

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getPreviewImage() : string
    {
        return $this->previewImage;
    }

    /**
     * @param string $previewImage
     * @return $this
     */
    public function setPreviewImage($previewImage)
    {
        $this->previewImage = $previewImage;
        return $this;
    }

    /**
     * @return string
     */
    public function getShortDescription() : string
    {
        return $this->shortDescription;
    }

    /**
     * @param string $shortDescription
     * @return $this
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;
        return $this;
    }

    /**
     * @return array
     */
    public function getRouteParameters() : array
    {
        return $this->routeParameters;
    }

    /**
     * @return string
     */
    public function getRouteName() : string
    {
        return $this->routeName;
    }

    /**
     * @param string $name
     * @param array $parameters
     *
     * @return $this
     */
    public function setRoute($name, array $parameters)
    {
        $this->routeName       = $name;
        $this->routeParameters = $parameters;
        return $this;
    }

    /**
     * @param string $categoryName
     * @return $this
     */
    public function setCategoryName($categoryName)
    {
        $this->categoryName = $categoryName;
        return $this;
    }

    /**
     * @return string
     */
    public function getCategoryName() : string
    {
        return $this->categoryName;
    }

    /**
     * @return string
     */
    public function getMetaText() : string
    {
        return $this->metaText;
    }

    /**
     * @param string $metaText
     * @return $this
     */
    public function setMetaText($metaText)
    {
        $this->metaText = $metaText;
        return $this;
    }
}
