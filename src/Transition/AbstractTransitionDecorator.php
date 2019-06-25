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

namespace Smalldb\StateMachine\Transition;

use Smalldb\StateMachine\Definition\StateDefinition;
use Smalldb\StateMachine\Definition\TransitionDefinition;
use Smalldb\StateMachine\ReferenceInterface;
use Smalldb\StateMachine\StateMachineHasErrorsException;


abstract class AbstractTransitionDecorator implements TransitionDecorator
{

	public function __construct()
	{
		// TODO: Add an event dispatcher
	}


	final public function invokeTransition(TransitionEvent $transitionEvent): TransitionEvent
	{
		// FIXME: Don't assume the getState() method (marking vs. state). Use a machine-specific event instead.

		// Get the transition definition, check the transition exists and is valid
		$ref = $transitionEvent->getRef();
		$sourceState = $ref->getState();
		$transitionDefinition = $this->getTransitionDefinition($ref, $transitionEvent->getTransitionName());

		// Check user's permissions to invoke the transitions
		$this->guardTransition($transitionEvent, $transitionDefinition);

		// Invoke the transition
		$this->doInvokeTransition($transitionEvent, $transitionDefinition);

		// Verify that the new state is expected according to the definition
		$ref->clearCache();
		$targetState = $ref->getState();
		$this->assertTransition($transitionEvent, $transitionDefinition, $sourceState, $targetState);

		// Dispatch an event about the transition
		$this->postTransition($transitionEvent, $transitionDefinition, $sourceState, $targetState);

		return $transitionEvent;
	}


	/**
	 * Get the transition definition: $ref->state + $transitionName --> TransitionDefinition
	 */
	private function getTransitionDefinition(ReferenceInterface $ref, $transitionName): TransitionDefinition
	{
		$definition = $ref->getDefinition();

		if ($definition->hasErrors()) {
			throw new StateMachineHasErrorsException('Cannot use a state machine with errors in the definition: '.$definition->getMachineType());
		}

		return $ref->getDefinition()->getTransition($transitionName, $ref->getState());
	}


	/**
	 * Guard the transition before it is invoked. Throw an exception if there is something wrong.
	 */
	private function guardTransition(TransitionEvent $transitionEvent, TransitionDefinition $transition): void
	{
		// TODO: Dispatch an event to voters.
	}


	/**
	 * Invoke the transition.
	 */
	abstract protected function doInvokeTransition(TransitionEvent $transitionEvent, TransitionDefinition $transitionDefinition): void;


	/**
	 * After the transition, make sure the state machine state is as expected.
	 * Throw an exception if something is wrong.
	 *
	 * @throws TransitionAssertException
	 */
	private function assertTransition(TransitionEvent $transitionEvent, TransitionDefinition $transitionDefinition, string $sourceState, string $targetState): void
	{
		$validTargetStates = $transitionDefinition->getTargetStates();
		if (!isset($validTargetStates[$targetState])) {
			throw new TransitionAssertException(sprintf('State machine "%s" got into an unexpected state "%s" after the "%s" transition from state "%s". (Expected states: %s)',
				$transitionEvent->getRef()->getMachineType(), $targetState, $transitionEvent->getTransitionName(), $sourceState,
				join(', ', array_map(function(StateDefinition $state) { return $state->getName(); }, $validTargetStates))));
		}
	}

	/**
	 * Dispatch a notification about the transition
	 */
	private function postTransition(TransitionEvent $transitionEvent, TransitionDefinition $transitionDefinition, string $sourceState, string $targetState): void
	{
		// TODO: Dispatch a notification
	}

}
