<?php

/**
 * Template part for displaying the calendar widget
 */
?>

<div class="calendar-widget">
    <button class="calendar-widget__trigger" aria-label="Toggle Calendar">
        <i class="fa-solid fa-calendar" aria-hidden="true"></i>
    </button>

    <div class="calendar-widget__content" style="display: none;">
        <!-- View Toggle -->
        <div class="view-toggle">
            <button class="view-toggle__btn view-toggle__btn--calendar active" aria-label="Calendar View">
                <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
            </button>
            <button class="view-toggle__btn view-toggle__btn--list" aria-label="List View">
                <i class="fa-solid fa-list" aria-hidden="true"></i>
            </button>
        </div>
        <!-- Calendar View -->
        <div class="calendar-view active">
            <div id="mythguard-calendar" class="mythguard-calendar">
                <!-- Flatpickr will be initialized here -->
            </div>

            <!-- Event Legend -->
            <div class="calendar-legend">
                <div class="legend-item">
                    <span class="legend-dot contract"></span>
                    <span>Contracts</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot event"></span>
                    <span>Gatherings</span>
                </div>
            </div>
        </div>

        <!-- List View Container -->
        <div class="calendar-list-view">
            <div class="calendar-list-view__contracts">
                <h3 class="calendar-list-view__header">
                    Contracts
                    <i class="fas fa-caret-up" aria-hidden="true"></i>
                </h3>
                <ul class="calendar-list-view__items"></ul>
            </div>
            <div class="calendar-list-view__gatherings">
                <h3 class="calendar-list-view__header">
                    Gatherings
                    <i class="fas fa-caret-up" aria-hidden="true"></i>
                </h3>
                <ul class="calendar-list-view__items"></ul>
            </div>
        </div>
    </div>
</div>