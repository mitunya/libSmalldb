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

namespace Smalldb\StateMachine\CodeGenerator;

use ReflectionClass;
use Smalldb\StateMachine\CodeGenerator\Annotation\GeneratedClass;
use Smalldb\StateMachine\Utils\AnnotationReader\AnnotationReaderInterface;
use Smalldb\StateMachine\Utils\ClassLocator\ClassLocator;
use Smalldb\StateMachine\Utils\AnnotationReader\AnnotationReader;


class RecipeLocator
{
	/** @var ClassLocator[] */
	private array $classLocators = [];

	private AnnotationReaderInterface $annotationReader;


	public function __construct(AnnotationReaderInterface $annotationReader = null)
	{
		$this->annotationReader = $annotationReader ?? (new AnnotationReader());
	}


	public function getAnnotationReader(): AnnotationReaderInterface
	{
		return $this->annotationReader;
	}


	public function addClassLocator(ClassLocator $classLocator): void
	{
		$this->classLocators[] = $classLocator;
	}


	public function locateClasses(): iterable
	{
		foreach ($this->classLocators as $classLocator) {
			foreach ($classLocator->getClasses() as $classname) {
				yield $classname;
			}
		}
	}


	public function locateRecipes(): \Generator
	{
		foreach ($this->locateClasses() as $className) {
			yield from $this->locateClassRecipes(new ReflectionClass($className));
		}
	}


	/**
	 * @return string[]  List of generated classes (FQCNs)
	 */
	public function locateClassRecipes(ReflectionClass $sourceClass): \Generator
	{
		$annotations = $this->annotationReader->getClassAnnotations($sourceClass);

		// Do not process generated classes
		foreach ($annotations as $annotation) {
			if ($annotation instanceof GeneratedClass) {
				return;
			}
		}

		// Convert annotations to recipes
		foreach ($annotations as $annotation) {
			if ($annotation instanceof AnnotationRecipeBuilder) {
				yield $annotation->buildRecipe($sourceClass);
			}
		}
	}

}
