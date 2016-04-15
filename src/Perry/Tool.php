<?php

namespace Perry;

class Tool
{
    /**
     * Parse a content-type header to a representation name.
     *
     * @param string $contentType
     *
     * @return bool
     */
    public static function parseContentTypeToRepresentation($contentType)
    {
        $matches = array();

        preg_match('/^application\/(.*)\+json; charset=utf-8$/im', $contentType, $matches);

        if (count($matches) == 2) {
            return $matches[1];
        }

        return false;
    }

    /**
     * convert a representation name including version to an
     * array of namespace and class parts.
     *
     * @param string $inputRepresentation
     *
     * @return array Class parts, e.g.:
     *               ['Representation', 'Eve', '1', 'Alliance']
     *
     * @throws \Exception
     */
    public static function parseRepresentation($inputRepresentation)
    {
        $version = substr($inputRepresentation, -2);
        $representation = substr($inputRepresentation, 0, -3);

        switch (substr($representation, 0, 7)) {
            case 'vnd.ccp': // EVE
                $data = explode('.', $representation);
                array_shift($data);
                array_shift($data);
                array_shift($data);
                
                $parsed = ['Perry', 'Representation', 'Eve', $version, $data[0]];
                break;
            case 'net.3rd': // OldApi
                $data = explode('.', $representation);
                array_shift($data);
                array_shift($data);
                array_shift($data);
                
                $parsed = ['Perry', 'Representation', 'OldApi', $version, $data[0]];
                
                if (count($data) > 1) {
                    $parsed[] = $data[1];
                }
                break;
            default:
                throw new \Exception('Malformed representation string: '.$inputRepresentation);
        }

        return $parsed;
    }
    
    /**
     * convert a representation name including version to the
     * corresponding class.
     *
     * @param string $inputRepresentation
     *
     * @return string Class name, e.g.: 
     *                \\Perry\\Representation\\Eve\\1\\Alliance
     *
     * @throws \Exception
     */
    public static function parseRepresentationToClass($inputRepresentation)
    {
        $parsed = self::parseRepresentation($inputRepresentation);

        $classname = "\\" . implode("\\", $parsed);
        
        return $classname;
    }
}
