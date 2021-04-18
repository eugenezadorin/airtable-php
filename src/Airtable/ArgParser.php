<?php

declare(strict_types=1);

namespace Zadorin\Airtable;

use Zadorin\Airtable\Errors;

class ArgParser
{
    /**
     * @param mixed ...$args
     * @return Record[]
     * @throws Errors\InvalidArgument
     */
    public static function makeRecordsFromFields(...$args): array
    {
        $records = [];
        
        /** @var mixed $arg */
        foreach ($args as $arg) {
            if (is_array($arg)) {

                if (self::isArrayOfArrays($arg)) {
                    /** @var array $fields */
                    foreach ($arg as $fields) {
                        $records[] = new Record($fields);
                    }
                } elseif (self::isArrayOfRecords($arg)) {
                    /** @var Record $record */
                    foreach ($arg as $record) {
                        $records[] = $record;
                    }
                } else {
                    $records[] = new Record($arg);
                }

            } elseif ($arg instanceof Record) {
                $records[] = $arg;
            } else {
                throw new Errors\InvalidArgument('Only arrays or instances of Zadorin\Airtable\Record are allowed');
            }
        }
        return $records;
    }

    /**
     * @param mixed ...$args
     * @return Record[]
     * @throws Errors\InvalidArgument
     */
    public static function makeRecordsFromIds(...$args): array
    {
        $records = [];

        /** @var mixed $arg */
        foreach ($args as $arg) {
            if ($arg instanceof Record) {
                $records[] = $arg;
            } elseif (is_string($arg)) {
                $records[] = new Record([], $arg);
            } else {
                throw new Errors\InvalidArgument('Only record ids or instances of Zadorin\Airtable\Record are allowed');
            }
        }
        return $records;
    }

    /**
     * @param mixed $var
     * @return bool
     */
    public static function isArrayOfArrays($var): bool
    {
        if (!is_array($var)) {
            return false;
        }
        if (count($var) < 1) {
            return false;
        }
        foreach ($var as $item) {
            if (!is_array($item)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param mixed $var
     * @return bool
     */
    protected static function isArrayOfRecords($var): bool
    {
        if (!is_array($var)) {
            return false;
        }
        if (count($var) < 1) {
            return false;
        }
        foreach ($var as $item) {
            if (!($item instanceof Record)) {
                return false;
            }
        }
        return true;
    }
}
