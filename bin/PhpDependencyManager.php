<?php

namespace PhpDependencyManager\Bin;

use Hoa\Iterator\Map;
use PhpDependencyManager\Entities\MapEntities;
use PhpDependencyManager\GraphDatabaseManager\GraphDatabaseManagerException;
use PhpDependencyManager\Extractor\EntityExtractor;
use PhpDependencyManager\Extractor\RelationExtractor;
use PhpDependencyManager\FileParser\ComposerJsonParserException;
use PhpDependencyManager\GraphDatabaseManager\Neo4JFactory;
use PhpDependencyManager\GraphDatabaseManager\Neo4JNodeManager;

require __DIR__ . '/../vendor/autoload.php';

if (empty($argv[1]) || !is_dir($argv[1])) {
    echo "usage : PhpDependencyManager $1 -> php sources directory $2{optional} -> path/to/root/composer.json\n";
    exit;
}

try {
    $entityExtractor = new EntityExtractor();

    echo "+ Recursivly parse PHP files in " . $argv[1] . "\n";
    $entityExtractor->extractObject($argv[1]);

//    if (!empty($argv[2]) && is_file($argv[2])){
//        if (file_exists($argv[2])){
            echo "+ Recursivly parse composer.json in " . $argv[1] . "\n";
            $entityExtractor->extractComponent($argv[2], $argv[1]);
//        }
//    }

    $neo4JClient = Neo4JFactory::getNeo4JClient(array('host'=>'localhost', 'port'=>'7474'));
    $neo4JNodeManager = new Neo4JNodeManager($neo4JClient);
    $neo4JNodeManager->deleteAllData();
    $mapEntities = new MapEntities($neo4JNodeManager);
    $mapEntities->mapEntities($entityExtractor->getDTOCollection());
    
    /*
    $dataManager = new DataManager();

    echo "+ Create schema (see bin/query.log)\n";
    $dataManager->createSchema($dependencyExtractor->getDTOCollection());

    $dBdriver = new Neo4JDriver(array('schema' => "default", 'url' => "http://localhost:7474"));

    echo "+ Drop previous schema \n";
    $dBdriver->dropData(true);

    echo "+ Sending query \n";
    $queries = array_merge(
        array_values($dataManager->nodeQueryCollection),
        array_values($dataManager->realComponentQueryCollection),
        array_values($dataManager->relationQueryCollection)
    );
    $dump = implode($queries);
    $dBdriver->executeQueries($queries);
    file_put_contents(__DIR__. '/query.log',$dump);
        */
} catch (ComposerJsonParserException $e){
    echo ($e . "\n");
} catch (DataBaseDriverException $e) {
    echo ($e . "\n");
} catch (Exception $e) {
    echo ($e . "\n");
}
