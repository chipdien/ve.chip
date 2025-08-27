<?php
// File: /teacher/components/class/documents_panel.php
// Giao diện cho tab tài liệu, được nhóm theo từng ca học.
?>
<div class="space-y-4">
    <!-- Hiển thị khi không có tài liệu nào -->
    <template x-if="!details.documents || Object.keys(details.documents).length === 0">
        <div class="bg-white p-6 rounded-xl shadow-sm text-center">
            <p class="text-gray-600">Lớp học này chưa có tài liệu nào.</p>
        </div>
    </template>

    <!-- Lặp qua từng ca học có tài liệu -->
    <template x-for="(sessionData, sessionId) in details.documents" :key="sessionId">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Header ca học -->
            <div class="p-4 bg-gray-50 border-b">
                <h3 class="font-bold text-gray-800" x-text="`Ca học ngày: ${formatDate(sessionData.session_date)}`"></h3>
            </div>

            <div class="p-4 space-y-4">
                <!-- Phần Tài liệu buổi học -->
                <template x-if="sessionData.documents && sessionData.documents.length > 0">
                    <div>
                        <h4 class="font-semibold text-gray-600 mb-2">Tài liệu buổi học</h4>
                        <ul class="divide-y divide-gray-200 border rounded-md">
                            <template x-for="doc in sessionData.documents" :key="doc.id">
                                <li class="p-3 flex items-center justify-between">
                                    <div class="flex items-center min-w-0">
                                        <span class="text-xl mr-3" x-text="getFileIcon(doc.type)"></span>
                                        <div class="min-w-0">
                                            <!-- truncate -->
                                            <p class="text-sm font-medium text-gray-800 " x-text="doc.name"></p>
                                            <p class="text-sm text-gray-500" x-text="formatFileSize(doc.size)"></p>
                                        </div>
                                    </div>
                                    <a :href="doc.url" target="_blank" class="ml-4 flex-shrink-0 text-green-600 hover:text-green-800 font-medium">Tải</a>
                                </li>
                            </template>
                        </ul>
                    </div>
                </template>

                <!-- Phần Bài tập về nhà -->
                <template x-if="sessionData.exercices && sessionData.exercices.length > 0">
                    <div>
                        <h4 class="font-semibold text-gray-600 mb-2">Bài tập về nhà</h4>
                        <ul class="divide-y divide-gray-200 border rounded-md">
                            <template x-for="doc in sessionData.exercices" :key="doc.id">
                                <li class="p-3 flex items-center justify-between">
                                    <div class="flex items-center min-w-0">
                                        <span class="text-xl mr-3" x-text="getFileIcon(doc.type)"></span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-800 truncate" x-text="doc.name"></p>
                                            <p class="text-sm text-gray-500" x-text="formatFileSize(doc.size)"></p>
                                        </div>
                                    </div>
                                    <a :href="doc.url" target="_blank" class="ml-4 flex-shrink-0 text-green-600 hover:text-green-800 font-medium">Tải</a>
                                </li>
                            </template>
                        </ul>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>
