<?php

namespace Phpro\SoapClient\CodeGenerator\Generator;

use Phpro\SoapClient\Soap\SoapFunction;

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
     * @param $functions
     *
     * @return string
     */
    public function generate($type, array $properties, array $functions)
    {
        return $this->renderType(
            $type,
            $properties,
            $this->isRequestType($type, $functions),
            $this->isResultType($type, $functions)
        );
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
     * @param boolean $isRequestType
     * @param boolean $isResultType
     *
     * @return string
     */
    protected function renderType($type, array $properties, $isRequestType, $isResultType)
    {
        $template = $this->getTypeTemplate($isRequestType, $isResultType);

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
     * @param string $type
     * @param SoapFunction[] $functions
     * @return boolean
     */
    protected function isResultType($type, array $functions)
    {
        foreach ($functions as $function) {
            if ($function->getReturnType() === $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $type
     * @param SoapFunction[] $functions
     * @return boolean
     */
    protected function isRequestType($type, array $functions)
    {
        foreach ($functions as $function) {
            if (count($function->getArguments()) > 1) {
                continue;
            }
            foreach ($function->getArguments() as $argType) {
                if ($type === $argType) {
                    return true;
                }
            }
        }

        return false;
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
     * @param $isRequestType
     * @param $isResultType
     * @return string
     */
    protected function getTypeTemplate($isRequestType, $isResultType)
    {
        if ($isRequestType) {
            return file_get_contents(__DIR__ . '/templates/type.request.template');
        }

        if ($isResultType) {
            return file_get_contents(__DIR__.'/templates/type.result.template');
        }

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
