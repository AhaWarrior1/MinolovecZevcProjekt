<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Minolovec Game</title>
<style>
body {
    font-family: 'Courier New', monospace;
    margin: 0;
    padding: 20px;
    text-align: center;
}

.header {
    margin-bottom: 20px;
}

.button-link {
    background-color: #f0f0f0;
    color: #000;
    padding: 10px 20px;
    text-decoration: none;
    border: 1px solid #ccc;
    display: inline-block;
    margin: 5px;
}

.button-link:hover {
    background-color: #e0e0e0;
}

.game-container {
    max-width: 800px;
    margin: 0 auto;
}

.game-controls {
    margin: 20px 0;
}

.difficulty-selector {
    margin: 10px;
}

.difficulty-selector button {
    background-color: #f0f0f0;
    color: #000;
    border: 1px solid #ccc;
    padding: 8px 16px;
    margin: 5px;
    cursor: pointer;
}

.difficulty-selector button:hover {
    background-color: #e0e0e0;
}

.difficulty-selector button.active {
    background-color: #d0d0d0;
    border-color: #999;
}

.game-info {
    display: flex;
    justify-content: center;
    gap: 30px;
    margin: 20px 0;
    font-size: 18px;
    font-weight: bold;
}

.minefield {
    display: inline-block;
    border: 2px solid #000;
}

.row {
    display: flex;
}

.cell {
    width: 30px;
    height: 30px;
    border: 2px outset #999;
    background-color: #c0c0c0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
    cursor: pointer;
    user-select: none;
}

.cell:hover {
    background-color: #d0d0d0;
}

.cell.revealed {
    border: 1px inset #999;
    background-color: #e0e0e0;
    color: #000;
}

.cell.mine {
    background-color: #ff0000;
    color: #000;
}

.cell.flagged {
    background-color: #ffff00;
    color: #000;
}

