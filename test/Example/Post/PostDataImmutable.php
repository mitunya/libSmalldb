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

namespace Smalldb\StateMachine\Test\Example\Post;

use DateTimeImmutable;


/**
 * Interface PostDataImmutable
 *
 * TODO: Can we generate this interface automatically?
 */
interface PostDataImmutable
{
	public function getId();
	public function getTitle(): string;
	public function getSlug(): string;
	public function getSummary(): string;
	public function getContent(): string;
	public function getPublishedAt(): DateTimeImmutable;
	public function getAuthorId(): int;
}
