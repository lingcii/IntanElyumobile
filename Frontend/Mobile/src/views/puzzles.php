<?php
$pageTitle = 'Gamification Zone';
$backRoute = 'dashboard';
include __DIR__ . '/../components/header.php';
?>

<div class="puzzles-container has-header animate-slide-up" style="padding: 16px; min-height: calc(100vh - 64px); background: #0a0a0e; color: #f8fafc;">
    
    <!-- Points Status Header -->
    <div style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.8)); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 16px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3); backdrop-filter: blur(10px);">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(56, 189, 248, 0.15); display: flex; align-items: center; justify-content: center; font-size: 20px; color: #38bdf8;">
                <i class="fa-solid fa-gamepad"></i>
            </div>
            <div>
                <h4 style="margin: 0 0 2px 0; font-size: 14px; color: rgba(148, 163, 184, 0.8); font-weight: 500;">Your Points Balance</h4>
                <div style="display: flex; align-items: baseline; gap: 6px;">
                    <span id="game-points-val" style="font-size: 22px; font-weight: 800; color: #38bdf8; letter-spacing: -0.5px;">--</span>
                    <span style="font-size: 12px; color: rgba(148, 163, 184, 0.6); font-weight: 600;">PTS</span>
                </div>
            </div>
        </div>
        <button onclick="navigateTo('profile')" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 8px 14px; border-radius: 12px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: 0.2s;">
            Redeem <i class="fa-solid fa-arrow-right" style="font-size: 10px;"></i>
        </button>
    </div>

    <!-- Tab Selector -->
    <div style="display: flex; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); padding: 4px; border-radius: 14px; margin-bottom: 20px;">
        <button id="tab-btn-puzzle" onclick="switchGameTab('puzzle')" style="flex: 1; border: none; background: #38bdf8; color: #000; padding: 10px; border-radius: 10px; font-weight: 800; font-size: 13px; cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <i class="fa-solid fa-puzzle-piece"></i> Sliding Puzzle
        </button>
        <button id="tab-btn-trivia" onclick="switchGameTab('trivia')" style="flex: 1; border: none; background: transparent; color: rgba(255,255,255,0.6); padding: 10px; border-radius: 10px; font-weight: 700; font-size: 13px; cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <i class="fa-solid fa-circle-question"></i> Trivia Quiz
        </button>
    </div>

    <!-- SLIDING PUZZLE TAB -->
    <div id="game-tab-puzzle" class="game-tab-content">
        <div style="background: rgba(30, 41, 59, 0.4); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; padding: 16px; margin-bottom: 16px; text-align: center;">
            <h3 style="margin: 0 0 6px 0; font-size: 16px; font-weight: 800;">Tangadan Falls Slide Puzzle</h3>
            <p style="margin: 0; font-size: 12px; color: rgba(148, 163, 184, 0.8); line-height: 1.4;">
                Rearrange the tiles to reveal the image of the famous Tangadan Waterfalls in San Gabriel! Solve to earn <strong style="color: #38bdf8;">+100 Points</strong>.
            </p>
            
            <!-- Moves and Timer info -->
            <div style="display: flex; justify-content: center; gap: 20px; margin-top: 14px;">
                <div style="font-size: 13px; color: rgba(255,255,255,0.6);">Moves: <span id="puzzle-moves" style="font-weight: 800; color: #fff;">0</span></div>
                <div style="font-size: 13px; color: rgba(255,255,255,0.6);">Time: <span id="puzzle-timer" style="font-weight: 800; color: #fff;">00:00</span></div>
            </div>
        </div>

        <!-- Puzzle Board Container -->
        <div style="display: flex; justify-content: center; margin-bottom: 20px;">
            <div id="puzzle-board" style="width: 300px; height: 300px; background: rgba(15,23,42,0.8); border: 4px solid rgba(255,255,255,0.1); border-radius: 12px; position: relative; overflow: hidden; display: grid; grid-template-columns: repeat(3, 1fr); grid-template-rows: repeat(3, 1fr); gap: 2px; box-shadow: 0 15px 30px rgba(0,0,0,0.5);">
                <!-- 9 Grid items dynamic -->
            </div>
        </div>

        <div style="display: flex; gap: 10px; justify-content: center;">
            <button onclick="initPuzzle()" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 10px 20px; border-radius: 12px; font-weight: 700; font-size: 13px; cursor: pointer;">
                <i class="fa-solid fa-arrows-rotate"></i> Reset Puzzle
            </button>
            <button onclick="cheatSolvePuzzle()" style="background: rgba(239, 68, 68, 0.1); border: 1px dashed rgba(239, 68, 68, 0.3); color: #ef4444; padding: 10px 20px; border-radius: 12px; font-weight: 700; font-size: 13px; cursor: pointer;">
                Auto-Solve
            </button>
        </div>
    </div>

    <!-- TRIVIA QUIZ TAB -->
    <div id="game-tab-trivia" class="game-tab-content" style="display: none;">
        <div style="background: rgba(30, 41, 59, 0.4); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; padding: 16px; margin-bottom: 16px; text-align: center;">
            <h3 style="margin: 0 0 6px 0; font-size: 16px; font-weight: 800;">La Union Trivia Challenge</h3>
            <p style="margin: 0; font-size: 12px; color: rgba(148, 163, 184, 0.8); line-height: 1.4;">
                Answer all 3 trivia questions correctly to test your knowledge of La Union and earn <strong style="color: #38bdf8;">+50 Points</strong>!
            </p>
        </div>

        <div id="trivia-questions-container" style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px;">
            <!-- Dynamic Questions -->
        </div>

        <button onclick="submitTriviaAnswers()" style="width: 100%; border: none; background: linear-gradient(135deg, #38bdf8, #2563eb); color: white; padding: 14px; border-radius: 14px; font-weight: 800; font-size: 14px; cursor: pointer; box-shadow: 0 10px 20px rgba(37,99,235,0.25);">
            Submit Answers
        </button>
    </div>

    <!-- Success Modal popup -->
    <div id="game-success-modal" style="display: none; position: fixed; inset: 0; z-index: 10000; background: rgba(0,0,0,0.85); align-items: center; justify-content: center; padding: 24px; backdrop-filter: blur(10px);">
        <div style="background: linear-gradient(135deg, #1e293b, #0f172a); border: 1px solid rgba(255,255,255,0.15); border-radius: 24px; width: 100%; max-width: 350px; padding: 30px 20px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); transform: scale(0.9); animation: modalEnter 0.3s forwards cubic-bezier(0.34, 1.56, 0.64, 1);">
            <div style="width: 72px; height: 72px; border-radius: 50%; background: rgba(34,197,94,0.15); border: 2px solid #22c55e; display: flex; align-items: center; justify-content: center; font-size: 32px; color: #22c55e; margin: 0 auto 20px;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h2 style="margin: 0 0 10px; font-size: 22px; font-weight: 800; color: #fff;">Awesome Job!</h2>
            <p id="success-points-msg" style="margin: 0 0 24px; font-size: 14px; color: rgba(148,163,184,0.9); line-height: 1.5;">You solved the puzzle and claimed your points!</p>
            <button onclick="closeGameSuccess()" style="width: 100%; border: none; background: #38bdf8; color: #000; padding: 12px; border-radius: 12px; font-weight: 800; font-size: 14px; cursor: pointer;">
                Awesome!
            </button>
        </div>
    </div>