.cell.number-1 { color: #0000ff; }
.cell.number-2 { color: #008000; }
.cell.number-3 { color: #ff0000; }
.cell.number-4 { color: #000080; }
.cell.number-5 { color: #800000; }
.cell.number-6 { color: #008080; }
.cell.number-7 { color: #000000; }
.cell.number-8 { color: #808080; }

.game-over {
    font-size: 24px;
    font-weight: bold;
    margin: 20px 0;
}

.win {
    color: #008000;
}

.lose {
    color: #ff0000;
}

.reset-button {
    background-color: #f0f0f0;
    color: #000;
    border: 1px solid #ccc;
    padding: 10px 20px;
    font-size: 16px;
    cursor: pointer;
    margin: 10px;
}

.reset-button:hover {
    background-color: #e0e0e0;
}
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
class Minesweeper {
    constructor() {
        this.difficulties = {
            beginner: { width: 9, height: 9, mines: 10 },
            intermediate: { width: 16, height: 16, mines: 40 }
        };
        
        this.currentDifficulty = 'beginner';
        this.width = 9;
        this.height = 9;
        this.totalMines = 10;
        this.mineLocations = new Set();
        this.revealedCells = new Set();
        this.flaggedCells = new Set();
        this.gameStarted = false;
        this.gameOver = false;
        this.gameWon = false;
        this.startTime = null;
        this.timerInterval = null;
        
        this.initGame();
    }
    
    setDifficulty(difficulty) {
        this.currentDifficulty = difficulty;
        const config = this.difficulties[difficulty];
        this.width = config.width;
        this.height = config.height;
        this.totalMines = config.mines;
        
        // Update button states
        document.querySelectorAll('.difficulty-selector button').forEach(btn => {
            btn.classList.remove('active');
        });
        document.getElementById(difficulty + '-btn').classList.add('active');
        
        this.resetGame();
    }
    
    initGame() {
        this.mineLocations.clear();
        this.revealedCells.clear();
        this.flaggedCells.clear();
        this.gameStarted = false;
        this.gameOver = false;
        this.gameWon = false;
        this.startTime = null;
        
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
            this.timerInterval = null;
        }
        
        this.updateDisplay();
        this.createMinefield();
    }
    
    createMinefield() {
        const minefield = document.getElementById('minefield');
        minefield.innerHTML = '';
        
        for (let row = 0; row < this.height; row++) {
            const rowDiv = document.createElement('div');
            rowDiv.className = 'row';
            
            for (let col = 0; col < this.width; col++) {
                const cell = document.createElement('div');
                cell.className = 'cell';
                cell.dataset.row = row;
                cell.dataset.col = col;
                
                cell.addEventListener('click', (e) => this.handleCellClick(e));
                cell.addEventListener('contextmenu', (e) => this.handleRightClick(e));
                
                rowDiv.appendChild(cell);
            }
            
            minefield.appendChild(rowDiv);
        }
    }
    
    placeMines(excludeRow, excludeCol) {
        this.mineLocations.clear();
        
        while (this.mineLocations.size < this.totalMines) {
            const row = Math.floor(Math.random() * this.height);
            const col = Math.floor(Math.random() * this.width);
            const key = `${row}-${col}`;
            
            // Don't place mine on first clicked cell
            if (row === excludeRow && col === excludeCol) continue;
            
            this.mineLocations.add(key);
        }
    }
    
    startTimer() {
        this.startTime = Date.now();
        this.timerInterval = setInterval(() => {
            if (!this.gameOver) {
                const elapsed = (Date.now() - this.startTime) / 1000;
                document.getElementById('timer').textContent = elapsed.toFixed(2);
            }
        }, 10);
    }
    
    handleCellClick(e) {
        if (this.gameOver) return;
        
        const row = parseInt(e.target.dataset.row);
        const col = parseInt(e.target.dataset.col);
        const key = `${row}-${col}`;
        
        if (this.flaggedCells.has(key)) return;
        
        if (!this.gameStarted) {
            this.gameStarted = true;
            this.placeMines(row, col);
            this.startTimer();
        }
        
        this.revealCell(row, col);
        this.checkWinCondition();
    }
    
    handleRightClick(e) {
        e.preventDefault();
        if (this.gameOver) return;
        
        const row = parseInt(e.target.dataset.row);
        const col = parseInt(e.target.dataset.col);
        const key = `${row}-${col}`;
        
        if (this.revealedCells.has(key)) return;
        
        if (this.flaggedCells.has(key)) {
            this.flaggedCells.delete(key);
            e.target.textContent = '';
            e.target.classList.remove('flagged');
        } else {
            this.flaggedCells.add(key);
            e.target.textContent = 'F';
            e.target.classList.add('flagged');
        }
        
        this.updateDisplay();
    }
    
    revealCell(row, col) {
        const key = `${row}-${col}`;
        
        if (this.revealedCells.has(key) || this.flaggedCells.has(key)) return;
        
        this.revealedCells.add(key);
        const cell = document.querySelector(`[data-row="${row}"][data-col="${col}"]`);
        cell.classList.add('revealed');
        
        if (this.mineLocations.has(key)) {
            cell.textContent = 'M';
            cell.classList.add('mine');
            this.gameOver = true;
            this.gameWon = false;
            this.revealAllMines();
            this.showGameResult(false);
            this.submitScore(); // Submit loss as well
            return;
        }
        
        const adjacentMines = this.countAdjacentMines(row, col);
        if (adjacentMines > 0) {
            cell.textContent = adjacentMines;
            cell.classList.add(`number-${adjacentMines}`);
        } else {
            // Auto-reveal adjacent cells if no adjacent mines
            for (let dr = -1; dr <= 1; dr++) {
                for (let dc = -1; dc <= 1; dc++) {
                    const newRow = row + dr;
                    const newCol = col + dc;
                    if (newRow >= 0 && newRow < this.height && 
                        newCol >= 0 && newCol < this.width) {
                        this.revealCell(newRow, newCol);
                    }
                }
            }
        }
    }
    
    countAdjacentMines(row, col) {
        let count = 0;
        for (let dr = -1; dr <= 1; dr++) {
            for (let dc = -1; dc <= 1; dc++) {
                const newRow = row + dr;
                const newCol = col + dc;
                if (newRow >= 0 && newRow < this.height && 
                    newCol >= 0 && newCol < this.width) {
                    if (this.mineLocations.has(`${newRow}-${newCol}`)) {
                        count++;
                    }
                }
            }
        }
        return count;
    }
    
    revealAllMines() {
        this.mineLocations.forEach(key => {
            const [row, col] = key.split('-').map(Number);
            const cell = document.querySelector(`[data-row="${row}"][data-col="${col}"]`);
            if (!this.revealedCells.has(key)) {
                cell.textContent = 'M';
                cell.classList.add('mine');
            }
        });
    }
    
    checkWinCondition() {
        const totalCells = this.width * this.height;
        const revealedCount = this.revealedCells.size;
        
        if (revealedCount === totalCells - this.totalMines) {
            this.gameOver = true;
            this.gameWon = true;
            this.showGameResult(true);
            this.submitScore();
        }
    }
    
    showGameResult(won) {
        const statusDiv = document.getElementById('game-status');
        if (won) {
            statusDiv.textContent = 'YOU WON!';
            statusDiv.className = 'game-over win';
        } else {
            statusDiv.textContent = 'GAME OVER!';
            statusDiv.className = 'game-over lose';
        }
        
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
            this.timerInterval = null;
        }
    }
    
    async submitScore() {
        if (!this.gameStarted || !this.startTime) return;
        
        const finalTime = (Date.now() - this.startTime) / 1000;
        
        try {
            const formData = new FormData();
            formData.append('time', finalTime.toFixed(3));
            formData.append('width', this.width);
            formData.append('height', this.height);
            formData.append('mine_count', this.totalMines);
            formData.append('won', this.gameWon ? '1' : '0'); // Add win/loss status
            
            const response = await fetch('submit_score.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.text();
            console.log('Game result submitted:', result);
        } catch (error) {
            console.error('Error submitting game result:', error);
        }
    }
    
    updateDisplay() {
        const minesLeft = this.totalMines - this.flaggedCells.size;
        document.getElementById('mines-count').textContent = minesLeft;
        
        if (!this.gameStarted) {
            document.getElementById('timer').textContent = '0.00';
        }
    }
    
    resetGame() {
        document.getElementById('game-status').textContent = '';
        document.getElementById('game-status').className = 'game-over';
        this.initGame();
    }
}

// Global functions
let game = new Minesweeper();

function setDifficulty(difficulty) {
    game.setDifficulty(difficulty);
}

function resetGame() {
    game.resetGame();
}
</script>
</body>
</html>