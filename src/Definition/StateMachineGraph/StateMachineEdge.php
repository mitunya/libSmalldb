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


namespace Smalldb\StateMachine\Definition\StateMachineGraph;

use Smalldb\StateMachine\Definition\TransitionDefinition;
use Smalldb\StateMachine\Graph\Edge;
use Smalldb\StateMachine\Graph\NestedGraph;
use Smalldb\StateMachine\Graph\Node;

class StateMachineEdge extends Edge
{
	/** @var TransitionDefinition */
	private $transition;

	public function __construct(TransitionDefinition $transition, NestedGraph $graph, string $id, Node $start, Node $end, array $attrs)
	{
		parent::__construct($graph, $id, $start, $end, $attrs);
		$this->transition = $transition;
	}

	public function getTransition(): TransitionDefinition
	{
		return $this->transition;
	}

}