</div>

<style>
@keyframes modalEnter {
    to { transform: scale(1); }
}
.puzzle-tile {
    width: 100%;
    height: 100%;
    background-image: url('https://raw.githubusercontent.com/Acekillersmile2131/Intan-Elyu/main/Tangadan_Falls_puzzle.jpg');
    background-size: 300px 300px;
    background-repeat: no-repeat;
    cursor: pointer;
    border-radius: 6px;
    transition: transform 0.15s ease, filter 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    color: rgba(255,255,255,0.3);
    text-shadow: 1px 1px 2px #000;
    font-size: 16px;
    box-shadow: inset 0 0 10px rgba(0,0,0,0.5);
}
.puzzle-tile:hover {
    filter: brightness(1.1);
}
.puzzle-empty {
    background: transparent;
    cursor: default;
    box-shadow: none;
}
.trivia-card {
    background: rgba(30,41,59,0.3);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 16px;
    padding: 16px;
}
.trivia-q-text {
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
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    color: rgba(248, 250, 252, 0.8);
    padding: 10px 14px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.trivia-option-btn:hover {
    background: rgba(255,255,255,0.07);
}
.trivia-option-btn.selected {
    background: rgba(56, 189, 248, 0.15);
    border-color: #38bdf8;
    color: #38bdf8;
}
</style>

<script>
// Load points balance
async function loadGamePoints() {
    try {
        const r = await fetch(backendUrl + '/api/tourist/points/balance', {
            headers: { 'Authorization': 'Bearer ' + localStorage.getItem('api_token') }
        });
        const d = await r.json();
        if (d.status === 'success') {
            document.getElementById('game-points-val').textContent = d.points;
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
    document.querySelectorAll('.game-tab-content').forEach(el => el.style.display = 'none');
    document.getElementById('game-tab-' + tabName).style.display = 'block';

    const pBtn = document.getElementById('tab-btn-puzzle');
    const tBtn = document.getElementById('tab-btn-trivia');

    if (tabName === 'puzzle') {
        pBtn.style.background = '#38bdf8';
        pBtn.style.color = '#000';
        pBtn.style.fontWeight = '800';
        tBtn.style.background = 'transparent';
        tBtn.style.color = 'rgba(255,255,255,0.6)';
        tBtn.style.fontWeight = '700';
        initPuzzle();
    } else {
        tBtn.style.background = '#38bdf8';
        tBtn.style.color = '#000';
        tBtn.style.fontWeight = '800';
        pBtn.style.background = 'transparent';
        pBtn.style.color = 'rgba(255,255,255,0.6)';
        pBtn.style.fontWeight = '700';
        initTrivia();
    }
}

// ----------------------------------------------------
// SLIDING PUZZLE LOGIC
// ----------------------------------------------------
let tiles = [];
let moves = 0;
let timeSec = 0;
let timerInterval = null;
let puzzleSolved = false;

const correctLayout = [0, 1, 2, 3, 4, 5, 6, 7, 8]; // 8 is the empty cell

function initPuzzle() {
    moves = 0;
    timeSec = 0;
    puzzleSolved = false;
    document.getElementById('puzzle-moves').textContent = '0';
    document.getElementById('puzzle-timer').textContent = '00:00';
    
    if (timerInterval) clearInterval(timerInterval);
    timerInterval = setInterval(() => {
        timeSec++;
        const mins = Math.floor(timeSec / 60).toString().padStart(2, '0');
        const secs = (timeSec % 60).toString().padStart(2, '0');
        document.getElementById('puzzle-timer').textContent = mins + ':' + secs;
    }, 1000);

    // Shuffle tiles ensuring solvable layout
    do {
        tiles = [...correctLayout];
        // Shuffle everything except the last element initially
        for (let i = 7; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [tiles[i], tiles[j]] = [tiles[j], tiles[i]];
        }
    } while (!isSolvable(tiles));

    renderPuzzleBoard();
}

function isSolvable(grid) {
    let inversions = 0;
    // Check inversions without empty tile (8)
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
    board.innerHTML = '';

    tiles.forEach((val, index) => {
        const div = document.createElement('div');
        if (val === 8) {
            div.className = 'puzzle-tile puzzle-empty';
        } else {
            div.className = 'puzzle-tile';
            // Set background position for segment
            const x = (val % 3) * 100;
            const y = Math.floor(val / 3) * 100;
            div.style.backgroundPosition = `-${x}px -${y}px`;
            
            // Add tile click
            div.onclick = () => moveTile(index);
        }
        board.appendChild(div);
    });
}

function moveTile(index) {
    if (puzzleSolved) return;

    // Get adjacent indices
    const emptyIndex = tiles.indexOf(8);
    const row = Math.floor(index / 3);
    const col = index % 3;
    const emptyRow = Math.floor(emptyIndex / 3);
    const emptyCol = emptyIndex % 3;

    const isAdjacent = (Math.abs(row - emptyRow) + Math.abs(col - emptyCol)) === 1;

    if (isAdjacent) {
        // Swap values
        tiles[emptyIndex] = tiles[index];
        tiles[index] = 8;
        moves++;
        document.getElementById('puzzle-moves').textContent = moves;
        
        renderPuzzleBoard();
        checkPuzzleSolved();
    }
}

function checkPuzzleSolved() {
    const isCorrect = tiles.every((v, i) => v === correctLayout[i]);
    if (isCorrect) {
        puzzleSolved = true;
        clearInterval(timerInterval);
        claimPuzzlePoints();
    }
}

async function claimPuzzlePoints() {
    try {
        const r = await fetch(backendUrl + '/api/tourist/points/puzzle', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + localStorage.getItem('api_token') 
            }
        });
        const d = await r.json();
        if (d.status === 'success') {
            openGameSuccess(d.message);
        } else {
            alert(d.message || "Failed to award points.");
        }
    } catch (e) {
        console.error("Points claim error:", e);
    }
}

function cheatSolvePuzzle() {
    if (puzzleSolved) return;
    tiles = [...correctLayout];
    renderPuzzleBoard();
    checkPuzzleSolved();
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
        question: "What is the name of the famous multi-tiered waterfalls located in San Gabriel, La Union?",
        options: ["Balay Anito Falls", "Occalong Falls", "Tangadan Falls", "Tuddingan Falls"],
        correct: 2,
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
    // Reset selections
    triviaData.forEach(q => q.selected = null);

    const container = document.getElementById('trivia-questions-container');
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
    
    // Toggle CSS selection
    for (let i = 0; i < triviaData[qIndex].options.length; i++) {
        const btn = document.getElementById(`q-${qIndex}-opt-${i}`);
        if (i === optIndex) {
            btn.classList.add('selected');
        } else {
            btn.classList.remove('selected');
        }
    }
}

async function submitTriviaAnswers() {
    // Check if all answered
    const unanswered = triviaData.some(q => q.selected === null);
    if (unanswered) {
        alert("Please answer all questions before submitting!");
        return;
    }

    // Check correctness
    const allCorrect = triviaData.every(q => q.selected === q.correct);
    if (!allCorrect) {
        alert("Some answers are incorrect. Try again! hint: Think surfing in San Juan, waterfalls in San Gabriel, and native Ilokano speakers.");
        return;
    }

    // Submit and award points
    try {
        const r = await fetch(backendUrl + '/api/tourist/points/trivia', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + localStorage.getItem('api_token')
            }
        });
        const d = await r.json();
        if (d.status === 'success') {
            openGameSuccess(d.message);
        } else {
            alert(d.message || "Failed to award points.");
        }
    } catch (e) {
        console.error("Trivia answer error:", e);
    }
}

// Success Modal Helpers
function openGameSuccess(message) {
    const modal = document.getElementById('game-success-modal');
    document.getElementById('success-points-msg').textContent = message || "You've successfully solved the game and earned points!";
    modal.style.display = 'flex';
    loadGamePoints();
}

function closeGameSuccess() {
    document.getElementById('game-success-modal').style.display = 'none';
}

// Startup
document.addEventListener('DOMContentLoaded', () => {
    loadGamePoints();
    initPuzzle();
});
</script>
