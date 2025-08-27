<div class="bg-white p-4 rounded-xl shadow-sm">
    <ul class="space-y-2">
        <template x-for="session in details.sessions.items" :key="session.id">
            <a :href="`#session_details/${session.id}`" @click.prevent="navigate('session_details', session.id)" 
               class="block p-4 hover:bg-gray-50 transition-colors border border-green-400 bg-gray-100 rounded-lg">
                <li class=" ">
                    <b x-text="`${formatDate(session.date)}: `"></b>
                    <span x-text="session.content"></span>
                </li>
            </a>
        </template>
    </ul>
</div>
