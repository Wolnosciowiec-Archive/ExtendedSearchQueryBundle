<?php

namespace ExtendedSearchQueryBundle\Exception\Collection;

/**
 * CollectionException
 * ===================
 *   Occurs in object collections
 */
class CollectionException extends \LogicException
{
    /**
     * CollectionException constructor.
     *
     * @param string $offset
     * @param mixed $element
     * @param string $duplicatedOffset
     */
    public function __construct($offset, $element, $duplicatedOffset)
    {
        switch (gettype($element)) {
            case 'object': $elementName = get_class($element); break;

            case 'resource':
            case 'array':
                $elementName  = gettype($element);
                break;

            default: $elementName = $element; break;
        }

        parent::__construct('Element "' . $elementName . '" at offset "' . $offset . '" is already duplicated on offset "' . $duplicatedOffset . '"');
    }
}