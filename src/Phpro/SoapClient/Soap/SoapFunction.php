<?php

namespace Phpro\SoapClient\Soap;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * SoapFunction.
 *
 * @author Adam Wójs <adam@wojs.pl>
 */
class SoapFunction 
{
    /**
     * Function name
     *
     * @var string
     */    
    private $name;
    
    /**
     * Function arguments.
     *
     * @var ArrayCollection
     */    
    private $arguments;
    
    /**
     * Function return type.
     *
     * @var string
     */    
    private $returnType;
    
    /**
     * @param string $name
     * @param array $arguments
     * @param string $returnType
     */
    public function __construct($name = null, $arguments = [], $returnType = null) 
    {
        $this->name = $name;
        $this->arguments = new ArrayCollection($arguments);
        $this->returnType = $returnType;
    }
    
    public function getName() 
    {
        return $this->name;
    }

    public function getArguments() 
    {
        return $this->arguments;
    }

    public function getReturnType() 
    {
        return $this->returnType;
    }

    public function setName($name) 
    {
        $this->name = $name;
    }

    public function setArguments($arguments) 
    {
        $this->arguments = $arguments;
    }

    public function setReturnType($returnType) 
    {
        $this->returnType = $returnType;
    }
}
