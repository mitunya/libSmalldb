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

namespace Smalldb\StateMachine\AccessControlExtension;

use Smalldb\StateMachine\AccessControlExtension\Definition\AccessControlExtension;
use Smalldb\StateMachine\AccessControlExtension\Definition\AccessControlPolicy;
use Smalldb\StateMachine\AccessControlExtension\Definition\AccessPolicyExtension;
use Smalldb\StateMachine\AccessControlExtension\Predicate\PredicateCompiled;
use Smalldb\StateMachine\Definition\StateMachineDefinition;
use Smalldb\StateMachine\Definition\TransitionDefinition;
use Smalldb\StateMachine\ReferenceInterface;
use Smalldb\StateMachine\RuntimeException;
use Smalldb\StateMachine\Transition\TransitionGuard;


class SimpleTransitionGuard implements TransitionGuard
{

	/** @var PredicateCompiled[][] */
	private array $transitionPredicates;


	/**
	 * @param PredicateCompiled[][] $transitionPredicates
	 */
	public function __construct(array $transitionPredicates = [])
	{
		$this->transitionPredicates = $transitionPredicates;
	}


	public function isTransitionAllowed(ReferenceInterface $ref, TransitionDefinition $transition): bool
	{
		return $this->isAccessAllowed($ref->getMachineType(), $transition->getName(), $ref);
	}


	public function isAccessAllowed(string $machineType, string $transitionName, ReferenceInterface $ref): bool
	{
		if (!empty($this->transitionPredicates[$machineType])) {
			if (isset($this->transitionPredicates[$machineType][$transitionName])) {
				return $this->transitionPredicates[$machineType][$transitionName]->evaluate($ref);
			} else {
				return false;
			}
		} else {
			return true;
		}
	}


	public function getTransitionPredicates(): array
	{
		return $this->transitionPredicates;
	}


	private function getPolicyName(TransitionDefinition $transition, StateMachineDefinition $definition): ?string
	{
		if ($transition->hasExtension(AccessPolicyExtension::class)) {
			/** @var AccessPolicyExtension $trPolicyExt */
			$trPolicyExt = $transition->getExtension(AccessPolicyExtension::class);
			$policyName = $trPolicyExt->getPolicyName();
			if ($policyName !== null) {
				return $policyName;
			}
		}

		if ($definition->hasExtension(AccessControlExtension::class)) {
			/** @var AccessControlExtension $ext */
			$ext = $definition->getExtension(AccessControlExtension::class);
			$defaultPolicyName = $ext->getDefaultPolicyName();
			if ($defaultPolicyName !== null) {
				return $defaultPolicyName;
			}
		}

		return null;
	}


	private function findPolicy(string $policyName, StateMachineDefinition $stateMachineDefinition): AccessControlPolicy
	{
		// Try state machine definition
		if ($stateMachineDefinition->hasExtension(AccessControlExtension::class)) {
			/** @var AccessControlExtension $ext */
			$ext = $stateMachineDefinition->getExtension(AccessControlExtension::class);

			$policy = $ext->getPolicy($policyName);
			if ($policy) {
				return $policy;
			}
		}

		// Try global policies
		$globalPolicy = $this->getPolicy($policyName);
		if ($globalPolicy) {
			return $globalPolicy;
		}

		throw new RuntimeException("Access control policy \"$policyName\" not not found"
			. " in state machine \"" . $stateMachineDefinition->getMachineType() . "\".");
	}

}
