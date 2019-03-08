<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\InventoryApi;

use Magento\Framework\App\ObjectManager;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryCatalog\Model\LegacyCatalogInventorySynchronization\Synchronize;
use Magento\InventoryCatalog\Model\SourceItemsSaveSynchronization\SetDataToLegacyCatalogInventory;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;

/**
 * Synchronization between legacy Stock Items and saved Source Items
 */
class SetDataToLegacyCatalogInventoryAtSourceItemsSavePlugin
{
    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemsAllowedForProductType;

    /**
     * @var GetProductTypesBySkusInterface
     */
    private $getProductTypeBySku;

    /**
     * @var Synchronize|null
     */
    private $synchronize;

    /**
     * @param DefaultSourceProviderInterface $defaultSourceProvider @deprecated
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemsAllowedForProductType @deprecated
     * @param GetProductTypesBySkusInterface $getProductTypeBySku @deprecated
     * @param SetDataToLegacyCatalogInventory $setDataToLegacyCatalogInventory @deprecated
     * @param Synchronize|null $synchronize
     * @SuppressWarnings(PHPMD.LongVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        DefaultSourceProviderInterface $defaultSourceProvider,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemsAllowedForProductType,
        GetProductTypesBySkusInterface $getProductTypeBySku,
        SetDataToLegacyCatalogInventory $setDataToLegacyCatalogInventory,
        Synchronize $synchronize = null
    ) {
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->isSourceItemsAllowedForProductType = $isSourceItemsAllowedForProductType;
        $this->getProductTypeBySku = $getProductTypeBySku;
        $this->synchronize = $synchronize ?:
            ObjectManager::getInstance()->get(Synchronize::class);
    }

    /**
     * @param SourceItemsSaveInterface $subject
     * @param void $result
     * @param SourceItemInterface[] $sourceItems
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(SourceItemsSaveInterface $subject, $result, array $sourceItems): void
    {
        $skuToSynchronize = [];
        foreach ($sourceItems as $sourceItem) {
            if ($sourceItem->getSourceCode() !== $this->defaultSourceProvider->getCode()) {
                continue;
            }

            $sku = $sourceItem->getSku();

            $productTypes = $this->getProductTypeBySku->execute([$sku]);
            if (isset($productTypes[$sku])) {
                $typeId = $productTypes[$sku];
            } else {
                continue;
            }

            if (false === $this->isSourceItemsAllowedForProductType->execute($typeId)) {
                continue;
            }

            $skuToSynchronize[] = $sourceItem->getSku();
        }

        $this->synchronize->execute(Synchronize::DIRECTION_TO_LEGACY, $skuToSynchronize);
    }
}
