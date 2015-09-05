<?php

namespace Phpro\SoapClient\Soap;

/**
 * Class SoapClient
 *
 * @package Phpro\SoapClient\Soap
 *
 * Note: Make sure to extend the \SoapClient without alias for php-vcr implementations.
 */
class SoapClient extends \SoapClient
{
    /**
     * SOAP types derived from WSDL
     *
     * @var array
     */
    protected $types = [];

    /**
     * SOAP function derived from WSDL
     * 
     * @var array
     */
    protected $functions = [];
    
    /**
     * Retrieve SOAP types from the WSDL and parse them
     *
     * @return array Array of types and their properties
     */
    public function getSoapTypes()
    {
        if ($this->types) {
            return $this->types;
        }

        $soapTypes = $this->__getTypes();
        foreach ($soapTypes as $soapType) {
            $properties = array();
            $lines = explode("\n", $soapType);
            if (!preg_match('/struct (.*) {/', $lines[0], $matches)) {
                continue;
            }
            $typeName = $matches[1];

            foreach (array_slice($lines, 1) as $line) {
                if ($line == '}') {
                    continue;
                }
                preg_match('/\s* (.*) (.*);/', $line, $matches);
                $properties[$matches[2]] = $matches[1];
            }

            $this->types[$typeName] = $properties;
        }

        return $this->types;
    }

    /**
     * Retrieve SOAP types from the WSDL and parse them
     *
     * @return array Array of types and their properties
     */    
    public function getSoapFunctions() 
    {
        if ($this->functions) {
            return $this->functions;
        }
        
        foreach ($this->__getFunctions() as $soapFunction) {
            // BFN grammar: 
            // <function> ::= <return-type> <function-name> "(" <args-list> ")"
            // <return-type> ::= ID
            // <function-name> ::= ID
            // <args-list> ::= empty | <non-empty-args-list>
            // <non-empty-args-list> ::= <arg> | <arg> "," <non-empty-args-list>
            // <arg> ::= <type> <parameter>
            // <type> ::= ID 
            // <parameter> ::= ID
            if (!preg_match('/^(.*) (.*)\((.*)\)$/', $soapFunction, $matches)) {
                continue;
            }
            
            $function = new SoapFunction();
            $function->setName($matches[2]);
            $function->setReturnType($matches[1]);
            
            foreach (explode(', ', $matches[3]) as $argument) {
                list($type, $name) = explode(' ', $argument, 2);
                
                $function->getArguments()->set($name, $type);
            }

            if (!in_array($function, $this->functions)) {
                $this->functions[] = $function;
            }
        }
        
        return $this->functions;
    }
    
    /**
     * Get a SOAP type’s elements
     *
     * @param string $type Object name
     * @return array       Elements for the type
     */

    /**
     * Get SOAP elements for a complexType
     *
     * @param string $complexType Name of SOAP complexType
     *
     * @return array  Names of elements and their types
     */
    public function getSoapElements($complexType)
    {
        $types = $this->getSoapTypes();
        if (isset($types[$complexType])) {
            return $types[$complexType];
        }
    }

    /**
     * Get a SOAP type’s element
     *
     * @param string $complexType Name of SOAP complexType
     * @param string $element     Name of element belonging to SOAP complexType
     *
     * @return string
     */
    public function getSoapElementType($complexType, $element)
    {
        $elements = $this->getSoapElements($complexType);
        if ($elements && isset($elements[$element])) {
            return $elements[$element];
        }
    }
}
