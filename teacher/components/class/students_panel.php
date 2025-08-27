<div class="bg-white p-4 rounded-xl shadow-sm">
    <ul class="divide-y divide-gray-200">
        <template x-for="student in details.students" :key="student.id">
            <!-- Mỗi học sinh là một link đến trang chi tiết -->
            <a :href="`#student_details/${student.id}`" @click.prevent="navigate('student_details', student.id)" class="block hover:bg-gray-50 transition-colors">
                <li class="p-4 flex items-center space-x-4">
                    <!-- Cột trái: Ảnh học sinh -->
                    <div class="flex-shrink-0">
                        <img :src="formatAvatarUrl(student.avatar,  student.full_name)" 
                             :alt="student.full_name" 
                             class="h-16 w-16 rounded-full object-cover">
                    </div>
                    <!-- Cột giữa: Họ tên và ngày sinh -->
                    <div class="flex-grow">
                        <p class="font-bold text-gray-800 text-lg" x-text="student.full_name"></p>
                        <p class="text-sm text-gray-500" x-text="formatDate(student.dob)"></p>
                    </div>
                    <!-- Cột phải: Nút mũi tên -->
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </li>
            </a>
        </template>
    </ul>
</div>
