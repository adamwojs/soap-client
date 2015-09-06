<?php

namespace Phpro\SoapClient\CodeGenerator\Generator;

use Phpro\SoapClient\Soap\SoapFunction;

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

    /**
     * @param SoapFunction[] $functions
     *
     * @return string
     */
    protected function renderMethods(array $functions)
    {
        $template = $this->getMethodTemplate();

        $rendered = '';
        foreach ($functions as $function) {
            $values = [
                '%name%' => $function->getName(),
                '%arguments%' => $this->renderArguments($function),
                '%call_arguments%' => $this->renderCallArguments($function),
                '%return_type%' => ucfirst($function->getReturnType())
            ];

            $rendered .= $this->renderString($template, $values);
        }

        return $rendered;
    }

    /**
     * @param SoapFunction $function
     *
     * @return string
     */
    protected function renderArguments(SoapFunction $function)
    {
        $arguments = [];
        foreach ($function->getArguments() as $name => $type) {
            $arguments[] = sprintf('%s %s', $type, $name);
        }

        return implode(', ', $arguments);
    }

    /**
     * @param SoapFunction $function
     *
     * @return string
     */
    protected function renderCallArguments(SoapFunction $function)
    {
        if (!$function->getArguments()->isEmpty()) {
            $arguments = [];
            foreach ($function->getArguments()->getKeys() as $name) {
                $arguments[] = $name;
            }

            return sprintf(', %s', implode(', ', $arguments));
        }

        return '';
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
