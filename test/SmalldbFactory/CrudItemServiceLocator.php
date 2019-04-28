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
namespace Smalldb\StateMachine\Test\SmalldbFactory;

use Psr\Container\ContainerInterface;
use Smalldb\StateMachine\AnnotationReader;
use Smalldb\StateMachine\Definition\StateMachineDefinition;
use Smalldb\StateMachine\Provider\LambdaProvider;
use Smalldb\StateMachine\Smalldb;
use Smalldb\StateMachine\Test\Example\CrudItem\CrudItemMachine;
use Smalldb\StateMachine\Test\Example\CrudItem\CrudItemRef;
use Smalldb\StateMachine\Test\Example\CrudItem\CrudItemRepository;
use Smalldb\StateMachine\Test\Example\CrudItem\CrudItemTransitions;
use Smalldb\StateMachine\Test\Database\ArrayDao;
use Smalldb\StateMachine\Test\TestTemplate\TestOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Reference;


class CrudItemServiceLocator implements SmalldbFactory
{

	/**
	 * Initialize Smalldb with a crud-item state machine.
	 */
	public function createSmalldb(): Smalldb
	{
		return $this->createCrudMachineContainer()->get(Smalldb::class);
	}


	private function createCrudMachineContainer(): ContainerInterface
	{
		$c = new ContainerBuilder();

		$smalldb = $c->autowire(Smalldb::class)
			->setPublic(true);

		// Definition
		$reader = $c->register(AnnotationReader::class . ' $crudItemReader', AnnotationReader::class)
			->addArgument(CrudItemMachine::class);
		$definitionId = StateMachineDefinition::class . ' $crudItemDefinition';
		$c->register($definitionId, StateMachineDefinition::class)
			->setFactory([$reader, 'getStateMachineDefinition']);

		// Repository
		$crudItemDaoId = ArrayDao::class . ' $crudItemDao';
		$c->autowire($crudItemDaoId, ArrayDao::class);
		$c->autowire(CrudItemRepository::class)
			->setArgument(ArrayDao::class, new Reference($crudItemDaoId));

		// Transitions implementation
		$transitionsId = CrudItemTransitions::class . ' $crudItemTransitionsImplementation';
		$c->autowire($transitionsId, CrudItemTransitions::class)
			->setArgument(ArrayDao::class, new Reference($crudItemDaoId));

		// Glue them together using a machine provider
		$machineProvider = $c->autowire(LambdaProvider::class)
			->addTag('container.service_locator')
			->addArgument([
				LambdaProvider::DEFINITION => new Reference($definitionId),
				LambdaProvider::TRANSITIONS_DECORATOR => new Reference($transitionsId),
				LambdaProvider::REPOSITORY => new Reference(CrudItemRepository::class),
			])
			->addArgument(CrudItemRef::class);

		// Register state machine type
		$smalldb->addMethodCall('registerMachineType', ['crud-item', $machineProvider]);

		$c->compile();

		// Dump the container so that we can examine it.
		$dumper = new PhpDumper($c);
		$output = new TestOutput();
		$output->writeResource(basename(__FILE__), $dumper->dump());

		return $c;
	}

}
