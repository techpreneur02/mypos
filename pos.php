<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: B2C Quick POS
Description: Commercial B2C Point of Sale module for fast retail billing and reporting
Version: 1.0.0-beta
Author: Sherwin Armas
Requires at least: 2.9.0
*/

define('POS_MODULE_NAME', 'pos');

// 1. A completely empty activation hook that does absolutely nothing
register_activation_hook(POS_MODULE_NAME, 'pos_module_activation_hook');
function pos_module_activation_hook()
{
    // Left completely empty to guarantee no database or server crashes
}

// 2. The simple sidebar menu item
hooks()->add_action('admin_init', 'pos_module_init_menu_items');
function pos_module_init_menu_items()
{
    $CI = &get_instance();

    register_staff_capabilities(
        'pos',
        [
            'capabilities' => [
                'cashiering'         => 'POS Cashiering',
                'cashier_report'     => 'POS Cashier Report (All Staff)',
                'cashier_report_own' => 'POS Cashier Report (Own)',
                'z_report'           => 'POS Z Report (All Staff)',
                'z_report_own'       => 'POS Z Report (Own)',
                'rewards_settings'   => 'POS Rewards Settings',
            ],
        ],
        'Point of Sale'
    );

    $canCashier = is_admin() || staff_can('cashiering', 'pos');
    $canCashierReport = is_admin() || staff_can('cashier_report', 'pos') || staff_can('cashier_report_own', 'pos');
    $canZReport = is_admin() || staff_can('z_report', 'pos') || staff_can('z_report_own', 'pos');
    $canRewardsSettings = is_admin() || staff_can('rewards_settings', 'pos');

    if (!$canCashier && !$canCashierReport && !$canZReport && !$canRewardsSettings) {
        return;
    }

    $CI->app_menu->add_sidebar_menu_item('pos-menu-item', [
        'name'     => 'POS Quick Sell',
        'href'     => $canCashier
            ? admin_url('pos/pos_controller')
            : ($canCashierReport
                ? admin_url('pos/pos_controller/cashier_report')
                : ($canZReport
                    ? admin_url('pos/pos_controller/z_report')
                    : admin_url('pos/pos_controller/rewards_settings'))),
        'icon'     => 'fa fa-th-large',
        'position' => 5,
    ]);

    if ($canCashier) {
        $CI->app_menu->add_sidebar_children_item('pos-menu-item', [
            'slug'     => 'pos-cashiering',
            'name'     => 'Cashiering',
            'href'     => admin_url('pos/pos_controller'),
            'position' => 1,
        ]);
    }

    if ($canCashierReport) {
        $CI->app_menu->add_sidebar_children_item('pos-menu-item', [
            'slug'     => 'pos-cashier-report',
            'name'     => 'Cashier Report',
            'href'     => admin_url('pos/pos_controller/cashier_report'),
            'position' => 2,
        ]);
    }

    if ($canZReport) {
        $CI->app_menu->add_sidebar_children_item('pos-menu-item', [
            'slug'     => 'pos-z-report',
            'name'     => 'Z Report',
            'href'     => admin_url('pos/pos_controller/z_report'),
            'position' => 3,
        ]);
    }

    if ($canRewardsSettings) {
        $CI->app_menu->add_sidebar_children_item('pos-menu-item', [
            'slug'     => 'pos-rewards-settings',
            'name'     => 'Rewards Settings',
            'href'     => admin_url('pos/pos_controller/rewards_settings'),
            'position' => 4,
        ]);

        $CI->app_menu->add_sidebar_children_item('pos-menu-item', [
            'slug'     => 'pos-rewards-history',
            'name'     => 'Rewards History',
            'href'     => admin_url('pos/pos_controller/rewards_history'),
            'position' => 5,
        ]);
    }
}
