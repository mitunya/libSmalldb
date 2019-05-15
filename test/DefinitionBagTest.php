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

use PHPUnit\Framework\TestCase;
use Smalldb\StateMachine\Definition\StateMachineDefinition;
use Smalldb\StateMachine\SmalldbDefinitionBag;
use Smalldb\StateMachine\Test\Example\CrudItem\CrudItem;


class DefinitionBagTest extends TestCase
{

	public function testDefinitionBag()
	{
		$bag = new SmalldbDefinitionBag();
		$this->assertEmpty($bag->getAllDefinitions(), "A new definition bag should be empty.");

		$fooDefinition = new StateMachineDefinition('foo', [], [], [], []);
		$barDefinition = new StateMachineDefinition('bar', [], [], [], []);
		$bag->addDefinition($fooDefinition);
		$bag->addDefinition($barDefinition);

		$crudItemDefinition = $bag->addFromAnnotatedClass(CrudItem::class);

		$allDefinitions = $bag->getAllDefinitions();
		$this->assertContains($fooDefinition, $allDefinitions);
		$this->assertContains($barDefinition, $allDefinitions);
		$this->assertContains($crudItemDefinition, $allDefinitions);

		$allMachineTypes = $bag->getAllMachineTypes();
		$this->assertContains('foo', $allMachineTypes);
		$this->assertContains('bar', $allMachineTypes);
		$this->assertContains($crudItemDefinition->getMachineType(), $allMachineTypes);

		$retrievedFooDef = $bag->getDefinition('foo');
		$this->assertEquals($fooDefinition, $retrievedFooDef);
	}


	public function testUndefinedDefinition()
	{
		$bag = new SmalldbDefinitionBag();
		$bag->addDefinition(new StateMachineDefinition('foo1', [], [], [], []));
		$bag->addDefinition(new StateMachineDefinition('foo2', [], [], [], []));
		$this->assertNotEmpty($bag->getAllDefinitions(), "The definition bag should not be empty.");

		$this->expectException(\InvalidArgumentException::class);
		$bag->getDefinition('bar');
	}


	public function testDuplicateDefinition()
	{
		$bag = new SmalldbDefinitionBag();
		$bag->addDefinition(new StateMachineDefinition('foo', [], [], [], []));

		$this->expectException(\InvalidArgumentException::class);
		$bag->addDefinition(new StateMachineDefinition('foo', [], [], [], []));
	}

}
