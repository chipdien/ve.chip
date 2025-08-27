<?php 
//
?>
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="p-4 flex justify-between items-center cursor-pointer hover:bg-gray-50">
        <h2 class="font-bold text-lg text-gray-700">Điểm danh & Tương tác nhanh</h2>
        <!-- <svg class="h-5 w-5 text-gray-400 transition-transform" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg> -->
    </div>
    <div class="px-4 pb-4 border-t">
        <div class="pt-3 grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
            <template x-for="student in students" :key="student.id">
                <div @click="cycleAttendance(student)" 
                     class="relative border-2 rounded-lg p-2 text-center cursor-pointer flex flex-col items-center justify-center aspect-square"
                     :class="getAttendanceClass(student.attendance_status)">
                    <!-- Ký tự điểm danh -->
                    <span class="absolute top-1 left-1 text-md font-bold" 
                          :class="{ 'text-green-600': student.attendance_status === 'present', 'text-yellow-600': student.attendance_status === 'n_absence', 'text-red-600': student.attendance_status === 'absence' }"
                          x-text="getAttendanceChar(student.attendance_status)"></span>
                    <!-- Icon feedback -->
                    <button @click.stop="openFeedbackPopup(student)" class="absolute top-1 right-1 text-gray-400 hover:text-green-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                    </button>
                    <!-- Avatar -->
                    <img :src="formatAvatarUrl(student.avatar,  student.full_name)" 
                         :alt="student.full_name" class="w-20 h-20 rounded-full mb-1 object-cover"> <!-- 16 -->
                    <!-- Tên học sinh -->
                    <p class="text-xs font-bold text-gray-700 leading-tight" x-text="student.full_name"></p>
                    <p class="text-xs font-medium text-gray-500 leading-tight" x-text="formatDate(student.dob)"></p>
                </div>
            </template>
        </div>

        <!-- Popup Feedback -->
        <div x-show="isFeedbackPopupOpen" @click.self="isFeedbackPopupOpen = false" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div x-show="isFeedbackPopupOpen" class="bg-white rounded-xl shadow-xl w-full max-w-md" x-transition>
                <div class="p-4 border-b">
                    <h3 class="font-bold text-lg" x-text="`Nhận xét cho ${selectedStudentForFeedback?.full_name}`"></h3>
                </div>
                <div class="p-4 max-h-[60vh] overflow-y-auto">
                    <!-- Tương tác nhanh -->
                    <div class="mb-4">
                        <h4 class="font-semibold mb-2">Tương tác nhanh</h4>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="option in feedbackOptions" :key="option.tag">
                                <button @click="addInteraction(option)" class="text-sm font-medium px-3 py-1.5 rounded-full flex items-center gap-2"
                                        :class="{ 'bg-green-100 text-green-800': option.type === 'positive', 'bg-yellow-100 text-yellow-800': option.type === 'warning', 'bg-red-100 text-red-800': option.type === 'negative' }">
                                    <span x-text="option.icon"></span>
                                    <span x-text="option.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                    <!-- Lịch sử tương tác -->
                    <div>
                        <h4 class="font-semibold mb-2">Lịch sử tương tác trong buổi</h4>
                        <div class="space-y-2">
                            <template x-for="interaction in selectedStudentForFeedback?.interactions" :key="interaction.id">
                                <div class="flex justify-between items-center bg-gray-50 p-2 rounded-md">
                                    <span x-text="`${interaction.icon} ${interaction.label}`"></span>
                                    <button @click="deleteInteraction(interaction.id, selectedStudentForFeedback)" class="text-gray-400 hover:text-red-500">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                    </button>
                                </div>
                            </template>
                            <p x-show="!selectedStudentForFeedback?.interactions.length" class="text-sm text-gray-500 italic">Chưa có tương tác nào.</p>
                        </div>
                    </div>
                </div>
                <div class="p-4 bg-gray-50 border-t rounded-b-xl">
                    <button @click="isFeedbackPopupOpen = false" class="w-full bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-lg hover:bg-gray-300">Xong</button>
                </div>
            </div>
        </div>


        <!-- <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
            <template x-for="student in students" :key="student.id">
                <div class="relative">
                    <div class="student-card aspect-square border-4 rounded-xl flex flex-col items-center justify-center p-1 cursor-pointer"
                            :class="{ 
                                'present': student.status === 'present', 
                                'absence': student.status === 'absence',
                                'n_absence': student.status === 'n_absence',
                                'border-gray-200': student.status === 'holding' 
                            }"
                            @click="toggleAttendance(student.id)">
                        <img :src="student.avatar" :alt="student.name" class="w-12 h-12 sm:w-14 sm:h-14 rounded-full mb-1 object-cover">
                        <p class="text-center text-xs sm:text-sm font-semibold text-gray-800" x-text="student.name"></p>
                    </div> -->
                    <!-- Attendance Symbol -->
                    <!-- <div x-show="student.status !== 'holding'"
                            class="absolute top-1 left-1 w-6 h-6 rounded-full flex items-center justify-center text-white font-bold text-sm pointer-events-none"
                            :class="{
                                'bg-green-500': student.status === 'present',
                                'bg-red-500': student.status === 'absence',
                                'bg-yellow-400': student.status === 'n_absence'
                            }"
                            x-cloak>
                        <span x-text="getAttendanceSymbol(student.status)"></span>
                    </div> -->
                    <!-- Quick Feedback Button -->
                    <!-- <button @click="openFeedback(student.id)" class="absolute top-1 right-1 bg-indigo-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-indigo-600">
                        <span x-text="(student.feedbacks || []).length || '💬'"></span>
                    </button>
                </div>
            </template>
        </div> -->
    </div>
</div>


