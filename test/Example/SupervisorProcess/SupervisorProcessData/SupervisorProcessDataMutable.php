<?php declare(strict_types = 1);
//
// Generated by Smalldb\StateMachine\CodeGenerator\InferClass\SmalldbEntityGenerator (@InferSmalldbEntity annotation).
// Do NOT edit! All changes will be lost!
// 
// 
namespace Smalldb\StateMachine\Test\Example\SupervisorProcess\SupervisorProcessData;

use DateTimeImmutable;
use Smalldb\StateMachine\CodeGenerator\Annotation\InferredClass;
use Smalldb\StateMachine\Test\Example\SupervisorProcess\SupervisorProcessData\SupervisorProcessDataImmutable;


/**
 * @InferredClass
 */
class SupervisorProcessDataMutable extends SupervisorProcessDataImmutable
{

	public function setId(int $id): void
	{
		$this->id = $id;
	}


	public function setState(string $state): void
	{
		$this->state = $state;
	}


	public function setCommand(string $command): void
	{
		$this->command = $command;
	}


	public function setCreatedAt(DateTimeImmutable $createdAt): void
	{
		$this->createdAt = $createdAt;
	}


	public function setModifiedAt(DateTimeImmutable $modifiedAt): void
	{
		$this->modifiedAt = $modifiedAt;
	}


	public function setMemoryLimit(?int $memoryLimit): void
	{
		$this->memoryLimit = $memoryLimit;
	}


	public function setArgs(?array $args): void
	{
		$this->args = $args;
	}

}

