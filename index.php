<?php

require './vendor/autoload.php';

use MongoDB\Client;
use App\DataAccessMDB;

DataAccessMDB::url("mongodb://localhost:27017");

//echo var_dump(DataAccessMDB::FindDocument('mydatabase', 'mycollection', ['name'], ['TEST']));
//DataAccessMDB::FindValue('mydatabase', 'mycollection', ['name'], ['TEST'], 'age');
//DataAccessMDB::InsertDocument('mydatabase', 'mycollection', ['name', 'age', 'gender'], ['pepe', 30, 'male']);
//DataAccessMDB::DeleteDocument('mydatabase', 'mycollection', ['gender'], ['male']);
//DataAccessMDB::UpdateDocuments('mydatabase', 'mycollection', ['name'], ['pepe'], ['age'], ['60']);