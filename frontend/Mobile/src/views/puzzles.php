<?php
$pageTitle = 'GameZone';
$backRoute = 'dashboard';
include __DIR__ . '/../components/header.php';
?>

<div class="puzzles-container has-header animate-slide-up" style="padding-left: 16px; padding-right: 16px; padding-bottom: 40px; min-height: 100vh; box-sizing: border-box; background: radial-gradient(ellipse at 85% 5%, rgba(0, 242, 254, 0.35) 0%, transparent 55%), radial-gradient(ellipse at 15% 45%, rgba(56, 189, 248, 0.3) 0%, transparent 60%), radial-gradient(ellipse at 80% 80%, rgba(63, 125, 183, 0.4) 0%, transparent 60%), linear-gradient(180deg, #1e3a8a 0%, #3f7db7 30%, #0284c7 65%, #06b6d4 90%, #00f2fe 100%) !important; background-attachment: fixed !important; color: #ffffff;">
    
    <!-- Points Status Header -->
    <div style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important; border: none !important; outline: none !important; border-radius: 20px; padding: 16px 18px; display: flex; align-items: center; justify-content: space-between; margin-top: 16px; margin-bottom: 20px; box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25) !important;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(56, 189, 248, 0.2); display: flex; align-items: center; justify-content: center; font-size: 20px; color: #ffffff; border: none !important; outline: none !important;">
                <i class="fa-solid fa-gamepad"></i>
            </div>
            <div>
                <h4 style="margin: 0 0 2px 0; font-size: 13px; color: rgba(226, 232, 240, 0.85); font-weight: 600;">Your Points Balance</h4>
                <div style="display: flex; align-items: baseline; gap: 6px;">
                    <span id="game-points-val" style="font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px;">--</span>
                    <span style="font-size: 12px; color: rgba(226, 232, 240, 0.7); font-weight: 700;">PTS</span>
                </div>
            </div>
        </div>
        <button onclick="navigateTo('discount')" style="background: rgba(255,255,255,0.18) !important; border: none !important; outline: none !important; color: #fff; padding: 8px 16px; border-radius: 100px; font-size: 12px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: 0.2s; box-shadow: none !important;">
            Redeem <i class="fa-solid fa-arrow-right" style="font-size: 10px;"></i>
        </button>
    </div>

    <!-- Tab Selector -->
    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; background: rgba(15, 23, 42, 0.4) !important; border: none !important; outline: none !important; padding: 6px; border-radius: 16px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.12) !important;">
        <button id="tab-btn-puzzle" onclick="switchGameTab('puzzle')" class="game-nav-tab active">
            <i class="fa-solid fa-puzzle-piece"></i> Slide Puzzle
        </button>
        <button id="tab-btn-memory" onclick="switchGameTab('memory')" class="game-nav-tab">
            <i class="fa-solid fa-clone"></i> Memory Match
        </button>
        <button id="tab-btn-scramble" onclick="switchGameTab('scramble')" class="game-nav-tab">
            <i class="fa-solid fa-font"></i> Word Scramble
        </button>
    </div>

    <!-- SLIDING PUZZLE TAB -->
    <div id="game-tab-puzzle" class="game-tab-content">
        <div style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important; border: none !important; outline: none !important; border-radius: 20px; padding: 18px 20px; margin-bottom: 16px; text-align: center; box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25) !important;">
            <h3 id="puzzle-title" style="margin: 0 0 6px 0; font-size: 16px; font-weight: 800; color: #ffffff;">Immuki Island Slide Puzzle</h3>
            <p id="puzzle-desc" style="margin: 0; font-size: 12px; color: rgba(226, 232, 240, 0.9); line-height: 1.4;">
                Rearrange the tiles to reveal the crystal clear lagoons of Immuki Island in Balaoan! Solve to earn <strong style="color: #00f2fe;">+100 Points</strong>.
            </p>

            <!-- Target Reference Image Preview -->
            <div style="margin-top: 10px; display: flex; align-items: center; justify-content: center; gap: 8px; background: rgba(0,0,0,0.2) !important; border: none !important; outline: none !important; border-radius: 12px; padding: 6px 12px; width: fit-content; margin-left: auto; margin-right: auto;">
                <span style="font-size: 11px; color: rgba(226,232,240,0.9); font-weight: 700;">Target Goal:</span>
                <img id="puzzle-target-img" src="https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/tourist_spots/spot_6a686f4d0f48b.jpg" alt="Immuki Island Target" style="width: 36px; height: 36px; border-radius: 8px; object-fit: cover; border: none !important; outline: none !important; box-shadow: 0 2px 8px rgba(0,0,0,0.3);">
            </div>
            
            <!-- Moves and Timer info -->
            <div style="display: flex; justify-content: center; gap: 20px; margin-top: 14px;">
                <div style="font-size: 13px; color: rgba(255,255,255,0.75);">Moves: <span id="puzzle-moves" style="font-weight: 800; color: #fff;">0</span></div>
                <div style="font-size: 13px; color: rgba(255,255,255,0.75);">Time: <span id="puzzle-timer" style="font-weight: 800; color: #fff;">00:00</span></div>
            </div>
        </div>

        <!-- Puzzle Board Container -->
        <div style="display: flex; justify-content: center; margin-bottom: 20px;">
            <div id="puzzle-board" style="width: 308px; height: 308px; background: #0c1a30; border: 3px solid rgba(255, 255, 255, 0.45) !important; outline: 2px solid rgba(56, 189, 248, 0.6) !important; border-radius: 18px; position: relative; overflow: hidden; display: grid; grid-template-columns: repeat(3, 1fr); grid-template-rows: repeat(3, 1fr); gap: 4px; padding: 4px; box-sizing: border-box; box-shadow: 0 15px 35px rgba(10, 25, 60, 0.5), 0 0 25px rgba(56, 189, 248, 0.25);">
                <!-- 9 Grid items dynamic -->
            </div>
        </div>

        <div style="display: flex; gap: 10px; justify-content: center; align-items: center; flex-wrap: wrap;">
            <button onclick="promptResetPuzzle()" style="background: rgba(255,255,255,0.18) !important; border: none !important; outline: none !important; color: #fff; padding: 10px 18px; border-radius: 12px; font-weight: 800; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; box-shadow: none !important;">
                <i class="fa-solid fa-arrows-rotate"></i> Reset Puzzle
            </button>
            <button onclick="promptChangePuzzle()" style="background: linear-gradient(135deg, #00f2fe 0%, #0284c7 100%) !important; border: none !important; outline: none !important; color: #ffffff !important; padding: 10px 18px; border-radius: 12px; font-weight: 800; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; box-shadow: 0 4px 15px rgba(0,242,254,0.3) !important;">
                <i class="fa-solid fa-shuffle"></i> Change Puzzle
            </button>
        </div>
    </div>

    <!-- MEMORY MATCH TAB -->
    <div id="game-tab-memory" class="game-tab-content" style="display: none;">
        <div style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important; border: none !important; outline: none !important; border-radius: 20px; padding: 18px 20px; margin-bottom: 16px; text-align: center; box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25) !important;">
            <h3 style="margin: 0 0 6px 0; font-size: 16px; font-weight: 800; color: #ffffff;">Elyu Spot Memory Match</h3>
            <p style="margin: 0; font-size: 12px; color: rgba(226, 232, 240, 0.9); line-height: 1.4;">
                Flip cards and match all 6 pairs of famous La Union landmarks & activities to earn <strong style="color: #00f2fe;">+75 Points</strong>!
            </p>
            <div style="display: flex; justify-content: center; gap: 20px; margin-top: 14px;">
                <div style="font-size: 13px; color: rgba(255,255,255,0.75);">Flips: <span id="memory-flips" style="font-weight: 800; color: #fff;">0</span></div>
                <div style="font-size: 13px; color: rgba(255,255,255,0.75);">Pairs: <span id="memory-pairs" style="font-weight: 800; color: #fff;">0/6</span></div>
            </div>
        </div>

        <div class="memory-grid" id="memory-board">
            <!-- 12 Cards Grid -->
        </div>

        <div style="display: flex; justify-content: center;">
            <button onclick="initMemoryGame()" style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important; border: none !important; outline: none !important; color: #fff; padding: 10px 20px; border-radius: 14px; font-weight: 800; font-size: 13px; cursor: pointer; box-shadow: 0 4px 15px rgba(10, 25, 60, 0.2) !important;">
                <i class="fa-solid fa-arrows-rotate"></i> Reset Game
            </button>
        </div>
    </div>

    <!-- WORD SCRAMBLE TAB -->
    <div id="game-tab-scramble" class="game-tab-content" style="display: none;">
        <div style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important; border: none !important; outline: none !important; border-radius: 20px; padding: 18px 20px; margin-bottom: 16px; text-align: center; box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25) !important;">
            <h3 style="margin: 0 0 6px 0; font-size: 16px; font-weight: 800; color: #ffffff;">La Union Eco Explorer Scramble</h3>
            <p style="margin: 0; font-size: 12px; color: rgba(226, 232, 240, 0.9); line-height: 1.4;">
                Unscramble all 4 La Union municipal & landmark names to earn <strong style="color: #00f2fe;">+75 Points</strong>!
            </p>
        </div>

        <div id="scramble-container" style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px;">
            <!-- 4 Scrambled Words -->
        </div>

        <button onclick="submitScrambleAnswers()" style="width: 100%; border: none !important; outline: none !important; background: linear-gradient(135deg, #00f2fe 0%, #0284c7 100%) !important; color: white; padding: 14px; border-radius: 14px; font-weight: 800; font-size: 14px; cursor: pointer; box-shadow: 0 8px 24px rgba(2, 132, 199, 0.35);">
            Submit Answers
        </button>
    </div>

    <!-- Confirm Modal popup -->
    <div id="game-confirm-modal" style="display: none; position: fixed; inset: 0; z-index: 10002; background: rgba(0,0,0,0.85); align-items: center; justify-content: center; padding: 24px; backdrop-filter: blur(10px);">
        <div style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important; border: none !important; outline: none !important; border-radius: 24px; width: 100%; max-width: 350px; padding: 28px 20px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.6) !important; transform: scale(0.9); animation: modalEnter 0.3s forwards cubic-bezier(0.34, 1.56, 0.64, 1);">
            <div id="confirm-modal-icon-bg" style="width: 64px; height: 64px; border-radius: 50%; background: rgba(56,189,248,0.2); border: none !important; outline: none !important; display: flex; align-items: center; justify-content: center; font-size: 26px; color: #38bdf8; margin: 0 auto 16px;">
                <i id="confirm-modal-icon" class="fa-solid fa-arrows-rotate"></i>
            </div>
            <h2 id="confirm-modal-title" style="margin: 0 0 10px; font-size: 20px; font-weight: 800; color: #fff;">Reset Puzzle?</h2>
            <p id="confirm-modal-msg" style="margin: 0 0 24px; font-size: 13px; color: rgba(226,232,240,0.9); line-height: 1.5;">Are you sure you want to reset puzzle?</p>
            <div style="display: flex; gap: 10px;">
                <button onclick="closeGameConfirm()" style="flex: 1; border: none !important; outline: none !important; background: rgba(255,255,255,0.18); color: #ffffff; padding: 12px; border-radius: 12px; font-weight: 700; font-size: 13px; cursor: pointer;">
                    Cancel
                </button>
                <button id="confirm-modal-action-btn" onclick="executeGameConfirm()" style="flex: 1; border: none !important; outline: none !important; background: linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); color: #ffffff; padding: 12px; border-radius: 12px; font-weight: 800; font-size: 13px; cursor: pointer; box-shadow: 0 4px 14px rgba(0, 242, 254, 0.3);">
                    Yes, Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Success Modal popup -->
    <div id="game-success-modal" style="display: none; position: fixed; inset: 0; z-index: 10000; background: rgba(0,0,0,0.85); align-items: center; justify-content: center; padding: 24px; backdrop-filter: blur(10px);">
        <div style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important; border: none !important; outline: none !important; border-radius: 24px; width: 100%; max-width: 350px; padding: 30px 20px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.6) !important; transform: scale(0.9); animation: modalEnter 0.3s forwards cubic-bezier(0.34, 1.56, 0.64, 1);">
            <div style="width: 72px; height: 72px; border-radius: 50%; background: rgba(34,197,94,0.2); border: none !important; outline: none !important; display: flex; align-items: center; justify-content: center; font-size: 32px; color: #22c55e; margin: 0 auto 20px;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h2 style="margin: 0 0 10px; font-size: 22px; font-weight: 800; color: #fff;">Awesome Job!</h2>
            <p id="success-points-msg" style="margin: 0 0 24px; font-size: 14px; color: rgba(226,232,240,0.9); line-height: 1.5;">You solved the game and claimed your points!</p>
            <button onclick="closeGameSuccess()" style="width: 100%; border: none !important; outline: none !important; background: linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); color: #ffffff; padding: 12px; border-radius: 12px; font-weight: 800; font-size: 14px; cursor: pointer; box-shadow: 0 4px 14px rgba(0, 242, 254, 0.3);">
                Awesome!
            </button>
        </div>
    </div>

    <div id="game-alert-modal" style="display: none; position: fixed; inset: 0; z-index: 10001; background: rgba(0,0,0,0.85); align-items: center; justify-content: center; padding: 24px; backdrop-filter: blur(10px);">
        <div style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important; border: none !important; outline: none !important; border-radius: 24px; width: 100%; max-width: 350px; padding: 30px 20px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.6) !important; transform: scale(0.9); animation: modalEnter 0.3s forwards cubic-bezier(0.34, 1.56, 0.64, 1);">
            <div style="width: 72px; height: 72px; border-radius: 50%; background: rgba(250,204,21,0.2); border: none !important; outline: none !important; display: flex; align-items: center; justify-content: center; font-size: 32px; color: #facc15; margin: 0 auto 20px;">
                <i class="fa-solid fa-clock"></i>
            </div>
            <h2 style="margin: 0 0 10px; font-size: 22px; font-weight: 800; color: #fff;">Already Done!</h2>
            <p id="alert-points-msg" style="margin: 0 0 24px; font-size: 14px; color: rgba(226,232,240,0.9); line-height: 1.5;">You already completed this game today!</p>
            <button onclick="closeGameAlert()" style="width: 100%; border: none !important; outline: none !important; background: linear-gradient(135deg, #facc15 0%, #eab308 100%); color: #000; padding: 12px; border-radius: 12px; font-weight: 800; font-size: 14px; cursor: pointer; box-shadow: 0 4px 14px rgba(250, 204, 21, 0.3);">
                Got it!
            </button>
        </div>
    </div>

