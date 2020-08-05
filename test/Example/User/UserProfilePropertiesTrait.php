<?php
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

namespace Smalldb\StateMachine\Test\Example\User;

use Smalldb\StateMachine\SqlExtension\Annotation\SQL;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * User - An entity based on Symfony Demo
 *
 * @SQL\Table("symfony_demo_user")
 */
trait UserProfilePropertiesTrait
{

	/**
	 * @SQL\Column(name = "full_name")
	 * @Assert\NotBlank()
	 */
	protected string $fullName;

	/**
	 * @SQL\Column
	 * @Assert\Email()
	 */
	protected string $email;

}