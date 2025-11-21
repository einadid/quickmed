<?php
/**
 * 404 Not Found - Aesthetic & Interactive
 */
require_once 'config.php'; // Ensure this path is correct
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Lost in Pharmacy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Space Mono', monospace;
            background-color: #064e3b; /* Deep Green Base */
            overflow: hidden;
        }

        /* --- Background Animation --- */
        .bg-grid {
            background-image: radial-gradient(#84cc16 1px, transparent 1px);
            background-size: 40px 40px;
            opacity: 0.1;
        }
        
        .floating-item {
            position: absolute;
            animation: floatUp 15s linear infinite;
            opacity: 0.2;
            z-index: 0;
        }
        
        @keyframes floatUp {
            0% { transform: translateY(100vh) rotate(0deg); }
            100% { transform: translateY(-100px) rotate(360deg); }
        }

        /* --- Glitch Text Effect --- */
        .glitch {
            position: relative;
            color: #84cc16;
        }
        .glitch::before, .glitch::after {
            content: attr(data-text);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        .glitch::before {
            left: 2px;
            text-shadow: -1px 0 #ff00c1;
            clip: rect(44px, 450px, 56px, 0);
            animation: glitch-anim 5s infinite linear alternate-reverse;
        }
        .glitch::after {
            left: -2px;
            text-shadow: -1px 0 #00fff9;
            clip: rect(44px, 450px, 56px, 0);
            animation: glitch-anim2 5s infinite linear alternate-reverse;
        }
        
        @keyframes glitch-anim {
            0% { clip: rect(10px, 9999px, 30px, 0); }
            20% { clip: rect(80px, 9999px, 100px, 0); }
            40% { clip: rect(40px, 9999px, 60px, 0); }
            60% { clip: rect(20px, 9999px, 10px, 0); }
            100% { clip: rect(70px, 9999px, 90px, 0); }
        }
        @keyframes glitch-anim2 {
            0% { clip: rect(60px, 9999px, 80px, 0); }
            20% { clip: rect(10px, 9999px, 30px, 0); }
            40% { clip: rect(90px, 9999px, 10px, 0); }
            60% { clip: rect(30px, 9999px, 50px, 0); }
            100% { clip: rect(50px, 9999px, 20px, 0); }
        }

        /* --- CSS ART: The Lying Cat --- */
        .cat-container {
            position: relative;
            width: 300px;
            height: 180px;
            margin: 0 auto;
            z-index: 10;
        }

        /* Body */
        .cat-body {
            position: absolute;
            bottom: 0;
            left: 50px;
            width: 200px;
            height: 100px;
            background: #ffffff;
            border-radius: 50px 50px 20px 20px;
            box-shadow: inset -10px -10px 0 #e5e7eb;
        }

        /* Head */
        .cat-head {
            position: absolute;
            bottom: 40px;
            left: 0;
            width: 110px;
            height: 110px;
            background: #ffffff;
            border-radius: 50%;
            z-index: 20;
            box-shadow: inset -5px -5px 0 #e5e7eb;
        }

        /* Ears */
        .cat-ear {
            position: absolute;
            top: -20px;
            width: 0;
            height: 0;
            border-left: 15px solid transparent;
            border-right: 15px solid transparent;
            border-bottom: 40px solid #ffffff;
            z-index: 15;
        }
        .ear-left { left: 5px; transform: rotate(-20deg); }
        .ear-right { right: 5px; transform: rotate(20deg); }

        /* Eyes (Interactive) */
        .cat-eye {
            position: absolute;
            top: 35px;
            width: 24px;
            height: 24px;
            background: #065f46;
            border-radius: 50%;
            overflow: hidden;
        }
        .eye-left { left: 25px; }
        .eye-right { right: 25px; }
        
        .pupil {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 8px;
            height: 8px;
            background: #84cc16; /* Lime Green Pupil */
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: all 0.1s ease;
        }

        /* Pill in Mouth */
        .pill {
            position: absolute;
            bottom: 25px;
            left: 50%;
            transform: translateX(-50%) rotate(-10deg);
            width: 50px;
            height: 20px;
            background: linear-gradient(90deg, #ef4444 50%, #3b82f6 50%);
            border-radius: 10px;
            border: 2px solid #064e3b;
            z-index: 25;
            animation: chew 2s infinite ease-in-out;
        }
        
        @keyframes chew {
            0%, 100% { transform: translateX(-50%) rotate(-10deg) translateY(0); }
            50% { transform: translateX(-50%) rotate(-5deg) translateY(-2px); }
        }

        /* Tail */
        .cat-tail {
            position: absolute;
            bottom: 10px;
            right: -20px;
            width: 80px;
            height: 20px;
            background: #ffffff;
            border-radius: 10px;
            transform-origin: left center;
            animation: tailWag 3s infinite ease-in-out;
            z-index: 5;
        }
        @keyframes tailWag {
            0%, 100% { transform: rotate(-10deg); }
            50% { transform: rotate(20deg); }
        }

        /* Paws */
        .paw {
            position: absolute;
            bottom: -10px;
            width: 30px;
            height: 20px;
            background: #ffffff;
            border-radius: 15px;
            box-shadow: inset -2px -2px 0 #e5e7eb;
        }
        .paw-left { left: 90px; }
        .paw-right { left: 130px; }

        /* Button Neon Hover */
        .neon-btn:hover {
            box-shadow: 0 0 10px #84cc16, 0 0 20px #84cc16;
            text-shadow: 0 0 5px #fff;
        }

    </style>
</head>
<body class="h-screen w-full flex flex-col items-center justify-center relative">

    <div class="absolute inset-0 bg-grid pointer-events-none"></div>

    <div id="background-elements">
        </div>

    <div class="text-center z-20 relative px-4">
        
        <h1 class="text-[8rem] md:text-[10rem] font-bold leading-none glitch mb-8" data-text="404">
            404
        </h1>

        
        <div class="cat-container group" title="I'm fine, just resting.">
            <div class="cat-tail"></div>
            <div class="cat-body">
                <div class="paw paw-left"></div>
                <div class="paw paw-right"></div>
            </div>
            <div class="cat-head">
                <div class="cat-ear ear-left"></div>
                <div class="cat-ear ear-right"></div>
                
                <div class="cat-eye eye-left"><div class="pupil"></div></div>
                <div class="cat-eye eye-right"><div class="pupil"></div></div>
                
                <div class="pill"></div>
            </div>
        </div>

        <h2 class="text-2xl md:text-4xl font-bold text-white mt-12 mb-4">
            Medicine Not Found!
        </h2>
        <p class="text-lime-300 font-mono mb-8 max-w-lg mx-auto">
            Our pharmacist cat ate the page you were looking for. <br>
            Wait, is that a pill in his mouth?
        </p>

        <div class="flex flex-col md:flex-row gap-4 justify-center">
            <a href="<?= SITE_URL ?>/index.php" 
               class="neon-btn bg-[#84cc16] text-[#064e3b] px-8 py-4 text-xl font-bold border-4 border-white hover:scale-105 transition-transform duration-200 uppercase tracking-wider">
                🏠 Return Home
            </a>
            <a href="javascript:history.back()" 
               class="neon-btn bg-transparent text-white px-8 py-4 text-xl font-bold border-4 border-[#84cc16] hover:bg-[#84cc16] hover:text-[#064e3b] transition-all duration-200 uppercase tracking-wider">
                ↩ Go Back
            </a>
        </div>
    </div>

    <script>
        // 1. Floating Background Elements Generator
        const container = document.getElementById('background-elements');
        const items = ['💊', '💉', '🩺', '🧬', '🩹', '🧪'];
        
        for (let i = 0; i < 15; i++) {
            const div = document.createElement('div');
            div.classList.add('floating-item');
            div.textContent = items[Math.floor(Math.random() * items.length)];
            
            // Randomize positions
            div.style.left = Math.random() * 100 + 'vw';
            div.style.fontSize = (Math.random() * 3 + 2) + 'rem'; // 2rem to 5rem
            div.style.animationDuration = (Math.random() * 10 + 10) + 's'; // 10s to 20s
            div.style.animationDelay = '-' + (Math.random() * 10) + 's'; // Start at random times
            
            container.appendChild(div);
        }

        // 2. Eye Tracking Script (The "Live" Effect)
        document.addEventListener('mousemove', (e) => {
            const pupils = document.querySelectorAll('.pupil');
            
            pupils.forEach(pupil => {
                // Get center of the eye
                const rect = pupil.parentElement.getBoundingClientRect();
                const x = rect.left + rect.width / 2;
                const y = rect.top + rect.height / 2;
                
                // Calculate angle
                const angle = Math.atan2(e.clientY - y, e.clientX - x);
                
                // Move pupil (max 5px)
                const distance = Math.min(5, Math.hypot(e.clientX - x, e.clientY - y) / 10);
                
                const xMove = Math.cos(angle) * distance;
                const yMove = Math.sin(angle) * distance;
                
                pupil.style.transform = `translate(calc(-50% + ${xMove}px), calc(-50% + ${yMove}px))`;
            });
        });
    </script>

</body>
</html>