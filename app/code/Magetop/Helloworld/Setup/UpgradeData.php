<?php
 
namespace Magetop\Helloworld\Setup;
 
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
 
class UpgradeData implements UpgradeDataInterface
{
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
 
        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $tableName = $setup->getTable('magetop_blog_categories');
            //Check for the existence of the table
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $data = [
                    [
                        'cat_title' => 'News',
                        'status' => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'cat_title' => 'Tutorials',
                        'status' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                    ],
                    [
                        'cat_title' => 'Uncategorized',
                        'status' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]
                ];
                foreach ($data as $item) {
                    //Insert data
                    $setup->getConnection()->insert($tableName, $item);
                }
            }
        }
        $setup->endSetup();
    }
}