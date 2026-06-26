<?php
/**
 * News Ticker: module script data trait.
 *
 * @package DiviTorqueLite\Modules\NewsTicker
 * @since   4.6.0
 */

namespace DiviTorqueLite\Modules\NewsTicker\NewsTickerTrait;

if (!defined('ABSPATH')) {
    exit;
}

trait ModuleScriptDataTrait
{
    /**
     * Output script data for the module.
     *
     * @param array $args Script data args.
     *
     * @return void
     */
    public static function module_script_data($args)
    {
        $elements = $args['elements'] ?? null;
        if (!$elements) {
            return;
        }

        $elements->script_data(
            [
                'attrName' => 'module',
            ]
        );
    }
}
