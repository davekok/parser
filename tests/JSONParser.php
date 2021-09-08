<?php

declare(strict_types=1);

namespace DaveKok\LALR1\Tests;

use DaveKok\LALR1\BranchSymbol;
use DaveKok\LALR1\LeafSymbol;
use DaveKok\LALR1\Parser;
use DaveKok\LALR1\ParserFactory;
use DaveKok\LALR1\RootSymbol;
use DaveKok\LALR1\Symbols;
use DaveKok\LALR1\Rule;
use Exception;
use stdClass;

#[Symbols(
    new LeafSymbol("null"),
    new LeafSymbol("boolean"),
    new LeafSymbol("number"),
    new LeafSymbol("string"),
    new LeafSymbol("opening-brace"),
    new LeafSymbol("closing-brace"),
    new LeafSymbol("opening-bracket"),
    new LeafSymbol("closing-bracket"),
    new LeafSymbol("comma"),
    new LeafSymbol("colon", 1),
    new BranchSymbol("object"),
    new BranchSymbol("array"),
    new BranchSymbol("key"),
    new RootSymbol("value")
)]
class JSONParser
{
    public readonly Parser $parser;

    public function __construct()
    {
        $this->parser = ParserFactory::createParser($this);
    }

    public function parse(string $buffer): mixed
    {
        foreach (new JSONScanner($this->parser, $buffer) as $token) {
            $this->parser->pushToken($token);
        }
        $this->parser->endOfTokens();
        return $this->parser->value;
    }

    #[Rule("null")]
    public function promoteNull(Token $nullToken): Token
    {
        return $this->parser->createToken("value", $nullToken->value);
    }

    #[Rule("boolean")]
    public function promoteBoolean(Token $booleanToken): Token
    {
        return $this->parser->createToken("value", $booleanToken->value);
    }

    #[Rule("number")]
    public function promoteNumber(Token $numberToken): Token
    {
        return $this->parser->createToken("value", $numberToken->value);
    }

    #[Rule("string")]
    public function promoteString(Token $stringToken): Token
    {
        return $this->parser->createToken("value", $stringToken->value);
    }

    #[Rule("opening-bracket closing-bracket")]
    public function emptyArray(Token $openingBracket, Token $closingBracket): Token
    {
        return $this->parser->createToken("array", []);
    }

    #[Rule("opening-bracket value")]
    public function startArray(Token $openingBracket, Token $value): Token
    {
        return $this->parser->createToken("array", [$value->value]);
    }

    #[Rule("array comma value")]
    public function addElement(Token $array, Token $comma, Token $value): Token
    {
        $array->value[] = $value->value;
        return $this->parser->createToken("array", $array);
    }

    #[Rule("array closing-brace")]
    public function endArray(Token $array, Token $closingBrace): Token
    {
        return $this->parser->createToken("value", $array->value);
    }

    #[Rule("opening-brace closing-brace")]
    public function emptyObject(Token $openingBrace, Token $closingBrace): Token
    {
        return $this->parser->createToken("object", new stdClass);
    }

    #[Rule("opening-brace key value")]
    public function startObject(Token $openingBrace, Token $key, Token $value): Token
    {
        $obj = new stdClass;
        $key = $key->value;
        $obj->$key = $value->value;
        return $this->parser->createToken("object", $obj);
    }

    #[Rule("object comma key value")]
    public function addProperty(Token $obj, Token $comma, Token $key, Token $value): Token
    {
        $key = $key->value;
        $obj->$key = $value->value;
        return $obj;
    }

    #[Rule("object closing-brace")]
    public function endObject(Token $obj, Token $closingBrace): Token
    {
        return $this->parser->createToken("value", $obj->value);
    }

    #[Rule("string colon")]
    public function promoteToKey(Token $string, Token $colon): Token
    {
        return $this->parser->createToken("key", $string->value);
    }
}