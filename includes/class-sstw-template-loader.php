<?php

class SstwTemplateLoader
{

    protected $base;

    public function __construct($base = '')
    {
        $this->base = plugin_dir_path(dirname(__FILE__)) . "{$base}/partials/";
    }

    /**
     * @see wp-includes/general-template.php:146 for standard WP implementation
     *
     * @param string $name
     * @param array $namedVariables
     * @param bool $echo
     * @return string
     * @throws Exception
     */
    public function load($name = null, array $namedVariables = [], $echo = true)
    {
        $name = (string) $name;

        $template = "{$this->base}{$name}.php";

        if (! file_exists($template)) {
            throw new Exception("Template with the name \"{$name}\" not found, full path: {$template}");
        }

        foreach ($namedVariables as $variableName => $value) {
            if (! self::isVariableNameValid($variableName)) {
                trigger_error('Variable names must be valid. Skipping "' . $variableName . '" because it is not a valid variable name.');

                continue;
            }

            $$variableName = $value;
        }

        if(! $echo) {
            ob_start();
        }

        require $template;

        if(! $echo) {
            $template = ob_get_clean();
        }

        return $template;
    }

    /**
     * Check if the provided $variableName is valid.
     *
     * @param $variableName
     * @return bool
     */
    private static function isVariableNameValid($variableName)
    {
        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_\x7f-\xff]*/', $variableName)) {
            return true;
        }

        return false;
    }

}