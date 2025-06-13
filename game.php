<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Minolovec Game</title>
<link rel="stylesheet" href="style.css">
<style>
body {
  margin: 0;
  padding: 20px;
  text-align: center;
  font-family: sans-serif;
}



.game-container {
  max-width: 800px;
  margin: auto;
}

.game-info {
  display: flex;
  justify-content: center;
  gap: 20px;
  font-weight: bold;
  margin: 15px 0;
}

.minefield {
  display: inline-block;
  border: 2px solid black;
}

.row {
  display: flex;
}

.cell {
  width: 30px;
  height: 30px;
  background: #c0c0c0;
  border: 2px outset #999;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 13px;
  cursor: pointer;
  user-select: none;
}

.cell:hover { background: #d0d0d0; }

.cell.revealed {
  background: #e0e0e0;
  border: 1px inset #999;
}

.cell.mine    { background: red; color: black; }
.cell.flagged { background: yellow; color: black; }

.cell.number-1 { color: blue; }
.cell.number-2 { color: green; }
.cell.number-3 { color: red; }
.cell.number-4 { color: navy; }
.cell.number-5 { color: maroon; }
.cell.number-6 { color: teal; }
.cell.number-7 { color: black; }
.cell.number-8 { color: gray; }

.game-over {
  font-size: 20px;
  font-weight: bold;
  margin: 15px 0;
}

.win  { color: green; }
.lose { color: red; }
</style>
</head>
<body>
<div class="header">
    <a href="logout.php" class="button-link">LOG OUT</a>
    <a href="index.php" class="button-link">LEADERBOARD</a>
</div>

<h1>MINOLOVEC</h1>

<div class="game-container">
    <div class="difficulty-selector">
        <button onclick="setDifficulty('beginner')" class="active" id="beginner-btn">BEGINNER (9x9, 10 mines)</button>
        <button onclick="setDifficulty('intermediate')" id="intermediate-btn">INTERMEDIATE (16x16, 40 mines)</button>
    </div>

    <div class="game-info">
        <div>MINES LEFT: <span id="mines-count">10</span></div>
        <div>TIME: <span id="timer">0.00</span>s</div>
    </div>

    <button class="reset-button" onclick="resetGame()">NEW GAME</button>

    <div id="game-status" class="game-over"></div>

    <div id="minefield" class="minefield"></div>
</div>

<script>
let width = 9;
let height = 9;
let totalMines = 10;

let mineLocations = [];
let revealedCells = [];
let flaggedCells = [];

let gameStarted = false;
let gameOver = false;
let gameWon = false;
let startTime = null;
let timerInterval = null;

// Difficulty settings
function setDifficulty(level) {
    if (level === 'beginner') {
        width = 9;
        height = 9;
        totalMines = 10;
    } else if (level === 'intermediate') {
        width = 16;
        height = 16;
        totalMines = 40;
    }

    document.querySelectorAll('.difficulty-selector button').forEach(btn => {
        btn.classList.remove('active');
    });
    document.getElementById(level + '-btn').classList.add('active');

    resetGame();
}

function resetGame() {
    document.getElementById('game-status').textContent = '';
    document.getElementById('game-status').className = 'game-over';

    mineLocations = [];
    revealedCells = [];
    flaggedCells = [];
    gameStarted = false;
    gameOver = false;
    gameWon = false;
    startTime = null;

    if (timerInterval) {
        clearInterval(timerInterval);
    }
    timerInterval = null;

    updateDisplay();
    createMinefield();
}

function createMinefield() {
    const minefield = document.getElementById('minefield');
    minefield.innerHTML = '';

    for (let row = 0; row < height; row++) {
        const rowDiv = document.createElement('div');
        rowDiv.className = 'row';

        for (let col = 0; col < width; col++) {
            const cell = document.createElement('div');
            cell.className = 'cell';
            cell.dataset.row = row;
            cell.dataset.col = col;

            cell.addEventListener('click', handleLeftClick);
            cell.addEventListener('contextmenu', handleRightClick);

            rowDiv.appendChild(cell);
        }

        minefield.appendChild(rowDiv);
    }
}

function placeMines(excludeRow, excludeCol) {
    mineLocations = [];

    while (mineLocations.length < totalMines) {
        let row = Math.floor(Math.random() * height);
        let col = Math.floor(Math.random() * width);
        let key = row + '-' + col;

        if ((row === excludeRow && col === excludeCol) || mineLocations.includes(key)) {
            continue;
        }

        mineLocations.push(key);
    }
}

function handleLeftClick(event) {
    if (gameOver) return;

    const row = parseInt(event.target.dataset.row);
    const col = parseInt(event.target.dataset.col);
    const key = row + '-' + col;

    if (flaggedCells.includes(key)) return;

    if (!gameStarted) {
        gameStarted = true;
        placeMines(row, col);
        startTimer();
    }

    revealCell(row, col);
    checkWinCondition();
}

function handleRightClick(event) {
    event.preventDefault();
    if (gameOver) return;

    const row = parseInt(event.target.dataset.row);
    const col = parseInt(event.target.dataset.col);
    const key = row + '-' + col;
    const cell = event.target;

    if (revealedCells.includes(key)) return;

    if (flaggedCells.includes(key)) {
        flaggedCells = flaggedCells.filter(k => k !== key);
        cell.textContent = '';
        cell.classList.remove('flagged');
    } else {
        flaggedCells.push(key);
        cell.textContent = 'F';
        cell.classList.add('flagged');
    }

    updateDisplay();
}

function revealCell(row, col) {
    const key = row + '-' + col;
    if (revealedCells.includes(key) || flaggedCells.includes(key)) return;

    revealedCells.push(key);

    const cell = document.querySelector(`[data-row="${row}"][data-col="${col}"]`);
    cell.classList.add('revealed');

    if (mineLocations.includes(key)) {
        cell.textContent = 'M';
        cell.classList.add('mine');
        endGame(false);
        return;
    }

    const count = countAdjacentMines(row, col);
    if (count > 0) {
        cell.textContent = count;
        cell.classList.add('number-' + count);
    } else {
        for (let dr = -1; dr <= 1; dr++) {
            for (let dc = -1; dc <= 1; dc++) {
                let nr = row + dr;
                let nc = col + dc;
                if (nr >= 0 && nr < height && nc >= 0 && nc < width) {
                    revealCell(nr, nc);
                }
            }
        }
    }
}

function countAdjacentMines(row, col) {
    let count = 0;
    for (let dr = -1; dr <= 1; dr++) {
        for (let dc = -1; dc <= 1; dc++) {
            let nr = row + dr;
            let nc = col + dc;
            let key = nr + '-' + nc;
            if (nr >= 0 && nr < height && nc >= 0 && nc < width) {
                if (mineLocations.includes(key)) {
                    count++;
                }
            }
        }
    }
    return count;
}

function endGame(won) {
    gameOver = true;
    gameWon = won;

    if (timerInterval) {
        clearInterval(timerInterval);
    }

    const statusDiv = document.getElementById('game-status');
    if (won) {
        statusDiv.textContent = 'YOU WON!';
        statusDiv.className = 'game-over win';
    } else {
        statusDiv.textContent = 'GAME OVER!';
        statusDiv.className = 'game-over lose';
        revealAllMines();
    }
}

function revealAllMines() {
    for (let i = 0; i < mineLocations.length; i++) {
        const parts = mineLocations[i].split('-');
        const row = parseInt(parts[0]);
        const col = parseInt(parts[1]);
        const cell = document.querySelector(`[data-row="${row}"][data-col="${col}"]`);
        if (!revealedCells.includes(mineLocations[i])) {
            cell.textContent = 'M';
            cell.classList.add('mine');
        }
    }
}

function checkWinCondition() {
    const totalCells = width * height;
    if (revealedCells.length === totalCells - totalMines) {
        endGame(true);
    }
}

function startTimer() {
    startTime = Date.now();
    timerInterval = setInterval(() => {
        if (!gameOver) {
            const elapsed = (Date.now() - startTime) / 1000;
            document.getElementById('timer').textContent = elapsed.toFixed(2);
        }
    }, 10);
}

function updateDisplay() {
    document.getElementById('mines-count').textContent = totalMines - flaggedCells.length;
    if (!gameStarted) {
        document.getElementById('timer').textContent = '0.00';
    }
}

// Start the game when script loads
resetGame();
</script>

    <?php include 'footer.php'; ?>
</body>
</html>