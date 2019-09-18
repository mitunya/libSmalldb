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

namespace Smalldb\StateMachine\ReferenceDataSource;


interface ReferenceDataSourceInterface
{

	/**
	 * Return the state of the refered state machine.
	 *
	 * TODO: Add locking mode so that we can lock an entity before invoking a transition.
	 */
	public function getState($id): string;

	/**
	 * Load data for the state machine and set the state
	 */
	public function loadData($id, string & $state);


	/**
	 * Invalidate cached data
	 */
	public function invalidateCache($id = null);

}