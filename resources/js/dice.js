import DiceBox from "@3d-dice/dice-box";

let diceBox = null;

window.initDiceBox = (containerId = '#dice-container') => {
    // Calculate dice scale based on viewport size (aim for ~2.5% of screen height - half the previous size)
    const viewportHeight = window.innerHeight;
    const targetDiceSize = viewportHeight * 0.025; // 2.5% of viewport height (half of 5%)
    const baseScale = targetDiceSize / 10; // Adjust this divisor as needed
    
    diceBox = new DiceBox({
        id: 'dice-canvas',
        container: containerId,
        assetPath: "/assets/dice-box/",
        theme: 'default',
        scale: Math.max(baseScale, 5), // Reduced minimum scale from 15 to 8
        delay: 150,
        gravity: 1.5,
        friction: 0.8,
        restitution: 0.5,
        throwForce: 8,
        spinForce: 6,
        linearDamping: 0.1,
        settleTimeout: 4000,
        enableShadows: true,
        shadowTransparency: 0.7,
        lightIntensity: 1.2,
        startingHeight: 15
    });
    
    diceBox.init().then(() => {
        console.log('DiceBox initialized');
    });
    
    return diceBox;
}

window.rollDice = (notation) => {
    if (!diceBox) {
        console.error('DiceBox not initialized');
        return;
    }
    
    try {
        diceBox.roll(notation);
    } catch (e) {
        console.error('Error rolling dice:', e);
    }
}

// Store current roll context for the callback
let currentRollContext = null;

// DaggerHeart specific functions
window.rollDualityDice = (modifier = 0, rollType = 'stat', rollData = {}) => {
    if (!diceBox) {
        console.error('DiceBox not initialized');
        return;
    }
    
    // Store the roll context for the callback
    currentRollContext = {
        modifier,
        rollType,
        rollData
    };
    
    console.log('Roll started:', { rollType, modifier, rollData });
    
    try {
        // Roll Hope die (white) and Fear die (black) separately for different colors
        // Note: We'll roll them as separate d12s and handle the coloring via theme
        diceBox.roll([
            { qty: 1, sides: 12, themeColor: '#ffffff' }, // Hope die - white
            { qty: 1, sides: 12, themeColor: '#000000' }  // Fear die - black
        ]);
    } catch (e) {
        console.error('Error rolling duality dice:', e);
        // Fallback to simple notation if the advanced format fails
        try {
            diceBox.roll('2d12');
        } catch (fallbackError) {
            console.error('Fallback roll also failed:', fallbackError);
        }
    }
}

// Roll custom dice selection
window.rollCustomDice = (diceArray) => {
    if (!diceBox) {
        console.error('DiceBox not initialized');
        return;
    }
    
    if (!diceArray || diceArray.length === 0) {
        console.error('No dice to roll');
        return;
    }
    
    // Store the roll context for the callback
    currentRollContext = {
        modifier: 0,
        rollType: 'custom',
        rollData: { diceArray }
    };
    
    console.log('Rolling custom dice:', diceArray);
    
    try {
        // Convert the dice array to the format expected by dice-box
        const rollNotation = diceArray.map(die => {
            return {
                qty: 1,
                sides: die.sides,
                theme: die.theme || 'default'
            };
        });
        
        diceBox.roll(rollNotation);
    } catch (e) {
        console.error('Error rolling custom dice:', e);
        // Fallback to simple notation
        try {
            const simpleNotation = diceArray.map(die => `1d${die.sides}`).join(' + ');
            diceBox.roll(simpleNotation);
        } catch (fallbackError) {
            console.error('Fallback roll also failed:', fallbackError);
        }
    }
}

