<?php declare(strict_types = 1);
//
// Generated by Smalldb\CodeCooker\Generator\DtoGenerator.
// Do NOT edit! All changes will be lost!
// 
// 
namespace Smalldb\StateMachine\Test\Example\Comment\CommentData;

use DateTimeImmutable;
use InvalidArgumentException;
use Smalldb\CodeCooker\Annotation\GeneratedClass;
use Smalldb\StateMachine\Test\Example\Comment\CommentProperties as Source_CommentProperties;


/**
 * @GeneratedClass
 * @see \Smalldb\StateMachine\Test\Example\Comment\CommentProperties
 */
class CommentDataImmutable extends Source_CommentProperties implements CommentData
{

	public function __construct(?CommentData $source = null)
	{
		if ($source !== null) {
			if ($source instanceof Source_CommentProperties) {
				$this->id = $source->id;
				$this->postId = $source->postId;
				$this->content = $source->content;
				$this->publishedAt = $source->publishedAt;
				$this->authorId = $source->authorId;
			} else {
				$this->id = $source->getId();
				$this->postId = $source->getPostId();
				$this->content = $source->getContent();
				$this->publishedAt = $source->getPublishedAt();
				$this->authorId = $source->getAuthorId();
			}
		}
	}


	public static function fromArray(?array $source, ?CommentData $sourceObj = null): ?self
	{
		if ($source === null) {
			return null;
		}
		$t = $sourceObj instanceof self ? clone $sourceObj : new self($sourceObj);
		$t->id = isset($source['id']) ? (int) $source['id'] : null;
		$t->postId = (int) $source['postId'];
		$t->content = (string) $source['content'];
		$t->publishedAt = ($v = $source['publishedAt'] ?? null) instanceof \DateTimeImmutable || $v === null ? $v : ($v instanceof \DateTime ? \DateTimeImmutable::createFromMutable($v) : new \DateTimeImmutable($v));
		$t->authorId = (int) $source['authorId'];
		return $t;
	}


	public static function fromIterable(?CommentData $sourceObj, iterable $source): self
	{
		$t = $sourceObj instanceof self ? clone $sourceObj : new self($sourceObj);
		foreach ($source as $prop => $value) {
			switch ($prop) {
				case 'id': $t->id = $value; break;
				case 'postId': $t->postId = $value; break;
				case 'content': $t->content = $value; break;
				case 'publishedAt': $t->publishedAt = $value instanceof \DateTime ? \DateTimeImmutable::createFromMutable($value) : $value; break;
				case 'authorId': $t->authorId = $value; break;
				default: throw new InvalidArgumentException('Unknown property: "' . $prop . '" not in ' . __CLASS__);
			}
		}
		return $t;
	}


	public function getId(): ?int
	{
		return $this->id;
	}


	public function getPostId(): int
	{
		return $this->postId;
	}


	public function getContent(): string
	{
		return $this->content;
	}


	public function getPublishedAt(): DateTimeImmutable
	{
		return $this->publishedAt;
	}


	public function getAuthorId(): int
	{
		return $this->authorId;
	}


	public static function get(CommentData $source, string $propertyName)
	{
		switch ($propertyName) {
			case 'id': return $source->getId();
			case 'postId': return $source->getPostId();
			case 'content': return $source->getContent();
			case 'publishedAt': return $source->getPublishedAt();
			case 'authorId': return $source->getAuthorId();
			default: throw new \InvalidArgumentException("Unknown property: " . $propertyName);
		}
	}


	public function withId(?int $id): self
	{
		$t = clone $this;
		$t->id = $id;
		return $t;
	}


	public function withPostId(int $postId): self
	{
		$t = clone $this;
		$t->postId = $postId;
		return $t;
	}


	public function withContent(string $content): self
	{
		$t = clone $this;
		$t->content = $content;
		return $t;
	}


	public function withPublishedAt(DateTimeImmutable $publishedAt): self
	{
		$t = clone $this;
		$t->publishedAt = $publishedAt;
		return $t;
	}


	public function withAuthorId(int $authorId): self
	{
		$t = clone $this;
		$t->authorId = $authorId;
		return $t;
	}

}

