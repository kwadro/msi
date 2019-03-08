<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\LegacyCatalogInventorySynchronization;

use Magento\InventoryCatalog\Model\LegacyCatalogInventorySynchronization\ToInventory\SetDataToSourceItem;
use Magento\InventoryCatalog\Model\LegacyCatalogInventorySynchronization\ToLegacyCatalogInventory\SetDataToLegacyInventory;

/**
 * Set Qty and status for legacy CatalogInventory Stock Item table
 */
class SetDataToDestination
{
    /**
     * @var SetDataToLegacyInventory
     */
    private $setDataToLegacyInventory;

    /**
     * @var SetDataToSourceItem
     */
    private $setDataToSourceItem;

    /**
     * SetDataToDestination constructor.
     * @param SetDataToLegacyInventory $setDataToLegacyInventory
     * @param SetDataToSourceItem $setDataToSourceItem
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        SetDataToLegacyInventory $setDataToLegacyInventory,
        SetDataToSourceItem $setDataToSourceItem
    ) {
        $this->setDataToLegacyInventory = $setDataToLegacyInventory;
        $this->setDataToSourceItem = $setDataToSourceItem;
    }

    /**
     * @param string $direction
     * @param array $skus
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(string $direction, array $skus): void
    {
        if ($direction === Synchronize::DIRECTION_TO_LEGACY) {
            $this->setDataToLegacyInventory->execute($skus);
        } else {
            $this->setDataToSourceItem->execute($skus);
        }
    }
}