// Set up roll completion handler
window.setupDiceCallbacks = (onRollComplete = null) => {
    if (diceBox && onRollComplete) {
        diceBox.onRollComplete = (results) => {
            // Process DaggerHeart results
            console.log('Raw dice results:', results);
            
            // Get stored context
            const modifier = currentRollContext ? currentRollContext.modifier : 0;
            const rollType = currentRollContext ? currentRollContext.rollType : 'unknown';
            const rollData = currentRollContext ? currentRollContext.rollData : {};
            
            // Handle different roll types
            if (rollType === 'custom') {
                // Custom dice roll - show all results
                const total = results.reduce((sum, die) => sum + die.value, 0);
                const diceList = results.map(die => `d${die.sides}: ${die.value}`).join(', ');
                
                const rollResult = {
                    total,
                    results,
                    rollType,
                    rollData,
                    diceList
                };
                
                console.log('Custom dice roll completed:', rollResult);
                
                // Show result
                window.showDiceResult(rollResult);
                
                // Call external callback if provided
                if (onRollComplete) {
                    onRollComplete(rollResult);
                }
                
                // Clear dice after delay
                setTimeout(() => {
                    if (diceBox && diceBox.clear) {
                        diceBox.clear();
                    }
                }, 3000);
                
                return;
            }
            
            if (results.length === 2 && rollType !== 'custom') {
                // This is a duality dice roll - identify Hope vs Fear dice
                let hopeDie, fearDie;
                
                // Try to identify by theme color if available
                const whiteResult = results.find(r => r.themeColor === '#ffffff' || r.theme === 'white');
                const blackResult = results.find(r => r.themeColor === '#000000' || r.theme === 'black');
                
                if (whiteResult && blackResult) {
                    hopeDie = whiteResult.value;
                    fearDie = blackResult.value;
                } else {
                    // Fallback: first die is Hope, second is Fear
                    hopeDie = results[0].value;
                    fearDie = results[1].value;
                }
                
                // Get modifier and other data from stored context
                const modifier = currentRollContext ? currentRollContext.modifier : 0;
                const rollType = currentRollContext ? currentRollContext.rollType : 'unknown';
                const rollData = currentRollContext ? currentRollContext.rollData : {};
                
                const diceTotal = hopeDie + fearDie;
                const total = diceTotal + modifier;
                
                let outcome, outcomeType;
                
                if (hopeDie === fearDie) {
                    outcome = 'Critical Success';
                    outcomeType = 'critical';
                } else if (hopeDie > fearDie) {
                    outcome = 'With Hope';
                    outcomeType = 'hope';
                } else {
                    outcome = 'With Fear';
                    outcomeType = 'fear';
                }
                
                const rollResult = {
                    hopeDie,
                    fearDie,
                    total,
                    outcome,
                    outcomeType,
                    modifier,
                    rollType,
                    rollData,
                    timestamp: new Date().toISOString()
                };
                
                console.log('Duality Dice Roll:', rollResult);
                
                // Show result to user and clear dice after delay
                window.showDiceResult(rollResult);
                
                // Clear dice after showing result
                setTimeout(() => {
                    if (diceBox && diceBox.clear) {
                        diceBox.clear();
                    }
                }, 3000);
                
                // Clear the roll context
                currentRollContext = null;
                
                if (onRollComplete) {
                    onRollComplete(rollResult);
                }
            } else {
                // Regular dice roll
                const dice = results.map(die => die.value);
                const diceTotal = dice.reduce((sum, die) => sum, 0);
                
                // Get modifier from context if available
                const modifier = currentRollContext ? currentRollContext.modifier : 0;
                const total = diceTotal + modifier;
                
                const rollResult = { 
                    dice, 
                    total, 
                    results,
                    modifier: currentRollContext ? currentRollContext.modifier : undefined,
                    rollType: currentRollContext ? currentRollContext.rollType : undefined,
                    rollData: currentRollContext ? currentRollContext.rollData : undefined
                };
                
                // Show result and clear dice
                window.showDiceResult(rollResult);
                
                setTimeout(() => {
                    if (diceBox && diceBox.clear) {
                        diceBox.clear();
                    }
                }, 3000);
                
                // Clear the roll context
                currentRollContext = null;
                
                if (onRollComplete) {
                    onRollComplete(rollResult);
                }
            }
        };
    }
}

