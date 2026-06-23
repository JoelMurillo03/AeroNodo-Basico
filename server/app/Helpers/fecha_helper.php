<?php

use CodeIgniter\I18n\Time;

if (!function_exists('fecha_local')) {
    function fecha_local($fechaUtc, $timezone = 'America/Hermosillo')
    {
        if (empty($fechaUtc)) {
            return null;
        }
        return Time::parse($fechaUtc, 'UTC')
            ->setTimezone($timezone)
            ->toDateTimeString();
    }
}