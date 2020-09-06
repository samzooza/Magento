<?php
 
namespace Magetop\Helloworld\Setup;
 
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
 
class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        //Add new fields to the created table
        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $table = $setup->getTable('magetop_blog');
            //Check for the existence of the table
            if ($setup->getConnection()->isTableExists($table) == true) {
                // Declare data
                $columns = [
                    'image' => [
                        'type' => Table::TYPE_TEXT,
                        ['nullable' => true],
                        'comment' => 'Image',
                    ],
                    'category_id' => [
                        'type' => Table::TYPE_INTEGER,
                        ['nullable' => false, 'default' => 0],
                        'comment' => 'Category ID',
                    ],
                ];
                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($table, $name, $definition);
                }
            }
        }
        //Create a new table
        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $categories = $setup->getTable('magetop_blog_categories');
            //Check for the existence of the table
            if ($setup->getConnection()->isTableExists($categories) != true) {
                $tableCategories = $setup->getConnection()
                    ->newTable($categories)
                    ->addColumn(
                        'cat_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                        'Category Id'
                    )
                    ->addColumn(
                        'status',
                        Table::TYPE_SMALLINT,
                        null,
                        ['nullable' => false, 'default' => 1],
                        'Status'
                    )
                    ->addColumn(
                        'cat_title',
                        Table::TYPE_TEXT,
                        null,
                        ['nullable' => false, 'default' => ''],
                        'Category Title'
                    )
                    ->addColumn(
                        'created_at',
                        Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false],
                        'Created At'
                    )
                    //Set comment for magetop_blog table
                    ->setComment('Magetop Blog Categories')
                    //Set option for magetop_blog table
                    ->setOption('type', 'InnoDB')
                    ->setOption('charset', 'utf8');
                $setup->getConnection()->createTable($tableCategories);
            }
        }
        $setup->endSetup();
    }
}