// Function to display dice results to the user
window.showDiceResult = (rollResult) => {
    // Create or update result display
    let resultDisplay = document.getElementById('dice-result-display');
    if (!resultDisplay) {
        resultDisplay = document.createElement('div');
        resultDisplay.id = 'dice-result-display';
        resultDisplay.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            border: 2px solid #4ade80;
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 600;
            z-index: 10000;
            min-width: 200px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            animation: slideInRight 0.3s ease-out;
        `;
        document.body.appendChild(resultDisplay);
        
        // Add CSS animation
        if (!document.getElementById('dice-result-styles')) {
            const style = document.createElement('style');
            style.id = 'dice-result-styles';
            style.textContent = `
                @keyframes slideInRight {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOutRight {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    // Format the result display
    let content = '';
    if (rollResult.rollType === 'custom') {
        // Custom dice roll result
        content = `
            <div style="text-align: center;">
                <div style="font-size: 28px; font-weight: bold; color: #ffffff; margin-bottom: 8px;">
                    ${rollResult.total}
                </div>
                <div style="font-size: 16px; color: #fbbf24; margin-bottom: 8px; font-weight: 600;">
                    Custom Roll
                </div>
                <div style="margin-bottom: 5px; font-size: 13px; color: #a0a0a0;">
                    ${rollResult.diceList}
                </div>
            </div>
        `;
    } else if (rollResult.hopeDie !== undefined && rollResult.fearDie !== undefined) {
        // DaggerHeart duality dice result
        const outcomeColor = rollResult.outcomeType === 'critical' ? '#fbbf24' : 
                           rollResult.outcomeType === 'hope' ? '#4ade80' : '#ef4444';
        
        const diceTotal = rollResult.hopeDie + rollResult.fearDie;
        const modifier = rollResult.modifier || 0;
        const hasModifier = modifier !== 0;
        
        // Build modifier display
        let modifierDisplay = '';
        let totalDisplay = '';
        
        if (hasModifier) {
            const modifierSign = modifier >= 0 ? '+' : '';
            modifierDisplay = `<div style="font-size: 12px; color: #a0a0a0; margin-bottom: 3px;">
                Dice: ${diceTotal} ${modifierSign}${modifier} modifier
            </div>`;
            totalDisplay = `Total: ${rollResult.total}`;
        } else {
            totalDisplay = `Total: ${rollResult.total}`;
        }
        
        // Add trait name if available
        let traitDisplay = '';
        if (rollResult.rollData && rollResult.rollData.traitName) {
            traitDisplay = `<div style="font-size: 12px; color: #a0a0a0; margin-bottom: 5px; text-transform: capitalize;">
                ${rollResult.rollData.traitName} Check
            </div>`;
        }
        
        content = `
            <div style="text-align: center;">
                ${traitDisplay}
                <div style="font-size: 28px; font-weight: bold; color: #ffffff; margin-bottom: 8px;">
                    ${rollResult.total}
                </div>
                <div style="font-size: 16px; color: ${outcomeColor}; margin-bottom: 8px; font-weight: 600;">
                    ${rollResult.outcome}
                </div>
                <div style="margin-bottom: 5px; font-size: 13px;">
                    <span style="color: #ffffff;">Hope: ${rollResult.hopeDie}</span> | 
                    <span style="color: #888888;">Fear: ${rollResult.fearDie}</span>
                </div>
                ${modifierDisplay}
            </div>
        `;
    } else {
        // Regular dice result
        const modifier = rollResult.modifier || 0;
        const hasModifier = modifier !== 0;
        
        let modifierDisplay = '';
        if (hasModifier) {
            const diceTotal = rollResult.dice ? rollResult.dice.reduce((sum, die) => sum + die, 0) : 0;
            const modifierSign = modifier >= 0 ? '+' : '';
            modifierDisplay = `<div style="font-size: 12px; color: #a0a0a0; margin-bottom: 3px;">
                Dice: ${diceTotal} ${modifierSign}${modifier} modifier
            </div>`;
        }
        
        content = `
            <div style="text-align: center;">
                <div style="margin-bottom: 5px;">
                    Dice: ${rollResult.dice ? rollResult.dice.join(', ') : 'N/A'}
                </div>
                ${modifierDisplay}
                <div style="font-size: 18px; color: #4ade80;">
                    Total: ${rollResult.total}
                </div>
            </div>
        `;
    }
    
    resultDisplay.innerHTML = content;
    
    // Auto-hide after 4 seconds
    setTimeout(() => {
        if (resultDisplay && resultDisplay.parentNode) {
            resultDisplay.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => {
                if (resultDisplay && resultDisplay.parentNode) {
                    resultDisplay.parentNode.removeChild(resultDisplay);
                }
            }, 300);
        }
    }, 4000);
}

// Character viewer specific functions
window.rollTraitCheck = (traitName, traitValue) => {
    console.log(`Rolling ${traitName} check with modifier ${traitValue}`);
    window.rollDualityDice(traitValue, 'stat', { traitName, traitValue });
}

window.rollWeaponAttack = (weaponKey) => {
    console.log(`Rolling weapon attack for ${weaponKey}`);
    window.rollDualityDice(0, 'attack', { weaponKey });
}

window.rollWeaponDamage = (weaponKey, isCritical = false) => {
    console.log(`Rolling weapon damage for ${weaponKey}`, isCritical ? '(Critical)' : '');
    // For now, roll 1d8 as example damage
    window.rollDice('1d8');
}
