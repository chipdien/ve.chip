<?php

// File này chứa tất cả các hàm truy vấn CSDL.
// Mỗi hàm đều nhận một tham số là đối tượng Medoo ($db).

function get_students_by_class(Medoo\Medoo $db, $class_id) {
    return $db->select('edu_student_class', ['[>]students' => ['student_id' => 'id']], ['edu_student_class.student_id (id)', 'students.full_name (name)', 'edu_student_class.status (status)', 'edu_student_class.tuition_fee (fee_rate)'], ['edu_student_class.class_id' => $class_id]);
}

function get_sessions_in_period(Medoo\Medoo $db, $class_id, $billing_period) {
    if (!$billing_period || !preg_match('/^\d{4}-\d{2}$/', $billing_period)) return [];
    $start_date = "{$billing_period}-01";
    $end_date = date("Y-m-t", strtotime($start_date));
    return $db->select('edu_sessions', ['[>]edu_teachers' => ['teacher_id' => 'id']], ['edu_sessions.id (id)', 'edu_sessions.date (date)', 'edu_teachers.name (teacher_name)', 'edu_sessions.duration (duration_hours)'], ['edu_sessions.class_id' => $class_id, 'edu_sessions.date[<>]' => [$start_date, $end_date], 'ORDER' => ['edu_sessions.date' => 'ASC']]);
}

function get_invoice_data(Medoo\Medoo $db, $student_id, $class_id, $billing_period) {
    $invoice = $db->get('fin_invoices', ['[>]fin_invoice_detail' => ['id' => 'invoice_id']], ['fin_invoices.id (invoice_id)', 'fin_invoices.content (content)', 'session_ids' => Medoo\Medoo::raw('GROUP_CONCAT(<fin_invoice_detail.model_id>)'), 'fin_invoices.paid_status', 'fin_invoices.discount_percentage'], ['fin_invoices.student_id' => $student_id, 'fin_invoices.model_id' => $class_id, 'fin_invoices.billing_period' => $billing_period, 'GROUP' => 'fin_invoices.id']);
    if ($invoice) {
        return ['invoice_id' => $invoice['invoice_id'], 'content' => $invoice['content'], 'paid_status' => $invoice['paid_status'] ?? 'draft', 'discount_percentage' => $invoice['discount_percentage'] ?? 0, 'checked_sessions' => $invoice['session_ids'] ? explode(',', $invoice['session_ids']) : []];
    }
    return ['invoice_id' => null, 'content' => 'Chưa tạo', 'paid_status' => 'draft', 'discount_percentage' => 0, 'checked_sessions' => []];
}

// ... Thêm các hàm khác như get_class_code, get_attendance_data_for_period, handle_invoice_update ...
// Ví dụ:
function get_class_code(Medoo\Medoo $db, $class_id) {
    return $db->get('edu_classes', ['code'], ['id' => $class_id]);
}

// ... (Các hàm khác sẽ được thêm vào đây)
