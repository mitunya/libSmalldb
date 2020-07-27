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

namespace Smalldb\StateMachine\AccessControlExtension\Annotation\AC;

use Smalldb\StateMachine\AccessControlExtension\Definition\AccessControlExtensionPlaceholder;
use Smalldb\StateMachine\AccessControlExtension\Definition\AccessControlPolicy;
use Smalldb\StateMachine\Definition\Builder\StateMachineBuilderApplyInterface;
use Smalldb\StateMachine\Definition\Builder\StateMachineDefinitionBuilder;


/**
 * List of access policies
 *
 * @Annotation
 * @Target({"CLASS"})
 */
class DefinePolicy implements StateMachineBuilderApplyInterface
{
	public string $policyName;
	public PredicateAnnotation $predicate;


	public function __construct($values)
	{
		[$this->policyName, $this->predicate] = $values['value'];
	}


	public function applyToBuilder(StateMachineDefinitionBuilder $builder): void
	{
		/** @var AccessControlExtensionPlaceholder $ext */
		$ext = $builder->getExtensionPlaceholder(AccessControlExtensionPlaceholder::class);
		$ext->addPolicy(new AccessControlPolicy($this->policyName, $this->predicate->buildPredicate()));
	}

}