<div>
    {{-- Close your eyes. Count to one. That is how long forever feels. --}}
    
    @if($unreadCount > 0)
        <span class="ml-auto inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
            {{ $unreadCount }}
        </span>
    @endif
</div>
