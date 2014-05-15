<?php
/*
 * Copyright (c) 2014, Josef Kufner  <jk@frozen-doe.net>
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

namespace Smalldb\Cascade;

use Smalldb\Machine\AbstractMachine;

/**
 * Raw and ugly connector to access Smalldb interface from outter world.
 *
 * Deprecated! This connector will be replaced with something better soon.
 *
 * This connector also directly reads $_GET and $_POST, which is also ugly.
 * And to make it even worse, it produces output!
 */
class RouterFactoryBlock extends BackendBlock
{

	protected $inputs = array(
	);

	protected $outputs = array(
		'postproc' => true,
		'done' => true,
	);

	const force_exec = true;


	public function main()
	{
		$this->out('postproc', array($this, 'postprocessor'));
		$this->out('done', true);
	}


	public function postprocessor($route)
	{
		return false;
	}
}

