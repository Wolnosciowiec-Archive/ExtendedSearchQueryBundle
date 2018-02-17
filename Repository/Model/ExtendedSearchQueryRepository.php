<?php

namespace ExtendedSearchQueryBundle\Repository\Model;

use Doctrine\ORM\QueryBuilder;
use ExtendedSearchQueryBundle\Service\ExtendedSearchQuery;

/**
 * ExtendedSearchQueryRepository
 * =============================
 * Repository extension that integrates repositories with
 * ExtendedSearchQuery parser, which allows to search elements by keywords
 * included keywords and excluded keywords
 */
trait ExtendedSearchQueryRepository
{
    /**
     * Add extended search query conditions using query builder
     * ========================================================
     *
     * @param ExtendedSearchQuery $searchQuery
     * @param QueryBuilder $qb
     * @param array $fields
     * @param string $alias
     */
    public function addExtendedSearchQuery(ExtendedSearchQuery $searchQuery, QueryBuilder $qb, $fields, $alias)
    {
        $alias         .= '.';
        $includePhrases = '';
        $excludePhrases = '';

        foreach ($fields as $field) {

            // phrases that are included in the query
            foreach ($searchQuery->getSearchPhrases() as $index => $phrase) {
                $includePhrases .= ' or ' . $alias . $field . ' LIKE :included_keyword_' . $index;
                $qb->setParameter('included_keyword_' . $index, '%' . $phrase . '%');
            }

            // phrases that are excluding items in the query
            foreach ($searchQuery->getExcludePhrases() as $index => $phrase) {
                $excludePhrases .= ' and ' . $alias . $field . ' NOT LIKE :excluded_keyword_' . $index;
                $qb->setParameter('excluded_keyword_' . $index, '%' . $phrase . '%');
            }
        }

        if (count($searchQuery->getSearchPhrases()) > 0) {
            $qb->andWhere('(' . ltrim($includePhrases, 'or ') . ')');
        }

        if (count($searchQuery->getExcludePhrases()) > 0) {
            $qb->andWhere('(' . ltrim($excludePhrases, 'and ') . ')');
        }
    }
}