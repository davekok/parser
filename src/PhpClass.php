<?php

declare(strict_types=1);

namespace davekok\parser;

use Exception;
use PHPUnit\TextUI\XmlConfiguration\Php;
use ReflectionClass;

class PhpClass
{
    private bool $final = false;
    private bool $abstract = false;
    private string|false $comment = false;
    private string|false $extends = false;
    private array $implements = [];
    private array $parts = [];

    public function __construct(
        public readonly string   $name,
        private readonly PhpFile $file,
        private readonly PhpText $text,
    ) {}

    public function comment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function final(): self
    {
        if ($this->abstract) {
            throw new Exception("Class can't both be abstract and final.");
        }
        $this->final = true;
        return $this;
    }

    public function abstract(): self
    {
        if ($this->final) {
            throw new Exception("Class can't both be abstract and final.");
        }
        $this->abstract = true;
        return $this;
    }

    public function extends(string|ReflectionClass $reference): self
    {
        $this->extends = $this->file->reference($reference);
        return $this;
    }

    public function implements(string|ReflectionClass $reference): self
    {
        $this->implements[] = $this->file->reference($reference);
        return $this;
    }

    public function constructor(PhpVisibility|false $visibility): PhpConstructor
    {
        return $this->parts[] = new PhpConstructor($this->text, $this->file, $this, $visibility);
    }

    public function public(): PhpClassVisibility
    {
        return new PhpClassVisibility($this, PhpVisibility::public);
    }

    public function protected(): PhpClassVisibility
    {
        return new PhpClassVisibility($this, PhpVisibility::protected);
    }

    public function private(): PhpClassVisibility
    {
        return new PhpClassVisibility($this, PhpVisibility::private);
    }

    public function method(string $name, PhpVisibility|false $visibility): PhpMethod
    {
        return $this->parts[] = new PhpMethod($this->text, $this->file, $this, $name, $visibility);
    }

    public function end(): PhpFile
    {
        return $this->file;
    }

    public function __toString(): string
    {
        $php = "";
        if ($this->comment) {
            $php .= $this->text->blockComment($this->comment);
        }
        $php .= $this->text->indent();
        if ($this->abstract) {
            $php .= "abstract ";
        } else if ($this->final) {
            $php .= "final ";
        }
        $php .= "class $this->name";
        if ($this->extends) {
            $php .= " extends $this->extends";
        }
        if ($this->implements) {
            $php .= " implements ";
            $i = 0;
            foreach ($this->implements as $reference) {
                if ($i++) $php .= ", ";
                $php .= $reference;
            }
        }
        $php .= $this->text->bodyOpen();
        $php .= $this->text->parts($this->parts);
        $php .= $this->text->bodyClose();
        return $php;
    }
}
