<?php
// File: /teacher/components/organisms/bottom_nav.php
// Phiên bản cuối cùng, tuân thủ quy chuẩn dự án.
?>
<nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-20">
    <div class="container mx-auto flex justify-around">
        
        <!-- Dashboard -->
        <a href="#dashboard" @click.prevent="navigate('dashboard')" 
           :class="{'active': $store.app.routing.page === 'dashboard'}" 
           class="bottom-nav-link flex-1 flex flex-col items-center justify-center p-2 text-gray-500">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-xs">Dashboard</span>
        </a>

        <!-- Lớp học -->
        <a href="#classes" @click.prevent="navigate('classes')" 
           :class="{'active': $store.app.routing.page.startsWith('classes')}" 
           class="bottom-nav-link flex-1 flex flex-col items-center justify-center p-2 text-gray-500">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <span class="text-xs">Lớp học</span>
        </a>

        <!-- Lịch dạy -->
        <a href="#weekly_schedule" @click.prevent="navigate('weekly_schedule')" 
           :class="{'active': $store.app.routing.page === 'weekly_schedule'}" 
           class="bottom-nav-link flex-1 flex flex-col items-center justify-center p-2 text-gray-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="text-xs">Lịch dạy</span>
        </a>

        <!-- Ca học -->
        <a href="#session" @click.prevent="navigate('session')" 
           :class="{'active': $store.app.routing.page.startsWith('session')}" 
           class="bottom-nav-link flex-1 flex flex-col items-center justify-center p-2 text-gray-500">
             <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-xs">Ca học</span>
        </a>
    </div>
</nav>