</div>

<style>
@keyframes modalEnter {
    to { transform: scale(1); }
}
.game-nav-tab {
    border: none !important;
    outline: none !important;
    background: transparent;
    color: rgba(255,255,255,0.7);
    padding: 10px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
}
.game-nav-tab:active {
    transform: scale(0.94);
}
.game-nav-tab.active {
    background: linear-gradient(135deg, #00f2fe 0%, #0284c7 60%, #1e3a8a 100%) !important;
    color: #ffffff !important;
    font-weight: 800;
    box-shadow: 0 4px 15px rgba(2, 132, 199, 0.35);
    transform: translateY(-1px);
    border: none !important;
    outline: none !important;
}

.game-tab-content {
    will-change: transform, opacity;
}
.game-tab-animate {
    animation: tabSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes tabSlideIn {
    from {
        opacity: 0;
        transform: translateY(14px) scale(0.97);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.puzzle-tile {
    width: 100%;
    height: 100%;
    background-size: 300px 300px;
    background-repeat: no-repeat;
    cursor: pointer;
    border-radius: 10px;
    border: 2px solid rgba(255, 255, 255, 0.7) !important;
    outline: 1.5px solid rgba(30, 58, 138, 0.5) !important;
    box-shadow: inset 0 0 8px rgba(0, 0, 0, 0.4), 0 3px 8px rgba(0, 0, 0, 0.3) !important;
    transition: transform 0.15s ease, filter 0.2s, border-color 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
}
.puzzle-tile:hover, .puzzle-tile:active {
    filter: brightness(1.15);
    transform: scale(0.97);
    border-color: #00f2fe !important;
}
.puzzle-empty {
    background: rgba(12, 26, 48, 0.7) !important;
    cursor: default;
    box-shadow: inset 0 0 14px rgba(0, 0, 0, 0.8) !important;
    border: 2px dashed rgba(56, 189, 248, 0.5) !important;
    outline: none !important;
    border-radius: 10px;
}
.trivia-card, .scramble-card {
    background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important;
    border: none !important;
    outline: none !important;
    border-radius: 18px !important;
    padding: 16px !important;
    box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25) !important;
}
.trivia-q-text, .scramble-q-text {
    margin: 0 0 12px;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.4;
    color: #fff;
}
.trivia-options {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.trivia-option-btn {
    width: 100%;
    text-align: left;
    background: rgba(255,255,255,0.15) !important;
    border: none !important;
    outline: none !important;
    color: #ffffff !important;
    padding: 10px 14px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.trivia-option-btn:hover {
    background: rgba(255,255,255,0.22) !important;
}
.trivia-option-btn.selected {
    background: linear-gradient(135deg, #00f2fe 0%, #0284c7 100%) !important;
    border: none !important;
    outline: none !important;
    color: #ffffff !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 242, 254, 0.3);
}

/* General Button Smooth Animations */
button, .game-nav-tab, .trivia-option-btn {
    border: none !important;
    outline: none !important;
    transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, filter 0.15s ease !important;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
}

button:active, .trivia-option-btn:active {
    transform: scale(0.93) translateY(1px) !important;
    filter: brightness(1.2);
}

@keyframes btnPulse {
    0% { transform: scale(1); }
    40% { transform: scale(0.92); }
    75% { transform: scale(1.03); }
    100% { transform: scale(1); }
}

.btn-click-effect {
    animation: btnPulse 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* Memory Match Styles */
.memory-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    max-width: 320px;
    margin: 0 auto 20px;
}
.memory-card {
    height: 85px;
    perspective: 1000px;
    cursor: pointer;
}
.memory-card-inner {
    position: relative;
    width: 100%;
    height: 100%;
    text-align: center;
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    transform-style: preserve-3d;
    border-radius: 14px;
}
.memory-card.flipped .memory-card-inner, .memory-card.matched .memory-card-inner {
    transform: rotateY(180deg);
}
.memory-card-front, .memory-card-back {
    position: absolute;
    width: 100%;
    height: 100%;
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden;
    border-radius: 14px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
}
.memory-card-front {
    background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important;
    border: none !important;
    outline: none !important;
    box-shadow: 0 4px 14px rgba(10, 25, 60, 0.25) !important;
    color: #ffffff;
    font-size: 24px;
}
.memory-card-back {
    background: linear-gradient(135deg, #00f2fe 0%, #0284c7 100%) !important;
    border: none !important;
    outline: none !important;
    color: #ffffff;
    transform: rotateY(180deg);
    padding: 6px;
}
.memory-card.matched .memory-card-back {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    border: none !important;
    outline: none !important;
}
.scramble-input {
    width: 100% !important;
    background: rgba(255, 255, 255, 0.2) !important;
    border: none !important;
    outline: none !important;
    border-radius: 14px !important;
    padding: 14px 16px !important;
    color: #ffffff !important;
    font-size: 15px !important;
    font-weight: 800 !important;
    letter-spacing: 1.5px !important;
    box-sizing: border-box !important;
    text-transform: uppercase !important;
    box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.2) !important;
    -webkit-text-fill-color: #ffffff !important;
}
.scramble-input::placeholder {
    color: rgba(255, 255, 255, 0.8) !important;
    -webkit-text-fill-color: rgba(255, 255, 255, 0.8) !important;
    font-weight: 700 !important;
    letter-spacing: 1px !important;
    opacity: 1 !important;
}
.scramble-input:focus {
    outline: none !important;
    border: none !important;
    background: rgba(255, 255, 255, 0.28) !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    box-shadow: 0 0 16px rgba(0, 242, 254, 0.35), inset 0 2px 6px rgba(0, 0, 0, 0.2) !important;
}
</style>

<script>
(function() {
// Load points balance
async function loadGamePoints() {
    try {
        const token = localStorage.getItem('api_token') || localStorage.getItem('intan_elyu_token');
        const _baseUrl = (window.backendUrl || 'https://api.intan-elyu.online').replace(/\/+$/, '');
        const r = await fetch(_baseUrl + '/api/tourist/points/balance', {
            headers: { 'Authorization': 'Bearer ' + token }
        });
        const d = await r.json();
        if (d.status === 'success') {
            const ptsEl = document.getElementById('game-points-val');
            if (ptsEl) ptsEl.textContent = d.points;
            if (window.updateProfilePointsDisplay) {
                window.updateProfilePointsDisplay(d.points);
            }
        }
    } catch (e) {
        console.error("Points load error:", e);
    }
}

// Switching between tabs
function switchGameTab(tabName) {
    document.querySelectorAll('.game-tab-content').forEach(el => {
        el.style.display = 'none';
        el.classList.remove('game-tab-animate');
    });
    document.querySelectorAll('.game-nav-tab').forEach(el => el.classList.remove('active'));

    const activeTabEl = document.getElementById('game-tab-' + tabName);
    const activeBtnEl = document.getElementById('tab-btn-' + tabName);
    
    if (activeTabEl) {
        activeTabEl.style.display = 'block';
        void activeTabEl.offsetWidth; // force CSS reflow
        activeTabEl.classList.add('game-tab-animate');
    }
    if (activeBtnEl) activeBtnEl.classList.add('active');

    if (tabName === 'puzzle') {
        initPuzzle();
    } else if (tabName === 'trivia') {
        initTrivia();
    } else if (tabName === 'memory') {
        initMemoryGame();
    } else if (tabName === 'scramble') {
        initScrambleGame();
    }
}

// ----------------------------------------------------
// SLIDING PUZZLE LOGIC (Randomized La Union Spot Images from Cloudflare R2)
// ----------------------------------------------------
const R2_PUBLIC_BASE = 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/tourist_spots/';

const PUZZLE_IMAGES = [
    {
        name: "Immuki Island",
        location: "Balaoan",
        image: R2_PUBLIC_BASE + "spot_6a686f4d0f48b.jpg",
        desc: 'Rearrange the tiles to reveal the crystal clear lagoons of Immuki Island in Balaoan! Solve to earn <strong style="color: #38bdf8;">+100 Points</strong>.'
    },
    {
        name: "Agoo Eco Fun World",
        location: "Agoo",
        image: R2_PUBLIC_BASE + "spot_6a689fe582fe5.jpg",
        desc: 'Rearrange the tiles to reveal the lush pine trees of Agoo Eco Fun World! Solve to earn <strong style="color: #38bdf8;">+100 Points</strong>.'
    }
];

let currentPuzzleItem = PUZZLE_IMAGES[0];
let dbPuzzleSpots = [];
let tiles = [];
let moves = 0;
let timeSec = 0;
let puzzleSolved = false;
let pendingConfirmAction = null;

async function fetchDatabasePuzzleSpots() {
    try {
        const token = localStorage.getItem('api_token') || localStorage.getItem('intan_elyu_token');
        const headers = { 'Accept': 'application/json' };
        if (token) headers['Authorization'] = 'Bearer ' + token;

        const _baseUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || window.location.origin)).replace(/\/+$/, '');
        let res = await fetch(_baseUrl + '/api/puzzles/spots', { headers });
        if (!res.ok) {
            res = await fetch(_baseUrl + '/api/tourist/puzzles/spots', { headers });
        }

        if (res.ok) {
            const data = await res.json();
            if (data.status === 'success' && Array.isArray(data.spots) && data.spots.length > 0) {
                dbPuzzleSpots = data.spots.map(s => {
                    let imgUrl = s.image;
                    if (typeof window.resolveImageUrl === 'function') {
                        imgUrl = window.resolveImageUrl(imgUrl);
                    } else if (imgUrl && imgUrl.includes('spot_')) {
                        const m = imgUrl.match(/(spot_[a-z0-9_]+\.(?:jpg|jpeg|png|webp|gif))/i);
                        if (m) imgUrl = R2_PUBLIC_BASE + m[1];
                    } else if (imgUrl && !imgUrl.startsWith('http') && !imgUrl.startsWith('data:')) {
                        imgUrl = _baseUrl + imgUrl;
                    }
                    return {
                        name: s.name,
                        location: s.location,
                        image: imgUrl,
                        desc: s.desc
                    };
                });
                // If it was the fallback default, replace with a random spot from database
                if (!currentPuzzleItem || PUZZLE_IMAGES.some(p => p.image === currentPuzzleItem.image)) {
                    initPuzzle(true);
                }
            }
        }
    } catch(e) {
        console.warn('Could not fetch database puzzle spots:', e);
    }
}
fetchDatabasePuzzleSpots();

// Clear any lingering interval from a prior IIFE run (SPA AJAX re-execution)
if (window._puzzleTimerInterval) clearInterval(window._puzzleTimerInterval);

const correctLayout = [0, 1, 2, 3, 4, 5, 6, 7, 8]; // 8 is the empty cell

function startPuzzleTimer() {
    if (window._puzzleTimerInterval) clearInterval(window._puzzleTimerInterval);
    window._puzzleTimerInterval = setInterval(() => {
        if (puzzleSolved) return;
        timeSec++;
        const mins = Math.floor(timeSec / 60).toString().padStart(2, '0');
        const secs = (timeSec % 60).toString().padStart(2, '0');
        const el = document.getElementById('puzzle-timer');
        if (el) el.textContent = mins + ':' + secs;
    }, 1000);
}

function shuffleTiles() {
    do {
        tiles = [...correctLayout];
        for (let i = 7; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [tiles[i], tiles[j]] = [tiles[j], tiles[i]];
        }
    } while (!isSolvable(tiles));
}

function updatePuzzleInfoUI() {
    if (!currentPuzzleItem) return;
    const titleEl = document.getElementById('puzzle-title');
    const descEl = document.getElementById('puzzle-desc');
    const targetImgEl = document.getElementById('puzzle-target-img');

    if (titleEl) titleEl.textContent = currentPuzzleItem.name + ' Slide Puzzle';
    if (descEl) descEl.innerHTML = currentPuzzleItem.desc;
    if (targetImgEl) targetImgEl.src = currentPuzzleItem.image;
}

function resetPuzzleState() {
    moves = 0;
    timeSec = 0;
    puzzleSolved = false;
    const movesEl = document.getElementById('puzzle-moves');
    const timerEl = document.getElementById('puzzle-timer');
    if (movesEl) movesEl.textContent = '0';
    if (timerEl) timerEl.textContent = '00:00';
    
    startPuzzleTimer();
    shuffleTiles();
    renderPuzzleBoard();
}

function initPuzzle(forceNewImage = false) {
    // Daily limit check
    try {
        if (localStorage.getItem('game_done_puzzle') === new Date().toDateString()) {
            openGameAlert('Puzzle already completed today! Come back tomorrow.');
            return;
        }
    } catch(e) {}

    const pool = (dbPuzzleSpots && dbPuzzleSpots.length > 0) ? dbPuzzleSpots : PUZZLE_IMAGES;
    if (forceNewImage || !currentPuzzleItem) {
        const randomIdx = Math.floor(Math.random() * pool.length);
        currentPuzzleItem = pool[randomIdx];
    }

    updatePuzzleInfoUI();
    resetPuzzleState();
}

function promptResetPuzzle() {
    try {
        if (localStorage.getItem('game_done_puzzle') === new Date().toDateString()) {
            openGameAlert('Puzzle already completed today! Come back tomorrow.');
            return;
        }
    } catch(e) {}

    openGameConfirm({
        title: 'Reset Puzzle?',
        message: 'Are you sure you want to reset puzzle?',
        icon: 'fa-arrows-rotate',
        iconColor: '#38bdf8',
        iconBg: 'rgba(56,189,248,0.15)',
        confirmText: 'Yes, Reset',
        confirmBg: '#38bdf8',
        onConfirm: resetPuzzle
    });
}

function resetPuzzle() {
    // Keeps the same image, only rearranges the tiles and resets moves/timer
    resetPuzzleState();
}

function promptChangePuzzle() {
    try {
        if (localStorage.getItem('game_done_puzzle') === new Date().toDateString()) {
            openGameAlert('Puzzle already completed today! Come back tomorrow.');
            return;
        }
    } catch(e) {}

    openGameConfirm({
        title: 'Change Puzzle?',
        message: 'Are you sure you want to change puzzle? A new image will be loaded.',
        icon: 'fa-shuffle',
        iconColor: '#38bdf8',
        iconBg: 'rgba(56,189,248,0.15)',
        confirmText: 'Yes, Change',
        confirmBg: '#38bdf8',
        onConfirm: changePuzzle
    });
}

function changePuzzle() {
    const pool = (dbPuzzleSpots && dbPuzzleSpots.length > 0) ? dbPuzzleSpots : PUZZLE_IMAGES;
    if (pool.length > 1 && currentPuzzleItem) {
        let newIdx = Math.floor(Math.random() * pool.length);
        let attempts = 0;
        while (pool[newIdx].image === currentPuzzleItem.image && attempts < 10) {
            newIdx = Math.floor(Math.random() * pool.length);
            attempts++;
        }
        currentPuzzleItem = pool[newIdx];
    } else if (pool.length > 0) {
        currentPuzzleItem = pool[0];
    }

    updatePuzzleInfoUI();
    resetPuzzleState();
}

function isSolvable(grid) {
    let inversions = 0;
    const arr = grid.filter(x => x !== 8);
    for (let i = 0; i < arr.length; i++) {
        for (let j = i + 1; j < arr.length; j++) {
            if (arr[i] > arr[j]) inversions++;
        }
    }
    return inversions % 2 === 0;
}

function renderPuzzleBoard() {
    const board = document.getElementById('puzzle-board');
    if (!board) return;
    board.innerHTML = '';

    tiles.forEach((val, index) => {
        const div = document.createElement('div');
        if (val === 8) {
            div.className = 'puzzle-tile puzzle-empty';
        } else {
            div.className = 'puzzle-tile';
            div.style.backgroundImage = `url('${currentPuzzleItem.image}')`;
            div.style.backgroundSize = '300px 300px';
            const x = (val % 3) * 100;
            const y = Math.floor(val / 3) * 100;
            div.style.backgroundPosition = `-${x}px -${y}px`;
            div.onclick = () => moveTile(index);
        }
        board.appendChild(div);
    });
}

function moveTile(index) {
    if (puzzleSolved) return;

    const emptyIndex = tiles.indexOf(8);
    const row = Math.floor(index / 3);
    const col = index % 3;
    const emptyRow = Math.floor(emptyIndex / 3);
    const emptyCol = emptyIndex % 3;

    const isAdjacent = (Math.abs(row - emptyRow) + Math.abs(col - emptyCol)) === 1;

    if (isAdjacent) {
        tiles[emptyIndex] = tiles[index];
        tiles[index] = 8;
        moves++;
        const pmEl = document.getElementById('puzzle-moves');
        if (pmEl) pmEl.textContent = moves;
        
        renderPuzzleBoard();
        checkPuzzleSolved();
    }
}

function checkPuzzleSolved() {
    const isCorrect = tiles.every((v, i) => v === correctLayout[i]);
    if (isCorrect) {
        puzzleSolved = true;
        if (window._puzzleTimerInterval) clearInterval(window._puzzleTimerInterval);
        claimMiniGamePoints('puzzle');
    }
}


// ----------------------------------------------------
// TRIVIA QUIZ LOGIC
// ----------------------------------------------------
const triviaData = [
    {
        id: 1,
        question: "Which municipality of La Union is widely known as the 'Surfing Capital of the North'?",
        options: ["San Juan", "Bauang", "Agoo", "Luna"],
        correct: 0,
        selected: null
    },
    {
        id: 2,
        question: "What is the famous historical Spanish watchtower located along the coastline of Luna, La Union?",
        options: ["Baluarte Watchtower", "Poro Point Lighthouse", "Ma-Cho Temple", "Pindangan Ruins"],
        correct: 0,
        selected: null
    },
    {
        id: 3,
        question: "What is the primary spoken language and native dialect of people living in La Union?",
        options: ["Tagalog", "Ilokano", "Pangasinense", "Kapampangan"],
        correct: 1,
        selected: null
    }
];

function initTrivia() {
    try {
        if (localStorage.getItem('game_done_trivia') === new Date().toDateString()) {
            openGameAlert('Trivia already completed today! Come back tomorrow.');
            return;
        }
    } catch(e) {}
    triviaData.forEach(q => q.selected = null);
    const container = document.getElementById('trivia-questions-container');
    if (!container) return;
    container.innerHTML = '';

    triviaData.forEach((q, qIndex) => {
        const card = document.createElement('div');
        card.className = 'trivia-card';

        const qTitle = document.createElement('h4');
        qTitle.className = 'trivia-q-text';
        qTitle.textContent = `${qIndex + 1}. ${q.question}`;
        card.appendChild(qTitle);

        const optionsDiv = document.createElement('div');
        optionsDiv.className = 'trivia-options';

        q.options.forEach((opt, optIndex) => {
            const btn = document.createElement('button');
            btn.className = 'trivia-option-btn';
            btn.textContent = opt;
            btn.id = `q-${qIndex}-opt-${optIndex}`;
            btn.onclick = () => selectTriviaOption(qIndex, optIndex);
            optionsDiv.appendChild(btn);
        });

        card.appendChild(optionsDiv);
        container.appendChild(card);
    });
}

function selectTriviaOption(qIndex, optIndex) {
    triviaData[qIndex].selected = optIndex;
    for (let i = 0; i < triviaData[qIndex].options.length; i++) {
        const btn = document.getElementById(`q-${qIndex}-opt-${i}`);
        if (btn) {
            if (i === optIndex) btn.classList.add('selected');
            else btn.classList.remove('selected');
        }
    }
}

async function submitTriviaAnswers() {
    const unanswered = triviaData.some(q => q.selected === null);
    if (unanswered) {
        alert("Please answer all questions before submitting!");
        return;
    }

    const allCorrect = triviaData.every(q => q.selected === q.correct);
    if (!allCorrect) {
        alert("Some answers are incorrect. Try again! hint: Think surfing in San Juan, waterfalls in San Gabriel, and native Ilokano speakers.");
        return;
    }

    claimMiniGamePoints('trivia');
}


// ----------------------------------------------------
// MEMORY MATCH GAME LOGIC
// ----------------------------------------------------
const memoryIcons = [
    { icon: 'fa-water', label: 'Surfing' },
    { icon: 'fa-wine-glass', label: 'Grapes' },
    { icon: 'fa-mountain', label: 'Falls' },
    { icon: 'fa-monument', label: 'Watchtower' },
    { icon: 'fa-church', label: 'Shrine' },
    { icon: 'fa-sun', label: 'Sunset' },
];

let memoryCards = [];
let flippedCards = [];
let matchedPairsCount = 0;
let memoryFlips = 0;
let isMemoryBusy = false;

function initMemoryGame() {
    try {
        if (localStorage.getItem('game_done_memory_match') === new Date().toDateString()) {
            openGameAlert('Memory Match already completed today! Come back tomorrow.');
            return;
        }
    } catch(e) {}
    memoryFlips = 0;
    matchedPairsCount = 0;
    flippedCards = [];
    isMemoryBusy = false;

    const memFlipsEl = document.getElementById('memory-flips');
    const memPairsEl = document.getElementById('memory-pairs');
    if (memFlipsEl) memFlipsEl.textContent = '0';
    if (memPairsEl) memPairsEl.textContent = '0/6';

    // Create 12 cards (6 pairs)
    const deck = [];
    memoryIcons.forEach((item, index) => {
        deck.push({ id: index, icon: item.icon, label: item.label });
        deck.push({ id: index, icon: item.icon, label: item.label });
    });

    // Shuffle deck
    for (let i = deck.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [deck[i], deck[j]] = [deck[j], deck[i]];
    }

    memoryCards = deck;
    renderMemoryBoard();
}

function renderMemoryBoard() {
    const board = document.getElementById('memory-board');
    if (!board) return;
    board.innerHTML = '';

    memoryCards.forEach((card, index) => {
        const cardEl = document.createElement('div');
        cardEl.className = 'memory-card';
        cardEl.id = `mem-card-${index}`;

        cardEl.innerHTML = `
            <div class="memory-card-inner">
                <div class="memory-card-front">
                    <i class="fa-solid fa-gem"></i>
                </div>
                <div class="memory-card-back">
                    <i class="fa-solid ${card.icon}" style="font-size:24px; margin-bottom:4px;"></i>
                    <span style="font-size:10px; font-weight:800; text-transform:uppercase;">${card.label}</span>
                </div>
            </div>
        `;

        cardEl.onclick = () => flipMemoryCard(index);
        board.appendChild(cardEl);
    });
}

function flipMemoryCard(index) {
    if (isMemoryBusy) return;
    const cardEl = document.getElementById(`mem-card-${index}`);
    if (!cardEl || cardEl.classList.contains('flipped') || cardEl.classList.contains('matched')) return;

    cardEl.classList.add('flipped');
    flippedCards.push({ index, card: memoryCards[index] });

    memoryFlips++;
    const flipsEl = document.getElementById('memory-flips');
    if (flipsEl) flipsEl.textContent = memoryFlips;

    if (flippedCards.length === 2) {
        isMemoryBusy = true;
        const [first, second] = flippedCards;

        if (first.card.id === second.card.id) {
            // Match found
            setTimeout(() => {
                const el1 = document.getElementById(`mem-card-${first.index}`);
                const el2 = document.getElementById(`mem-card-${second.index}`);
                if (el1) el1.classList.add('matched');
                if (el2) el2.classList.add('matched');

                matchedPairsCount++;
                const pairsEl = document.getElementById('memory-pairs');
                if (pairsEl) pairsEl.textContent = `${matchedPairsCount}/6`;
                flippedCards = [];
                isMemoryBusy = false;

                if (matchedPairsCount === 6) {
                    claimMiniGamePoints('memory_match');
                }
            }, 300);
        } else {
            // No match -> flip back
            setTimeout(() => {
                const el1 = document.getElementById(`mem-card-${first.index}`);
                const el2 = document.getElementById(`mem-card-${second.index}`);
                if (el1) el1.classList.remove('flipped');
                if (el2) el2.classList.remove('flipped');
                flippedCards = [];
                isMemoryBusy = false;
            }, 900);
        }
    }
}


// ----------------------------------------------------
// WORD SCRAMBLE GAME LOGIC
// ----------------------------------------------------
const scrambleData = [
    {
        id: 1,
        scrambled: "UNAJ NAS",
        answer: "SAN JUAN",
        hint: "Surfing Capital of Northern Luzon"
    },
    {
        id: 2,
        scrambled: "GABERIL NAS",
        answer: "SAN GABRIEL",
        hint: "Known for scenic mountain trails and highland nature"
    },
    {
        id: 3,
        scrambled: "AGUANB",
        answer: "BAUANG",
        hint: "Famous for its lush grape farms & winemaking"
    },
    {
        id: 4,
        scrambled: "ALURATBE",
        answer: "BALUARTE",
        hint: "Historic Spanish-era watchtower in Luna"
    }
];

function initScrambleGame() {
    try {
        if (localStorage.getItem('game_done_word_scramble') === new Date().toDateString()) {
            openGameAlert('Word Scramble already completed today! Come back tomorrow.');
            return;
        }
    } catch(e) {}
    const container = document.getElementById('scramble-container');
    if (!container) return;
    container.innerHTML = '';

    scrambleData.forEach((item, index) => {
        const card = document.createElement('div');
        card.className = 'scramble-card';

        card.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <span style="font-size:12px; font-weight:800; color:#00f2fe; text-transform:uppercase; letter-spacing:0.8px;">Word #${index + 1}</span>
                <span id="scramble-status-${index}" style="font-size:14px; color:rgba(255,255,255,0.4);"><i class="fa-solid fa-pen"></i></span>
            </div>
            <div style="font-size:22px; font-weight:900; color:#ffffff !important; letter-spacing:3px; margin-bottom:10px; text-align:center; background:rgba(0,0,0,0.25) !important; padding:12px; border-radius:14px; border:none !important; outline:none !important; text-shadow:0 2px 4px rgba(0,0,0,0.4);">
                ${item.scrambled}
            </div>
            <p style="margin:0 0 12px 0; font-size:12.5px; color:#ffffff !important; font-weight:600; text-shadow:0 1px 2px rgba(0,0,0,0.4); line-height:1.4;">
                <span style="color:#facc15; font-size:13px; font-weight:800;">💡 Hint:</span> <span style="color:#ffffff !important; font-weight:700;">${item.hint}</span>
            </p>
            <input type="text" id="scramble-input-${index}" class="scramble-input" placeholder="TYPE ANSWER HERE..." oninput="checkScrambleWord(${index})" style="color:#ffffff !important; -webkit-text-fill-color:#ffffff !important; border:none !important; outline:none !important;">
        `;

        container.appendChild(card);
    });
}

function checkScrambleWord(index) {
    const input = document.getElementById(`scramble-input-${index}`);
    const status = document.getElementById(`scramble-status-${index}`);
    if (!input || !status) return;

    const val = input.value.trim().toUpperCase();
    if (val === scrambleData[index].answer) {
        status.innerHTML = '<i class="fa-solid fa-circle-check" style="color:#34d399; font-size:18px;"></i>';
        input.style.border = 'none';
        input.style.outline = 'none';
        input.style.background = 'rgba(52, 211, 153, 0.3)';
        input.style.color = '#ffffff';
        input.style.webkitTextFillColor = '#ffffff';
    } else {
        status.innerHTML = '<i class="fa-solid fa-pen" style="color:rgba(255,255,255,0.4); font-size:14px;"></i>';
        input.style.border = 'none';
        input.style.outline = 'none';
        input.style.background = 'rgba(255, 255, 255, 0.2)';
        input.style.color = '#ffffff';
        input.style.webkitTextFillColor = '#ffffff';
    }
}

async function submitScrambleAnswers() {
    let allCorrect = true;

    scrambleData.forEach((item, index) => {
        const input = document.getElementById(`scramble-input-${index}`);
        const val = input ? input.value.trim().toUpperCase() : '';
        if (val !== item.answer) {
            allCorrect = false;
        }
    });

    if (!allCorrect) {
        alert("Some words are still incorrect or incomplete. Use the hints to help unscramble all 4 words!");
        return;
    }

    claimMiniGamePoints('word_scramble');
}


// ----------------------------------------------------
// GENERAL API & SUCCESS MODAL HELPERS
// ----------------------------------------------------
async function claimMiniGamePoints(gameType) {
    try {
        const token = localStorage.getItem('api_token') || localStorage.getItem('intan_elyu_token');
        const _baseUrl = (window.backendUrl || 'https://api.intan-elyu.online').replace(/\/+$/, '');

        // If generic minigame endpoint, call minigame API
        let endpoint = '/api/tourist/points/minigame';
        let bodyObj = { game_type: gameType };

        if (gameType === 'puzzle') {
            endpoint = '/api/tourist/points/puzzle';
            bodyObj = {};
        } else if (gameType === 'trivia') {
            endpoint = '/api/tourist/points/trivia';
            bodyObj = {};
        }

        const r = await fetch(_baseUrl + endpoint, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify(bodyObj)
        });
        const d = await r.json();
        if (r.ok && d.status === 'success') {
            // Mark as completed today for local checks
            try { localStorage.setItem('game_done_' + gameType, new Date().toDateString()); } catch(e) {}
            openGameSuccess(d.message);
        } else {
            const title = (r.status === 429) ? "Already Done!" : (r.status >= 500 ? "Server Error" : "Notice");
            openGameAlert(d.message || "Game already completed today!", title);
        }
    } catch (e) {
        console.error("Points claim error:", e);
        openGameAlert("Connection issue. Please try again.", "Error");
    }
}

function openGameSuccess(message) {
    const modal = document.getElementById('game-success-modal');
    if (modal) {
        const msgEl = document.getElementById('success-points-msg');
        if (msgEl) {
            msgEl.textContent = message || "You've successfully completed the game and earned points!";
        }
        modal.style.display = 'flex';
        loadGamePoints();
    }
}

function closeGameSuccess() {
    const modal = document.getElementById('game-success-modal');
    if (modal) modal.style.display = 'none';
}

function openGameAlert(message, title = "Already Done!") {
    const modal = document.getElementById('game-alert-modal');
    if (modal) {
        const titleEl = modal.querySelector('h2');
        if (titleEl) titleEl.textContent = title;
        const msgEl = document.getElementById('alert-points-msg');
        if (msgEl) msgEl.textContent = message || "You already completed this game today!";
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeGameAlert() {
    const modal = document.getElementById('game-alert-modal');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
}

function openGameConfirm({ title, message, icon = 'fa-arrows-rotate', iconColor = '#38bdf8', iconBg = 'rgba(56,189,248,0.15)', confirmText = 'Confirm', confirmBg = '#38bdf8', onConfirm }) {
    const modal = document.getElementById('game-confirm-modal');
    if (!modal) return;
    
    const titleEl = document.getElementById('confirm-modal-title');
    const msgEl = document.getElementById('confirm-modal-msg');
    const iconEl = document.getElementById('confirm-modal-icon');
    const iconBgEl = document.getElementById('confirm-modal-icon-bg');
    const actionBtn = document.getElementById('confirm-modal-action-btn');

    if (titleEl) titleEl.textContent = title || 'Confirm Action';
    if (msgEl) msgEl.textContent = message || 'Are you sure?';
    if (iconEl) {
        iconEl.className = `fa-solid ${icon}`;
        iconEl.style.color = iconColor;
    }
    if (iconBgEl) {
        iconBgEl.style.background = iconBg;
        iconBgEl.style.borderColor = iconColor;
    }
    if (actionBtn) {
        actionBtn.textContent = confirmText;
        actionBtn.style.background = confirmBg;
        if (confirmBg === '#38bdf8' || confirmBg.includes('#38bdf8')) {
            actionBtn.style.color = '#0f172a';
        } else {
            actionBtn.style.color = '#fff';
        }
    }

    pendingConfirmAction = onConfirm;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeGameConfirm() {
    const modal = document.getElementById('game-confirm-modal');
    if (modal) modal.style.display = 'none';
    pendingConfirmAction = null;
    document.body.style.overflow = '';
}

function executeGameConfirm() {
    const action = pendingConfirmAction;
    closeGameConfirm();
    if (typeof action === 'function') {
        action();
    }
}

// Global Smooth Button Click Feedback Listener
document.addEventListener('click', (e) => {
    const btn = e.target.closest('button, .game-nav-tab, .trivia-option-btn');
    if (btn) {
        btn.classList.remove('btn-click-effect');
        void btn.offsetWidth; // trigger reflow
        btn.classList.add('btn-click-effect');
    }
});

// Startup
loadGamePoints();
switchGameTab('puzzle');

// Expose needed functions globally
window.switchGameTab = switchGameTab;
window.loadGamePoints = loadGamePoints;
window.openGameSuccess = openGameSuccess;
window.claimMiniGamePoints = claimMiniGamePoints;
window.initPuzzle = initPuzzle;
window.promptResetPuzzle = promptResetPuzzle;
window.resetPuzzle = resetPuzzle;
window.promptChangePuzzle = promptChangePuzzle;
window.changePuzzle = changePuzzle;
window.openGameConfirm = openGameConfirm;
window.closeGameConfirm = closeGameConfirm;
window.executeGameConfirm = executeGameConfirm;
window.closeGameSuccess = closeGameSuccess;
window.submitTriviaAnswers = submitTriviaAnswers;
window.initMemoryGame = initMemoryGame;
window.submitScrambleAnswers = submitScrambleAnswers;
window.checkScrambleWord = checkScrambleWord;
window.closeGameAlert = closeGameAlert;
})();
</script>
