<?php

namespace Broda\Component\Rest\Filter\Param;

use Broda\Component\Rest\Filter\Tokenizers\BasicTokenizer;
use Broda\Component\Rest\Filter\Tokenizers\TokenizerInterface;

/**
 * Classe Searching
 *
 * @author raphael
 */
class Searching
{

    /**
     * Valor a ser buscado.
     *
     * @var string
     */
    protected $value;

    /**
     * Indica se a pesquisa será através de expressão regular ou não.
     *
     * Padrão: FALSE
     *
     * @var bool
     */
    protected $regex = false;

    /**
     * Nome da coluna a ser pesquisada.
     *
     * Se for NULL, a pesquisa é considerada "global" (em todas as colunas).
     * Porém este atributo pode ser ignorado pelo {@link Incorporator},
     * dependendo de como será o tratamento dos globalSearch e columnSearch.
     *
     * @var string|null
     */
    protected $columnName = null;

    /**
     * Irá transformar o $value num array de tokens a serem
     * pesquisados na busca.
     *
     * Padrão: {@link BasicTokenizer}
     *
     * @var TokenizerInterface
     */
    protected $tokenizer;

    /**
     * Indica se o {@link FilterInterface} deve tokenizar ou
     * não seu valor ao pesquisar na busca.
     *
     * Padrão: TRUE
     *
     * @var bool
     */
    protected $isTokenizable = true;

    /**
     * Indica se o {@link FilterInterface} deve pesquisar exatamente
     * aquele token/valor ou verificar se o mesmo apenas está contido
     * no registro (conhecido como LIKE, em SQL).
     *
     * Padrão: FALSE (usar LIKE).
     *
     * Nota: Para que a pesquisa considere os espaços da busca,
     * defina {@link Searching::setTokenizable} para FALSE ou
     * use um Tokenizer que considere espaços.
     *
     * @var bool
     */
    protected $exactly = false;

    /**
     * Separador lógico usado entre cada token ao ser usado na
     * busca.
     *
     * Vamos supor que o Tokenizer deste Searching separe
     * cada palavra de uma busca em tokens, ex: "joao da silva"
     * vai retornar do Tokenizer: ["joao", "da", "silva"]
     *
     * Este separador vai definir se essa busca irá encontrar
     * resultados que contenham "joao" E "da" E "silva" ou
     * "joao" OU "da" OU "silva".
     *
     * Usar 'OR' é útil para retornar resultados mesmo quando
     * um dos tokens não tiver nada a ver com o outro. Ex: buscar
     * por "joao maria" encontraria "joão roberto", "joão santos" e
     * "maria joaquina" em vez de encontrar "joão maria da silva",
     * "joão maria" ou "joão sergio maria".
     *
     * Valores possíveis: 'AND' e 'OR'
     * Valor padrão: 'AND'
     *
     * @var string
     */
    protected $tokenSeparator = 'AND';

    /**
     * Construtor.
     *
     * @param string $value
     * @param string $columnName
     */
    public function __construct($value, $columnName = null)
    {
        $this->value = $value;
        $this->columnName = $columnName;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function setTokenizable($tokenizable)
    {
        $this->isTokenizable = (bool)$tokenizable;
        return $this;
    }

    public function isTokenizable()
    {
        return $this->isTokenizable;
    }

    public function isExactly()
    {
        return $this->exactly;
    }

    public function setExactly($exactly)
    {
        $this->exactly = (bool)$exactly;
        return $this;
    }

    public function getRegex()
    {
        return $this->regex;
    }

    public function setRegex($regex)
    {
        $this->regex = $regex;
        return $this;
    }

    public function getColumnName()
    {
        return $this->columnName;
    }

    public function setColumnName($columnName)
    {
        $this->columnName = $columnName;
        return $this;
    }

    /**
     * Retorna o valor a ser buscado em um array de tokens.
     *
     * @return string[]
     */
    public function getTokens()
    {
        return $this->getTokenizer()->tokenize($this->value);
    }

    /**
     * Define o separador lógico usado entre cada token ao
     * ser procurado na filtragem.
     *
     * Os valores possíveis são: 'AND' e 'OR'.
     *
     * @param string $separator
     * @return self
     */
    public function setTokenSeparator($separator)
    {
        $this->tokenSeparator = $separator;
        return $this;
    }

    /**
     * Retorna o separador lógico usado entre cada token ao
     * ser procurado na filtragem.
     *
     * @return string 'AND' ou 'OR'
     */
    public function getTokenSeparator()
    {
        return $this->tokenSeparator;
    }

    public function setTokenizer(TokenizerInterface $tokenizer)
    {
        $this->tokenizer = $tokenizer;
    }

    public function getTokenizer()
    {
        if (null === $this->tokenizer) {
            $this->tokenizer = new BasicTokenizer();
        }
        return $this->tokenizer;
    }



}
