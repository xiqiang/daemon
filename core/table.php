<?php


/**
 * read table
 **/

class Table
{
    public static $COL_TYPES = [
        'INT'       => 1,
        'FLOAT'     => 1,
        'STRING'    => 1,
        'COMMENT'   => 1,
        'BOOL'      => 1,
    ];

    public static $delimiter = "\t";        // splitter
    public static $skipRowPrefix = '#';     // prefix to ignor the whole line.

    // read txt file with tab splitter
    // the codes with encoding operations reference：
    // https://stackoverflow.com/questions/15092764/how-to-read-unicode-text-file-in-php

    public static function read($fileName)
    {
        Frame::log(E_NOTICE, "reading table '$fileName'...");

        $handle = fopen($fileName, 'r');
        if(!$handle) {
            Frame::log(E_ERROR, "read table '$fileName' failed: can't open");
            return false;
        }
        
        $bom = fread($handle, 2);
        rewind($handle);
        if(!$bom) {
            Frame::log(E_ERROR, "read table '$fileName' failed: read bom error");
            return false;
        }

        $encoding = '';
        if($bom === chr(0xff).chr(0xfe) || $bom === chr(0xfe).chr(0xff))
            $encoding = 'UTF-16';       // UTF-16
        else
            $encoding = 'GB 2312';      // GB-2312
        stream_filter_append($handle, "convert.iconv.'$encoding'/UTF-8");

        $typeLine = fgets($handle);
        if(!$typeLine) {
            Frame::log(E_ERROR, "read table '$fileName' failed: can't read typeLine");
            return false;
        }

        $typeRow = str_getcsv($typeLine, self::$delimiter);
        foreach($typeRow as $typeName) {
            if(!self::$COL_TYPES[$typeName]) {
                Frame::log(E_ERROR, "read table '$fileName' failed: invalid type [$typeName]");
                return false;
            }
        }

        $commentLine = fgets($handle);
        if(!$typeLine) {
            Frame::log(E_ERROR, "read table '$fileName' failed: can't read typeLine");
            return false;
        }

        $nameLine = fgets($handle);
        if(!$nameLine) {
            Frame::log(E_ERROR, "read table '$fileName' failed: can't read nameLine");
            return false;
        }

        $nameRow = str_getcsv($nameLine, self::$delimiter);
        $nameRowSize = count($nameRow);
        $typeRowSize = count($typeRow);

        if($nameRowSize != $typeRowSize) {
            Frame::log(E_ERROR, "read table '$fileName' failed: column size mismatch nameRow[$nameRowSize]-typeRow[$typeRowSize]");
            return false;
        }

        $table = array();
        $lineIndex = 3;         // skip: type, comment, name

        while (!feof($handle)) {
            ++$lineIndex;
            $line = fgets($handle);
            if(empty($line))
                continue;

            $row = str_getcsv($line, self::$delimiter);
            if(self::$skipRowPrefix == substr($row[0], 0, 1))
                continue;

            $rowSize = count($row);
            if($rowSize != $nameRowSize) {
                Frame::log(E_ERROR, "read table '$fileName' failed: column size mismatch in line:$lineIndex row[$rowSize]-nameRow[$nameRowSize]");
                return false;
            }

            $table[$row[0]] = array_combine($nameRow, $row);
        }

        fclose($handle);
        //Frame::log(E_NOTICE, "read table '$fileName'[$encoding] successful.");
        return $table;
    }
}
