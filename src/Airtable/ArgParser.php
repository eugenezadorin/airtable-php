<?php

declare(strict_types=1);

namespace Zadorin\Airtable;

use \InvalidArgumentException;

class ArgParser
{
    /**
     * @var mixed $args
     * @return Record[]
     * @throws InvalidArgumentException
     */
    public static function makeRecordsFromFields(...$args): array
    {
        $records = [];
        foreach ($args as $arg) {
            if (is_array($arg)) {
                
                // we want allways work with array of arrays
                $first = reset($arg);
                if (!is_array($first) && !($first instanceof Record)) {
                    $arg = [$arg];
                }

                foreach ($arg as $fields) {
                    if (is_array($fields)) {
                        $records[] = new Record($fields);
                    } elseif ($fields instanceof Record) {
                        $records[] = $fields;
                    } else {
                        throw new InvalidArgumentException('Only arrays or instances of Zadorin\Airtable\Record are allowed');
                    }
                }
            } elseif ($arg instanceof Record) {
                $records[] = $arg;
            } else {
                throw new InvalidArgumentException('Only arrays or instances of Zadorin\Airtable\Record are allowed');
            }
        }
        return $records;
    }

    /**
     * @var mixed $args
     * @return Record[]
     * @throws InvalidArgumentException
     */
    public static function makeRecordsFromIds(...$args): array
    {
        $records = [];
        foreach ($args as $arg) {
            if ($arg instanceof Record) {
                $records[] = $arg;
            } elseif (is_string($arg)) {
                $records[] = new Record([], $arg);
            } else {
                throw new InvalidArgumentException('Only arrays or instances of Zadorin\Airtable\Record are allowed');
            }
        }
        return $records;
    }
}
