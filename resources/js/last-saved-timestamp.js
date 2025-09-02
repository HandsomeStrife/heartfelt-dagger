/**
 * Last Saved Timestamp AlpineJS Component
 * Handles the "Last saved X minutes ago" display with automatic updates
 */
export function lastSavedTimestampComponent(initialTimestamp) {
    return {
        lastSavedTimestamp: initialTimestamp,
        timeAgoText: '',
        updateTimer: null,

        /**
         * Initialize the component
         */
        init() {
            this.updateTimeAgo();
            this.startTimer();
            
            // Listen for save events from Livewire to update timestamp
            this.$wire.$on('character-saved-timestamp', (data) => {
                this.lastSavedTimestamp = data.timestamp;
                this.updateTimeAgo();
                this.restartTimer();
            });
        },

        /**
         * Update the "time ago" text based on current timestamp
         */
        updateTimeAgo() {
            // Validate that we have a valid timestamp
            if (!this.lastSavedTimestamp || isNaN(this.lastSavedTimestamp)) {
                this.timeAgoText = '';
                return;
            }
            
            const now = Math.floor(Date.now() / 1000);
            const diffInSeconds = now - this.lastSavedTimestamp;
            
            // Handle negative values (future dates)
            if (diffInSeconds < 0) {
                this.timeAgoText = 'just now';
            } else if (diffInSeconds < 60) {
                this.timeAgoText = 'just now';
            } else if (diffInSeconds < 3600) {
                const minutes = Math.floor(diffInSeconds / 60);
                this.timeAgoText = minutes + (minutes === 1 ? ' minute ago' : ' minutes ago');
            } else if (diffInSeconds < 86400) {
                const hours = Math.floor(diffInSeconds / 3600);
                this.timeAgoText = hours + (hours === 1 ? ' hour ago' : ' hours ago');
            } else {
                const days = Math.floor(diffInSeconds / 86400);
                this.timeAgoText = days + (days === 1 ? ' day ago' : ' days ago');
            }
        },

        /**
         * Start the automatic update timer
         */
        startTimer() {
            this.updateTimer = setInterval(() => {
                this.updateTimeAgo();
            }, 30000); // Update every 30 seconds
        },

        /**
         * Restart the timer (used when timestamp updates)
         */
        restartTimer() {
            if (this.updateTimer) {
                clearInterval(this.updateTimer);
            }
            this.startTimer();
        },

        /**
         * Cleanup method (called when component is destroyed)
         */
        destroy() {
            if (this.updateTimer) {
                clearInterval(this.updateTimer);
                this.updateTimer = null;
            }
        }
    };
}
