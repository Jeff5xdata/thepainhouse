<div x-data="{ 
    open: false,
    step: 'email', // 'email' or 'link'
    emails: [''],
    isLoading: false,
    shareLink: '',
    message: '',
    copied: false,
    workoutPlanId: null,
    addEmail() {
        if (this.emails.length < 5) {
            this.emails.push('');
        }
    },
    removeEmail(index) {
        if (this.emails.length > 1) {
            this.emails.splice(index, 1);
        }
    },
    async generateLink() {
        this.isLoading = true;
        try {
            const response = await fetch(`/workout-plans/${this.workoutPlanId}/share`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                }
            });

            const data = await response.json();
            if (response.ok) {
                this.shareLink = data.share_link;
            } else {
                this.message = data.message || 'An error occurred while generating the link';
            }
        } catch (error) {
            console.error('Share error:', error);
            this.message = 'An error occurred. Please try again.';
        }
        this.isLoading = false;
    },
    async share() {
        if (!this.emails.some(email => email.trim())) {
            this.message = 'Please enter at least one email address';
            return;
        }
        
        if (!this.shareLink) {
            await this.generateLink();
            if (!this.shareLink) {
                return; // Stop if share link generation failed
            }
        }
        
        this.isLoading = true;
        this.message = '';
        try {
            console.log('Sending share request for workout plan:', this.workoutPlanId);
            console.log('Emails:', this.emails.filter(email => email.trim()));
            
            const response = await fetch(`/workout-plans/${this.workoutPlanId}/share-emails`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify({
                    emails: this.emails.filter(email => email.trim()),
                    share_link: this.shareLink
                })
            });

            console.log('Share response status:', response.status);
            const data = await response.json();
            console.log('Share response data:', data);

            if (response.ok) {
                this.step = 'link';
                this.message = 'Emails sent successfully!';
            } else {
                this.message = data.message || 'An error occurred while sharing';
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).flat();
                    if (errorMessages.length > 0) {
                        this.message = errorMessages.join('\n');
                    }
                }
            }
        } catch (error) {
            console.error('Share error:', error);
            this.message = 'An error occurred while sharing. Please check your internet connection and try again.';
        }
        this.isLoading = false;
    },
    async copyLink() {
        if (!this.shareLink) {
            await this.generateLink();
        }
        if (this.shareLink) {
            await navigator.clipboard.writeText(this.shareLink);
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        }
    },
    reset() {
        this.step = 'email';
        this.emails = [''];
        this.shareLink = '';
        this.message = '';
        this.isLoading = false;
        this.copied = false;
    }
}" x-init="workoutPlanId = {{ $workoutPlan->id }}; generateLink()" @keydown.escape="open = false">
    <!-- Trigger Button -->
    <button @click="open = true" class="inline-flex items-center px-4 py-2 bg-blue-600 dark:bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 dark:hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
        </svg>
        Share Plan
    </button>

    <!-- Modal -->
    <div x-show="open" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50" 
         style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0">
            <!-- Primary blur layer -->
            <div class="absolute inset-0 backdrop-blur-[100px] bg-white/95 dark:bg-white/70"></div>
            <!-- Secondary blur layer for depth -->
            <div class="absolute inset-0 backdrop-blur-3xl bg-white/90 dark:bg-white/60"></div>
            <!-- Color overlay -->
            <div class="absolute inset-0 bg-white/[0.98] dark:bg-white/55"></div>
        </div>
        
        <!-- Modal Content -->
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     @click.away="open = false"
                     class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-md mx-auto transform transition-all 
                            ring-1 ring-black/5 dark:ring-white/10
                            shadow-[0_0_30px_-5px_rgba(0,0,0,0.2),0_0_10px_-2px_rgba(0,0,0,0.1),inset_0_0_5px_rgba(0,0,0,0.05),0_4px_6px_-1px_rgba(0,0,0,0.1),0_2px_4px_-1px_rgba(0,0,0,0.06)] 
                            dark:shadow-[0_0_30px_-5px_rgba(255,255,255,0.1),0_0_10px_-2px_rgba(255,255,255,0.05),inset_0_0_5px_rgba(255,255,255,0.02),0_4px_6px_-1px_rgba(0,0,0,0.2),0_2px_4px_-1px_rgba(0,0,0,0.16)]">
                    
                    <!-- Header -->
                    <div class="flex justify-between items-center p-4 border-b border-black/5 dark:border-white/10">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            <span x-show="step === 'email'">Share Workout Plan</span>
                            <span x-show="step === 'link'">Share Link Created</span>
                        </h3>
                        <button @click="open = false; reset()" class="text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400 transition-colors duration-200">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-4">
                        <!-- Email Step -->
                        <div x-show="step === 'email'" class="space-y-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Enter email addresses to share this workout plan with others.
                            </p>
                            
                            <!-- Share Link Section -->
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Share Link
                                    </label>
                                    <div class="flex gap-2">
                                        <input type="text" 
                                               x-model="shareLink" 
                                               readonly 
                                               class="flex-1 px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400">
                                        <button @click="copyLink"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 dark:bg-blue-500 hover:bg-blue-700 dark:hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                            <template x-if="!copied">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                                </svg>
                                            </template>
                                            <template x-if="copied">
                                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </template>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <template x-for="(email, index) in emails" :key="index">
                                    <div class="flex gap-2">
                                        <div class="relative flex-1">
                                            <input type="email" 
                                                x-model="emails[index]" 
                                                :placeholder="'Enter email address ' + (index + 1)"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent transition duration-200"
                                                :class="{'border-red-300 dark:border-red-500': message && !email.trim()}">
                                        </div>
                                        <button @click="removeEmail(index)" 
                                                x-show="emails.length > 1"
                                                class="flex-shrink-0 text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors duration-200">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <button @click="addEmail" 
                                    x-show="emails.length < 5"
                                    class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors duration-200">
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Add another email
                            </button>

                            <div x-show="message" 
                                 x-text="message"
                                 class="mt-4 p-3 rounded-md text-sm"
                                 :class="{
                                     'bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-200': !shareLink,
                                     'bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-200': shareLink
                                 }">
                            </div>
                        </div>

                        <!-- Link Step -->
                        <div x-show="step === 'link'" class="space-y-4">
                            <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                            Workout plan shared successfully!
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Share Link
                                </label>
                                <div class="flex gap-2">
                                    <input type="text" 
                                           x-model="shareLink" 
                                           readonly 
                                           class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400">
                                    <button @click="copyLink"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 dark:bg-blue-500 hover:bg-blue-700 dark:hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                        <template x-if="!copied">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                            </svg>
                                        </template>
                                        <template x-if="copied">
                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </template>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex justify-end space-x-3">
                            <button @click="open = false; reset()" 
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-500 dark:hover:text-gray-400 transition-colors duration-200">
                                Cancel
                            </button>
                            <button x-show="step === 'email'"
                                    @click="share" 
                                    :disabled="isLoading || !emails.some(email => email.trim())"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 dark:bg-blue-500 hover:bg-blue-700 dark:hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                                <svg x-show="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="isLoading ? 'Sharing...' : 'Share'"></span>
                            </button>
                            <button x-show="step === 'link'"
                                    @click="step = 'email'"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 dark:bg-blue-500 hover:bg-blue-700 dark:hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                Share with More
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 