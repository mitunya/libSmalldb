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

use PDO;
use PHPUnit\Framework\TestCase;
use Smalldb\StateMachine\Smalldb;
use Smalldb\StateMachine\Test\Example\Post\Post;
use Smalldb\StateMachine\Test\Example\Post\PostData;
use Smalldb\StateMachine\Test\Example\Post\PostDataImmutable;
use Smalldb\StateMachine\Test\Example\Post\PostRepository;
use Smalldb\StateMachine\Test\SmalldbFactory\SymfonyDemoContainer;


class PostRepositoryTest extends TestCase
{
	/** @var Smalldb */
	private $smalldb;

	/** @var PostRepository */
	private $postRepository;


	public function setUp(): void
	{
		$containerFactory = new SymfonyDemoContainer();
		$container = $containerFactory->createContainer();
		$this->postRepository = $container->get(PostRepository::class);
		$this->smalldb = $container->get(Smalldb::class);
	}


	public function testLoadState()
	{
		$ref = $this->smalldb->ref(Post::class, 1);
		$state = $ref->getState();
		$this->assertEquals('Exists', $state);

		// One query to load the state
		$this->assertEquals(1, $this->postRepository->getDataSourceQueryCount());
	}


	public function testLoadData()
	{
		/** @var Post $ref */
		$ref = $this->smalldb->ref(Post::class, 1);
		$this->assertEquals('Exists', $ref->getState());
		$this->assertEquals(1, $ref->getId());
		$this->assertNotEmpty($ref->getTitle());

		// One query to load the state, second to load data. One would be better.
		$this->assertLessThanOrEqual(2, $this->postRepository->getDataSourceQueryCount());
	}

	public function testPostObjects()
	{
		$mutablePost = $this->createPostData();
		$immutablePost = new PostDataImmutable($mutablePost);
		$this->assertEquals(get_object_vars($mutablePost), get_object_vars($immutablePost));
	}


	public function testPostReferenceObjects()
	{
		/** @var Post $ref */
		$ref = $this->smalldb->ref(Post::class, 1);

		$postData = new PostDataImmutable($ref);

		$dataTitle = $postData->getTitle();
		$refTitle = $ref->getTitle();
		$this->assertEquals($refTitle, $dataTitle);
	}


	private function createPostData(): PostData
	{
		$postData = new PostData();
		$postData->setTitle('Foo');
		$postData->setSlug('foo');
		$postData->setAuthorId(1);
		$postData->setPublishedAt(new \DateTimeImmutable());
		$postData->setSummary('Foo, foo.');
		$postData->setContent('Foo. Foo. Foo.');
		return $postData;
	}

	public function testCreate()
	{
		/** @var Post $ref */
		$ref = $this->smalldb->ref(Post::class, 1000);
		$postData = $this->createPostData();

		/** @var PostData $data */
		$this->assertEquals('', $ref->getState());
		$ref->create($postData);
		$this->assertEquals('Exists', $ref->getState());
		$this->assertEquals(1000, $ref->getId());
		$this->assertEquals('Foo', $ref->getTitle());
	}


	public function testUpdate()
	{
		/** @var Post $ref */
		$ref = $this->smalldb->ref(Post::class, 1000);
		$ref->create($this->createPostData());
		$this->assertEquals('Exists', $ref->getState());
		$this->assertEquals('Foo', $ref->getTitle());

		$postData = new PostData($ref);
		$postData->setTitle('Bar');

		$ref->update($postData);
		$this->assertEquals('Exists', $ref->getState());
		$this->assertEquals(1000, $ref->getId());
		$this->assertEquals('Bar', $ref->getTitle());
	}


	/**
	 * @depends testUpdate
	 */
	public function testDelete()
	{
		/** @var Post $ref */
		$ref = $this->smalldb->ref(Post::class, 1000);
		$ref->create($this->createPostData());
		$this->assertEquals('Exists', $ref->getState());

		$ref->delete();
		$this->assertEquals('', $ref->getState());
	}


	/**
	 * Baseline test for testFindLatest benchmark
	 */
	public function testAssertBenchmark()
	{
		$N = 1000;
		$foo = 0;
		for ($i = 0; $i < 25 * $N; $i++) {
			$post = new PostData();
			$post->setTitle('Foo');
			$foo |= empty($post->getTitle());
		}
		$this->assertEmpty($foo);
		$this->assertEquals($N, 1000, 'Just counting.');
	}


	/** @dataProvider fetchMode */
	public function testFindLatest($fetchMode)
	{
		$N = 1000;
		$this->postRepository->fetchMode = $fetchMode;
		$hasEmptyTitle = 0;

		for ($i = 0; $i < $N; $i++) {
			$latestPosts = $this->postRepository->findLatest();

			// Make sure each reference has its data loaded
			foreach ($latestPosts as $post) {
				$hasEmptyTitle |= empty($post->getTitle());
			}
		}

		$this->assertEmpty($hasEmptyTitle, 'Some post is missing its title.');

		// One query to load everything; data source should not query any additional data.
		$queryCount = $this->postRepository->getDataSourceQueryCount();
		$this->assertEquals($N, $queryCount, "Unexpected query count: $queryCount (should be $N; fetch mode $fetchMode)");
	}

	public function fetchMode()
	{
		foreach (PostRepository::FETCH_MODES as $fetchMode) {
			yield "Fetch mode $fetchMode" => [$fetchMode];
		}
	}

}
