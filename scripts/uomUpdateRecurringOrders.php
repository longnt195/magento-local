<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', 30000);

error_reporting(E_ALL);

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\ResourceConnection;

include(__DIR__ .'/../app/bootstrap.php');
$bootstrap = Bootstrap::create(BP, $_SERVER);

$objectManager = $bootstrap->getObjectManager();

$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

$count = 0;
try {
    $resourceConnection = $objectManager->get(ResourceConnection::class);
    $connection = $resourceConnection->getConnection();

    $query = "SELECT main.profile_id, main.order_id, so.increment_id, po.order_id as initial_order_id, ov.value as publicly_acknowledge_gift
FROM aw_sarp2_profile_order main
INNER JOIN aw_sarp2_profile_order po ON po.profile_id = main.profile_id AND po.is_initial = 1
INNER JOIN sales_order so ON so.entity_id = main.order_id
LEFT JOIN amasty_order_attribute_entity aoae ON aoae.parent_id = po.order_id AND aoae.parent_entity_type = 1
LEFT JOIN amasty_order_attribute_entity_varchar pag ON
pag.attribute_id = (SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'publicly_acknowledge_gift')
AND pag.entity_id = aoae.entity_id
LEFT JOIN eav_attribute_option_value ov ON ov.option_id = pag.value
WHERE main.is_initial = 0 AND ov.value IS NOT NULL
UNION
SELECT main.profile_id, main.order_id, so.increment_id, po.order_id as initial_order_id, ov.value as publicly_acknowledge_gift
FROM aw_sarp2_profile_order main
INNER JOIN aw_sarp2_profile_order po ON po.profile_id = main.profile_id AND po.is_initial = 1
INNER JOIN sales_order so ON so.entity_id = main.order_id
INNER JOIN sales_order poso ON poso.entity_id = po.order_id
LEFT JOIN amasty_order_attribute_entity aoae ON aoae.parent_id = poso.quote_id AND aoae.parent_entity_type = 2
LEFT JOIN amasty_order_attribute_entity_varchar pag ON
pag.attribute_id = (SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'publicly_acknowledge_gift')
AND pag.entity_id = aoae.entity_id
LEFT JOIN eav_attribute_option_value ov ON ov.option_id = pag.value
WHERE main.is_initial = 0 AND ov.value IS NOT NULL";

    $records = $connection->fetchAll($query);


    $orderRepository = $objectManager->get('Magento\Sales\Api\OrderRepositoryInterface');

    foreach ($records as $record) {
        $initialOrder = $orderRepository->get($record['initial_order_id']);
        $attributesToCopy = [];
        $extensionAttributes = $initialOrder->getExtensionAttributes();
        $amastyAttributes = method_exists($extensionAttributes, 'getAmastyOrderAttributes') ?
            $extensionAttributes->getAmastyOrderAttributes() : [];
        foreach ($amastyAttributes as $amastyAttribute) {
            if (in_array($amastyAttribute->getAttributeCode(), ['publicly_acknowledge_gift'])) {
                $attributesToCopy[] = $amastyAttribute;
            }
        }
        $order = $orderRepository->get($record['order_id']);
        $orderAttributes = $order->getExtensionAttributes() ?: $this->orderExtensionFactory->create();
        $orderAttributes->setAmastyOrderAttributes($attributesToCopy);
        $order->setExtensionAttributes($orderAttributes);
        $orderRepository->save($order);
        echo $order->getEntityId();
        echo "\n";
    }

} catch (\Exception $e){
    echo $e->__toString();
}

echo "\nFINISH!\n";

?>
