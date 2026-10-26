<?php
/**
 * Copyright © OOCS Commerce (https://www.oocscommerce.com). All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace OOCS\LynxProductAlert\Setup\Patch\Data;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use OOCS\LynxCatalog\Setup\Patch\Data\InstallCatalogLayouts;
use OOCS\LynxPageBuilder\Model\Design;

/**
 * Product alerts in the default design: both links under Add to Cart of every Product Detail, and the stock alert's
 * unsubscribe page. Nodes and layouts already there are kept.
 */
class AddProductAlertLayouts implements DataPatchInterface
{
    private const TABLE = 'lynx_pagebuilder_layout';

    private const UNSUBSCRIBE_PATH = 'productalert_unsubscribe_email';

    private const PRODUCT_ALERT = 'OOCS_LynxProductAlert/Components/ProductAlert';

    private const DETAIL_PREFIX = 'default_product_detail_';

    private const ALERTS = [
        'default_product_price_alert' => 'Price Alert',
        'default_product_stock_alert' => 'Stock Alert',
    ];

    /**
     * Setup for the connection, serializer for the node values
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param Json $json
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly Json $json,
    ) {
    }

    /**
     * Patches this one runs after
     *
     * @return string[]
     */
    public static function getDependencies(): array
    {
        // The product layouts the alerts go into are written there.
        return [InstallCatalogLayouts::class];
    }

    /**
     * Former names of this patch
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Add the alert nodes to the product layouts and the unsubscribe page's layout
     *
     * @return self
     */
    public function apply(): self
    {
        $connection = $this->moduleDataSetup->getConnection();
        $table      = $this->moduleDataSetup->getTable(self::TABLE);

        $select = $connection->select()
            ->from($table, ['path', 'value'])
            ->where('design_id = ?', Design::DEFAULT_ID)
            ->where('path LIKE ?', 'catalog\_product\_view\_type\_%');

        $rows = [];
        foreach ($connection->fetchPairs($select) as $path => $value) {
            $added = false;
            $nodes = $this->withAlerts($this->json->unserialize($value), $added);
            if ($added) {
                $rows[] = [
                    'design_id' => Design::DEFAULT_ID,
                    'path'      => $path,
                    'value'     => $this->json->serialize($nodes),
                ];
            }
        }
        if ($rows) {
            $connection->insertOnDuplicate($table, $rows, ['value']);
        }

        // Insert only: a layout already saved under the path stays as it is

        $saved = $connection->fetchOne(
            $connection->select()
                ->from($table, ['path'])
                ->where('design_id = ?', Design::DEFAULT_ID)
                ->where('path = ?', self::UNSUBSCRIBE_PATH)
        );
        if (!$saved) {
            $connection->insert($table, [
                'design_id' => Design::DEFAULT_ID,
                'path'      => self::UNSUBSCRIBE_PATH,
                'value'     => $this->json->serialize([$this->unsubscribePage()]),
            ]);
        }

        return $this;
    }

    /**
     * The nodes with the missing alerts added to Below Add to Cart of each Product Detail, nested ones included
     *
     * @param array $nodes
     * @param bool $added set when an alert was added
     * @return array
     */
    private function withAlerts(array $nodes, bool &$added): array
    {
        foreach ($nodes as &$node) {
            if (!\is_array($node)) {
                continue;
            }
            foreach ((array) ($node['slots'] ?? []) as $slot => $children) {
                $node['slots'][$slot] = $this->withAlerts((array) $children, $added);
            }
            if (!str_starts_with((string) ($node['name'] ?? ''), self::DETAIL_PREFIX)) {
                continue;
            }

            $slot = $node['slots']['info_after'] ?? [];
            $names = array_column($slot, 'name');
            foreach (self::ALERTS as $name => $label) {
                if (!\in_array($name, $names, true)) {
                    $slot[] = $this->node($name, self::PRODUCT_ALERT, $label);
                    $added = true;
                }
            }
            $node['slots']['info_after'] = $slot;
        }
        unset($node);

        return $nodes;
    }

    /**
     * The unsubscribe page: its component alone in a centred group
     *
     * @return array
     */
    private function unsubscribePage(): array
    {
        $group = $this->node('default_group', 'OOCS_LynxThemeComponent/Components/Group', 'Group', [
            'layout'    => 'constrained',
            'width'     => 'site',
            'padding_y' => 'sm',
            'padding_x' => 'none',
            'gap'       => 'md',
        ]);
        $group['slots'] = ['default' => [
            $this->node(
                'default_stock_alert_unsubscribe',
                'OOCS_LynxProductAlert/Components/StockAlertUnsubscribe',
                'Stock Alert Unsubscribe'
            ),
        ]];

        return $group;
    }

    /**
     * One node, in the shape the editor writes
     *
     * @param string $name
     * @param string $component
     * @param string $label
     * @param array $props
     * @return array
     */
    private function node(string $name, string $component, string $label, array $props = []): array
    {
        return [
            'label'     => $label,
            'name'      => $name,
            'component' => $component,
            'props'     => $props,
            'visible'   => true,
        ];
    }
}
