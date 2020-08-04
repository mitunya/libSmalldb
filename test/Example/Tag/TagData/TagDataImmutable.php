<?php declare(strict_types = 1);
//
// Generated by Smalldb\CodeCooker\Generator\DtoGenerator.
// Do NOT edit! All changes will be lost!
// 
// 
namespace Smalldb\StateMachine\Test\Example\Tag\TagData;

use InvalidArgumentException;
use Smalldb\CodeCooker\Annotation\GeneratedClass;
use Smalldb\StateMachine\Test\Example\Tag\TagProperties as Source_TagProperties;


/**
 * @GeneratedClass
 * @see \Smalldb\StateMachine\Test\Example\Tag\TagProperties
 */
class TagDataImmutable extends Source_TagProperties implements TagData
{

	public function __construct(?TagData $source = null)
	{
		if ($source !== null) {
			if ($source instanceof Source_TagProperties) {
				$this->id = $source->id;
				$this->name = $source->name;
			} else {
				$this->id = $source->getId();
				$this->name = $source->getName();
			}
		}
	}


	public static function fromArray(?array $source, ?TagData $sourceObj = null): ?self
	{
		if ($source === null) {
			return null;
		}
		$t = $sourceObj instanceof self ? clone $sourceObj : new self($sourceObj);
		$t->id = isset($source['id']) ? (int) $source['id'] : null;
		$t->name = (string) $source['name'];
		return $t;
	}


	public static function fromIterable(?TagData $sourceObj, iterable $source): self
	{
		$t = $sourceObj instanceof self ? clone $sourceObj : new self($sourceObj);
		foreach ($source as $prop => $value) {
			switch ($prop) {
				case 'id': $t->id = $value; break;
				case 'name': $t->name = $value; break;
				default: throw new InvalidArgumentException('Unknown property: "' . $prop . '" not in ' . __CLASS__);
			}
		}
		return $t;
	}


	public function getId(): ?int
	{
		return $this->id;
	}


	public function getName(): string
	{
		return $this->name;
	}


	public static function get(TagData $source, string $propertyName)
	{
		switch ($propertyName) {
			case 'id': return $source->getId();
			case 'name': return $source->getName();
			default: throw new \InvalidArgumentException("Unknown property: " . $propertyName);
		}
	}


	public function withId(?int $id): self
	{
		$t = clone $this;
		$t->id = $id;
		return $t;
	}


	public function withName(string $name): self
	{
		$t = clone $this;
		$t->name = $name;
		return $t;
	}


	public function withNameFromSlug(string $slug): self
	{
		$t = clone $this;
		$t->setNameFromSlug($slug);
		return $t;
	}


	public function withResetName(): self
	{
		$t = clone $this;
		$t->resetName();
		return $t;
	}

}

