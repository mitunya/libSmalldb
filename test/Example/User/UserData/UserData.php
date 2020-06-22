<?php declare(strict_types = 1);
//
// Generated by Smalldb\StateMachine\CodeCooker\Generator\DtoGenerator.
// Do NOT edit! All changes will be lost!
// 
// 
namespace Smalldb\StateMachine\Test\Example\User\UserData;

use Smalldb\StateMachine\CodeCooker\Annotation\GeneratedClass;


/**
 * @GeneratedClass
 * @see \Smalldb\StateMachine\Test\Example\User\UserProperties
 */
interface UserData
{

	public function getId(): ?int;

	public function getFullName(): string;

	public function getUsername(): string;

	public function getEmail(): string;

	public function getPassword(): string;

	public function getRoles(): array;
}

