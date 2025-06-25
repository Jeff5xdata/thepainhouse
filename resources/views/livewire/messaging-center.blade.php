<div class="max-w-6xl mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Messaging Center</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Manage your messages and trainer requests</p>
    </div>

    @if(session('message'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md">
            <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('message') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
            <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Tab Navigation -->
    <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
        <nav class="-mb-px flex space-x-8">
            <button 
                wire:click="setActiveTab('messages')"
                class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 @if($activeTab === 'messages') border-blue-500 text-blue-600 dark:text-blue-400 @else border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 @endif"
            >
                Messages
            </button>
            <button 
                wire:click="setActiveTab('incoming-requests')"
                class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 @if($activeTab === 'incoming-requests') border-blue-500 text-blue-600 dark:text-blue-400 @else border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 @endif"
            >
                Incoming Requests
                @if(count($incomingTrainerRequests) > 0)
                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                        {{ count($incomingTrainerRequests) }}
                    </span>
                @endif
            </button>
            <button 
                wire:click="setActiveTab('outgoing-requests')"
                class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 @if($activeTab === 'outgoing-requests') border-blue-500 text-blue-600 dark:text-blue-400 @else border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 @endif"
            >
                My Requests
                @if(count($outgoingTrainerRequests) > 0)
                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        {{ count($outgoingTrainerRequests) }}
                    </span>
                @endif
            </button>
        </nav>
    </div>

    <!-- Messages Tab -->
    @if($activeTab === 'messages')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Message List -->
            <div class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Messages</h3>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($messages as $message)
                        <div 
                            wire:click="selectMessage({{ $message['id'] }})"
                            class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors duration-200 @if($selectedMessage && $selectedMessage['id'] === $message['id']) bg-blue-50 dark:bg-blue-900/20 @endif"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $message['sender']['name'] }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                        {{ $message['subject'] }}
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                        {{ \Carbon\Carbon::parse($message['created_at'])->diffForHumans() }}
                                    </p>
                                </div>
                                @if(!$message['is_read'])
                                    <div class="ml-2 flex-shrink-0">
                                        <div class="h-2 w-2 bg-blue-500 rounded-full"></div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                            <p>No messages yet</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Message Content -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow">
                @if($selectedMessage)
                    <div class="p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $selectedMessage['subject'] }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                From: {{ $selectedMessage['sender']['name'] }} ({{ $selectedMessage['sender']['email'] }})
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($selectedMessage['created_at'])->format('F j, Y \a\t g:i A') }}
                            </p>
                        </div>
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $selectedMessage['content'] }}</p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button 
                                wire:click="selectConversation({{ $selectedMessage['sender']['id'] }})"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200"
                            >
                                Reply
                            </button>
                        </div>
                    </div>
                @else
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                        <p>Select a message to view its content</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Trainer Requests Tab -->
    @if($activeTab === 'incoming-requests' || $activeTab === 'outgoing-requests')
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    @if($activeTab === 'incoming-requests')
                        Incoming Trainer Requests
                    @else
                        My Trainer Requests
                    @endif
                </h3>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @if($activeTab === 'incoming-requests')
                    @forelse($incomingTrainerRequests as $request)
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-white">
                                        Request from {{ $request['client']['name'] }}
                                    </h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $request['client']['email'] }}
                                    </p>
                                    @if($request['message'])
                                        <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-md">
                                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $request['message'] }}</p>
                                        </div>
                                    @endif
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                                        Requested {{ \Carbon\Carbon::parse($request['created_at'])->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="ml-4 flex space-x-2">
                                    <button 
                                        wire:click="replyToTrainerRequest('{{ $request['client']['email'] }}')"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md shadow-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200"
                                    >
                                        Reply
                                    </button>
                                    <button 
                                        wire:click="acceptTrainerRequest({{ $request['id'] }})"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-200"
                                    >
                                        Accept
                                    </button>
                                    <button 
                                        wire:click="declineTrainerRequest({{ $request['id'] }})"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md shadow-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200"
                                    >
                                        Decline
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                            <p>No incoming trainer requests</p>
                        </div>
                    @endforelse
                @else
                    @forelse($outgoingTrainerRequests as $request)
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-white">
                                        Request to {{ $request['trainer_email'] }}
                                    </h4>
                                    <div class="mt-2">
                                        @if($request['status'] === 'pending')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                Pending
                                            </span>
                                        @elseif($request['status'] === 'accepted')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                Approved
                                            </span>
                                        @elseif($request['status'] === 'declined')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                Rejected
                                            </span>
                                        @endif
                                    </div>
                                    @if($request['message'])
                                        <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-md">
                                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $request['message'] }}</p>
                                        </div>
                                    @endif
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                                        Requested {{ \Carbon\Carbon::parse($request['created_at'])->diffForHumans() }}
                                        @if($request['responded_at'])
                                            • Responded {{ \Carbon\Carbon::parse($request['responded_at'])->diffForHumans() }}
                                        @endif
                                    </p>
                                </div>
                                <div class="ml-4 flex space-x-2">
                                    <button 
                                        wire:click="replyToTrainerRequest('{{ $request['trainer_email'] }}')"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md shadow-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200"
                                    >
                                        Reply
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                            <p>No outgoing trainer requests</p>
                        </div>
                    @endforelse
                @endif
            </div>
        </div>
    @endif

    <!-- Conversation View -->
    @if($selectedConversation)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="$set('selectedConversation', null)">
            <div class="relative top-20 mx-auto p-5 border w-3/4 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800" wire:click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Conversation with {{ $selectedConversation['other_user']['name'] }}
                    </h3>
                    <button wire:click="$set('selectedConversation', null)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="max-h-96 overflow-y-auto space-y-4 mb-4">
                    @foreach($selectedConversation['messages'] as $message)
                        <div class="flex @if($message['sender_id'] === auth()->id()) justify-end @else justify-start @endif">
                            <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg @if($message['sender_id'] === auth()->id()) bg-blue-600 text-white @else bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white @endif">
                                <p class="text-sm">{{ $message['content'] }}</p>
                                <p class="text-xs @if($message['sender_id'] === auth()->id()) text-blue-100 @else text-gray-500 dark:text-gray-400 @endif mt-1">
                                    {{ \Carbon\Carbon::parse($message['created_at'])->format('g:i A') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <form wire:submit.prevent="sendMessage" class="space-y-4">
                    <input type="hidden" wire:model="newMessage.recipient_id" value="{{ $selectedConversation['other_user']['id'] }}">
                    <div>
                        <input 
                            type="text" 
                            wire:model="newMessage.subject"
                            placeholder="Subject"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
                        >
                    </div>
                    <div>
                        <textarea 
                            wire:model="newMessage.content"
                            rows="3"
                            placeholder="Type your message..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
                        ></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button 
                            type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition duration-200"
                        >
                            <span wire:loading.remove wire:target="sendMessage">Send</span>
                            <span wire:loading wire:target="sendMessage">Sending...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div> 