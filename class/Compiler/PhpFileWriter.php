<?php
/*
 * Copyright (c) 2017, Josef Kufner  <josef@kufner.cz>
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

namespace Smalldb\StateMachine\Compiler;


/**
 * Write PHP files in a convenient way.
 */
class PhpFileWriter
{
	private $f;
	private $indent = "";
	private $indentDepth = 0;

	/**
	 * PhpFileWriter constructor.
	 */
	public function __construct(string $filename)
	{
		$this->f = fopen($filename, "w");
		if ($this->f === false) {
			$err = error_get_last();
			throw \ErrorException($filename.": ".$err['message']);
		}
	}


	public function writeln(string $string = ''): self
	{
		if ($string !== '') {
			fwrite($this->f, $this->indent);
			fwrite($this->f, $string);
		}
		fwrite($this->f, "\n");
		return $this;
	}


	public function close(): self
	{
		if ($this->indentDepth !== 0) {
			throw new \RuntimeException("Block not closed when generating PHP file.");
		}
		return $this;
	}


	public function beginBlock(string $statement = ''): self
	{
		if ($statement === '') {
			$this->writeln("{");
		} else {
			$this->writeln("$statement {");
		}
		$this->indent = $this->indent."\t";
		$this->indentDepth++;
		return $this;
	}

	public function endBlock(): self
	{
		$this->indentDepth--;
		$this->indent = str_repeat("\t", $this->indentDepth);
		$this->writeln("}");
		return $this;
	}

	public function comment(string $comment): self
	{
		$this->writeln("// ".str_replace("\n", "\n// ", $comment));
		return $this;
	}


	public function namespace(string $namespace): self
	{
		$this->writeln("namespace $namespace;\n");
		return $this;
	}


	public function beginClass(string $classname): self
	{
		$this->writeln("class $classname");
		$this->beginBlock();
		return $this;
	}


	public function endClass(): self
	{
		$this->endBlock();
		return $this;
	}


	public function beginMethod(string $name, array $args = [], $returnType = ''): self
	{
		$this->writeln('');
		$this->writeln("public function $name(".join(', ', $args).")".($returnType === '' ? '' : ": $returnType"));
		$this->beginBlock();
		return $this;
	}


	public function endMethod(): self
	{
		$this->endBlock();
		$this->writeln('');
		return $this;
	}

}
