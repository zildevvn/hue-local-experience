<?php
/**
 * External API - Filters
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="vm-external-orders__filters">

    <div class="vm-filter-field">
        <label for="vm-time-type">
            Time Period
        </label>

        <select id="vm-time-type" data-time-type>
            <option value="day">Day</option>
            <option value="week">Week</option>
            <option value="month">Month</option>
            <option value="year">Year</option>
        </select>
    </div>

    <div class="vm-filter-field" data-filter-day>
        <label for="vm-filter-date">
            Date
        </label>

        <input type="date" id="vm-filter-date" data-date>
    </div>

    <div class="vm-filter-field" data-filter-week hidden>
        <label for="vm-filter-week">
            Week
        </label>

        <input type="week" id="vm-filter-week" data-week>
    </div>

    <div class="vm-filter-field" data-filter-month hidden>
        <label for="vm-filter-month">
            Month
        </label>

        <input type="month" id="vm-filter-month" data-month>
    </div>

    <div class="vm-filter-field" data-filter-year hidden>
        <label for="vm-filter-year">
            Year
        </label>

        <input type="number" id="vm-filter-year" data-year min="2000" max="2100">
    </div>

    <div class="vm-filter-actions">
        <button type="button" data-call-api>
            Call API
        </button>
    </div>

</div>