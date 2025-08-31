@props(['modalId', 'title', 'inviteCode', 'inviteUrl', 'codeLabel' => 'Invite Code', 'linkLabel' => 'Invite Link'])

<!-- Invite Modal -->
<div id="{{ $modalId }}" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-slate-900 border border-slate-700 rounded-2xl p-8 max-w-lg w-full mx-4">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-outfit font-bold text-white">{{ $title }}</h3>
            <button onclick="hideModal('{{ $modalId }}')" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <div class="space-y-6">
            <!-- Invite Code -->
            <div>
                <label class="block text-slate-300 text-sm font-semibold mb-2">{{ $codeLabel }}</label>
                <div class="flex items-center space-x-3">
                    <code class="flex-1 bg-slate-800 text-emerald-400 px-4 py-3 rounded-lg font-mono text-lg tracking-wider border border-slate-600">
                        {{ $inviteCode }}
                    </code>
                    <button onclick="copyText('{{ $inviteCode }}', this)" class="bg-emerald-500 hover:bg-emerald-400 text-white px-4 py-3 rounded-lg font-semibold transition-colors">
                        Copy
                    </button>
                </div>
                <p class="text-slate-400 text-xs mt-2">Players can use this code to join</p>
            </div>

            <!-- Invite Link -->
            <div>
                <label class="block text-slate-300 text-sm font-semibold mb-2">{{ $linkLabel }}</label>
                <div class="flex items-center space-x-3">
                    <input type="text" readonly value="{{ $inviteUrl }}" 
                           class="flex-1 bg-slate-800 text-slate-300 px-4 py-3 rounded-lg border border-slate-600 text-sm">
                    <button onclick="copyText('{{ $inviteUrl }}', this)" class="bg-emerald-500 hover:bg-emerald-400 text-white px-4 py-3 rounded-lg font-semibold transition-colors">
                        Copy
                    </button>
                </div>
                <p class="text-slate-400 text-xs mt-2">Share this link for easy joining</p>
            </div>
        </div>
    </div>
</div>

<script>
    window.showModal = function(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    window.hideModal = function(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    window.copyText = function(text, button) {
        navigator.clipboard.writeText(text).then(function() {
            const originalText = button.textContent;
            button.textContent = 'Copied!';
            button.classList.remove('bg-emerald-500', 'hover:bg-emerald-400');
            button.classList.add('bg-green-500');
            
            setTimeout(() => {
                button.textContent = originalText;
                button.classList.remove('bg-green-500');
                button.classList.add('bg-emerald-500', 'hover:bg-emerald-400');
            }, 2000);
        }, function(err) {
            console.error('Could not copy text: ', err);
            button.textContent = 'Error';
            button.classList.add('bg-red-500');
            setTimeout(() => {
                button.textContent = 'Copy';
                button.classList.remove('bg-red-500');
            }, 2000);
        });
    }

    // Setup modal event listeners when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Close modal when clicking outside
        document.querySelectorAll('[id$="Modal"]').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    hideModal(this.id);
                }
            });
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('[id$="Modal"]').forEach(modal => {
                    if (!modal.classList.contains('hidden')) {
                        hideModal(modal.id);
                    }
                });
            }
        });
    });
</script>
