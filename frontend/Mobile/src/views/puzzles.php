<?php
$pageTitle = 'GameZone';
$backRoute = 'dashboard';
include __DIR__ . '/../components/header.php';
?>

<div id="gamezone-container" class="puzzles-container has-header animate-slide-up">

    <!-- Offline Status Badge -->
    <div id="puzzles-offline-badge" style="display: none; align-items: center; justify-content: center; gap: 8px; margin: 0 auto 14px auto; padding: 7px 16px; border-radius: 100px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35); color: #d97706; font-size: 11.5px; font-weight: 800; text-align: center; width: fit-content;">
        <i class="fa-solid fa-cloud-slash" style="color: #f59e0b;"></i>
        <span>Offline Mode &bull; Games Playable Offline (Rewards Queue Locally)</span>
    </div>

    <!-- Points Status Header -->
    <div
        style="background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border: none !important; outline: none !important; border-radius: 20px; padding: 16px 18px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; box-shadow: 0 4px 14px rgba(32, 63, 141, 0.28) !important;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div
                style="width: 44px; height: 44px; border-radius: 12px; background: #ffffff !important; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #203f8d !important; border: none !important; outline: none !important; box-shadow: 0 2px 8px rgba(0,0,0,0.12);">
                <i class="fa-solid fa-gamepad" style="color: #203f8d !important;"></i>
            </div>
            <div>
                <h4 style="margin: 0 0 2px 0; font-size: 11.5px; color: rgba(255, 255, 255, 0.85); font-weight: 600;">Your
                    Total Points</h4>
                <div style="display: flex; align-items: baseline; gap: 5px;">
                    <span id="game-points-val"
                        style="font-size: 18px; font-weight: 800; color: #ffffff; letter-spacing: -0.4px;">--</span>
                    <span style="font-size: 10.5px; color: rgba(255, 255, 255, 0.75); font-weight: 700;">PTS</span>
                </div>
            </div>
        </div>
        <button onclick="navigateTo('discount')"
            style="background: #ffffff !important; border: none !important; outline: none !important; color: #203f8d !important; padding: 6px 14px; border-radius: 100px; font-size: 11px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 5px; transition: 0.2s; box-shadow: 0 2px 8px rgba(0,0,0,0.12) !important;">
            Redeem <i class="fa-solid fa-arrow-right" style="font-size: 9px; color: #203f8d !important;"></i>
        </button>
    </div>

    <!-- Tab Selector with Sliding Pill Indicator -->
    <div id="game-tab-selector-bar"
        style="position: relative; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 4px; background: #f1f5f9 !important; border: none !important; outline: none !important; padding: 5px; border-radius: 16px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06) !important;">
        <div id="game-tab-indicator"></div>
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
        <div
            style="background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border: none !important; outline: none !important; border-radius: 20px; padding: 18px 20px; margin-bottom: 16px; text-align: center; box-shadow: 0 4px 14px rgba(32, 63, 141, 0.28) !important;">
            <h3 id="puzzle-title" style="margin: 0 0 4px 0; font-size: 14px; font-weight: 800; color: #ffffff;">Slide
                Puzzle</h3>
            <p id="puzzle-desc" style="margin: 0; font-size: 11px; color: rgba(255, 255, 255, 0.9); line-height: 1.35;">
                Rearrange the tiles to reveal the crystal clear lagoons of Immuki Island in Balaoan! Solve to earn
                <strong style="color: #38bdf8;">+50 Points</strong>.
            </p>

            <!-- Target Reference Image Preview -->
            <div id="puzzle-target-container" onclick="openTargetGoalPreviewModal()" role="button" tabindex="0"
                title="Tap to preview full reference image" aria-label="Tap to preview full reference image"
                style="margin-top: 8px; display: flex; align-items: center; justify-content: center; gap: 8px; background: rgba(0,0,0,0.22) !important; border: 1px solid rgba(255,255,255,0.2) !important; outline: none !important; border-radius: 12px; padding: 5px 12px; width: fit-content; margin-left: auto; margin-right: auto; cursor: pointer; transition: transform 0.15s ease, background 0.15s ease;">
                <span style="font-size: 11.5px; color: #ffffff; font-weight: 800; display: inline-flex; align-items: center; gap: 5px;">
                    Target Goal <i class="fa-solid fa-magnifying-glass-plus" style="font-size: 10.5px; color: #38bdf8;"></i>:
                </span>
                <img id="puzzle-target-img"
                    src="https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/tourist_spots/spot_6a686f4d0f48b.jpg"
                    alt="Target"
                    style="width: 48px; height: 48px; border-radius: 10px; object-fit: cover; border: 1.5px solid rgba(255,255,255,0.4) !important; outline: none !important; box-shadow: 0 4px 12px rgba(0,0,0,0.35);">
            </div>

            <!-- Moves and Timer info -->
            <div id="puzzle-stats-container"
                style="display: flex; justify-content: center; gap: 18px; margin-top: 10px;">
                <div style="font-size: 11.5px; color: rgba(255,255,255,0.85);">Moves: <span id="puzzle-moves"
                        style="font-weight: 800; color: #fff;">0</span></div>
                <div style="font-size: 11.5px; color: rgba(255,255,255,0.85);">Time: <span id="puzzle-timer"
                        style="font-weight: 800; color: #fff;">00:00</span></div>
            </div>

            <!-- Finished Banner -->
            <div id="puzzle-finished-banner"
                style="display: none; margin: 0; padding: 22px 16px; background: rgba(0,0,0,0.2); border-radius: 16px; text-align: center;">
                <div
                    style="width: 50px; height: 50px; border-radius: 50%; background: rgba(255,255,255,0.2); color: #38bdf8; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 12px;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <p style="margin: 0; font-size: 12.5px; font-weight: 700; color: #ffffff; line-height: 1.45;">
                    You have finished this mode please come back tomorrow.
                </p>
                <div style="margin-top: 9px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.18); padding: 4px 12px; border-radius: 100px; color: #38bdf8; font-size: 11px; font-weight: 700;">
                    <i class="fa-regular fa-clock" style="font-size: 11.5px; color: #38bdf8;"></i>
                    <span>Resets in: <strong class="game-reset-countdown" style="color: #ffffff; font-family: monospace; font-size: 11.5px; letter-spacing: 0.5px;">--h --m --s</strong></span>
                </div>
                <div style="margin-top: 14px; display: flex; justify-content: center;">
                    <button type="button" onclick="playInPracticeMode('puzzle')"
                        style="background: rgba(255,255,255,0.18) !important; border: 1px solid rgba(255,255,255,0.3) !important; outline: none !important; color: #ffffff !important; padding: 7px 14px; border-radius: 100px; font-size: 11px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                        <i class="fa-solid fa-gamepad" style="color: #38bdf8;"></i> Play Practice Mode
                    </button>
                </div>
            </div>
        </div>

        <!-- Puzzle Board Container Card (Matching Memory Match card style) -->
        <div id="puzzle-board-wrapper"
            style="background: #ffffff; border-radius: 22px; padding: 18px 12px; margin-bottom: 18px; box-shadow: 0 4px 18px rgba(32, 63, 141, 0.1); border: 1.5px solid #e2e8f0; display: flex; flex-direction: column; align-items: center;">
            <div id="puzzle-board"
                style="width: 308px; height: 308px; background: #0c1a30; border: 3px solid rgba(255, 255, 255, 0.45) !important; outline: 2px solid rgba(32, 63, 141, 0.6) !important; border-radius: 18px; position: relative; overflow: hidden; display: grid; grid-template-columns: repeat(3, 1fr); grid-template-rows: repeat(3, 1fr); gap: 4px; padding: 4px; box-sizing: border-box; box-shadow: 0 10px 28px rgba(32, 63, 141, 0.25);">
                <!-- 9 Grid items dynamic -->
            </div>

            <div id="puzzle-controls"
                style="display: flex; gap: 8px; justify-content: center; align-items: center; flex-wrap: wrap; margin-top: 16px; width: 100%;">
                <button onclick="promptResetPuzzle()"
                    style="background: #f1f5f9 !important; border: none !important; outline: none !important; color: #203f8d !important; padding: 8px 15px; border-radius: 12px; font-weight: 800; font-size: 11.5px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.06) !important;">
                    <i class="fa-solid fa-arrows-rotate" style="color: #203f8d !important; font-size: 11px;"></i> Reset Puzzle
                </button>
                <button onclick="promptChangePuzzle()"
                    style="background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border: none !important; outline: none !important; color: #ffffff !important; padding: 8px 15px; border-radius: 12px; font-weight: 800; font-size: 11.5px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 14px rgba(32, 63, 141, 0.28) !important;">
                    <i class="fa-solid fa-shuffle" style="color: #ffffff !important; font-size: 11px;"></i> Change Puzzle
                </button>
            </div>
        </div>
    </div>

    <!-- MEMORY MATCH TAB -->
    <div id="game-tab-memory" class="game-tab-content" style="display: none;">
        <div
            style="background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border: none !important; outline: none !important; border-radius: 20px; padding: 18px 20px; margin-bottom: 16px; text-align: center; box-shadow: 0 4px 14px rgba(32, 63, 141, 0.28) !important;">
            <h3 id="memory-title" style="margin: 0 0 4px 0; font-size: 14px; font-weight: 800; color: #ffffff;">Elyu
                Spot Memory Match</h3>
            <p id="memory-desc" style="margin: 0; font-size: 11px; color: rgba(255, 255, 255, 0.9); line-height: 1.35;">
                Flip cards and match all 6 pairs of famous La Union landmarks & activities to earn <strong
                    style="color: #38bdf8;">+25 Points</strong>!
            </p>
            <div id="memory-stats-container"
                style="display: flex; justify-content: center; gap: 18px; margin-top: 10px;">
                <div style="font-size: 11.5px; color: rgba(255,255,255,0.85);">Flips: <span id="memory-flips"
                        style="font-weight: 800; color: #fff;">0</span></div>
                <div style="font-size: 11.5px; color: rgba(255,255,255,0.85);">Pairs: <span id="memory-pairs"
                        style="font-weight: 800; color: #fff;">0/6</span></div>
            </div>

            <!-- Finished Banner -->
            <div id="memory-finished-banner"
                style="display: none; margin: 0; padding: 22px 16px; background: rgba(0,0,0,0.2); border-radius: 16px; text-align: center;">
                <div
                    style="width: 50px; height: 50px; border-radius: 50%; background: rgba(255,255,255,0.2); color: #38bdf8; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 12px;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <p style="margin: 0; font-size: 12.5px; font-weight: 700; color: #ffffff; line-height: 1.45;">
                    You have finished this mode please come back tomorrow.
                </p>
                <div style="margin-top: 9px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.18); padding: 4px 12px; border-radius: 100px; color: #38bdf8; font-size: 11px; font-weight: 700;">
                    <i class="fa-regular fa-clock" style="font-size: 11.5px; color: #38bdf8;"></i>
                    <span>Resets in: <strong class="game-reset-countdown" style="color: #ffffff; font-family: monospace; font-size: 11.5px; letter-spacing: 0.5px;">--h --m --s</strong></span>
                </div>
                <div style="margin-top: 14px; display: flex; justify-content: center;">
                    <button type="button" onclick="playInPracticeMode('memory_match')"
                        style="background: rgba(255,255,255,0.18) !important; border: 1px solid rgba(255,255,255,0.3) !important; outline: none !important; color: #ffffff !important; padding: 7px 14px; border-radius: 100px; font-size: 11px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                        <i class="fa-solid fa-gamepad" style="color: #38bdf8;"></i> Play Practice Mode
                    </button>
                </div>
            </div>
        </div>

        <!-- Memory Match Board Container Card -->
        <div id="memory-board-wrapper"
            style="background: #ffffff; border-radius: 22px; padding: 18px 12px; margin-bottom: 18px; box-shadow: 0 4px 18px rgba(32, 63, 141, 0.1); border: 1.5px solid #e2e8f0;">
            <div class="memory-grid" id="memory-board">
                <!-- 12 Cards Grid -->
            </div>

            <div id="memory-controls" style="display: flex; justify-content: center; margin-top: 16px;">
                <button onclick="promptResetMemory()"
                    style="background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border: none !important; outline: none !important; color: #fff; padding: 8px 20px; border-radius: 12px; font-weight: 800; font-size: 11.5px; cursor: pointer; box-shadow: 0 4px 14px rgba(32, 63, 141, 0.28) !important; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-arrows-rotate" style="font-size: 11px;"></i> Reset Game
                </button>
            </div>
        </div>
    </div>

    <!-- WORD SCRAMBLE TAB -->
    <div id="game-tab-scramble" class="game-tab-content" style="display: none;">
        <div
            style="background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border: none !important; outline: none !important; border-radius: 20px; padding: 18px 20px; margin-bottom: 16px; text-align: center; box-shadow: 0 4px 14px rgba(32, 63, 141, 0.28) !important;">
            <h3 id="scramble-title" style="margin: 0 0 4px 0; font-size: 14px; font-weight: 800; color: #ffffff;">La
                Union Eco Explorer Scramble</h3>
            <p id="scramble-desc"
                style="margin: 0; font-size: 11px; color: rgba(255, 255, 255, 0.92); line-height: 1.35;">
                Unscramble all 4 La Union municipal & landmark names to earn <strong style="color: #38bdf8;">+25
                    Points</strong>!
            </p>

            <!-- Finished Banner -->
            <div id="scramble-finished-banner"
                style="display: none; margin: 0; padding: 22px 16px; background: rgba(0,0,0,0.2); border-radius: 16px; text-align: center;">
                <div
                    style="width: 50px; height: 50px; border-radius: 50%; background: rgba(255,255,255,0.2); color: #38bdf8; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 12px;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <p style="margin: 0; font-size: 12.5px; font-weight: 700; color: #ffffff; line-height: 1.45;">
                    You have finished this mode please come back tomorrow.
                </p>
                <div style="margin-top: 9px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.18); padding: 4px 12px; border-radius: 100px; color: #38bdf8; font-size: 11px; font-weight: 700;">
                    <i class="fa-regular fa-clock" style="font-size: 11.5px; color: #38bdf8;"></i>
                    <span>Resets in: <strong class="game-reset-countdown" style="color: #ffffff; font-family: monospace; font-size: 11.5px; letter-spacing: 0.5px;">--h --m --s</strong></span>
                </div>
                <div style="margin-top: 14px; display: flex; justify-content: center;">
                    <button type="button" onclick="playInPracticeMode('word_scramble')"
                        style="background: rgba(255,255,255,0.18) !important; border: 1px solid rgba(255,255,255,0.3) !important; outline: none !important; color: #ffffff !important; padding: 7px 14px; border-radius: 100px; font-size: 11px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                        <i class="fa-solid fa-gamepad" style="color: #38bdf8;"></i> Play Practice Mode
                    </button>
                </div>
            </div>
        </div>


        <!-- Scramble Navigation, Slider & Controls Container Card (Matching Memory Match card style) -->
        <div id="scramble-board-wrapper"
            style="background: #ffffff; border-radius: 22px; padding: 18px 14px; margin-bottom: 18px; box-shadow: 0 4px 18px rgba(32, 63, 141, 0.1); border: 1.5px solid #e2e8f0;">
            <!-- Scramble Question Indicator & Step Dots -->
            <div id="scramble-nav-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; padding: 0 4px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span id="scramble-step-badge" style="font-size: 11.5px; font-weight: 800; color: #203f8d; background: #e0e7ff; padding: 4px 12px; border-radius: 100px;">
                        Word 1 of 4
                    </span>
                </div>
                <!-- 4 Dot Step Indicators -->
                <div id="scramble-dots" style="display: flex; align-items: center; gap: 6px;">
                    <span class="scramble-dot" style="width: 22px; height: 8px; border-radius: 10px; background: #203f8d; transition: all 0.25s ease;"></span>
                    <span class="scramble-dot" style="width: 8px; height: 8px; border-radius: 10px; background: #cbd5e1; transition: all 0.25s ease;"></span>
                    <span class="scramble-dot" style="width: 8px; height: 8px; border-radius: 10px; background: #cbd5e1; transition: all 0.25s ease;"></span>
                    <span class="scramble-dot" style="width: 8px; height: 8px; border-radius: 10px; background: #cbd5e1; transition: all 0.25s ease;"></span>
                </div>
            </div>

            <!-- Slider Viewport & Track -->
            <div id="scramble-slider-viewport" style="width: 100%; overflow: hidden; position: relative; margin: 0 0 16px 0; padding: 0; box-sizing: border-box; border-radius: 18px;">
                <div id="scramble-container" style="display: flex; flex-direction: row; flex-wrap: nowrap; width: 100%; margin: 0; padding: 0; gap: 0; box-sizing: border-box; transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1); will-change: transform;">
                    <!-- 4 Scrambled Words Slider -->
                </div>
            </div>

            <!-- Sliding Navigation Buttons -->
            <div id="scramble-slider-controls" style="display: flex; gap: 8px; align-items: center;">
                <button id="scramble-prev-btn" type="button" onclick="prevScrambleQuestion()"
                    style="display: none; background: #f1f5f9 !important; border: none !important; outline: none !important; color: #203f8d !important; padding: 10px 16px; border-radius: 12px; font-weight: 800; font-size: 12.5px; cursor: pointer; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
                    <i class="fa-solid fa-arrow-left" style="font-size: 11px;"></i> Prev
                </button>
                <button id="scramble-action-btn" type="button" onclick="handleScrambleActionClick()"
                    style="flex: 1; border: none !important; outline: none !important; background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; color: white; padding: 11px 18px; border-radius: 12px; font-weight: 800; font-size: 12.5px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 7px; box-shadow: 0 4px 14px rgba(32, 63, 141, 0.28) !important; transition: all 0.25s ease;">
                    <span id="scramble-action-btn-text">Next Word</span>
                    <i id="scramble-action-btn-icon" class="fa-solid fa-arrow-right" style="font-size: 11px;"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Confirm Modal popup -->
    <div id="game-confirm-modal" onclick="if(event.target===this) closeGameConfirm();"
        style="display: none; position: fixed; inset: 0; z-index: 10002; background: rgba(10, 25, 60, 0.72); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
        <div
            style="background: #ffffff !important; border: 1.5px solid #e2e8f0; border-radius: 24px; width: 100%; max-width: 340px; padding: 26px 20px 22px 20px; text-align: center; box-shadow: 0 20px 45px rgba(0, 0, 0, 0.28); position: relative; animation: popInGameModal 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards; box-sizing: border-box;">
            
            <div id="confirm-modal-icon-bg"
                style="width: 58px; height: 58px; border-radius: 50%; background: #eff6ff !important; border: none !important; outline: none !important; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #1e3a8a !important; margin: 0 auto 16px; box-shadow: 0 4px 14px rgba(30, 58, 138, 0.15);">
                <i id="confirm-modal-icon" class="fa-solid fa-arrows-rotate" style="color: #1e3a8a !important;"></i>
            </div>
            
            <h3 id="confirm-modal-title" style="margin: 0 0 8px; font-size: 19px; font-weight: 800; color: #0f172a; line-height: 1.3;">
                Reset Puzzle?
            </h3>
            
            <p id="confirm-modal-msg"
                style="margin: 0 0 22px; font-size: 13.5px; color: #64748b; line-height: 1.5; font-weight: 500;">
                Are you sure you want to reset puzzle?
            </p>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <button type="button" id="confirm-modal-cancel-btn" onclick="closeGameConfirm()"
                    style="padding: 12px 16px; border-radius: 14px; background: #f1f5f9; border: 1.5px solid #e2e8f0; color: #475569; font-size: 14px; font-weight: 800; cursor: pointer; transition: all 0.15s ease;"
                    onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'">
                    No
                </button>
                <button type="button" id="confirm-modal-action-btn" onclick="executeGameConfirm()"
                    style="padding: 12px 16px; border-radius: 14px; background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%) !important; border: none; color: #ffffff !important; font-size: 14px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35); transition: all 0.15s ease;"
                    onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'">
                    Yes, Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Success Modal popup -->
    <div id="game-success-modal" onclick="if(event.target===this) closeGameSuccess();"
        style="display: none; position: fixed; inset: 0; z-index: 10000; background: rgba(10, 25, 60, 0.72); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
        <div
            style="background: #ffffff !important; border: 1.5px solid #e2e8f0; border-radius: 24px; width: 100%; max-width: 340px; padding: 26px 20px 22px 20px; text-align: center; box-shadow: 0 20px 45px rgba(0, 0, 0, 0.28); position: relative; animation: popInGameModal 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards; box-sizing: border-box;">
            
            <div
                style="width: 58px; height: 58px; border-radius: 50%; background: #dcfce7 !important; border: none !important; outline: none !important; display: flex; align-items: center; justify-content: center; font-size: 28px; color: #16a34a !important; margin: 0 auto 12px; box-shadow: 0 4px 14px rgba(22, 163, 74, 0.2);">
                <i class="fa-solid fa-circle-check" style="color: #16a34a !important;"></i>
            </div>
            
            <h3 id="success-modal-title" style="margin: 0 0 10px; font-size: 20px; font-weight: 800; color: #0f172a; line-height: 1.3;">Awesome Job!</h3>

            <!-- Points Awarded Badge -->
            <div id="success-points-chip"
                style="display: inline-flex; align-items: center; gap: 8px; background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 100px; padding: 6px 18px; margin: 0 auto 12px auto; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.15);">
                <i class="fa-solid fa-star" style="color: #f59e0b; font-size: 14px;"></i>
                <span id="success-points-awarded-val" style="font-size: 16px; font-weight: 800; color: #047857; letter-spacing: -0.3px;">+50 PTS</span>
            </div>
            
            <p id="success-points-msg"
                style="margin: 0 0 14px; font-size: 13.5px; color: #475569; line-height: 1.5; font-weight: 600;">
                You solved the game and earned Points!
            </p>

            <!-- Come Back Tomorrow Notice Card -->
            <div id="success-comeback-card"
                style="background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 14px; padding: 12px 14px; margin: 0 0 14px 0; display: flex; align-items: center; justify-content: center; gap: 10px; text-align: left;">
                <div style="width: 32px; height: 32px; border-radius: 10px; background: #2563eb; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div>
                    <div style="font-size: 12.5px; font-weight: 800; color: #1e3a8a; line-height: 1.3;">Daily Reward Claimed!</div>
                    <div style="font-size: 11.5px; font-weight: 600; color: #2563eb; line-height: 1.35;">Please come back tomorrow for more reward points!</div>
                    <div style="font-size: 11.5px; font-weight: 700; color: #0284c7; margin-top: 4px; display: flex; align-items: center; gap: 5px;">
                        <i class="fa-regular fa-clock"></i> Resets in: <strong class="game-reset-countdown" style="font-family: monospace; font-size: 12px; color: #1e3a8a;">--h --m --s</strong>
                    </div>
                </div>
            </div>

            <!-- Total Points Balance Footer -->
            <div style="font-size: 12px; color: #64748b; font-weight: 600; margin-bottom: 18px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                <span>Total Balance:</span>
                <span id="success-total-points-val" style="font-size: 14.5px; font-weight: 800; color: #203f8d;">-- PTS</span>
            </div>
            
            <button type="button" onclick="closeGameSuccess()"
                style="width: 100%; padding: 13px 20px; border-radius: 14px; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%) !important; border: none; color: #ffffff !important; font-size: 14px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 14px rgba(22, 163, 74, 0.35); transition: all 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'">
                Awesome! Got It
            </button>
        </div>
    </div>

    <!-- Alert Modal popup -->
    <div id="game-alert-modal" onclick="if(event.target===this) closeGameAlert();"
        style="display: none; position: fixed; inset: 0; z-index: 10001; background: rgba(10, 25, 60, 0.72); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
        <div
            style="background: #ffffff !important; border: 1.5px solid #e2e8f0; border-radius: 24px; width: 100%; max-width: 340px; padding: 26px 20px 22px 20px; text-align: center; box-shadow: 0 20px 45px rgba(0, 0, 0, 0.28); position: relative; animation: popInGameModal 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards; box-sizing: border-box;">
            
            <div
                style="width: 58px; height: 58px; border-radius: 50%; background: #fef3c7 !important; border: none !important; outline: none !important; display: flex; align-items: center; justify-content: center; font-size: 28px; color: #d97706 !important; margin: 0 auto 12px; box-shadow: 0 4px 14px rgba(217, 119, 6, 0.2);">
                <i class="fa-solid fa-clock" style="color: #d97706 !important;"></i>
            </div>
            
            <h3 id="alert-modal-title" style="margin: 0 0 10px; font-size: 20px; font-weight: 800; color: #0f172a; line-height: 1.3;">Already Done!</h3>
            
            <p id="alert-points-msg"
                style="margin: 0 0 14px; font-size: 13.5px; color: #475569; line-height: 1.5; font-weight: 500;">
                You already completed this game today! Please come back tomorrow.
            </p>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px 12px; margin-bottom: 18px; font-size: 12px; color: #64748b; font-weight: 600;">
                <i class="fa-solid fa-circle-info" style="color: #0284c7; margin-right: 6px;"></i>
                Daily rewards reset every day at midnight!
            </div>
            
            <button type="button" onclick="closeGameAlert()"
                style="width: 100%; padding: 13px 20px; border-radius: 14px; background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%) !important; border: none; color: #ffffff !important; font-size: 14px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35); transition: all 0.15s ease;"
                onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'">
                Got it!
            </button>
        </div>
    </div>

    <style>
    @keyframes popInGameModal {
        0% { opacity: 0; transform: scale(0.92); }
        100% { opacity: 1; transform: scale(1); }
    }
    </style>

    <!-- Guest Login-Required Modal (guests may browse GameZone, but must log in to play any mode) -->
    <div id="gamezone-guest-restriction-overlay" onclick="if(event.target===this) closeGuestGameLoginModal();"
        style="display: none; position: fixed; inset: 0; z-index: 10005; background: rgba(10, 25, 60, 0.6) !important; backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); align-items: center; justify-content: center; padding: 22px; box-sizing: border-box;">
        <div style="background: #ffffff !important; border: 1px solid rgba(226, 232, 240, 0.9); border-radius: 26px; max-width: 370px; width: 100%; overflow: hidden; text-align: center; box-shadow: 0 20px 48px rgba(10, 25, 60, 0.4); box-sizing: border-box; animation: popInGameModal 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
            <!-- Header Banner -->
            <div style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%) !important; padding: 16px 18px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 10px; text-align: left;">
                    <div style="width: 34px; height: 34px; border-radius: 10px; background: rgba(255,255,255,0.16); display: flex; align-items: center; justify-content: center; color: #38bdf8; font-size: 16px;">
                        <i class="fa-solid fa-gamepad"></i>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 15.5px; font-weight: 800; color: #ffffff; letter-spacing: -0.3px;">Login Required</h3>
                        <span style="font-size: 11px; color: #bfdbfe; font-weight: 600;">You're browsing in Guest Mode</span>
                    </div>
                </div>
                <button type="button" onclick="closeGuestGameLoginModal()" aria-label="Close"
                    style="background: rgba(255,255,255,0.18) !important; border: none !important; outline: none !important; color: #ffffff !important; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 13px;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Body -->
            <div style="padding: 22px 20px 20px 20px;">
                <div style="width: 64px; height: 64px; border-radius: 20px; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1.5px solid #bfdbfe; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px auto; color: #1e3a8a; font-size: 28px; box-shadow: 0 8px 20px rgba(30, 58, 138, 0.15);">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <h4 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 800; color: #0f172a; letter-spacing: -0.4px;">Log in to Play</h4>
                <p style="margin: 0 0 16px 0; font-size: 13px; color: #475569; line-height: 1.5; font-weight: 500;">
                    You need to <strong>log in first</strong> before playing any GameZone mode &mdash; Slide Puzzle, Memory Match, or Word Scramble.
                </p>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 12px 14px; text-align: left; margin-bottom: 18px; display: flex; flex-direction: column; gap: 9px;">
                    <div style="display: flex; align-items: center; gap: 10px; font-size: 12px; color: #334155; font-weight: 700;">
                        <i class="fa-solid fa-coins" style="color: #f59e0b; width: 16px; text-align: center;"></i>
                        <span>Earn daily reward points from every mode</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; font-size: 12px; color: #334155; font-weight: 700;">
                        <i class="fa-solid fa-ticket" style="color: #0284c7; width: 16px; text-align: center;"></i>
                        <span>Redeem points for partner vouchers</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; font-size: 12px; color: #334155; font-weight: 700;">
                        <i class="fa-solid fa-ranking-star" style="color: #10b981; width: 16px; text-align: center;"></i>
                        <span>Climb the explorer leaderboard</span>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 9px;">
                    <button type="button" id="gamezone-guest-login-btn" onclick="closeGuestGameLoginModal(); navigateTo('auth');"
                        style="width: 100%; padding: 13px 20px; border-radius: 14px; background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%) !important; color: #ffffff !important; border: none !important; outline: none !important; font-size: 14px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35); transition: transform 0.15s ease; display: flex; align-items: center; justify-content: center; gap: 8px;"
                        onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i> Log In / Register
                    </button>
                    <button type="button" id="gamezone-guest-browse-btn" onclick="closeGuestGameLoginModal()"
                        style="width: 100%; padding: 12px 20px; border-radius: 14px; background: #ffffff !important; color: #64748b !important; border: 1.5px solid #cbd5e1 !important; outline: none !important; font-size: 13px; font-weight: 700; cursor: pointer; transition: transform 0.15s ease;"
                        onpointerdown="this.style.transform='scale(0.97)'" onpointerup="this.style.transform='scale(1)'">
                        Continue Browsing
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Target Goal Preview Modal (Reference Image Inspection) -->
    <div id="target-goal-preview-modal" onclick="if(event.target===this) closeTargetGoalPreviewModal();"
        style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(10,15,28,0.85); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:18px; box-sizing:border-box;">
        <div style="background:#ffffff; border-radius:24px; width:100%; max-width:360px; overflow:hidden; box-shadow:0 24px 60px rgba(0,0,0,0.45); animation:popInGameModal 0.22s cubic-bezier(0.16,1,0.3,1) forwards; display:flex; flex-direction:column;">
            <div style="padding:14px 18px; background:#1e3a8a; display:flex; align-items:center; justify-content:space-between; color:#ffffff;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-bullseye" style="color:#38bdf8; font-size:15px;"></i>
                    <strong style="font-size:14px; letter-spacing:-0.2px;">Target Goal Reference</strong>
                </div>
                <button type="button" onclick="closeTargetGoalPreviewModal()" aria-label="Close"
                    style="width:30px; height:30px; border-radius:50%; background:rgba(255,255,255,0.18); border:none; color:#ffffff; font-size:14px; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div style="padding:14px; background:#ffffff; text-align:center;">
                <img id="target-goal-preview-img" src="" alt="Target Goal"
                    style="width:100%; height:auto; max-height:280px; object-fit:cover; border-radius:14px; border:1.5px solid #e2e8f0; box-shadow:0 6px 18px rgba(0,0,0,0.08); display:block; margin:0 auto;">
                <div id="target-goal-preview-name" style="margin-top:12px; font-size:14px; font-weight:800; color:#0f172a;">
                    Spot Name
                </div>
                <div style="font-size:11.5px; color:#64748b; margin-top:3px; font-weight:500;">
                    Match your tiles to this exact photo to solve the puzzle!
                </div>
            </div>
            <div style="padding:12px 16px; background:#f8fafc; border-top:1px solid #e2e8f0; display:flex; justify-content:center;">
                <button type="button" onclick="closeTargetGoalPreviewModal()"
                    style="width:100%; height:42px; border-radius:14px; background:#1e3a8a; color:#ffffff; border:none; font-size:13px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; box-shadow:0 4px 12px rgba(30,58,138,0.25);">
                    <i class="fa-solid fa-puzzle-piece"></i> Got it, Back to Puzzle
                </button>
            </div>
        </div>
    </div>

