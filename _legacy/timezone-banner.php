<?php
// Handle AJAX requests at the top
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $jsonFile = __DIR__ . '/../assets/holiday-data.json';

    if ($_POST['action'] === 'save_holiday_data') {
        $data = json_decode($_POST['data'] ?? '[]', true);
        if ($data === null) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data']);
            exit;
        }
        if (file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT)) !== false) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to write to file']);
        }
        exit;
    }

    if ($_POST['action'] === 'load_holiday_data') {
        $data = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
        if ($data === null) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to read or parse JSON file']);
            exit;
        }
        echo json_encode($data);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    exit;
}

// Load saved data for initial display
$jsonFile = __DIR__ . '/../assets/holiday-data.json';
$savedData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
?>
<section class="section">
    <div style="position: relative;">
        <h2>holiday planner</h2>
        <button type="button" style="position: absolute; top: 0; right: 0;" onclick="toggleCollapse(this)">+</button>
    </div>
    <div id="holiday-planner-content" style="display: none;">
        <form id="year-form">
            <div style="margin-bottom: 10px;">
                <select name="year" onchange="updateYear(this.value)">
                    <?php for ($y = date('Y') - 5; $y <= date('Y') + 5; $y++) { ?>
                        <option value="<?php echo $y; ?>" <?php if ($y == (isset($_GET['year']) ? (int)$_GET['year'] : date('Y'))) echo 'selected'; ?>><?php echo $y; ?></option>
                    <?php } ?>
                </select>
                <span style="margin-left: 10px;">Days off: <span id="days-off-count">0</span> / Total off: <span id="total-off-count">0</span></span>
                <span style="margin-left: 10px;">Color: </span>
                <span id="color-buttons">
                    <button type="button" class="color-btn" style="background-color: #ff0000; width: 20px; height: 20px; border: 1px solid black; margin: 0 5px;" onclick="setColor('#ff0000')"></button>
                    <button type="button" class="color-btn" style="background-color: #00ff00; width: 20px; height: 20px; border: 1px solid black; margin: 0 5px;" onclick="setColor('#00ff00')"></button>
                    <button type="button" class="color-btn" style="background-color: #0000ff; width: 20px; height: 20px; border: 1px solid black; margin: 0 5px;" onclick="setColor('#0000ff')"></button>
                    <button type="button" class="color-btn" style="background-color: #ffff00; width: 20px; height: 20px; border: 1px solid black; margin: 0 5px;" onclick="setColor('#ffff00')"></button>
                    <button type="button" class="color-btn" style="background-color: #ff00ff; width: 20px; height: 20px; border: 1px solid black; margin: 0 5px;" onclick="setColor('#ff00ff')"></button>
                    <button type="button" class="color-btn" style="background-color: #00ffff; width: 20px; height: 20px; border: 1px solid black; margin: 0 5px;" onclick="setColor('#00ffff')"></button>
                    <button type="button" class="color-btn" style="background-color: #ff8000; width: 20px; height: 20px; border: 1px solid black; margin: 0 5px;" onclick="setColor('#ff8000')"></button>
                    <button type="button" class="color-btn" style="background-color: #ff0080; width: 20px; height: 20px; border: 1px solid black; margin: 0 5px;" onclick="setColor('#ff0080')"></button>
                </span>
                <label style="margin: 0 5px;">
                    <input type="checkbox" id="two-color-checkbox" onchange="toggleTwoColorMode(this)"> 2 Colors
                </label>
                <div id="color-preview" style="width: 20px; height: 20px; border: 1px solid black; margin: 0 5px; display: inline-block; background: #ff0000;"></div>
            </div>
            <div style="margin-top: 10px;">
                <button type="button" id="save-btn" style="margin: 0 5px; padding: 2px 5px;" onclick="saveData()">Save</button>
                <button type="button" id="view-btn" style="margin: 0 5px; padding: 2px 5px;" onclick="viewSavedData()">View</button>
                <button type="button" id="pick-color-btn" style="margin: 0 5px; padding: 2px 5px;" onclick="pickColor()">Pick Color</button>
                <button type="button" id="default-btn" style="margin: 0 5px; padding: 2px 5px;" onclick="resetToDefaultColors()">Default</button>
                <button type="button" id="clear-btn" style="margin: 0 5px; padding: 2px 5px;" onclick="clearHighlights()">Clear</button>
            </div>
        </form>

        <div id="calendar-container" style="display: flex; flex-wrap: wrap; justify-content: space-between;">
