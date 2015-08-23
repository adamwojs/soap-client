<?php

namespace Phpro\SoapClient\CodeGenerator\Generator;

/**
 * Class TypeGenerator
 *
 * @package Phpro\SoapClient\Generator
 */
class TypeGenerator
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @param $namespace
     */
    public function __construct($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @param $type
     * @param $properties
     *
     * @return string
     */
    public function generate($type, array $properties)
    {
        return $this->renderType($type, $properties);
    }

    /**
     * @param array $properties
     *
     * @return string
     */
    protected function renderProperties(array $properties)
    {
        $template = $this->getTypePropertyTemplate();
        $rendered = '';
        foreach ($properties as $property => $type) {
            $type = strtr($type, [
                'long' => 'int',
                'dateTime' => '\\DateTime',
                'date' => '\\DateTime',
                'boolean' => 'bool',
            ]);

            $values = [
                '%property%' => $property,
                '%type%' => $type
            ];
            $rendered .= $this->renderString($template, $values);
        }

        return $rendered;
    }
    
    /**
     * @param array $properties
     * 
     * @return string
     */
    protected function renderGetters(array $properties) 
    {
        $template = $this->getTypePropertyGetterTemplate();
        $rendered = '';
        foreach ($properties as $property => $type) {
            $type = strtr($type, [
                'long' => 'int',
                'dateTime' => '\\DateTime',
                'date' => '\\DateTime',
                'boolean' => 'bool',
            ]);

            $values = [
                '%method%' => 'get'.ucfirst($property),
                '%property%' => $property,
                '%type%' => $type
            ];
            $rendered .= $this->renderString($template, $values);            
        }
        
        return $rendered;
    }

    /**
     * @param array $properties
     * 
     * @return string
     */
    protected function renderSetters(array $properties) 
    {
        $template = $this->getTypePropertySetterTemplate();
        $rendered = '';
        foreach ($properties as $property => $type) {
            $type = strtr($type, [
                'long' => 'int',
                'dateTime' => '\\DateTime',
                'date' => '\\DateTime',
                'boolean' => 'bool',
            ]);

            $values = [
                '%method%' => 'set'.ucfirst($property),
                '%property%' => $property,
                '%type%' => $type
            ];
            $rendered .= $this->renderString($template, $values);            
        }
        
        return $rendered;
    }    
    
    /**
     * @param string $type
     * @param array $properties
     *  
     * @return string
     */
    protected function renderType($type, array $properties)
    {
        $template = $this->getTypeTemplate();
        $values = [
            '%name%' => ucfirst($type),
            '%namespace_block%' => $this->namespace ? sprintf("\n\nnamespace %s;", $this->namespace) : '',
            '%properties%' => $this->renderProperties($properties),
            '%getters%' => $this->renderGetters($properties),
            '%setters%' => $this->renderSetters($properties)
        ]; 
        
        return $this->renderString($template, $values);
    }

    /**
     * @param $template
     * @param $values
     *
     * @return string
     */
    protected function renderString($template, array $values)
    {
        return strtr($template, $values);
    }

    /**
     * @return string
     */
    protected function getTypeTemplate()
    {
        return file_get_contents(__DIR__ . '/templates/type.template');
    }

    /**
     * @return string
     */
    protected function getTypePropertyTemplate()
    {
        return file_get_contents(__DIR__ . '/templates/type-property.template');
    }
    
    /**
     * @return string
     */
    protected function getTypePropertyGetterTemplate() 
    {
        return file_get_contents(__DIR__ . '/templates/type-property-getter.template');
    }
    
    /**
     * @return string
     */
    protected function getTypePropertySetterTemplate() 
    {
        return file_get_contents(__DIR__ . '/templates/type-property-setter.template');
    }    
}
