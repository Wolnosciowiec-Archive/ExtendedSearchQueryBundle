<?php

namespace ExtendedSearchQueryBundle\Tests\Repository\Model;

use Doctrine\ORM\QueryBuilder;
use ExtendedSearchQueryBundle\Repository\Model\ExtendedSearchQueryRepository;
use ExtendedSearchQueryBundle\Service\ExtendedSearchQuery;

/**
 * ExtendedSearchQueryRepositoryTest
 * =================================
 *
 * @see ExtendedSearchQuery
 * @see ExtendedSearchQueryRepository
 */
class ExtendedSearchQueryRepositoryTest extends ContainerAwareTestCase
{
    use ExtendedSearchQueryRepository;

    /**
     * @return array
     */
    public function queryStringDataProvider()
    {
        return [
            'Search phrase + exclude' => [
                'concert -disco',
                '(event.title LIKE :included_keyword_0 or event.description LIKE :included_keyword_0)) AND ((event.title NOT LIKE :excluded_keyword_0 and event.description NOT LIKE :excluded_keyword_0)',
                [
                    'included_keyword_0' => '%concert%',
                    'excluded_keyword_0' => '%disco%',
                ],
            ],

            'Four search phrases' => [
                'concert of the analogs',
                'event.title LIKE :included_keyword_0 or event.title LIKE :included_keyword_1 or event.title LIKE :included_keyword_2 or event.title LIKE :included_keyword_3 or event.description LIKE :included_keyword_0 or event.description LIKE :included_keyword_1 or event.description LIKE :included_keyword_2 or event.description LIKE :included_keyword_3',
                [
                    'included_keyword_0' => '%concert%',
                    'included_keyword_1' => '%of%',
                    'included_keyword_2' => '%the%',
                    'included_keyword_3' => '%analogs%',
                ],
            ],
        ];
    }

    /**
     * Test adding where conditions for extended title and description search
     * ======================================================================
     *
     * @dataProvider queryStringDataProvider
     *
     * @param string $queryString
     * @param string $expectedDQL
     * @param array $expectedParameters
     */
    public function testQueryBuilderConditions($queryString, $expectedDQL, $expectedParameters)
    {
        $searchQuery = new ExtendedSearchQuery($queryString);
        $qb = new QueryBuilder($this->container->get('doctrine.orm.entity_manager'));

        $this->addExtendedSearchQuery($searchQuery, $qb, [
            'title', 'description',
        ], 'event');

        $parameters = [];

        foreach ($qb->getParameters() as $parameter) {
            $parameters[$parameter->getName()] = $parameter->getValue();
        }

        $this->assertContains($expectedDQL, $qb->getDQL());
        $this->assertSame($expectedParameters, $parameters);
    }
}