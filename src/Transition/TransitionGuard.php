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

use Smalldb\StateMachine\Reference;


/**
 * Interface TransitionGuard
 *
 * A transition guard tells the transition implementation decorator
 * whether it may or may not allow a transition.
 *
 * Guard before the transition: Is the given transition valid and allowed
 *     on a given state machine?
 *
 * Assert after the transition: Is the new state machine state one
 *     of the states expected after the transition?
 */
interface TransitionGuard
{

	/**
	 * A simple and cost-effective check whether the transition can
	 * be invoked. This check is called when we want to know wheter or not
	 * we can invoke the transition, but we have no intention of invoking
	 * the transition right now (e.g., when rendering a menu with available
	 * transitions).
	 */
	public function isTransitionAllowed(Reference $ref, string $transitionName): bool;

	/**
	 * Guard the transition before it is invoked.
	 *
	 * This check MUST perform the same check as the isTransitionAllowed
	 * method, but it may collect additional data for the assert after
	 * the transition finishes.
	 *
	 * If transition is not allowed, the guard calls $event->abortTransition().
	 */
	public function guardTransition(TransitionEvent $event): void;

	/**
	 * Assert the state of the state machine after the transition finishes.
	 *
	 * This check is called after the transition to verify that the state
	 * of the state machine has been updated accordingly to the definition.
	 *
	 * If transition is wrong, the guard calls $event->abortTransition().
	 *
	 * Both guardTransition() and assertTransition() will receive the same $event.
	 */
	public function assertTransition(TransitionEvent $event): void;

}