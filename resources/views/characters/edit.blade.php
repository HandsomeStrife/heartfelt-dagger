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
        
        // View character function for the character builder
        async function viewCharacter() {
            const character_key = '{{ $character_key }}';
            if (!character_key) {
                console.error('No character key available');
                return;
            }
            
            try {
                // Get character data to find the public_key
                const response = await fetch(`/api/character/${character_key}`);
                if (response.ok) {
                    const character = await response.json();
                    if (character.public_key) {
                        window.location.href = `/character/${character.public_key}`;
                    } else {
                        console.error('No public_key found for character');
                        alert('Unable to view character. Please try again.');
                    }
                } else {
                    console.error('Failed to load character data');
                    alert('Unable to view character. Please try again.');
                }
            } catch (error) {
                console.error('Error viewing character:', error);
                alert('An error occurred while viewing the character. Please try again.');
            }
        }

        // View character function for opening in new window
        async function viewCharacterInNewWindow() {
            const character_key = '{{ $character_key }}';
            if (!character_key) {
                console.error('No character key available');
                return;
            }
            
            try {
                // Get character data to find the public_key
                const response = await fetch(`/api/character/${character_key}`);
                if (response.ok) {
                    const character = await response.json();
                    if (character.public_key) {
                        window.open(`/character/${character.public_key}`, '_blank');
                    } else {
                        console.error('No public_key found for character');
                        alert('Unable to view character. Please try again.');
                    }
                } else {
                    console.error('Failed to load character data');
                    alert('Unable to view character. Please try again.');
                }
            } catch (error) {
                console.error('Error viewing character:', error);
                alert('An error occurred while viewing the character. Please try again.');
            }
        }
    </script>
</x-layout>