<?php 
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
for ($month = 1; $month <= 12; $month++) { ?>
    <div style="width: 23%; margin: 5px; font-size: 10px;">
        <h3 style="font-size: 12px; color: #00ffff;"><?php echo date('M', mktime(0, 0, 0, $month, 1, $year)); ?></h3>
        <table style="width: 100%;">
            <tr>
                <th style="width: 14%;"><font color="#00ffff">su</font></th>
                <th style="width: 14%;">mo</th>
                <th style="width: 14%;">tu</th>
                <th style="width: 14%;">we</th>
                <th style="width: 14%;">th</th>
                <th style="width: 14%;">fr</th>
                <th style="width: 14%;"><font color="#00ffff">sa</font></th>
            </tr>
<?php 
$firstDay = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDay);
$dayOfWeek = date('w', $firstDay);
$day = 1;
for ($i = 0; $i < ceil(($daysInMonth + $dayOfWeek) / 7); $i++) { ?>
<tr>
<?php for ($j = 0; $j < 7; $j++) { 
    if ($i == 0 && $j < $dayOfWeek) { ?>
        <td style="width: 14%;">&nbsp;</td>
    <?php } elseif ($day <= $daysInMonth) { ?>
        <td style="width: 14%; <?php if ($j == 0 || $j == 6) echo 'color: #00ffff'; ?>" onclick="updateCount(this, <?php echo $j; ?>)"><?php echo $day; ?></td>
        <?php $day++; ?>
    <?php } else { ?>
        <td style="width: 14%;">&nbsp;</td>
    <?php } ?>
<?php } ?>
</tr>
<?php } ?>
        </table>
    </div>
<?php } ?>
        </div>
    </div>
</section>

<script>
let count = 0;
let totalCount = 0;
let selectedColor = '#ff0000';
let twoColorMode = false;
let secondColor = null;
let currentYear = <?php echo $year; ?>;
let savedData = <?php echo json_encode($savedData); ?>;
const defaultColors = ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff', '#ff8000', '#ff0080'];

// Helper function to show temporary button feedback
function showButtonFeedback(buttonId, message, duration = 2000) {
    const button = document.getElementById(buttonId);
    const originalText = button.textContent;
    button.textContent = message;
    button.disabled = true;
    setTimeout(() => {
        button.textContent = originalText;
        button.disabled = false;
    }, duration);
}

function setColor(color) {
    if(twoColorMode){
        if(!selectedColor){
            selectedColor = color;
        } else if(!secondColor && color !== selectedColor){
            secondColor = color;
        } else if(selectedColor === color){
            selectedColor = secondColor;
            secondColor = null;
        } else if(secondColor === color){
            secondColor = null;
        } else {
            selectedColor = color;
            secondColor = null;
        }
    } else {
        selectedColor = color;
        secondColor = null;
    }
    updateColorButtons();
    updateColorPreview();
}

function updateColorButtons(){
    document.querySelectorAll('.color-btn').forEach(btn=>{
        const btnColor = btn.style.backgroundColor;
        const hexColor = rgbToHex(btnColor);
        if(twoColorMode && (hexColor === selectedColor || hexColor === secondColor)){
            btn.style.border = '2px solid black';
        } else if(!twoColorMode && hexColor === selectedColor){
            btn.style.border = '2px solid black';
        } else {
            btn.style.border = '1px solid black';
        }
    });
}

function updateColorPreview(){
    const preview = document.getElementById('color-preview');
    if(twoColorMode && selectedColor && secondColor){
        preview.style.background = `linear-gradient(45deg, ${selectedColor} 50%, ${secondColor} 50%)`;
    } else {
        preview.style.background = selectedColor || '#000000';
    }
}

function toggleTwoColorMode(checkbox){
    twoColorMode = checkbox.checked;
    if(!twoColorMode) secondColor = null;
    updateColorButtons();
    updateColorPreview();
}

function pickColor(){
    const input = document.createElement('input');
    input.type = 'color';
    input.onchange = function() {
        const newColor = input.value;
        if (!document.querySelector(`.color-btn[style*="background-color: ${newColor}"]`)) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'color-btn';
            btn.style.backgroundColor = newColor;
            btn.style.width = '20px';
            btn.style.height = '20px';
            btn.style.border = '1px solid black';
            btn.style.margin = '0 5px';
            btn.onclick = () => setColor(newColor);
            document.getElementById('color-buttons').appendChild(btn);
            setColor(newColor);
        }
    };
    input.click();
}

