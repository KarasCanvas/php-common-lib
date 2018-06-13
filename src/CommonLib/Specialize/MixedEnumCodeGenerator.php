<?php
/**
 * @author Raven <karascanvas@qq.com>
 */
namespace CommonLib\Specialize;

abstract class MixedEnumCodeGenerator
{
    private function __construct() { }


    public static function build($dir, $output = '_enum.php', $ns = null)
    {
        $out = [];
        $out[] = '<?php';
        $out[] = '';
        if($ns) {
            $out[] = sprintf('namespace %s;', $ns);
            $out[] = '';
        }
        $out[] = 'use CommonLib\Specialize\MixedEnumBase;';
        $out[] = '';
        $out[] = 'abstract class Enum extends MixedEnumBase';
        $out[] = '{';
        $data = static::getEnumData($dir);

        foreach ($data as $file => $enum) {
            if($enum['name'] == $enum['text']) {
                $out[] = sprintf('    // Enum : %s', $enum['text']);
            } else {
                $out[] = sprintf('    // Enum : %s (%s)', $enum['name'], $enum['text']);
            }
            foreach ($enum['fields'] as $field) {
                $out[] = sprintf('    const %s_%s = %s; // %s', $enum['name'], $field['name'], $field['value'], $field['text']);
            }
            $out[] = '';
        }
        $out[] = '    protected static $data = [';
        foreach ($data as $file => $enum) {
            $out[] = sprintf("        '%s' => [", $enum['name']);
            foreach ($enum['fields'] as $field) {
                $out[] = sprintf("            %s => ['%s', '%s'],", $field['value'], $field['name'], $field['text']);
            }
            $out[] = '        ],';
        }
        $out[] = '    ];';
        $out[] = '';
        $out[] = '}';
        file_put_contents($output, implode(PHP_EOL, $out));
    }


    protected static function getEnumData($dir)
    {
        $files = scandir($dir);
        $list = [];
        foreach ($files as $file) {
            if (preg_match('/^(\w+)\.txt$/i', $file, $matches)) {
                $name = strtoupper(trim($matches[1]));
                $content = file_get_contents($dir . '\\' . $file);
                $list[$file] = static::parseEnum($content, $name);
            }
        }
        return $list;
    }


    protected static function parseEnum($content, $name)
    {
        $text = $name;
        $lines = explode(PHP_EOL, $content);
        $fields = [];
        for ($i = 0; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if ($line == '' || static::isCommentLine($line)) {
                continue;
            }
            if (static::isNameDefinition($line)) {
                $name = strtoupper(trim(substr($line, 1)));
            } elseif (static::isTextDefinition($line)) {
                $text = trim(substr($line, 1));
            } elseif (static::parseField($lines[$i], $i, $field)) {
                $fields[] = $field;
            }
        }
        return [
            'name'   => $name,
            'text'   => $text,
            'fields' => $fields
        ];
    }


    protected static function isNameDefinition($line)
    {
        return strpos($line, '@') === 0;
    }


    protected static function isTextDefinition($line)
    {
        return strpos($line, '>') === 0;
    }


    protected static function isCommentLine($line)
    {
        return strpos($line, '#') === 0 || strpos($line, '--') === 0;
    }


    protected static function parseField($line, $index, &$field)
    {
        $line = trim($line);
        if ($line == '') {
            return false;
        }
        $text = null;
        if (strpos($line, '|') !== false) {
            $tmp = explode('|', $line);
            $line = trim($tmp[0]);
            $text = trim($tmp[1]);
        }
        $tmp = explode('=', $line);
        if (count($tmp) > 1) {
            $field['value'] = strtoupper(trim($tmp[1]));
            $field['name'] = strtoupper(trim($tmp[0]));
        } else {
            $field['value'] = $index;
            $field['name'] = strtoupper(trim($tmp[0]));
        }
        $field['text'] = $text == null ? $field[1] : $text;
        return true;
    }

}