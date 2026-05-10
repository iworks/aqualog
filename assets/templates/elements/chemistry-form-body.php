<?php
/**
 * Chemistry Form Body Template
 *
 * @package    iWorks
 * @subpackage AquaLog
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026 Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @version    1.0.0
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="aqualog-chemistry-item-body-scale-buttons">
    <button type="button" class="aqualog-chemistry-item-body-scale-button" data-value="-{{data.step_big}}"><span class="dashicons dashicons-remove"></span>{{data.step_big}}</button>
    <button type="button" class="aqualog-chemistry-item-body-scale-button" data-value="-{{data.step_small}}"><span class="dashicons dashicons-minus"></span>{{data.step_small}}</button>
    <div class="aqualog-chemistry-item-body-scale-value-container">
        <input type="number" name="measurement" class="aqualog-chemistry-item-body-scale-value" value="">
        <span class="aqualog-chemistry-item-body-scale-value-unit">{{data.unit}}</span>
    </div>
    <button type="button" class="aqualog-chemistry-item-body-scale-button" data-value="{{data.step_small}}"><span class="dashicons dashicons-plus"></span>{{data.step_small}}</button>
    <button type="button" class="aqualog-chemistry-item-body-scale-button" data-value="{{data.step_big}}"><span class="dashicons dashicons-insert"></span>{{data.step_big}}</button>
</div>

<div class="aqualog-chemistry-item-body-scale">
    <div class="aqualog-chemistry-item-body-scale-char aqualog-chemistry-item-body-scale-slider" data-range-min="{{data.range[0]}}" data-range-max="{{data.range[1]}}" data-range-step="{{data.range_step}}" style="{{data.style}}"></div>
    <div class="aqualog-chemistry-item-body-scale-legend">
        <span class="legend-item--min">{{data.range[0]}} {{data.unit}}</span>
        <span class="legend-item--max">{{data.range[1]}} {{data.unit}}</span>
    </div>
</div>