<?php
$pageTitle = 'QR Attendance Management';
$currentPage = 'attendance';
ob_start();
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>QR Attendance Management</h1>
        </div>
        <div class="card-body">
            <div class="section-selector">
                <label>Select Section:</label>
                <select id="sectionSelect" onchange="loadSectionData()">
                    <option value="">-- Select a Section --</option>
                    <?php foreach ($sections as $sec): ?>
                        <option value="<?= $sec['id'] ?>" data-subject="<?= htmlspecialchars($sec['subject_name']) ?>">
                            <?= htmlspecialchars($sec['subject_name']) ?> - <?= htmlspecialchars($sec['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div id="sectionInfo" style="display:none;" class="section-info">
                <h3 id="sectionName"></h3>
                <p id="subjectName"></p>
            </div>
            
            <div id="qrGenerator" style="display:none;" class="qr-generator">
                <h3>Generate QR Code for Attendance</h3>
                <form id="qrForm" class="qr-form">
                    <input type="hidden" name="section_id" id="qrSectionId">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Date:</label>
                            <input type="date" name="date" id="qrDate" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Session Type:</label>
                            <select name="session_type" id="sessionType" required>
                                <option value="morning_in">Morning In (Start of Morning)</option>
                                <option value="morning_out">Morning Out (End of Morning)</option>
                                <option value="afternoon_in">Afternoon In (Start of Afternoon)</option>
                                <option value="afternoon_out">Afternoon Out (End of Afternoon)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn-primary">Generate QR Code</button>
                        </div>
                    </div>
                </form>
                
                <div id="qrResult" class="qr-result"></div>
            </div>
            
            <div id="activeQRCodes" style="display:none;" class="active-qr-codes">
                <h3>Active QR Codes</h3>
                <div id="qrList" class="qr-list"></div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$extraCSS = '
.section-selector {
    text-align: center;
    margin-bottom: 30px;
    padding: 20px;
    background: #f8fafc;
    border-radius: 20px;
}
.section-selector select {
    padding: 12px 25px;
    border-radius: 40px;
    border: 2px solid #216699;
    font-size: 1rem;
    min-width: 300px;
}
.section-info {
    text-align: center;
    padding: 20px;
    background: #e8f4f8;
    border-radius: 20px;
    margin-bottom: 30px;
}
.section-info h3 {
    color: #216699;
    font-size: 1.5rem;
    margin-bottom: 5px;
}
.qr-generator {
    padding: 20px;
    background: white;
    border-radius: 20px;
    margin-bottom: 30px;
    border: 1px solid #e2e8f0;
}
.qr-generator h3 {
    color: #216699;
    margin-bottom: 20px;
}
.qr-form {
    max-width: 600px;
    margin: 0 auto;
}
.form-row {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: flex-end;
}
.form-group {
    flex: 1;
    min-width: 150px;
}
.form-group label {
    display: block;
    margin-bottom: 5px;
    color: #216699;
    font-weight: bold;
}
.form-group input, .form-group select {
    width: 100%;
    padding: 10px;
    border: 2px solid #216699;
    border-radius: 40px;
}
.qr-result {
    margin-top: 30px;
    padding: 20px;
    background: #fef9e3;
    border-radius: 20px;
    text-align: center;
}
.qr-code-display {
    text-align: center;
    padding: 20px;
}
.qr-code-display img {
    max-width: 200px;
    margin: 10px auto;
}
.qr-token {
    font-family: monospace;
    background: #f0f0f0;
    padding: 10px;
    border-radius: 10px;
    margin: 10px 0;
    word-break: break-all;
}
.active-qr-codes {
    padding: 20px;
    background: white;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
}
.qr-list {
    display: grid;
    gap: 15px;
    margin-top: 15px;
}
.qr-item {
    background: #fef9e3;
    padding: 15px;
    border-radius: 15px;
    border-left: 4px solid #e2362c;
}
.qr-item strong {
    color: #216699;
}
';
$extraJS = '
function loadSectionData() {
    var select = document.getElementById("sectionSelect");
    var sectionId = select.value;
    var selectedOption = select.options[select.selectedIndex];
    var subjectName = selectedOption.getAttribute("data-subject");
    
    if (sectionId) {
        document.getElementById("sectionInfo").style.display = "block";
        document.getElementById("qrGenerator").style.display = "block";
        document.getElementById("activeQRCodes").style.display = "block";
        document.getElementById("sectionName").innerHTML = "Section: " + selectedOption.text.split(" - ")[1];
        document.getElementById("subjectName").innerHTML = "Subject: " + subjectName;
        document.getElementById("qrSectionId").value = sectionId;
        loadActiveQRCodes(sectionId);
    } else {
        document.getElementById("sectionInfo").style.display = "none";
        document.getElementById("qrGenerator").style.display = "none";
        document.getElementById("activeQRCodes").style.display = "none";
    }
}

function loadActiveQRCodes(sectionId) {
    fetch("/qr/active?section_id=" + sectionId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.qr_codes.length > 0) {
                var html = "";
                data.qr_codes.forEach(function(qr) {
                    html += \'<div class="qr-item">\' +
                        \'<p><strong>Date:</strong> \' + qr.date + \'</p>\' +
                        \'<p><strong>Session:</strong> \' + qr.session_type.replace(/_/g, " ") + \'</p>\' +
                        \'<p><strong>Expires:</strong> \' + qr.expires_at + \'</p>\' +
                        \'<button onclick="deactivateQR(\' + qr.id + \')\" class="btn-danger btn-sm">Deactivate</button>\' +
                        \'</div>\';
                });
                document.getElementById("qrList").innerHTML = html;
            } else {
                document.getElementById("qrList").innerHTML = "<p>No active QR codes.</p>";
            }
        });
}

