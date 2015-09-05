<?php

namespace Phpro\SoapClient\CodeGenerator\Generator;

/**
 * ClientGenerator.
 *
 * @author Adam Wójs <adam@wojs.pl>
 */
class ClientGenerator 
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $name;
    
    /**
     * @param $namespace
     */
    public function __construct($name, $namespace)
    {
        $this->name = $name;
        $this->namespace = $namespace;
    }

    /**
     * @param $methods
     *
     * @return string
     */
    public function generate($methods)
    {
        return $this->renderClient($methods);
    }    
    
    protected function renderClient(array $methods) 
    {
        $template = $this->getClientTemplate();
        $values = [
            '%namespace_block%' => $this->namespace ? sprintf("\n\nnamespace %s;", $this->namespace) : '',
            '%name%' => $this->name,
            '%methods%' => $this->renderMethods($methods)
        ];
        
        return $this->renderString($template, $values);
    }
    
    protected function renderMethods(array $methods) 
    {
        $template = $this->getMethodTemplate();
        $rendered = '';    
        foreach ($methods as $method) {
            $values = [
                '%name%' => $method->getName(),
                '%return_type%' => $method->getReturnType()
            ];
            
            $rendered .= $this->renderString($template, $values);
        }
        
        return $rendered;
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
    protected function getClientTemplate()
    {
        return file_get_contents(__DIR__ . '/templates/client.template');
    }    
    
    /**
     * @return string
     */
    protected function getMethodTemplate()
    {
        return file_get_contents(__DIR__ . '/templates/client-method.template');
    }     
}
