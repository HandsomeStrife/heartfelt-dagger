<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dice Box Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            margin: 0;
            padding: 20px;
            background: #1a1a1a;
            color: white;
            font-family: Arial, sans-serif;
        }
        #dice-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            z-index: 9999; /* Highest z-index to be on top */
        }
        
        #dice-container canvas {
            width: 100% !important;
            height: 100% !important;
            pointer-events: none !important; /* No pointer events on canvas */
            z-index: 9999 !important;
        }
        #controls {
            position: relative;
            z-index: 10000; /* Above dice container */
            padding: 20px;
            background: rgba(0,0,0,0.8);
            border-radius: 10px;
            margin: 20px;
        }
        button {
            padding: 10px 20px;
            margin: 5px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #45a049;
        }
        #log {
            background: #333;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            max-height: 300px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div id="controls">
        <h1>Dice Box Test</h1>
        <button onclick="testRoll()">Roll 2d12</button>
        <button onclick="testSingleDie()">Roll 1d6</button>
        <button onclick="clearLog()">Clear Log</button>
        <div id="log"></div>
    </div>
    
    <div id="dice-container" wire:ignore></div>

    <script>
        function log(message) {
            const logDiv = document.getElementById('log');
            const timestamp = new Date().toLocaleTimeString();
            logDiv.innerHTML += `[${timestamp}] ${message}<br>`;
            logDiv.scrollTop = logDiv.scrollHeight;
            console.log(message);
        }
        
        window.testRoll = function() {
            log('Rolling 2d12...');
            if (typeof window.rollDualityDice === 'function') {
                window.rollDualityDice(0, 'test', { test: true });
            } else {
                log('ERROR: rollDualityDice function not available');
            }
        };
        
        window.testSingleDie = function() {
            log('Rolling 1d6...');
            if (typeof window.rollDice === 'function') {
                window.rollDice('1d6');
            } else {
                log('ERROR: rollDice function not available');
            }
        };
        
        window.clearLog = function() {
            document.getElementById('log').innerHTML = '';
        };
        
        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const initTest = () => {
                if (typeof window.initDiceBox !== 'undefined') {
                    log('Initializing DiceBox...');
                    log(`Viewport: ${window.innerWidth}x${window.innerHeight}`);
                    
                    let diceBox = window.initDiceBox('#dice-container');
                    
                    if (typeof window.setupDiceCallbacks === 'function') {
                        window.setupDiceCallbacks((rollResult) => {
                            log(`Roll complete: ${JSON.stringify(rollResult)}`);
                        });
                    }
                    
                    // Verify canvas creation
                    setTimeout(() => {
                        const canvas = document.querySelector('#dice-container canvas');
                        if (canvas) {
                            log(`Canvas found: ${canvas.width}x${canvas.height}`);
                        } else {
                            log('No canvas found after initialization');
                        }
                    }, 1000);
                    
                    log('DiceBox initialized successfully!');
                } else {
                    log('Waiting for dice functions to load...');
                    setTimeout(initTest, 100);
                }
            };
            
            initTest();
        });
    </script>
    @livewireScriptConfig
</body>
</html>
