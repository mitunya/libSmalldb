<?php declare(strict_types = 1);
/*
 * Copyright (c) 2020, Josef Kufner  <josef@kufner.cz>
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

namespace Smalldb\CodeCooker\Recipe;

use Smalldb\ClassLocator\ClassLocator;


/**
 * ClassRecipe: an empty recipe, a base class for other recipes.
 */
abstract class ClassRecipe
{
	/** @var string[] */
	private array $targetClassNames;


	public function __construct(array $targetClassNames)
	{
		$this->targetClassNames = $targetClassNames;
	}


	public function getTargetClassNames(): array
	{
		return $this->targetClassNames;
	}


	abstract public function cookRecipe(ClassLocator $classLocator): array;

}
