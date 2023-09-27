<?php
namespace App;
use MongoDB\Client;
use Exception;

class DataAccessMDB
{
    private static $url;

    public static function url(string $url)
    {
        self::$url = $url;
    }

    public static function RetrieveWholeCollection(string $databaseStr, string $collectionStr)
    {
        $mongoClient = new Client(self::$url);
        $database = $mongoClient->$databaseStr;
        $collection = $database->$collectionStr;
        try
        {
            $cursor = $collection->find([]);            
            if($cursor)
            {
                $result = [];
                foreach($cursor as $document)
                {
                    $result[] = $document->getArrayCopy();
                }
                return $result;
            }
            else
            {
                return false;
            }
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }
    }

    /**
     * @param string $databaseStr
     * @param string $collectionStr
     * @param array $keys
     * @param array $values
     * 
     * @return array|false Returns an associative array with the content of the document/s found on success, or false on failure.
     */
    public static function FindDocument(string $databaseStr, string $collectionStr, array $keys, array $values)
    {
        $mongoClient = new Client(self::$url);
        $database = $mongoClient->$databaseStr;
        $collection = $database->$collectionStr;
        try
        {
            if (!is_array($keys) || !is_array($values) || count($keys) !== count($values))
            {
                throw new Exception("Invalid input: Columns and values must be arrays, and be of the same length.");
            }

            $filter =
            [
                '$and' => [],
            ];

            for($i = 0; $i < count($keys); $i++)
            {
                $filter['$and'][] = [$keys[$i] => $values[$i]];
            }
            
            $cursor = $collection->find($filter);
            if($cursor)
            {
                $result = [];
                foreach($cursor as $document)
                {
                    $result[] = $document->getArrayCopy();
                }
                return $result;
            }
            else
            {
                return false;
            }
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }
    }

    /**
     * Retrieves a vaule from a document on the db based on a set of keys and values.
     * @param string $databaseStr
     * @param string $collectionStr
     * @param array $keys Array of the properties that have to match the values.
     * @param array $values Array of values that have to match.
     * @param string $valueToBring Name of the property to seek.
     * 
     * @return mixed|false Returns the value if success, false if failure.
     */
    public static function FindDocumentValue(string $databaseStr, string $collectionStr, array $keys, array $values, string $valueToBring)
    {
        $mongoClient = new Client(self::$url);
        $database = $mongoClient->$databaseStr;
        $collection = $database->$collectionStr;
        try
        {
            if (!is_array($keys) || !is_array($values) || count($keys) !== count($values))
            {
                throw new Exception("Invalid input: Columns and values must be arrays, and be of the same length.");
            }

            $filter =
            [
                '$and' => [],
            ];

            for($i = 0; $i < count($keys); $i++)
            {
                $filter['$and'][] = [$keys[$i] => $values[$i]];
            }

            $cursor = $collection->find($filter);
            if($cursor)
            {
                foreach($cursor as $document)
                {
                    if(isset($document["{$valueToBring}"]))
                    {
                        return $document["{$valueToBring}"];
                    }                    
                }
            }
            else
            {
                return false;
            }
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }
    }

    /**
     * Inserts a new document to into a database and collection with the provided keys and values.
     * @param string $databaseStr
     * @param string $collectionStr
     * @param array $keys
     * @param array $values
     * 
     * @return bool True on success, false on failure.
     */
    public static function InsertDocument(string $databaseStr, string $collectionStr, array $keys, array $values)
    {
        $mongoClient = new Client(self::$url);
        $database = $mongoClient->$databaseStr;
        $collection = $database->$collectionStr;
        try
        {
            if (!is_array($keys) || !is_array($values) || count($keys) !== count($values))
            {
                throw new Exception("Invalid input: Columns and values must be arrays, and be of the same length.");
            }

            $document = [];
            for($i = 0; $i < count($keys); $i++)
            {
                $document[$keys[$i]] = $values[$i];
            }

            $result = $collection->insertOne($document);
            if($result->getInsertedCount() > 0)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }
    }

    /**
     * Deletes a document where the keys and values match.
     * @param string $databaseStr
     * @param string $collectionStr
     * @param array $whereKeys
     * @param array $whereValues
     * 
     * @return bool True on success, false on failure.
     */
    public static function DeleteDocument(string $databaseStr, string $collectionStr, array $whereKeys, array $whereValues)
    {
        $mongoClient = new Client(self::$url);
        $database = $mongoClient->$databaseStr;
        $collection = $database->$collectionStr;
        try
        {
            if (!is_array($whereKeys) || !is_array($whereValues) || count($whereKeys) !== count($whereValues))
            {
                throw new Exception("Invalid input: Columns and values must be arrays, and be of the same length.");
            }

            $filter =
            [
                '$and' => [],
            ];

            for($i = 0; $i < count($whereKeys); $i++)
            {
                $filter['$and'][] = [$whereKeys[$i] => $whereValues[$i]];
            }
            $result = $collection->deleteOne($filter);
            if($result->getDeletedCount() > 0)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }
    }

    /**
     * Updates documents based on the filter keys and values, modifying the provided keys and values for the update. It has 2 modes to update either the first
     * occurrence, os all the documents that match the filter.
     * @param string $databaseStr
     * @param string $collectionStr
     * @param array $whereKeys
     * @param array $whereKalues
     * @param mixed $keysUpdate
     * @param mixed $valuesUpdate
     * @param bool $manyDocuments
     * 
     * @return bool True on success, false on failure.
     */
    public static function UpdateDocuments(string $databaseStr, string $collectionStr, array $whereKeys, array $whereKalues, $keysUpdate, $valuesUpdate, $manyDocuments = false)
    {
        $mongoClient = new Client(self::$url);
        $database = $mongoClient->$databaseStr;
        $collection = $database->$collectionStr;
        try
        {
            if (!is_array($whereKeys) || !is_array($whereKalues) || count($whereKeys) !== count($whereKalues))
            {
                throw new Exception("Invalid input: Columns and values must be arrays, and be of the same length.");
            }

            if (!is_array($keysUpdate) || !is_array($valuesUpdate) || count($keysUpdate) !== count($valuesUpdate))
            {
                throw new Exception("Invalid input: Update Columns and Update Values must be arrays, and be of the same length.");
            }

            $filter =
            [
                '$and' => [],
            ];

            for($i = 0; $i < count($whereKeys); $i++)
            {
                $filter['$and'][] = [$whereKeys[$i] => $whereKalues[$i]];
            }

            $update = 
            [
                '$set' => [],
            ];

            for($i = 0; $i < count($keysUpdate); $i++)
            {
                $update['$set'][$keysUpdate[$i]] = $valuesUpdate[$i];
            }
            
            if($manyDocuments == true)
            {
                $updateResult = $collection->updateMany($filter, $update);
            }
            else
            {
                $updateResult = $collection->updateOne($filter, $update);
            }
            if($updateResult->getModifiedCount() > 0)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }
    }
}