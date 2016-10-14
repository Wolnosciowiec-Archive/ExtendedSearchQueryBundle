<?php

namespace ExtendedSearchQueryBundle\Tests\Service;

use Wolnosciowiec\AppBundle\Tests\ContainerAwareTestCase;
use ExtendedSearchQueryBundle\Service\ExtendedSearchQuery;

/**
 * ExtendedSearchQueryTest
 * =======================
 *
 * @see ExtendedSearchQuery
 * @package ExtendedSearchQueryBundle\Tests\Service
 */
class ExtendedSearchQueryTest extends ContainerAwareTestCase
{
	/**
	 * @return array
	 */
	public function queryStringDataProvider()
	{
		return [
			'Search phrase + exclude' => [
				'concert -disco',
				['concert'],
				['disco'],
				[],
			],

			'Four search phrases' => [
				'concert of the analogs',
				['concert', 'of', 'the', 'analogs'],
				[],
				[],
			],

			'Quoted phrases and excludes' => [
				'"the analogs" and +bunkier -farben -"hip hop"',
				['the analogs', 'and', 'bunkier'],
				['farben', 'hip hop'],
				[],
			],

			'Options' => [
				'!c only',
				['only'],
				[],
				['search_in_city_only'],
			]
		];
	}

	/**
	 * @dataProvider queryStringDataProvider
	 *
	 * @param string $queryString
	 * @param string[] $expectedPhrases
	 * @param string[] $expectedExcludePhrases
	 * @param string[] $expectedOptions
	 */
	public function testParsingQuery($queryString, $expectedPhrases, $expectedExcludePhrases, $expectedOptions)
	{
		$query = new ExtendedSearchQuery($queryString, 10, [
			'c' => 'search_in_city_only',
		]);

		$this->assertEquals($expectedPhrases, $query->getSearchPhrases());
		$this->assertEquals($expectedExcludePhrases, $query->getExcludePhrases());
		$this->assertEquals($expectedOptions, $query->getOptions());
		$this->assertEquals($queryString, rtrim((string)$query));
	}
}