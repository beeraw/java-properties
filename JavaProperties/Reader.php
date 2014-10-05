<?php

namespace JavaProperties;

/**
 * Java .properties file reader.
 *
 * @author Jean-Luc Petit
 */
class Reader
{

    /**
     * The path of the java .properties file.
     * @var string
     */
    protected $path;

    /**
     * The java properties.
     * @var array
     */
    protected $properties = array();

    /**
     * 
     * @param string $path
     */
    public function __construct($path)
    {
        $handle = fopen($path, 'r');
        $multiline = false;
        while ($line = $this->unicodeEncode(rtrim(ltrim(fgets($handle), " \t"), "\n"))) {
            $isComment = !$multiline && (0 === strpos($line, '#') || 0 === strpos($line, '!'));
            if (empty($line) || $isComment) {
                continue;
            }

            if (!$multiline) {
                $name = stripslashes(rtrim(substr($line, 0, strpos($line, '=')), ' '));
                $temp = substr($line, strpos($line, '=') + 1, strlen($line));
                $value = rtrim(ltrim($temp, ' '), '\\');
            } else {
                $value .= rtrim($line, '\\');
            }

            if ('\\' === substr($line, -1)) {
                $value .= "\n";
                $multiline = true;
            } else {
                $multiline = false;
                $this->properties[$name] = $value;
            }
        }
        fclose($handle);
    }

    /**
     * Get the Java properties file.
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Encode iso-8859-1 (latin 1) escaped sequences to utf-8.
     * @param type $str
     * @return type
     */
    protected function unicodeEncode($str)
    {
        return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'ISO-8859-1');
        }, $str);
    }

}