function resetToDefaultColors(){
    const colorButtons = document.getElementById('color-buttons');
    colorButtons.innerHTML = defaultColors.map(color => 
        `<button type="button" class="color-btn" style="background-color: ${color}; width: 20px; height: 20px; border: 1px solid black; margin: 0 5px;" onclick="setColor('${color}')"></button>`
    ).join('');
    selectedColor = '#ff0000';
    secondColor = null;
    twoColorMode = false;
    document.getElementById('two-color-checkbox').checked = false;
    updateColorButtons();
    updateColorPreview();
    showButtonFeedback('default-btn', 'Defaults Set!', 1000);
}

function updateCount(td, day){
    const isHighlighted = td.style.background && td.style.background !== '';
    if(isHighlighted){
        td.style.background = '';
        td.style.border = '';
        if(day != 0 && day != 6){
            count--;
            if(count < 0) count = 0;
            document.getElementById('days-off-count').innerHTML = count;
        }
        totalCount--;
        if(totalCount < 0) totalCount = 0;
        document.getElementById('total-off-count').innerHTML = totalCount;
    } else {
        if(twoColorMode && selectedColor && secondColor){
            td.style.background = `linear-gradient(45deg, ${selectedColor} 50%, ${secondColor} 50%)`;
        } else {
            td.style.background = selectedColor;
        }
        td.style.border = '1px solid black';
        if(day != 0 && day != 6){
            count++;
            document.getElementById('days-off-count').innerHTML = count;
        }
        totalCount++;
        document.getElementById('total-off-count').innerHTML = totalCount;
    }
}

function clearHighlights(){
    document.querySelectorAll('#calendar-container td').forEach(td=>{
        td.style.background = '';
        td.style.border = '';
    });
    count = 0;
    totalCount = 0;
    document.getElementById('days-off-count').innerHTML = count;
    document.getElementById('total-off-count').innerHTML = totalCount;
    twoColorMode = false;
    secondColor = null;
    document.getElementById('two-color-checkbox').checked = false;
    setColor('#ff0000');
    showButtonFeedback('clear-btn', 'Cleared!', 1000);
}

function toggleCollapse(button){
    const content = document.getElementById('holiday-planner-content');
    if(content.style.display === 'none'){
        content.style.display = 'block';
        button.innerHTML = '-';
    } else {
        content.style.display = 'none';
        button.innerHTML = '+';
    }
}

function updateYear(year){
    currentYear = parseInt(year);
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    let html = '';
    for(let month=0; month<12; month++){
        const firstDay = new Date(year, month,1);
        const daysInMonth = new Date(year, month+1,0).getDate();
        const dayOfWeek = firstDay.getDay();
        let day =1;
        html += `<div style="width:23%; margin:5px; font-size:10px;">
            <h3 style="font-size:12px; color:#00ffff;">${months[month]}</h3>
            <table style="width:100%;">
            <tr>
                <th style="width:14%;"><font color="#00ffff">su</font></th>
                <th style="width:14%;">mo</th>
                <th style="width:14%;">tu</th>
                <th style="width:14%;">we</th>
                <th style="width:14%;">th</th>
                <th style="width:14%;">fr</th>
                <th style="width:14%;"><font color="#00ffff">sa</font></th>
            </tr>`;
        for(let i=0;i<Math.ceil((daysInMonth+dayOfWeek)/7);i++){
            html+='<tr>';
            for(let j=0;j<7;j++){
                if(i===0 && j<dayOfWeek){html+='<td style="width:14%;">&nbsp;</td>';}
                else if(day<=daysInMonth){
                    html+=`<td style="width:14%; ${j===0||j===6?'color:#00ffff':''}" onclick="updateCount(this,${j})">${day}</td>`;
                    day++;
                } else {html+='<td style="width:14%;">&nbsp;</td>';}
            }
            html+='</tr>';
        }
        html+='</table></div>';
    }
    document.getElementById('calendar-container').innerHTML = html;
    applySavedData();
}

