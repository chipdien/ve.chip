<?php
// File: src/Helpers/ViewHelper.php

namespace App\Helpers; // Đặt một namespace để định danh

class View
{
    /**
     * Hiển thị một giá trị một cách an toàn.
     */
    public static function display($value, string $default = 'N/A')
    {
        if (!empty($value)) {
            echo htmlspecialchars($value);
        } else {
            echo htmlspecialchars($default);
        }
    }
}