</div>


<script>
    (function () {
        // Remove any lingering body-ported modals from a previous visit to GameZone
        document.querySelectorAll('body > #game-success-modal, body > #game-alert-modal, body > #game-confirm-modal, body > #target-goal-preview-modal').forEach(el => {
            try { el.remove(); } catch (e) {}
        });

        window._inFlightPointsClaims = Number(window._inFlightPointsClaims || 0);
        let _latestBalanceReqSeq = 0;

        // User profile and Guest mode restriction check
        let _authUser = {};
        try {
            _authUser = JSON.parse(localStorage.getItem('auth_user') || localStorage.getItem('intan_elyu_user') || '{}');
        } catch (e) { }

        const _isRealUser = Boolean(
            _authUser && 
            _authUser.id && 
            _authUser.id !== 'guest' && 
            _authUser.id !== 'offline_guest' && 
            !_authUser.is_guest && 
            !_authUser.is_offline_guest
        );

        if (_isRealUser) {
            try {
                localStorage.removeItem('intan_elyu_guest');
                localStorage.removeItem('intan_elyu_offline_guest');
            } catch (e) { }
        }

        const _authToken = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('api_token') || '';
        const _isGuest = (typeof window.isGuestUser === 'function' && window.isGuestUser()) ||
            (!_isRealUser && (
                !_authToken || 
                _authToken === 'guest_token' || 
                _authToken === 'offline_guest_token' || 
                _authToken.startsWith('guest_') || 
                localStorage.getItem('intan_elyu_guest') === 'true' || 
                localStorage.getItem('intan_elyu_offline_guest') === 'true'
            ));

        function resolveBestCachedPoints() {
            if (typeof window._userPointsBalance === 'number' && !isNaN(window._userPointsBalance) && window._userPointsBalance >= 0) {
                return window._userPointsBalance;
            }
            const curId = Number(_authUser.id || _authUser.user_id || 0);
            const tok = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('api_token') || '';
            const tokPrefix = tok ? tok.substring(0, 10) : '';
            const candidates = [];

            const pushTimestamped = (raw) => {
                if (!raw) return;
                try {
                    const parsed = JSON.parse(raw);
                    if (!parsed || typeof parsed !== 'object') return;
                    const ts = Number(parsed.timestamp || 0);
                    const data = (parsed.data && typeof parsed.data === 'object') ? parsed.data : parsed;
                    const dataUid = Number((data.user && data.user.id) || data.user_id || 0);
                    if (curId > 0 && dataUid > 0 && curId !== dataUid) return;
                    const rawPts = data.stats && data.stats.points !== undefined
                        ? data.stats.points
                        : (data.points !== undefined ? data.points : (data.user && data.user.points !== undefined ? data.user.points : undefined));
                    if (rawPts !== undefined && rawPts !== null && !isNaN(Number(rawPts))) {
                        candidates.push({ pts: Math.max(0, Number(rawPts)), ts: isNaN(ts) ? 0 : ts });
                    }
                } catch (e) {}
            };

            if (tokPrefix) {
                pushTimestamped(localStorage.getItem('points_balance_' + tokPrefix));
                pushTimestamped(localStorage.getItem('profile_data_' + tokPrefix));
                pushTimestamped(localStorage.getItem('dashboard_data_v4_' + tokPrefix));
                pushTimestamped(localStorage.getItem('dashboard_data_' + tokPrefix));
            }
            pushTimestamped(localStorage.getItem('dashboard_cached_payload'));

            if (candidates.length > 0) {
                candidates.sort((a, b) => b.ts - a.ts);
                return candidates[0].pts;
            }

            const fallbackRaw = _authUser.points !== undefined
                ? Number(_authUser.points)
                : (_authUser.total_points !== undefined ? Number(_authUser.total_points) : Number(_authUser.xp ?? 0));
            return (!isNaN(fallbackRaw) && fallbackRaw >= 0) ? fallbackRaw : 0;
        }

        // Guests may browse GameZone, but any attempt to play a mode opens the login-required modal.
        function showGuestGameLoginModal() {
            const guestModal = document.getElementById('gamezone-guest-restriction-overlay');
            if (guestModal) guestModal.style.setProperty('display', 'flex', 'important');
        }
        function closeGuestGameLoginModal() {
            const guestModal = document.getElementById('gamezone-guest-restriction-overlay');
            if (guestModal) guestModal.style.setProperty('display', 'none', 'important');
        }
        window.showGuestGameLoginModal = showGuestGameLoginModal;
        window.closeGuestGameLoginModal = closeGuestGameLoginModal;

        if (_isGuest) {
            // Never show a previous account's cached points to a guest
            const guestPtsEl = document.getElementById('game-points-val');
            if (guestPtsEl) guestPtsEl.textContent = '0';

            // Intercept every interaction inside the game play areas (boards, inputs, controls,
            // practice-mode buttons). Tab buttons remain usable so guests can preview each mode.
            const gzContainer = document.getElementById('gamezone-container');
            if (gzContainer) {
                const blockGuestPlay = (e) => {
                    const playArea = e.target && e.target.closest ? e.target.closest('.game-tab-content') : null;
                    if (!playArea) return;
                    e.preventDefault();
                    e.stopPropagation();
                    if (e.type === 'focusin' && typeof e.target.blur === 'function') e.target.blur();
                    if (e.type === 'click' || e.type === 'focusin') showGuestGameLoginModal();
                };
                ['pointerdown', 'mousedown', 'touchstart', 'click', 'focusin', 'keydown'].forEach(evt => {
                    gzContainer.addEventListener(evt, blockGuestPlay, { capture: true, passive: false });
                });
            }

            // Let the guest know up-front that playing requires an account
            setTimeout(showGuestGameLoginModal, 350);
        } else {
            // Immediately populate points for authenticated users from freshest in-memory/localStorage state
            const immediatePts = resolveBestCachedPoints();
            const ptsEl = document.getElementById('game-points-val');
            if (ptsEl && !isNaN(immediatePts)) {
                ptsEl.textContent = Number(immediatePts).toLocaleString();
            }
            if (typeof window._userPointsBalance !== 'number' || isNaN(window._userPointsBalance)) {
                window._userPointsBalance = immediatePts;
            }
        }

        // Load points balance
        async function loadGamePoints() {
            if (_isGuest) {
                const ptsEl = document.getElementById('game-points-val');
                if (ptsEl) ptsEl.textContent = '0';
                return;
            }
            try {
                const cachedPts = resolveBestCachedPoints();
                const ptsEl = document.getElementById('game-points-val');
                if (ptsEl && !isNaN(cachedPts)) {
                    ptsEl.textContent = Number(cachedPts).toLocaleString();
                }
            } catch (e) { }

            try {
                const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('api_token');
                if (!token) return;
                const _baseUrl = (typeof window.getBackendUrl === 'function'
                    ? window.getBackendUrl()
                    : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
                const claimsAtStart = Number(window._inFlightPointsClaims || 0);
                const reqSeq = ++_latestBalanceReqSeq;
                const r = await fetch(_baseUrl + '/api/tourist/points/balance?light=1&_t=' + Date.now(), {
                    cache: 'no-store',
                    headers: {
                        'Accept': 'application/json',
                        'Cache-Control': 'no-cache',
                        'ngrok-skip-browser-warning': 'true',
                        'Authorization': 'Bearer ' + token
                    }
                });
                const d = await r.json();
                if (reqSeq !== _latestBalanceReqSeq) return;
                if (d.status === 'success') {
                    const userPoints = Number(d.points !== undefined ? d.points : (d.xp ?? 0));
                    const hasActiveClaimRace = (Number(window._inFlightPointsClaims || 0) > 0 || claimsAtStart > 0) &&
                        typeof window._userPointsBalance === 'number' &&
                        window._userPointsBalance > userPoints;

                    if (!hasActiveClaimRace) {
                        const ptsEl = document.getElementById('game-points-val');
                        if (ptsEl) ptsEl.textContent = Number(userPoints).toLocaleString();
                        window._userPointsBalance = userPoints;

                        // Update auth_user and intan_elyu_user in localStorage so other pages have the latest points
                        try {
                            const authUser = JSON.parse(localStorage.getItem('auth_user') || '{}');
                            authUser.points = userPoints;
                            if (d.xp !== undefined) authUser.xp = Math.max(Number(d.xp) || 0, userPoints);
                            localStorage.setItem('auth_user', JSON.stringify(authUser));
                        } catch (e) { }
                        try {
                            const intanUser = JSON.parse(localStorage.getItem('intan_elyu_user') || '{}');
                            intanUser.points = userPoints;
                            if (d.xp !== undefined) intanUser.xp = Math.max(Number(d.xp) || 0, userPoints);
                            localStorage.setItem('intan_elyu_user', JSON.stringify(intanUser));
                        } catch (e) { }

                        if (typeof window.syncUserPointsEverywhere === 'function') {
                            window.syncUserPointsEverywhere(userPoints, null, { xp: d.xp });
                        } else if (window.updateProfilePointsDisplay) {
                            window.updateProfilePointsDisplay(userPoints);
                        }
                    }

                    // Reconcile today's completed games with server state
                    const todayLocalStr = new Date().toDateString();
                    const serverCompletedToday = {
                        puzzle: false,
                        memory_match: false,
                        word_scramble: false,
                        trivia: false
                    };

                    if (d.today_completed && typeof d.today_completed === 'object') {
                        Object.assign(serverCompletedToday, d.today_completed);
                    }

                    if (d.history && Array.isArray(d.history)) {
                        d.history.forEach(item => {
                            if (!item.created_at) return;
                            const itemDate = new Date(item.created_at);
                            if (!isNaN(itemDate.getTime()) && itemDate.toDateString() === todayLocalStr) {
                                const desc = (item.description || '').toLowerCase();
                                if (desc.includes('puzzle')) serverCompletedToday.puzzle = true;
                                if (desc.includes('memory')) serverCompletedToday.memory_match = true;
                                if (desc.includes('scramble')) serverCompletedToday.word_scramble = true;
                                if (desc.includes('trivia')) serverCompletedToday.trivia = true;
                            }
                        });
                    }

                    // Retain daily completion if server verified OR if completed locally today
                    ['puzzle', 'memory_match', 'word_scramble', 'trivia'].forEach(gameType => {
                        const key = getGameDoneKey(gameType);
                        const localVal = localStorage.getItem(key);
                        if (serverCompletedToday[gameType] || localVal === todayLocalStr) {
                            localStorage.setItem(key, todayLocalStr);
                        } else if (localVal && localVal !== todayLocalStr) {
                            // Only clear stale locks from prior days
                            localStorage.removeItem(key);
                        }
                        // Purge legacy un-scoped key
                        localStorage.removeItem('game_done_' + gameType);
                    });

                    updateAllGamesFinishedUI();
                }
            } catch (e) {
                console.error("Points load error:", e);
            }
        }

        let currentActiveTab = 'puzzle';

        // Switching between tabs
        function switchGameTab(tabName) {
            currentActiveTab = tabName;
            document.querySelectorAll('.game-tab-content').forEach(el => {
                el.style.display = 'none';
                el.classList.remove('game-tab-animate');
            });
            document.querySelectorAll('.game-nav-tab').forEach(el => el.classList.remove('active'));

            const activeTabEl = document.getElementById('game-tab-' + tabName);
            const activeBtnEl = document.getElementById('tab-btn-' + tabName);
            const indicator = document.getElementById('game-tab-indicator');

            if (indicator && activeBtnEl) {
                indicator.style.width = activeBtnEl.offsetWidth + 'px';
                const offset = activeBtnEl.offsetLeft - 5;
                indicator.style.transform = `translateX(${offset}px)`;
            }

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

        window.addEventListener('resize', () => {
            const indicator = document.getElementById('game-tab-indicator');
            const activeBtnEl = document.getElementById('tab-btn-' + currentActiveTab);
            if (indicator && activeBtnEl) {
                indicator.style.width = activeBtnEl.offsetWidth + 'px';
                const offset = activeBtnEl.offsetLeft - 5;
                indicator.style.transform = `translateX(${offset}px)`;
            }
        });

        // ----------------------------------------------------
        // SLIDING PUZZLE LOGIC (Randomized La Union Spot Images from Cloudflare R2)
        // ----------------------------------------------------
        const R2_PUBLIC_BASE = 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/tourist_spots/';

        const LOCAL_PUZZLE_IMAGES = [
            { name: 'Ma-Cho Temple', location: 'San Fernando', image: 'assets/img/onboarding/ma-cho-temple.webp' },
            { name: 'La Union Waterfall', location: 'La Union', image: 'assets/img/onboarding/la-union-waterfall.jpg' },
            { name: 'Baluarte Watchtower', location: 'Luna', image: 'assets/img/onboarding/baluarte-watchtower.jpg' },
            { name: 'Elyu Coast at Blue Hour', location: 'La Union', image: 'assets/img/onboarding/elyu-coast-blue-hour.jpg' },
            { name: 'Elyu Island Coast', location: 'La Union', image: 'assets/img/onboarding/elyu-coast-island.jpg' },
            { name: 'Elyu Coast in the Morning', location: 'La Union', image: 'assets/img/onboarding/elyu-coast-morning.jpg' }
        ].map(item => ({
            ...item,
            desc: `Rearrange the tiles to reveal ${item.name}! Solve to earn <strong style="color: #38bdf8;">+50 Points</strong>.`
        }));

        const PUZZLE_IMAGES = [
            {
                name: "Immuki Island",
                location: "Balaoan",
                image: R2_PUBLIC_BASE + "spot_6a686f4d0f48b.jpg",
                desc: 'Rearrange the tiles to reveal the crystal clear lagoons of Immuki Island in Balaoan! Solve to earn <strong style="color: #38bdf8;">+50 Points</strong>.'
            },
            {
                name: "Agoo Eco Fun World",
                location: "Agoo",
                image: R2_PUBLIC_BASE + "spot_6a689fe582fe5.jpg",
                desc: 'Rearrange the tiles to reveal the lush pine trees of Agoo Eco Fun World! Solve to earn <strong style="color: #38bdf8;">+50 Points</strong>.'
            },
            ...LOCAL_PUZZLE_IMAGES
        ];

        const PUZZLE_FALLBACK_IMAGES = LOCAL_PUZZLE_IMAGES.map(item => item.image);

        function getPuzzleFallbackImage(item) {
            const seed = String(item?.name || item?.location || 'elyu');
            const index = [...seed].reduce((sum, char) => sum + char.charCodeAt(0), 0) % PUZZLE_FALLBACK_IMAGES.length;
            return PUZZLE_FALLBACK_IMAGES[index];
        }

        let dbPuzzleSpots = [];
        let tiles = [];
        let moves = 0;
        let timeSec = 0;
        let puzzleSolved = false;
        let pendingConfirmAction = null;

        function normalizePuzzleSpots(spots, baseUrl = '') {
            if (!Array.isArray(spots)) return [];
            return spots.flatMap(spot => {
                const images = [...new Set([
                    spot.image,
                    spot.photo_url,
                    spot.cover_image,
                    ...(Array.isArray(spot.images) ? spot.images : [])
                ].filter(Boolean))];

                return images.map((image, imageIndex) => {
                    let imageUrl = image;
                    if (typeof window.resolveImageUrl === 'function') {
                        imageUrl = window.resolveImageUrl(imageUrl);
                    } else if (imageUrl && imageUrl.includes('spot_')) {
                        const match = imageUrl.match(/(spot_[a-z0-9_]+\.(?:jpg|jpeg|png|webp|gif))/i);
                        if (match) imageUrl = R2_PUBLIC_BASE + match[1];
                    } else if (imageUrl && !imageUrl.startsWith('http') && !imageUrl.startsWith('data:')) {
                        imageUrl = baseUrl + imageUrl;
                    }

                    return {
                        id: spot.id ? `${spot.id}-${imageIndex}` : `${spot.name || 'spot'}-${imageIndex}`,
                        name: spot.name || 'La Union Tourist Site',
                        location: spot.location || spot.municipality || 'La Union',
                        image: imageUrl,
                        desc: (spot.desc || `Rearrange the tiles to reveal ${spot.name || 'this La Union tourist site'}! Solve to earn <strong style="color: #38bdf8;">+50 Points</strong>.`).replace(/\+(?:100|30)\s*Points/gi, '+50 Points')
                    };
                });
            }).filter(spot => spot.image);
        }

        function selectNextPuzzle(pool, avoidCurrent = true) {
            if (!pool || !pool.length) return null;
            if (pool.length === 1) return pool[0];
            if (avoidCurrent && currentPuzzleItem) {
                const candidates = pool.filter(item => 
                    item.image !== currentPuzzleItem.image && 
                    item.name !== currentPuzzleItem.name
                );
                if (candidates.length > 0) {
                    const rnd = Math.floor(Math.random() * candidates.length);
                    return candidates[rnd];
                }
            }
            const rnd = Math.floor(Math.random() * pool.length);
            return pool[rnd];
        }

        let currentPuzzleItem = selectNextPuzzle(PUZZLE_IMAGES, false);

        async function fetchDatabasePuzzleSpots() {
            try {
                const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('api_token');
                const headers = { 'Accept': 'application/json' };
                if (token) headers['Authorization'] = 'Bearer ' + token;

                const _baseUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || window.location.origin)).replace(/\/+$/, '');
                let res = await fetch(_baseUrl + '/api/puzzles/spots?_t=' + Date.now(), { headers, cache: 'no-store' });
                if (!res.ok) {
                    res = await fetch(_baseUrl + '/api/tourist/puzzles/spots?_t=' + Date.now(), { headers, cache: 'no-store' });
                }

                if (res.ok) {
                    const data = await res.json();
                    if (data.status === 'success' && Array.isArray(data.spots) && data.spots.length > 0) {
                        dbPuzzleSpots = normalizePuzzleSpots(data.spots, _baseUrl);
                        // Pick a random real tourist spot from the database catalog
                        currentPuzzleItem = selectNextPuzzle(dbPuzzleSpots, false);
                        initPuzzle(false);
                    }
                }
            } catch (e) {
                console.warn('Could not fetch database puzzle spots:', e);
            }

            // Offline resilience fallback
            if (!dbPuzzleSpots.length) {
                try {
                    const cached = JSON.parse(localStorage.getItem('intan_elyu_cached_spots_map') || '[]');
                    const localSpots = Array.isArray(cached)
                        ? cached
                        : (cached?.data?.destinations || cached?.data?.spots || cached?.destinations || cached?.spots || []);
                    if (localSpots.length > 0) {
                        dbPuzzleSpots = normalizePuzzleSpots(localSpots);
                        if (dbPuzzleSpots.length > 0) {
                            currentPuzzleItem = selectNextPuzzle(dbPuzzleSpots, false);
                            initPuzzle(false);
                        }
                    }
                } catch (e) { }
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
            } while (!isSolvable(tiles) || tiles.every((v, i) => v === correctLayout[i]));
        }

        window._practiceModes = window._practiceModes || {};

        function playInPracticeMode(gameType) {
            window._practiceModes = window._practiceModes || {};
            window._practiceModes[gameType] = true;

            if (gameType === 'puzzle') {
                updatePuzzleInfoUI();
                initPuzzle(true);
            } else if (gameType === 'memory_match') {
                updateMemoryInfoUI();
                initMemoryGame();
            } else if (gameType === 'word_scramble') {
                updateScrambleInfoUI();
                initScrambleGame();
            }
        }

        function getGameDoneKey(gameType) {
            try {
                const u = JSON.parse(localStorage.getItem('auth_user') || '{}');
                const uid = u.id || u.user_id || '';
                if (uid) return `game_done_${gameType}_user_${uid}`;
            } catch (e) { }
            const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('api_token') || 'guest';
            return `game_done_${gameType}_${token.substring(0, 10)}`;
        }

        function isGameDoneToday(gameType) {
            if (window._practiceModes && window._practiceModes[gameType]) {
                return false;
            }
            try {
                const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('api_token');
                if (!token || token === 'guest_token' || token.startsWith('offline_') || token.startsWith('guest_')) return false;
                const key = getGameDoneKey(gameType);
                return localStorage.getItem(key) === new Date().toDateString();
            } catch (e) {
                return false;
            }
        }

        function updateAllGamesFinishedUI() {
            updatePuzzleInfoUI();
            updateMemoryInfoUI();
            updateScrambleInfoUI();
        }

        function updatePuzzleInfoUI() {
            const titleEl = document.getElementById('puzzle-title');
            const descEl = document.getElementById('puzzle-desc');
            const targetContainer = document.getElementById('puzzle-target-container');
            const statsContainer = document.getElementById('puzzle-stats-container');
            const boardWrapper = document.getElementById('puzzle-board-wrapper');
            const controlsWrapper = document.getElementById('puzzle-controls');
            const finishedBanner = document.getElementById('puzzle-finished-banner');

            const isDone = isGameDoneToday('puzzle');

            if (isDone) {
                // User requested: "dont show Immuki Island Slide Puzzle" and "dont show any image, and the text will hide and show You have finished this mode please come back tomorrow"
                if (titleEl) titleEl.style.display = 'none';
                if (descEl) descEl.style.display = 'none';
                if (targetContainer) targetContainer.style.display = 'none';
                if (statsContainer) statsContainer.style.display = 'none';
                if (boardWrapper) boardWrapper.style.display = 'none';
                if (controlsWrapper) controlsWrapper.style.display = 'none';
                if (finishedBanner) finishedBanner.style.display = 'block';
                if (window._puzzleTimerInterval) clearInterval(window._puzzleTimerInterval);
            } else {
                if (titleEl) {
                    titleEl.style.display = 'block';
                    titleEl.textContent = 'Slide Puzzle';
                }
                if (descEl && currentPuzzleItem) {
                    descEl.style.display = 'block';
                    descEl.innerHTML = (currentPuzzleItem.desc || '').replace(/\+(?:100|30)\s*Points/gi, '+50 Points');
                }
                if (targetContainer) targetContainer.style.display = 'flex';
                const targetImgEl = document.getElementById('puzzle-target-img');
                if (targetImgEl && currentPuzzleItem) {
                    targetImgEl.onerror = () => {
                        targetImgEl.onerror = null;
                        currentPuzzleItem.resolvedImage = getPuzzleFallbackImage(currentPuzzleItem);
                        targetImgEl.src = currentPuzzleItem.resolvedImage;
                        renderPuzzleBoard();
                    };
                    targetImgEl.src = currentPuzzleItem.resolvedImage || currentPuzzleItem.image || getPuzzleFallbackImage(currentPuzzleItem);
                }
                if (statsContainer) statsContainer.style.display = 'flex';
                if (boardWrapper) boardWrapper.style.display = 'flex';
                if (controlsWrapper) controlsWrapper.style.display = 'flex';
                if (finishedBanner) finishedBanner.style.display = 'none';
            }
        }

        function updateMemoryInfoUI() {
            const titleEl = document.getElementById('memory-title');
            const descEl = document.getElementById('memory-desc');
            const statsContainer = document.getElementById('memory-stats-container');
            const boardEl = document.getElementById('memory-board');
            const controlsEl = document.getElementById('memory-controls');
            const boardWrapper = document.getElementById('memory-board-wrapper');
            const finishedBanner = document.getElementById('memory-finished-banner');

            const isDone = isGameDoneToday('memory_match');

            if (isDone) {
                if (titleEl) titleEl.style.display = 'none';
                if (descEl) descEl.style.display = 'none';
                if (statsContainer) statsContainer.style.display = 'none';
                if (boardEl) boardEl.style.display = 'none';
                if (controlsEl) controlsEl.style.display = 'none';
                if (boardWrapper) boardWrapper.style.display = 'none';
                if (finishedBanner) finishedBanner.style.display = 'block';
            } else {
                if (titleEl) titleEl.style.display = 'block';
                if (descEl) {
                    descEl.style.display = 'block';
                    descEl.innerHTML = `Flip cards and match all 6 pairs of famous La Union landmarks & activities to earn <strong style="color: #38bdf8;">+25 Points</strong>!`;
                }
                if (statsContainer) statsContainer.style.display = 'flex';
                if (boardEl) boardEl.style.display = 'grid';
                if (controlsEl) controlsEl.style.display = 'flex';
                if (boardWrapper) boardWrapper.style.display = 'block';
                if (finishedBanner) finishedBanner.style.display = 'none';
            }
        }

        function updateScrambleInfoUI() {
            const titleEl = document.getElementById('scramble-title');
            const descEl = document.getElementById('scramble-desc');
            const navHeader = document.getElementById('scramble-nav-header');
            const sliderViewport = document.getElementById('scramble-slider-viewport');
            const sliderControls = document.getElementById('scramble-slider-controls');
            const containerEl = document.getElementById('scramble-container');
            const submitBtn = document.getElementById('scramble-submit-btn');
            const finishedBanner = document.getElementById('scramble-finished-banner');
            const mechanicsEl = document.getElementById('scramble-mechanics-wrapper');
            const statsBarEl = document.getElementById('scramble-stats-bar');
            const boardWrapper = document.getElementById('scramble-board-wrapper');

            const isDone = isGameDoneToday('word_scramble');

            if (isDone) {
                if (titleEl) titleEl.style.display = 'none';
                if (descEl) descEl.style.display = 'none';
                if (boardWrapper) boardWrapper.style.display = 'none';
                if (navHeader) navHeader.style.display = 'none';
                if (sliderViewport) sliderViewport.style.display = 'none';
                if (sliderControls) sliderControls.style.display = 'none';
                if (containerEl) containerEl.style.display = 'none';
                if (submitBtn) submitBtn.style.display = 'none';
                if (mechanicsEl) mechanicsEl.style.display = 'none';
                if (statsBarEl) statsBarEl.style.display = 'none';
                if (finishedBanner) finishedBanner.style.display = 'block';
            } else {
                if (titleEl) titleEl.style.display = 'block';
                if (descEl) {
                    descEl.style.display = 'block';
                    descEl.innerHTML = `Unscramble all 4 La Union municipal & landmark names to earn <strong style="color: #00f2fe;">+25 Points</strong>!`;
                }
                if (boardWrapper) boardWrapper.style.display = 'block';
                if (navHeader) navHeader.style.display = 'flex';
                if (sliderViewport) sliderViewport.style.display = 'block';
                if (sliderControls) sliderControls.style.display = 'flex';
                if (containerEl) containerEl.style.display = 'flex';
                if (submitBtn) submitBtn.style.display = 'none';
                if (mechanicsEl) mechanicsEl.style.display = 'block';
                if (statsBarEl) statsBarEl.style.display = 'none';
                if (finishedBanner) finishedBanner.style.display = 'none';
            }
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
            if (isGameDoneToday('puzzle')) {
                updatePuzzleInfoUI();
                return;
            }

            const pool = (dbPuzzleSpots && dbPuzzleSpots.length > 0) ? dbPuzzleSpots : PUZZLE_IMAGES;
            if (forceNewImage || !currentPuzzleItem) {
                currentPuzzleItem = selectNextPuzzle(pool, true);
            }

            updatePuzzleInfoUI();
            resetPuzzleState();
        }

        function promptResetPuzzle() {
            if (isGameDoneToday('puzzle')) {
                return;
            }

            openGameConfirm({
                title: 'Reset Puzzle?',
                message: 'Are you sure you want to reset puzzle?',
                icon: 'fa-arrows-rotate',
                iconColor: '#1e3a8a',
                iconBg: '#eff6ff',
                confirmText: 'Yes, Reset',
                confirmBg: 'linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%)',
                onConfirm: resetPuzzle
            });
        }

        function resetPuzzle() {
            // Keeps the same image, only rearranges the tiles and resets moves/timer
            resetPuzzleState();
        }

        function promptChangePuzzle() {
            if (isGameDoneToday('puzzle')) {
                return;
            }

            openGameConfirm({
                title: 'Change Puzzle?',
                message: 'Are you sure you want to change puzzle? A new image will be loaded.',
                icon: 'fa-shuffle',
                iconColor: '#1e3a8a',
                iconBg: '#eff6ff',
                confirmText: 'Yes, Change',
                confirmBg: 'linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%)',
                onConfirm: changePuzzle
            });
        }

        function promptResetMemory() {
            if (isGameDoneToday('memory_match')) {
                return;
            }
            if (memoryFlips === 0 && matchedPairsCount === 0) {
                initMemoryGame();
                return;
            }
            openGameConfirm({
                title: 'Reset Memory Game?',
                message: 'Are you sure you want to reset your memory cards match?',
                icon: 'fa-arrows-rotate',
                iconColor: '#1e3a8a',
                iconBg: '#eff6ff',
                confirmText: 'Yes, Reset',
                confirmBg: 'linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%)',
                onConfirm: initMemoryGame
            });
        }

        function changePuzzle() {
            const pool = (dbPuzzleSpots && dbPuzzleSpots.length > 0) ? dbPuzzleSpots : PUZZLE_IMAGES;
            currentPuzzleItem = selectNextPuzzle(pool, true);

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
                    const primaryImage = currentPuzzleItem.resolvedImage || currentPuzzleItem.image || getPuzzleFallbackImage(currentPuzzleItem);
                    const fallbackImage = getPuzzleFallbackImage(currentPuzzleItem);
                    div.style.backgroundImage = `url("${primaryImage}"), url("${fallbackImage}")`;
                    div.style.backgroundSize = '300px 300px, 300px 300px';
                    const x = (val % 3) * 100;
                    const y = Math.floor(val / 3) * 100;
                    div.style.backgroundPosition = `-${x}px -${y}px, -${x}px -${y}px`;
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
                if (isGameDoneToday('puzzle')) {
                    updatePuzzleInfoUI();
                } else {
                    claimMiniGamePoints('puzzle');
                }
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
            if (isGameDoneToday('trivia')) {
                return;
            }
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
                openGameAlert("Please answer all questions before submitting!", "Notice");
                return;
            }

            const allCorrect = triviaData.every(q => q.selected === q.correct);
            if (!allCorrect) {
                openGameAlert("Some answers are incorrect. Try again! Hint: Think surfing in San Juan, watchtowers in Luna, and native Ilokano speakers.", "Not Quite Right");
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
            if (isGameDoneToday('memory_match')) {
                updateMemoryInfoUI();
                return;
            }
            updateMemoryInfoUI();
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
                    <span style="font-size:9px; font-weight:800; text-transform:uppercase; letter-spacing:0.3px;">${card.label}</span>
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
        // WORD SCRAMBLE GAME LOGIC (Dynamic Randomization Engine)
        // ----------------------------------------------------
        const laUnionWordPool = [
            { answer: "SAN JUAN", hint: "Surfing Capital of Northern Luzon & Urbiztondo Beach" },
            { answer: "SAN GABRIEL", hint: "Home to the famous Tangadan Falls and highland cold streams" },
            { answer: "BAUANG", hint: "Famous for lush grape picking farms and sunset beach resorts" },
            { answer: "BALUARTE", hint: "Historic 400-year-old Spanish brick watchtower standing in Luna" },
            { answer: "TANGADAN", hint: "Majestic two-tiered waterfall in San Gabriel with natural swimming pools" },
            { answer: "IMMUKI", hint: "Enchanting mangrove lagoon and emerald tidal pools in Balaoan" },
            { answer: "BALAOAN", hint: "Coastal municipality home to Immuki Island and crystal clear waters" },
            { answer: "AGOO", hint: "Historic town with Basilica of Our Lady of Charity & Eco Fun World" },
            { answer: "LUNA", hint: "Municipality known for stone handcrafts, Baluarte, and pebble beaches" },
            { answer: "BACNOTAN", hint: "Center of beekeeping, sericulture, and Quirino Protected Landscape" },
            { answer: "SAN FERNANDO", hint: "Provincial capital of La Union, home to Pindangan Ruins & Ma-Cho Temple" },
            { answer: "PINDANGAN", hint: "Historic 18th-century Spanish church ruins made of coral stone" },
            { answer: "MA CHO TEMPLE", hint: "Iconic Taoist temple built in 1975 overlooking San Fernando Bay" },
            { answer: "ARINGAY", hint: "Coastal town celebrated for dried fish, artisanal salt-making, and river trails" },
            { answer: "CABA", hint: "Birthplace of hero Diego Silang, known for bamboo crafts and rice fields" },
            { answer: "TUBAO", hint: "Lush inland valley town celebrated for fresh sweet pineapples & tobacco" },
            { answer: "PUGO", hint: "Southern highland gateway known for Tapuacan River and adventure eco-parks" },
            { answer: "ROSARIO", hint: "Southern gateway of La Union connecting major mountain trail expressways" },
            { answer: "BAGULIN", hint: "Highland indigenous eco-tourism town with Kedlap Cave & Loslosi Falls" },
            { answer: "BURGOS", hint: "Scenic highland town tucked in foothills featuring Bolikewkew Falls" },
            { answer: "SANTO TOMAS", hint: "Famous coastal fishing municipality known for sun-dried daing" },
            { answer: "SUDIPEN", hint: "Northern border town with the scenic Amburayan River & hanging bridge" },
            { answer: "SANTOL", hint: "Mountain peaks, cool highland climate, and pristine Simminublan Falls" },
            { answer: "TAPUACAN", hint: "Known as the cleanest inland river in Region 1, located in Pugo" },
            { answer: "PEBBLE BEACH", hint: "Luna's world-famous picturesque shoreline covered in multi-colored stones" },
            { answer: "GRAPE FARM", hint: "Bauang's popular agritourism attraction where visitors harvest fresh sweet grapes" }
        ];

        let currentScrambleWords = [];

        // Helper to scramble a single word token (Fisher-Yates)
        function shuffleLetters(token) {
            if (!token || token.length <= 1) return token;
            const chars = token.split('');
            let shuffled = token;
            let attempts = 0;
            while (shuffled === token && attempts < 25) {
                for (let i = chars.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [chars[i], chars[j]] = [chars[j], chars[i]];
                }
                shuffled = chars.join('');
                attempts++;
            }
            return shuffled;
        }

        // Scramble a phrase (handles multi-word names while keeping spaces recognizable)
        function scramblePhrase(phrase) {
            return phrase
                .split(' ')
                .map(token => shuffleLetters(token))
                .join(' ');
        }

        // Normalize strings for user-friendly comparison (ignores spaces, hyphens, and case)
        function normalizeScrambleText(str) {
            return (str || '').toString().trim().toUpperCase().replace(/[^A-Z0-9]/g, '');
        }

        // Toggle game mechanics accordion
        function toggleScrambleMechanics() {
            const content = document.getElementById('scramble-mechanics-content');
            const icon = document.getElementById('scramble-mechanics-icon');
            if (!content) return;

            if (content.style.display === 'none' || content.style.display === '') {
                content.style.display = 'flex';
                if (icon) icon.style.transform = 'rotate(180deg)';
            } else {
                content.style.display = 'none';
                if (icon) icon.style.transform = 'rotate(0deg)';
            }
        }

        // Draw 4 random locations from the pool and scramble them
        function randomizeScrambleWords() {
            if (isGameDoneToday('word_scramble')) {
                updateScrambleInfoUI();
                return;
            }

            // Pick 4 unique items
            const poolCopy = [...laUnionWordPool];
            for (let i = poolCopy.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [poolCopy[i], poolCopy[j]] = [poolCopy[j], poolCopy[i]];
            }

            const selected = poolCopy.slice(0, 4);
            currentScrambleWords = selected.map((item, idx) => {
                let scrambled = scramblePhrase(item.answer);
                if (scrambled === item.answer && item.answer.replace(/\s+/g, '').length > 1) {
                    scrambled = scramblePhrase(item.answer);
                }
                return {
                    id: idx + 1,
                    answer: item.answer,
                    scrambled: scrambled,
                    hint: item.hint
                };
            });

            renderScrambleCards();
            updateScrambleProgress();
        }

        let currentScrambleIndex = 0;

        // Render the 4 scramble cards to the DOM (Carousel / Slider)
        function renderScrambleCards() {
            const container = document.getElementById('scramble-container');
            if (!container) return;
            container.innerHTML = '';

            const viewport = document.getElementById('scramble-slider-viewport');
            if (viewport) {
                viewport.scrollLeft = 0;
                if (!viewport._hasScrollGuard) {
                    viewport._hasScrollGuard = true;
                    viewport.addEventListener('scroll', () => {
                        if (viewport.scrollLeft !== 0) viewport.scrollLeft = 0;
                    }, { passive: true });
                }
            }

            currentScrambleWords.forEach((item, index) => {
                const card = document.createElement('div');
                card.className = 'scramble-card';
                card.id = `scramble-card-${index}`;
                card.style.flex = '0 0 100%';
                card.style.width = '100%';
                card.style.minWidth = '100%';
                card.style.maxWidth = '100%';
                card.style.flexShrink = '0';
                card.style.margin = '0';
                card.style.boxSizing = 'border-box';

                card.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span style="font-size: 11px; font-weight: 800; color: #00f2fe; text-transform: uppercase; letter-spacing: 0.8px;">
                    Word #${index + 1}
                </span>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <button type="button" class="scramble-reshuffle-btn" onclick="reshuffleWordLetters(${index})" title="Reshuffle scrambled letters" style="width: 28px; height: 28px; font-size: 11px;">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </button>
                    <span id="scramble-status-${index}" style="font-size: 13px; color: rgba(255,255,255,0.4);">
                        <i class="fa-solid fa-pen"></i>
                    </span>
                </div>
            </div>
            <div id="scramble-word-display-${index}" style="font-size: 18px; font-weight: 900; color: #ffffff !important; letter-spacing: 3px; margin-bottom: 10px; text-align: center; background: rgba(0,0,0,0.28) !important; padding: 10px; border-radius: 12px; border: none !important; outline: none !important; text-shadow: 0 2px 4px rgba(0,0,0,0.4); user-select: none; word-break: break-word; overflow-wrap: break-word;">
                ${item.scrambled}
            </div>
            <p style="margin: 0 0 10px 0; font-size: 11.5px; color: #ffffff !important; font-weight: 600; text-shadow: 0 1px 2px rgba(0,0,0,0.4); line-height: 1.4;">
                <span style="color: #facc15; font-size: 12px; font-weight: 800;">💡 Hint:</span> <span style="color: #ffffff !important; font-weight: 700;">${item.hint}</span>
            </p>
            <input type="text" id="scramble-input-${index}" class="scramble-input" placeholder="TYPE ANSWER HERE..." oninput="checkScrambleWord(${index})" onkeydown="if(event.key==='Enter'){event.preventDefault();handleScrambleActionClick();}" autocomplete="off" autocapitalize="characters" spellcheck="false" style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; border: none !important; outline: none !important;">
        `;

                container.appendChild(card);
            });

            goToScrambleQuestion(0);
        }

        // Navigate between Word Scramble questions
        function goToScrambleQuestion(index) {
            if (index < 0 || index >= currentScrambleWords.length) return;
            currentScrambleIndex = index;

            const viewport = document.getElementById('scramble-slider-viewport');
            if (viewport) {
                viewport.scrollLeft = 0;
            }

            const container = document.getElementById('scramble-container');
            if (container) {
                container.style.transform = `translateX(-${currentScrambleIndex * 100}%)`;
            }

            const badge = document.getElementById('scramble-step-badge');
            if (badge) badge.textContent = `Word ${currentScrambleIndex + 1} of ${currentScrambleWords.length}`;

            const dots = document.querySelectorAll('.scramble-dot');
            dots.forEach((dot, idx) => {
                if (idx === currentScrambleIndex) {
                    dot.style.width = '22px';
                    dot.style.background = '#203f8d';
                } else {
                    dot.style.width = '8px';
                    const inp = document.getElementById(`scramble-input-${idx}`);
                    const isSolved = inp && currentScrambleWords[idx] && normalizeScrambleText(inp.value) === normalizeScrambleText(currentScrambleWords[idx].answer);
                    dot.style.background = isSolved ? '#34d399' : '#cbd5e1';
                }
            });

            const prevBtn = document.getElementById('scramble-prev-btn');
            if (prevBtn) {
                prevBtn.style.display = currentScrambleIndex > 0 ? 'inline-flex' : 'none';
            }

            const isLast = (currentScrambleIndex === currentScrambleWords.length - 1);
            const btnText = document.getElementById('scramble-action-btn-text');
            const btnIcon = document.getElementById('scramble-action-btn-icon');
            const actionBtn = document.getElementById('scramble-action-btn');

            if (btnText) btnText.textContent = isLast ? 'Submit Answers' : 'Next Word';
            if (btnIcon) btnIcon.className = isLast ? 'fa-solid fa-paper-plane' : 'fa-solid fa-arrow-right';

            // Check if current word is solved to highlight button
            const curInput = document.getElementById(`scramble-input-${currentScrambleIndex}`);
            const curCorrect = curInput && currentScrambleWords[currentScrambleIndex] && normalizeScrambleText(curInput.value) === normalizeScrambleText(currentScrambleWords[currentScrambleIndex].answer);
            if (actionBtn) {
                if (curCorrect) {
                    actionBtn.style.setProperty('background', 'linear-gradient(135deg, #059669 0%, #10b981 100%)', 'important');
                    actionBtn.style.setProperty('box-shadow', '0 4px 16px rgba(16, 185, 129, 0.4)', 'important');
                } else {
                    actionBtn.style.setProperty('background', 'linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%)', 'important');
                    actionBtn.style.setProperty('box-shadow', '0 4px 14px rgba(32, 63, 141, 0.28)', 'important');
                }
            }

            setTimeout(() => {
                if (curInput) {
                    try {
                        curInput.focus({ preventScroll: true });
                    } catch (e) {
                        curInput.focus();
                    }
                }
                const vp = document.getElementById('scramble-slider-viewport');
                if (vp) vp.scrollLeft = 0;
            }, 250);
        }

        function prevScrambleQuestion() {
            if (currentScrambleIndex > 0) {
                goToScrambleQuestion(currentScrambleIndex - 1);
            }
        }

        function handleScrambleActionClick() {
            if (currentScrambleWords.length === 0) return;
            const curItem = currentScrambleWords[currentScrambleIndex];
            const input = document.getElementById(`scramble-input-${currentScrambleIndex}`);
            const val = input ? normalizeScrambleText(input.value) : '';
            const expected = curItem ? normalizeScrambleText(curItem.answer) : '';

            if (val !== expected) {
                const card = document.getElementById(`scramble-card-${currentScrambleIndex}`);
                if (card) {
                    card.style.transform = 'translateX(-6px)';
                    setTimeout(() => { card.style.transform = 'translateX(6px)'; }, 80);
                    setTimeout(() => { card.style.transform = 'translateX(-4px)'; }, 160);
                    setTimeout(() => { card.style.transform = 'translateX(4px)'; }, 240);
                    setTimeout(() => { card.style.transform = 'translateX(0)'; }, 320);
                }
                if (typeof showToast === 'function') {
                    showToast('Unscramble this word correctly first!', 'info');
                } else {
                    openGameAlert("Please unscramble this word correctly before moving to the next! Check the hint or tap Reshuffle if you need help.", "Almost There!");
                }
                return;
            }

            if (currentScrambleIndex < currentScrambleWords.length - 1) {
                goToScrambleQuestion(currentScrambleIndex + 1);
            } else {
                submitScrambleAnswers();
            }
        }

        // Reshuffle just one word's letters if the user wants another perspective
        function reshuffleWordLetters(index) {
            if (!currentScrambleWords[index]) return;
            const item = currentScrambleWords[index];
            const prev = item.scrambled;
            let next = scramblePhrase(item.answer);
            let attempts = 0;
            while (next === prev && attempts < 10) {
                next = scramblePhrase(item.answer);
                attempts++;
            }
            item.scrambled = next;
            const displayEl = document.getElementById(`scramble-word-display-${index}`);
            if (displayEl) {
                displayEl.style.transform = 'scale(0.95)';
                displayEl.style.transition = 'transform 0.15s ease';
                setTimeout(() => {
                    displayEl.textContent = next;
                    displayEl.style.transform = 'scale(1)';
                }, 150);
            }
        }

        // Validate input against answer
        function checkScrambleWord(index) {
            const input = document.getElementById(`scramble-input-${index}`);
            const status = document.getElementById(`scramble-status-${index}`);
            const card = document.getElementById(`scramble-card-${index}`);
            if (!input || !status || !currentScrambleWords[index]) return;

            const val = normalizeScrambleText(input.value);
            const expected = normalizeScrambleText(currentScrambleWords[index].answer);
            const isCorrect = (val.length > 0 && val === expected);

            if (isCorrect) {
                status.innerHTML = '<i class="fa-solid fa-circle-check" style="color: #34d399; font-size: 18px;"></i>';
                input.style.border = 'none';
                input.style.outline = 'none';
                input.style.background = 'rgba(52, 211, 153, 0.28)';
                input.style.color = '#ffffff';
                input.style.webkitTextFillColor = '#ffffff';
                if (card) card.classList.add('is-correct');
            } else {
                status.innerHTML = '<i class="fa-solid fa-pen" style="color: rgba(255,255,255,0.4); font-size: 14px;"></i>';
                input.style.border = 'none';
                input.style.outline = 'none';
                input.style.background = 'rgba(255, 255, 255, 0.2)';
                input.style.color = '#ffffff';
                input.style.webkitTextFillColor = '#ffffff';
                if (card) card.classList.remove('is-correct');
            }

            if (index === currentScrambleIndex) {
                const actionBtn = document.getElementById('scramble-action-btn');
                if (actionBtn) {
                    if (isCorrect) {
                        actionBtn.style.setProperty('background', 'linear-gradient(135deg, #059669 0%, #10b981 100%)', 'important');
                        actionBtn.style.setProperty('box-shadow', '0 4px 16px rgba(16, 185, 129, 0.4)', 'important');
                    } else {
                        actionBtn.style.setProperty('background', 'linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%)', 'important');
                        actionBtn.style.setProperty('box-shadow', '0 4px 14px rgba(32, 63, 141, 0.28)', 'important');
                    }
                }
            }
            updateScrambleProgress();
        }

        // Update the progress bar and counter
        function updateScrambleProgress() {
            let solved = 0;
            currentScrambleWords.forEach((item, index) => {
                const input = document.getElementById(`scramble-input-${index}`);
                if (input && normalizeScrambleText(input.value) === normalizeScrambleText(item.answer)) {
                    solved++;
                }
            });

            const badge = document.getElementById('scramble-solved-badge');
            const fill = document.getElementById('scramble-progress-fill');
            if (badge) {
                if (solved === 4) {
                    badge.textContent = '4 / 4 Solved! 🎉';
                    badge.style.color = '#34d399';
                    badge.style.background = 'rgba(52, 211, 153, 0.25)';
                } else {
                    badge.textContent = `${solved} / 4 Solved`;
                    badge.style.color = '#00f2fe';
                    badge.style.background = 'rgba(0, 242, 254, 0.18)';
                }
            }
            if (fill) {
                fill.style.width = `${(solved / 4) * 100}%`;
            }

            const dots = document.querySelectorAll('.scramble-dot');
            dots.forEach((dot, idx) => {
                if (idx !== currentScrambleIndex) {
                    const inp = document.getElementById(`scramble-input-${idx}`);
                    const isSolved = inp && currentScrambleWords[idx] && normalizeScrambleText(inp.value) === normalizeScrambleText(currentScrambleWords[idx].answer);
                    dot.style.background = isSolved ? '#34d399' : '#cbd5e1';
                }
            });
        }

        // Reset all 4 inputs back to empty
        function resetScrambleInputs() {
            currentScrambleWords.forEach((_, index) => {
                const input = document.getElementById(`scramble-input-${index}`);
                const status = document.getElementById(`scramble-status-${index}`);
                const card = document.getElementById(`scramble-card-${index}`);
                if (input) {
                    input.value = '';
                    input.style.background = 'rgba(255, 255, 255, 0.2)';
                }
                if (status) {
                    status.innerHTML = '<i class="fa-solid fa-pen" style="color: rgba(255,255,255,0.4); font-size: 14px;"></i>';
                }
                if (card) card.classList.remove('is-correct');
            });
            goToScrambleQuestion(0);
            updateScrambleProgress();
        }

        // Submit answers and claim points
        async function submitScrambleAnswers() {
            if (currentScrambleWords.length === 0) return;

            let allCorrect = true;
            let firstIncorrectIndex = -1;
            currentScrambleWords.forEach((item, index) => {
                const input = document.getElementById(`scramble-input-${index}`);
                const val = input ? normalizeScrambleText(input.value) : '';
                if (val !== normalizeScrambleText(item.answer)) {
                    allCorrect = false;
                    if (firstIncorrectIndex === -1) firstIncorrectIndex = index;
                }
            });

            if (!allCorrect) {
                if (firstIncorrectIndex !== -1) {
                    goToScrambleQuestion(firstIncorrectIndex);
                }
                openGameAlert("Some words are still incorrect or incomplete. Use the hints or tap Reshuffle to help unscramble all 4 words!", "Not Quite Finished");
                return;
            }

            claimMiniGamePoints('word_scramble');
        }

        // Initialize Scramble Game
        function initScrambleGame() {
            if (isGameDoneToday('word_scramble')) {
                updateScrambleInfoUI();
                return;
            }
            updateScrambleInfoUI();
            if (currentScrambleWords.length === 0) {
                randomizeScrambleWords();
            }
        }


        // ----------------------------------------------------
        // GENERAL API & SUCCESS MODAL HELPERS
        // ----------------------------------------------------
        function animateHeaderPointsIncrease(awardedPts, targetTotal) {
            const el = document.getElementById('game-points-val');
            if (!el) return;
            const currentVal = parseInt(el.textContent.replace(/,/g, ''), 10) || 0;
            const finalVal = (typeof targetTotal === 'number' && targetTotal > 0) ? targetTotal : (currentVal + (awardedPts || 0));

            if (finalVal === currentVal) {
                el.textContent = Number(finalVal).toLocaleString();
                return;
            }

            let start = null;
            const duration = 650;
            const diff = finalVal - currentVal;

            function step(timestamp) {
                if (!start) start = timestamp;
                const progress = Math.min((timestamp - start) / duration, 1);
                // easeOutCubic
                const eased = 1 - Math.pow(1 - progress, 3);
                const currentStepVal = Math.round(currentVal + diff * eased);
                el.textContent = Number(currentStepVal).toLocaleString();
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                } else {
                    el.textContent = Number(finalVal).toLocaleString();
                }
            }
            window.requestAnimationFrame(step);

            // Pop bounce & highlight animation
            el.style.transition = 'transform 0.22s cubic-bezier(0.175, 0.885, 0.32, 1.275), color 0.22s ease';
            el.style.transform = 'scale(1.22)';
            el.style.color = '#38bdf8';
            setTimeout(() => {
                el.style.transform = 'scale(1)';
                el.style.color = '#ffffff';
            }, 360);
        }

        async function claimMiniGamePoints(gameType) {
            if (_isGuest) {
                showGuestGameLoginModal();
                return;
            }
            try {
                if (window._practiceModes && window._practiceModes[gameType]) {
                    delete window._practiceModes[gameType];
                    updateAllGamesFinishedUI();
                    openGameSuccess({
                        title: "Practice Completed!",
                        message: "Awesome practice run! You have already claimed today's daily points. Come back tomorrow for new reward points!",
                        awardedPts: 0,
                        isPractice: true,
                        comeBackTomorrow: true
                    });
                    return;
                }

                const defaultPointsMap = {
                    'puzzle': 50,
                    'memory_match': 25,
                    'word_scramble': 25,
                    'trivia': 20
                };
                const expectedPts = defaultPointsMap[gameType] || 25;

                // Mark completed today in localStorage immediately
                const todayStr = new Date().toDateString();
                const gameKey = getGameDoneKey(gameType);
                localStorage.setItem(gameKey, todayStr);
                localStorage.removeItem('game_done_' + gameType);
                updateAllGamesFinishedUI();

                // Optimistically calculate total points immediately for instant UI feedback
                let optimisticTotal = expectedPts;
                try {
                    const curPts = resolveBestCachedPoints();
                    optimisticTotal = curPts + expectedPts;
                    const authUser = JSON.parse(localStorage.getItem('auth_user') || '{}');
                    authUser.points = optimisticTotal;
                    localStorage.setItem('auth_user', JSON.stringify(authUser));
                    window._userPointsBalance = optimisticTotal;
                    if (typeof window.syncUserPointsEverywhere === 'function') {
                        window.syncUserPointsEverywhere(optimisticTotal);
                    }
                } catch (e) { }

                // Animate points counter in header
                animateHeaderPointsIncrease(expectedPts, optimisticTotal);

                // Show Awesome Job modal IMMEDIATELY (no waiting for remote API network latency!)
                openGameSuccess({
                    title: "Awesome Job!",
                    awardedPts: expectedPts,
                    newTotalPoints: optimisticTotal,
                    message: `Congratulations! You earned ${expectedPts} Points!`,
                    comeBackTomorrow: true,
                    skipRefresh: true
                });

                // Ambient offline game queueing if solving while on a walk outside
                if (!navigator.onLine || window.isAppOnline === false) {
                    try {
                        const queueKey = 'intan_elyu_offline_game_claims_' + (getGameDoneKey('claim').replace('game_done_claim_', ''));
                        let queue = JSON.parse(localStorage.getItem(queueKey) || '[]');
                        if (!queue.some(q => q.gameType === gameType)) {
                            queue.push({ gameType: gameType, timestamp: Date.now() });
                            localStorage.setItem(queueKey, JSON.stringify(queue));
                        }
                    } catch (e) { }
                    return;
                }

                const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('api_token');
                const _baseUrl = (typeof window.getBackendUrl === 'function'
                    ? window.getBackendUrl()
                    : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');

                let endpoint = '/api/tourist/points/minigame';
                let bodyObj = { game_type: gameType };

                if (gameType === 'puzzle') {
                    endpoint = '/api/tourist/points/puzzle';
                    bodyObj = {};
                } else if (gameType === 'trivia') {
                    endpoint = '/api/tourist/points/trivia';
                    bodyObj = {};
                }

                // Guard against concurrent balance GETs overwriting optimistic points while POST is in flight
                window._inFlightPointsClaims = Number(window._inFlightPointsClaims || 0) + 1;
                let claimSucceeded = false;

                // Fire sync to server in background
                fetch(_baseUrl + endpoint, {
                    method: 'POST',
                    cache: 'no-store',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'ngrok-skip-browser-warning': 'true',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify(bodyObj)
                }).then(async (r) => {
                    const d = await r.json();
                    if (r.ok && d.status === 'success') {
                        claimSucceeded = true;
                        const awardedPts = Number(d.points_awarded ?? d.xp_awarded ?? expectedPts);
                        const newTotalPoints = Number(d.points ?? d.xp ?? 0);
                        let resolvedPts = newTotalPoints;

                        try {
                            const curPts = resolveBestCachedPoints();
                            const finalPts = newTotalPoints > 0 ? newTotalPoints : (curPts + awardedPts);
                            resolvedPts = finalPts;
                            const authUser = JSON.parse(localStorage.getItem('auth_user') || '{}');
                            authUser.points = finalPts;
                            if (d.xp !== undefined) authUser.xp = Math.max(Number(d.xp) || 0, finalPts);
                            localStorage.setItem('auth_user', JSON.stringify(authUser));
                            window._userPointsBalance = finalPts;
                            if (typeof window.syncUserPointsEverywhere === 'function') {
                                window.syncUserPointsEverywhere(finalPts, null, { xp: d.xp });
                            }
                            window.leaderboardNeedsRefresh = true;
                            Object.keys(localStorage).forEach(k => {
                                if (k.startsWith('leaderboard_data_')) localStorage.removeItem(k);
                            });
                        } catch (e) { }

                        const totalPtsEl = document.getElementById('success-total-points-val');
                        if (totalPtsEl && resolvedPts > 0) {
                            totalPtsEl.textContent = Number(resolvedPts).toLocaleString() + ' PTS';
                        }
                    } else if (r.status === 429) {
                        updateAllGamesFinishedUI();
                    }
                }).catch(err => {
                    console.warn("Points sync queued for background retry:", err);
                    try {
                        const queueKey = 'intan_elyu_offline_game_claims_' + (getGameDoneKey('claim').replace('game_done_claim_', ''));
                        let queue = JSON.parse(localStorage.getItem(queueKey) || '[]');
                        if (!queue.some(q => q.gameType === gameType)) {
                            queue.push({ gameType: gameType, timestamp: Date.now() });
                            localStorage.setItem(queueKey, JSON.stringify(queue));
                        }
                    } catch (e) { }
                }).finally(() => {
                    window._inFlightPointsClaims = Math.max(0, Number(window._inFlightPointsClaims || 0) - 1);
                    if (claimSucceeded) {
                        loadGamePoints();
                    }
                });
            } catch (e) {
                console.error("Points claim error, saving offline:", e);
                try {
                    const queueKey = 'intan_elyu_offline_game_claims_' + (getGameDoneKey('claim').replace('game_done_claim_', ''));
                    let queue = JSON.parse(localStorage.getItem(queueKey) || '[]');
                    if (!queue.some(q => q.gameType === gameType)) {
                        queue.push({ gameType: gameType, timestamp: Date.now() });
                        localStorage.setItem(queueKey, JSON.stringify(queue));
                    }
                    localStorage.setItem(getGameDoneKey(gameType), new Date().toDateString());
                    localStorage.removeItem('game_done_' + gameType);
                    updateAllGamesFinishedUI();
                    openGameSuccess({
                        title: "Awesome Job!",
                        awardedPts: 25,
                        message: "Game completed! Your points will sync automatically when back online. Come back tomorrow!",
                        comeBackTomorrow: true,
                        skipRefresh: true
                    });
                    return;
                } catch (queueErr) { }
                openGameAlert("Connection issue. Please try again.", "Error");
            }
        }

        function openGameSuccess(opts) {
            const modal = document.getElementById('game-success-modal');
            if (!modal) return;

            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }

            let message = '';
            let awardedPts = 0;
            let newTotalPoints = null;
            let comeBackTomorrow = true;
            let isPractice = false;
            let title = "Awesome Job!";

            if (typeof opts === 'string') {
                message = opts;
            } else if (opts && typeof opts === 'object') {
                message = opts.message || '';
                awardedPts = opts.awardedPts ?? 0;
                newTotalPoints = opts.newTotalPoints ?? null;
                comeBackTomorrow = opts.comeBackTomorrow !== false;
                isPractice = opts.isPractice === true;
                if (opts.title) title = opts.title;
            }

            const titleEl = document.getElementById('success-modal-title');
            if (titleEl) titleEl.textContent = title;

            const chipEl = document.getElementById('success-points-chip');
            const awardedValEl = document.getElementById('success-points-awarded-val');
            if (chipEl && awardedValEl) {
                if (awardedPts > 0) {
                    chipEl.style.display = 'inline-flex';
                    awardedValEl.textContent = `+${awardedPts} PTS`;
                } else if (isPractice) {
                    chipEl.style.display = 'inline-flex';
                    awardedValEl.textContent = 'Practice Mode';
                } else {
                    chipEl.style.display = 'none';
                }
            }

            const msgEl = document.getElementById('success-points-msg');
            if (msgEl) {
                msgEl.textContent = message || "You've successfully completed the game and earned points!";
            }

            const comebackCard = document.getElementById('success-comeback-card');
            if (comebackCard) {
                comebackCard.style.display = comeBackTomorrow ? 'flex' : 'none';
            }

            const totalPtsEl = document.getElementById('success-total-points-val');
            if (totalPtsEl) {
                if (newTotalPoints !== null && newTotalPoints !== undefined && newTotalPoints > 0) {
                    totalPtsEl.textContent = Number(newTotalPoints).toLocaleString() + ' PTS';
                } else {
                    try {
                        const curPts = resolveBestCachedPoints();
                        totalPtsEl.textContent = Number(curPts).toLocaleString() + ' PTS';
                    } catch (e) {
                        totalPtsEl.textContent = '-- PTS';
                    }
                }
            }

            modal.style.display = 'flex';
            document.body.classList.add('modal-blurred');
            document.documentElement.classList.add('modal-blurred');
            if (!(opts && typeof opts === 'object' && opts.skipRefresh) && !(Number(window._inFlightPointsClaims || 0) > 0)) {
                loadGamePoints();
            }
        }

        function closeGameSuccess() {
            const modal = document.getElementById('game-success-modal');
            if (modal) modal.style.display = 'none';
            document.body.classList.remove('modal-blurred');
            document.documentElement.classList.remove('modal-blurred');
            updateAllGamesFinishedUI();
        }

        function openGameAlert(message, title = "Already Done!") {
            const modal = document.getElementById('game-alert-modal');
            if (modal) {
                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
                const titleEl = document.getElementById('alert-modal-title') || modal.querySelector('h3, h2');
                if (titleEl) titleEl.textContent = title;
                const msgEl = document.getElementById('alert-points-msg');
                if (msgEl) msgEl.textContent = message || "You already completed this game today! Please come back tomorrow.";
                modal.style.display = 'flex';
                document.body.classList.add('modal-blurred');
                document.documentElement.classList.add('modal-blurred');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeGameAlert() {
            const modal = document.getElementById('game-alert-modal');
            if (modal) modal.style.display = 'none';
            document.body.classList.remove('modal-blurred');
            document.documentElement.classList.remove('modal-blurred');
            document.body.style.overflow = '';
        }

        function openGameConfirm({ title, message, icon = 'fa-arrows-rotate', iconColor = '#1e3a8a', iconBg = '#eff6ff', confirmText = 'Yes, Reset', confirmBg = 'linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%)', onConfirm }) {
            const modal = document.getElementById('game-confirm-modal');
            if (!modal) return;

            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }

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
                iconBgEl.style.color = iconColor;
            }
            if (actionBtn) {
                actionBtn.textContent = confirmText || 'Yes, Reset';
                actionBtn.style.background = confirmBg || 'linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%)';
                actionBtn.style.color = '#ffffff';
            }

            pendingConfirmAction = onConfirm;
            modal.style.display = 'flex';
            document.body.classList.add('modal-blurred');
            document.documentElement.classList.add('modal-blurred');
            document.body.style.overflow = 'hidden';
        }

        function closeGameConfirm() {
            const modal = document.getElementById('game-confirm-modal');
            if (modal) modal.style.display = 'none';
            pendingConfirmAction = null;
            document.body.classList.remove('modal-blurred');
            document.documentElement.classList.remove('modal-blurred');
            document.body.style.overflow = '';
        }

        function executeGameConfirm() {
            const action = pendingConfirmAction;
            closeGameConfirm();
            if (typeof action === 'function') {
                action();
            }
        }

        function openTargetGoalPreviewModal() {
            const modal = document.getElementById('target-goal-preview-modal');
            const imgEl = document.getElementById('target-goal-preview-img');
            const nameEl = document.getElementById('target-goal-preview-name');
            if (!modal) return;
            if (currentPuzzleItem) {
                const targetSrc = currentPuzzleItem.resolvedImage || currentPuzzleItem.image || getPuzzleFallbackImage(currentPuzzleItem);
                if (imgEl) imgEl.src = targetSrc;
                if (nameEl) nameEl.textContent = currentPuzzleItem.name || 'Tourist Spot';
            }
            modal.style.display = 'flex';
            document.body.classList.add('modal-blurred');
            document.documentElement.classList.add('modal-blurred');
        }

        function closeTargetGoalPreviewModal() {
            const modal = document.getElementById('target-goal-preview-modal');
            if (modal) modal.style.display = 'none';
            document.body.classList.remove('modal-blurred');
            document.documentElement.classList.remove('modal-blurred');
        }

        window.openTargetGoalPreviewModal = openTargetGoalPreviewModal;
        window.closeTargetGoalPreviewModal = closeTargetGoalPreviewModal;

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
        updateAllGamesFinishedUI();
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
        window.promptResetMemory = promptResetMemory;
        window.openGameConfirm = openGameConfirm;
        window.closeGameConfirm = closeGameConfirm;
        window.executeGameConfirm = executeGameConfirm;
        window.closeGameSuccess = closeGameSuccess;
        window.closeGameAlert = closeGameAlert;
        window.submitTriviaAnswers = submitTriviaAnswers;
        window.initMemoryGame = initMemoryGame;
        window.initScrambleGame = initScrambleGame;
        window.submitScrambleAnswers = submitScrambleAnswers;
        window.checkScrambleWord = checkScrambleWord;
        window.randomizeScrambleWords = randomizeScrambleWords;
        window.resetScrambleInputs = resetScrambleInputs;
        window.reshuffleWordLetters = reshuffleWordLetters;
        window.toggleScrambleMechanics = toggleScrambleMechanics;
        window.goToScrambleQuestion = goToScrambleQuestion;
        window.prevScrambleQuestion = prevScrambleQuestion;
        window.handleScrambleActionClick = handleScrambleActionClick;
        async function syncOfflineGameClaims() {
            if (_isGuest) return;
            if (!navigator.onLine) return;
            let didIncrement = false;
            try {
                const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('api_token');
                if (!token) return;
                const _baseUrl = (typeof window.getBackendUrl === 'function'
                    ? window.getBackendUrl()
                    : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');

                const claimKeys = Object.keys(localStorage).filter(k => k.startsWith('intan_elyu_offline_game_claims'));
                let allItems = [];
                claimKeys.forEach(k => {
                    try {
                        const parsed = JSON.parse(localStorage.getItem(k) || '[]');
                        if (Array.isArray(parsed)) allItems.push(...parsed);
                    } catch(e) {}
                });

                if (!allItems.length) return;

                const uniqueClaims = [];
                const seenTypes = new Set();
                for (const item of allItems) {
                    if (item && item.gameType && !seenTypes.has(item.gameType)) {
                        seenTypes.add(item.gameType);
                        uniqueClaims.push(item);
                    }
                }

                window._inFlightPointsClaims = Number(window._inFlightPointsClaims || 0) + 1;
                didIncrement = true;

                for (let i = 0; i < uniqueClaims.length; i++) {
                    const item = uniqueClaims[i];
                    let endpoint = '/api/tourist/points/minigame';
                    let bodyObj = { game_type: item.gameType };
                    if (item.gameType === 'puzzle') {
                        endpoint = '/api/tourist/points/puzzle';
                        bodyObj = {};
                    } else if (item.gameType === 'trivia') {
                        endpoint = '/api/tourist/points/trivia';
                        bodyObj = {};
                    }

                    await fetch(_baseUrl + endpoint, {
                        method: 'POST',
                        cache: 'no-store',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'ngrok-skip-browser-warning': 'true',
                            'Authorization': 'Bearer ' + token
                        },
                        body: JSON.stringify(bodyObj)
                    });
                }
                claimKeys.forEach(k => localStorage.removeItem(k));
            } catch (e) { }
            finally {
                if (didIncrement) {
                    window._inFlightPointsClaims = Math.max(0, Number(window._inFlightPointsClaims || 0) - 1);
                    loadGamePoints();
                }
            }
        }

        window.addEventListener('online', async function () {
            await syncOfflineGameClaims();
            loadGamePoints();
        });
        syncOfflineGameClaims();

        function updatePuzzlesOfflineStatus() {
            var badge = document.getElementById('puzzles-offline-badge');
            var isOffline = (typeof navigator.onLine === 'boolean') ? !navigator.onLine : (window.isAppOnline === false);
            if (badge) {
                badge.style.setProperty('display', isOffline ? 'inline-flex' : 'none', 'important');
            }
            if (!isOffline) {
                try {
                    localStorage.removeItem('intan_elyu_offline_mode');
                    localStorage.removeItem('intan_elyu_offline_guest');
                } catch(e) {}
            }
        }
        window.addEventListener('online', () => {
            updatePuzzlesOfflineStatus();
            if (typeof window.enforceAllOfflineBadgesHidden === 'function') window.enforceAllOfflineBadgesHidden();
        });
        window.addEventListener('offline', updatePuzzlesOfflineStatus);
        window.addEventListener('appOnline', () => {
            updatePuzzlesOfflineStatus();
            if (typeof window.enforceAllOfflineBadgesHidden === 'function') window.enforceAllOfflineBadgesHidden();
        });
        window.addEventListener('appOffline', updatePuzzlesOfflineStatus);
        setTimeout(updatePuzzlesOfflineStatus, 100);

        // Countdown timer for daily game reset (Midnight local time)
        if (window._gameResetCountdownInterval) clearInterval(window._gameResetCountdownInterval);
        function updateDailyResetCountdown() {
            const now = new Date();
            const midnight = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1, 0, 0, 0);
            const diffMs = Math.max(0, midnight.getTime() - now.getTime());

            const hours = Math.floor(diffMs / (1000 * 60 * 60));
            const mins = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
            const secs = Math.floor((diffMs % (1000 * 60)) / 1000);

            const formatted = `${hours}h ${String(mins).padStart(2, '0')}m ${String(secs).padStart(2, '0')}s`;

            document.querySelectorAll('.game-reset-countdown').forEach(el => {
                el.textContent = formatted;
            });

            if (diffMs <= 1000 && !window._dayResetTriggered) {
                window._dayResetTriggered = true;
                setTimeout(() => {
                    window._dayResetTriggered = false;
                    loadGamePoints();
                }, 1500);
            }
        }
        updateDailyResetCountdown();
        window._gameResetCountdownInterval = setInterval(updateDailyResetCountdown, 1000);
        window.updateDailyResetCountdown = updateDailyResetCountdown;

        window.playInPracticeMode = playInPracticeMode;
    })();
</script>
