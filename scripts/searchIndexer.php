<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', 30000);

error_reporting(E_ALL);

use Magento\Framework\App\Bootstrap;

include(__DIR__ .'/../app/bootstrap.php');
$bootstrap = Bootstrap::create(BP, $_SERVER);

$objectManager = $bootstrap->getObjectManager();

$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

try {
    $data = [
        "indexer_id" => "catalogsearch_fulltext",
        "primary" => null,
        "view_id" => "catalogsearch_fulltext",
        "action_class" => "Magento\CatalogSearch\Model\Indexer\Fulltext",
        "shared_index" => null,
        "title" => "Catalog Search",
        "description" => "Rebuild Catalog product fulltext search index",
        "dependencies" => ["catalog_category_product", "cataloginventory_stock", "catalogpermissions_product", "catalog_product_price"],
        "fieldsets" => [],
        "structure" => "Magento\CatalogSearch\Model\Indexer\IndexStructure"
    ];
    $fulltext = $objectManager->create('Magento\CatalogSearch\Model\Indexer\Fulltext', ['data' => $data]);
    $price = $objectManager->create('Magento\Catalog\Model\Indexer\Product\Price');
    $permission = $objectManager->create('Magento\CatalogPermissions\Model\Indexer\Product');
    $stock = $objectManager->create('Magento\CatalogInventory\Model\Indexer\Stock');

    $productIds = [330930, 330709, 330961, 1146555,
    	329578,
	329577,
	329576,
	329575,
	329574,
	329573,
	329572,
	329571,
	314694];

    $selection = $objectManager->create('\Magento\Bundle\Model\ResourceModel\Selection');
    $options = $selection->getChildrenIds(330930);
    $productIds = array_merge($productIds, ...$options);

    $price->execute($productIds);
    $fulltext->execute($productIds);
    $permission->execute($productIds);
    $stock->execute($productIds);

//    $time_start = microtime(true);
//    $productRepository = $objectManager->get('Magento\Catalog\Model\ProductRepository');
//    $productRepository->getById(330641);
//    $time_end = microtime(true);
//    $execution_time = ($time_end - $time_start);
//    var_dump($execution_time);
} catch (\Exception $e){
    echo $e->__toString();
}

echo 'FINISH!';

?>
