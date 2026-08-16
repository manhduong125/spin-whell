/**
 * Lightweight, high-performance Canvas Confetti
 */
(function (root) {
    if (typeof root.confetti === 'function') {
        return;
    }

    var defaultColors = ['#ff4d00', '#f6fa00', '#22c55e', '#3b82f6', '#ec4899', '#a855f7', '#06b6d4'];

    function randomRange(min, max) {
        return Math.random() * (max - min) + min;
    }

    function ConfettiParticle(x, y, color) {
        this.x = x;
        this.y = y;
        this.size = randomRange(6, 12);
        this.color = color;
        this.vx = randomRange(-6, 6);
        this.vy = randomRange(-12, -4);
        this.gravity = 0.35;
        this.rotation = randomRange(0, 360);
        this.rotationSpeed = randomRange(-8, 8);
        this.opacity = 1;
        this.decay = randomRange(0.008, 0.018);
        this.wobble = 0;
        this.wobbleSpeed = randomRange(0.05, 0.12);
    }

    ConfettiParticle.prototype.update = function () {
        this.x += this.vx + Math.sin(this.wobble) * 1.5;
        this.y += this.vy;
        this.vy += this.gravity;
        this.rotation += this.rotationSpeed;
        this.opacity -= this.decay;
        this.wobble += this.wobbleSpeed;
    };

    ConfettiParticle.prototype.draw = function (ctx) {
        if (this.opacity <= 0) return;
        ctx.save();
        ctx.translate(this.x, this.y);
        ctx.rotate((this.rotation * Math.PI) / 180);
        ctx.globalAlpha = Math.max(0, this.opacity);
        ctx.fillStyle = this.color;
        ctx.fillRect(-this.size / 2, -this.size / 2, this.size, this.size * 0.6);
        ctx.restore();
    };

    function runConfetti(options) {
        options = options || {};
        var count = options.particleCount || 100;
        var colors = options.colors || defaultColors;

        var canvas = document.createElement('canvas');
        canvas.style.position = 'fixed';
        canvas.style.top = '0';
        canvas.style.left = '0';
        canvas.style.width = '100vw';
        canvas.style.height = '100vh';
        canvas.style.pointerEvents = 'none';
        canvas.style.zIndex = '999999';

        var width = (canvas.width = window.innerWidth);
        var height = (canvas.height = window.innerHeight);
        document.body.appendChild(canvas);

        var ctx = canvas.getContext('2d');
        var particles = [];

        var originX = (options.origin && options.origin.x !== undefined ? options.origin.x : 0.5) * width;
        var originY = (options.origin && options.origin.y !== undefined ? options.origin.y : 0.6) * height;

        for (var i = 0; i < count; i++) {
            var color = colors[Math.floor(Math.random() * colors.length)];
            particles.push(new ConfettiParticle(originX, originY, color));
        }

        var animationFrameId;

        function animate() {
            ctx.clearRect(0, 0, width, height);

            var activeCount = 0;
            for (var j = 0; j < particles.length; j++) {
                var p = particles[j];
                p.update();
                p.draw(ctx);
                if (p.opacity > 0 && p.y < height + 50) {
                    activeCount++;
                }
            }

            if (activeCount > 0) {
                animationFrameId = requestAnimationFrame(animate);
            } else {
                cancelAnimationFrame(animationFrameId);
                if (canvas.parentNode) {
                    canvas.parentNode.removeChild(canvas);
                }
            }
        }

        animate();
    }

    root.confetti = runConfetti;
})(typeof window !== 'undefined' ? window : this);