function saveData(){
    const data = [];
    document.querySelectorAll('#calendar-container > div').forEach((monthDiv, mIndex) => {
        const month = mIndex + 1;
        monthDiv.querySelectorAll('td').forEach(td => {
            const dayNum = parseInt(td.textContent.trim());
            if (!isNaN(dayNum) && td.style.background && td.style.background !== '') {
                const dateStr = `${currentYear}-${String(month).padStart(2, '0')}-${String(dayNum).padStart(2, '0')}`;
                const entry = { date: dateStr };
                const bg = td.style.background;

                if (twoColorMode && selectedColor && secondColor) {
                    entry.colors = [rgbToHex(selectedColor), rgbToHex(secondColor)];
                    console.log(`Saving two-color entry for ${dateStr}:`, entry.colors);
                } else {
                    entry.color = rgbToHex(selectedColor);
                    console.log(`Saving single-color entry for ${dateStr}:`, entry.color);
                }

                data.push(entry);
            }
        });
    });

    showButtonFeedback('save-btn', 'Saving...', 2000);
    fetch('templates/holiday-planner.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=save_holiday_data&data=${encodeURIComponent(JSON.stringify(data))}`
    })
    .then(r => {
        if (!r.ok) throw new Error(`HTTP error: ${r.status} ${r.statusText}`);
        return r.json();
    })
    .then(result => {
        if (result.status === 'success') {
            showButtonFeedback('save-btn', 'Saved!', 1000);
            loadLatestSavedData();
        } else {
            showButtonFeedback('save-btn', 'Save Failed', 2000);
            alert('Failed to save data: ' + (result.message || 'Unknown error'));
        }
    }).catch(e => {
        showButtonFeedback('save-btn', 'Save Failed', 2000);
        alert('Error saving data: ' + e.message);
        console.error('Error saving data:', e);
    });
}

function rgbToHex(color) {
    if (color.startsWith('#')) return color;
    if (!color || color === 'none') return '#000000';
    const rgbMatch = color.match(/rgb\((\d+),\s*(\d+),\s*(\d+)\)/);
    if (rgbMatch) {
        const [, r, g, b] = rgbMatch;
        return '#' + [r, g, b].map(x => parseInt(x).toString(16).padStart(2, '0')).join('');
    }
    return color;
}

function viewSavedData(){
    showButtonFeedback('view-btn', 'Loading...', 2000);
    clearHighlights();
    loadLatestSavedData().then(data => {
        applySavedData();
        showButtonFeedback('view-btn', data && data.length > 0 ? 'Loaded!' : 'No Data', 1000);
        if (data && data.length > 0) {
            alert('Saved data reloaded on calendar!');
        } else {
            alert('No saved data found for the current year.');
        }
    }).catch(error => {
        showButtonFeedback('view-btn', 'Load Failed', 2000);
        alert('Failed to load saved data: ' + error.message);
        console.error('Error in viewSavedData:', error);
    });
}

function loadLatestSavedData(){
    return fetch('templates/holiday-planner.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=load_holiday_data'
    })
    .then(r => {
        if (!r.ok) throw new Error(`HTTP error: ${r.status} ${r.statusText}`);
        return r.json();
    })
    .then(data => {
        savedData = data || [];
        return savedData;
    })
    .catch(error => {
        savedData = [];
        throw error;
    });
}

function applySavedData(){
    count = 0;
    totalCount = 0;
    document.querySelectorAll('#calendar-container > div').forEach((monthDiv, mIndex) => {
        const month = mIndex + 1;
        monthDiv.querySelectorAll('td').forEach(td => {
            const dayNum = parseInt(td.textContent.trim());
            if (!isNaN(dayNum)) {
                const dateStr = `${currentYear}-${String(month).padStart(2, '0')}-${String(dayNum).padStart(2, '0')}`;
                const entry = savedData.find(e => e.date === dateStr);
                if (entry) {
                    if (entry.colors && entry.colors.length >= 2) {
                        td.style.background = `linear-gradient(45deg, ${entry.colors[0]} 50%, ${entry.colors[1]} 50%)`;
                        console.log(`Applying two-color entry for ${dateStr}:`, entry.colors);
                    } else if (entry.color) {
                        td.style.background = entry.color;
                        console.log(`Applying single-color entry for ${dateStr}:`, entry.color);
                    }
                    td.style.border = '1px solid black';
                    const isWeekend = td.cellIndex === 0 || td.cellIndex === 6;
                    if (!isWeekend) count++;
                    totalCount++;
                } else {
                    td.style.background = '';
                    td.style.border = '';
                }
            }
        });
    });
    document.getElementById('days-off-count').innerHTML = count;
    document.getElementById('total-off-count').innerHTML = totalCount;
}

// Initialize
setColor('#ff0000');
updateColorPreview();
applySavedData();
</script>