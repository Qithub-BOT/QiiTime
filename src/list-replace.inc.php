<?php
/**
 * テンプレートで置き換え可能な文字列.
 */

$list_replace  = [
    '%%ICON_HOUR%%'     => get_icon_hour(),           // 現在時刻を🕗のアイコンで表示
    '%%DATE_HOUR24%%'   => date('H', TIME_NOW),       // 現在の時間を 24 時間表示で表示
    '%%DATE_TODAY%%'    => date('Y/m/d', TIME_NOW),   // Y/m/d の書式で日付を表示
    '%%DATE_TAG_Ym%%'   => date('Y_m', TIME_NOW),     // Y_m の書式で年月を表示
    '%%DATE_TAG_Ymd%%'  => date('Y_m_d', TIME_NOW),   // Y_m_d の書式で年月日を表示
    '%%DATE_TAG_YmdH%%' => date('Y_m_d_H', TIME_NOW), // Y_m_d_H の書式で年月日と時間表示
];
