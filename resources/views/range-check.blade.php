<x-layout>
    <div class="container mx-auto max-w-6xl px-4 py-8">
        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700 rounded-2xl p-6 shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-outfit font-bold text-white">DaggerHeart Range Viewer</h1>
                <div class="text-sm text-slate-400">Tip: the figure should move toward the horizon as distance increases.
                </div>
            </div>

            <!-- DaggerHeart Range Controls -->
            <div class="mb-6">
                <h3 class="text-lg font-outfit font-semibold text-slate-300 mb-4">Select Range</h3>
                <div class="flex flex-wrap gap-3">
                    <button data-range="melee" data-d="1"
                        class="range-btn bg-slate-700 hover:bg-slate-600 text-white font-medium py-3 px-6 rounded-lg border border-slate-600 transition-colors">
                        <div class="text-center">
                            <div class="font-outfit text-sm uppercase tracking-wide">Melee</div>
                            <div class="text-xs opacity-70">Touching (1ft)</div>
                        </div>
                    </button>

                    <button data-range="very-close" data-d="7"
                        class="range-btn bg-slate-700 hover:bg-slate-600 text-white font-medium py-3 px-6 rounded-lg border border-slate-600 transition-colors">
                        <div class="text-center">
                            <div class="font-outfit text-sm uppercase tracking-wide">Very Close</div>
                            <div class="text-xs opacity-70">5-10 ft</div>
                        </div>
                    </button>

                    <button data-range="close" data-d="20"
                        class="range-btn bg-slate-700 hover:bg-slate-600 text-white font-medium py-3 px-6 rounded-lg border border-slate-600 transition-colors">
                        <div class="text-center">
                            <div class="font-outfit text-sm uppercase tracking-wide">Close</div>
                            <div class="text-xs opacity-70">10-30 ft</div>
                        </div>
                    </button>

                    <button data-range="far" data-d="65"
                        class="range-btn bg-slate-700 hover:bg-slate-600 text-white font-medium py-3 px-6 rounded-lg border border-slate-600 transition-colors">
                        <div class="text-center">
                            <div class="font-outfit text-sm uppercase tracking-wide">Far</div>
                            <div class="text-xs opacity-70">30-100 ft</div>
                        </div>
                    </button>

                    <button data-range="very-far" data-d="200"
                        class="range-btn bg-slate-700 hover:bg-slate-600 text-white font-medium py-3 px-6 rounded-lg border border-slate-600 transition-colors">
                        <div class="text-center">
                            <div class="font-outfit text-sm uppercase tracking-wide">Very Far</div>
                            <div class="text-xs opacity-70">100-300 ft</div>
                        </div>
                    </button>

                    <button data-range="out-of-range" data-d="400"
                        class="range-btn bg-slate-700 hover:bg-slate-600 text-white font-medium py-3 px-6 rounded-lg border border-slate-600 transition-colors">
                        <div class="text-center">
                            <div class="font-outfit text-sm uppercase tracking-wide">Out of Range</div>
                            <div class="text-xs opacity-70">300+ ft</div>
                        </div>
                    </button>
                </div>

                <!-- Current distance display and download button -->
                <div class="flex justify-between items-center mt-4 pt-4 border-t border-slate-700">
                    <div class="text-slate-300">
                        Current Distance: <span id="dist_display" class="font-bold text-amber-400">20 ft</span>
                    </div>
                    <button id="save"
                        class="bg-slate-700 hover:bg-slate-600 text-white font-medium py-2 px-4 rounded-lg border border-slate-600 transition-colors">
                        Download PNG
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl overflow-hidden border border-slate-300 shadow-inner">
                <canvas id="cv" width="1400" height="700" class="w-full h-auto"></canvas>
            </div>
            <div class="text-xs text-slate-500 mt-3 text-center">
                Projection: y<sub>px</sub> = c<sub>y</sub> + f · h<sub>eye</sub> / d; height<sub>px</sub> = f ·
                h<sub>char</sub> / d
            </div>
        </div>
    </div>

    <script>
        const cv = document.getElementById('cv');
        const ctx = cv.getContext('2d');

        const $ = id => document.getElementById(id);

        // Default values for DaggerHeart
        const settings = {
            dist: 20, // Start with Close range
            ch: 6.6, // Character height as requested
            eh: 5.5, // Eye height  
            fov: 45, // Field of view
            w: 1400, // Canvas width
            h: 700 // Canvas height
        };

        let currentDistance = settings.dist;
        let targetDistance = settings.dist;
        let startDistance = settings.dist; // Store the distance when animation begins
        let animationStartTime = 0;
        let animationDuration = 600; // 600ms smooth animation

        // Load character image
        const characterImg = new Image();
        characterImg.src = '/img/character.png';
        let imageLoaded = false;

        characterImg.onload = function() {
            imageLoaded = true;
            draw(); // Redraw once image is loaded
        };

        function fpx(H, fov) {
            return (H / 2) / Math.tan((fov * Math.PI / 180) / 2);
        }

        function draw() {
            // Handle animation
            const now = performance.now();
            if (animationStartTime > 0 && currentDistance !== targetDistance) {
                const elapsed = now - animationStartTime;
                const progress = Math.min(elapsed / animationDuration, 1);

                // Smooth easing function (ease-out cubic)
                const easedProgress = 1 - Math.pow(1 - progress, 3);

                // Calculate animated distance using stored start distance
                currentDistance = startDistance + (targetDistance - startDistance) * easedProgress;

                if (progress >= 1) {
                    currentDistance = targetDistance;
                    animationStartTime = 0; // Stop animation
                }
            }

            const W = settings.w;
            const H = settings.h;
            if (cv.width !== W || cv.height !== H) {
                cv.width = W;
                cv.height = H;
            }

            const d = currentDistance; // distance in ft (possibly animated)
            const ch = settings.ch; // character height in ft
            const eh = settings.eh; // eye height in ft
            const fov = settings.fov; // field of view

            // Update distance display
            $('dist_display').textContent = Math.round(d * 10) / 10 + ' ft';

            const cx = W / 2,
                cy = H / 2; // principal point
            const f = fpx(H, fov);
            const horizonY = cy; // camera looking level

            // background gradient sky
            const skyGrad = ctx.createLinearGradient(0, 0, 0, horizonY);
            skyGrad.addColorStop(0, '#d2e1f5');
            skyGrad.addColorStop(1, '#eef3ff');
            ctx.fillStyle = skyGrad;
            ctx.fillRect(0, 0, W, horizonY);
            // ground
            ctx.fillStyle = '#e8e9f0';
            ctx.fillRect(0, horizonY, W, H - horizonY);

            // perspective grid: radial lines to vanishing point + selected ground distances
            ctx.strokeStyle = '#cfd3e4';
            ctx.lineWidth = 1;
            for (let x = 0; x <= W; x += 60) {
                ctx.beginPath();
                ctx.moveTo(x, H);
                ctx.lineTo(cx, horizonY);
                ctx.stroke();
            }

            const groundDistances = [5, 10, 20, 30, 50, 75, 100, 150, 200, 300, 400, 500, 600, 800];
            ctx.strokeStyle = '#d9dced';
            groundDistances.forEach(z => {
                const y = cy + f * (eh / z);
                if (y > horizonY && y < H) {
                    ctx.beginPath();
                    ctx.moveTo(0, y);
                    ctx.lineTo(W, y);
                    ctx.stroke();
                }
            });

            // compute feet position at distance d
            const feetY = cy + f * (eh / d);
            const charHpx = f * (ch / d);

            // label bubble
            drawLabel(ctx, 18, 18, `${d.toFixed(2)} ft`);

            // draw figure silhouette centered
            drawCharacter(ctx, cx, feetY, charHpx);

            // subtle vignette
            vignette(ctx, W, H);

            // Continue animation if needed
            if (animationStartTime > 0 && currentDistance !== targetDistance) {
                requestAnimationFrame(draw);
            }
        }

        function drawLabel(ctx, x, y, text) {
            ctx.save();
            ctx.font = 'bold 48px system-ui, -apple-system, Segoe UI, Roboto, Arial';
            const padX = 18,
                padY = 12;
            const m = ctx.measureText(text);
            const w = m.width + padX * 2,
                h = 56 + padY * 2;
            ctx.fillStyle = 'white';
            roundRect(ctx, x, y, w, h, 16);
            ctx.fill();
            ctx.strokeStyle = '#9aa2bd';
            ctx.lineWidth = 2;
            roundRect(ctx, x, y, w, h, 16);
            ctx.stroke();
            ctx.fillStyle = '#222533';
            ctx.fillText(text, x + padX, y + padY + 44);
            ctx.restore();
        }

        function roundRect(ctx, x, y, w, h, r) {
            ctx.beginPath();
            ctx.moveTo(x + r, y);
            ctx.arcTo(x + w, y, x + w, y + h, r);
            ctx.arcTo(x + w, y + h, x, y + h, r);
            ctx.arcTo(x, y + h, x, y, r);
            ctx.arcTo(x, y, x + w, y, r);
            ctx.closePath();
        }

        function drawCharacter(ctx, xCenter, feetY, h) {
            if (!imageLoaded) return; // Don't draw if image isn't loaded yet

            ctx.save();

            // Calculate image dimensions to maintain aspect ratio
            const imgAspect = characterImg.width / characterImg.height;
            const charWidth = h * imgAspect * 0.8; // Scale width based on height and aspect ratio
            const charHeight = h * 0.9; // Use most of the available height

            // Position the image 20% higher than the feet position
            const drawX = xCenter - charWidth / 2;
            const drawY = feetY - (charHeight * 1.2); // Move up by 20% of character height

            // Calculate where the character's feet actually are (bottom of the image)
            const actualFeetY = drawY + charHeight;

            // Draw shadow ellipse directly under the character - scale proportionally with character size
            const shadowWidth = Math.max(8, charWidth * 0.5); // Minimum 8px, scales with character
            const shadowHeight = Math.max(2, shadowWidth * 0.15); // Proportional to shadow width
            ctx.fillStyle = 'rgba(0,0,0,0.28)';
            ctx.beginPath();
            ctx.ellipse(xCenter, actualFeetY - 4, shadowWidth, shadowHeight, 0, 0, Math.PI * 2);
            ctx.fill();

            // Draw the character image
            ctx.drawImage(characterImg, drawX, drawY, charWidth, charHeight);

            ctx.restore();
        }

        function fillPoly(ctx, pts) {
            ctx.beginPath();
            pts.forEach((p, i) => i ? ctx.lineTo(p[0], p[1]) : ctx.moveTo(p[0], p[1]));
            ctx.closePath();
            ctx.fill();
        }

        function vignette(ctx, W, H) {
            const g = ctx.createRadialGradient(W / 2, H / 2, Math.min(W, H) * 0.2, W / 2, H / 2, Math.max(W, H) * 0.8);
            g.addColorStop(0, 'rgba(0,0,0,0)');
            g.addColorStop(1, 'rgba(0,0,0,0.18)');
            ctx.fillStyle = g;
            ctx.fillRect(0, 0, W, H);
        }

        // Hook up UI - Range buttons
        document.querySelectorAll('.range-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                // Remove active state from all buttons
                document.querySelectorAll('.range-btn').forEach(b => {
                    b.classList.remove('bg-amber-500', 'border-amber-400');
                    b.classList.add('bg-slate-700', 'border-slate-600');
                });

                // Add active state to clicked button
                btn.classList.remove('bg-slate-700', 'border-slate-600');
                btn.classList.add('bg-amber-500', 'border-amber-400');

                // Start animation to new distance
                const newDistance = parseFloat(btn.dataset.d);
                if (newDistance !== targetDistance) {
                    startDistance = currentDistance; // Store current position as start of animation
                    targetDistance = newDistance;
                    animationStartTime = performance.now();
                    draw(); // Start the animation
                }
            });
        });

        // Set initial active button (Close range)
        const closeBtn = document.querySelector('[data-range="close"]');
        closeBtn.classList.remove('bg-slate-700', 'border-slate-600');
        closeBtn.classList.add('bg-amber-500', 'border-amber-400');

        $('save').addEventListener('click', () => {
            const link = document.createElement('a');
            link.download = `daggerheart_range_${currentDistance}ft.png`;
            link.href = cv.toDataURL('image/png');
            link.click();
        });

        // initial draw
        requestAnimationFrame(draw);
    </script>
</x-layout>
