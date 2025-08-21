<x-layout>
    <livewire:character-builder :character-key="$character_key ?? null" />

    <script>
        // Store character key in localStorage when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const character_key = '{{ $character_key }}';
            
            if (character_key) {
                storeCharacterKey(character_key);
            }
        });

        function storeCharacterKey(character_key) {
            try {
                // Get existing character keys from localStorage
                let storedKeys = localStorage.getItem('daggerheart_characters');
                let character_keys = storedKeys ? JSON.parse(storedKeys) : [];
                
                // Add the new character key if it doesn't already exist
                if (!character_keys.includes(character_key)) {
                    character_keys.push(character_key);
                    localStorage.setItem('daggerheart_characters', JSON.stringify(character_keys));
                    console.log('Character key stored:', character_key);
                }
            } catch (error) {
                console.error('Error storing character key:', error);
            }
        }

        // Listen for character updates from Livewire
        window.addEventListener('character-updated', function(event) {
            const character_key = '{{ $character_key }}';
            if (character_key) {
                storeCharacterKey(character_key);
            }
        });
    </script>
</x-layout>