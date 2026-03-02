<?php

declare(strict_types=1);

namespace Jca\JcaLocationdevis\Install;

use Module;
use Tab;
use PrestaShopBundle\Install\SqlLoader;

class Installer
{
    private $tabs = [
        [
            'class_name' => 'AdminJcaLocationdevis',
            'parent_class_name' => 'AdminCatalog',
            'name' => 'Location Devis',
            'visible' => true,
            'icon' => '',
            'wording' => 'Configuration Location Devis',
            'wording_domain' => 'Modules.JcaLocationDevis.Admin'
        ],
    ];
    /**
     * Module's installation entry point.
     *
     * @param \Module $module
     *
     * @return bool
     */
    public function install($module): bool
    {
        if (!$this->installTabs($module)) {
            return false;
        }

        if (!$this->executeSqlFromFile($module->getLocalPath() . 'src/Install/install.sql')) {
            return false;
        }

        if (!$this->registerHooks($module)) {
            return false;
        }

        return true;
    }

    /**
     * @param \Module $module
     *
     * @return bool
     */
    public function uninstall(Module $module): bool
    {

        if (!$this->unregisterHooks($module)) {
            return false;
        }

        if (!$this->executeSqlFromFile($module->getLocalPath() . 'src/Install/uninstall.sql')) {
            return false;
        }

        return true;
    }

    /**
     * Register hooks for the module.
     *
     * @see https://devdocs.prestashop.com/8/modules/concepts/hooks/
     *
     * @param \Module $module
     *
     * @return bool
     */
    private function registerHooks(Module $module): bool
    {
        $hooks = [
            'actionAdminControllerSetMedia',
            'actionPresentProduct',
            'actionFrontControllerSetMedia',
            'actionCartUpdateQuantityBefore',
            'actionPresentCart',
            'actionCartUpdateQuantityAfter',
            'actionObjectCartProductAddAfter',
            'actionCartSave',
            'displayCustomerAccount'

        ];

        return (bool) $module->registerHook($hooks);
    }

    /**
     * Unregister hooks for the module.
     *
     * @param \Module $module
     *
     * @return bool
     */
    private function unregisterHooks(Module $module): bool
    {
        $hooks = [
            'actionAdminControllerSetMedia',
            'actionPresentProduct',
            'actionFrontControllerSetMedia',
            'actionCartUpdateQuantityBefore',
            'actionPresentCart',
            'actionCartUpdateQuantityAfter',
            'actionCartSave',

        ];

        foreach ($hooks as $hook) {
            if (!$module->unregisterHook($hook)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Install admin tabs for the module.
     *
     * @param \Module $module
     *
     * @return bool
     */
    public function installTabs(Module $module): bool
    {

        foreach ($this->tabs as $tabData) {
            $tabId = Tab::getIdFromClassName($tabData['class_name']);
            if ($tabId) {
                continue;
            }

            $newTab = new Tab();
            $newTab->active = $tabData['visible'];
            $newTab->enabled = true;
            $newTab->module = $module->name;
            $newTab->class_name = $tabData['class_name'];
            $newTab->id_parent = (int) Tab::getInstanceFromClassName($tabData['parent_class_name'])->id;
            foreach (\Language::getLanguages() as $lang) {
                $newTab->name[$lang['id_lang']] = $tabData['name'];
            }
            $newTab->icon = $tabData['icon'];
            $newTab->wording = $tabData['wording'];
            $newTab->wording_domain = $tabData['wording_domain'];
            $newTab->save();
        }

        return true;
    }


    /**
     * @param string $filepath
     *
     * @return bool
     */
    private function executeSqlFromFile(string $filepath): bool
    {
        if (!file_exists($filepath)) {
            return true;
        }

        $allowedCollations = ['utf8mb4_general_ci', 'utf8mb4_unicode_ci'];
        $databaseCollation = \Db::getInstance()->getValue('SELECT @@collation_database');
        $sqlLoader = new SqlLoader();
        $sqlLoader->setMetaData([
            'PREFIX_' => _DB_PREFIX_,
            'ENGINE_TYPE' => _MYSQL_ENGINE_,
            'COLLATION' => (empty($databaseCollation) || !in_array($databaseCollation, $allowedCollations)) ? '' : 'COLLATE ' . $databaseCollation,
        ]);

        return $sqlLoader->parseFile($filepath);
    }
}
