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

namespace Smalldb\StateMachine\SqlExtension;

use Smalldb\StateMachine\Definition\ExtensionInterface;
use Smalldb\StateMachine\Utils\SimpleJsonSerializableTrait;


class SqlPropertyExtension implements ExtensionInterface
{
	use SimpleJsonSerializableTrait;

	/** @var string|null */
	private $sqlColumn;

	/** @var bool */
	private $isId;


	public function __construct(?string $sqlColumn = null, bool $isId = false)
	{
		$this->sqlColumn = $sqlColumn;
		$this->isId = $isId;
	}


	public function getSqlColumn(): ?string
	{
		return $this->sqlColumn;
	}


	public function isId(): bool
	{
		return $this->isId;
	}

}