function deactivateQR(qrId) {
    if (confirm("Deactivate this QR code?")) {
        fetch("/qr/deactivate", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: "qr_id=" + qrId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("QR code deactivated.");
                loadActiveQRCodes(document.getElementById("sectionSelect").value);
            } else {
                alert("Error: " + data.message);
            }
        });
    }
}

document.getElementById("qrForm").onsubmit = function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    
    fetch("/qr/generate", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            var qrUrl = "/qr/show?token=" + data.token;
            var qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" + encodeURIComponent(qrUrl);
            
            document.getElementById("qrResult").innerHTML = 
                '<div class="qr-code-display">' +
                '<h4>QR Code Generated Successfully!</h4>' +
                '<img src="' + qrCodeUrl + '" alt="QR Code">' +
                '<p class="qr-token"><strong>Token:</strong> ' + data.token + '</p>' +
                '<p><strong>Expires:</strong> ' + data.expires_at + '</p>' +
                '<a href="' + qrUrl + '" target="_blank" class="btn-primary">Open QR Page</a> ' +
                '<button onclick="printQR(\'' + qrCodeUrl + '\', \'' + data.token + '\')" class="btn-warning">Print QR</button>' +
                '</div>';
            
            loadActiveQRCodes(document.getElementById("sectionSelect").value);
        } else {
            alert("Error: " + data.message);
        }
    });
};

function printQR(qrCodeUrl, token) {
    var printWindow = window.open("", "_blank");
    printWindow.document.write(`
        <html>
        <head><title>QR Code - Internship Tracker</title></head>
        <body style="text-align:center; font-family:Arial;">
            <h2>Attendance QR Code</h2>
            <img src="${qrCodeUrl}" style="margin:20px auto;">
            <p><strong>Token:</strong> ${token}</p>
            <p>Scan this QR code to record attendance</p>
            <p>Valid for 30 minutes only</p>
            <script>window.onload = function() { window.print(); window.close(); };</script>
        </body>
        </html>
    `);
    printWindow.document.close();
}
';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navbar.php';
echo $content;
include __DIR__ . '/../layouts/footer.php';
?>