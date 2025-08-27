<!-- File: /teacher/components/session/organisms/lesson_exercice_panel.php -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <!-- Header của section -->
    <div @click="toggleSection('lesson_exercice')" class="p-4 flex justify-between items-center cursor-pointer hover:bg-gray-50">
        <h2 class="font-bold text-lg text-gray-700">Bài tập</h2>
        <svg class="h-5 w-5 text-gray-400 transition-transform" :class="{'rotate-180': activeSection === 'lesson_exercice'}" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
    </div>
    
    <!-- Nội dung có thể đóng/mở -->
    <div x-show="activeSection === 'lesson_exercice'" x-collapse class="px-4 pb-4 border-t">
        <div class="pt-3 space-y-4">
            <!-- Textarea tự động lưu -->
            <div>
                <label for="lesson-exercice" class="block text-sm font-medium text-gray-700 mb-1">Bài tập Về nhà:</label>
                <div class="relative">
                    <!-- Vditor Editor Container -->
                    <div x-data="{
                        initVditor() {
                            const vditor = new Vditor(this.$el, {
                                height: 250,
                                toolbar: ['headings', 'bold', 'italic', 'strike', '|', 'list', 'ordered-list', 'check', '|', 'link', 'table', 'quote', '|', 'undo', 'redo', '|', 'math', 'graph', 'chart'],
                                cache: { enable: false },
                                mode: 'ir', // Chế độ xem trước tức thì
                                preview: {
                                    math: {
                                        engine: 'KaTeX', // Sử dụng KaTeX
                                        inlineDigit: true
                                    }
                                },
                                after: () => {
                                    const initialContent = details.exercice || ''; // || defaultComment;
                                    vditor.setValue(initialContent);
                                },
                                blur: (value) => {
                                    // Chỉ lưu nếu nội dung có thay đổi
                                    if (value !== details.exercice) {
                                        details.exercice = value;
                                        saveSessionData(details.session_id, 'exercice', value);
                                    }
                                }
                            });
                        }
                    }" x-init="initVditor()">
                    </div>

                    <!-- <textarea id="lesson-exercice"
                              x-model="details.exercice"
                              @blur="saveSessionData(details.session_id, 'exercice', details.exercice)"
                              class="w-full border border-gray-300 rounded-md p-2 focus:ring-green-500 focus:border-green-500" 
                              rows="5" 
                              placeholder="Nhập nội dung bài tập về nhà..."></textarea> -->
                    <!-- Thông báo trạng thái lưu -->
                    <div x-show="saveStatus" x-transition class="absolute bottom-2 right-2 text-xs px-2 py-0.5 rounded-full"
                         :class="{ 'bg-blue-100 text-blue-800': saveStatus === 'saving', 'bg-green-100 text-green-800': saveStatus === 'saved', 'bg-red-100 text-red-800': saveStatus === 'error' }">
                        <span x-show="saveStatus === 'saving'">Đang lưu...</span>
                        <span x-show="saveStatus === 'saved'">Đã lưu ✓</span>
                        <span x-show="saveStatus === 'error'">Lỗi!</span>
                    </div>
                </div>
            </div>

            <!-- Chức năng tải file -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phiếu Bài tập đính kèm:</label>
                <!-- Danh sách file đã tải lên -->
                <div class="space-y-1 mb-2">
                    <template x-for="file in details.exercice_files_array" :key="file.url">
                        <div class="flex items-center justify-between bg-gray-50 p-2 rounded-md">
                            <a :href="file.url" target="_blank" class="text-sm text-green-600 hover:underline truncate" x-text="file.name"></a>
                            <!-- Nút Xóa -->
                            <button @click.prevent.stop="deleteSessionFile(file.url, 'exercices')" class="ml-2 text-gray-400 hover:text-red-600 flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
                <!-- Input tải file -->
                <input type="file" @change="uploadSessionFile($event, 'exercices')" :disabled="isUploadingFile"
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"/>
                <!-- Thông báo đang tải -->
                <span x-show="isUploadingFile" class="text-sm text-gray-500 italic">Đang tải lên...</span>
            </div>
        </div>
    </div>
</div>
