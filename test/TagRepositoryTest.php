<?php declare(strict_types = 1);
/*
 * Copyright (c) 2019, Josef Kufner  <josef@kufner.cz>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

namespace Smalldb\StateMachine\Test;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Smalldb\StateMachine\Smalldb;
use Smalldb\StateMachine\SqlExtension\Definition\SqlTableExtension;
use Smalldb\StateMachine\Test\Example\Tag\Tag;
use Smalldb\StateMachine\Test\Example\Tag\TagData\TagData;
use Smalldb\StateMachine\Test\Example\Tag\TagData\TagDataImmutable;
use Smalldb\StateMachine\Test\Example\Tag\TagData\TagDataMutable;
use Smalldb\StateMachine\Test\Example\Tag\TagRepository;
use Smalldb\StateMachine\Test\SmalldbFactory\SymfonyDemoContainer;


class TagRepositoryTest extends TestCase
{
	private Smalldb $smalldb;
	private TagRepository $tagRepository;


	public function setUp(): void
	{
		$containerFactory = new SymfonyDemoContainer();
		$container = $containerFactory->createContainer();
		$this->tagRepository = $container->get(TagRepository::class);
		$this->smalldb = $container->get(Smalldb::class);

		/** @var Connection $dbal */
		$dbal = $container->get(Connection::class);
		$stmt = $dbal->query("SELECT COUNT(*) FROM symfony_demo_tag");
		$this->assertGreaterThan(0, $stmt->fetchColumn());
	}


	public function testTagDefinitionSqlExtension()
	{
		$definition = $this->smalldb->getDefinition(Tag::class);
		$this->assertTrue($definition->hasExtension(SqlTableExtension::class), "SQL Extension is missing from the Tag definition!");

		/** @var SqlTableExtension $sqlExt */
		$sqlExt = $definition->getExtension(SqlTableExtension::class);
		$this->assertEquals("symfony_demo_tag", $sqlExt->getSqlTable());
	}


	public function testLoadState()
	{
		$ref = $this->smalldb->ref(Tag::class, 1);
		$state = $ref->getState();
		$this->assertEquals('Exists', $state);

		// One query to load the state
		$this->assertEquals(1, $this->tagRepository->getQueryCount());
	}


	public function testLoadData()
	{
		/** @var Tag $ref */
		$ref = $this->smalldb->ref(Tag::class, 1);
		$this->assertEquals('Exists', $ref->getState());
		$this->assertEquals(1, $ref->getId());
		$this->assertNotEmpty($ref->getTitle());

		// One query to load the state, second to load data. One would be better.
		$this->assertLessThanOrEqual(2, $this->tagRepository->getQueryCount());
	}

	public function testTagObjects()
	{
		$mutableTag = $this->createTagData();
		$immutableTag = new TagDataImmutable($mutableTag);
		$this->assertEquals(get_object_vars($mutableTag), get_object_vars($immutableTag));
	}


	public function testTagReferenceObjects()
	{
		/** @var Tag $ref */
		$ref = $this->smalldb->ref(Tag::class, 1);

		// FIXME: There should be no need to trigger loading manually.
		$ref->getTitle();

		$tagData = new TagDataImmutable($ref);

		$dataTitle = $tagData->getTitle();
		$refTitle = $ref->getTitle();
		$this->assertEquals($refTitle, $dataTitle);
	}


	private function createTagData(): TagData
	{
		$tagData = new TagDataMutable();
		$tagData->setTitle('Foo');
		$tagData->setSlug('foo');
		$tagData->setAuthorId(1);
		$tagData->setPublishedAt(new DateTimeImmutable());
		$tagData->setSummary('Foo, foo.');
		$tagData->setContent('Foo. Foo. Foo.');
		return new TagDataImmutable($tagData);
	}

	public function testCreate()
	{
		/** @var Tag $ref */
		$ref = $this->smalldb->ref(Tag::class, 1000);
		$tagData = $this->createTagData();

		/** @var TagData $data */
		$this->assertEquals('', $ref->getState());
		$ref->create($tagData);
		$this->assertEquals('Exists', $ref->getState());
		$this->assertEquals(1000, $ref->getId());
		$this->assertEquals('Foo', $ref->getTitle());
	}


	public function testUpdate()
	{
		/** @var Tag $ref */
		$ref = $this->smalldb->ref(Tag::class, 1000);
		$ref->create($this->createTagData());
		$this->assertEquals('Exists', $ref->getState());
		$this->assertEquals('Foo', $ref->getTitle());

		$tagData = new TagDataImmutable($ref);
		$tagData = $tagData->withTitle('Bar');

		$ref->update($tagData);
		$this->assertEquals('Exists', $ref->getState());
		$this->assertEquals(1000, $ref->getId());
		$this->assertEquals('Bar', $ref->getTitle());
	}


	/**
	 * @depends testUpdate
	 */
	public function testDelete()
	{
		/** @var Tag $ref */
		$ref = $this->smalldb->ref(Tag::class, 1000);
		$ref->create($this->createTagData());
		$this->assertEquals('Exists', $ref->getState());

		$ref->delete();
		$this->assertEquals('', $ref->getState());
	}


	public function testFindBySlug()
	{
		$slug = 'vae-humani-generis';  // Tag ID 20 in the test database
		$expectedTagId = 20;

		// Check test data that the slug exists
		$testRef = $this->tagRepository->ref($expectedTagId);
		$existingSlug = $testRef->getSlug();
		$this->assertEquals($slug, $existingSlug);

		$this->assertQueryCount(1);

		// Try to find
		$foundRef = $this->tagRepository->findBySlug($slug);
		$this->assertInstanceOf(Tag::class, $foundRef);
		$this->assertEquals(Tag::EXISTS, $foundRef->getState());
		$this->assertEquals($slug, $foundRef->getSlug());
		$this->assertNotEmpty($foundRef->getTitle());

		// Single query to both find the Tag and load the data.
		$this->assertQueryCount(2);
	}


	public function testFindLatest()
	{
		$N = 100;
		$hasEmptyTitle = 0;

		for ($i = 0; $i < $N; $i++) {
			$latestTags = $this->tagRepository->findLatest();
			$this->assertNotEmpty($latestTags);

			// Make sure each reference has its data loaded
			$count = 0;
			foreach ($latestTags as $tag) {
				$hasEmptyTitle |= empty($tag->getTitle());
				$count++;
			}
			$this->assertGreaterThan(1, $count);
		}

		$this->assertEmpty($hasEmptyTitle, 'Some tag is missing its title.');

		// One query to load everything; data source should not query any additional data.
		$this->assertQueryCount($N);
	}


	public function testFindAll()
	{
		$hasEmptyTitle = 0;

		foreach ($this->tagRepository->findLatest() as $tag) {
			$hasEmptyTitle |= empty($tag->getTitle());
		}

		$this->assertEmpty($hasEmptyTitle, 'Some tag is missing its title.');

		// One query to load everything; data source should not query any additional data.
		$this->assertQueryCount(1);
	}


	private function assertQueryCount(int $expected): void
	{
		$actual = $this->tagRepository->getQueryCount();
		$this->assertEquals($expected, $actual, "Unexpected query count: $actual (should be $expected)");
	}

}
