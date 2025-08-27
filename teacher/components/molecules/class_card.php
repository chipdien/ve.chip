<?php
// File: /teacher/components/molecules/class_card.php
// Component "Molecule"
// Nhận vào biến $class
?>
<div class="bg-white p-4 rounded-xl shadow-sm cursor-pointer hover:shadow-md transition-shadow">
    <p class="font-bold text-red-600"><?= htmlspecialchars($class['code']) ?></p>
    <p class="text-sm text-gray-500">Chuyên cần: 
        <span class="font-medium"><?= htmlspecialchars($class['attendance_rate']) ?>%</span>
    </p>
</div>
