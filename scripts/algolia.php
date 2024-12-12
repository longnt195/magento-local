<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', 30000);

error_reporting(E_ALL);

use Magento\Framework\App\Bootstrap;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItemInterface;

include(__DIR__ .'/../app/bootstrap.php');
$bootstrap = Bootstrap::create(BP, $_SERVER);

$objectManager = $bootstrap->getObjectManager();
$GLOBALS['objectManager'] = $objectManager;

$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

try {
    $algolia = $objectManager->get('\Overdose\AlgoliaPopularProduct\Model\Handler\Data');
    $algolia->rebuildStorePopularProductIndex(12, [82295]);
    $algolia = $objectManager->get('\Overdose\AlgoliaMagefanBlog\Model\Indexer\PostIndexer');
    $algolia->execute([94]);
} catch (\Exception $e){
    echo $e->__toString();
}

echo 'FINISH!';

?>
